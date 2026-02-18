<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CrmService
{
    public static function createOrUpdateDeal(Order $order, string $stage = '', string $fulfillmentStatus = 'unfulfilled')
    {
        try {
            $deal = Deal::where('order_id', $order->id)->first();
            $orderStatus = strtolower(trim($order->order_status));

            $dealStatus = match ($orderStatus) {
                'delivered' => 'won',
                'canceled'  => 'lost',
                default     => 'open',
            };

            $stage = match ($orderStatus) {
                'delivered'        => 'Won/Closed',
                'confirmed'        => 'Confirmed Order',
                'pending'          => 'Pending Order',
                'processing'       => 'Processing Order',
                'out_for_delivery' => 'Out for Delivery',
                'failed'           => 'Failed Payment',
                'canceled'         => 'Canceled',
                'returned'         => 'Returned',
                default            => 'Pending Order',
            };
            $data = [
                'order_id'           => $order->id,
                'source_id'          => $order->id,
                'contact_id'         => $order->customer_id,
                'related_party_type' => 'contact',
                'related_party_id'   => $order->customer_id,
                'stage'              => $stage,
                'status'             => $dealStatus,
                'payment_status'     => $order->payment_status,
                'fulfillment_status' => $fulfillmentStatus,
                'owner_id'           => null,
                'department_id'      => null,
                'priority'           => 'low',
                'value'              => $order->order_amount ?? 0,
            ];

            if ($deal) {
                $deal->update($data);
            } else {
                $deal = Deal::create($data);
            }

            $activityType = match ($orderStatus) {
                'delivered'        => 'Order Delivered',
                'confirmed'        => 'Order Confirmed',
                'pending'          => 'Order Created',
                'processing'       => 'Order Processing',
                'out_for_delivery' => 'Out for Delivery',
                'failed'           => 'Payment Failed',
                'canceled'         => 'Order Canceled',
                'returned'         => 'Order Returned',
                default            => 'Order Created',
            };

            $title = match ($orderStatus) {
                'delivered'        => 'Order Successfully Delivered',
                'confirmed'        => 'Order Confirmed',
                'pending'          => 'New Order Created',
                'processing'       => 'Order is Processing',
                'out_for_delivery' => 'Order Out for Delivery',
                'failed'           => 'Payment Failed',
                'canceled'         => 'Order Canceled',
                'returned'         => 'Order Returned',
                default            => 'Order Created',
            };

            $subject = match ($orderStatus) {
                'delivered'        => "Order ID {$order->id} has been successfully delivered.",
                'confirmed'        => "Order ID {$order->id} is now confirmed.",
                'pending'          => "Order has been created with Order ID {$order->id}.",
                'processing'       => "Order ID {$order->id} is currently being processed.",
                'out_for_delivery' => "Order ID {$order->id} is now out for delivery. Delivery has been initialized.",
                'failed'           => "Payment failed for Order ID {$order->id}.",
                'canceled'         => "Order ID {$order->id} has been canceled.",
                'returned'         => "Order ID {$order->id} has been returned.",
                default            => "Order ID {$order->id} has been created.",
            };

            $activity = DealActivity::create([
                'deal_id'      => $deal->id,
                'activity_type'=> $activityType,
                'title'        => $title,
                'subject'      => substr($subject, 0, 255),
                'note_date'    => now(),
                'employee_id'  => Auth::guard('admin')->id() ?? 1,
                'details'      => json_encode([
                    'order_id'       => $order->id,
                    'stage'          => $stage,
                    'payment_status' => $order->payment_status,
                    'order_status'   => $order->order_status,
                ]),
            ]);

            return $deal;
        } catch (\Exception $e) {
            Log::error('CRM deal sync failed', [
                'order_id' => $order->id ?? null,
                'order_status' => $order->order_status ?? null,
                'payment_status' => $order->payment_status ?? null,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return null;
        }
    }
}
