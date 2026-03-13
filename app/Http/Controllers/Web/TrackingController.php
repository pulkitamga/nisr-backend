<?php

namespace App\Http\Controllers\Web;

use App\Services\BostaService;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class TrackingController extends Controller
{
    protected $bostaService;

    public function __construct(BostaService $bostaService)
    {
        $this->bostaService = $bostaService;
    }

    public function trackOrder($orderId)
    {
        try {
            $order = Order::with('customer')->findOrFail($orderId);

            // Calculate if order is digital-only (fix for the view variable)
            $isOrderOnlyDigital = true;
            if ($order->details) {
                foreach ($order->details as $detail) {
                    if ($detail->product && $detail->product->product_type !== 'digital') {
                        $isOrderOnlyDigital = false;
                        break;
                    }
                }
            }

            // **CHECK IF IT'S AN AJAX REQUEST**
            if (request()->ajax() || request()->wantsJson()) {
                // Return JSON for AJAX requests
                if ($order->delivery_service_name !== 'bosta' || empty($order->third_party_delivery_tracking_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Not a Bosta shipment'
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
                    'tracking' => $trackingResult['formatted'] ?? $trackingResult['data']
                ]);
            }

            // Regular request - return HTML view
            if ($order->delivery_service_name !== 'bosta' || empty($order->third_party_delivery_tracking_id)) {
                return view('web-views.users-profile.account-details.track-order', [
                    'orderDetails' => $order,
                    'isOrderOnlyDigital' => $isOrderOnlyDigital,
                    'error' => 'This order is not using Bosta delivery service or tracking number not available'
                ]);
            }

            $trackingResult = $this->bostaService->getTrackingDetails($order->third_party_delivery_tracking_id);

            if (!$trackingResult['success']) {
                return view('web-views.users-profile.account-details.track-order', [
                    'orderDetails' => $order,
                    'isOrderOnlyDigital' => $isOrderOnlyDigital,
                    'error' => $trackingResult['message']
                ]);
            }

            return view('web-views.users-profile.account-details.track-order', [
                'orderDetails' => $order,
                'isOrderOnlyDigital' => $isOrderOnlyDigital,
                'bostaTracking' => $trackingResult['formatted'],
                'rawTracking' => $trackingResult['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Tracking error: ' . $e->getMessage());

            // Handle AJAX error
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching tracking: ' . $e->getMessage()
                ], 500);
            }

            // Handle regular request error
            return view('web-views.users-profile.account-details.track-order', [
                'isOrderOnlyDigital' => false,
                'error' => 'Failed to fetch tracking information'
            ]);
        }
    }
    public function trackByNumber(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string'
        ]);

        try {
            $trackingNumber = $request->tracking_number;

            // First try to find the order
            $order = Order::where('third_party_track_id', $trackingNumber)
                ->orWhere('third_party_delivery_tracking_id', $trackingNumber)
                ->first();

            $trackingResult = $this->bostaService->getTrackingDetails($trackingNumber);

            if (!$trackingResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $trackingResult['message']
                ]);
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
                'tracking' => $trackingResult['formatted']
            ]);
        } catch (\Exception $e) {
            Log::error('Track by number error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Invalid tracking number or service error'
            ]);
        }
    }
}
