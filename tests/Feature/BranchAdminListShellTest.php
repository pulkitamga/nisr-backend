<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class BranchAdminListShellTest extends TestCase
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

    public function test_branch_list_renders_crm_toolbar_and_export_contract(): void
    {
        $branchId = $this->createBranch('North Branch');
        $admin = $this->createAdmin($branchId, 'branch-shell-admin');

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.branch-list', [
                'searchValue' => 'North',
                'choose_first' => 25,
            ]));

        $response->assertOk();
        $response->assertSee('id="branch-list-toolbar"', false);
        $response->assertSee('data-crm-export-button="true"', false);
        $response->assertSee(route('admin.branch.export'), false);
        $response->assertSee('North Branch', false);
        $response->assertSee('crm-primary-link', false);
        $response->assertSee('crm-row-actions__menu', false);
    }

    public function test_branch_stock_list_renders_crm_toolbar_and_filters_server_side(): void
    {
        $branchId = $this->createBranch('Warehouse Branch');
        $admin = $this->createAdmin($branchId, 'branch-stock-admin');
        $matchingProductId = $this->createProduct($branchId, 'Rotor Filter', 'RF-100');
        $otherProductId = $this->createProduct($branchId, 'Brake Pad', 'BP-200');

        $this->createManageBranchProductStock($branchId, $matchingProductId, 12);
        $this->createManageBranchProductStock($branchId, $otherProductId, 5);

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.branch-stock-list', [
                'searchValue' => 'Rotor',
                'choose_first' => 10,
            ]));

        $response->assertOk();
        $response->assertSee('id="branch-stock-toolbar"', false);
        $response->assertSee(route('admin.branch.export', ['export_scope' => 'branch_stock']), false);
        $response->assertSee(route('admin.branch.stock-history'), false);
        $response->assertSee('crm-primary-link', false);
        $response->assertSee('Rotor Filter', false);
        $response->assertSee('Warehouse Branch', false);
        $response->assertDontSee('data-history=', false);
        $response->assertDontSee('>5<', false);
    }

    public function test_branch_inventory_search_filters_products_and_shows_export_button(): void
    {
        $branchId = $this->createBranch('Inventory Branch');
        $admin = $this->createAdmin($branchId, 'inventory-admin');
        $this->createProduct($branchId, 'Starter Motor', 'SM-001');
        $this->createProduct($branchId, 'Radiator Cap', 'RC-002');

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.product-inventory', [
                'searchValue' => 'Starter',
                'choose_first' => 5,
            ]));

        $response->assertOk();
        $response->assertSee('id="branch-inventory-toolbar"', false);
        $response->assertSee(route('admin.branch.product-inventory.export'), false);
        $response->assertSee('Branch Product Inventory', false);
        $response->assertSee('crm-primary-link', false);
        $response->assertSee('Starter Motor', false);
        $response->assertDontSee('Radiator Cap', false);
    }

    public function test_branch_inventory_choose_first_and_export_follow_active_filters(): void
    {
        $branchId = $this->createBranch('Inventory Page Branch');
        $admin = $this->createAdmin($branchId, 'inventory-page-admin');
        $olderProductId = $this->createProduct($branchId, 'Alternator', 'ALT-001');
        $newerProductId = $this->createProduct($branchId, 'Fuel Pump', 'FP-002');

        $pageResponse = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.product-inventory', [
                'choose_first' => 1,
            ]));

        $pageResponse->assertOk();
        $pageResponse->assertSee('Fuel Pump', false);
        $pageResponse->assertDontSee('Alternator', false);

        $exportResponse = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.product-inventory.export', [
                'searchValue' => 'Fuel',
            ]));

        $exportResponse->assertOk();
        $exportContent = $exportResponse->streamedContent();
        $this->assertStringContainsString('Fuel Pump', $exportContent);
        $this->assertStringNotContainsString('Alternator', $exportContent);
    }

    public function test_branch_vendor_list_searches_vendors_and_does_not_render_product_rows(): void
    {
        $sellerId = $this->createSeller('alpha-vendor');
        $branchId = $this->createBranch('Vendor Branch', $sellerId);
        $admin = $this->createAdmin($branchId, 'vendor-admin');

        $otherSellerId = $this->createSeller('beta-vendor');
        $otherBranchId = $this->createBranch('Other Branch', $otherSellerId);
        $this->createProduct($branchId, 'Inventory Only Product', 'INV-123', $sellerId);
        $this->createProduct($otherBranchId, 'Other Branch Product', 'INV-456', $otherSellerId);

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.vendors', [
                'searchValue' => 'alpha-vendor',
                'choose_first' => 5,
            ]));

        $response->assertOk();
        $response->assertSee('id="branch-vendors-toolbar"', false);
        $response->assertSee(route('admin.branch.vendors.export'), false);
        $response->assertSee('Branch Vendors', false);
        $response->assertSee('alpha-vendor', false);
        $response->assertSee('crm-primary-link', false);
        $response->assertDontSee('Inventory Only Product', false);
        $response->assertDontSee('beta-vendor', false);
    }

    public function test_branch_vendor_export_follows_active_search(): void
    {
        $sellerId = $this->createSeller('gamma-vendor');
        $branchId = $this->createBranch('Gamma Vendor Branch', $sellerId);
        $admin = $this->createAdmin($branchId, 'vendor-export-admin');

        $otherSellerId = $this->createSeller('delta-vendor');
        $this->createBranch('Delta Vendor Branch', $otherSellerId);

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.vendors.export', [
                'searchValue' => 'gamma-vendor',
            ]));

        $response->assertOk();
        $exportContent = $response->streamedContent();
        $this->assertStringContainsString('gamma-vendor', $exportContent);
        $this->assertStringNotContainsString('delta-vendor', $exportContent);
    }

    public function test_stock_request_transfer_and_received_lists_render_crm_shell_exports(): void
    {
        $fromBranchId = $this->createBranch('From Branch');
        $toBranchId = $this->createBranch('To Branch');
        $admin = $this->createAdmin($toBranchId, 'stock-pages-admin');
        $categoryId = $this->createCategory();
        $productId = $this->createProduct($fromBranchId, 'Transfer Pump', 'TP-001');
        $otherProductId = $this->createProduct($fromBranchId, 'Brake Disc', 'BD-404');

        $stockRequestId = $this->createStockRequest($fromBranchId);
        $this->createStockRequestProduct($stockRequestId, $productId, $categoryId, 3);
        $this->createStockRequestProduct($stockRequestId, $otherProductId, $categoryId, 1);

        $stockTransferId = $this->createTransfer($fromBranchId, $toBranchId);
        $this->createTransferProduct($stockTransferId, $productId, $categoryId, 'pending', 4);
        $this->createTransferProduct($stockTransferId, $otherProductId, $categoryId, 'pending', 2);

        $requestResponse = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.stock-request.list', ['searchValue' => 'Pump']));

        $requestResponse->assertOk();
        $requestResponse->assertSee('id="stock-request-toolbar"', false);
        $requestResponse->assertSee(route('admin.stock-request.export'), false);
        $requestResponse->assertSee('Transfer Pump', false);
        $requestResponse->assertSee('crm-primary-link', false);

        $transferResponse = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.stock-transfer.list', ['searchValue' => 'Pump']));

        $transferResponse->assertOk();
        $transferResponse->assertSee('id="stock-transfer-toolbar"', false);
        $transferResponse->assertSee(route('admin.stock-transfer.export'), false);
        $transferResponse->assertSee('Transfer Pump', false);
        $transferResponse->assertSee('crm-primary-link', false);
        $transferResponse->assertSee('crm-row-actions__menu', false);

        $receivedResponse = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.stock.received', ['searchValue' => 'Pump']));

        $receivedResponse->assertOk();
        $receivedResponse->assertSee('id="branch-received-toolbar"', false);
        $receivedResponse->assertSee(route('admin.branch.stock.received.export'), false);
        $receivedResponse->assertSee('Transfer Pump', false);
        $receivedResponse->assertSee('crm-primary-link', false);

        $requestExportResponse = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.stock-request.export', ['searchValue' => 'Pump']));

        $requestExportResponse->assertOk();
        $requestExportContent = $requestExportResponse->streamedContent();
        $this->assertStringContainsString('Transfer Pump', $requestExportContent);
        $this->assertStringNotContainsString('Brake Disc', $requestExportContent);

        $transferExportResponse = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.stock-transfer.export', ['searchValue' => 'Pump']));

        $transferExportResponse->assertOk();
        $transferExportContent = $transferExportResponse->streamedContent();
        $this->assertStringContainsString('Transfer Pump', $transferExportContent);
        $this->assertStringNotContainsString('Brake Disc', $transferExportContent);

        $receivedExportResponse = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.stock.received.export', ['searchValue' => 'Pump']));

        $receivedExportResponse->assertOk();
        $receivedExportContent = $receivedExportResponse->streamedContent();
        $this->assertStringContainsString('Transfer Pump', $receivedExportContent);
        $this->assertStringNotContainsString('Brake Disc', $receivedExportContent);
    }

    public function test_branch_stock_approval_page_uses_shared_assets_and_no_bootstrap_cdn(): void
    {
        $fromBranchId = $this->createBranch('Approve Asset From');
        $toBranchId = $this->createBranch('Approve Asset To');
        $admin = $this->createAdmin($toBranchId, 'approve-assets-admin');
        $categoryId = $this->createCategory();
        $productId = $this->createProduct($fromBranchId, 'Approval Rotor', 'AR-001');
        $transferId = $this->createTransfer($fromBranchId, $toBranchId);
        $this->createTransferProduct($transferId, $productId, $categoryId, 'pending', 2);

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.stock.approvelist'));

        $response->assertOk();
        $response->assertSee('Pending Stock Approvals', false);
        $response->assertSee('branch-stock-approval.js', false);
        $response->assertSee('crm-row-actions__menu', false);
        $response->assertDontSee('cdn.jsdelivr.net/npm/bootstrap', false);
    }

    public function test_branch_stock_transfer_report_uses_shared_report_module(): void
    {
        $branchId = $this->createBranch('Report Branch');
        $admin = $this->createAdmin($branchId, 'report-branch-admin');

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.stock.transfer-report'));

        $response->assertOk();
        $response->assertSee('branch-stock-transfer-report.js', false);
        $response->assertSee('window.branchStockTransferReportConfig', false);
        $response->assertDontSee('const loadReport = async () =>', false);
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

    private function createSeller(string $prefix): int
    {
        return (int) DB::table('sellers')->insertGetId([
            'f_name' => ucfirst($prefix),
            'l_name' => 'Test',
            'email' => $prefix . '-' . uniqid() . '@example.com',
            'phone' => '+2010' . random_int(10000000, 99999999),
            'password' => bcrypt('password'),
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createBranch(string $name, int $sellerId = 1): int
    {
        if ($sellerId === 1 && DB::table('sellers')->where('id', 1)->doesntExist()) {
            $sellerId = $this->createSeller('default-seller');
        }

        return (int) DB::table('branches')->insertGetId([
            'vendor_id' => $sellerId,
            'branch_name' => $name . '-' . uniqid(),
            'branch_state' => 'Test',
            'status' => 'active',
            'email' => strtolower(str_replace(' ', '-', $name)) . '@example.com',
            'phone' => '+2011' . random_int(10000000, 99999999),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCategory(): int
    {
        return (int) DB::table('categories')->insertGetId([
            'name' => 'Branch Test Category ' . uniqid(),
            'slug' => 'branch-test-category-' . uniqid(),
            'parent_id' => 0,
            'position' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createProduct(int $branchId, string $name, string $code, int $sellerId = 1): int
    {
        if ($sellerId === 1 && DB::table('sellers')->where('id', 1)->doesntExist()) {
            $sellerId = $this->createSeller('product-seller');
        }

        return (int) DB::table('products')->insertGetId([
            'added_by' => 'admin',
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
            'product_type' => 'physical',
            'branch_id' => $branchId,
            'user_id' => $sellerId,
            'code' => $code,
            'color_image' => '',
            'variation' => json_encode([]),
            'current_stock' => 10,
            'unit_price' => 100,
            'purchase_price' => 80,
            'tax' => '0.00',
            'discount' => '0.00',
            'status' => 1,
            'featured_status' => 1,
            'request_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createManageBranchProductStock(int $branchId, int $productId, int $stock): int
    {
        return (int) DB::table('manage_branch_product_stock')->insertGetId([
            'branch_id' => $branchId,
            'product_id' => $productId,
            'current_stock' => $stock,
            'variation_type' => null,
            'variation_key' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createStockRequest(int $fromBranchId): int
    {
        return (int) DB::table('stock_requests')->insertGetId([
            'from_branch_id' => $fromBranchId,
            'transfer_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createStockRequestProduct(int $requestId, int $productId, int $categoryId, int $quantity): int
    {
        return (int) DB::table('stock_request_products')->insertGetId([
            'stock_requests_id' => $requestId,
            'category_id' => $categoryId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTransfer(int $fromBranchId, int $toBranchId): int
    {
        return (int) DB::table('stock_transfers')->insertGetId([
            'from_branch_id' => $fromBranchId,
            'to_branch_id' => $toBranchId,
            'transfer_date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTransferProduct(int $transferId, int $productId, int $categoryId, string $status, int $quantity): int
    {
        return (int) DB::table('stock_transfer_products')->insertGetId([
            'stock_transfers_id' => $transferId,
            'category_id' => $categoryId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
