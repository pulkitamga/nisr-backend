<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BranchStockActionSecurityTest extends TestCase
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

    public function test_branch_manager_can_approve_transferred_stock_once(): void
    {
        $sourceBranchId = $this->createBranch('Approve-From');
        $destinationBranchId = $this->createBranch('Approve-To');
        $admin = $this->createAdmin($destinationBranchId, 'approve-manager');
        $categoryId = $this->createCategory();
        $productId = $this->createProduct($sourceBranchId);
        $transferId = $this->createTransfer($sourceBranchId, $destinationBranchId);
        $transferProductId = $this->createTransferProduct($transferId, $productId, $categoryId, 'transferred', 5);

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->from(route('admin.branch.stock.approvelist'))
            ->post(route('admin.branch.stock.approve', ['id' => $transferProductId]));

        $response->assertRedirect(route('admin.branch.stock.approvelist'));
        $response->assertSessionHas('success', translate('stock_approved_and_received_successfully'));

        $this->assertSame('approved', DB::table('stock_transfer_products')->where('id', $transferProductId)->value('status'));
        $this->assertNotNull(DB::table('stock_transfer_products')->where('id', $transferProductId)->value('approved_at'));
        $this->assertSame(1, DB::table('stock_received')->where('branch_id', $destinationBranchId)->where('product_id', $productId)->where('status', 'approved')->count());

        $duplicateResponse = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->from(route('admin.branch.stock.approvelist'))
            ->post(route('admin.branch.stock.approve', ['id' => $transferProductId]));

        $duplicateResponse->assertRedirect(route('admin.branch.stock.approvelist'));
        $duplicateResponse->assertSessionHas('error', translate('stock_transfer_has_already_been_processed'));
        $this->assertSame(1, DB::table('stock_received')->where('branch_id', $destinationBranchId)->where('product_id', $productId)->where('status', 'approved')->count());
    }

    public function test_branch_manager_cannot_approve_other_branch_transfer(): void
    {
        $sourceBranchId = $this->createBranch('Unauthorized-From');
        $destinationBranchId = $this->createBranch('Unauthorized-To');
        $otherBranchId = $this->createBranch('Unauthorized-Viewer');
        $admin = $this->createAdmin($otherBranchId, 'wrong-manager');
        $categoryId = $this->createCategory();
        $productId = $this->createProduct($sourceBranchId);
        $transferId = $this->createTransfer($sourceBranchId, $destinationBranchId);
        $transferProductId = $this->createTransferProduct($transferId, $productId, $categoryId, 'Transferred', 3);

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->from(route('admin.branch.stock.approvelist'))
            ->post(route('admin.branch.stock.approve', ['id' => $transferProductId]));

        $response->assertRedirect(route('admin.branch.stock.approvelist'));
        $response->assertSessionHas('error', translate('you_are_not_authorized_to_manage_this_stock_transfer'));

        $this->assertSame('Transferred', DB::table('stock_transfer_products')->where('id', $transferProductId)->value('status'));
        $this->assertSame(0, DB::table('stock_received')->where('branch_id', $destinationBranchId)->where('product_id', $productId)->count());
    }

    public function test_branch_manager_can_approve_legacy_transferred_status_rows(): void
    {
        $sourceBranchId = $this->createBranch('Legacy-From');
        $destinationBranchId = $this->createBranch('Legacy-To');
        $admin = $this->createAdmin($destinationBranchId, 'legacy-manager');
        $categoryId = $this->createCategory();
        $productId = $this->createProduct($sourceBranchId);
        $transferId = $this->createTransfer($sourceBranchId, $destinationBranchId);
        $transferProductId = $this->createTransferProduct($transferId, $productId, $categoryId, 'Transferred', 2);

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->from(route('admin.branch.stock.approvelist'))
            ->post(route('admin.branch.stock.approve', ['id' => $transferProductId]));

        $response->assertRedirect(route('admin.branch.stock.approvelist'));
        $response->assertSessionHas('success', translate('stock_approved_and_received_successfully'));

        $this->assertSame('approved', DB::table('stock_transfer_products')->where('id', $transferProductId)->value('status'));
    }

    public function test_branch_history_export_is_limited_to_the_users_branch(): void
    {
        $managerBranchId = $this->createBranch('Export-Manager');
        $otherBranchId = $this->createBranch('Export-Other');
        $admin = $this->createAdmin($managerBranchId, 'export-manager');
        $productId = $this->createProduct($otherBranchId);

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.export', [
                'product_id' => $productId,
                'branch_id' => $otherBranchId,
                'variation_type' => 'No Variation',
            ]));

        $response->assertRedirect(route('admin.branch.branch-stock-list'));
    }

    public function test_branch_stock_history_endpoint_is_limited_to_the_users_branch(): void
    {
        $managerBranchId = $this->createBranch('History-Manager');
        $otherBranchId = $this->createBranch('History-Other');
        $admin = $this->createAdmin($managerBranchId, 'history-manager');
        $otherProductId = $this->createProduct($otherBranchId);
        $ownProductId = $this->createProduct($managerBranchId);

        $this->createManageBranchProductStock($otherBranchId, $otherProductId, 7);
        $this->createManageBranchProductStock($managerBranchId, $ownProductId, 5);

        $forbiddenResponse = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->getJson(route('admin.branch.stock-history', [
                'branch_id' => $otherBranchId,
                'product_id' => $otherProductId,
                'variation_type' => 'No Variation',
                'variation_key' => 'No Variation',
            ]));

        $forbiddenResponse->assertStatus(403);

        $allowedResponse = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->getJson(route('admin.branch.stock-history', [
                'branch_id' => $managerBranchId,
                'product_id' => $ownProductId,
                'variation_type' => 'No Variation',
                'variation_key' => 'No Variation',
            ]));

        $allowedResponse->assertOk();
        $allowedResponse->assertJsonStructure([
            'branch_name',
            'product_name',
            'variation_label',
            'current_stock',
            'history',
            'export_url',
        ]);
    }

    public function test_vendor_view_is_limited_to_the_managers_branch(): void
    {
        $managerBranchId = $this->createBranch('Vendor-Manager');
        $otherSellerId = $this->createSeller('other-seller');
        $otherBranchId = $this->createBranch('Vendor-Other', $otherSellerId);
        $admin = $this->createAdmin($managerBranchId, 'vendor-manager');

        $response = $this->withoutMiddleware()
            ->actingAs($admin, 'admin')
            ->get(route('admin.branch.vendors.view', ['id' => $otherSellerId]));

        $response->assertRedirect(route('admin.branch.vendors'));
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

    private function createProduct(int $branchId): int
    {
        return (int) DB::table('products')->insertGetId([
            'added_by' => 'admin',
            'name' => 'Branch Test Product ' . uniqid(),
            'slug' => 'branch-test-product-' . uniqid(),
            'product_type' => 'physical',
            'branch_id' => $branchId,
            'color_image' => '',
            'variation' => json_encode([]),
            'current_stock' => 10,
            'unit_price' => 100,
            'purchase_price' => 80,
            'tax' => '0.00',
            'discount' => '0.00',
            'status' => 1,
            'featured_status' => 1,
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
}
