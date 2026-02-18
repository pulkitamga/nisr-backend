<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductStockAnalyticsReportExport;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\ManageBranchProductStock;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductStockTransaction;
use App\Enums\StockReason;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductStockReportController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->buildReportData($request);
        return view('admin-views.report.product-stock', $data);
    }

    public function filter(Request $request): RedirectResponse
    {
        return redirect()->route('admin.stock.product-stock', $request->all());
    }

    public function export(Request $request): BinaryFileResponse
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();
        return Excel::download(new ProductStockAnalyticsReportExport($data), 'product-stock-analytics-report.xlsx');
    }

    public function exportPdf(Request $request): Response
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();

        $pdf = Pdf::loadView('admin-views.report.product-stock-pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('product-stock-analytics-report.pdf');
    }

    private function buildReportData(Request $request): array
    {
        [$fromDate, $toDate] = $this->resolveDateRange($request);

        $categoryId = (string)$request->input('category_id', 'all');
        $productIds = $this->normalizeMultiIds($request->input('product_ids', $request->input('product_id', [])));
        $branchIds = $this->normalizeMultiIds($request->input('branch_ids', $request->input('branch_id', [])));
        $includeInternalTransfer = (bool)$request->boolean('include_internal_transfer');

        $categories = Category::query()
            ->where('position', 0)
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

        $products = Product::query()
            ->where('added_by', 'admin')
            ->where('product_type', 'physical')
            ->when($categoryId !== 'all', fn(Builder $query) => $this->applyCategoryFilter($query, (int)$categoryId))
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $branches = Branch::query()
            ->select('id', 'branch_name')
            ->orderBy('branch_name')
            ->get();

        $branchMap = $branches->pluck('branch_name', 'id');
        $scopedProductIds = $this->getScopedProductIds($categoryId, $productIds);

        $stockByProductRows = $this->getStockByProductRows($scopedProductIds, $branchIds);
        $stockByBranchRows = $this->getStockByBranchRows($scopedProductIds, $branchIds);
        $stockByBranchProductRows = $this->getStockByBranchProductRows($scopedProductIds, $branchIds);
        $movementRows = $this->getMovementRows(
            fromDate: $fromDate,
            toDate: $toDate,
            productIds: $scopedProductIds,
            branchIds: $branchIds,
            includeInternalTransfer: $includeInternalTransfer,
            branchMap: $branchMap
        );
        $movementSummary = $this->buildMovementSummary($movementRows);

        $movementByProduct = $movementRows
            ->groupBy('product_id')
            ->map(function (Collection $rows) {
                return [
                    'stock_in' => (int)$rows->where('type', 'IN')->sum('quantity'),
                    'stock_out' => (int)$rows->where('type', 'OUT')->sum('quantity'),
                ];
            });

        $stockByProductRows = $stockByProductRows->map(function ($row) use ($movementByProduct) {
            $movement = $movementByProduct->get((int)$row->product_id, ['stock_in' => 0, 'stock_out' => 0]);
            $row->stock_in = (int)($movement['stock_in'] ?? 0);
            $row->stock_out = (int)($movement['stock_out'] ?? 0);
            $row->net_movement = (int)$row->stock_in - (int)$row->stock_out;
            return $row;
        });

        $summary = [
            'total_current_stock' => (int)$stockByProductRows->sum('current_stock'),
            'total_stock_in' => (int)$movementSummary['totals']['in'],
            'total_stock_out' => (int)$movementSummary['totals']['out'],
            'net_stock_movement' => (int)$movementSummary['totals']['in'] - (int)$movementSummary['totals']['out'],
            'products_count' => (int)$stockByProductRows->count(),
            'branches_count' => (int)$stockByBranchRows->count(),
        ];

        $dateTrend = $this->buildDateTrend($fromDate, $toDate, $movementRows);

        $branchChartRows = $stockByBranchRows->sortByDesc('current_stock')->take(12)->values();
        $productChartRows = $stockByProductRows->sortByDesc('current_stock')->take(12)->values();
        $branchProductChartRows = $stockByBranchProductRows->sortByDesc('current_stock')->take(16)->values();

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
                'include_internal_transfer' => $includeInternalTransfer,
            ],
            'summary' => $summary,
            'movementSummary' => $movementSummary,
            'stockByProductRows' => $stockByProductRows,
            'stockByBranchRows' => $stockByBranchRows,
            'stockByBranchProductRows' => $stockByBranchProductRows,
            'movementRows' => $movementRows,
            'chart' => [
                'date_labels' => $dateTrend['labels'],
                'date_stock_in' => $dateTrend['in'],
                'date_stock_out' => $dateTrend['out'],
                'branch_labels' => $branchChartRows->pluck('branch_name')->all(),
                'branch_values' => $branchChartRows->pluck('current_stock')->map(fn($value) => (int)$value)->all(),
                'product_labels' => $productChartRows->pluck('product_name')->all(),
                'product_values' => $productChartRows->pluck('current_stock')->map(fn($value) => (int)$value)->all(),
                'branch_product_labels' => $branchProductChartRows->map(
                    fn($row) => (string)$row->branch_name . ' - ' . (string)$row->product_name
                )->all(),
                'branch_product_values' => $branchProductChartRows->pluck('current_stock')->map(fn($value) => (int)$value)->all(),
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

    private function applyCategoryFilter(Builder $query, int $categoryId): Builder
    {
        return $query->where(function (Builder $nestedQuery) use ($categoryId) {
            $nestedQuery->where('category_id', $categoryId)
                ->orWhereJsonContains('category_ids', ['id' => $categoryId]);
        });
    }

    private function getScopedProductIds(string $categoryId, array $productIds): array
    {
        $query = Product::query()
            ->where('added_by', 'admin')
            ->where('product_type', 'physical');

        if ($categoryId !== 'all') {
            $this->applyCategoryFilter($query, (int)$categoryId);
        }

        if (!empty($productIds)) {
            $query->whereIn('id', $productIds);
        }

        return $query->pluck('id')->map(fn($id) => (int)$id)->all();
    }

    private function getStockByProductRows(array $productIds, array $branchIds): Collection
    {
        if (empty($productIds)) {
            return collect();
        }

        if (!empty($branchIds)) {
            $stockSubQuery = ManageBranchProductStock::query()
                ->select([
                    'product_id',
                    DB::raw('SUM(current_stock) as total_stock'),
                    DB::raw('COUNT(DISTINCT branch_id) as branch_count'),
                ])
                ->whereIn('branch_id', $branchIds)
                ->groupBy('product_id');
        } else {
            $stockSubQuery = ProductStock::query()
                ->select([
                    'product_id',
                    DB::raw('SUM(qty) as total_stock'),
                    DB::raw('0 as branch_count'),
                ])
                ->groupBy('product_id');
        }

        return Product::query()
            ->whereIn('products.id', $productIds)
            ->leftJoinSub($stockSubQuery, 'stock_agg', function ($join) {
                $join->on('stock_agg.product_id', '=', 'products.id');
            })
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('COALESCE(stock_agg.total_stock, 0) as current_stock'),
                DB::raw('COALESCE(stock_agg.branch_count, 0) as branch_count'),
            ])
            ->orderByDesc('current_stock')
            ->orderBy('products.name')
            ->get()
            ->map(function ($row) {
                $row->product_id = (int)$row->product_id;
                $row->current_stock = (int)$row->current_stock;
                $row->branch_count = (int)$row->branch_count;
                return $row;
            });
    }

    private function getStockByBranchRows(array $productIds, array $branchIds): Collection
    {
        if (empty($productIds)) {
            return collect();
        }

        return ManageBranchProductStock::query()
            ->from('manage_branch_product_stock as m')
            ->join('branches as b', 'b.id', '=', 'm.branch_id')
            ->whereIn('m.product_id', $productIds)
            ->when(!empty($branchIds), fn($query) => $query->whereIn('m.branch_id', $branchIds))
            ->select([
                'b.id as branch_id',
                'b.branch_name',
                DB::raw('SUM(m.current_stock) as current_stock'),
                DB::raw('COUNT(DISTINCT m.product_id) as products_count'),
            ])
            ->groupBy('b.id', 'b.branch_name')
            ->orderByDesc('current_stock')
            ->get()
            ->map(function ($row) {
                $row->branch_id = (int)$row->branch_id;
                $row->current_stock = (int)$row->current_stock;
                $row->products_count = (int)$row->products_count;
                return $row;
            });
    }

    private function getStockByBranchProductRows(array $productIds, array $branchIds): Collection
    {
        if (empty($productIds)) {
            return collect();
        }

        return ManageBranchProductStock::query()
            ->from('manage_branch_product_stock as m')
            ->join('branches as b', 'b.id', '=', 'm.branch_id')
            ->join('products as p', 'p.id', '=', 'm.product_id')
            ->whereIn('m.product_id', $productIds)
            ->when(!empty($branchIds), fn($query) => $query->whereIn('m.branch_id', $branchIds))
            ->select([
                'b.id as branch_id',
                'b.branch_name',
                'p.id as product_id',
                'p.name as product_name',
                DB::raw('SUM(m.current_stock) as current_stock'),
            ])
            ->groupBy('b.id', 'b.branch_name', 'p.id', 'p.name')
            ->orderByDesc('current_stock')
            ->get()
            ->map(function ($row) {
                $row->branch_id = (int)$row->branch_id;
                $row->product_id = (int)$row->product_id;
                $row->current_stock = (int)$row->current_stock;
                return $row;
            });
    }

    private function getMovementRows(
        Carbon $fromDate,
        Carbon $toDate,
        array $productIds,
        array $branchIds,
        bool $includeInternalTransfer,
        Collection $branchMap
    ): Collection {
        if (empty($productIds)) {
            return collect();
        }

        $rows = ProductStockTransaction::query()
            ->from('product_stock_transactions as pst')
            ->join('product_stocks as ps', 'ps.id', '=', 'pst.product_stock_id')
            ->join('products as p', 'p.id', '=', 'ps.product_id')
            ->leftJoin('branches as fb', 'fb.id', '=', 'pst.from_branch_id')
            ->leftJoin('branches as tb', 'tb.id', '=', 'pst.to_branch_id')
            ->whereIn('ps.product_id', $productIds)
            ->whereBetween('pst.created_at', [$fromDate, $toDate])
            ->when(!$includeInternalTransfer, fn($query) => $query->where('pst.reason', '!=', StockReason::BRANCH_TRANSFER))
            ->when(!empty($branchIds), function ($query) use ($branchIds) {
                $query->where(function ($nestedQuery) use ($branchIds) {
                    $nestedQuery->whereIn('pst.from_branch_id', $branchIds)
                        ->orWhereIn('pst.to_branch_id', $branchIds);
                });
            })
            ->select([
                'pst.id',
                'pst.created_at',
                'pst.type',
                'pst.quantity',
                'pst.reason',
                'pst.remarks',
                'pst.from_branch_id',
                'pst.to_branch_id',
                'ps.product_id',
                'ps.variant',
                'p.name as product_name',
                'fb.branch_name as from_branch_name',
                'tb.branch_name as to_branch_name',
            ])
            ->orderByDesc('pst.id')
            ->get();

        return $rows->map(function ($row) use ($branchMap) {
            $type = strtoupper((string)$row->type);
            $classified = $this->classifyStockTransaction(
                reason: (string)$row->reason,
                type: $type,
                remarks: (string)($row->remarks ?? '')
            );

            $fallbackBranchName = translate('system');
            $branchId = $type === 'IN' ? (int)($row->to_branch_id ?? 0) : (int)($row->from_branch_id ?? 0);
            $branchName = $type === 'IN'
                ? ((string)($row->to_branch_name ?: $row->from_branch_name ?: ($branchMap->get($branchId) ?? $fallbackBranchName)))
                : ((string)($row->from_branch_name ?: $row->to_branch_name ?: ($branchMap->get($branchId) ?? $fallbackBranchName)));

            return (object)[
                'id' => (int)$row->id,
                'date' => $row->created_at,
                'type' => $type,
                'quantity' => (int)$row->quantity,
                'reason' => (string)$row->reason,
                'category' => $classified['label'],
                'summary_group' => $classified['summaryGroup'],
                'summary_key' => $classified['summaryKey'],
                'product_id' => (int)$row->product_id,
                'product_name' => (string)$row->product_name,
                'variation' => $row->variant ?: null,
                'branch_id' => $branchId,
                'branch_name' => $branchName,
                'from_branch_name' => $row->from_branch_name,
                'to_branch_name' => $row->to_branch_name,
                'reference' => $this->buildReferenceText((string)$row->reason, (string)($row->remarks ?? '')),
                'remarks' => (string)($row->remarks ?? ''),
            ];
        });
    }

    private function buildMovementSummary(Collection $movementRows): array
    {
        $summary = [
            'stock_in' => [
                'initial_stock' => 0,
                'manual_adjust_add' => 0,
                'returns' => 0,
            ],
            'stock_out' => [
                'sales_pos' => 0,
                'sales_online' => 0,
                'sales_wholesale_transfer' => 0,
                'manual_adjust_negative' => 0,
            ],
            'internal_transfer' => [
                'in' => 0,
                'out' => 0,
            ],
            'totals' => [
                'in' => 0,
                'out' => 0,
            ],
        ];

        foreach ($movementRows as $row) {
            if ($row->type === 'IN') {
                $summary['totals']['in'] += (int)$row->quantity;
            } else {
                $summary['totals']['out'] += (int)$row->quantity;
            }

            if ($row->summary_group === 'stock_in' && isset($summary['stock_in'][$row->summary_key])) {
                $summary['stock_in'][$row->summary_key] += (int)$row->quantity;
                continue;
            }

            if ($row->summary_group === 'stock_out' && isset($summary['stock_out'][$row->summary_key])) {
                $summary['stock_out'][$row->summary_key] += (int)$row->quantity;
                continue;
            }

            if ($row->summary_group === 'internal_transfer') {
                $transferKey = $row->type === 'IN' ? 'in' : 'out';
                $summary['internal_transfer'][$transferKey] += (int)$row->quantity;
            }
        }

        return $summary;
    }

    private function classifyStockTransaction(string $reason, string $type, string $remarks): array
    {
        $normalizedReason = strtoupper(trim($reason));
        $normalizedType = strtoupper(trim($type));
        $normalizedRemarks = strtoupper($remarks);

        if ($normalizedReason === StockReason::INITIAL_STOCK && $normalizedType === 'IN') {
            return ['summaryGroup' => 'stock_in', 'summaryKey' => 'initial_stock', 'label' => translate('initial_stock')];
        }

        if ($normalizedReason === StockReason::MANUAL_ADJUSTMENT && $normalizedType === 'IN') {
            return ['summaryGroup' => 'stock_in', 'summaryKey' => 'manual_adjust_add', 'label' => translate('manual_adjust_add')];
        }

        if (in_array($normalizedReason, [StockReason::RETURN, StockReason::ORDER_CANCELLED], true) && $normalizedType === 'IN') {
            return ['summaryGroup' => 'stock_in', 'summaryKey' => 'returns', 'label' => translate('returns')];
        }

        if ($normalizedReason === StockReason::WHOLESALE_DELIVERY && $normalizedType === 'OUT') {
            return ['summaryGroup' => 'stock_out', 'summaryKey' => 'sales_wholesale_transfer', 'label' => translate('sales_wholesale_transfer')];
        }

        if ($normalizedReason === StockReason::MANUAL_ADJUSTMENT && $normalizedType === 'OUT') {
            return ['summaryGroup' => 'stock_out', 'summaryKey' => 'manual_adjust_negative', 'label' => translate('manual_adjust_negative')];
        }

        if ($normalizedReason === StockReason::ORDER_PLACED && $normalizedType === 'OUT') {
            if (str_contains($normalizedRemarks, 'POS')) {
                return ['summaryGroup' => 'stock_out', 'summaryKey' => 'sales_pos', 'label' => translate('sales_pos')];
            }
            return ['summaryGroup' => 'stock_out', 'summaryKey' => 'sales_online', 'label' => translate('sales_online')];
        }

        if ($normalizedReason === StockReason::BRANCH_TRANSFER) {
            return ['summaryGroup' => 'internal_transfer', 'summaryKey' => 'branch_transfer', 'label' => translate('internal_branch_transfer')];
        }

        if ($normalizedType === 'IN') {
            return ['summaryGroup' => 'stock_in', 'summaryKey' => 'returns', 'label' => translate('stock_in')];
        }

        return ['summaryGroup' => 'stock_out', 'summaryKey' => 'sales_online', 'label' => translate('stock_out')];
    }

    private function buildReferenceText(string $reason, string $remarks): string
    {
        $reasonText = ucwords(str_replace('_', ' ', strtolower($reason)));
        if (trim($remarks) === '') {
            return $reasonText;
        }

        return $reasonText . ' - ' . $remarks;
    }

    private function buildDateTrend(Carbon $fromDate, Carbon $toDate, Collection $movementRows): array
    {
        $period = CarbonPeriod::create($fromDate->copy()->startOfDay(), $toDate->copy()->startOfDay());
        $labels = [];
        $map = [];

        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $map[$key] = ['in' => 0, 'out' => 0];
        }

        foreach ($movementRows as $row) {
            $key = Carbon::parse($row->date)->format('Y-m-d');
            if (!isset($map[$key])) {
                continue;
            }

            if ($row->type === 'IN') {
                $map[$key]['in'] += (int)$row->quantity;
            } else {
                $map[$key]['out'] += (int)$row->quantity;
            }
        }

        return [
            'labels' => $labels,
            'in' => array_values(array_map(fn($row) => (int)$row['in'], $map)),
            'out' => array_values(array_map(fn($row) => (int)$row['out'], $map)),
        ];
    }

}
