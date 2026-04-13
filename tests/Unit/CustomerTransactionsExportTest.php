<?php

namespace Tests\Unit;

use App\Exports\CustomerTransactionsExport;
use Illuminate\Support\Collection;
use stdClass;
use Tests\TestCase;

class CustomerTransactionsExportTest extends TestCase
{
    public function test_loyalty_export_view_uses_arabic_title_and_dates(): void
    {
        app()->setLocale('ar');

        $user = new stdClass();
        $user->f_name = 'Ahmed';
        $user->l_name = 'Ali';

        $transaction = new stdClass();
        $transaction->transaction_id = 'TXN-1';
        $transaction->user = $user;
        $transaction->credit = 10;
        $transaction->debit = 0;
        $transaction->balance = 10;
        $transaction->transaction_type = 'order_place';
        $transaction->reference = 'order_place';
        $transaction->created_at = '2026-04-13 10:00:00';

        $export = new CustomerTransactionsExport([
            'type' => 'loyalty',
            'transactions' => new Collection([$transaction]),
            'credit' => 10,
            'debit' => 0,
            'balance' => 10,
            'transaction_type' => null,
            'to' => '2026-04-13',
            'from' => '2026-04-01',
            'customer' => 'all_customers',
        ]);

        $html = $export->view()->render();

        $this->assertStringContainsString('تقرير نقاط ولاء العميل', $html);
        $this->assertStringContainsString('أبريل', $html);
    }
}
