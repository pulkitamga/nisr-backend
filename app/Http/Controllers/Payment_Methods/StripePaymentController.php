<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\PaymentRequest;
use App\Traits\Processor;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Throwable;

class StripePaymentController extends Controller
{
    use Processor;

    private $config_values;
    private PaymentRequest $payment;

    public function __construct(PaymentRequest $payment)
    {
        $config = $this->payment_config('stripe', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config_values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config_values = json_decode($config->test_values);
        }
        $this->payment = $payment;
    }

    public function index(Request $request): View|Factory|JsonResponse|Application
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
        $config = $this->config_values;

        return view('payment.stripe', compact('data', 'config'));
    }

    public function payment_process_3d(Request $request): JsonResponse
    {
        $data = $this->payment::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        $payment_amount = $data['payment_amount'];

        Stripe::setApiKey($this->config_values->api_key);
        header('Content-Type: application/json');
        $currency_code = $data->currency_code;

        if ($data['additional_data'] != null) {
            $business = json_decode($data['additional_data']);
            $business_name = $business->business_name ?? "my_business";
            $business_logo = $business->business_logo ??  url('/');
        } else {
            $business_name = "my_business";
            $business_logo = url('/');
        }

        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency_code ?? 'usd',
                    'unit_amount' => round($payment_amount, 2) * 100,
                    'product_data' => [
                        'name' => $business_name,
                        'images' => [$business_logo],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'client_reference_id' => (string)$data->id,
            'metadata' => [
                'payment_id' => (string)$data->id,
                'currency_code' => (string)$currency_code,
            ],
            'success_url' => url('/') . '/payment/stripe/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url()->previous(),
        ]);

        return response()->json(['id' => $checkout_session->id]);
    }

    public function success(Request $request)
    {
        Stripe::setApiKey($this->config_values->api_key);
        try {
            $session = Session::retrieve($request->get('session_id'));
        } catch (Throwable $exception) {
            Log::warning('Stripe callback rejected because the checkout session could not be retrieved.', [
                'session_id' => $request->get('session_id'),
                'payment_id' => $request->get('payment_id'),
            ]);

            abort(404);
        }

        $paymentId = (string)(data_get($session, 'metadata.payment_id') ?? $session->client_reference_id ?? '');
        $paymentRequest = $this->payment::where('id', $paymentId)->first();

        if (!$paymentRequest) {
            Log::warning('Stripe callback rejected because the payment request was not found.', [
                'session_id' => $request->get('session_id'),
                'resolved_payment_id' => $paymentId,
            ]);

            abort(404);
        }

        if ($request->filled('payment_id') && (string)$request->get('payment_id') !== $paymentId) {
            Log::warning('Stripe callback rejected because the callback payment id did not match the Stripe session metadata.', [
                'request_payment_id' => (string)$request->get('payment_id'),
                'resolved_payment_id' => $paymentId,
            ]);

            abort(403);
        }

        $amountMatches = (int)($session->amount_total ?? -1) === (int)round(((float)$paymentRequest->payment_amount) * 100);
        $currencyMatches = strtoupper((string)($session->currency ?? '')) === strtoupper((string)$paymentRequest->currency_code);

        if (!$amountMatches || !$currencyMatches) {
            Log::warning('Stripe callback rejected because the session payload did not match the payment request.', [
                'payment_request_id' => $paymentRequest->getKey(),
                'session_amount_total' => $session->amount_total ?? null,
                'expected_amount_total' => (int)round(((float)$paymentRequest->payment_amount) * 100),
                'session_currency' => $session->currency ?? null,
                'expected_currency' => $paymentRequest->currency_code,
            ]);

            abort(403);
        }

        if ($session->payment_status == 'paid' && $session->status == 'complete') {
            $paymentRequest->update([
                'payment_method' => 'stripe',
                'is_paid' => 1,
                'transaction_id' => $session->payment_intent,
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
