<?php

namespace Tests\Feature;

use Tests\TestCase;

class RefundLoyaltyGateRegressionTest extends TestCase
{
    public function test_refund_flows_do_not_gate_on_customer_loyalty_balance(): void
    {
        $files = [
            base_path('app/Http/Controllers/Web/UserProfileController.php'),
            base_path('app/Http/Controllers/RestAPI/v1/OrderController.php'),
            base_path('app/Http/Controllers/Vendor/RefundController.php'),
            base_path('app/Http/Controllers/Admin/Order/RefundController.php'),
            base_path('app/Http/Controllers/RestAPI/v2/seller/RefundController.php'),
            base_path('app/Http/Controllers/RestAPI/v3/seller/RefundController.php'),
        ];

        foreach ($files as $file) {
            $source = file_get_contents($file);

            $this->assertIsString($source);
            $this->assertStringNotContainsString('you_have_not_sufficient_loyalty_point_to_refund_this_order', $source);
            $this->assertStringNotContainsString('customer_has_not_sufficient_loyalty_point_to_take_refund_for_this_order', $source);
        }
    }

    public function test_refund_loyalty_reversal_still_debits_points(): void
    {
        $source = file_get_contents(base_path('app/Repositories/LoyaltyPointTransactionRepository.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("} else if (\$transactionType == 'refund_order') {", $source);
        $this->assertStringContainsString("\$debit = \$amount;", $source);
        $this->assertStringContainsString("\$currentBalance = \$user['loyalty_point'] + \$credit - \$debit;", $source);
    }
}
