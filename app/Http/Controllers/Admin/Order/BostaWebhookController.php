<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Log;

class BostaWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Bosta Webhook Received:', $request->all());

        $payload = $request->json()->all();
        $trackingNumber = $payload['trackingNumber'] ?? null;
        $stateCode = (int) ($payload['state'] ?? 0);
        $businessReference = $payload['businessReference'] ?? null;
        
        if (!$trackingNumber || !$businessReference) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $order = Order::with('details')
            ->where('third_party_delivery_tracking_id', $trackingNumber)
            ->orWhere('id', $businessReference)
            ->first();

        if (!$order) {
            Log::error('Bosta Webhook: Order not found.', [
                'tracking' => $trackingNumber, 
                'businessRef' => $businessReference
            ]);
            return response()->json(['error' => 'Order not found'], 404);
        }

        $localStatus = $this->mapBostaStateToOrderStatus($stateCode, $payload['type'] ?? 'SEND');
        $deliveryStatus = $this->mapToDeliveryStatus($localStatus);
        
        $previousStatus = $order->order_status;
        $order->order_status = $localStatus;
        
        $this->updateOrderDetails($order, $deliveryStatus, $localStatus);
        $this->handleSpecialCases($order, $payload, $localStatus);
        $order->save();
        $this->createStatusHistory($order, $previousStatus, $localStatus, $stateCode, $payload);

        Log::info("Bosta Webhook: Order #{$order->id} updated from '{$previousStatus}' to '{$localStatus}'");
        return response()->json(['message' => 'Webhook processed successfully']);
    }

    private function mapBostaStateToOrderStatus(int $bostaStateCode, string $orderType): string
    {
        $stateMap = [
            10 => 'pending', 11 => 'pending', 20 => 'processing', 21 => 'processing',
            22 => 'processing', 23 => 'processing', 24 => 'processing', 25 => 'processing',
            30 => 'out_for_delivery', 40 => 'out_for_delivery', 41 => 'out_for_delivery',
            45 => 'delivered', 46 => 'delivered', 47 => 'failed', 48 => 'canceled',
            49 => 'canceled', 100 => 'failed', 101 => 'failed', 102 => 'processing',
            103 => 'pending', 104 => 'delivered', 105 => 'processing',
        ];

        $status = $stateMap[$bostaStateCode] ?? 'processing';
        
        if ($orderType !== 'SEND' && $bostaStateCode === 46) {
            $status = 'returned';
        }
        
        return $status;
    }

    private function mapToDeliveryStatus(string $orderStatus): string
    {
        $deliveryStatusMap = [
            'pending' => 'pending', 'confirmed' => 'confirmed', 'processing' => 'processing',
            'out_for_delivery' => 'on_the_way', 'delivered' => 'delivered', 'returned' => 'returned',
            'failed' => 'failed', 'canceled' => 'canceled',
        ];
        
        return $deliveryStatusMap[$orderStatus] ?? 'pending';
    }

    private function updateOrderDetails(Order $order, string $deliveryStatus, string $orderStatus): void
    {
        $updatedCount = $order->details()->update(['delivery_status' => $deliveryStatus]);
        
        if ($orderStatus === 'delivered' && $order->payment_method === 'cash_on_delivery') {
            $order->details()->update(['payment_status' => 'paid']);
            Log::info("Updated payment_status to 'paid' for Order #{$order->id}");
        }
        
        Log::info("Updated {$updatedCount} order detail(s) with delivery_status: '{$deliveryStatus}'");
    }

    private function handleSpecialCases(Order $order, array $payload, string $status): void
    {
        if ($status === 'delivered' && isset($payload['cod'])) {
            $codAmount = $payload['cod'];
            Log::info("COD amount collected: {$codAmount} for Order #{$order->id}");
            
            if ($order->payment_method === 'cash_on_delivery') {
                $order->payment_status = 'paid';
                $order->paid_amount = $codAmount;
            }
        }
        
        if (isset($payload['isConfirmedDelivery']) && $payload['isConfirmedDelivery']) {
            Log::info("Confirmed delivery proof received for Order #{$order->id}");
        }
        
        if (isset($payload['deliveryPromiseDate'])) {
            $promiseDate = $payload['deliveryPromiseDate'];
            try {
                $date = \DateTime::createFromFormat('d-m-Y', $promiseDate);
                if ($date) {
                    $order->expected_delivery_date = $date->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                Log::warning("Failed to parse delivery promise date: {$promiseDate}");
            }
        }
        
        if (isset($payload['exceptionReason'])) {
            $currentNote = $order->order_note ?? '';
            $exceptionNote = "Bosta Exception: {$payload['exceptionReason']}";
            if (isset($payload['exceptionCode'])) {
                $exceptionNote .= " (Code: {$payload['exceptionCode']})";
            }
            if (isset($payload['numberOfAttempts'])) {
                $exceptionNote .= " - Attempts: {$payload['numberOfAttempts']}";
            }
            $order->order_note = $currentNote ? $currentNote . "\n" . $exceptionNote : $exceptionNote;
        }
    }

    private function createStatusHistory(
        Order $order, 
        string $previousStatus, 
        string $newStatus, 
        int $bostaStateCode,
        array $payload
    ): void {
        $statusNames = [
            10 => 'Pickup requested', 11 => 'Waiting for route', 20 => 'Route Assigned',
            21 => 'Picked up from business', 22 => 'Picking up from consignee',
            23 => 'Picked up from consignee', 24 => 'Received at warehouse',
            25 => 'Fulfilled', 30 => 'In transit between Hubs', 40 => 'Picking up (Cash Collection)',
            41 => 'Picked up (Out for delivery)', 45 => 'Delivered', 46 => 'Returned to business',
            47 => 'Exception', 48 => 'Terminated', 49 => 'Canceled', 100 => 'Lost', 101 => 'Damaged',
        ];
        
        $statusName = $statusNames[$bostaStateCode] ?? "Bosta State {$bostaStateCode}";
        
        $cause = '';
        if (isset($payload['exceptionReason'])) {
            $cause = "Exception: {$payload['exceptionReason']}";
            if (isset($payload['exceptionCode'])) {
                $cause .= " (Code: {$payload['exceptionCode']})";
            }
        }
        
        if (isset($payload['numberOfAttempts']) && $payload['numberOfAttempts'] > 0) {
            $cause .= $cause ? ', ' : '';
            $cause .= "Delivery attempts: {$payload['numberOfAttempts']}";
        }

        if (isset($payload['cod']) && $payload['cod'] > 0) {
            $cause .= $cause ? ', ' : '';
            $cause .= "COD collected: {$payload['cod']}";
        }

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => 0,
            'user_type' => 'admin',
            'status' => $newStatus,
            'cause' => $cause ?: "Bosta status update: {$statusName}",
        ]);
        
        Log::info("Created status history for Order #{$order->id}: {$statusName}");
    }
}