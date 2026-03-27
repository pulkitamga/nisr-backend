<?php

namespace App\Http\Controllers\Payment_Methods;

use App\Models\PaymentRequest;
use App\Models\User;
use App\Traits\Processor;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaystackController extends Controller
{
    use Processor;

    private PaymentRequest $payment;
    private $user;

    public function __construct(PaymentRequest $payment, User $user)
    {
        $config = $this->payment_config('paystack', 'payment_config');
        $values = false;
        if (!is_null($config) && $config->mode == 'live') {
            $values = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $values = json_decode($config->test_values);
        }

        if ($values) {
            $config = array(
                'publicKey' => env('PAYSTACK_PUBLIC_KEY', $values->public_key),
                'secretKey' => env('PAYSTACK_SECRET_KEY', $values->secret_key),
                'paymentUrl' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
                'merchantEmail' => env('MERCHANT_EMAIL', $values->merchant_email),
            );
            Config::set('paystack', $config);
        }

        $this->payment = $payment;
        $this->user = $user;
    }

    public function index(Request $request)
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

        $payer = json_decode($data['payer_information']);

        $reference = Paystack::genTranxRef();

        return view('payment.paystack', compact('data', 'payer', 'reference'));
    }

    public function redirectToGateway(Request $request)
    {
        return Paystack::getAuthorizationUrl()->redirectNow();
    }

    public function handleGatewayCallback(Request $request)
    {
        $paymentDetails = Paystack::getPaymentData();
        $resolvedPaymentId = (string)($paymentDetails['data']['metadata']['payment_id'] ?? '');
        $paymentRequest = $this->payment::where('id', $resolvedPaymentId)->first();

        if (!$paymentRequest) {
            Log::warning('Paystack callback rejected because the payment request could not be resolved.', [
                'resolved_payment_id' => $resolvedPaymentId,
                'transaction_reference' => $request['trxref'] ?? null,
            ]);

            abort(404);
        }

        if ($paymentDetails['status'] == true) {
            $amountMatches = (int)($paymentDetails['data']['amount'] ?? -1) === (int)round(((float)$paymentRequest->payment_amount) * 100);
            $currencyMatches = strtoupper((string)($paymentDetails['data']['currency'] ?? '')) === strtoupper((string)$paymentRequest->currency_code);

            if (!$amountMatches || !$currencyMatches) {
                Log::warning('Paystack callback rejected because the payment payload did not match the payment request.', [
                    'payment_request_id' => $paymentRequest->getKey(),
                    'callback_amount' => $paymentDetails['data']['amount'] ?? null,
                    'expected_amount' => (int)round(((float)$paymentRequest->payment_amount) * 100),
                    'callback_currency' => $paymentDetails['data']['currency'] ?? null,
                    'expected_currency' => $paymentRequest->currency_code,
                ]);

                abort(403);
            }

            $paymentRequest->update([
                'payment_method' => 'paystack',
                'is_paid' => 1,
                'transaction_id' => $request['trxref'],
            ]);
            $data = $paymentRequest->fresh();
            $this->executePaymentHook($data?->success_hook, $data);

            return $this->payment_response($data, 'success');
        }

        $payment_data = $paymentRequest->fresh();
        $this->executePaymentHook($payment_data?->failure_hook, $payment_data);

        return $this->payment_response($payment_data, 'fail');
    }

    public function cancel(Request $request): Application|JsonResponse|Redirector|RedirectResponse
    {
        $payment_data = $this->payment::where(['id' => $request['payments_id']])->first();
        $this->executePaymentHook($payment_data?->failure_hook, $payment_data);

        return $this->payment_response($payment_data, 'fail');
    }
}
