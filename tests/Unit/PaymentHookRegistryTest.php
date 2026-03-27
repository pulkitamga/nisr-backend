<?php

namespace Tests\Unit;

use App\Models\PaymentRequest;
use App\Support\Payments\PaymentHookRegistry;
use Tests\TestCase;

class PaymentHookRegistryTest extends TestCase
{
    public function test_known_hooks_are_allowlisted(): void
    {
        $this->assertTrue(PaymentHookRegistry::isAllowed('digital_payment_success'));
        $this->assertTrue(PaymentHookRegistry::isAllowed('warranty_claim_payment_fail'));
        $this->assertFalse(PaymentHookRegistry::isAllowed('phpinfo'));
    }

    public function test_dispatch_rejects_unknown_hooks(): void
    {
        $paymentRequest = new PaymentRequest();
        $paymentRequest->id = 'test-payment-request';

        $this->assertFalse(PaymentHookRegistry::dispatch('phpinfo', $paymentRequest));
    }

    public function test_dispatch_executes_known_noop_failure_hooks(): void
    {
        $paymentRequest = new PaymentRequest();
        $paymentRequest->id = 'test-payment-request';

        $this->assertTrue(PaymentHookRegistry::dispatch('add_fund_to_wallet_fail', $paymentRequest));
        $this->assertTrue(PaymentHookRegistry::dispatch('warranty_claim_payment_fail', $paymentRequest));
    }
}
