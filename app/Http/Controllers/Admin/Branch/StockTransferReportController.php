<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Exports\StockTransferReportExport;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\StockTransfers;
use App\Services\ReportPdfService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class StockTransferReportController extends Controller
{
    public function index(): View
    {
        $branches = Branch::where('status', 'active')->orderBy('branch_name')->get();

        return view('admin-views.branch-management.stock-transfer-report', compact('branches'));
    }

    public function getTransferData(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'date_type' => 'nullable|in:this_year,this_month,this_week,today,custom_date',
                'from' => 'nullable|date',
                'to' => 'nullable|date',
                'from_branch_id' => 'nullable|exists:branches,id',
                'to_branch_id' => 'nullable|exists:branches,id', 
                'status' => 'nullable|in:pending,approved,rejected',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => translate('validation_failed'),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $report = $this->buildReportData($request);

            return response()->json([
                'success' => true,
                'transfers' => $report['transfers'],
                'chartData' => $report['chartData'],
                'statistics' => $report['statistics'],
                'periodType' => $report['periodType'],
                'filters' => $report['filters'],
            ]);
        } catch (\Throwable $exception) {
            Log::error('Stock transfer report load failed', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => translate('failed_to_load_report_data'),
            ], 500);
        }
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();

        return Excel::download(
            new StockTransferReportExport($data),
            'stock-transfer-report.xlsx'
        );
    }

    public function exportPdf(Request $request): Response
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();

        return app(ReportPdfService::class)->download(
            view: 'admin-views.branch-management.stock-transfer-report-pdf',
            data: $data,
            fileName: 'stock-transfer-report.pdf',
            orientation: 'landscape'
        );
    }

    private function buildReportData(Request $request): array
    {
        [$fromDate, $toDate, $dateType] = $this->resolveDateRange($request);

        $fromBranchId = $request->input('from_branch_id');
        $toBranchId = $request->input('to_branch_id');
        $status = strtolower((string)$request->input('status', ''));

        $query = StockTransfers::query()
            ->with([
                'fromBranch',
                'toBranch',
                'products' => function ($builder) use ($status) {
                    if ($status !== '') {
                        $builder->where('status', $status);
                    }

                    $builder->with(['product', 'category']);
                },
            ])
            ->whereDate('transfer_date', '>=', $fromDate->toDateString())
            ->whereDate('transfer_date', '<=', $toDate->toDateString())
            ->when(!empty($fromBranchId), fn($builder) => $builder->where('from_branch_id', (int)$fromBranchId))
            ->when(!empty($toBranchId), fn($builder) => $builder->where('to_branch_id', (int)$toBranchId))
            ->when($status !== '', fn($builder) => $builder->whereHas('products', fn($products) => $products->where('status', $status)));

        $transfers = $query->orderBy('transfer_date', 'desc')->get();
        $periodType = $this->resolvePeriodType($fromDate, $toDate);

        return [
            'transfers' => $transfers,
            'chartData' => $this->prepareChartData($transfers, $fromDate, $toDate, $periodType),
            'statistics' => $this->calculateStatistics($transfers),
            'periodType' => $periodType,
            'filters' => [
                'date_type' => $dateType,
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
                'from_branch_id' => $fromBranchId ? (int)$fromBranchId : null,
                'to_branch_id' => $toBranchId ? (int)$toBranchId : null,
                'status' => $status,
            ],
        ];
    }

    private function resolveDateRange(Request $request): array
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

            case 'this_year':
            default:
                $fromDate = now()->startOfYear()->startOfDay();
                $toDate = now()->endOfYear()->endOfDay();
                $dateType = 'this_year';
                break;
        }

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        return [$fromDate, $toDate, $dateType];
    }

    private function resolvePeriodType(Carbon $fromDate, Carbon $toDate): string
    {
        $daysDifference = $fromDate->diffInDays($toDate);

        if ($daysDifference > 60) {
            return 'month';
        }

        if ($daysDifference <= 7) {
            return 'weekday';
        }

        if ($daysDifference <= 31) {
            return 'day';
        }

        return 'date';
    }

    private function buildPeriodBuckets(Carbon $fromDate, Carbon $toDate, string $periodType): array
    {
        $keys = [];
        $labels = [];

        if ($periodType === 'month') {
            $period = CarbonPeriod::create($fromDate->copy()->startOfMonth(), '1 month', $toDate->copy()->endOfMonth());
            foreach ($period as $date) {
                $keys[] = $date->format('Y-m');
                $labels[] = $date->locale(app()->getLocale())->translatedFormat('M');
            }

            return [$keys, $labels];
        }

        $period = CarbonPeriod::create($fromDate->copy()->startOfDay(), $toDate->copy()->endOfDay());
        foreach ($period as $date) {
            $keys[] = $date->format('Y-m-d');

            $labels[] = match ($periodType) {
                'weekday' => $date->locale(app()->getLocale())->translatedFormat('l'),
                'day' => $date->format('j'),
                default => $date->locale(app()->getLocale())->translatedFormat('j M'),
            };
        }

        return [$keys, $labels];
    }

    private function prepareChartData(Collection $transfers, Carbon $fromDate, Carbon $toDate, string $periodType): array
    {
        [$keys, $labels] = $this->buildPeriodBuckets($fromDate, $toDate, $periodType);
        $keyIndex = array_flip($keys);

        $statuses = ['pending', 'approved', 'rejected'];
        $statusSeries = [
            'pending' => array_fill(0, count($keys), 0),
            'approved' => array_fill(0, count($keys), 0),
            'rejected' => array_fill(0, count($keys), 0),
        ];

        foreach ($transfers as $transfer) {
            $transferDate = Carbon::parse((string)$transfer->transfer_date);
            $bucketKey = $periodType === 'month' ? $transferDate->format('Y-m') : $transferDate->format('Y-m-d');
            $position = $keyIndex[$bucketKey] ?? null;
            if ($position === null) {
                continue;
            }

            $statusValues = collect($transfer->products ?? [])
                ->pluck('status')
                ->filter()
                ->map(fn($value) => strtolower((string)$value))
                ->unique();

            foreach ($statuses as $status) {
                if ($statusValues->contains($status)) {
                    $statusSeries[$status][$position] += 1;
                }
            }
        }

        $colors = [
            'pending' => '#f39c12',
            'approved' => '#2ecc71',
            'rejected' => '#e74c3c',
        ];

        return [
            'labels' => $labels,
            'datasets' => collect($statuses)->map(function (string $status) use ($statusSeries, $colors) {
                return [
                    'label' => translate($status),
                    'data' => $statusSeries[$status],
                    'backgroundColor' => ($colors[$status] ?? '#2563eb') . '80',
                    'borderColor' => $colors[$status] ?? '#2563eb',
                    'borderWidth' => 3,
                    'tension' => 0.1,
                ];
            })->values()->all(),
        ];
    }

    private function calculateStatistics(Collection $transfers): array
    {
        $stats = [
            'total_transfers' => (int)$transfers->count(),
            'pending_transfers' => 0,
            'approved_transfers' => 0,
            'rejected_transfers' => 0,
            'total_quantity' => 0,
            'top_from_branch' => null,
            'top_to_branch' => null,
        ];

        $fromBranchCounts = [];
        $toBranchCounts = [];

        foreach ($transfers as $transfer) {
            foreach (($transfer->products ?? []) as $product) {
                $status = strtolower((string)($product->status ?? ''));
                if ($status === 'pending') {
                    $stats['pending_transfers']++;
                } elseif ($status === 'approved') {
                    $stats['approved_transfers']++;
                    $stats['total_quantity'] += (int)($product->quantity ?? 0);
                } elseif ($status === 'rejected') {
                    $stats['rejected_transfers']++;
                }
            }

            if ($transfer->fromBranch) {
                $branchId = (int)$transfer->fromBranch->id;
                if (!isset($fromBranchCounts[$branchId])) {
                    $fromBranchCounts[$branchId] = [
                        'name' => (string)$transfer->fromBranch->branch_name,
                        'count' => 0,
                    ];
                }
                $fromBranchCounts[$branchId]['count']++;
            }

            if ($transfer->toBranch) {
                $branchId = (int)$transfer->toBranch->id;
                if (!isset($toBranchCounts[$branchId])) {
                    $toBranchCounts[$branchId] = [
                        'name' => (string)$transfer->toBranch->branch_name,
                        'count' => 0,
                    ];
                }
                $toBranchCounts[$branchId]['count']++;
            }
        }

        if (!empty($fromBranchCounts)) {
            $stats['top_from_branch'] = collect($fromBranchCounts)->sortByDesc('count')->first();
        }

        if (!empty($toBranchCounts)) {
            $stats['top_to_branch'] = collect($toBranchCounts)->sortByDesc('count')->first();
        }

        return $stats;
    }
}
