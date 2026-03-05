<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WholesaleAddToCartSelectionTest extends TestCase
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

    public function test_add_to_cart_uses_selected_range_when_variant_input_is_stale(): void
    {
        $user = $this->createWholesalerUser(tier: '1');
        $productId = $this->createWholesaleProductFixture();

        $leftWholesaleId = $this->createWholesaleVariation($productId, 'Left', 'variant:Left');
        $rightWholesaleId = $this->createWholesaleVariation($productId, 'Right', 'variant:Right');

        $this->createPriceRange($leftWholesaleId, '1', 20, 50, 900.00);
        $rightRangeId = $this->createPriceRange($rightWholesaleId, '1', 20, 50, 950.00);

        $this->actingAs($user, 'customer');

        $response = $this->from('/wholesale-test-referrer')->post(route('web.addwholesale'), [
            'product_id' => $productId,
            'price_range_id' => $rightRangeId,
            'quantity' => 20,
            // Simulates stale UI hidden input; selected range is Right.
            'variant' => 'variant:Left',
        ]);

        $response->assertRedirect('/wholesale-test-referrer');

        $cartRow = DB::table('carts')
            ->where('customer_id', $user->id)
            ->where('product_id', $productId)
            ->orderByDesc('id')
            ->first();

        $this->assertNotNull($cartRow, 'Cart row should be created for selected wholesale range.');
        $this->assertSame('Right', (string)$cartRow->variant);
        $this->assertSame(20, (int)$cartRow->quantity);
        $this->assertSame(950.00, (float)$cartRow->price);

        $choices = json_decode((string)($cartRow->choices ?? ''), true) ?: [];
        $this->assertSame('variant:Right', (string)($choices['original_variation_key'] ?? ''));
    }

    private function createWholesalerUser(string $tier): User
    {
        $now = now();
        $id = DB::table('users')->insertGetId([
            'name' => 'Wholesale Test User',
            'f_name' => 'Wholesale',
            'l_name' => 'Tester',
            'phone' => '2010' . random_int(1000000, 9999999),
            'email' => 'wholesale-test-' . uniqid() . '@example.com',
            'user_type' => 1,
            'password' => bcrypt('password'),
            'tier' => $tier,
            'wholesaler_status' => 1,
            'moq_override_enabled' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return User::query()->findOrFail($id);
    }

    private function createWholesaleProductFixture(): int
    {
        $now = now();

        return (int)DB::table('products')->insertGetId([
            'added_by' => 'admin',
            'user_id' => 1,
            'name' => 'Wholesale Cart Test ' . uniqid(),
            'slug' => 'wholesale-cart-test-' . uniqid(),
            'color_image' => '',
            'thumbnail' => 'test.png',
            'tax' => '0.00',
            'tax_model' => 'exclude',
            'unit_price' => 100,
            'purchase_price' => 80,
            'status' => 1,
            'featured_status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function createWholesaleVariation(int $productId, string $variationType, string $variationKey): int
    {
        return (int)DB::table('wholesale_products')->insertGetId([
            'category_id' => 1,
            'sub_category_id' => 1,
            'product_id' => $productId,
            'variation_type' => $variationType,
            'variation_key' => $variationKey,
            'status' => 1,
        ]);
    }

    private function createPriceRange(int $wholesaleId, string $tier, int $minQty, int $maxQty, float $price): int
    {
        $now = now();

        return (int)DB::table('wholesale_price_ranges')->insertGetId([
            'wholesale_id' => $wholesaleId,
            'tier' => $tier,
            'min_qty' => (string)$minQty,
            'max_qty' => (string)$maxQty,
            'price_per_piece' => $price,
            'discount' => 0,
            'status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
