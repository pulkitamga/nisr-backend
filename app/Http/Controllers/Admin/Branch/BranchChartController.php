<?php

namespace App\Http\Controllers\Admin\Branch;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Product;
use App\Domain\Stock\Support\VariantMatcher;
use App\Enums\StockReason;
use App\Models\OrderDetail;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use App\Models\StockTransfers;
use App\Services\ReportPdfService;
use Illuminate\Http\JsonResponse;
use App\Exports\BranchStockExport;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\BaseAdminController;
use App\Models\StockTransferProduct;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProductStockTransaction;
use App\Models\ManageBranchProductStock;
use Illuminate\Support\Facades\Validator;

class BranchChartController extends BaseAdminController
{

    public function index(?Request $request = null, string $type = null): View
    {
        $branches = Branch::where('status', 'active')->get();
        $agents   = Admin::where('status', 1)->get();
        $products = Product::where('status', 1)->get();

        $currentYear = date('Y');
        $years = [];
        for ($y = $currentYear; $y >= 2020; $y--) {
            $years[] = $y;
        }

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = Carbon::create()->month($m)->format('F');
        }

        return view(
            'admin-views.branch-management.sales-chart.sale',
            compact('branches', 'agents', 'years', 'months', 'products')
        );
    }

    public function getChartData(Request $request): JsonResponse
    {
        $branchId      = $request->branch_id;
        $productId     = $request->product_id;
        $variationType = $request->variation_type;
        $dateType      = $request->date_type;

        $hasAnyFilter = $branchId || $productId || $variationType || $dateType;

        // ✅ FIRST LOAD / RESET → GLOBAL VIEW
        if (!$hasAnyFilter) {
            return $this->getGlobalStockData();
        }

        // ✅ FILTERED VIEW
        return $this->getFilteredStockData($request);
    }

    private function getGlobalStockData(): JsonResponse
    {
        // 1️⃣ Get active branches once
        $branches = Branch::where('status', 'active')
            ->select('id', 'branch_name')
            ->get();

        if ($branches->isEmpty()) {
            return response()->json([
                'success' => true,
                'mode'    => 'global-branch',
                'total_stats' => [
                    'current_stock' => 0,
                    'total_in'      => 0,
                    'total_out'     => 0,
                ],
                'branches' => []
            ]);
        }

        $branchIds = $branches->pluck('id')->toArray();

        // 2️⃣ Single aggregation query for ALL branches
        $txRows = ProductStockTransaction::query()
            ->where('reason', '!=', StockReason::BRANCH_TRANSFER)
            ->where(function ($q) use ($branchIds) {
                $q->whereIn('to_branch_id', $branchIds)
                    ->orWhereIn('from_branch_id', $branchIds);
            })
            ->selectRaw("
            COALESCE(to_branch_id, from_branch_id) AS branch_id,
            SUM(CASE WHEN type = 'IN'  AND to_branch_id IS NOT NULL THEN quantity ELSE 0 END) AS total_in,
            SUM(CASE WHEN type = 'OUT' AND from_branch_id IS NOT NULL THEN quantity ELSE 0 END) AS total_out,
            MAX(created_at) AS last_updated
        ")
            ->groupBy(DB::raw('COALESCE(to_branch_id, from_branch_id)'))
            ->get()
            ->keyBy('branch_id');

        // 3️⃣ Map data back to branches (NO extra queries)
        $branchData = [];
        $overallIn = 0;
        $overallOut = 0;

        foreach ($branches as $branch) {
            $row = $txRows[$branch->id] ?? null;

            $totalIn  = (int) ($row->total_in ?? 0);
            $totalOut = (int) ($row->total_out ?? 0);

            $overallIn  += $totalIn;
            $overallOut += $totalOut;

            $branchData[] = [
                'branch_id'     => $branch->id,
                'branch_name'   => $branch->branch_name,
                'current_stock' => $totalIn - $totalOut,
                'total_in'      => $totalIn,
                'total_out'     => $totalOut,
                'last_updated'  => $row->last_updated ?? null,
            ];
        }

        // 4️⃣ Final response (same structure)
        return response()->json([
            'success' => true,
            'mode'    => 'global-branch',
            'total_stats' => [
                'current_stock' => $overallIn - $overallOut,
                'total_in'      => $overallIn,
                'total_out'     => $overallOut,
            ],
            'branches' => $branchData
        ]);
    }

    private function getFilteredStockData(Request $request): JsonResponse
    {
        $branchId      = $request->branch_id;
        $productId     = $request->product_id;
        $variationType = $request->variation_type;
        $dateType      = $request->date_type;

        /* =====================================================
       BASE TRANSACTION QUERY
    ====================================================== */

        $tx = ProductStockTransaction::query()
            ->join('product_stocks', 'product_stocks.id', '=', 'product_stock_transactions.product_stock_id')
            ->when(
                $productId,
                fn($q) =>
                $q->where('product_stocks.product_id', $productId)
            )
            ->when(
                $variationType,
                fn($q) =>
                $q->where('product_stocks.variant', $variationType)
            );

        /* =====================================================
       SAFE DATE FILTER
    ====================================================== */

        if ($dateType) {
            if ($dateType === 'custom' && $request->from_date && $request->to_date) {
                $start = Carbon::parse($request->from_date)->startOfDay();
                $end   = Carbon::parse($request->to_date)->endOfDay();
            } else {
                [$start, $end] = match ($dateType) {
                    'day'   => [now()->startOfDay(), now()->endOfDay()],
                    'week'  => [now()->startOfWeek(), now()->endOfWeek()],
                    'month' => [now()->startOfMonth(), now()->endOfMonth()],
                    'year'  => [now()->startOfYear(), now()->endOfYear()],
                    default => [null, null],
                };
            }

            if ($start && $end) {
                $tx->whereBetween('product_stock_transactions.created_at', [$start, $end]);
            }
        }
        /* =====================================================
   1️⃣ BRANCH + ALL PRODUCTS (PER PRODUCT ROW)
====================================================== */

        if ($branchId && !$productId) {

            $rows = ProductStockTransaction::query()
                ->join('product_stocks', 'product_stocks.id', '=', 'product_stock_transactions.product_stock_id')
                ->join('products', 'products.id', '=', 'product_stocks.product_id')
                ->where(function ($q) use ($branchId) {
                    $q->where('to_branch_id', $branchId)
                        ->orWhere('from_branch_id', $branchId);
                })
                ->selectRaw("
        products.id AS product_id,
        products.name AS product_name,
        SUM(CASE WHEN type='IN'  AND to_branch_id = ? THEN quantity ELSE 0 END) AS total_in,
        SUM(CASE WHEN type='OUT' AND from_branch_id = ? THEN quantity ELSE 0 END) AS total_out,
        MAX(product_stock_transactions.created_at) AS last_updated
    ", [$branchId, $branchId])
                ->groupBy('products.id', 'products.name')
                ->get();


            $products = [];

            foreach ($rows as $row) {
                $currentStock = (int)$row->total_in - (int)$row->total_out;
                if ($currentStock <= 0) continue;

                $products[] = [
                    'branch_id'     => $branchId,
                    'branch_name'   => Branch::find($branchId)?->branch_name,
                    'product_id'    => $row->product_id,
                    'product_name'  => $row->product_name,
                    'current_stock' => $currentStock,
                    'total_in'      => (int)$row->total_in,
                    'total_out'     => (int)$row->total_out,
                    'last_updated'  => $row->last_updated,
                ];
            }

            return response()->json([
                'success' => true,
                'mode'    => 'branch-products',
                'products' => $products,
                'total_stats' => [
                    'current_stock' => array_sum(array_column($products, 'current_stock')),
                    'total_in'      => array_sum(array_column($products, 'total_in')),
                    'total_out'     => array_sum(array_column($products, 'total_out')),
                ],
            ]);
        }


        /* =====================================================
       1️⃣ BRANCH-SPECIFIC VIEW (Transfers allowed)
    ====================================================== */

        if ($branchId) {
            $rows = (clone $tx)
                ->where(
                    fn($q) =>
                    $q->where('to_branch_id', $branchId)
                        ->orWhere('from_branch_id', $branchId)
                )
                ->selectRaw("
                SUM(CASE WHEN type='IN'  AND to_branch_id = ? THEN quantity ELSE 0 END) AS total_in,
                SUM(CASE WHEN type='OUT' AND from_branch_id = ? THEN quantity ELSE 0 END) AS total_out,
                MAX(product_stock_transactions.created_at) AS last_updated
            ", [$branchId, $branchId])
                ->first();

            return response()->json([
                'success' => true,
                'mode'    => 'branch-single',
                'branches' => [[
                    'branch_id'     => $branchId,
                    'branch_name'   => Branch::find($branchId)?->getTranslatedField('branch_name') ?? translate('not_available'),
                    'current_stock' => ($rows->total_in ?? 0) - ($rows->total_out ?? 0),
                    'total_in'      => (int) ($rows->total_in ?? 0),
                    'total_out'     => (int) ($rows->total_out ?? 0),
                    'last_updated'  => $rows->last_updated,
                ]],
                'total_stats' => [
                    'current_stock' => ($rows->total_in ?? 0) - ($rows->total_out ?? 0),
                    'total_in'      => (int) ($rows->total_in ?? 0),
                    'total_out'     => (int) ($rows->total_out ?? 0),
                ],
            ]);
        }

        /* =====================================================
       2️⃣ PRODUCT ACROSS BRANCHES (NO TRANSFER COUNT)
    ====================================================== */

        if ($productId) {
            $branches = Branch::where('status', 'active')->get();
            $branchData = [];
            $variantMatcher = app(VariantMatcher::class);

            foreach ($branches as $branch) {
                $branchStockRows = ManageBranchProductStock::where('branch_id', $branch->id)
                    ->where('product_id', $productId)
                    ->get();

                if ($variationType) {
                    $branchStockRows = $branchStockRows->filter(function ($row) use ($variationType, $variantMatcher) {
                        return $variantMatcher->matches($variationType, $row->variation_type)
                            || $variantMatcher->matches($variationType, $row->variation_key);
                    })->values();
                } else {
                    $branchStockRows = $branchStockRows->filter(function ($row) use ($variantMatcher) {
                        return $variantMatcher->isDefault($row->variation_type)
                            || $variantMatcher->isDefault($row->variation_key);
                    })->values();
                }

                $branchStock = (int)$branchStockRows->sum('current_stock');

                if ($branchStock <= 0) continue;

                $lastUpdated = optional($branchStockRows->sortByDesc('updated_at')->first())->updated_at;

                $branchData[] = [
                    'branch_id'     => $branch->id,
                    'branch_name'   => $branch->branch_name,
                    'current_stock' => $branchStock,
                    'last_updated'  => $lastUpdated,
                    'total_in'      => null, // Add these to maintain consistent structure
                    'total_out'     => null,
                ];
            }

            return response()->json([
                'success' => true,
                'mode'    => 'global-branch',
                'branches' => $branchData,
                'total_stats' => [
                    'current_stock' => array_sum(array_column($branchData, 'current_stock')),
                    'total_in'      => null,
                    'total_out'     => null,
                ],
            ]);
        }

        /* =====================================================
       3️⃣ DEFAULT → GLOBAL VIEW
    ====================================================== */

        return $this->getGlobalStockData();
    }

    private function generatePeriods($startDate, $endDate, $periodType)
    {
        $periods = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        switch ($periodType) {
            case 'daily':
                while ($current <= $end) {
                    $periods[] = $current->format('d M');
                    $current->addDay();
                }
                break;

            case 'weekly':
                $current->startOfWeek();
                while ($current <= $end) {
                    $weekEnd = $current->copy()->endOfWeek();
                    if ($weekEnd > $end) {
                        $weekEnd = $end;
                    }
                    $periods[] = $current->format('d M') . ' - ' . $weekEnd->format('d M');
                    $current->addWeek();
                }
                break;

            case 'monthly':
                $current->startOfMonth();
                while ($current <= $end) {
                    $periods[] = $current->format('M Y');
                    $current->addMonth();
                }
                break;

            case 'year':
                $current->startOfYear();
                while ($current <= $end) {
                    $periods[] = $current->format('Y');
                    $current->addYear();
                }
                break;

            default:
                while ($current <= $end) {
                    $periods[] = $current->format('d M');
                    $current->addDay();
                }
        }

        return $periods;
    }

    /**
     * Get transfer period based on period type
     */
    private function getTransferPeriod($date, $periodType)
    {
        switch ($periodType) {
            case 'daily':
                return $date->format('d M');
            case 'weekly':
                $weekStart = $date->copy()->startOfWeek();
                $weekEnd = $date->copy()->endOfWeek();
                return $weekStart->format('d M') . ' - ' . $weekEnd->format('d M');
            case 'monthly':
                return $date->format('M Y');
            case 'year':
                return $date->format('Y');
            default:
                return $date->format('d M');
        }
    }

    /**
     * Get CRM data
     */
    public function agentCRMReport()
    {
        return view('admin-views.report.crm-chart-report');
    }

    public function export(Request $request)
    {
        try {

            $exportType = $request->input('export_type', 'excel');
            $chartImage = $request->input('chart_image');
            $locale = $request->input('locale', app()->getLocale());

            // Normalize variation type
            if ($request->variation_type) {
                $request->merge([
                    'variation_type' => rtrim($request->variation_type, '-')
                ]);
            }

            // 🔥 SAME logic as chart
            $hasAnyFilter =
                $request->branch_id ||
                $request->product_id ||
                $request->variation_type ||
                $request->date_type;

            if (!$hasAnyFilter) {
                $response = $this->getGlobalStockData();
            } else {
                $response = $this->getFilteredStockData($request);
            }

            $data = $response->getData(true);

            $branches = $data['branches'] ?? [];
            $totalStats = $data['total_stats'] ?? [];

            $product = null;
            if ($request->product_id) {
                $product = Product::find($request->product_id);
            }

            $fileName = 'branch_stock_report_' . now()->format('Y_m_d_H_i_s');

            if ($exportType === 'pdf') {
                return $this->exportToPDF(
                    $branches,
                    $product,
                    $request->all(),
                    $fileName,
                    $chartImage,
                    $totalStats
                );
            }

            return Excel::download(
                new BranchStockExport($branches, $product, $request->all(), $totalStats, $locale),
                $fileName . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function exportToPDF(
        $branches,
        $product,
        $filters,
        $fileName,
        $chartImage = null,
        $totalStats = []
    ) {
        // Use the date range from the request if provided
        $dateRange = $filters['date_range'] ?? 'All Time';
        $startDate = null;
        $endDate = null;

        if (!empty($filters['date_type'])) {
            switch ($filters['date_type']) {
                case 'day':
                    $startDate = now()->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case 'week':
                    $startDate = now()->startOfWeek();
                    $endDate = now()->endOfWeek();
                    break;
                case 'month':
                    $startDate = now()->startOfMonth();
                    $endDate = now()->endOfMonth();
                    break;
                case 'year':
                    $startDate = now()->startOfYear();
                    $endDate = now()->endOfYear();
                    break;
                case 'custom':
                    if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
                        $startDate = Carbon::parse($filters['from_date']);
                        $endDate = Carbon::parse($filters['to_date']);
                    }
                    break;
            }
        }

        $data = [
            'branches'   => $branches,
            'totalStats' => $totalStats,
            'product'    => $product,
            'filters'    => $filters,
            'chartImage' => $chartImage,
            'exportDate' => now()->format('d M Y H:i'),
            'dateRange'  => $dateRange, // Use the formatted date range from frontend
            'startDate'  => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate'    => $endDate ? $endDate->format('Y-m-d') : null,
            'startDateFormatted' => $startDate ? $startDate->format('d M Y') : null,
            'endDateFormatted'   => $endDate ? $endDate->format('d M Y') : null,
            'hasChart'   => !empty($chartImage),
            'report_title' => translate('branch_stock_report'),
        ];

        return app(ReportPdfService::class)->download(
            view: 'admin-views.branch-management.sales-chart.stock-pdf',
            data: $data,
            fileName: $fileName . '.pdf'
        );
    }
}
