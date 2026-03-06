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
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\WholesaleConfirmOrder;
use App\Models\WholesalePurchaseOrder;
use App\Models\WholesaleQuotation;
use App\Models\User;
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
        $vendorOrderEarningArray = $this->getOrderStatisticsData(from: $from, to: $to, range: $range, type: $type, userType: 'seller');
        $label = $dateTypeArray['keyRange'] ?? [];
        $inHouseOrderEarningArray = array_values($inHouseOrderEarningArray);
        $vendorOrderEarningArray = array_values($vendorOrderEarningArray);
        return response()->json([
            'view' => view(Dashboard::ORDER_STATISTICS[VIEW], compact('inHouseOrderEarningArray', 'vendorOrderEarningArray', 'label', 'dateType'))->render(),
        ]);
    }

    protected function getOrderStatisticsData($from, $to, $range, $type, $userType): array
    {
        if ($userType === 'seller') {
            $empty = [];
            foreach ($range as $value) {
                $empty[$value] = 0;
            }
            return $empty;
        }

        $orderEarnings = WholesaleConfirmOrder::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('IFNULL(sum(final_price),0) as sums, YEAR(created_at) year, MONTH(created_at) month, DAY(created_at) day, DAYNAME(created_at) day_of_week')
            ->groupBy('year', 'month', 'day', 'day_of_week')
            ->get();

        $orderEarningArray = [];
        foreach ($range as $value) {
            $matchingEarnings = $orderEarnings->where($type, $value);
            if ($matchingEarnings->count() > 0) {
                $orderEarningArray[$value] = usdToDefaultCurrency($matchingEarnings->sum('sums'));
            } else {
                $orderEarningArray[$value] = 0;
            }
        }

        return $orderEarningArray;
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

    public function revenueReport(): View
    {
        $snapshotFrom = now()->subDays(89)->startOfDay();
        $snapshotTo = now()->endOfDay();
        $trendStart = now()->copy()->startOfMonth()->subMonths(11);
        $trendEnd = now()->endOfDay();

        $snapshotQuery = WholesaleConfirmOrder::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo]);

        $totalOrders = (clone $snapshotQuery)->count();
        $totalRevenue = (float)(clone $snapshotQuery)->sum('final_price');
        $paidRevenue = (float)(clone $snapshotQuery)->where('payment_status', 'paid')->sum('final_price');
        $deliveredOrders = (clone $snapshotQuery)->where('delivery_status', 'delivered')->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $collectionRate = $totalRevenue > 0 ? ($paidRevenue / $totalRevenue) * 100 : 0;
        $fulfillmentRate = $totalOrders > 0 ? ($deliveredOrders / $totalOrders) * 100 : 0;
        $openRevenue = max(0, $totalRevenue - $paidRevenue);

        $trendRows = WholesaleConfirmOrder::query()
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(COALESCE(final_price, 0)) as total_revenue')
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' THEN COALESCE(final_price, 0) ELSE 0 END) as paid_revenue")
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn($row) => sprintf('%04d-%02d', (int)$row->year, (int)$row->month));

        $trendLabels = [];
        $trendRevenue = [];
        $trendPaidRevenue = [];
        $trendOrders = [];
        for ($monthIndex = 0; $monthIndex < 12; $monthIndex++) {
            $monthDate = $trendStart->copy()->addMonths($monthIndex);
            $monthKey = $monthDate->format('Y-m');
            $row = $trendRows->get($monthKey);

            $trendLabels[] = $monthDate->format('M Y');
            $trendRevenue[] = (float)($row->total_revenue ?? 0);
            $trendPaidRevenue[] = (float)($row->paid_revenue ?? 0);
            $trendOrders[] = (int)($row->orders_count ?? 0);
        }

        $deliveryRows = (clone $snapshotQuery)
            ->selectRaw("COALESCE(NULLIF(delivery_status, ''), 'pending') as delivery_status, COUNT(*) as total")
            ->groupBy('delivery_status')
            ->orderByDesc('total')
            ->get();

        $topWholesalers = WholesaleConfirmOrder::query()
            ->with(['wholeseller.wholesalerBusiness'])
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->select('wholesaler_id')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(COALESCE(final_price, 0)) as total_revenue')
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' THEN COALESCE(final_price, 0) ELSE 0 END) as paid_revenue")
            ->groupBy('wholesaler_id')
            ->orderByDesc('total_revenue')
            ->limit(8)
            ->get();

        $recentRevenue = (float)WholesaleConfirmOrder::query()
            ->whereBetween('created_at', [now()->subDays(29)->startOfDay(), $snapshotTo])
            ->sum('final_price');
        $previousRevenue = (float)WholesaleConfirmOrder::query()
            ->whereBetween('created_at', [now()->subDays(59)->startOfDay(), now()->subDays(30)->endOfDay()])
            ->sum('final_price');
        $momentumRate = $previousRevenue > 0 ? (($recentRevenue - $previousRevenue) / $previousRevenue) * 100 : null;

        $kpi = [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'paid_revenue' => $paidRevenue,
            'open_revenue' => $openRevenue,
            'avg_order_value' => $avgOrderValue,
            'collection_rate' => $collectionRate,
            'fulfillment_rate' => $fulfillmentRate,
        ];

        $trendChartData = [
            'labels' => $trendLabels,
            'revenue' => $trendRevenue,
            'paid_revenue' => $trendPaidRevenue,
            'orders' => $trendOrders,
        ];

        $deliveryChartData = [
            'labels' => $deliveryRows->map(fn($row) => ucfirst((string)$row->delivery_status))->values()->all(),
            'counts' => $deliveryRows->map(fn($row) => (int)$row->total)->values()->all(),
        ];

        $insights = $this->buildRevenueInsights(
            kpi: $kpi,
            trendLabels: $trendLabels,
            trendRevenue: $trendRevenue,
            deliveryRows: $deliveryRows->toArray(),
            momentumRate: $momentumRate,
            recentRevenue: $recentRevenue,
            previousRevenue: $previousRevenue
        );

        return view('admin-views.wholesaler-business.reports.revenue', compact(
            'kpi',
            'trendChartData',
            'deliveryChartData',
            'topWholesalers',
            'insights',
            'snapshotFrom',
            'snapshotTo'
        ));
    }

    public function pipelineReport(): View
    {
        $snapshotFrom = now()->subDays(89)->startOfDay();
        $snapshotTo = now()->endOfDay();
        $trendStart = now()->copy()->startOfMonth()->subMonths(5);
        $trendEnd = now()->endOfDay();

        $purchaseCount = WholesalePurchaseOrder::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->count();
        $quotationCount = WholesaleQuotation::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->count();
        $confirmedCount = WholesaleConfirmOrder::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->count();

        $purchaseToQuotationRate = $purchaseCount > 0 ? ($quotationCount / $purchaseCount) * 100 : 0;
        $quotationToConfirmedRate = $quotationCount > 0 ? ($confirmedCount / $quotationCount) * 100 : 0;
        $endToEndRate = $purchaseCount > 0 ? ($confirmedCount / $purchaseCount) * 100 : 0;

        $purchaseTrendRows = WholesalePurchaseOrder::query()
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->get();
        $quotationTrendRows = WholesaleQuotation::query()
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->get();
        $confirmedTrendRows = WholesaleConfirmOrder::query()
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->get();

        $purchaseTrendMap = $this->buildMonthlyCountMap($purchaseTrendRows->toArray());
        $quotationTrendMap = $this->buildMonthlyCountMap($quotationTrendRows->toArray());
        $confirmedTrendMap = $this->buildMonthlyCountMap($confirmedTrendRows->toArray());

        $trendLabels = [];
        $purchaseTrend = [];
        $quotationTrend = [];
        $confirmedTrend = [];
        for ($monthIndex = 0; $monthIndex < 6; $monthIndex++) {
            $monthDate = $trendStart->copy()->addMonths($monthIndex);
            $monthKey = $monthDate->format('Y-m');

            $trendLabels[] = $monthDate->format('M Y');
            $purchaseTrend[] = (int)($purchaseTrendMap[$monthKey] ?? 0);
            $quotationTrend[] = (int)($quotationTrendMap[$monthKey] ?? 0);
            $confirmedTrend[] = (int)($confirmedTrendMap[$monthKey] ?? 0);
        }

        $topProducts = DB::table('wholesale_confirmorder_item as item')
            ->join('wholesale_confirm_orders as orders', 'orders.id', '=', 'item.confirmed_order_id')
            ->leftJoin('products as products', 'products.id', '=', 'item.product_id')
            ->whereNull('orders.deleted_at')
            ->whereBetween('orders.created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw('item.product_id, products.name as product_name')
            ->selectRaw('SUM(CASE WHEN COALESCE(item.quantity_sent, 0) > 0 THEN item.quantity_sent ELSE COALESCE(item.product_quantity, 0) END) as total_quantity')
            ->selectRaw('SUM(COALESCE(item.final_price, 0)) as total_value')
            ->groupBy('item.product_id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(8)
            ->get();

        $tierMix = User::query()
            ->where('user_type', 1)
            ->where('wholesaler_status', 1)
            ->selectRaw("COALESCE(NULLIF(tier, ''), 'Unassigned') as tier_name, COUNT(*) as wholesaler_count")
            ->groupBy(DB::raw("COALESCE(NULLIF(tier, ''), 'Unassigned')"))
            ->orderByDesc('wholesaler_count')
            ->get();

        $tierRevenue = WholesaleConfirmOrder::query()
            ->join('users', 'users.id', '=', 'wholesale_confirm_orders.wholesaler_id')
            ->whereBetween('wholesale_confirm_orders.created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw("COALESCE(NULLIF(users.tier, ''), 'Unassigned') as tier_name")
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(COALESCE(wholesale_confirm_orders.final_price, 0)) as total_revenue')
            ->groupBy(DB::raw("COALESCE(NULLIF(users.tier, ''), 'Unassigned')"))
            ->orderByDesc('total_revenue')
            ->get();

        $avgPoToQuoteHoursRaw = DB::table('wholesale_purchase_orders as po')
            ->join('wholesale_quotations as q', 'q.purchase_order_no', '=', 'po.purchase_order_no')
            ->whereNull('po.deleted_at')
            ->whereNull('q.deleted_at')
            ->whereNotNull('po.purchase_order_no')
            ->where('po.purchase_order_no', '!=', '')
            ->whereBetween('q.created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, po.created_at, q.created_at)) as avg_hours')
            ->value('avg_hours');

        $avgQuoteToConfirmHoursRaw = DB::table('wholesale_quotations as q')
            ->join('wholesale_confirm_orders as co', 'co.quotation_no', '=', 'q.quotation_no')
            ->whereNull('q.deleted_at')
            ->whereNull('co.deleted_at')
            ->whereNotNull('q.quotation_no')
            ->where('q.quotation_no', '!=', '')
            ->whereBetween('co.created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, q.created_at, co.created_at)) as avg_hours')
            ->value('avg_hours');

        $avgPoToQuoteHours = $avgPoToQuoteHoursRaw !== null ? round((float)$avgPoToQuoteHoursRaw, 1) : null;
        $avgQuoteToConfirmHours = $avgQuoteToConfirmHoursRaw !== null ? round((float)$avgQuoteToConfirmHoursRaw, 1) : null;

        $topProductTotalQty = (float)$topProducts->sum('total_quantity');
        $topProductShare = $topProductTotalQty > 0 && $topProducts->isNotEmpty()
            ? ((float)$topProducts->first()->total_quantity / $topProductTotalQty) * 100
            : 0;

        $kpi = [
            'purchase_count' => $purchaseCount,
            'quotation_count' => $quotationCount,
            'confirmed_count' => $confirmedCount,
            'purchase_to_quotation_rate' => $purchaseToQuotationRate,
            'quotation_to_confirmed_rate' => $quotationToConfirmedRate,
            'end_to_end_rate' => $endToEndRate,
            'avg_po_to_quote_hours' => $avgPoToQuoteHours,
            'avg_quote_to_confirm_hours' => $avgQuoteToConfirmHours,
            'top_product_share' => $topProductShare,
        ];

        $pipelineStageChartData = [
            'labels' => ['Purchase Orders', 'Quotations', 'Confirmed Orders'],
            'counts' => [$purchaseCount, $quotationCount, $confirmedCount],
        ];

        $pipelineTrendChartData = [
            'labels' => $trendLabels,
            'purchase' => $purchaseTrend,
            'quotation' => $quotationTrend,
            'confirmed' => $confirmedTrend,
        ];

        $topProductsChartData = [
            'labels' => $topProducts->map(fn($row) => $row->product_name ?: ('Product #' . $row->product_id))->values()->all(),
            'quantities' => $topProducts->map(fn($row) => (float)$row->total_quantity)->values()->all(),
        ];

        $tierMixChartData = [
            'labels' => $tierMix->pluck('tier_name')->values()->all(),
            'counts' => $tierMix->pluck('wholesaler_count')->map(fn($value) => (int)$value)->values()->all(),
        ];

        $insights = $this->buildPipelineInsights(
            purchaseCount: $purchaseCount,
            quotationCount: $quotationCount,
            confirmedCount: $confirmedCount,
            purchaseToQuotationRate: $purchaseToQuotationRate,
            quotationToConfirmedRate: $quotationToConfirmedRate,
            endToEndRate: $endToEndRate,
            avgPoToQuoteHours: $avgPoToQuoteHours,
            avgQuoteToConfirmHours: $avgQuoteToConfirmHours,
            topProducts: $topProducts->toArray(),
            tierRevenue: $tierRevenue->toArray()
        );

        return view('admin-views.wholesaler-business.reports.pipeline', compact(
            'kpi',
            'pipelineStageChartData',
            'pipelineTrendChartData',
            'topProductsChartData',
            'tierMixChartData',
            'topProducts',
            'tierRevenue',
            'insights',
            'snapshotFrom',
            'snapshotTo'
        ));
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

    private function buildMonthlyCountMap(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $year = (int)data_get($row, 'year', 0);
            $month = (int)data_get($row, 'month', 0);
            if ($year > 0 && $month > 0) {
                $map[sprintf('%04d-%02d', $year, $month)] = (int)data_get($row, 'total', 0);
            }
        }
        return $map;
    }

    private function buildRevenueInsights(
        array $kpi,
        array $trendLabels,
        array $trendRevenue,
        array $deliveryRows,
        ?float $momentumRate,
        float $recentRevenue,
        float $previousRevenue
    ): array {
        if (($kpi['total_orders'] ?? 0) === 0) {
            return ['No confirmed wholesale orders were found in the last 90 days.'];
        }

        $insights = [];
        $insights[] = 'Last 90 days revenue reached ' . $this->formatMoney((float)$kpi['total_revenue'])
            . ' from ' . (int)$kpi['total_orders'] . ' confirmed orders.';

        $maxRevenue = max($trendRevenue);
        if ($maxRevenue > 0) {
            $bestMonthIndex = array_search($maxRevenue, $trendRevenue, true);
            if ($bestMonthIndex !== false && isset($trendLabels[$bestMonthIndex])) {
                $insights[] = 'Peak month was ' . $trendLabels[$bestMonthIndex]
                    . ' at ' . $this->formatMoney((float)$maxRevenue) . '.';
            }
        }

        if ($momentumRate !== null) {
            $direction = $momentumRate >= 0 ? 'up' : 'down';
            $insights[] = 'Revenue momentum is ' . $direction . ' '
                . number_format(abs($momentumRate), 1) . '% (last 30 days vs previous 30 days).';
        } elseif ($recentRevenue > 0 && $previousRevenue == 0.0) {
            $insights[] = 'Revenue appeared this month while the previous 30-day window had no revenue.';
        }

        $insights[] = 'Collection rate is ' . number_format((float)$kpi['collection_rate'], 1) . '%, leaving '
            . $this->formatMoney((float)$kpi['open_revenue']) . ' still open.';

        if (!empty($deliveryRows)) {
            $largestBucket = $deliveryRows[0];
            $bucketStatus = ucfirst((string)data_get($largestBucket, 'delivery_status', 'pending'));
            $bucketTotal = (int)data_get($largestBucket, 'total', 0);
            $insights[] = 'Largest delivery bucket is ' . $bucketStatus . ' with ' . $bucketTotal . ' orders.';
        }

        return $insights;
    }

    private function buildPipelineInsights(
        int $purchaseCount,
        int $quotationCount,
        int $confirmedCount,
        float $purchaseToQuotationRate,
        float $quotationToConfirmedRate,
        float $endToEndRate,
        ?float $avgPoToQuoteHours,
        ?float $avgQuoteToConfirmHours,
        array $topProducts,
        array $tierRevenue
    ): array {
        if ($purchaseCount === 0 && $quotationCount === 0 && $confirmedCount === 0) {
            return ['No wholesale pipeline activity was found in the last 90 days.'];
        }

        $insights = [];
        $insights[] = 'Pipeline conversion is '
            . number_format($purchaseToQuotationRate, 1) . '% (PO to quote), '
            . number_format($quotationToConfirmedRate, 1) . '% (quote to confirmed), and '
            . number_format($endToEndRate, 1) . '% end-to-end.';

        $dropFromPurchase = max(0, $purchaseCount - $quotationCount);
        $dropFromQuote = max(0, $quotationCount - $confirmedCount);
        $insights[] = 'Current stage leakage: ' . $dropFromPurchase
            . ' records between purchase and quotation, ' . $dropFromQuote
            . ' between quotation and confirmation.';

        if ($avgPoToQuoteHours !== null || $avgQuoteToConfirmHours !== null) {
            $poToQuoteText = $avgPoToQuoteHours !== null ? number_format($avgPoToQuoteHours, 1) . 'h' : 'n/a';
            $quoteToConfirmText = $avgQuoteToConfirmHours !== null ? number_format($avgQuoteToConfirmHours, 1) . 'h' : 'n/a';
            $insights[] = 'Cycle time averages: PO→Quote ' . $poToQuoteText
                . ', Quote→Confirmed ' . $quoteToConfirmText . '.';
        }

        if (!empty($topProducts)) {
            $topProduct = $topProducts[0];
            $topProductName = data_get($topProduct, 'product_name') ?: ('Product #' . data_get($topProduct, 'product_id', 'N/A'));
            $insights[] = 'Top shipped product is ' . $topProductName
                . ' with ' . number_format((float)data_get($topProduct, 'total_quantity', 0), 0)
                . ' units in the period.';
        }

        if (!empty($tierRevenue)) {
            $topTier = $tierRevenue[0];
            $insights[] = 'Highest grossing tier is ' . data_get($topTier, 'tier_name', 'Unassigned')
                . ' at ' . $this->formatMoney((float)data_get($topTier, 'total_revenue', 0)) . '.';
        }

        return $insights;
    }

    private function formatMoney(float $value): string
    {
        return number_format($value, 2);
    }

    public function getRealTimeActivities(): JsonResponse
    {
        $newOrder = $this->orderRepo->getListWhere(filters: ['delivery_status' => 'pending'], dataLimit: 'all')->count();
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
