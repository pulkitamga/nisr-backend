<?php

namespace Tests\Unit;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\DigitalProductVariationRepositoryInterface;
use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StorageRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Http\Controllers\Admin\POS\POSOrderController as AdminPosOrderController;
use App\Http\Controllers\Vendor\POS\POSOrderController as VendorPosOrderController;
use App\Models\Product;
use App\Services\CartService;
use App\Services\InventoryMutationService;
use App\Services\OrderDetailsService;
use App\Services\OrderService;
use App\Services\POSService;
use App\Services\ProductExtraChargeResolverService;
use App\Services\PosCartStateService;
use App\Services\PosIdempotencyService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PosModuleSecurityFixTest extends TestCase
{
    public function test_admin_pos_order_controller_reprices_cart_line_from_authoritative_product_data(): void
    {
        $product = new Product([
            'id' => 15,
            'unit_price' => 100.0,
            'tax_model' => 'exclude',
            'product_type' => 'physical',
            'discount' => 0,
            'discount_type' => 'flat',
            'category_id' => 9,
            'sub_category_id' => 0,
            'sub_sub_category_id' => 0,
        ]);

        $productRepo = $this->createMock(ProductRepositoryInterface::class);
        $productRepo->expects($this->once())
            ->method('getFirstWhere')
            ->with(
                ['id' => 15],
                $this->callback(fn($relations) => is_array($relations) && array_key_exists('clearanceSale', $relations))
            )
            ->willReturn($product);

        $extraChargeResolver = $this->createMock(ProductExtraChargeResolverService::class);
        $extraChargeResolver->expects($this->once())
            ->method('resolveForProduct')
            ->with($product)
            ->willReturn([
                'installation' => 25.0,
                'exchange' => 10.0,
            ]);

        $controller = new AdminPosOrderController(
            $productRepo,
            $this->createMock(CustomerRepositoryInterface::class),
            $this->createMock(OrderRepositoryInterface::class),
            $this->createMock(OrderDetailRepositoryInterface::class),
            $this->createMock(VendorRepositoryInterface::class),
            $this->createMock(DigitalProductVariationRepositoryInterface::class),
            $this->createMock(StorageRepositoryInterface::class),
            $this->createMock(POSService::class),
            $this->createMock(CartService::class),
            $this->createMock(PosCartStateService::class),
            $this->createMock(PosIdempotencyService::class),
            $this->createMock(InventoryMutationService::class),
            $this->createMock(OrderDetailsService::class),
            $this->createMock(OrderService::class),
            $extraChargeResolver
        );

        $method = new \ReflectionMethod($controller, 'getValidatedCartLineItems');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [[
            'id' => 15,
            'quantity' => 2,
            'price' => 1.0,
            'discount' => 99.0,
            'branch_id' => 7,
        ]], 7);

        $this->assertSame(100.0, $result[0]['price']);
        $this->assertEquals(0.0, $result[0]['discount']);
        $this->assertSame(25.0, $result[0]['installation_charge']);
        $this->assertSame(10.0, $result[0]['exchange_charge']);
        $this->assertSame(7, $result[0]['branch_id']);
    }

    public function test_vendor_pos_order_controller_rejects_invalid_payment_type(): void
    {
        $controller = new VendorPosOrderController(
            $this->createMock(ProductRepositoryInterface::class),
            $this->createMock(CustomerRepositoryInterface::class),
            $this->createMock(OrderRepositoryInterface::class),
            $this->createMock(OrderDetailRepositoryInterface::class),
            $this->createMock(VendorRepositoryInterface::class),
            $this->createMock(DigitalProductVariationRepositoryInterface::class),
            $this->createMock(StorageRepositoryInterface::class),
            $this->createMock(POSService::class),
            $this->createMock(CartService::class),
            $this->createMock(PosCartStateService::class),
            $this->createMock(InventoryMutationService::class),
            $this->createMock(OrderDetailsService::class),
            $this->createMock(OrderService::class)
        );

        $method = new \ReflectionMethod($controller, 'validatePaymentType');
        $method->setAccessible(true);

        $this->expectException(ValidationException::class);
        $method->invoke($controller, 'wire_transfer');
    }
}
