<?php

namespace Tests\Feature;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Models\Admin;
use App\Models\QuotationMeta;
use App\Models\WholesaleConfirmOrder;
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
            $mock->shouldReceive('getForEmployee')
                ->andReturn(new Collection());
            $mock->shouldReceive('getForUser')
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

    public function test_create_quotation_page_renders_structured_builder_sections_and_sticky_submit_bar(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
            'wholesaler_section.create_quotation',
        ]);

        $wholesaler = $this->createWholesalerUser();
        $this->createWholesalerBusiness($wholesaler->id, [
            'company_name' => 'Builder Wholesale Company',
        ]);

        $categoryId = $this->createCategory();
        $subCategoryId = $this->createCategory($categoryId);
        $productId = $this->createProduct();
        $this->createWholesaleProduct($productId, $categoryId, $subCategoryId);

        $response = $this->get(route('admin.wholesale.business.create-quotation'));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('wholesale-builder-shell', $html);
        $this->assertStringContainsString('id="sticky-submit-bar"', $html);
        $this->assertStringContainsString('id="summary-selected-wholesaler"', $html);
        $this->assertStringContainsString('id="builder-quotation-number"', $html);
        $this->assertStringContainsString(translate('Quotation setup'), $html);
        $this->assertStringContainsString(translate('Final Summary'), $html);
        $this->assertStringContainsString('wholesaleBuilderConfig', $html);
        $this->assertStringContainsString('wholesale-builder.js', $html);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertSame(1, substr_count($html, 'id="quotation-form"'));
    }

    public function test_order_view_renders_structured_builder_sections_with_single_form(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
            'wholesaler_section.purchase_request_view',
        ]);

        $wholesaler = $this->createWholesalerUser();
        $this->createWholesalerBusiness($wholesaler->id, [
            'company_name' => 'Builder Order Company',
        ]);

        $productId = $this->createProduct();
        $order = WholesalePurchaseOrder::query()->create([
            'order_id' => 'PO-BUILDER-' . uniqid(),
            'purchase_order_no' => 'PO-BUILDER-NO-' . uniqid(),
            'wholeseller_id' => $wholesaler->id,
            'wholeseller_tier' => 'gold',
            'status' => 'processed',
            'final_price' => 228.00,
        ]);

        DB::table('wholesale_purchase_order_items')->insert([
            'wholesale_order_id' => $order->id,
            'product_id' => $productId,
            'product_quantity' => 2,
            'product_variation_type' => 'Default',
            'base_price' => 100,
            'tax' => '14',
            'final_price' => 228,
            'price_range_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('admin.wholesale.business.order.view', $order->id));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('wholesale-builder-shell', $html);
        $this->assertStringContainsString('id="sticky-submit-bar"', $html);
        $this->assertStringContainsString('id="builder-order-number"', $html);
        $this->assertStringContainsString('wholesale-builder-language-block', $html);
        $this->assertStringContainsString('id="builder-final-total"', $html);
        $this->assertStringContainsString(route('admin.wholesale.business.orders.approve', $order->id), $html);
        $this->assertStringContainsString(translate('Final Summary'), $html);
        $this->assertStringContainsString('wholesaleBuilderConfig', $html);
        $this->assertStringContainsString('wholesale-builder.js', $html);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertSame(1, substr_count($html, 'id="quotation-form"'));
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

    public function test_wholesaler_list_renders_crm_toolbar_and_primary_company_link(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
            'wholesaler_section.wholesaler_view',
        ]);

        $wholesaler = $this->createWholesalerUser();
        $businessId = $this->createWholesalerBusiness($wholesaler->id, [
            'company_name' => 'Toolbar Wholesale Company',
        ]);

        $response = $this->get(route('admin.wholesale.business.list', [
            'choose_first' => 50,
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('id="wholesale-wholesalers-toolbar"', $html);
        $this->assertStringContainsString('data-crm-export-button="true"', $html);
        $this->assertStringContainsString(route('admin.wholesale.business.wholesaler.profile', $businessId), $html);
        $this->assertStringContainsString('Toolbar Wholesale Company', $html);
        $this->assertStringContainsString('wholesaleListConfig', $html);
        $this->assertStringContainsString('wholesale-list.js', $html);
    }

    public function test_wholesaler_join_request_list_renders_crm_toolbar_and_primary_company_link(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
            'wholesaler_section.wholesaler_join_request',
        ]);

        $wholesaler = $this->createWholesalerUser();
        $wholesaler->forceFill([
            'wholesaler_status' => 0,
            'tier' => null,
        ])->save();

        $businessId = $this->createWholesalerBusiness($wholesaler->id, [
            'company_name' => 'Pending Wholesale Company',
            'register_copy' => 'register-proof.png',
            'tax_card_copy' => 'tax-proof.png',
            'vat_register_copy' => 'vat-proof.png',
        ]);

        $response = $this->get(route('admin.wholesale.business.request', [
            'choose_first' => 50,
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('id="wholesale-request-toolbar"', $html);
        $this->assertStringContainsString('data-crm-export-button="true"', $html);
        $this->assertStringContainsString(route('admin.wholesale.business.wholesaler.profile', $businessId), $html);
        $this->assertStringContainsString('Pending Wholesale Company', $html);
        $this->assertStringContainsString('approvalReviewModal' . $wholesaler->id, $html);
        $this->assertStringContainsString(translate('Business summary'), $html);
        $this->assertStringContainsString(translate('Available documents'), $html);
        $this->assertStringContainsString(translate('Pending setup'), $html);
        $this->assertStringContainsString(translate('Approve'), $html);
        $this->assertStringContainsString(translate('Reject'), $html);
        $this->assertStringNotContainsString('swal-approve-btn', $html);
        $this->assertStringContainsString('wholesaleListConfig', $html);
        $this->assertStringContainsString('wholesale-list.js', $html);
    }

    public function test_purchase_request_list_renders_crm_toolbar_export_and_primary_company_link(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
            'wholesaler_section.purchase_request_view',
        ]);

        $wholesaler = $this->createWholesalerUser();
        $this->createWholesalerBusiness($wholesaler->id, [
            'company_name' => 'Purchase Request Company',
        ]);

        $order = WholesalePurchaseOrder::query()->create([
            'order_id' => 'PO-LIST-' . uniqid(),
            'purchase_order_no' => 'PO-REQUEST-' . uniqid(),
            'wholeseller_id' => $wholesaler->id,
            'wholeseller_tier' => 'gold',
            'status' => 'processed',
            'final_price' => 150.00,
        ]);

        $response = $this->get(route('admin.wholesale.business.order.request', [
            'choose_first' => 50,
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('id="wholesale-purchase-toolbar"', $html);
        $this->assertStringContainsString('data-crm-export-button="true"', $html);
        $this->assertStringContainsString(route('admin.wholesale.business.order.view', $order->id), $html);
        $this->assertStringContainsString('Purchase Request Company', $html);
        $this->assertStringContainsString('crm-row-actions__toggle', $html);
        $this->assertStringContainsString(translate('Assign Purchase Order No'), $html);
        $this->assertStringContainsString(translate('History'), $html);
        $this->assertStringContainsString('wholesaleListConfig', $html);
        $this->assertStringContainsString('wholesale-list.js', $html);
        $this->assertStringNotContainsString('confirmAndDelete(', $html);
    }

    public function test_quotation_list_renders_crm_toolbar_export_and_primary_company_link(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
            'wholesaler_section.quotation_view',
        ]);

        $wholesaler = $this->createWholesalerUser();
        $this->createWholesalerBusiness($wholesaler->id, [
            'company_name' => 'Quotation Company',
        ]);

        $order = WholesaleQuotation::query()->create([
            'order_id' => 'PO-QUOTE-' . uniqid(),
            'purchase_order_no' => 'PO-QUOTE-NO-' . uniqid(),
            'quotation_no' => 'Q-' . uniqid(),
            'wholeseller_id' => $wholesaler->id,
            'wholeseller_tier' => 'gold',
            'status' => 'sent',
            'final_price' => 220.00,
        ]);

        $response = $this->get(route('admin.wholesale.business.wholesale.order', [
            'choose_first' => 50,
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('id="wholesale-quotation-toolbar"', $html);
        $this->assertStringContainsString('data-crm-export-button="true"', $html);
        $this->assertStringContainsString('crm-primary-link', $html);
        $this->assertStringContainsString('Quotation Company', $html);
        $this->assertStringContainsString('crm-row-actions__toggle', $html);
        $this->assertStringContainsString(translate('History'), $html);
        $this->assertStringContainsString(translate('Delete'), $html);
        $this->assertStringContainsString('wholesaleListConfig', $html);
        $this->assertStringContainsString('wholesale-list.js', $html);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringNotContainsString('confirmAndDelete(', $html);
    }

    public function test_confirmed_order_list_renders_crm_toolbar_export_and_primary_company_link(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
            'wholesaler_section.confirme_order_view',
        ]);

        $wholesaler = $this->createWholesalerUser();
        $this->createWholesalerBusiness($wholesaler->id, [
            'company_name' => 'Confirmed Order Company',
        ]);

        $order = WholesaleConfirmOrder::query()->create([
            'order_id' => 'PO-CONFIRMED-' . uniqid(),
            'purchase_order_no' => 'PO-CONF-' . uniqid(),
            'quotation_no' => 'CQ-' . uniqid(),
            'confirm_order_no' => 'CONF-' . uniqid(),
            'invoice_no' => 'INV-' . uniqid(),
            'wholesaler_id' => $wholesaler->id,
            'status' => 'accepted',
            'payment_status' => 'paid',
            'delivery_status' => 'delivered',
            'confirmed_at' => now(),
            'final_price' => 300.00,
        ]);

        $response = $this->get(route('admin.wholesale.business.wholesale.confirmedorder', [
            'choose_first' => 50,
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('id="wholesale-confirmed-toolbar"', $html);
        $this->assertStringContainsString('data-crm-export-button="true"', $html);
        $this->assertStringContainsString('crm-primary-link', $html);
        $this->assertStringContainsString('Confirmed Order Company', $html);
        $this->assertStringContainsString('crm-row-actions__toggle', $html);
        $this->assertStringContainsString(translate('Payment'), $html);
        $this->assertStringContainsString(translate('History'), $html);
        $this->assertStringNotContainsString('action-popup', $html);
        $this->assertStringContainsString('wholesaleListConfig', $html);
        $this->assertStringContainsString('wholesale-list.js', $html);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringNotContainsString('confirmAndDelete(', $html);
    }

    public function test_wholesale_product_list_renders_crm_toolbar_export_and_primary_product_link(): void
    {
        $this->signInWholesaleAdmin([
            'wholesaler_section.access',
            'wholesaler_section.product_list',
        ]);

        $categoryId = $this->createCategory();
        $subCategoryId = $this->createCategory($categoryId);
        $productId = $this->createProduct();
        $wholesaleProductId = $this->createWholesaleProduct($productId, $categoryId, $subCategoryId);

        $response = $this->get(route('admin.wholesale.product.list', [
            'choose_first' => 50,
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('id="wholesale-product-toolbar"', $html);
        $this->assertStringContainsString('data-crm-export-button="true"', $html);
        $this->assertStringContainsString(route('admin.wholesale.product.view', $wholesaleProductId), $html);
        $this->assertStringContainsString('Wholesale Quote Product', $html);
        $this->assertStringContainsString('crm-row-actions__toggle', $html);
        $this->assertStringContainsString(translate('delete'), $html);
        $this->assertStringContainsString('wholesaleListConfig', $html);
        $this->assertStringContainsString('wholesale-list.js', $html);
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

    private function createWholesalerBusiness(int $wholesalerId, array $overrides = []): int
    {
        $now = now();

        return (int) DB::table('wholesaler_businesses')->insertGetId(array_merge([
            'wholesaler_id' => $wholesalerId,
            'company_name' => 'Wholesale Business ' . uniqid(),
            'trade_name' => 'Trade ' . uniqid(),
            'registration_number' => 'REG-' . uniqid(),
            'tax_id' => 'TAX-' . uniqid(),
            'vat_number' => 'VAT-' . uniqid(),
            'created_at' => $now,
            'updated_at' => $now,
        ], $overrides));
    }

    private function createCategory(int $parentId = 0): int
    {
        return (int) DB::table('categories')->insertGetId([
            'name' => 'Wholesale Category ' . uniqid(),
            'slug' => 'wholesale-category-' . uniqid(),
            'parent_id' => $parentId,
            'position' => $parentId > 0 ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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

    private function createWholesaleProduct(int $productId, int $categoryId, int $subCategoryId): int
    {
        return (int) DB::table('wholesale_products')->insertGetId([
            'product_id' => $productId,
            'category_id' => $categoryId,
            'sub_category_id' => $subCategoryId,
            'variation_type' => 'Default',
            'variation_key' => 'variant:Default',
            'status' => 1,
            'deleted_at' => null,
        ]);
    }
}
