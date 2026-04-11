<?php

namespace Tests\Feature;

use App\Utils\CartManager;
use App\Models\Cart;
use App\Models\Order;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class CartSecurityRegressionTest extends TestCase
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

        if (!defined('VIEW_FILE_NAMES')) {
            define('VIEW_FILE_NAMES', require base_path('resources/themes/default/file_names.php'));
        }

        View::addLocation(base_path('resources/themes/default'));
    }

    protected function connectionsToTransact(): array
    {
        return ['mysql'];
    }

    public function test_logged_in_customer_cannot_remove_guest_cart_with_same_numeric_customer_id(): void
    {
        $user = $this->createCustomer();
        $productId = $this->createProduct();

        $guestCartId = $this->createCart([
            'customer_id' => $user->id,
            'is_guest' => 1,
            'product_id' => $productId,
            'cart_group_id' => 'guest-test-group',
        ]);

        $response = $this->actingAs($user, 'customer')
            ->post(route('cart.remove'), ['key' => $guestCartId]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('carts', [
            'id' => $guestCartId,
            'is_guest' => 1,
        ]);
    }

    public function test_logged_in_customer_cannot_update_guest_cart_with_same_numeric_customer_id(): void
    {
        $user = $this->createCustomer();
        $productId = $this->createProduct(currentStock: 10);

        $guestCartId = $this->createCart([
            'customer_id' => $user->id,
            'is_guest' => 1,
            'product_id' => $productId,
            'quantity' => 2,
            'cart_group_id' => 'guest-update-group',
        ]);

        $response = $this->actingAs($user, 'customer')
            ->post(route('cart.updateQuantity'), [
                'key' => $guestCartId,
                'quantity' => 5,
            ]);

        $response->assertOk()
            ->assertJson([
                'status' => 0,
            ]);

        $this->assertDatabaseHas('carts', [
            'id' => $guestCartId,
            'quantity' => 2,
            'is_guest' => 1,
        ]);
    }

    public function test_update_variation_does_not_overwrite_unrelated_cart_row_with_matching_primary_key(): void
    {
        $user = $this->createCustomer();
        $targetProductId = 400001;
        $existingProductId = 400002;

        $this->createProduct(id: $targetProductId, currentStock: 10);
        $this->createProduct(id: $existingProductId, currentStock: 10);

        $existingCartId = $this->createCart([
            'id' => $targetProductId,
            'customer_id' => $user->id,
            'is_guest' => 0,
            'product_id' => $existingProductId,
            'name' => 'Existing cart row',
            'slug' => 'existing-cart-row',
            'cart_group_id' => 'customer-existing-group',
        ]);

        $response = $this->actingAs($user, 'customer')
            ->post(route('cart.update-variation'), [
                'id' => $targetProductId,
                'product_id' => $targetProductId,
                'quantity' => 1,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('carts', [
            'id' => $existingCartId,
            'product_id' => $existingProductId,
            'name' => 'Existing cart row',
        ]);

        $this->assertDatabaseHas('carts', [
            'customer_id' => $user->id,
            'is_guest' => 0,
            'product_id' => $targetProductId,
        ]);
    }

    public function test_remove_all_requires_post_and_clears_cart_shipping_for_current_owner(): void
    {
        $user = $this->createCustomer();
        $productId = $this->createProduct();
        $cartGroupId = 'customer-clear-group';

        $this->createCart([
            'customer_id' => $user->id,
            'is_guest' => 0,
            'product_id' => $productId,
            'cart_group_id' => $cartGroupId,
        ]);

        DB::table('cart_shippings')->insert([
            'cart_group_id' => $cartGroupId,
            'shipping_method_id' => 0,
            'shipping_cost' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user, 'customer')
            ->get(route('cart.remove-all'))
            ->assertStatus(405);

        $this->actingAs($user, 'customer')
            ->from('/cart-test')
            ->post(route('cart.remove-all'))
            ->assertRedirect('/cart-test');

        $this->assertDatabaseMissing('carts', [
            'customer_id' => $user->id,
            'cart_group_id' => $cartGroupId,
        ]);

        $this->assertDatabaseMissing('cart_shippings', [
            'cart_group_id' => $cartGroupId,
        ]);
    }

    public function test_cart_price_refresh_recalculates_stale_stored_values(): void
    {
        $user = $this->createCustomer();
        $productId = $this->createProduct(currentStock: 10, unitPrice: 220);
        $cartId = $this->createCart([
            'customer_id' => $user->id,
            'is_guest' => 0,
            'product_id' => $productId,
            'cart_group_id' => 'stale-price-group',
            'price' => 5,
            'discount' => 4,
            'tax' => 7,
        ]);

        $cart = Cart::query()->findOrFail($cartId);
        CartManager::refreshCartItemPricing($cart);

        $this->assertSame(220.0, (float) $cart->fresh()->price);
        $this->assertSame(0.0, (float) $cart->fresh()->discount);
        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'price' => 220.0,
            'discount' => 0.0,
            'tax' => 0.0,
        ]);
    }

    public function test_area_wise_shipping_cost_is_preserved_during_cart_price_refresh(): void
    {
        DB::table('shipping_types')->insert([
            'seller_id' => 0,
            'shipping_type' => 'area_wise',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = $this->createProduct(currentStock: 10, unitPrice: 220);
        $cartId = $this->createCart([
            'customer_id' => 700001,
            'is_guest' => 1,
            'product_id' => $productId,
            'cart_group_id' => 'area-wise-preserve-group',
            'shipping_type' => 'area_wise',
            'shipping_cost' => 85,
            'price' => 10,
        ]);

        $cart = Cart::query()->findOrFail($cartId);
        CartManager::refreshCartItemPricing($cart);

        $freshCart = $cart->fresh();
        $this->assertSame(220.0, (float) $freshCart->price);
        $this->assertSame(85.0, (float) $freshCart->shipping_cost);
    }

    public function test_rest_api_cart_list_refreshes_stale_pricing_for_mobile_clients(): void
    {
        $guestId = $this->createGuestUser();
        $productId = $this->createProduct(currentStock: 10, unitPrice: 220);

        $this->createCart([
            'customer_id' => $guestId,
            'is_guest' => 1,
            'product_id' => $productId,
            'cart_group_id' => 'mobile-refresh-group',
            'price' => 5,
            'discount' => 4,
            'tax' => 7,
        ]);

        $response = $this->getJson("/api/v1/cart?guest_id={$guestId}");

        $response->assertOk()
            ->assertJsonPath('0.price', 220)
            ->assertJsonPath('0.discount', 0)
            ->assertJsonPath('0.tax', 0);
    }

    public function test_area_wise_shipping_cost_is_saved_on_order_and_returned_in_order_details(): void
    {
        $this->setBusinessSetting('delivery_zip_code_area_restriction', 0);
        $this->setBusinessSetting('delivery_country_restriction', 0);
        $this->setAreaWiseShippingType();
        Cache::flush();

        $user = $this->createCustomer();
        $productId = $this->createProduct(currentStock: 10, unitPrice: 250, freeShipping: 0);
        $stateId = $this->createState();
        $cityId = $this->createCity($stateId);
        $area = 'Area-' . uniqid();
        $shippingCost = 85.0;

        $this->createShippingMethodArea(
            stateId: $stateId,
            cityId: $cityId,
            area: $area,
            cost: $shippingCost
        );

        $addressId = $this->createShippingAddress(
            customerId: (string) $user->id,
            isGuest: 0,
            state: 'Cairo',
            city: 'Cairo City',
            area: $area
        );

        $this->createCart([
            'customer_id' => $user->id,
            'is_guest' => 0,
            'product_id' => $productId,
            'cart_group_id' => CartManager::generateOpaqueCartGroupId(),
            'shipping_type' => 'area_wise',
            'shipping_cost' => 0,
        ]);

        $shippingResponse = $this->actingAs($user, 'customer')
            ->postJson('/api/v1/cart/update-shipping-cost', [
                'country' => 'Egypt',
                'state_id' => $stateId,
                'city_id' => $cityId,
                'area_name' => $area,
                'delivery_type' => 'delivery',
            ]);

        $shippingResponse->assertOk()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('is_area_wise_shipping_resolved', true);
        $this->assertSame($shippingCost, (float) $shippingResponse->json('shipping_cost'));

        $placeOrderResponse = $this->getJson('/api/v1/customer/order/place?' . http_build_query([
                'payment_request_from' => 'app',
                'is_guest' => 0,
                'customer_id' => $user->id,
                'address_id' => $addressId,
                'billing_address_id' => $addressId,
                'delivery_type' => 'delivery',
            ]));

        $placeOrderResponse->assertOk();

        $orderId = $placeOrderResponse->json('order_ids.0');
        $this->assertNotNull($orderId);

        $order = Order::query()->findOrFail($orderId);
        $this->assertSame($shippingCost, (float) $order->shipping_cost);

        $detailsResponse = $this->getJson('/api/v1/customer/order/details?' . http_build_query([
                'payment_request_from' => 'app',
                'is_guest' => 0,
                'customer_id' => $user->id,
                'order_id' => $orderId,
            ]));

        $detailsResponse->assertOk();
        $this->assertSame($shippingCost, (float) $detailsResponse->json('0.order.shipping_cost'));
    }

    public function test_generated_cart_group_ids_are_opaque(): void
    {
        $cartGroupId = CartManager::generateOpaqueCartGroupId();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f-]{36}$/',
            $cartGroupId
        );
        $this->assertDoesNotMatchRegularExpression('/^(guest|\d+)-/i', $cartGroupId);
    }

    public function test_buy_now_with_configurable_physical_extra_charges_redirects_to_cart_review(): void
    {
        $this->setWebShippingType('product_wise');

        $user = $this->createCustomer();
        $categoryId = $this->createCategory();
        $productId = $this->createProduct(categoryId: $categoryId);
        $this->createExtraCharge(categoryId: $categoryId, type: 'installation', charges: 150.0);

        $response = $this->actingAs($user, 'customer')->post(route('cart.add'), [
            'id' => $productId,
            'quantity' => 1,
            'buy_now' => 1,
        ]);

        $response->assertRedirect(route('shop-cart'));
    }

    public function test_buy_now_without_configurable_physical_extra_charges_still_redirects_to_checkout_details(): void
    {
        $this->setWebShippingType('product_wise');

        $user = $this->createCustomer();
        $productId = $this->createProduct();

        $response = $this->actingAs($user, 'customer')->post(route('cart.add'), [
            'id' => $productId,
            'quantity' => 1,
            'buy_now' => 1,
        ]);

        $response->assertRedirect(route('checkout-details'));
    }

    private function createCustomer(): User
    {
        $now = now();
        $id = DB::table('users')->insertGetId([
            'name' => 'Cart Test User',
            'f_name' => 'Cart',
            'l_name' => 'Tester',
            'phone' => '2011' . random_int(1000000, 9999999),
            'email' => 'cart-test-' . uniqid() . '@example.com',
            'user_type' => 0,
            'password' => bcrypt('password'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return User::query()->findOrFail($id);
    }

    private function createGuestUser(): int
    {
        return (int) DB::table('guest_users')->insertGetId([
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createProduct(
        ?int $id = null,
        int $currentStock = 5,
        int $unitPrice = 100,
        int $freeShipping = 1,
        int $categoryId = 0
    ): int
    {
        $now = now();
        $data = [
            'added_by' => 'admin',
            'user_id' => 1,
            'name' => 'Cart Product ' . uniqid(),
            'slug' => 'cart-product-' . uniqid(),
            'color_image' => '',
            'thumbnail' => 'test.png',
            'tax' => '0.00',
            'tax_model' => 'exclude',
            'unit_price' => $unitPrice,
            'purchase_price' => 80,
            'current_stock' => $currentStock,
            'minimum_order_qty' => 1,
            'choice_options' => '[]',
            'variation' => '[]',
            'category_id' => $categoryId,
            'sub_category_id' => 0,
            'sub_sub_category_id' => 0,
            'category_ids' => $categoryId > 0 ? json_encode([['id' => (string) $categoryId, 'position' => 1]]) : '[]',
            'product_type' => 'physical',
            'free_shipping' => $freeShipping,
            'shipping_cost' => 0,
            'status' => 1,
            'request_status' => 1,
            'featured_status' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($id !== null) {
            $data['id'] = $id;
            DB::table('products')->insert($data);

            return $id;
        }

        return (int)DB::table('products')->insertGetId($data);
    }

    private function createCategory(?string $name = null): int
    {
        $now = now();

        return (int) DB::table('categories')->insertGetId([
            'name' => $name ?? 'Cart Category ' . uniqid(),
            'slug' => 'cart-category-' . uniqid(),
            'icon' => 'test.png',
            'icon_storage_type' => 'public',
            'parent_id' => 0,
            'position' => 0,
            'home_status' => 0,
            'priority' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function createExtraCharge(int $categoryId, string $type, float $charges): int
    {
        return (int) DB::table('manage_extra_charges')->insertGetId([
            'category_id' => $categoryId,
            'type' => $type,
            'charges' => $charges,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCart(array $overrides = []): int
    {
        $now = now();
        $data = array_merge([
            'customer_id' => 1,
            'cart_group_id' => 'cart-group-' . uniqid(),
            'product_id' => 1,
            'product_type' => 'physical',
            'digital_product_type' => null,
            'color' => null,
            'choices' => '[]',
            'variations' => '[]',
            'variant' => '',
            'quantity' => 1,
            'price' => 100,
            'tax' => 0,
            'discount' => 0,
            'installtion_charges' => 0,
            'exchange_qty' => 0,
            'exchange_charges' => 0,
            'tax_model' => 'exclude',
            'is_checked' => 1,
            'slug' => 'cart-item-' . uniqid(),
            'name' => 'Cart item',
            'thumbnail' => 'test.png',
            'seller_id' => 1,
            'seller_is' => 'admin',
            'shop_info' => 'In-house',
            'shipping_cost' => 0,
            'shipping_type' => 'order_wise',
            'is_guest' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], $overrides);

        if (array_key_exists('id', $data)) {
            DB::table('carts')->insert($data);

            return (int)$data['id'];
        }

        return (int)DB::table('carts')->insertGetId($data);
    }

    private function setBusinessSetting(string $type, mixed $value): void
    {
        DB::table('business_settings')->updateOrInsert(
            ['type' => $type],
            [
                'value' => is_scalar($value) ? (string) $value : json_encode($value),
                'is_active' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function setAreaWiseShippingType(): void
    {
        DB::table('shipping_types')->updateOrInsert(
            ['seller_id' => 0],
            [
                'shipping_type' => 'area_wise',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function setWebShippingType(string $shippingType): void
    {
        DB::table('shipping_types')->updateOrInsert(
            ['seller_id' => 0],
            [
                'shipping_type' => $shippingType,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function createState(?string $name = null, string $country = 'EG'): int
    {
        $name ??= 'State-' . uniqid();

        return (int) DB::table('states')->insertGetId([
            'name' => $name,
            'country' => $country,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCity(int $stateId, ?string $name = null): int
    {
        $name ??= 'City-' . uniqid();

        return (int) DB::table('cities')->insertGetId([
            'name' => $name,
            'state_id' => $stateId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createShippingMethodArea(
        int $stateId,
        int $cityId,
        string $area,
        float $cost,
        string $country = 'EG'
    ): int {
        return (int) DB::table('shipping_method_areas')->insertGetId([
            'creator_id' => 1,
            'creator_type' => 'admin',
            'country' => $country,
            'state_id' => $stateId,
            'city_id' => $cityId,
            'area' => $area,
            'cost' => $cost,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createShippingAddress(
        string $customerId,
        int $isGuest,
        string $state,
        string $city,
        string $area
    ): int {
        return (int) DB::table('shipping_addresses')->insertGetId([
            'customer_id' => $customerId,
            'contact_person_name' => 'Guest Cart Tester',
            'address_type' => 'home',
            'address' => '123 Test Street',
            'city' => $city,
            'zip' => '11311',
            'phone' => '2011' . random_int(1000000, 9999999),
            'email' => 'guest-' . uniqid() . '@example.com',
            'country' => 'Egypt',
            'state' => $state,
            'area' => $area,
            'latitude' => '30.0444',
            'longitude' => '31.2357',
            'is_billing' => 1,
            'is_guest' => $isGuest,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
