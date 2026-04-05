<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class OrderAdminListShellTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        $database = (string) ($_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '');
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

        view()->share('errors', new ViewErrorBag());
    }

    protected function connectionsToTransact(): array
    {
        return ['mysql'];
    }

    public function test_order_list_renders_crm_toolbar_and_choose_first_filters_visible_rows(): void
    {
        $branchId = $this->createBranch('Order Shell Branch');
        $admin = $this->createAdmin($branchId, 'order-shell-admin');
        $customer = $this->createCustomer('order-shell-customer');

        $olderOrderId = $this->nextOrderId();
        $newerOrderId = $olderOrderId + 1;

        $this->createOrder($olderOrderId, $customer->id, now()->subMinute());
        $this->createOrder($newerOrderId, $customer->id, now());

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.orders.list', [
                'status' => 'all',
                'choose_first' => 1,
            ]));

        $response->assertOk();
        $response->assertSee('id="order-list-toolbar"', false);
        $response->assertSee('data-crm-export-button="true"', false);
        $response->assertSee(route('admin.orders.export-excel', ['status' => 'all']), false);
        $response->assertSee('data-form="#order-list-toolbar"', false);
        $response->assertSee(translate('Rows_to_show'), false);
        $response->assertSee('crm-primary-link', false);
        $response->assertSee('crm-row-actions__toggle', false);
        $response->assertSee('crm-row-actions__chip', false);
        $response->assertSee('<bdi dir="ltr"', false);
        $response->assertSee(translate('More actions'), false);
        $response->assertSee(translate('invoice'), false);
        $response->assertSee('customer_id_value', false);
        $response->assertSee((string) $newerOrderId, false);
        $response->assertDontSee((string) $olderOrderId, false);
        $response->assertDontSee(translate('filter_order'), false);
    }

    private function createAdmin(int $branchId, string $prefix): Admin
    {
        return Admin::query()->create([
            'name' => ucfirst($prefix),
            'email' => $prefix . '-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'branch_id' => $branchId,
            'status' => 1,
        ]);
    }

    private function createCustomer(string $prefix): User
    {
        return User::query()->create([
            'f_name' => ucfirst($prefix),
            'l_name' => 'User',
            'phone' => '+2010' . random_int(10000000, 99999999),
            'email' => $prefix . '-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'is_active' => 1,
        ]);
    }

    private function createOrder(int $id, int $customerId, $createdAt): void
    {
        DB::table('orders')->insert([
            'id' => $id,
            'customer_id' => $customerId,
            'seller_id' => 1,
            'seller_is' => 'admin',
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cash_on_delivery',
            'order_amount' => 100,
            'paid_amount' => 0,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'extra_discount' => 0,
            'extra_discount_type' => 'amount',
            'coupon_code' => null,
            'is_shipping_free' => 0,
            'deliveryman_charge' => 0,
            'installation_charge' => 0,
            'exchange_charge' => 0,
            'order_type' => 'default',
            'checked' => 1,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function nextOrderId(): int
    {
        $maxId = (int) DB::table('orders')->max('id');

        return max(100001, $maxId + 1);
    }

    private function createSeller(string $prefix): int
    {
        return (int) DB::table('sellers')->insertGetId([
            'f_name' => ucfirst($prefix),
            'l_name' => 'Test',
            'email' => $prefix . '-' . uniqid() . '@example.com',
            'phone' => '+2011' . random_int(10000000, 99999999),
            'password' => bcrypt('password'),
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createBranch(string $name): int
    {
        $sellerId = $this->createSeller('order-shell-seller');

        return (int) DB::table('branches')->insertGetId([
            'vendor_id' => $sellerId,
            'branch_name' => $name . '-' . uniqid(),
            'branch_state' => 'Test',
            'status' => 'active',
            'email' => strtolower(str_replace(' ', '-', $name)) . '@example.com',
            'phone' => '+2012' . random_int(10000000, 99999999),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
