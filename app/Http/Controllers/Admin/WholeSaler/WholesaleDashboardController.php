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
use App\Services\ReportPdfService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Branch;
use App\Models\WholesaleConfirmOrder;
use App\Models\WholesalePurchaseOrder;
use App\Models\WholesaleQuotation;
use App\Models\User;
use App\Enums\ViewPaths\Admin\WholeSaler;
use Maatwebsite\Excel\Concerns\WithStyles;

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
            relations: ['product.translations', 'category.translations', 'subcategory.translations']
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

    public function revenueReport(Request $request): View|BinaryFileResponse|Response
    {
        [$snapshotFrom, $snapshotTo] = $this->resolveReportDateRange($request);
        $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';

        if ($isRtl) {
            $snapshotFromDisplay = $snapshotFrom->translatedFormat('d F Y');
            $snapshotToDisplay   = $snapshotTo->translatedFormat('d F Y');
        } else {
            $snapshotFromDisplay = $snapshotFrom->format('d M, Y');
            $snapshotToDisplay   = $snapshotTo->format('d M, Y');
        }
        $filters = [
            'date_type' => (string)$request->input('date_type', 'this_year'),
            'from' => $snapshotFrom->toDateString(),
            'to' => $snapshotTo->toDateString(),
            'payment_status' => (string)$request->input('payment_status', ''),
            'delivery_status' => (string)$request->input('delivery_status', ''),
            'wholesaler_id' => (int)$request->input('wholesaler_id', 0),
        ];
        $trendGrouping = $this->resolveReportTrendGrouping($snapshotFrom, $snapshotTo);
        $periodKeys = $this->buildReportPeriodKeys($snapshotFrom, $snapshotTo, $trendGrouping['unit']);

        $snapshotQuery = WholesaleConfirmOrder::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo]);
        $this->applyRevenueFilters($snapshotQuery, $filters);

        $totalOrders = (clone $snapshotQuery)->count();
        $totalRevenue = (float)(clone $snapshotQuery)->sum('final_price');
        $paidRevenue = (float)(clone $snapshotQuery)->where('payment_status', 'paid')->sum('final_price');
        $deliveredOrdersQuery = clone $snapshotQuery;
        $this->applyFulfilledDeliveryStatusFilter($deliveredOrdersQuery);
        $deliveredOrders = $deliveredOrdersQuery->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $collectionRate = $totalRevenue > 0 ? ($paidRevenue / $totalRevenue) * 100 : 0;
        $fulfillmentRate = $totalOrders > 0 ? ($deliveredOrders / $totalOrders) * 100 : 0;
        $openRevenue = max(0, $totalRevenue - $paidRevenue);
        $dateRange = $snapshotFrom->translatedFormat('d M, Y') . ' - ' . $snapshotTo->translatedFormat('d M, Y');
        $trendRows = WholesaleConfirmOrder::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw($trendGrouping['select'] . ' as period_key')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(COALESCE(final_price, 0)) as total_revenue')
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' THEN COALESCE(final_price, 0) ELSE 0 END) as paid_revenue")
            ->when($filters['payment_status'] !== '', fn($query) => $query->where('payment_status', $filters['payment_status']))
            ->when(($filters['delivery_status'] ?? '') !== '', fn($query) => $this->applyDeliveryStatusFilter($query, (string)$filters['delivery_status']))
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('wholesaler_id', $filters['wholesaler_id']))
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get()
            ->keyBy('period_key');

        $trendLabels = [];
        $trendRevenue = [];
        $trendPaidRevenue = [];
        $trendOrders = [];
        foreach ($periodKeys as $periodKey) {
            $row = $trendRows->get($periodKey);
            $trendLabels[] = $this->formatReportPeriodLabel($periodKey, $trendGrouping['unit']);
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
            ->when($filters['payment_status'] !== '', fn($query) => $query->where('payment_status', $filters['payment_status']))
            ->when(($filters['delivery_status'] ?? '') !== '', fn($query) => $this->applyDeliveryStatusFilter($query, (string)$filters['delivery_status']))
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('wholesaler_id', $filters['wholesaler_id']))
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
            ->when($filters['payment_status'] !== '', fn($query) => $query->where('payment_status', $filters['payment_status']))
            ->when(($filters['delivery_status'] ?? '') !== '', fn($query) => $this->applyDeliveryStatusFilter($query, (string)$filters['delivery_status']))
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('wholesaler_id', $filters['wholesaler_id']))
            ->sum('final_price');
        $previousRevenue = (float)WholesaleConfirmOrder::query()
            ->whereBetween('created_at', [now()->subDays(59)->startOfDay(), now()->subDays(30)->endOfDay()])
            ->when($filters['payment_status'] !== '', fn($query) => $query->where('payment_status', $filters['payment_status']))
            ->when(($filters['delivery_status'] ?? '') !== '', fn($query) => $this->applyDeliveryStatusFilter($query, (string)$filters['delivery_status']))
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('wholesaler_id', $filters['wholesaler_id']))
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
            previousRevenue: $previousRevenue,
            snapshotFrom: $snapshotFrom,
            snapshotTo: $snapshotTo
        );

        $download = (string)$request->input('download', '');
        if ($download === 'excel') {
            $rows = $topWholesalers->map(function ($row) use ($isRtl) {

                $collection = $row->total_revenue > 0
                    ? round(($row->paid_revenue / $row->total_revenue) * 100, 1) . '%'
                    : '0%';

                $data = [
                    (string)($row->wholeseller?->name ?? 'N/A'),
                    (string)($row->wholeseller?->wholesalerBusiness?->company_name ?? 'N/A'),
                    (int)$row->orders_count,
                    round((float)$row->total_revenue, 2),
                    $collection
                ];

                return $isRtl ? array_reverse($data) : $data;
            })->values()->all();
            return Excel::download(new class($rows) implements FromArray, WithHeadings, WithStyles, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
                public function __construct(private readonly array $rows) {}
                public function array(): array
                {
                    return $this->rows;
                }
                public function headings(): array
                {
                    $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';

                    if ($isRtl) {
                        return [
                            translate('collection'),
                            translate('revenue'),
                            translate('Orders'),
                            translate('Company'),
                            translate('Wholesaler'),
                        ];
                    }

                    return [
                        translate('Wholesaler'),
                        translate('Company'),
                        translate('Orders'),
                        translate('revenue'),
                        translate('collection'),
                    ];
                }
                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    return [
                        1 => [
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '239e92'] // Your Seafoam Green
                            ],
                            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                        ],
                    ];
                }
            }, 'wholesale-revenue-report.xlsx');
        }

        if ($download === 'pdf') {
            $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';

            // Get chart images from request (sent via POST from frontend)
            $revenueTrendChartImage = $request->input('trend_chart');
            $deliveryStatusChartImage = $request->input('delivery_chart');

            return app(ReportPdfService::class)->download(
                view: 'admin-views.wholesaler-business.reports.revenue-pdf',
                data: array_merge(
                    compact(
                        'kpi',
                        'topWholesalers',
                        'snapshotFromDisplay',
                        'snapshotToDisplay',
                        'isRtl',
                        'insights',
                        'revenueTrendChartImage',
                        'deliveryStatusChartImage'
                    ),
                    [
                        'report_title' => translate('wholesale_revenue_report')
                    ]
                ),
                fileName: 'wholesale-revenue-report.pdf'
            );
        }

        $wholesalers = User::query()->where('user_type', 1)->where('wholesaler_status', 1)->select('id', 'name')->orderBy('name')->get();

        return view('admin-views.wholesaler-business.reports.revenue', compact(
            'kpi',
            'trendChartData',
            'deliveryChartData',
            'topWholesalers',
            'insights',
            'snapshotFrom',
            'snapshotTo',
            'filters',
            'wholesalers',
            'dateRange'
        ));
    }

    public function pipelineReport(Request $request): View|BinaryFileResponse|Response
    {
        [$snapshotFrom, $snapshotTo] = $this->resolveReportDateRange($request);
        $dateRange = $snapshotFrom->format('d M, Y') . ' - ' . $snapshotTo->format('d M, Y');
        $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';

        $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';

        if ($isRtl) {
            $snapshotFromDisplay = $snapshotFrom->translatedFormat('d F Y');
            $snapshotToDisplay   = $snapshotTo->translatedFormat('d F Y');
        } else {
            $snapshotFromDisplay = $snapshotFrom->format('d M, Y');
            $snapshotToDisplay   = $snapshotTo->format('d M, Y');
        }
        $filters = [
            'date_type' => (string)$request->input('date_type', 'this_year'),
            'from' => $snapshotFrom->toDateString(),
            'to' => $snapshotTo->toDateString(),
            'wholesaler_id' => (int)$request->input('wholesaler_id', 0),
            'tier' => (string)$request->input('tier', ''),
        ];
        $trendGrouping = $this->resolveReportTrendGrouping($snapshotFrom, $snapshotTo);
        $periodKeys = $this->buildReportPeriodKeys($snapshotFrom, $snapshotTo, $trendGrouping['unit']);

        $purchaseCount = WholesalePurchaseOrder::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('wholeseller_id', $filters['wholesaler_id']))
            ->when($filters['tier'] !== '', fn($query) => $query->where('wholeseller_tier', $filters['tier']))
            ->count();
        $quotationCount = WholesaleQuotation::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('wholeseller_id', $filters['wholesaler_id']))
            ->when($filters['tier'] !== '', fn($query) => $query->where('wholeseller_tier', $filters['tier']))
            ->count();
        $confirmedCount = WholesaleConfirmOrder::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('wholesaler_id', $filters['wholesaler_id']))
            ->count();

        $purchaseToQuotationRate = $purchaseCount > 0 ? ($quotationCount / $purchaseCount) * 100 : 0;
        $quotationToConfirmedRate = $quotationCount > 0 ? ($confirmedCount / $quotationCount) * 100 : 0;
        $endToEndRate = $purchaseCount > 0 ? ($confirmedCount / $purchaseCount) * 100 : 0;

        $purchaseTrendRows = WholesalePurchaseOrder::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('wholeseller_id', $filters['wholesaler_id']))
            ->when($filters['tier'] !== '', fn($query) => $query->where('wholeseller_tier', $filters['tier']))
            ->selectRaw($trendGrouping['select'] . ' as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->get();
        $quotationTrendRows = WholesaleQuotation::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('wholeseller_id', $filters['wholesaler_id']))
            ->when($filters['tier'] !== '', fn($query) => $query->where('wholeseller_tier', $filters['tier']))
            ->selectRaw($trendGrouping['select'] . ' as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->get();
        $confirmedTrendRows = WholesaleConfirmOrder::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('wholesaler_id', $filters['wholesaler_id']))
            ->selectRaw($trendGrouping['select'] . ' as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->get();

        $purchaseTrendMap = $purchaseTrendRows->pluck('total', 'period_key')->all();
        $quotationTrendMap = $quotationTrendRows->pluck('total', 'period_key')->all();
        $confirmedTrendMap = $confirmedTrendRows->pluck('total', 'period_key')->all();

        $trendLabels = [];
        $purchaseTrend = [];
        $quotationTrend = [];
        $confirmedTrend = [];
        foreach ($periodKeys as $periodKey) {
            $trendLabels[] = $this->formatReportPeriodLabel($periodKey, $trendGrouping['unit']);
            $purchaseTrend[] = (int)($purchaseTrendMap[$periodKey] ?? 0);
            $quotationTrend[] = (int)($quotationTrendMap[$periodKey] ?? 0);
            $confirmedTrend[] = (int)($confirmedTrendMap[$periodKey] ?? 0);
        }

        $topProducts = DB::table('wholesale_confirmorder_item as item')
            ->join('wholesale_confirm_orders as orders', 'orders.id', '=', 'item.confirmed_order_id')
            ->leftJoin('products as products', 'products.id', '=', 'item.product_id')
            ->whereNull('orders.deleted_at')
            ->whereBetween('orders.created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('orders.wholesaler_id', $filters['wholesaler_id']))
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
            ->groupBy('tier_name')
            ->orderByDesc('wholesaler_count')
            ->get();

        $tierRevenue = WholesaleConfirmOrder::query()
            ->join('users', 'users.id', '=', 'wholesale_confirm_orders.wholesaler_id')
            ->whereBetween('wholesale_confirm_orders.created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['wholesaler_id'] > 0, fn($query) => $query->where('wholesale_confirm_orders.wholesaler_id', $filters['wholesaler_id']))
            ->when($filters['tier'] !== '', fn($query) => $query->where('users.tier', $filters['tier']))
            ->selectRaw("COALESCE(NULLIF(users.tier, ''), 'Unassigned') as tier_name")
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(COALESCE(wholesale_confirm_orders.final_price, 0)) as total_revenue')
            ->groupBy('tier_name')
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

        $download = (string)$request->input('download', '');
        if ($download === 'excel') {

            $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';

            $rows = $tierRevenue->map(function ($row) use ($isRtl) {

                $data = [
                    (string)$row->tier_name,
                    (int)$row->orders_count,
                    round((float)$row->total_revenue, 2)
                ];

                return $isRtl ? array_reverse($data) : $data;
            })->values()->all();

            return Excel::download(new class($rows, $isRtl) implements
                FromArray,
                WithHeadings,
                WithStyles,
                \Maatwebsite\Excel\Concerns\ShouldAutoSize
            {

                public function __construct(
                    private readonly array $rows,
                    private readonly bool $isRtl
                ) {}

                public function array(): array
                {
                    return $this->rows;
                }

                public function headings(): array
                {
                    if ($this->isRtl) {
                        return [
                            translate('revenue'),
                            translate('Orders'),
                            translate('Tier'),
                        ];
                    }

                    return [
                        translate('Tier'),
                        translate('Orders'),
                        translate('revenue'),
                    ];
                }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    if ($this->isRtl) {
                        $sheet->setRightToLeft(true);
                    }

                    $sheet->getStyle('A1:C1')->getFill()->applyFromArray([
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '239e92']
                    ]);

                    return [
                        1 => [
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'],
                                'size' => 12
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                        ],
                    ];
                }
            }, 'wholesale-pipeline-report.xlsx');
        }
        if ($download === 'pdf') {
            $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';

            // Get chart images from request (sent via POST from frontend)
            $stageSnapshotChartImage = $request->input('stage_snapshot_chart');
            $pipelineTrendChartImage = $request->input('pipeline_trend_chart');
            $topProductsChartImage = $request->input('top_products_chart');
            $tierMixChartImage = $request->input('tier_mix_chart');

            return app(ReportPdfService::class)->download(
                view: 'admin-views.wholesaler-business.reports.pipeline-pdf',
                data: array_merge(
                    compact(
                        'kpi',
                        'tierRevenue',
                        'snapshotFrom',
                        'snapshotTo',
                        'snapshotFromDisplay',
                        'snapshotToDisplay',
                        'isRtl',
                        'insights',
                        'stageSnapshotChartImage',
                        'pipelineTrendChartImage',
                        'topProductsChartImage',
                        'tierMixChartImage'
                    ),
                    [
                        'report_title' => translate('wholesale_pipeline_reportt')
                    ]
                ),
                fileName: 'wholesale-pipeline-report.pdf'
            );
        }

        $wholesalers = User::query()->where('user_type', 1)->where('wholesaler_status', 1)->select('id', 'name', 'tier')->orderBy('name')->get();
        $tiers = $wholesalers->pluck('tier')->filter()->unique()->values();

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
            'snapshotTo',
            'snapshotFromDisplay',
            'snapshotToDisplay',
            'filters',
            'wholesalers',
            'tiers'
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

    private function resolveReportDateRange(Request $request): array
    {
        $dateType = (string)$request->input('date_type', 'this_year');
        $from = $request->input('from');
        $to = $request->input('to');

        switch ($dateType) {
            case 'this_month':
                $fromDate = now()->startOfMonth()->startOfDay();
                $toDate = now()->endOfMonth()->endOfDay();
                break;
            case 'this_week':
                $fromDate = now()->startOfWeek()->startOfDay();
                $toDate = now()->endOfWeek()->endOfDay();
                break;
            case 'today':
                $fromDate = now()->startOfDay();
                $toDate = now()->endOfDay();
                break;
            case 'custom_date':
                $fromDate = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(29)->startOfDay();
                $toDate = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
                break;
            case 'this_year':
            default:
                $fromDate = now()->startOfYear()->startOfDay();
                $toDate = now()->endOfYear()->endOfDay();
                break;
        }

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        return [$fromDate, $toDate];
    }

    private function applyRevenueFilters($query, array $filters): void
    {
        if (($filters['payment_status'] ?? '') !== '') {
            $query->where('payment_status', $filters['payment_status']);
        }
        if (($filters['delivery_status'] ?? '') !== '') {
            $this->applyDeliveryStatusFilter($query, (string)$filters['delivery_status']);
        }
        if (($filters['wholesaler_id'] ?? 0) > 0) {
            $query->where('wholesaler_id', (int)$filters['wholesaler_id']);
        }
    }

    private function applyFulfilledDeliveryStatusFilter($query): void
    {
        $query->whereIn('delivery_status', ['fulfilled', 'delivered']);
    }

    private function applyDeliveryStatusFilter($query, string $deliveryStatus): void
    {
        $status = strtolower(trim($deliveryStatus));
        if ($status === '') {
            return;
        }

        if ($status === 'partial') {
            $query->whereIn('delivery_status', ['partial', 'partials']);
            return;
        }

        if ($status === 'fulfilled') {
            $this->applyFulfilledDeliveryStatusFilter($query);
            return;
        }

        $query->where('delivery_status', $status);
    }

    private function resolveReportTrendGrouping(Carbon $fromDate, Carbon $toDate): array
    {
        $days = $fromDate->diffInDays($toDate);
        if ($days <= 31) {
            return ['unit' => 'day', 'select' => 'DATE(created_at)'];
        }
        if ($days <= 180) {
            return ['unit' => 'week', 'select' => "DATE_FORMAT(created_at, '%x-W%v')"];
        }
        return ['unit' => 'month', 'select' => "DATE_FORMAT(created_at, '%Y-%m')"];
    }

    private function buildReportPeriodKeys(Carbon $fromDate, Carbon $toDate, string $unit): array
    {
        $keys = [];
        $cursor = $fromDate->copy();
        if ($unit === 'day') {
            while ($cursor->lte($toDate)) {
                $keys[] = $cursor->format('Y-m-d');
                $cursor->addDay();
            }
            return $keys;
        }
        if ($unit === 'week') {
            $cursor = $fromDate->copy()->startOfWeek();
            $limit = $toDate->copy()->endOfWeek();
            while ($cursor->lte($limit)) {
                $keys[] = $cursor->format('o-\WW');
                $cursor->addWeek();
            }
            return $keys;
        }
        $cursor = $fromDate->copy()->startOfMonth();
        $limit = $toDate->copy()->endOfMonth();
        while ($cursor->lte($limit)) {
            $keys[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }
        return $keys;
    }

    private function formatReportPeriodLabel(string $periodKey, string $unit): string
    {
        if ($unit === 'day') {
            return Carbon::parse($periodKey)->translatedFormat('M d');
        }
        if ($unit === 'week') {
            [$year, $week] = explode('-W', $periodKey);
            return 'W' . $week . ' ' . $year;
        }
        return Carbon::createFromFormat('Y-m', $periodKey)->translatedFormat('M Y');
    }

    private function buildRevenueInsights(
        array $kpi,
        array $trendLabels,
        array $trendRevenue,
        array $deliveryRows,
        ?float $momentumRate,
        float $recentRevenue,
        float $previousRevenue,
        Carbon $snapshotFrom,
        Carbon $snapshotTo
    ): array {
        if (($kpi['total_orders'] ?? 0) === 0) {
            return [translate('no_confirmed_wholesale_orders_found_in_last_90_days')];
        }
        $dateRange = $snapshotFrom->format('d M, Y') . ' - ' . $snapshotTo->format('d M, Y');
        $insights = [];
        $insights[] = "Revenue between {$dateRange} reached "
            . $this->formatMoney((float)$kpi['total_revenue'])
            . " from "
            . (int)$kpi['total_orders']
            . " confirmed orders.";

        $maxRevenue = max($trendRevenue);
        if ($maxRevenue > 0) {
            $bestMonthIndex = array_search($maxRevenue, $trendRevenue, true);
            if ($bestMonthIndex !== false && isset($trendLabels[$bestMonthIndex])) {
                $insights[] = strtr(translate('wholesale_revenue_insight_peak_month'), [
                    ':period' => $trendLabels[$bestMonthIndex],
                    ':revenue' => $this->formatMoney((float)$maxRevenue),
                ]);
            }
        }

        if ($momentumRate !== null) {
            $direction = $momentumRate >= 0 ? translate('up') : translate('down');
            $insights[] = strtr(translate('wholesale_revenue_insight_momentum'), [
                ':direction' => $direction,
                ':rate' => number_format(abs($momentumRate), 1),
            ]);
        } elseif ($recentRevenue > 0 && $previousRevenue == 0.0) {
            $insights[] = translate('wholesale_revenue_insight_recent_only');
        }

        $insights[] = strtr(translate('wholesale_revenue_insight_collection'), [
            ':collection_rate' => number_format((float)$kpi['collection_rate'], 1),
            ':open_revenue' => $this->formatMoney((float)$kpi['open_revenue']),
        ]);

        if (!empty($deliveryRows)) {
            $largestBucket = $deliveryRows[0];
            $bucketStatus = ucfirst((string)data_get($largestBucket, 'delivery_status', translate('Pending')));
            $bucketTotal = (int)data_get($largestBucket, 'total', 0);
            $insights[] = strtr(translate('wholesale_revenue_insight_largest_delivery_bucket'), [
                ':status' => $bucketStatus,
                ':orders' => (string)$bucketTotal,
            ]);
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
            return [translate('no_wholesale_pipeline_activity_found_in_last_90_days')];
        }

        $insights = [];
        $insights[] = strtr(translate('wholesale_pipeline_insight_conversion'), [
            ':po_to_quote_rate' => number_format($purchaseToQuotationRate, 1),
            ':quote_to_confirmed_rate' => number_format($quotationToConfirmedRate, 1),
            ':end_to_end_rate' => number_format($endToEndRate, 1),
        ]);

        $dropFromPurchase = max(0, $purchaseCount - $quotationCount);
        $dropFromQuote = max(0, $quotationCount - $confirmedCount);
        $insights[] = strtr(translate('wholesale_pipeline_insight_stage_leakage'), [
            ':purchase_drop' => (string)$dropFromPurchase,
            ':quote_drop' => (string)$dropFromQuote,
        ]);

        if ($avgPoToQuoteHours !== null || $avgQuoteToConfirmHours !== null) {
            $poToQuoteText = $avgPoToQuoteHours !== null ? number_format($avgPoToQuoteHours, 1) . 'h' : translate('na');
            $quoteToConfirmText = $avgQuoteToConfirmHours !== null ? number_format($avgQuoteToConfirmHours, 1) . 'h' : translate('na');
            $insights[] = strtr(translate('wholesale_pipeline_insight_cycle_time'), [
                ':po_to_quote' => $poToQuoteText,
                ':quote_to_confirmed' => $quoteToConfirmText,
            ]);
        }

        if (!empty($topProducts)) {
            $topProduct = $topProducts[0];
            $topProductName = data_get($topProduct, 'product_name') ?: (translate('Product') . ' #' . data_get($topProduct, 'product_id', 'N/A'));
            $insights[] = strtr(translate('wholesale_pipeline_insight_top_product'), [
                ':product_name' => $topProductName,
                ':units' => number_format((float)data_get($topProduct, 'total_quantity', 0), 0),
            ]);
        }

        if (!empty($tierRevenue)) {
            $topTier = $tierRevenue[0];
            $insights[] = strtr(translate('wholesale_pipeline_insight_highest_tier'), [
                ':tier_name' => (string)data_get($topTier, 'tier_name', translate('Unassigned')),
                ':revenue' => $this->formatMoney((float)data_get($topTier, 'total_revenue', 0)),
            ]);
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
            $products = $this->restockProductRepo->getListWhere(orderBy: ['updated_at' => 'desc'], filters: ['added_by' => 'in_house'], relations: ['product.translations'], dataLimit: 'all');
            $firstProduct = $products->first();
            $count = $products?->sum('restock_product_customers_count') ?? 0;
            $restockProduct = [
                'title' => $firstProduct?->product?->name ?? '',
                'body' => $count < 100 ? translate('This_product_has') . ' ' . $count . ' ' . translate('Restock_Request') : translate('This_product_has') . ' 99+ ' . translate('Restock_Request'),
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
    private function generateChartImage($type, $labels, $datasets, $colors, $datasetLabels = null)
    {
        try {
            // Build chart configuration
            $chartConfig = [
                'type' => $type,
                'data' => [
                    'labels' => $labels,
                    'datasets' => []
                ],
                'options' => [
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'plugins' => [
                        'legend' => ['display' => $type !== 'bar']
                    ]
                ]
            ];

            if ($type == 'bar' || $type == 'horizontalBar') {
                foreach ($datasets as $index => $data) {
                    $label = is_array($datasetLabels) ? ($datasetLabels[$index] ?? 'Dataset') : ($datasetLabels ?? 'Dataset');
                    $chartConfig['data']['datasets'][] = [
                        'label' => $label,
                        'data' => $data,
                        'backgroundColor' => $colors[$index] ?? $colors[0],
                        'borderColor' => $colors[$index] ?? $colors[0],
                        'borderRadius' => 8
                    ];
                }
            } elseif ($type == 'line') {
                foreach ($datasets as $index => $data) {
                    $label = is_array($datasetLabels) ? ($datasetLabels[$index] ?? 'Dataset') : ($datasetLabels ?? 'Dataset');
                    $chartConfig['data']['datasets'][] = [
                        'label' => $label,
                        'data' => $data,
                        'borderColor' => $colors[$index] ?? $colors[0],
                        'backgroundColor' => 'rgba(0,0,0,0)',
                        'tension' => 0.32,
                        'fill' => false
                    ];
                }
            } elseif ($type == 'doughnut') {
                foreach ($datasets as $index => $data) {
                    $chartConfig['data']['datasets'][] = [
                        'data' => $data,
                        'backgroundColor' => $colors,
                        'borderWidth' => 0
                    ];
                }
            }

            // Encode config and get image from QuickChart API
            $configJson = json_encode($chartConfig);
            $encodedConfig = urlencode($configJson);
            $imageUrl = "https://quickchart.io/chart?c={$encodedConfig}&width=800&height=400&format=png";

            // Download image and convert to base64
            $imageData = file_get_contents($imageUrl);
            if ($imageData) {
                return 'data:image/png;base64,' . base64_encode($imageData);
            }

            return '';
        } catch (\Exception $e) {
            return '';
        }
    }
}
