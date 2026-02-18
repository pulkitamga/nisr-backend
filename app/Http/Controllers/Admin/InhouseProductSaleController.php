<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Enums\ViewPaths\Admin\InhouseProductSale;
use App\Exports\InhouseProductSaleReportExport;
use App\Http\Controllers\BaseController;
use App\Models\Branch;
use App\Models\OrderDetail;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class InhouseProductSaleController extends BaseController
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepo,
    )
    {
    }

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

    public function exportPdf(Request $request): Response
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();

        $pdf = Pdf::loadView(InhouseProductSale::EXPORT_PDF[VIEW], $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('inhouse-product-sale-report.pdf');
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

        $posRows = $this->getOrderChannelRows(
            channel: 'POS',
            fromDate: $fromDate,
            toDate: $toDate,
            categoryId: $categoryId,
            productIds: $productIds,
            branchIds: $branchIds,
            branchMap: $branchMap
        );

        $onlineRows = $this->getOrderChannelRows(
            channel: 'ONLINE',
            fromDate: $fromDate,
            toDate: $toDate,
            categoryId: $categoryId,
            productIds: $productIds,
            branchIds: $branchIds,
            branchMap: $branchMap
        );

        $wholesaleRows = $this->getWholesaleRows(
            fromDate: $fromDate,
            toDate: $toDate,
            categoryId: $categoryId,
            productIds: $productIds,
            branchIds: $branchIds,
            branchMap: $branchMap
        );

        $summary = [
            'pos_amount' => (float)$posRows->sum('total_amount'),
            'online_amount' => (float)$onlineRows->sum('total_amount'),
            'wholesale_amount' => (float)$wholesaleRows->sum('total_amount'),
            'pos_qty' => (int)$posRows->sum('total_qty'),
            'online_qty' => (int)$onlineRows->sum('total_qty'),
            'wholesale_qty' => (int)$wholesaleRows->sum('total_qty'),
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

        $productBreakdown = $this->mergeByKey(
            rows: [$posRows, $onlineRows, $wholesaleRows],
            keyField: 'product_id',
            labelField: 'product_name'
        )->sortByDesc('total_amount')->take(12)->values();

        $branchBreakdown = $this->mergeByKey(
            rows: [$posRows, $onlineRows, $wholesaleRows],
            keyField: 'branch_id',
            labelField: 'branch_name'
        )->sortByDesc('total_amount')->take(12)->values();

        $branchBySalesType = $this->buildChannelSplitByDimension(
            posRows: $posRows,
            onlineRows: $onlineRows,
            wholesaleRows: $wholesaleRows,
            keyField: 'branch_id',
            labelField: 'branch_name'
        );

        $productBySalesType = $this->buildChannelSplitByDimension(
            posRows: $posRows,
            onlineRows: $onlineRows,
            wholesaleRows: $wholesaleRows,
            keyField: 'product_id',
            labelField: 'product_name'
        );

        $branchProductBreakdown = $this->buildBranchProductBreakdown(
            posRows: $posRows,
            onlineRows: $onlineRows,
            wholesaleRows: $wholesaleRows
        );

        return [
            'categories' => $categories,
            'products' => $products,
            'branches' => $branches,
            'filters' => [
                'category_id' => $categoryId,
                'product_ids' => $productIds,
                'branch_ids' => $branchIds,
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
            ],
            'summary' => $summary,
            'posRows' => $posRows,
            'onlineRows' => $onlineRows,
            'wholesaleRows' => $wholesaleRows,
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
        $from = $request->input('from');
        $to = $request->input('to');

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

    private function getDateTrend(
        Carbon $fromDate,
        Carbon $toDate,
        string $categoryId,
        array $productIds,
        array $branchIds
    ): array {
        $period = CarbonPeriod::create($fromDate->copy()->startOfDay(), $toDate->copy()->startOfDay());
        $labels = [];
        $seriesDateMap = [];
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $seriesDateMap[$key] = [
                'pos' => 0,
                'online' => 0,
                'wholesale' => 0,
            ];
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
            $key = (string)$row->report_date;
            if (isset($seriesDateMap[$key])) {
                $seriesDateMap[$key]['pos'] = round((float)$row->total_amount, 2);
            }
        }

        $onlineDaily = (clone $baseOrderQuery)
            ->whereRaw("UPPER(COALESCE(orders.order_type, '')) <> 'POS'")
            ->selectRaw('DATE(orders.created_at) as report_date, SUM(order_details.qty * order_details.price) as total_amount')
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->get();

        foreach ($onlineDaily as $row) {
            $key = (string)$row->report_date;
            if (isset($seriesDateMap[$key])) {
                $seriesDateMap[$key]['online'] = round((float)$row->total_amount, 2);
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
            $key = (string)$row->report_date;
            if (isset($seriesDateMap[$key])) {
                $seriesDateMap[$key]['wholesale'] = round((float)$row->total_amount, 2);
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
