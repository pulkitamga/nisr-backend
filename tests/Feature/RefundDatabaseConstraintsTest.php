<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RefundDatabaseConstraintsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        $database = (string)($_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '');
        if ($database === '' || $database === ':memory:') {
            $database = basename(getcwd());
        }

        putenv('DB_CONNECTION=mysql');
        putenv("DB_DATABASE={$database}");
        $_SERVER['DB_CONNECTION'] = 'mysql';
        $_ENV['DB_CONNECTION'] = 'mysql';
        $_SERVER['DB_DATABASE'] = $database;
        $_ENV['DB_DATABASE'] = $database;

        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => $database,
        ]);
    }

    protected function connectionsToTransact(): array
    {
        return ['mysql'];
    }

    public function test_refund_request_is_unique_per_order_detail(): void
    {
        $orderId = (int)DB::table('orders')->insertGetId([
            'transfer_from_branch' => 1,
            'pickup_from_branch' => 0,
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderDetailId = (int)DB::table('order_details')->insertGetId([
            'order_id' => $orderId,
            'product_id' => 1,
            'seller_id' => 1,
            'qty' => 1,
            'price' => 100,
            'tax' => 0,
            'discount' => 0,
            'delivery_status' => 'delivered',
            'is_stock_decreased' => 1,
            'refund_request' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('refund_requests')->insert([
            'order_details_id' => $orderDetailId,
            'customer_id' => 1,
            'status' => 'pending',
            'approved_count' => 0,
            'denied_count' => 0,
            'amount' => 10,
            'product_id' => 1,
            'order_id' => $orderId,
            'refund_reason' => 'Initial request',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);
        DB::table('refund_requests')->insert([
            'order_details_id' => $orderDetailId,
            'customer_id' => 1,
            'status' => 'pending',
            'approved_count' => 0,
            'denied_count' => 0,
            'amount' => 10,
            'product_id' => 1,
            'order_id' => $orderId,
            'refund_reason' => 'Duplicate request',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_refund_transaction_is_unique_per_refund_id(): void
    {
        DB::table('refund_transactions')->insert([
            'order_id' => 1,
            'payment_for' => 'Refund Request',
            'payer_id' => 1,
            'payment_receiver_id' => 1,
            'paid_by' => 'admin',
            'paid_to' => 'customer',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'amount' => 10,
            'transaction_type' => 'Refund',
            'order_details_id' => 1,
            'refund_id' => 991001,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);
        DB::table('refund_transactions')->insert([
            'order_id' => 1,
            'payment_for' => 'Refund Request',
            'payer_id' => 1,
            'payment_receiver_id' => 1,
            'paid_by' => 'admin',
            'paid_to' => 'customer',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'amount' => 10,
            'transaction_type' => 'Refund',
            'order_details_id' => 1,
            'refund_id' => 991001,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

