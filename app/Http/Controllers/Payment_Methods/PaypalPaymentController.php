<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\PaymentRequest;
use App\Traits\Processor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class PaypalPaymentController extends Controller
{
    use Processor;

    private $config_values;
    private $base_url;

    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('paypal', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }

        if($config){
            $this->base_url = ($config->mode == 'test') ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        }
        $this->payment = $payment;
    }

    public function token(){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->base_url.'/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_USERPWD, $this->config_values->client_id . ':' . $this->config_values->client_secret);

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $accessToken = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $accessToken;
    }

    /**
     * Responds with a welcome message with instructions
     *
     */
    public function payment(Request $request)
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

        if ($data['additional_data'] != null) {
            $business = json_decode($data['additional_data']);
            $business_name = $business->business_name ?? "my_business";
        } else {
            $business_name = "my_business";
        }

        $accessToken = json_decode($this->token(),true);

        if ( isset($accessToken['access_token'])) {
            $accessToken = $accessToken['access_token'];
            $payment_data = [];
            $payment_data['purchase_units'] = [
                [
                    'reference_id' => $data->id,
                    'custom_id' => $data->id,
                    'name' => $business_name,
                    'desc'  => 'payment ID :' . $data->id,
                    'amount' => [
                        'currency_code' => $data->currency_code,
                        'value' => round($data->payment_amount, 2)
                    ]
                ]
            ];

            $payment_data['invoice_id'] = $data->id;
            $payment_data['invoice_description'] = "Order #{$payment_data['invoice_id']} Invoice";
            $payment_data['total'] = round($data->payment_amount, 2);
            $payment_data['intent'] = 'CAPTURE';
            $payment_data['application_context'] = [
                'return_url' => route('paypal.success',['payment_id' => $data->id]),
                'cancel_url' => route('paypal.cancel',['payment_id' => $data->id])
            ];
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $this->base_url.'/v2/checkout/orders');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($payment_data));

            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = "Authorization: Bearer $accessToken";
            $headers[] = "Paypal-Request-Id:".Str::uuid();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
        }else{
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
;
        $response = json_decode($response);

        $links = $response->links;
        return Redirect::away($links[1]->href);

        return 0;

    }

    /**
     * Responds with a welcome message with instructions
     */
    public function cancel(Request $request)
    {
        $data = $this->payment::where(['id' => $request['payment_id']])->first();
        return $this->payment_response($data,'cancel');
    }

    /**
     * Responds with a welcome message with instructions
     */
    public function success(Request $request)
    {
        $accessToken = json_decode($this->token(),true);
        $accessToken = $accessToken['access_token'];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->base_url."/v2/checkout/orders/{$request->token}/capture");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = "Authorization: Bearer  $accessToken";
        $headers[] = 'Paypal-Request-Id:'.Str::uuid();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        try {
            $response = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            Log::warning('PayPal callback rejected because the capture payload was invalid JSON.', [
                'payment_id' => $request->get('payment_id'),
                'paypal_token' => $request->get('token'),
            ]);

            abort(404);
        }

        $resolvedPaymentId = (string)(
            data_get($response, 'purchase_units.0.reference_id')
            ?? data_get($response, 'purchase_units.0.custom_id')
            ?? ''
        );
        $paymentRequest = $this->payment::where('id', $resolvedPaymentId)->first();

        if (!$paymentRequest) {
            Log::warning('PayPal callback rejected because the payment request could not be resolved.', [
                'request_payment_id' => $request->get('payment_id'),
                'resolved_payment_id' => $resolvedPaymentId,
                'paypal_token' => $request->get('token'),
            ]);

            abort(404);
        }

        if ($request->filled('payment_id') && (string)$request->get('payment_id') !== $resolvedPaymentId) {
            Log::warning('PayPal callback rejected because the callback payment id did not match the captured order.', [
                'request_payment_id' => (string)$request->get('payment_id'),
                'resolved_payment_id' => $resolvedPaymentId,
            ]);

            abort(403);
        }

        $capturedAmount = (float)(data_get($response, 'purchase_units.0.payments.captures.0.amount.value') ?? -1);
        $capturedCurrency = strtoupper((string)(data_get($response, 'purchase_units.0.payments.captures.0.amount.currency_code') ?? ''));
        if ($capturedAmount !== round((float)$paymentRequest->payment_amount, 2) || $capturedCurrency !== strtoupper((string)$paymentRequest->currency_code)) {
            Log::warning('PayPal callback rejected because the capture payload did not match the payment request.', [
                'payment_request_id' => $paymentRequest->getKey(),
                'captured_amount' => $capturedAmount,
                'expected_amount' => round((float)$paymentRequest->payment_amount, 2),
                'captured_currency' => $capturedCurrency,
                'expected_currency' => strtoupper((string)$paymentRequest->currency_code),
            ]);

            abort(403);
        }

        if (($response['status'] ?? null) === 'COMPLETED') {
            $paymentRequest->update([
                'payment_method' => 'paypal',
                'is_paid' => 1,
                'transaction_id' => $response['id'] ?? $request->get('token'),
            ]);

            $data = $paymentRequest->fresh();
            $this->executePaymentHook($data?->success_hook, $data);

            return $this->payment_response($data,'success');
        }

        $payment_data = $paymentRequest->fresh();
        $this->executePaymentHook($payment_data?->failure_hook, $payment_data);

        return $this->payment_response($payment_data,'fail');
    }
}
