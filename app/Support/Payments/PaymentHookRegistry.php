<?php

namespace App\Support\Payments;

use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Log;

final class PaymentHookRegistry
{
    private const ALLOWED_HOOKS = [
        'digital_payment_success',
        'digital_payment_fail',
        'service_invoice_payment_success',
        'service_invoice_payment_fail',
        'add_fund_to_wallet_success',
        'add_fund_to_wallet_fail',
        'warranty_claim_payment_success',
        'warranty_claim_payment_fail',
    ];

    public static function isAllowed(?string $hook): bool
    {
        return is_string($hook) && in_array($hook, self::ALLOWED_HOOKS, true);
    }

    public static function dispatch(?string $hook, PaymentRequest $paymentRequest): bool
    {
        if (!self::isAllowed($hook)) {
            Log::warning('Blocked unsupported payment callback hook.', [
                'payment_request_id' => $paymentRequest->getKey(),
                'hook' => $hook,
            ]);

            return false;
        }

        match ($hook) {
            'digital_payment_success' => digital_payment_success($paymentRequest),
            'digital_payment_fail' => digital_payment_fail($paymentRequest),
            'service_invoice_payment_success' => service_invoice_payment_success($paymentRequest),
            'service_invoice_payment_fail' => service_invoice_payment_fail($paymentRequest),
            'add_fund_to_wallet_success' => add_fund_to_wallet_success($paymentRequest),
            'add_fund_to_wallet_fail' => add_fund_to_wallet_fail($paymentRequest),
            'warranty_claim_payment_success', 'warranty_claim_payment_fail' => null,
        };

        return true;
    }
}
