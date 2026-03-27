<?php

namespace App\Http\Controllers\Payment_Methods;


use App\Models\PaymentRequest;
use App\Models\User;
use App\Traits\Processor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PaymobController extends Controller
{
    use Processor;

    private const DEFAULT_BASE_URL = 'https://accept.paymob.com';

    private const CALLBACK_HMAC_FIELDS = [
        'amount_cents',
        'created_at',
        'currency',
        'error_occured',
        'has_parent_transaction',
        'id',
        'integration_id',
        'is_3d_secure',
        'is_auth',
        'is_capture',
        'is_refunded',
        'is_standalone_payment',
        'is_voided',
        'order',
        'owner',
        'pending',
        'source_data_pan',
        'source_data_sub_type',
        'source_data_type',
        'success',
    ];

    private $config_values;

    private PaymentRequest $payment;
    private $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('paymob_accept', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }
        $this->payment = $payment;
        $this->user = $user;
    }

    protected function cURL(string $url, array $json, array $extraHeaders = []): array
    {
        $ch = curl_init($url);
        $headers = array_merge(['Content-Type: application/json'], $extraHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $output = curl_exec($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($output === false) {
            $errorMessage = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Paymob request failed: ' . $errorMessage);
        }

        curl_close($ch);

        $decodedResponse = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid Paymob response payload');
        }

        if ($statusCode >= 400) {
            throw new \RuntimeException('Paymob request failed with status: ' . $statusCode);
        }

        return $decodedResponse;
    }

    public function credit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }

        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }

        session()->put('payment_id', $data->id);

        if ($data['additional_data'] != null) {
            $business = json_decode($data['additional_data']);
            $business_name = $business->business_name ?? "my_business";
        } else {
            $business_name = "my_business";
        }

        $payer = json_decode($data['payer_information']);

        try {
            $token = $this->getToken();
            $order = $this->createOrder($token, $data, $business_name, (string)$data->id);
            if (isset($order['id'])) {
                $data->update(['transaction_id' => (string)$order['id']]);
            }
            $paymentToken = $this->getPaymentToken($order, $token, $data, $payer);
        } catch (Throwable $exception) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_404), 200);
        }
        $baseUrl = rtrim((string)($this->config_values->base_url ?? self::DEFAULT_BASE_URL), '/');

        return Redirect::away($baseUrl . '/api/acceptance/iframes/' . $this->config_values->iframe_id . '?payment_token=' . $paymentToken);
    }

    public function getToken(): string
    {
        $response = $this->cURL(
            'https://accept.paymob.com/api/auth/tokens',
            ['api_key' => $this->config_values->api_key]
        );

        if (!isset($response['token'])) {
            throw new \RuntimeException('Missing Paymob auth token');
        }

        return (string)$response['token'];
    }

    public function createOrder(string $token, PaymentRequest $payment_data, string $business_name, string $merchantOrderId): array
    {
        $items[] = [
            'name' => $business_name,
            'amount_cents' => round($payment_data->payment_amount * 100),
            'description' => 'payment ID :' . $payment_data->id,
            'quantity' => 1
        ];

        $data = [
            "auth_token" => $token,
            "delivery_needed" => "false",
            "amount_cents" => round($payment_data->payment_amount * 100),
            "currency" => $payment_data->currency_code,
            "items" => $items,
            "merchant_order_id" => $merchantOrderId,
        ];

        return $this->cURL(
            'https://accept.paymob.com/api/ecommerce/orders',
            $data
        );
    }

    public function getPaymentToken(array $order, string $token, PaymentRequest $payment_data, object $payer): string
    {
        $value = $payment_data->payment_amount;
        $billingData = [
            "apartment" => "N/A",
            "email" => $payer->email,
            "floor" => "N/A",
            "first_name" => $payer->name,
            "street" => "N/A",
            "building" => "N/A",
            "phone_number" => $payer->phone ?? "N/A",
            "shipping_method" => "PKG",
            "postal_code" => "N/A",
            "city" => "N/A",
            "country" => "N/A",
            "last_name" => $payer->name,
            "state" => "N/A",
        ];

        $data = [
            "auth_token" => $token,
            "amount_cents" => round($value * 100),
            "expiration" => 3600,
            "order_id" => $order['id'] ?? null,
            "billing_data" => $billingData,
            "currency" => $payment_data->currency_code,
            "integration_id" => $this->config_values->integration_id
        ];

        if (!$data['order_id']) {
            throw new \RuntimeException('Missing Paymob order id');
        }

        $response = $this->cURL(
            'https://accept.paymob.com/api/acceptance/payment_keys',
            $data
        );

        if (!isset($response['token'])) {
            throw new \RuntimeException('Missing Paymob payment token');
        }

        return (string)$response['token'];
    }

    public function callback(Request $request)
    {
        $callbackData = $this->extractCallbackData($request);
        $paymentData = $this->resolvePaymentRequest($callbackData, $request);
        $calculatedHmac = $this->generateHmac($callbackData['fields']);
        $receivedHmac = (string)($callbackData['hmac'] ?? '');
        $isValidHmac = $receivedHmac !== '' && hash_equals(strtolower($calculatedHmac), strtolower($receivedHmac));
        $isSuccessfulPayment = $this->parseBoolean($callbackData['success'] ?? false);
        $isPending = $this->parseBoolean($callbackData['pending'] ?? false);

        if ($isValidHmac && $isSuccessfulPayment && $paymentData) {
            if ((int)$paymentData->is_paid !== 1) {
                $paymentData->update([
                    'payment_method' => 'paymob_accept',
                    'is_paid' => 1,
                    'transaction_id' => (string)($callbackData['transaction_id'] ?? $paymentData->transaction_id ?? $paymentData->id),
                ]);
                $paymentData->refresh();
                $this->executePaymentHook($paymentData?->success_hook, $paymentData);
            }

            return $this->buildCallbackResponse($request, $paymentData, 'success', 200);
        }

        if ($paymentData && $isValidHmac && !$isPending) {
            $this->executePaymentHook($paymentData->failure_hook, $paymentData);
        }

        return $this->buildCallbackResponse($request, $paymentData, 'fail', $isValidHmac ? 200 : 400);
    }

    private function extractCallbackData(Request $request): array
    {
        $allData = $request->all();
        $processedTransactionObject = $request->input('obj');

        if (is_array($processedTransactionObject)) {
            $orderId = data_get($processedTransactionObject, 'order.id');

            return [
                'hmac' => $request->input('hmac') ?? $request->query('hmac'),
                'success' => data_get($processedTransactionObject, 'success'),
                'pending' => data_get($processedTransactionObject, 'pending'),
                'order_id' => $orderId,
                'merchant_order_id' => data_get($processedTransactionObject, 'order.merchant_order_id')
                    ?? data_get($processedTransactionObject, 'merchant_order_id')
                    ?? data_get($processedTransactionObject, 'payment_key_claims.extra.payment_id'),
                'transaction_id' => data_get($processedTransactionObject, 'id'),
                'fields' => [
                    'amount_cents' => data_get($processedTransactionObject, 'amount_cents'),
                    'created_at' => data_get($processedTransactionObject, 'created_at'),
                    'currency' => data_get($processedTransactionObject, 'currency'),
                    'error_occured' => data_get($processedTransactionObject, 'error_occured'),
                    'has_parent_transaction' => data_get($processedTransactionObject, 'has_parent_transaction'),
                    'id' => data_get($processedTransactionObject, 'id'),
                    'integration_id' => data_get($processedTransactionObject, 'integration_id'),
                    'is_3d_secure' => data_get($processedTransactionObject, 'is_3d_secure'),
                    'is_auth' => data_get($processedTransactionObject, 'is_auth'),
                    'is_capture' => data_get($processedTransactionObject, 'is_capture'),
                    'is_refunded' => data_get($processedTransactionObject, 'is_refunded'),
                    'is_standalone_payment' => data_get($processedTransactionObject, 'is_standalone_payment'),
                    'is_voided' => data_get($processedTransactionObject, 'is_voided'),
                    'order' => $orderId,
                    'owner' => data_get($processedTransactionObject, 'owner'),
                    'pending' => data_get($processedTransactionObject, 'pending'),
                    'source_data_pan' => data_get($processedTransactionObject, 'source_data.pan'),
                    'source_data_sub_type' => data_get($processedTransactionObject, 'source_data.sub_type'),
                    'source_data_type' => data_get($processedTransactionObject, 'source_data.type'),
                    'success' => data_get($processedTransactionObject, 'success'),
                ],
            ];
        }

        $orderId = data_get($allData, 'order') ?? data_get($allData, 'order_id');

        return [
            'hmac' => data_get($allData, 'hmac'),
            'success' => data_get($allData, 'success'),
            'pending' => data_get($allData, 'pending'),
            'order_id' => $orderId,
            'merchant_order_id' => data_get($allData, 'merchant_order_id')
                ?? data_get($allData, 'special_reference')
                ?? data_get($allData, 'payment_id'),
            'transaction_id' => data_get($allData, 'id'),
            'fields' => [
                'amount_cents' => data_get($allData, 'amount_cents'),
                'created_at' => data_get($allData, 'created_at'),
                'currency' => data_get($allData, 'currency'),
                'error_occured' => data_get($allData, 'error_occured'),
                'has_parent_transaction' => data_get($allData, 'has_parent_transaction'),
                'id' => data_get($allData, 'id'),
                'integration_id' => data_get($allData, 'integration_id'),
                'is_3d_secure' => data_get($allData, 'is_3d_secure'),
                'is_auth' => data_get($allData, 'is_auth'),
                'is_capture' => data_get($allData, 'is_capture'),
                'is_refunded' => data_get($allData, 'is_refunded'),
                'is_standalone_payment' => data_get($allData, 'is_standalone_payment'),
                'is_voided' => data_get($allData, 'is_voided'),
                'order' => $orderId,
                'owner' => data_get($allData, 'owner'),
                'pending' => data_get($allData, 'pending'),
                'source_data_pan' => data_get($allData, 'source_data_pan') ?? data_get($allData, 'source_data.pan'),
                'source_data_sub_type' => data_get($allData, 'source_data_sub_type') ?? data_get($allData, 'source_data.sub_type'),
                'source_data_type' => data_get($allData, 'source_data_type') ?? data_get($allData, 'source_data.type'),
                'success' => data_get($allData, 'success'),
            ],
        ];
    }

    private function resolvePaymentRequest(array $callbackData, Request $request): ?PaymentRequest
    {
        $candidatePaymentIds = array_filter([
            data_get($callbackData, 'merchant_order_id'),
            $request->query('payment_id'),
            session('payment_id'),
        ], fn($value) => !is_null($value) && $value !== '');

        foreach ($candidatePaymentIds as $paymentId) {
            $paymentData = $this->payment::where('id', (string)$paymentId)->first();
            if ($paymentData) {
                return $paymentData;
            }
        }

        $orderId = data_get($callbackData, 'order_id');
        if ($orderId) {
            return $this->payment::where('transaction_id', (string)$orderId)
                ->latest('created_at')
                ->first();
        }

        return null;
    }

    private function generateHmac(array $callbackFields): string
    {
        $connectedString = '';
        foreach (self::CALLBACK_HMAC_FIELDS as $field) {
            $connectedString .= $this->normalizeHmacValue($callbackFields[$field] ?? null);
        }

        $secret = (string)($this->config_values->hmac ?? '');
        return hash_hmac('sha512', $connectedString, $secret);
    }

    private function normalizeHmacValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return '';
        }

        if (is_scalar($value)) {
            return (string)$value;
        }

        return '';
    }

    private function parseBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value === 1;
        }

        return in_array(strtolower((string)$value), ['true', '1', 'yes'], true);
    }

    private function buildCallbackResponse(Request $request, ?PaymentRequest $paymentData, string $paymentStatus, int $statusCode)
    {
        // Processed callbacks are server-to-server POST requests from Paymob and should return JSON.
        if ($request->isMethod('post') || is_array($request->input('obj'))) {
            return response()->json(['status' => $paymentStatus], $statusCode);
        }

        if ($paymentData) {
            session()->forget('payment_id');
            return $this->payment_response($paymentData, $paymentStatus);
        }

        return redirect()->route('payment-fail');
    }
}
