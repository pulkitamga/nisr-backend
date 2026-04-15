<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductStockTransaction;
use App\Services\InventoryMutationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventoryMutationServiceTest extends TestCase
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

    public function test_manual_adjust_updates_branch_ledger_and_product_mirrors(): void
    {
        $branchId = $this->createBranch('ManualAdjust-Branch');
        $fixture = $this->seedProductStock(qty: 10, variant: 'Left', branchId: $branchId);
        $service = new InventoryMutationService();

        $decrease = $service->manualAdjust(
            productId: $fixture['product_id'],
            branchId: $branchId,
            variant: 'Left',
            delta: -3,
            note: 'audit count'
        );
        $this->assertTrue($decrease['status'], $decrease['message'] ?? 'Manual decrease failed');

        $this->assertSame(7, (int)DB::table('manage_branch_product_stock')->where('id', $fixture['branch_stock_id'])->value('current_stock'));
        $this->assertSame(7, (int)DB::table('product_stocks')->where('id', $fixture['product_stock_id'])->value('qty'));
        $this->assertSame(7, (int)DB::table('products')->where('id', $fixture['product_id'])->value('current_stock'));
        $this->assertSame(7, $this->getProductVariationQty($fixture['product_id'], 'Left'));

        $increase = $service->manualAdjust(
            productId: $fixture['product_id'],
            branchId: $branchId,
            variant: 'Left',
            delta: 2,
            note: 'count correction'
        );
        $this->assertTrue($increase['status'], $increase['message'] ?? 'Manual increase failed');

        $this->assertSame(9, (int)DB::table('manage_branch_product_stock')->where('id', $fixture['branch_stock_id'])->value('current_stock'));
        $this->assertSame(9, (int)DB::table('product_stocks')->where('id', $fixture['product_stock_id'])->value('qty'));
        $this->assertSame(9, (int)DB::table('products')->where('id', $fixture['product_id'])->value('current_stock'));
        $this->assertSame(9, $this->getProductVariationQty($fixture['product_id'], 'Left'));

        $this->assertSame(2, DB::table('product_stock_transactions')->where('product_stock_id', $fixture['product_stock_id'])->count());
        $this->assertSame(1, DB::table('product_stock_transactions')->where('product_stock_id', $fixture['product_stock_id'])->where('type', 'OUT')->count());
        $this->assertSame(1, DB::table('product_stock_transactions')->where('product_stock_id', $fixture['product_stock_id'])->where('type', 'IN')->count());
    }

    public function test_transfer_between_branches_moves_branch_stock_with_no_global_drift(): void
    {
        $fromBranchId = $this->createBranch('Transfer-From');
        $toBranchId = $this->createBranch('Transfer-To');
        $fixture = $this->seedProductStock(qty: 10, variant: 'Right', branchId: $fromBranchId);
        $service = new InventoryMutationService();

        $result = $service->transferBetweenBranches(
            productId: $fixture['product_id'],
            qty: 4,
            fromBranchId: $fromBranchId,
            toBranchId: $toBranchId,
            variant: 'Right',
            referenceId: 9001
        );
        $this->assertTrue($result['status'], $result['message'] ?? 'Branch transfer failed');

        $this->assertSame(6, (int)DB::table('manage_branch_product_stock')
            ->where('product_id', $fixture['product_id'])
            ->where('branch_id', $fromBranchId)
            ->where('variation_type', 'Right')
            ->value('current_stock'));

        $this->assertSame(4, (int)DB::table('manage_branch_product_stock')
            ->where('product_id', $fixture['product_id'])
            ->where('branch_id', $toBranchId)
            ->where('variation_type', 'Right')
            ->value('current_stock'));

        // Global stock should stay unchanged after transfer out + in.
        $this->assertSame(10, (int)DB::table('product_stocks')->where('id', $fixture['product_stock_id'])->value('qty'));
        $this->assertSame(10, (int)DB::table('products')->where('id', $fixture['product_id'])->value('current_stock'));
        $this->assertSame(10, $this->getProductVariationQty($fixture['product_id'], 'Right'));
    }

    public function test_delete_for_product_removes_transactions_via_product_stock_ids(): void
    {
        $branchId = $this->createBranch('DeleteFlow-Branch');
        $fixture = $this->seedProductStock(qty: 10, variant: 'DeleteMe', branchId: $branchId);

        DB::table('product_stock_transactions')->insert([
            [
                'product_stock_id' => $fixture['product_stock_id'],
                'type' => 'IN',
                'quantity' => 10,
                'reason' => 'initial_stock',
                'remarks' => 'seeded for deletion test',
                'from_branch_id' => null,
                'to_branch_id' => $branchId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_stock_id' => $fixture['product_stock_id'],
                'type' => 'OUT',
                'quantity' => 2,
                'reason' => 'manual_adjustment',
                'remarks' => 'seeded for deletion test',
                'from_branch_id' => $branchId,
                'to_branch_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->assertSame(2, DB::table('product_stock_transactions')->where('product_stock_id', $fixture['product_stock_id'])->count());

        ProductStockTransaction::deleteForProduct($fixture['product_id']);

        $this->assertSame(0, DB::table('product_stock_transactions')->where('product_stock_id', $fixture['product_stock_id'])->count());
        $this->assertSame(1, DB::table('product_stocks')->where('id', $fixture['product_stock_id'])->count());
    }

    public function test_seed_initial_physical_inventory_creates_creation_time_stock_mirrors(): void
    {
        $branchId = $this->createBranch('SeedInit-Branch');
        $now = now();
        $productId = (int) DB::table('products')->insertGetId([
            'added_by' => 'admin',
            'name' => 'Seed Init Product ' . uniqid(),
            'slug' => 'seed-init-' . uniqid(),
            'product_type' => 'physical',
            'branch_id' => $branchId,
            'code' => 'SEED-' . uniqid(),
            'color_image' => '',
            'variation' => json_encode([
                [
                    'type' => 'Left',
                    'qty' => 4,
                    'sku' => 'LEFT-' . uniqid(),
                    'price' => 120,
                ],
                [
                    'type' => 'Right',
                    'qty' => 6,
                    'sku' => 'RIGHT-' . uniqid(),
                    'price' => 130,
                ],
            ]),
            'current_stock' => 10,
            'unit_price' => 100,
            'purchase_price' => 80,
            'tax' => '0.00',
            'discount' => '0.00',
            'status' => 1,
            'featured_status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $product = Product::query()->findOrFail($productId);
        $service = new InventoryMutationService();

        $response = $service->seedInitialPhysicalInventory($product, $branchId);

        $this->assertTrue($response['status'], $response['message'] ?? 'Initial inventory seed failed');
        $this->assertSame(2, DB::table('product_stocks')->where('product_id', $productId)->count());
        $this->assertSame(4, (int) DB::table('product_stocks')->where('product_id', $productId)->where('variant', 'Left')->value('qty'));
        $this->assertSame(6, (int) DB::table('product_stocks')->where('product_id', $productId)->where('variant', 'Right')->value('qty'));

        $this->assertSame(4, $this->getBranchStockQuantity($productId, $branchId, 'Left'));
        $this->assertSame(6, $this->getBranchStockQuantity($productId, $branchId, 'Right'));

        $this->assertSame(2, DB::table('product_stock_transactions')->whereIn(
            'product_stock_id',
            DB::table('product_stocks')->where('product_id', $productId)->pluck('id')
        )->count());
    }

    public function test_order_status_delivered_then_returned_is_reversible_for_stock_and_flags(): void
    {
        $branchId = $this->createBranch('OrderFlow-Branch');
        $fixture = $this->seedProductStock(qty: 10, variant: 'Left', branchId: $branchId);

        $orderId = (int)DB::table('orders')->insertGetId([
            'transfer_from_branch' => $branchId,
            'pickup_from_branch' => 0,
            'order_status' => 'pending',
            'payment_status' => 'unpaid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_details')->insert([
            'order_id' => $orderId,
            'product_id' => $fixture['product_id'],
            'seller_id' => 1,
            'qty' => 2,
            'price' => 100,
            'tax' => 0,
            'discount' => 0,
            'variant' => 'Left',
            'delivery_status' => 'pending',
            'is_stock_decreased' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new InventoryMutationService();

        $delivered = $service->decreaseForOrderById($orderId);
        $this->assertTrue($delivered['status'], $delivered['message'] ?? 'Delivered transition failed');

        $detailAfterDelivered = DB::table('order_details')->where('order_id', $orderId)->first();
        $this->assertSame(1, (int)$detailAfterDelivered->is_stock_decreased);
        $this->assertSame('delivered', $detailAfterDelivered->delivery_status);
        $this->assertSame(8, (int)DB::table('manage_branch_product_stock')->where('id', $fixture['branch_stock_id'])->value('current_stock'));
        $this->assertSame(8, (int)DB::table('product_stocks')->where('id', $fixture['product_stock_id'])->value('qty'));
        $this->assertSame(8, (int)DB::table('products')->where('id', $fixture['product_id'])->value('current_stock'));
        $this->assertSame(8, $this->getProductVariationQty($fixture['product_id'], 'Left'));

        $returned = $service->increaseForReturnOrCancelById($orderId, 'returned');
        $this->assertTrue($returned['status'], $returned['message'] ?? 'Returned transition failed');

        $detailAfterReturned = DB::table('order_details')->where('order_id', $orderId)->first();
        $this->assertSame(0, (int)$detailAfterReturned->is_stock_decreased);
        $this->assertSame('returned', $detailAfterReturned->delivery_status);
        $this->assertSame(10, (int)DB::table('manage_branch_product_stock')->where('id', $fixture['branch_stock_id'])->value('current_stock'));
        $this->assertSame(10, (int)DB::table('product_stocks')->where('id', $fixture['product_stock_id'])->value('qty'));
        $this->assertSame(10, (int)DB::table('products')->where('id', $fixture['product_id'])->value('current_stock'));
        $this->assertSame(10, $this->getProductVariationQty($fixture['product_id'], 'Left'));
    }

    private function createBranch(string $name): int
    {
        return (int)DB::table('branches')->insertGetId([
            'vendor_id' => 1,
            'branch_name' => $name . '-' . uniqid(),
            'branch_state' => 'Test',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedProductStock(int $qty, string $variant, int $branchId): array
    {
        $now = now();
        $productId = (int)DB::table('products')->insertGetId([
            'added_by' => 'admin',
            'name' => 'Inventory Test Product ' . uniqid(),
            'slug' => 'inventory-test-' . uniqid(),
            'product_type' => 'physical',
            'branch_id' => $branchId,
            'color_image' => '',
            'variation' => json_encode([
                [
                    'type' => $variant,
                    'qty' => $qty,
                ],
            ]),
            'current_stock' => $qty,
            'unit_price' => 100,
            'purchase_price' => 80,
            'tax' => '0.00',
            'discount' => '0.00',
            'status' => 1,
            'featured_status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $productStockId = (int)DB::table('product_stocks')->insertGetId([
            'product_id' => $productId,
            'variant' => $variant,
            'sku' => 'SKU-' . uniqid(),
            'price' => 100,
            'qty' => $qty,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $branchStockId = (int)DB::table('manage_branch_product_stock')->insertGetId([
            'branch_id' => $branchId,
            'product_id' => $productId,
            'variation_type' => $variant,
            'variation_key' => $variant,
            'current_stock' => $qty,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'product_id' => $productId,
            'product_stock_id' => $productStockId,
            'branch_stock_id' => $branchStockId,
        ];
    }

    private function getProductVariationQty(int $productId, string $variant): int
    {
        $raw = DB::table('products')->where('id', $productId)->value('variation');
        $rows = json_decode((string)$raw, true);
        if (!is_array($rows)) {
            return 0;
        }

        foreach ($rows as $row) {
            if ((string)($row['type'] ?? '') === $variant) {
                return (int)($row['qty'] ?? 0);
            }
        }

        return 0;
    }

    private function getBranchStockQuantity(int $productId, int $branchId, string $variant): int
    {
        $variationColumns = [];
        if (Schema::hasColumn('manage_branch_product_stock', 'variation_key')) {
            $variationColumns[] = 'variation_key';
        }
        if (Schema::hasColumn('manage_branch_product_stock', 'variation_type')) {
            $variationColumns[] = 'variation_type';
        }
        if (Schema::hasColumn('manage_branch_product_stock', 'attributes')) {
            $variationColumns[] = 'attributes';
        }

        $query = DB::table('manage_branch_product_stock')
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->where(function ($variationQuery) use ($variant, $variationColumns) {
                foreach ($variationColumns as $index => $column) {
                    if ($index === 0) {
                        $variationQuery->where($column, $variant);
                    } else {
                        $variationQuery->orWhere($column, $variant);
                    }
                }
            });

        return (int) ($query->value('current_stock') ?? 0);
    }
}
