<?php

namespace App\Http\Controllers\Admin\WholeSaler;

use App\Contracts\Repositories\AdminWalletRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\WholeSalerRepositoryInterface;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\WholesaleOrderRepositoryInterface;
use App\Contracts\Repositories\OrderTransactionRepositoryInterface;
use App\Contracts\Repositories\WholesaleproductsRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Enums\ViewPaths\Admin\Dashboard;
use App\Http\Controllers\BaseController;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Branch;
use App\Enums\ViewPaths\Admin\WholeSaler;


class WholesaleDashboardController extends BaseController
{
    public function __construct(
        private readonly AdminWalletRepositoryInterface       $adminWalletRepo,
        private readonly WholeSalerRepositoryInterface          $customerRepo,
        private readonly OrderTransactionRepositoryInterface  $orderTransactionRepo,
        private readonly WholesaleproductsRepositoryInterface $productRepo,
        private readonly DeliveryManRepositoryInterface       $deliveryManRepo,
        private readonly WholesaleOrderRepositoryInterface    $orderRepo,
        private readonly BrandRepositoryInterface             $brandRepo,
        private readonly VendorRepositoryInterface            $vendorRepo,
        private readonly VendorWalletRepositoryInterface      $vendorWalletRepo,
        private readonly RestockProductRepositoryInterface    $restockProductRepo,
        private readonly DashboardService                     $dashboardService,
    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->dashboard();
    }

    public function dashboard(): View
    {
        $topWholesaler = $this->orderRepo->getTopWholesalerList(relations: ['wholeseller'], dataLimit: 'all')->take(DASHBOARD_DATA_LIMIT);
        $topSellProduct = $this->productRepo->getTopSellingWholesaleProducts(
            relations: ['product', 'category', 'subcategory']
        )->take(DASHBOARD_TOP_SELL_DATA_LIMIT);

        $data = self::getOrderStatusData();
        $from = now()->startOfYear()->format('Y-m-d');
        $to = now()->endOfYear()->format('Y-m-d');
        $range = range(1, 12);
        $label = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $dateType = 'yearEarn';
        $data += [
            'order' => $this->orderRepo->getListWhere(dataLimit: 'all')->count(),
            'brand' => $this->brandRepo->getListWhere(dataLimit: 'all')->count(),
            'topSellProduct' => $topSellProduct,
            'top_wholesaler' => $topWholesaler,
            'getTotalCustomerCount' => $this->customerRepo->getList(dataLimit: 'all')->count(),
        ];

        return view(wholesaler::DASHBOARD[VIEW], compact('data', 'label', 'dateType'));
    }

    public function getOrderStatus(Request $request): JsonResponse
    {
        session()->put('statistics_type', $request['statistics_type']);
        $data = self::getOrderStatusData();
        return response()->json(['view' => view('admin-views.partials._dashboard-order-status', compact('data'))->render()], 200);
    }

    public function getOrderStatusData(): array
    {
        $orderQuery = $this->orderRepo->getListWhere(dataLimit: 'all');
        $quotationQuery = $this->orderRepo->getQuotationListWhere(dataLimit: 'all');
        $purchaseOrderrderQuery = $this->orderRepo->getPurchaseListWhere(dataLimit: 'all');
        $productQuery = $this->productRepo->getListWhere(dataLimit: 'all');
        $customerQuery = $this->customerRepo->getListWhere(dataLimit: 'all');
        $rejectedQuery = $this->orderRepo->getListWhere(filters: ['status' => 'rejected'], dataLimit: 'all');
        $confirmedQuery = $this->orderRepo->getListWhere(filters: ['status' => 'confirmed'], dataLimit: 'all');
        $deliveredQuery = $this->orderRepo->getListWhere(filters: ['delivery_status' => 'delivered'], dataLimit: 'all');
        $partialsQuery = $this->orderRepo->getListWhere(filters: ['delivery_status' => 'partials'], dataLimit: 'all');

        return [
            'order' => self::getCommonQueryOrderStatus($orderQuery),
            'purchase' => self::getCommonQueryOrderStatus($purchaseOrderrderQuery),
            'quotation' => self::getCommonQueryOrderStatus($quotationQuery),
            'product' => self::getCommonQueryOrderStatus($productQuery),
            'customer' => self::getCommonQueryOrderStatus($customerQuery),
            'rejected' => self::getCommonQueryOrderStatus($rejectedQuery),
            'confirmed' => self::getCommonQueryOrderStatus($confirmedQuery),
            'partials' => self::getCommonQueryOrderStatus($partialsQuery),
            'delivered' => self::getCommonQueryOrderStatus($deliveredQuery),
        ];
    }

    public function getCommonQueryOrderStatus($query)
    {
        $today = session()->has('statistics_type') && session('statistics_type') == 'today' ? 1 : 0;
        $this_month = session()->has('statistics_type') && session('statistics_type') == 'this_month' ? 1 : 0;

        return $query->when($today, function ($query) {
            return $query->where('created_at', '>=', now()->startOfDay())
                ->where('created_at', '<', now()->endOfDay());
        })->when($this_month, function ($query) {
            return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        })->count();
    }
    public function getOrderStatistics(Request $request): JsonResponse
    {
        $dateType = $request['type'];
        $dateTypeArray = $this->dashboardService->getDateTypeData(dateType: $dateType);
        $from = $dateTypeArray['from'];
        $to = $dateTypeArray['to'];
        $type = $dateTypeArray['type'];
        $range = $dateTypeArray['range'];
        $inHouseOrderEarningArray = $this->getOrderStatisticsData(from: $from, to: $to, range: $range, type: $type, userType: 'admin');
        $label = $dateTypeArray['keyRange'] ?? [];
        $inHouseOrderEarningArray = array_values($inHouseOrderEarningArray);
        $vendorOrderEarningArray = array_values($vendorOrderEarningArray);
        return response()->json([
            'view' => view(Dashboard::ORDER_STATISTICS[VIEW], compact('inHouseOrderEarningArray', 'vendorOrderEarningArray', 'label', 'dateType'))->render(),
        ]);
    }
    public function getEarningStatistics(Request $request): JsonResponse
    {
        $dateType = $request['type'];
        $dateTypeArray = $this->dashboardService->getDateTypeData(dateType: $dateType);
        $from = $dateTypeArray['from'];
        $to = $dateTypeArray['to'];
        $type = $dateTypeArray['type'];
        $range = $dateTypeArray['range'];
        $inHouseEarning = $this->getEarning(from: $from, to: $to, range: $range, type: $type, userType: 'admin');
        $vendorEarning = $this->getEarning(from: $from, to: $to, range: $range, type: $type, userType: 'seller');
        $commissionEarn = $this->getAdminCommission(from: $from, to: $to, range: $range, type: $type);
        $label = $dateTypeArray['keyRange'] ?? [];
        $inHouseEarning = array_values($inHouseEarning);
        $vendorEarning = array_values($vendorEarning);
        $commissionEarn = array_values($commissionEarn);
        return response()->json([
            'view' => view(Dashboard::EARNING_STATISTICS[VIEW], compact('inHouseEarning', 'vendorEarning', 'commissionEarn', 'label', 'dateType'))->render(),
        ]);
    }


    protected function getEarning(string|Carbon $from, string|Carbon $to, array $range, string $type, $userType): array
    {
        $earning = $this->orderTransactionRepo->getListWhereBetween(
            filters: [
                'seller_is' => $userType,
                'status' => 'disburse',
            ],
            selectColumn: 'seller_amount',
            whereBetween: 'created_at',
            groupBy: $type,
            whereBetweenFilters: [$from, $to],
        );
        return $this->dashboardService->getDateWiseAmount(range: $range, type: $type, amountArray: $earning);
    }

    /**
     * @param string|Carbon $from
     * @param string|Carbon $to
     * @param array $range
     * @param string $type
     * @return array
     */
    protected function getAdminCommission(string|Carbon $from, string|Carbon $to, array $range, string $type): array
    {
        $commissionGiven = $this->orderTransactionRepo->getListWhereBetween(
            filters: [
                'seller_is' => 'seller',
                'status' => 'disburse',
            ],
            selectColumn: 'admin_commission',
            whereBetween: 'created_at',
            groupBy: $type,
            whereBetweenFilters: [$from, $to],
        );
        return $this->dashboardService->getDateWiseAmount(range: $range, type: $type, amountArray: $commissionGiven);
    }

    public function getRealTimeActivities(): JsonResponse
    {
        $newOrder = $this->orderRepo->getListWhere(filters: ['checked' => 0], dataLimit: 'all')->count();
        $restockProductList = $this->restockProductRepo->getListWhere(filters: ['added_by' => 'in_house'], dataLimit: 'all')->groupBy('product_id');
        $restockProduct = [];
        if (count($restockProductList) == 1) {
            $products = $this->restockProductRepo->getListWhere(orderBy: ['updated_at' => 'desc'], filters: ['added_by' => 'in_house'], relations: ['product'], dataLimit: 'all');
            $firstProduct = $products->first();
            $count = $products?->sum('restock_product_customers_count') ?? 0;
            $restockProduct = [
                'title' => $firstProduct?->product?->name ?? '',
                'body' => $count < 100 ? translate('This_product_has') . ' ' . $count . ' ' . translate('restock_request') : translate('This_product_has') . ' 99+ ' . translate('restock_request'),
                'image' => getStorageImages(path: $firstProduct?->product?->thumbnail_full_url ?? '', type: 'product'),
                'route' => route('admin.products.request-restock-list')
            ];
        } elseif (count($restockProductList) > 1) {
            $restockProduct = [
                'title' => translate('Restock_Request'),
                'body' => count($restockProductList) < 100 ? (count($restockProductList) . ' ' . translate('products_have_restock_request')) : ('99 +' . ' ' . translate('more_products_have_restock_request')),
                'image' => dynamicAsset(path: 'public/assets/back-end/img/icons/restock-request-icon.svg'),
                'route' => route('admin.products.request-restock-list')
            ];
        }

        return response()->json([
            'success' => 1,
            'new_order_count' => $newOrder,
            'restockProductCount' => $restockProductList->count(),
            'restockProduct' => $restockProduct
        ]);
    }
}
