<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Enums\ViewPaths\Admin\InhouseProductSale;
use App\Exports\InhouseProductSaleReportExport;
use App\Http\Controllers\BaseController;
use App\Models\Branch;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Services\ReportPdfService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;

class InhouseProductSaleController extends BaseController
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepo,
    ) {}

    public function index(?Request $request, string $type = null): View
    {
        $request = $request ?? request();
        $data = $this->buildReportData($request);
        return view(InhouseProductSale::VIEW[VIEW], $data);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();
        return Excel::download(new InhouseProductSaleReportExport($data), 'inhouse-product-sale-report.xlsx');
    }


    private function generateChartImages(array $chartData): array
    {
        $images = [];

        // Define chart configurations
        $charts = [
            'trend' => [
                'type' => 'line',
                'title' => 'Sales by Date',
                'series' => [
                    ['name' => 'POS', 'data' => $chartData['trend_pos']],
                    ['name' => 'Online', 'data' => $chartData['trend_online']],
                    ['name' => 'Wholesale', 'data' => $chartData['trend_wholesale']]
                ],
                'labels' => $chartData['trend_labels'],
                'colors' => ['#1f8ef1', '#22c55e', '#f59e0b']
            ],
            'channel' => [
                'type' => 'donut',
                'title' => 'Channel Mix',
                'series' => $chartData['channel_values'],
                'labels' => ['POS', 'Online', 'Wholesale'],
                'colors' => ['#1f8ef1', '#22c55e', '#f59e0b']
            ],
            'branch_type' => [
                'type' => 'bar',
                'title' => 'Branch & Sales Type',
                'series' => [
                    ['name' => 'POS', 'data' => $chartData['branch_type_pos']],
                    ['name' => 'Online', 'data' => $chartData['branch_type_online']],
                    ['name' => 'Wholesale', 'data' => $chartData['branch_type_wholesale']]
                ],
                'labels' => $chartData['branch_type_labels'],
                'colors' => ['#1f8ef1', '#22c55e', '#f59e0b']
            ],
            'product_type' => [
                'type' => 'bar',
                'title' => 'Sales Type & Product',
                'series' => [
                    ['name' => 'POS', 'data' => $chartData['product_type_pos']],
                    ['name' => 'Online', 'data' => $chartData['product_type_online']],
                    ['name' => 'Wholesale', 'data' => $chartData['product_type_wholesale']]
                ],
                'labels' => $chartData['product_type_labels'],
                'colors' => ['#1f8ef1', '#22c55e', '#f59e0b']
            ],
            'branch_product' => [
                'type' => 'bar',
                'title' => 'Branch & Product',
                'series' => [
                    ['name' => 'Sales', 'data' => $chartData['branch_product_values']]
                ],
                'labels' => $chartData['branch_product_labels'],
                'colors' => ['#0ea5e9']
            ]
        ];

        foreach ($charts as $key => $config) {
            // Generate chart image using a service like QuickChart or similar
            // You'll need to implement this based on your chart library
            // This is a placeholder - you need to implement actual chart generation
            $images[$key] = $this->generateChartImage($config);
        }

        return $images;
    }

    private function generateChartImage($config): string
    {
        $chartConfig = [
            'type' => $config['type'],
            'data' => [
                'labels' => $config['labels'] ?? [],
                'datasets' => []
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false
            ]
        ];

        if ($config['type'] == 'donut') {
            $chartConfig['data']['datasets'][] = [
                'data' => $config['series'],
                'backgroundColor' => $config['colors'],
                'borderWidth' => 0
            ];
        } else {
            foreach ($config['series'] as $index => $series) {
                $chartConfig['data']['datasets'][] = [
                    'label' => $series['name'],
                    'data' => $series['data'],
                    'borderColor' => $config['colors'][$index] ?? '#000',
                    'backgroundColor' => $config['colors'][$index] ?? '#000',
                    'fill' => false
                ];
            }
        }

        $url = "https://quickchart.io/chart?width=500&height=250&c=" . urlencode(json_encode($chartConfig));

        $imageData = file_get_contents($url);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    public function exportPdf(Request $request): Response
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();
        $data['chartImages'] = $this->generateChartImages($data['chart']);
        return app(ReportPdfService::class)->download(
            view: InhouseProductSale::EXPORT_PDF[VIEW],
            data: $data,
            fileName: 'inhouse-product-sale-report.pdf',
            orientation: 'landscape'
        );
    }

    private function buildReportData(Request $request): array
    {
        [$fromDate, $toDate] = $this->resolveDateRange($request);

        $categoryId = (string)$request->input('category_id', 'all');
        $productIds = $this->normalizeMultiIds($request->input('product_ids', $request->input('product_id', [])));
        $branchIds = $this->normalizeMultiIds($request->input('branch_ids', $request->input('branch_id', [])));

        $categories = $this->categoryRepo->getListWhere(filters: ['parent_id' => 0], dataLimit: 'all');
        $products = Product::query()
            ->where('added_by', 'admin')
            ->where('product_type', 'physical')
            ->when($categoryId !== 'all', fn($query) => $query->where('category_id', (int)$categoryId))
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $branches = Branch::query()
            ->select('id', 'branch_name')
            ->orderBy('branch_name')
            ->get();
        $branchMap = $branches->pluck('branch_name', 'id');

        // FOR CHARTS - Use original methods (no period)
        $posRowsForCharts = $this->getOrderChannelRows(
            channel: 'POS',
            fromDate: $fromDate,
            toDate: $toDate,
            categoryId: $categoryId,
            productIds: $productIds,
            branchIds: $branchIds,
            branchMap: $branchMap
        );

        $onlineRowsForCharts = $this->getOrderChannelRows(
            channel: 'ONLINE',
            fromDate: $fromDate,
            toDate: $toDate,
            categoryId: $categoryId,
            productIds: $productIds,
            branchIds: $branchIds,
            branchMap: $branchMap
        );

        $wholesaleRowsForCharts = $this->getWholesaleRows(
            fromDate: $fromDate,
            toDate: $toDate,
            categoryId: $categoryId,
            productIds: $productIds,
            branchIds: $branchIds,
            branchMap: $branchMap
        );

        // FOR TABLES (POS, Online, Wholesale) - Use new period methods
        $posRowsForTables = $this->getOrderChannelRowsWithPeriod(
            channel: 'POS',
            fromDate: $fromDate,
            toDate: $toDate,
            categoryId: $categoryId,
            productIds: $productIds,
            branchIds: $branchIds,
            branchMap: $branchMap
        );

        $onlineRowsForTables = $this->getOrderChannelRowsWithPeriod(
            channel: 'ONLINE',
            fromDate: $fromDate,
            toDate: $toDate,
            categoryId: $categoryId,
            productIds: $productIds,
            branchIds: $branchIds,
            branchMap: $branchMap
        );

        $wholesaleRowsForTables = $this->getWholesaleRowsWithPeriod(
            fromDate: $fromDate,
            toDate: $toDate,
            categoryId: $categoryId,
            productIds: $productIds,
            branchIds: $branchIds,
            branchMap: $branchMap
        );

        $summary = [
            'pos_amount' => (float)$posRowsForCharts->sum('total_amount'),
            'online_amount' => (float)$onlineRowsForCharts->sum('total_amount'),
            'wholesale_amount' => (float)$wholesaleRowsForCharts->sum('total_amount'),
            'pos_qty' => (int)$posRowsForCharts->sum('total_qty'),
            'online_qty' => (int)$onlineRowsForCharts->sum('total_qty'),
            'wholesale_qty' => (int)$wholesaleRowsForCharts->sum('total_qty'),
        ];
        $summary['total_amount'] = $summary['pos_amount'] + $summary['online_amount'] + $summary['wholesale_amount'];
        $summary['total_qty'] = $summary['pos_qty'] + $summary['online_qty'] + $summary['wholesale_qty'];

        $trend = $this->getDateTrend(
            fromDate: $fromDate,
            toDate: $toDate,
            categoryId: $categoryId,
            productIds: $productIds,
            branchIds: $branchIds
        );

        // FOR CHARTS - Use chart data from original methods
        $productBreakdown = $this->mergeByKey(
            rows: [$posRowsForCharts, $onlineRowsForCharts, $wholesaleRowsForCharts],
            keyField: 'product_id',
            labelField: 'product_name'
        )->sortByDesc('total_amount')->take(12)->values();

        $branchBreakdown = $this->mergeByKey(
            rows: [$posRowsForCharts, $onlineRowsForCharts, $wholesaleRowsForCharts],
            keyField: 'branch_id',
            labelField: 'branch_name'
        )->sortByDesc('total_amount')->take(12)->values();

        $branchBySalesType = $this->buildChannelSplitByDimension(
            posRows: $posRowsForCharts,
            onlineRows: $onlineRowsForCharts,
            wholesaleRows: $wholesaleRowsForCharts,
            keyField: 'branch_id',
            labelField: 'branch_name'
        );

        $productBySalesType = $this->buildChannelSplitByDimension(
            posRows: $posRowsForCharts,
            onlineRows: $onlineRowsForCharts,
            wholesaleRows: $wholesaleRowsForCharts,
            keyField: 'product_id',
            labelField: 'product_name'
        );

        $branchProductBreakdown = $this->buildBranchProductBreakdown(
            posRows: $posRowsForCharts,
            onlineRows: $onlineRowsForCharts,
            wholesaleRows: $wholesaleRowsForCharts
        );

        return [
            'categories' => $categories,
            'products' => $products,
            'branches' => $branches,
            'filters' => [
                'category_id' => $categoryId,
                'product_ids' => $productIds,
                'branch_ids' => $branchIds,
                'date_type' => $request->input('date_type', 'this_year'),
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
            ],
            'summary' => $summary,
            // Tables use period-based data
            'posRows' => $posRowsForTables,
            'onlineRows' => $onlineRowsForTables,
            'wholesaleRows' => $wholesaleRowsForTables,
            'chart' => [
                'trend_labels' => $trend['labels'],
                'trend_pos' => $trend['pos'],
                'trend_online' => $trend['online'],
                'trend_wholesale' => $trend['wholesale'],
                'product_labels' => $productBreakdown->pluck('label')->all(),
                'product_values' => $productBreakdown->pluck('total_amount')->map(fn($value) => round((float)$value, 2))->all(),
                'branch_labels' => $branchBreakdown->pluck('label')->all(),
                'branch_values' => $branchBreakdown->pluck('total_amount')->map(fn($value) => round((float)$value, 2))->all(),
                'channel_labels' => ['POS', 'Online', 'Wholesale'],
                'channel_values' => [
                    round($summary['pos_amount'], 2),
                    round($summary['online_amount'], 2),
                    round($summary['wholesale_amount'], 2),
                ],
                'branch_type_labels' => $branchBySalesType['labels'],
                'branch_type_pos' => $branchBySalesType['pos'],
                'branch_type_online' => $branchBySalesType['online'],
                'branch_type_wholesale' => $branchBySalesType['wholesale'],
                'product_type_labels' => $productBySalesType['labels'],
                'product_type_pos' => $productBySalesType['pos'],
                'product_type_online' => $productBySalesType['online'],
                'product_type_wholesale' => $productBySalesType['wholesale'],
                'branch_product_labels' => $branchProductBreakdown->pluck('label')->all(),
                'branch_product_values' => $branchProductBreakdown->pluck('total_amount')->all(),
            ],
        ];
    }

    private function resolveDateRange(Request $request): array
    {
        $dateType = $request->input('date_type', 'this_year');
        $from = $request->input('from');
        $to = $request->input('to');

        switch ($dateType) {
            case 'this_year':
                $fromDate = now()->startOfYear()->startOfDay();
                $toDate = now()->endOfYear()->endOfDay();
                break;

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
                try {
                    $fromDate = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(29)->startOfDay();
                } catch (\Throwable) {
                    $fromDate = now()->subDays(29)->startOfDay();
                }

                try {
                    $toDate = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
                } catch (\Throwable) {
                    $toDate = now()->endOfDay();
                }
                break;

            default:
                $fromDate = now()->startOfYear()->startOfDay();
                $toDate = now()->endOfYear()->endOfDay();
        }

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        return [$fromDate, $toDate];
    }



    private function getOrderChannelRows(
        string $channel,
        Carbon $fromDate,
        Carbon $toDate,
        string $categoryId,
        array $productIds,
        array $branchIds,
        Collection $branchMap
    ): Collection {
        $branchExpr = 'COALESCE(orders.transfer_from_branch, orders.pickup_from_branch, 1)';
        $branchPlaceholders = implode(',', array_fill(0, count($branchIds), '?'));

        $query = OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('orders.seller_is', 'admin')
            ->where('orders.order_status', 'delivered')
            ->where('products.added_by', 'admin')
            ->where('products.product_type', 'physical')
            ->whereBetween('orders.created_at', [$fromDate, $toDate])
            ->when($categoryId !== 'all', fn($q) => $q->where('products.category_id', (int)$categoryId))
            ->when(!empty($productIds), fn($q) => $q->whereIn('products.id', $productIds))
            ->when(!empty($branchIds), fn($q) => $q->whereRaw("{$branchExpr} IN ({$branchPlaceholders})", $branchIds));

        if ($channel === 'POS') {
            $query->whereRaw("UPPER(COALESCE(orders.order_type, '')) = 'POS'");
        } else {
            $query->whereRaw("UPPER(COALESCE(orders.order_type, '')) <> 'POS'");
        }

        $rows = $query
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                DB::raw("{$branchExpr} as branch_id"),
                DB::raw('SUM(order_details.qty) as total_qty'),
                DB::raw('SUM(order_details.qty * order_details.price) as total_amount'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
            ])
            ->groupBy('products.id', 'products.name', DB::raw($branchExpr))
            ->orderByDesc('total_amount')
            ->get();

        return $rows->map(function ($row) use ($branchMap) {
            $row->branch_id = (int)$row->branch_id;
            $row->branch_name = $branchMap->get($row->branch_id, 'Branch #' . $row->branch_id);
            $row->total_qty = (int)$row->total_qty;
            $row->total_amount = (float)$row->total_amount;
            $row->total_orders = (int)$row->total_orders;
            return $row;
        });
    }

    private function getWholesaleRows(
        Carbon $fromDate,
        Carbon $toDate,
        string $categoryId,
        array $productIds,
        array $branchIds,
        Collection $branchMap
    ): Collection {
        $query = DB::table('wholesale_order_delivery as wod')
            ->join('products as p', 'p.id', '=', 'wod.product_id')
            ->leftJoin('wholesale_confirmorder_item as wci', function ($join) {
                $join->on('wci.confirmed_order_id', '=', 'wod.confirmed_order_id')
                    ->on('wci.product_id', '=', 'wod.product_id')
                    ->whereRaw('COALESCE(wci.product_variation_type, "") = COALESCE(wod.product_variation_type, "")');
            })
            ->where('p.added_by', 'admin')
            ->where('p.product_type', 'physical')
            ->whereDate('wod.delivery_date', '>=', $fromDate->toDateString())
            ->whereDate('wod.delivery_date', '<=', $toDate->toDateString())
            ->when($categoryId !== 'all', fn($q) => $q->where('p.category_id', (int)$categoryId))
            ->when(!empty($productIds), fn($q) => $q->whereIn('p.id', $productIds))
            ->when(!empty($branchIds), fn($q) => $q->whereIn('wod.branch_id', $branchIds))
            ->select([
                'p.id as product_id',
                'p.name as product_name',
                'wod.branch_id as branch_id',
                DB::raw('SUM(wod.quantity_sent) as total_qty'),
                DB::raw('COUNT(DISTINCT wod.confirmed_order_id) as total_orders'),
                DB::raw('SUM(wod.quantity_sent * (CASE WHEN COALESCE(wci.product_quantity, 0) > 0 THEN (COALESCE(wci.final_price, 0) / wci.product_quantity) ELSE COALESCE(wci.base_price, 0) END)) as total_amount'),
            ])
            ->groupBy('p.id', 'p.name', 'wod.branch_id')
            ->orderByDesc('total_amount')
            ->get();

        return collect($query)->map(function ($row) use ($branchMap) {
            $row->branch_id = (int)($row->branch_id ?? 0);
            $row->branch_name = $branchMap->get($row->branch_id, 'Branch #' . $row->branch_id);
            $row->total_qty = (int)$row->total_qty;
            $row->total_amount = (float)$row->total_amount;
            $row->total_orders = (int)$row->total_orders;
            return $row;
        });
    }

    /**
     * NEW METHOD: For POS/Online tables with period breakdown
     * This won't affect the existing chart methods
     */
    private function getOrderChannelRowsWithPeriod(
        string $channel,
        Carbon $fromDate,
        Carbon $toDate,
        string $categoryId,
        array $productIds,
        array $branchIds,
        Collection $branchMap
    ): Collection {
        $daysDifference = $fromDate->diffInDays($toDate);
        $branchExpr = 'COALESCE(orders.transfer_from_branch, orders.pickup_from_branch, 1)';
        $branchPlaceholders = implode(',', array_fill(0, count($branchIds), '?'));

        $query = OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('orders.seller_is', 'admin')
            ->where('orders.order_status', 'delivered')
            ->where('products.added_by', 'admin')
            ->where('products.product_type', 'physical')
            ->whereBetween('orders.created_at', [$fromDate, $toDate])
            ->when($categoryId !== 'all', fn($q) => $q->where('products.category_id', (int)$categoryId))
            ->when(!empty($productIds), fn($q) => $q->whereIn('products.id', $productIds))
            ->when(!empty($branchIds), fn($q) => $q->whereRaw("{$branchExpr} IN ({$branchPlaceholders})", $branchIds));

        if ($channel === 'POS') {
            $query->whereRaw("UPPER(COALESCE(orders.order_type, '')) = 'POS'");
        } else {
            $query->whereRaw("UPPER(COALESCE(orders.order_type, '')) <> 'POS'");
        }

        // Add period column but keep product/branch grouping
        if ($daysDifference > 60) {
            // For year view - add month period
            $query->selectRaw("
            DATE_FORMAT(orders.created_at, '%Y-%m') as period,
            DATE_FORMAT(orders.created_at, '%b') as period_label,
            products.id as product_id,
            products.name as product_name,
            {$branchExpr} as branch_id,
            SUM(order_details.qty) as total_qty,
            SUM(order_details.qty * order_details.price) as total_amount,
            COUNT(DISTINCT orders.id) as total_orders
        ")->groupBy('products.id', 'products.name', DB::raw($branchExpr), DB::raw("DATE_FORMAT(orders.created_at, '%Y-%m')"), DB::raw("DATE_FORMAT(orders.created_at, '%b')"));
        } elseif ($daysDifference <= 7) {
            // For week view - add day name
            $query->selectRaw("
            DATE(orders.created_at) as period,
            DAYNAME(orders.created_at) as period_label,
            products.id as product_id,
            products.name as product_name,
            {$branchExpr} as branch_id,
            SUM(order_details.qty) as total_qty,
            SUM(order_details.qty * order_details.price) as total_amount,
            COUNT(DISTINCT orders.id) as total_orders
        ")->groupBy('products.id', 'products.name', DB::raw($branchExpr), DB::raw("DATE(orders.created_at)"), DB::raw("DAYNAME(orders.created_at)"));
        } elseif ($daysDifference <= 31) {
            // For month view - add day number
            $query->selectRaw("
            DATE(orders.created_at) as period,
            DAY(orders.created_at) as period_label,
            products.id as product_id,
            products.name as product_name,
            {$branchExpr} as branch_id,
            SUM(order_details.qty) as total_qty,
            SUM(order_details.qty * order_details.price) as total_amount,
            COUNT(DISTINCT orders.id) as total_orders
        ")->groupBy('products.id', 'products.name', DB::raw($branchExpr), DB::raw("DATE(orders.created_at)"), DB::raw("DAY(orders.created_at)"));
        } else {
            // Default - add date
            $query->selectRaw("
            DATE(orders.created_at) as period,
            DATE_FORMAT(orders.created_at, '%d %b') as period_label,
            products.id as product_id,
            products.name as product_name,
            {$branchExpr} as branch_id,
            SUM(order_details.qty) as total_qty,
            SUM(order_details.qty * order_details.price) as total_amount,
            COUNT(DISTINCT orders.id) as total_orders
        ")->groupBy('products.id', 'products.name', DB::raw($branchExpr), DB::raw("DATE(orders.created_at)"));
        }

        $rows = $query->orderBy('period')->orderBy('product_name')->get();

        return $rows->map(function ($row) use ($branchMap) {
            $row->branch_id = (int)$row->branch_id;
            $row->branch_name = $branchMap->get($row->branch_id, 'Branch #' . $row->branch_id);
            $row->total_qty = (int)$row->total_qty;
            $row->total_amount = (float)$row->total_amount;
            $row->total_orders = (int)$row->total_orders;
            return $row;
        });
    }

    /**
     * NEW METHOD: For Wholesale table with period breakdown
     */
    private function getWholesaleRowsWithPeriod(
        Carbon $fromDate,
        Carbon $toDate,
        string $categoryId,
        array $productIds,
        array $branchIds,
        Collection $branchMap
    ): Collection {
        $daysDifference = $fromDate->diffInDays($toDate);

        $query = DB::table('wholesale_order_delivery as wod')
            ->join('products as p', 'p.id', '=', 'wod.product_id')
            ->leftJoin('wholesale_confirmorder_item as wci', function ($join) {
                $join->on('wci.confirmed_order_id', '=', 'wod.confirmed_order_id')
                    ->on('wci.product_id', '=', 'wod.product_id')
                    ->whereRaw('COALESCE(wci.product_variation_type, "") = COALESCE(wod.product_variation_type, "")');
            })
            ->where('p.added_by', 'admin')
            ->where('p.product_type', 'physical')
            ->whereDate('wod.delivery_date', '>=', $fromDate->toDateString())
            ->whereDate('wod.delivery_date', '<=', $toDate->toDateString())
            ->when($categoryId !== 'all', fn($q) => $q->where('p.category_id', (int)$categoryId))
            ->when(!empty($productIds), fn($q) => $q->whereIn('p.id', $productIds))
            ->when(!empty($branchIds), fn($q) => $q->whereIn('wod.branch_id', $branchIds));

        if ($daysDifference > 60) {
            // For year view
            $query->selectRaw("
            DATE_FORMAT(wod.delivery_date, '%Y-%m') as period,
            DATE_FORMAT(wod.delivery_date, '%b') as period_label,
            p.id as product_id,
            p.name as product_name,
            wod.branch_id as branch_id,
            SUM(wod.quantity_sent) as total_qty,
            COUNT(DISTINCT wod.confirmed_order_id) as total_orders,
            SUM(wod.quantity_sent * (CASE WHEN COALESCE(wci.product_quantity, 0) > 0 THEN (COALESCE(wci.final_price, 0) / wci.product_quantity) ELSE COALESCE(wci.base_price, 0) END)) as total_amount
        ")->groupBy('p.id', 'p.name', 'wod.branch_id', DB::raw("DATE_FORMAT(wod.delivery_date, '%Y-%m')"), DB::raw("DATE_FORMAT(wod.delivery_date, '%b')"));
        } elseif ($daysDifference <= 7) {
            // For week view
            $query->selectRaw("
            DATE(wod.delivery_date) as period,
            DAYNAME(wod.delivery_date) as period_label,
            p.id as product_id,
            p.name as product_name,
            wod.branch_id as branch_id,
            SUM(wod.quantity_sent) as total_qty,
            COUNT(DISTINCT wod.confirmed_order_id) as total_orders,
            SUM(wod.quantity_sent * (CASE WHEN COALESCE(wci.product_quantity, 0) > 0 THEN (COALESCE(wci.final_price, 0) / wci.product_quantity) ELSE COALESCE(wci.base_price, 0) END)) as total_amount
        ")->groupBy('p.id', 'p.name', 'wod.branch_id', DB::raw("DATE(wod.delivery_date)"), DB::raw("DAYNAME(wod.delivery_date)"));
        } elseif ($daysDifference <= 31) {
            // For month view
            $query->selectRaw("
            DATE(wod.delivery_date) as period,
            DAY(wod.delivery_date) as period_label,
            p.id as product_id,
            p.name as product_name,
            wod.branch_id as branch_id,
            SUM(wod.quantity_sent) as total_qty,
            COUNT(DISTINCT wod.confirmed_order_id) as total_orders,
            SUM(wod.quantity_sent * (CASE WHEN COALESCE(wci.product_quantity, 0) > 0 THEN (COALESCE(wci.final_price, 0) / wci.product_quantity) ELSE COALESCE(wci.base_price, 0) END)) as total_amount
        ")->groupBy('p.id', 'p.name', 'wod.branch_id', DB::raw("DATE(wod.delivery_date)"), DB::raw("DAY(wod.delivery_date)"));
        } else {
            // Default
            $query->selectRaw("
            DATE(wod.delivery_date) as period,
            DATE_FORMAT(wod.delivery_date, '%d %b') as period_label,
            p.id as product_id,
            p.name as product_name,
            wod.branch_id as branch_id,
            SUM(wod.quantity_sent) as total_qty,
            COUNT(DISTINCT wod.confirmed_order_id) as total_orders,
            SUM(wod.quantity_sent * (CASE WHEN COALESCE(wci.product_quantity, 0) > 0 THEN (COALESCE(wci.final_price, 0) / wci.product_quantity) ELSE COALESCE(wci.base_price, 0) END)) as total_amount
        ")->groupBy('p.id', 'p.name', 'wod.branch_id', DB::raw("DATE(wod.delivery_date)"));
        }

        $rows = $query->orderBy('period')->orderBy('product_name')->get();

        return collect($rows)->map(function ($row) use ($branchMap) {
            $row->branch_id = (int)($row->branch_id ?? 0);
            $row->branch_name = $branchMap->get($row->branch_id, 'Branch #' . $row->branch_id);
            $row->total_qty = (int)$row->total_qty;
            $row->total_amount = (float)$row->total_amount;
            $row->total_orders = (int)$row->total_orders;
            return $row;
        });
    }


    private function getDateTrend(
        Carbon $fromDate,
        Carbon $toDate,
        string $categoryId,
        array $productIds,
        array $branchIds
    ): array {
        $daysDifference = $fromDate->diffInDays($toDate);
        $labels = [];
        $seriesDateMap = [];

        // Determine label format based on date range
        if ($daysDifference > 60) {
            // Monthly grouping for year or long ranges - show only month name (Jan, Feb)
            $period = CarbonPeriod::create($fromDate->copy()->startOfMonth(), '1 month', $toDate->copy()->endOfMonth());
            foreach ($period as $date) {
                $key = $date->format('Y-m');
                $labels[] = $date->format('M'); // Jan, Feb, Mar (without year)
                $seriesDateMap[$key] = [
                    'pos' => 0,
                    'online' => 0,
                    'wholesale' => 0,
                ];
            }
        } elseif ($daysDifference <= 7) {
            // Daily grouping for week
            $period = CarbonPeriod::create($fromDate->copy()->startOfDay(), $toDate->copy()->endOfDay());
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $labels[] = $date->format('l'); // Monday, Tuesday etc.
                $seriesDateMap[$key] = [
                    'pos' => 0,
                    'online' => 0,
                    'wholesale' => 0,
                ];
            }
        } elseif ($daysDifference <= 31) {
            // Daily grouping for month - show day only (1, 2, 3) not 01 Feb
            $period = CarbonPeriod::create($fromDate->copy()->startOfDay(), $toDate->copy()->endOfDay());
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $labels[] = $date->format('j'); // 1, 2, 3, 4... (without leading zero and without month)
                $seriesDateMap[$key] = [
                    'pos' => 0,
                    'online' => 0,
                    'wholesale' => 0,
                ];
            }
        } else {
            // Default daily grouping
            $period = CarbonPeriod::create($fromDate->copy()->startOfDay(), $toDate->copy()->endOfDay());
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $labels[] = $date->format('j M'); // 1 Jan, 2 Jan (without leading zero)
                $seriesDateMap[$key] = [
                    'pos' => 0,
                    'online' => 0,
                    'wholesale' => 0,
                ];
            }
        }

        $orderBranchExpr = 'COALESCE(orders.transfer_from_branch, orders.pickup_from_branch, 1)';
        $branchPlaceholders = implode(',', array_fill(0, count($branchIds), '?'));

        $baseOrderQuery = OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('orders.seller_is', 'admin')
            ->where('orders.order_status', 'delivered')
            ->where('products.added_by', 'admin')
            ->where('products.product_type', 'physical')
            ->whereBetween('orders.created_at', [$fromDate, $toDate])
            ->when($categoryId !== 'all', fn($q) => $q->where('products.category_id', (int)$categoryId))
            ->when(!empty($productIds), fn($q) => $q->whereIn('products.id', $productIds))
            ->when(!empty($branchIds), fn($q) => $q->whereRaw("{$orderBranchExpr} IN ({$branchPlaceholders})", $branchIds));

        $posDaily = (clone $baseOrderQuery)
            ->whereRaw("UPPER(COALESCE(orders.order_type, '')) = 'POS'")
            ->selectRaw('DATE(orders.created_at) as report_date, SUM(order_details.qty * order_details.price) as total_amount')
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->get();

        foreach ($posDaily as $row) {
            $date = Carbon::parse($row->report_date);
            if ($daysDifference > 60) {
                $key = $date->format('Y-m');
            } else {
                $key = $date->format('Y-m-d');
            }

            if (isset($seriesDateMap[$key])) {
                $seriesDateMap[$key]['pos'] += round((float)$row->total_amount, 2);
            }
        }

        $onlineDaily = (clone $baseOrderQuery)
            ->whereRaw("UPPER(COALESCE(orders.order_type, '')) <> 'POS'")
            ->selectRaw('DATE(orders.created_at) as report_date, SUM(order_details.qty * order_details.price) as total_amount')
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->get();

        foreach ($onlineDaily as $row) {
            $date = Carbon::parse($row->report_date);
            if ($daysDifference > 60) {
                $key = $date->format('Y-m');
            } else {
                $key = $date->format('Y-m-d');
            }

            if (isset($seriesDateMap[$key])) {
                $seriesDateMap[$key]['online'] += round((float)$row->total_amount, 2);
            }
        }

        $wholesaleDaily = DB::table('wholesale_order_delivery as wod')
            ->join('products as p', 'p.id', '=', 'wod.product_id')
            ->leftJoin('wholesale_confirmorder_item as wci', function ($join) {
                $join->on('wci.confirmed_order_id', '=', 'wod.confirmed_order_id')
                    ->on('wci.product_id', '=', 'wod.product_id')
                    ->whereRaw('COALESCE(wci.product_variation_type, "") = COALESCE(wod.product_variation_type, "")');
            })
            ->where('p.added_by', 'admin')
            ->where('p.product_type', 'physical')
            ->whereDate('wod.delivery_date', '>=', $fromDate->toDateString())
            ->whereDate('wod.delivery_date', '<=', $toDate->toDateString())
            ->when($categoryId !== 'all', fn($q) => $q->where('p.category_id', (int)$categoryId))
            ->when(!empty($productIds), fn($q) => $q->whereIn('p.id', $productIds))
            ->when(!empty($branchIds), fn($q) => $q->whereIn('wod.branch_id', $branchIds))
            ->selectRaw('DATE(wod.delivery_date) as report_date, SUM(wod.quantity_sent * (CASE WHEN COALESCE(wci.product_quantity, 0) > 0 THEN (COALESCE(wci.final_price, 0) / wci.product_quantity) ELSE COALESCE(wci.base_price, 0) END)) as total_amount')
            ->groupBy(DB::raw('DATE(wod.delivery_date)'))
            ->get();

        foreach ($wholesaleDaily as $row) {
            $date = Carbon::parse($row->report_date);
            if ($daysDifference > 60) {
                $key = $date->format('Y-m');
            } else {
                $key = $date->format('Y-m-d');
            }

            if (isset($seriesDateMap[$key])) {
                $seriesDateMap[$key]['wholesale'] += round((float)$row->total_amount, 2);
            }
        }

        return [
            'labels' => $labels,
            'pos' => array_values(array_map(fn($day) => $day['pos'], $seriesDateMap)),
            'online' => array_values(array_map(fn($day) => $day['online'], $seriesDateMap)),
            'wholesale' => array_values(array_map(fn($day) => $day['wholesale'], $seriesDateMap)),
        ];
    }
    private function mergeByKey(array $rows, string $keyField, string $labelField): Collection
    {
        $merged = [];

        foreach ($rows as $dataset) {
            foreach ($dataset as $row) {
                $key = (string)$row->{$keyField};
                if (!isset($merged[$key])) {
                    $merged[$key] = [
                        'key' => $key,
                        'label' => (string)($row->{$labelField} ?? ('#' . $key)),
                        'total_qty' => 0,
                        'total_amount' => 0.0,
                    ];
                }

                $merged[$key]['total_qty'] += (int)($row->total_qty ?? 0);
                $merged[$key]['total_amount'] += (float)($row->total_amount ?? 0);
            }
        }

        return collect($merged)->map(function ($row) {
            $row['total_amount'] = round((float)$row['total_amount'], 2);
            return (object)$row;
        });
    }

    private function normalizeMultiIds(mixed $input): array
    {
        if ($input === null || $input === '' || $input === 'all') {
            return [];
        }

        if (!is_array($input)) {
            $input = is_string($input) ? explode(',', $input) : [$input];
        }

        return collect($input)
            ->filter(fn($value) => $value !== null && $value !== '' && $value !== 'all')
            ->map(fn($value) => (int)$value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function buildChannelSplitByDimension(
        Collection $posRows,
        Collection $onlineRows,
        Collection $wholesaleRows,
        string $keyField,
        string $labelField,
        int $limit = 12
    ): array {
        $bucket = [];
        $datasets = [
            'pos' => $posRows,
            'online' => $onlineRows,
            'wholesale' => $wholesaleRows,
        ];

        foreach ($datasets as $channel => $rows) {
            foreach ($rows as $row) {
                $key = (string)($row->{$keyField} ?? '');
                if ($key === '') {
                    continue;
                }

                if (!isset($bucket[$key])) {
                    $bucket[$key] = [
                        'label' => (string)($row->{$labelField} ?? ('#' . $key)),
                        'pos' => 0.0,
                        'online' => 0.0,
                        'wholesale' => 0.0,
                        'total' => 0.0,
                    ];
                }

                $bucket[$key][$channel] += (float)($row->total_amount ?? 0);
                $bucket[$key]['total'] += (float)($row->total_amount ?? 0);
            }
        }

        $rows = collect($bucket)
            ->sortByDesc('total')
            ->take($limit)
            ->values();

        return [
            'labels' => $rows->pluck('label')->all(),
            'pos' => $rows->pluck('pos')->map(fn($value) => round((float)$value, 2))->all(),
            'online' => $rows->pluck('online')->map(fn($value) => round((float)$value, 2))->all(),
            'wholesale' => $rows->pluck('wholesale')->map(fn($value) => round((float)$value, 2))->all(),
        ];
    }

    private function buildBranchProductBreakdown(
        Collection $posRows,
        Collection $onlineRows,
        Collection $wholesaleRows,
        int $limit = 15
    ): Collection {
        $merged = [];

        foreach ([$posRows, $onlineRows, $wholesaleRows] as $dataset) {
            foreach ($dataset as $row) {
                $key = (string)($row->branch_id ?? 0) . '|' . (string)($row->product_id ?? 0);
                if (!isset($merged[$key])) {
                    $branchName = (string)($row->branch_name ?? translate('branch'));
                    $productName = (string)($row->product_name ?? translate('product'));
                    $merged[$key] = [
                        'label' => $branchName . ' - ' . $productName,
                        'total_amount' => 0.0,
                    ];
                }

                $merged[$key]['total_amount'] += (float)($row->total_amount ?? 0);
            }
        }

        return collect($merged)
            ->map(function ($row) {
                $row['total_amount'] = round((float)$row['total_amount'], 2);
                return (object)$row;
            })
            ->sortByDesc('total_amount')
            ->take($limit)
            ->values();
    }
}
