<?php

namespace Tests\Unit;

use App\Contracts\Repositories\AdminWalletRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\OrderTransactionRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Contracts\Repositories\WholeSalerRepositoryInterface;
use App\Contracts\Repositories\WholesaleOrderRepositoryInterface;
use App\Contracts\Repositories\WholesaleproductsRepositoryInterface;
use App\Http\Controllers\Admin\WholeSaler\WholesaleDashboardController;
use App\Services\DashboardService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class WholesaleDashboardControllerTest extends TestCase
{
    public function test_get_order_status_data_returns_product_count_without_loading_relations(): void
    {
        $orderRepo = $this->createMock(WholesaleOrderRepositoryInterface::class);
        $productRepo = $this->createMock(WholesaleproductsRepositoryInterface::class);
        $customerRepo = $this->createMock(WholeSalerRepositoryInterface::class);

        $orders = new Collection([1, 2, 3]);
        $products = new Collection([1, 2]);
        $customers = new Collection([1]);

        $orderRepo->expects($this->exactly(5))
            ->method('getListWhere')
            ->willReturnCallback(function (
                array $orderBy = [],
                ?string $searchValue = null,
                array $filters = [],
                array $relations = [],
                int|string $dataLimit = DEFAULT_DATA_LIMIT,
                ?int $offset = null
            ) use ($orders) {
                $this->assertSame([], $orderBy);
                $this->assertNull($searchValue);
                $this->assertSame([], $relations);
                $this->assertSame('all', $dataLimit);
                $this->assertNull($offset);
                $this->assertContains($filters, [
                    [],
                    ['status' => 'rejected'],
                    ['status' => 'confirmed'],
                    ['delivery_status' => 'delivered'],
                    ['delivery_status' => 'partials'],
                ]);

                return $orders;
            });
        $orderRepo->expects($this->once())
            ->method('getQuotationListWhere')
            ->with([], null, [], [], 'all', null)
            ->willReturn($orders);
        $orderRepo->expects($this->once())
            ->method('getPurchaseListWhere')
            ->with([], null, [], [], 'all', null)
            ->willReturn($orders);

        $productRepo->expects($this->once())
            ->method('getListWhere')
            ->with([], null, [], [], 'all', null)
            ->willReturn($products);

        $customerRepo->expects($this->once())
            ->method('getListWhere')
            ->with([], null, [], [], 'all', null)
            ->willReturn($customers);

        $controller = new WholesaleDashboardController(
            $this->createMock(AdminWalletRepositoryInterface::class),
            $customerRepo,
            $this->createMock(OrderTransactionRepositoryInterface::class),
            $productRepo,
            $this->createMock(DeliveryManRepositoryInterface::class),
            $orderRepo,
            $this->createMock(BrandRepositoryInterface::class),
            $this->createMock(VendorRepositoryInterface::class),
            $this->createMock(VendorWalletRepositoryInterface::class),
            $this->createMock(RestockProductRepositoryInterface::class),
            $this->createMock(DashboardService::class),
        );

        $data = $controller->getOrderStatusData();

        $this->assertSame(2, $data['product']);
        $this->assertSame(3, $data['order']);
        $this->assertSame(1, $data['customer']);
    }
}
