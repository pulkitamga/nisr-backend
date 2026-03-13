<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BostaService
{
    private function resolveCredentials(): array
    {
        $setting = Setting::where([
            'settings_type' => 'shipping_config',
            'key_name' => 'bosta',
        ])->first();

        $stored = ($setting && (int)$setting->is_active === 1) ? ($setting->live_values ?? []) : [];

        $apiKey = $stored['api_key'] ?? config('services.bosta.key');
        $baseUrl = rtrim($stored['base_url'] ?? config('services.bosta.base_url', 'https://app.bosta.co/api/v2'), '/');

        return [
            'api_key' => $apiKey,
            'base_url' => $baseUrl,
        ];
    }

    public function createShipment($order)
    {
        $credentials = $this->resolveCredentials();
        if (empty($credentials['api_key'])) {
            throw new \Exception('Bosta API key is missing');
        }

        $address = json_decode(json_encode($order->shipping_address_data), true);

        Log::info('Order Address Data:', $address);

        $payload = [
            "type" => 10,
            "specs" => [
                "packageType" => "Parcel",
                "packageDetails" => [
                    "itemsCount" => $order->details->sum('qty'),
                    "description" => "Order #" . $order->id
                ]
            ],
            "businessReference" => (string) $order->id,
            "receiver" => [
                "firstName" => $order->customer->f_name ?? 'Customer',
                "lastName"  => $order->customer->l_name ?? '',
                "phone"     => $order->customer->phone ?? '',
                "email"     => $order->customer->email ?? ($address['email'] ?? ''),
            ],
            "dropOffAddress" => [
                "firstLine" => $this->getFirstLineAddress($address), // ✅ REQUIRED FIELD
                "cityCode"   => $this->getCityCode($address),
                "zone"       => $address['area'] ?? 'Unknown',
                "secondLine" => $address['address'] ?? 'Unknown',
                "buildingNumber" => "1",
                "floor" => "1",
                "apartment" => "1"
            ],
            "cod" => $order->payment_method === 'cash_on_delivery'
                ? (float) $order->order_amount
                : 0,
            "notes" => "Order from 6Valley",
            "allowToOpenPackage" => false
        ];

        Log::info('Bosta API Request:', $payload);

        $response = Http::withHeaders([
            'Authorization' => $credentials['api_key'],
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->timeout(30)->post($credentials['base_url'] . '/deliveries', $payload);

        Log::info('Bosta Response:', [
            'status' => $response->status(),
            'body' => $response->json()
        ]);

        if (!$response->successful()) {
            $error = $response->json();
            throw new \Exception('Bosta Error: ' . ($error['message'] ?? 'API request failed'));
        }

        return $response->json();
    }

    private function getFirstLineAddress($address)
    {
        // firstLine is usually the main street/area
        // You can use area or a combination of fields
        return $address['area'] ??
            $address['city'] ??
            $address['address'] ??
            'Main Street';
    }

    private function getCityCode($address)
    {
        if (isset($address['city_code']) && !empty($address['city_code'])) {
            return $address['city_code'];
        }

        $cityName = $address['city'] ?? '';
        $cityMap = [
            'cairo' => '01',
            'alexandria' => '02',
            'giza' => '03',
            'sharqia' => '04',
            'dakahlia' => '05',
            'beheira' => '06',
            'qalyubia' => '07',
            'menoufia' => '08',
            'gharbia' => '09',
            'port said' => '10',
            'suez' => '11',
            'ismailia' => '12',
        ];

        $lowerCity = strtolower(trim($cityName));
        return $cityMap[$lowerCity] ?? '01';
    }

    //Track order
    public function trackShipment($trackingNumber)
    {
        $credentials = $this->resolveCredentials();
        if (empty($credentials['api_key'])) {
            throw new \Exception('Bosta API key is missing');
        }

        Log::info('Tracking Bosta shipment:', ['trackingNumber' => $trackingNumber]);

        $response = Http::withHeaders([
            'Authorization' => $credentials['api_key'],
            'Accept'        => 'application/json',
        ])
            ->timeout(30)
            ->get($credentials['base_url'] . "/deliveries/business/{$trackingNumber}");

        Log::info('Bosta Tracking Response:', [
            'status' => $response->status(),
            'body'   => $response->json()
        ]);

        if (!$response->successful()) {
            $error = $response->json();
            throw new \Exception(
                $error['message'] ?? 'Bosta Tracking Error'
            );
        }

        return $response->json(); // FULL RESPONSE
    }


    public function getTrackingDetails($trackingNumber)
    {
        try {
            $response = $this->trackShipment($trackingNumber);

            return [
                'success'   => true,
                'data'      => $response['data'],
                'formatted' => $this->formatTrackingData($response['data'])
            ];
        } catch (\Exception $e) {
            Log::error('Bosta tracking error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    private function formatTrackingData($data)
    {
        $stateMap = [
            10 => ['text' => 'Pickup Requested', 'status' => 'pending'],
            21 => ['text' => 'Picked Up', 'status' => 'processing'],
            30 => ['text' => 'In Transit', 'status' => 'on_the_way'],
            41 => ['text' => 'Out for Delivery', 'status' => 'out_for_delivery'],
            45 => ['text' => 'Delivered', 'status' => 'delivered'],
            46 => ['text' => 'Returned', 'status' => 'returned'],
            47 => ['text' => 'Exception', 'status' => 'failed'],
            48 => ['text' => 'Canceled', 'status' => 'canceled'],
            49 => ['text' => 'Canceled', 'status' => 'canceled'],
        ];

        $stateCode = $data['state']['code'] ?? 0;
        $stateInfo = $stateMap[$stateCode] ?? [
            'text' => $data['state']['value'] ?? 'Unknown',
            'status' => 'pending'
        ];

        // ✅ Timeline from Bosta
        $timeline = collect($data['timeline'] ?? [])->map(function ($event) use ($stateMap) {
            return [
                'code'  => $event['code'],
                'state' => $stateMap[$event['code']]['text'] ?? ucfirst(str_replace('_', ' ', $event['value'])),
                'done'  => $event['done'],
                'date'  => $event['date'] ?? null,
            ];
        })->values();

        return [
            'trackingNumber'   => $data['trackingNumber'] ?? '',
            'businessReference' => $data['businessReference'] ?? '',
            'state'            => $stateInfo['text'],
            'status'           => $stateInfo['status'],
            'stateCode'        => $stateCode,
            'cod'              => $data['cod'] ?? 0,
            'shipmentFees'     => $data['shipmentFees'] ?? 0,
            'createdAt'        => $data['createdAt'] ?? null,
            'updatedAt'        => $data['updatedAt'] ?? null,
            'pickupAddress'    => $data['pickupAddress'] ?? [],
            'dropOffAddress'   => $data['dropOffAddress'] ?? [],
            'receiver'         => $data['receiver'] ?? [],
            'timeline'         => $timeline,
            'isDelivered'      => $stateCode === 45,
            'isConfirmed'      => $data['isConfirmedDelivery'] ?? false,
        ];
    }
}
