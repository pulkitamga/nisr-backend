<?php

namespace Tests\Feature;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Models\Admin;
use App\Models\QuotationMeta;
use App\Models\WholesalePurchaseOrder;
use App\Models\WholesaleQuotation;
use App\Models\WholesaleQuotationItem;
use App\Support\AdminPermissionRegistry;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WholesaleAdminWorkflowTest extends TestCase
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

        $this->mock(AdminNotificationRepositoryInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('notifyRecipients')
                ->andReturn(new Collection());
        });

        $this->mock(TranslationRepositoryInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('add')
                ->andReturn(true);
        });
    }

    protected function connectionsToTransact(): array
    {
        return ['mysql'];
    }

    public function test_authorized_admin_can_assign_unique_purchase_order_number(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
            'wholesaler_section.assign_purchase_order_no',
        ]);

        $wholesaler = $this->createWholesalerUser();
        $order = WholesalePurchaseOrder::query()->create([
            'order_id' => 'PO-ASSIGN-' . uniqid(),
            'wholeseller_id' => $wholesaler->id,
            'wholeseller_tier' => 'gold',
            'status' => 'pending',
            'final_price' => 100.00,
        ]);

        $response = $this->from('/admin/wholesale/business/purchase-request')
            ->post(route('admin.wholesale.business.order.assign-number'), [
                'order_id' => $order->id,
                'purchase_order_no' => 'PO-UNIQUE-' . uniqid(),
            ]);

        $response->assertRedirect('/admin/wholesale/business/purchase-request');
        $this->assertSame('processed', (string) $order->fresh()->status);
        $this->assertNotNull($order->fresh()->purchase_order_no);
    }

    public function test_authorized_admin_cannot_assign_duplicate_purchase_order_number(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
            'wholesaler_section.assign_purchase_order_no',
        ]);

        $wholesaler = $this->createWholesalerUser();
        WholesalePurchaseOrder::query()->create([
            'order_id' => 'PO-EXISTING-' . uniqid(),
            'purchase_order_no' => 'PO-DUPLICATE-1',
            'wholeseller_id' => $wholesaler->id,
            'wholeseller_tier' => 'gold',
            'status' => 'processed',
            'final_price' => 150.00,
        ]);

        $targetOrder = WholesalePurchaseOrder::query()->create([
            'order_id' => 'PO-TARGET-' . uniqid(),
            'wholeseller_id' => $wholesaler->id,
            'wholeseller_tier' => 'gold',
            'status' => 'pending',
            'final_price' => 90.00,
        ]);

        $response = $this->from('/admin/wholesale/business/purchase-request')
            ->post(route('admin.wholesale.business.order.assign-number'), [
                'order_id' => $targetOrder->id,
                'purchase_order_no' => 'PO-DUPLICATE-1',
            ]);

        $response->assertRedirect('/admin/wholesale/business/purchase-request');
        $response->assertSessionHasErrors('purchase_order_no');
        $this->assertNull($targetOrder->fresh()->purchase_order_no);
        $this->assertSame('pending', (string) $targetOrder->fresh()->status);
    }

    public function test_authorized_admin_can_create_wholesale_quotation_through_route(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
            'wholesaler_section.create_quotation',
        ]);

        $wholesaler = $this->createWholesalerUser();
        $productId = $this->createProduct();
        $quotationNo = 'Q-' . uniqid();

        $response = $this->post(route('admin.wholesale.business.store-quotation'), [
            'quotation_no' => $quotationNo,
            'wholesaler_id' => $wholesaler->id,
            'wholesale_tier' => 'gold',
            'wholesaler_discount' => '10%',
            'wholesaler_discount_amount' => 25.50,
            'final_price' => 274.50,
            'lang' => ['ar', 'en'],
            'terms_and_conditions' => ['ar terms', 'en terms'],
            'note' => ['ar note', 'en note'],
            'products' => [
                [
                    'product_id' => $productId,
                    'variation_type' => 'Left',
                    'approved_quantity' => 3,
                    'price' => 100,
                    'final_price' => 285,
                    'tax' => '14%',
                ],
            ],
            'charges' => [
                ['name' => 'Shipping', 'value' => 15],
            ],
            'discounts' => [
                ['name' => 'Promo', 'value' => 10.5],
            ],
        ]);

        $response->assertRedirect(route('admin.wholesale.business.wholesale.order'));

        $quotation = WholesaleQuotation::query()
            ->where('quotation_no', $quotationNo)
            ->firstOrFail();

        $this->assertSame($wholesaler->id, (int) $quotation->wholeseller_id);
        $this->assertSame('gold', (string) $quotation->wholeseller_tier);
        $this->assertSame('en terms', (string) $quotation->terms_and_conditions);
        $this->assertSame('en note', (string) $quotation->note);

        $item = WholesaleQuotationItem::query()
            ->where('wholesale_quotation_id', $quotation->id)
            ->firstOrFail();

        $this->assertSame($productId, (int) $item->product_id);
        $this->assertSame(3, (int) $item->product_quantity);
        $this->assertSame('Left', (string) $item->product_variation_type);
        $this->assertSame(285.0, (float) $item->final_price);

        $metaSummary = QuotationMeta::query()
            ->where('wholesale_quotation_id', $quotation->id)
            ->orderBy('type')
            ->orderBy('key')
            ->get(['type', 'key', 'value'])
            ->map(fn(QuotationMeta $meta): array => [
                'type' => $meta->type,
                'key' => $meta->key,
                'value' => (float) $meta->value,
            ])
            ->all();

        $this->assertSame([
            ['type' => 'charge', 'key' => 'Shipping', 'value' => 15.0],
            ['type' => 'discount', 'key' => 'Promo', 'value' => 10.5],
        ], $metaSummary);
    }

    public function test_admin_without_route_specific_permission_is_forbidden_from_wholesale_write_routes(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
        ]);

        $wholesaler = $this->createWholesalerUser();
        $productId = $this->createProduct();
        $order = WholesalePurchaseOrder::query()->create([
            'order_id' => 'PO-FORBIDDEN-' . uniqid(),
            'wholeseller_id' => $wholesaler->id,
            'wholeseller_tier' => 'gold',
            'status' => 'pending',
            'final_price' => 99.00,
        ]);

        $this->post(route('admin.wholesale.business.order.assign-number'), [
            'order_id' => $order->id,
            'purchase_order_no' => 'PO-BLOCKED-' . uniqid(),
        ])->assertForbidden();

        $this->post(route('admin.wholesale.business.store-quotation'), [
            'quotation_no' => 'Q-BLOCKED-' . uniqid(),
            'wholesaler_id' => $wholesaler->id,
            'wholesale_tier' => 'gold',
            'final_price' => 100,
            'lang' => ['en'],
            'terms_and_conditions' => ['blocked terms'],
            'note' => ['blocked note'],
            'products' => [
                [
                    'product_id' => $productId,
                    'approved_quantity' => 1,
                    'price' => 100,
                    'final_price' => 100,
                    'tax' => '0%',
                ],
            ],
        ])->assertForbidden();
    }

    private function signInWholesaleAdmin(array $permissions): Admin
    {
        $guard = AdminPermissionRegistry::guard();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, $guard);
        }

        $role = Role::findOrCreate('Wholesale Test Role ' . uniqid(), $guard);
        $role->syncPermissions($permissions);

        $admin = Admin::query()->create([
            'name' => 'Wholesale Test Admin',
            'phone' => '1000000000',
            'email' => 'wholesale-admin-' . uniqid() . '@example.com',
            'password' => bcrypt('Password@123'),
            'status' => 1,
        ]);
        $admin->assignRole($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    private function createWholesalerUser(): User
    {
        $now = now();
        $id = DB::table('users')->insertGetId([
            'name' => 'Wholesale Customer',
            'f_name' => 'Wholesale',
            'l_name' => 'Customer',
            'phone' => '2010' . random_int(1000000, 9999999),
            'image' => 'def.png',
            'email' => 'wholesale-customer-' . uniqid() . '@example.com',
            'user_type' => 1,
            'password' => bcrypt('Password@123'),
            'is_active' => 1,
            'app_language' => 'en',
            'wholesaler_status' => 1,
            'wholesaler_discount' => 10.00,
            'tier' => 'gold',
            'moq_override_enabled' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return User::query()->findOrFail($id);
    }

    private function createProduct(): int
    {
        $now = now();

        return (int) DB::table('products')->insertGetId([
            'added_by' => 'admin',
            'user_id' => null,
            'shop_id' => null,
            'name' => 'Wholesale Quote Product ' . uniqid(),
            'slug' => 'wholesale-quote-product-' . uniqid(),
            'product_type' => 'physical',
            'branch_id' => 0,
            'min_qty' => 1,
            'refundable' => 1,
            'color_image' => '[]',
            'thumbnail' => 'test.png',
            'show_cms' => 0,
            'showcase_product' => 0,
            'variant_product' => 0,
            'published' => 1,
            'unit_price' => 100,
            'purchase_price' => 80,
            'tax' => '14.00',
            'tax_type' => 'percent',
            'tax_model' => 'exclude',
            'discount' => '0.00',
            'current_stock' => 10,
            'minimum_order_qty' => 1,
            'warranty_duration' => 12,
            'free_shipping' => 0,
            'status' => 1,
            'featured_status' => 1,
            'request_status' => 1,
            'is_warranty' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
