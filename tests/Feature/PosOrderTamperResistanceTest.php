<?php

namespace Tests\Feature;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\DigitalProductVariationRepositoryInterface;
use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StorageRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Http\Controllers\Admin\POS\POSOrderController as AdminPosOrderController;
use App\Http\Controllers\Vendor\POS\POSOrderController as VendorPosOrderController;
use App\Models\Admin;
use App\Models\PosCartState;
use App\Models\Product;
use App\Models\Seller;
use App\Services\CartService;
use App\Services\InventoryMutationService;
use App\Services\OrderDetailsService;
use App\Services\OrderService;
use App\Services\POSService;
use App\Services\ProductExtraChargeResolverService;
use App\Services\PosCartStateService;
use App\Services\PosIdempotencyService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PosOrderTamperResistanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureTestTablesExist();

        Route::middleware('web')->post('/__test/admin-pos/order-place', [AdminPosOrderController::class, 'placeOrder']);
        Route::middleware('web')->post('/__test/vendor-pos/order-place', [VendorPosOrderController::class, 'placeOrder']);
    }

    public function test_admin_pos_place_order_ignores_tampered_cart_price(): void
    {
        $this->actingAs(new Admin(['id' => 1]), 'admin');

        $product = new Product([
            'id' => 11,
            'unit_price' => 100.0,
            'tax' => 0,
            'tax_model' => 'exclude',
            'product_type' => 'physical',
            'discount' => 0,
            'discount_type' => 'flat',
            'user_id' => 1,
            'category_id' => 1,
            'sub_category_id' => 0,
            'sub_sub_category_id' => 0,
        ]);

        $cartPayload = [[
            'id' => 11,
            'quantity' => 1,
            'price' => 1.0,
            'discount' => 99.0,
            'variant' => '',
            'branch_id' => 1,
            'customerId' => 0,
            'customerOnHold' => false,
        ]];

        $this->bindAdminPosDependencies($product, $cartPayload);

        $response = $this->postJson('/__test/admin-pos/order-place', [
            'cart_id' => 'walking-customer-admin-test-b1',
            'branch_id' => 1,
            'idempotency_key' => 'admin-pos-tamper-test',
            'type' => 'card',
        ]);

        $response->assertOk();
        $response->assertJson(['orderId' => 100001, 'cartId' => 'walking-customer-new-b1']);
    }

    public function test_vendor_pos_place_order_ignores_tampered_request_amount_and_cart_price(): void
    {
        $this->actingAs(new Seller(['id' => 7, 'pos_status' => 1]), 'seller');

        $product = new Product([
            'id' => 22,
            'unit_price' => 100.0,
            'tax' => 0,
            'tax_model' => 'exclude',
            'product_type' => 'physical',
            'discount' => 0,
            'discount_type' => 'flat',
            'user_id' => 7,
            'category_id' => 1,
        ]);

        $cartPayload = [[
            'id' => 22,
            'quantity' => 1,
            'price' => 1.0,
            'discount' => 90.0,
            'variant' => '',
            'customerId' => 0,
            'customerOnHold' => false,
        ]];

        $this->bindVendorPosDependencies($product, $cartPayload);

        $response = $this->withSession([
            'pos_cart_id' => 'walking-customer-vendor-test-b1',
        ])->postJson('/__test/vendor-pos/order-place', [
            'branch_id' => 1,
            'amount' => 1.0,
            'type' => 'card',
        ]);

        $response->assertOk();
    }

    private function bindAdminPosDependencies(Product $product, array $cartPayload): void
    {
        $productRepo = $this->createMock(ProductRepositoryInterface::class);
        $productRepo->method('getFirstWhere')->willReturn($product);
        $this->app->instance(ProductRepositoryInterface::class, $productRepo);

        $this->app->instance(CustomerRepositoryInterface::class, $this->createMock(CustomerRepositoryInterface::class));
        $this->app->instance(VendorRepositoryInterface::class, $this->createMock(VendorRepositoryInterface::class));
        $this->app->instance(StorageRepositoryInterface::class, $this->createMock(StorageRepositoryInterface::class));

        $digitalProductVariationRepo = $this->createMock(DigitalProductVariationRepositoryInterface::class);
        $digitalProductVariationRepo->method('getFirstWhere')->willReturn(null);
        $this->app->instance(DigitalProductVariationRepositoryInterface::class, $digitalProductVariationRepo);

        $cartService = $this->createMock(CartService::class);
        $cartService->method('cartBelongsToBranch')->willReturn(true);
        $cartService->method('getUserId')->willReturn(0);
        $cartService->method('checkProductTypeDigital')->willReturn(false);
        $cartService->method('generateWalkingCustomerCartId')->willReturn('walking-customer-new-b1');
        $cartService->method('getCartSubtotalCalculation')->willReturnCallback(function ($productArg, $cartItem) {
            return [
                'countItem' => 1,
                'totalQuantity' => (int)$cartItem['quantity'],
                'taxCalculate' => 0,
                'totalTaxShow' => 0,
                'totalTax' => 0,
                'totalIncludeTax' => 0,
                'subtotal' => (float)$cartItem['price'] * (int)$cartItem['quantity'],
                'discountOnProduct' => (float)$cartItem['discount'] * (int)$cartItem['quantity'],
                'productSubtotal' => ((float)$cartItem['price'] - (float)$cartItem['discount']) * (int)$cartItem['quantity'],
            ];
        });
        $this->app->instance(CartService::class, $cartService);

        $posCartStateService = $this->createMock(PosCartStateService::class);
        $cartState = new PosCartState([
            'cart_id' => 'walking-customer-admin-test-b1',
            'branch_id' => 1,
            'actor_type' => 'admin',
            'actor_id' => 1,
            'payload' => $cartPayload,
        ]);
        $posCartStateService->method('assertCart')->willReturn($cartState);
        $posCartStateService->method('getPayload')->willReturn($cartPayload);
        $posCartStateService->method('putPayload')->willReturn($cartState);
        $posCartStateService->method('ensureCart')->willReturn($cartState);
        $this->app->instance(PosCartStateService::class, $posCartStateService);

        $posIdempotencyService = $this->createMock(PosIdempotencyService::class);
        $posIdempotencyService->method('execute')->willReturnCallback(
            fn($action, $idempotencyKey, $actorType, $actorId, $callback) => $callback()
        );
        $this->app->instance(PosIdempotencyService::class, $posIdempotencyService);

        $posService = $this->createMock(POSService::class);
        $posService->expects($this->once())
            ->method('checkConditions')
            ->with(
                $this->callback(fn($amount) => abs((float)$amount - 114.0) < 0.0001),
                null,
                'walking-customer-admin-test-b1',
                1,
                'admin',
                0
            )
            ->willReturn(false);
        $this->app->instance(POSService::class, $posService);

        $inventoryMutationService = $this->createMock(InventoryMutationService::class);
        $inventoryMutationService->method('decreaseForPosLine')->willReturn([
            'status' => true,
            'branchId' => 1,
        ]);
        $this->app->instance(InventoryMutationService::class, $inventoryMutationService);

        $orderDetailsService = $this->createMock(OrderDetailsService::class);
        $orderDetailsService->expects($this->once())
            ->method('getPOSOrderDetailsData')
            ->with(
                100001,
                $this->callback(fn($item) => abs((float)$item['price'] - 100.0) < 0.0001 && abs((float)$item['discount']) < 0.0001),
                $this->anything(),
                100.0,
                0.0,
                0.0,
                0.0
            )
            ->willReturn(['order_id' => 100001, 'product_id' => 11]);
        $this->app->instance(OrderDetailsService::class, $orderDetailsService);

        $orderService = $this->createMock(OrderService::class);
        $orderService->expects($this->once())
            ->method('getPOSOrderData')
            ->with(
                100001,
                $cartPayload,
                114.0,
                114.0,
                'card',
                'admin',
                0,
                0.0,
                0.0,
                1.0
            )
            ->willReturn(['id' => 100001]);
        $this->app->instance(OrderService::class, $orderService);

        $orderDetailRepo = $this->createMock(OrderDetailRepositoryInterface::class);
        $orderDetailRepo->expects($this->once())->method('add')->with(['order_id' => 100001, 'product_id' => 11]);
        $this->app->instance(OrderDetailRepositoryInterface::class, $orderDetailRepo);

        $orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $orderRepo->expects($this->once())->method('add')->with(['id' => 100001]);
        $this->app->instance(OrderRepositoryInterface::class, $orderRepo);

        $extraChargeResolver = $this->createMock(ProductExtraChargeResolverService::class);
        $extraChargeResolver->method('resolveForProduct')->willReturn([
            'installation' => 0.0,
            'exchange' => 0.0,
        ]);
        $this->app->instance(ProductExtraChargeResolverService::class, $extraChargeResolver);
    }

    private function ensureTestTablesExist(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->unsignedBigInteger('id')->primary();
            });
        }

        if (!Schema::hasTable('order_details')) {
            Schema::create('order_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable()->index();
            });
        }

        if (!Schema::hasTable('business_settings')) {
            Schema::create('business_settings', function (Blueprint $table) {
                $table->id();
                $table->string('type')->unique();
                $table->text('value')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->nullableMorphs('translationable');
                $table->string('locale')->nullable();
                $table->string('key')->nullable();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        Cache::flush();
        \DB::table('business_settings')->updateOrInsert(
            ['type' => 'currency_model'],
            ['value' => 'single_currency', 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
        );
    }

    private function bindVendorPosDependencies(Product $product, array $cartPayload): void
    {
        $productRepo = $this->createMock(ProductRepositoryInterface::class);
        $productRepo->method('getFirstWhere')->willReturn($product);
        $this->app->instance(ProductRepositoryInterface::class, $productRepo);

        $this->app->instance(CustomerRepositoryInterface::class, $this->createMock(CustomerRepositoryInterface::class));
        $this->app->instance(VendorRepositoryInterface::class, $this->createMock(VendorRepositoryInterface::class));
        $this->app->instance(StorageRepositoryInterface::class, $this->createMock(StorageRepositoryInterface::class));

        $digitalProductVariationRepo = $this->createMock(DigitalProductVariationRepositoryInterface::class);
        $digitalProductVariationRepo->method('getFirstWhere')->willReturn(null);
        $this->app->instance(DigitalProductVariationRepositoryInterface::class, $digitalProductVariationRepo);

        $cartService = $this->createMock(CartService::class);
        $cartService->method('getUserId')->willReturn(0);
        $cartService->method('checkProductTypeDigital')->willReturn(false);
        $cartService->method('getNewCartId')->willReturnCallback(fn() => null);
        $cartService->method('getCartSubtotalCalculation')->willReturnCallback(function ($productArg, $cartItem) {
            return [
                'countItem' => 1,
                'totalQuantity' => (int)$cartItem['quantity'],
                'taxCalculate' => 0,
                'totalTaxShow' => 0,
                'totalTax' => 0,
                'totalIncludeTax' => 0,
                'subtotal' => (float)$cartItem['price'] * (int)$cartItem['quantity'],
                'discountOnProduct' => (float)$cartItem['discount'] * (int)$cartItem['quantity'],
                'productSubtotal' => ((float)$cartItem['price'] - (float)$cartItem['discount']) * (int)$cartItem['quantity'],
            ];
        });
        $cartService->method('getTotalCalculation')->willReturn([
            'totalAmount' => 100.0,
            'couponDiscount' => 0.0,
            'extraDiscount' => 0.0,
            'taxableBase' => 100.0,
            'taxTotal' => 0.0,
            'subTotalWithVat' => 100.0,
            'total' => 100.0,
        ]);
        $this->app->instance(CartService::class, $cartService);

        $posCartStateService = $this->createMock(PosCartStateService::class);
        $cartState = new PosCartState([
            'cart_id' => 'walking-customer-vendor-test-b1',
            'branch_id' => 1,
            'actor_type' => 'seller',
            'actor_id' => 7,
            'payload' => $cartPayload,
        ]);
        $posCartStateService->method('assertCart')->willReturn($cartState);
        $posCartStateService->method('getPayload')->willReturn($cartPayload);
        $posCartStateService->method('putPayload')->willReturn($cartState);
        $posCartStateService->method('ensureCart')->willReturn($cartState);
        $this->app->instance(PosCartStateService::class, $posCartStateService);

        $posService = $this->createMock(POSService::class);
        $posService->expects($this->once())
            ->method('checkConditions')
            ->with(
                $this->callback(fn($amount) => abs((float)$amount - 100.0) < 0.0001),
                null
            )
            ->willReturn(false);
        $this->app->instance(POSService::class, $posService);

        $inventoryMutationService = $this->createMock(InventoryMutationService::class);
        $inventoryMutationService->method('decreaseForPosLine')->willReturn([
            'status' => true,
            'branchId' => 1,
        ]);
        $this->app->instance(InventoryMutationService::class, $inventoryMutationService);

        $orderDetailsService = $this->createMock(OrderDetailsService::class);
        $orderDetailsService->expects($this->once())
            ->method('getPOSOrderDetailsData')
            ->with(
                $this->isType('int'),
                $this->callback(fn($item) => abs((float)$item['price'] - 100.0) < 0.0001 && abs((float)$item['discount']) < 0.0001),
                $this->anything(),
                100.0,
                0.0,
                0.0,
                0.0
            )
            ->willReturn(['order_id' => 100001, 'product_id' => 22]);
        $this->app->instance(OrderDetailsService::class, $orderDetailsService);

        $orderService = $this->createMock(OrderService::class);
        $orderService->expects($this->once())
            ->method('getPOSOrderData')
            ->with(
                $this->isType('int'),
                $cartPayload,
                100.0,
                100.0,
                'card',
                'seller',
                0,
                0.0,
                0.0,
                1.0
            )
            ->willReturn(['id' => 100001]);
        $this->app->instance(OrderService::class, $orderService);

        $orderDetailRepo = $this->createMock(OrderDetailRepositoryInterface::class);
        $orderDetailRepo->expects($this->once())->method('add')->with(['order_id' => 100001, 'product_id' => 22]);
        $this->app->instance(OrderDetailRepositoryInterface::class, $orderDetailRepo);

        $orderRepo = $this->createMock(OrderRepositoryInterface::class);
        $orderRepo->expects($this->once())->method('add')->with(['id' => 100001]);
        $this->app->instance(OrderRepositoryInterface::class, $orderRepo);
    }
}
