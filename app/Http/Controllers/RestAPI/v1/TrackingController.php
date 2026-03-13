<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Services\BostaService;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    protected $bostaService;

    public function __construct(BostaService $bostaService)
    {
        $this->bostaService = $bostaService;
    }

    /**
     * Track order by ID (for app API)
     */
    public function trackOrder($trackingNumber)
    {
        try {
            // Find order by tracking number
            $order = Order::with('customer', 'details.product')
                ->where('third_party_delivery_tracking_id', $trackingNumber)
                ->firstOrFail(); // will throw exception if not found

            if ($order->delivery_service_name !== 'bosta' || empty($order->third_party_delivery_tracking_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order is not using Bosta delivery service or tracking number not available'
                ], 404);
            }

            $trackingResult = $this->bostaService->getTrackingDetails($order->third_party_delivery_tracking_id);

            if (!$trackingResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $trackingResult['message']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'tracking_number' => $order->third_party_delivery_tracking_id,
                    'order_amount' => $order->order_amount,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at,
                    'customer_name' => $order->customer ? $order->customer->f_name . ' ' . $order->customer->l_name : 'Customer',
                ],
                'tracking' => $trackingResult['formatted'] ?? $trackingResult['data']
            ]);
        } catch (\Exception $e) {
            Log::error('API Track order error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching tracking: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Track order by tracking number (optional API endpoint)
     */
    public function trackByNumber(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string'
        ]);

        try {
            $trackingNumber = $request->tracking_number;

            // Find order by tracking number
            $order = Order::where('third_party_track_id', $trackingNumber)
                ->orWhere('third_party_delivery_tracking_id', $trackingNumber)
                ->first();

            $trackingResult = $this->bostaService->getTrackingDetails($trackingNumber);

            if (!$trackingResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $trackingResult['message']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'order' => $order ? [
                    'id' => $order->id,
                    'order_amount' => $order->order_amount,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at,
                    'customer_name' => $order->customer ? $order->customer->f_name . ' ' . $order->customer->l_name : 'Customer',
                ] : null,
                'tracking' => $trackingResult['formatted'] ?? $trackingResult['data']
            ]);
        } catch (\Exception $e) {
            Log::error('API Track by number error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Invalid tracking number or service error'
            ], 500);
        }
    }
}
