<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Exports\CrmSalesReportExport;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Services\ReportPdfService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class CrmSalesReportController extends Controller
{
    public function index(): View
    {
        $agents = $this->getAssignedCrmAdmins();

        return view('admin-views.branch-management.crm-sales-report', compact('agents'));
    }

    public function getSalesData(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'date_type' => 'nullable|in:this_year,this_month,this_week,today,custom_date',
                'from' => 'nullable|date',
                'to' => 'nullable|date',
                'agent_id' => 'nullable|integer|exists:admins,id',
                'agent_ids' => 'nullable|array',
                'agent_ids.*' => 'integer|exists:admins,id',
                'sale_type' => 'nullable|in:retail,wholesale',
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
                'agents' => $report['agents'],
                'pivotData' => $report['pivotData'],
                'chartData' => $report['chartData'],
                'statistics' => $report['statistics'],
                'periodType' => $report['periodType'],
                'filters' => $report['filters'],
            ]);
        } catch (\Throwable $exception) {
            Log::error('CRM sales report load failed', [
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
            new CrmSalesReportExport($data),
            'crm-sales-report.xlsx'
        );
    }

   public function exportPdf(Request $request): Response
{
    $data = $this->buildReportData($request);
      \Carbon\Carbon::setLocale(app()->getLocale());
    $data['exportedAt'] = now();
     $data['report_title'] = translate('crm_sales_report');
    // IMPORTANT: Get chart image from request (sent via POST from JavaScript)
    $chartImage = $request->input('chart_image');
    
    // Add chart image to data array
    $data['chartImage'] = $chartImage;
    
    // Add filters for display in PDF
    $data['filters'] = [
        'from' => $data['filters']['from'] ?? '-',
        'to' => $data['filters']['to'] ?? '-',
        'date_type' => $data['filters']['date_type'] ?? 'this_year',
        'sale_type' => $data['filters']['sale_type'] ? translate($data['filters']['sale_type']) : translate('all'),
        'agent' => $data['filters']['agent_id'] ? Admin::find($data['filters']['agent_id'])->name : translate('all'),
    ];

    return app(ReportPdfService::class)->download(
        view: 'admin-views.branch-management.crm-sales-report-pdf',
        data: $data,
        fileName: 'crm-sales-report.pdf',
        orientation: 'landscape'
    );
}

    private function buildReportData(Request $request): array
    {
        [$fromDate, $toDate, $dateType] = $this->resolveDateRange($request);
        $saleType = strtolower((string)$request->input('sale_type', ''));
        $agentIds = collect($request->input('agent_ids', []))
            ->map(fn($value) => (int)$value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values()
            ->all();
        $singleAgentId = (int)$request->input('agent_id', 0);
        if ($singleAgentId > 0) {
            $agentIds = [$singleAgentId];
        }

        $agents = $this->getAssignedCrmAdmins($agentIds);
        $reportAgents = $this->appendUnassignedAgentIfNeeded($agents, $agentIds);
        $periodType = $this->resolvePeriodType($fromDate, $toDate);
        $salesData = $this->getAgentSalesData($fromDate, $toDate, $reportAgents, $saleType);
        $pivotData = $this->preparePivotData($salesData, $reportAgents, $fromDate, $toDate, $periodType);

        return [
            'agents' => $agents,
            'pivotData' => $pivotData,
            'chartData' => $this->prepareChartData($pivotData),
            'statistics' => $this->calculateStatistics($pivotData, $reportAgents),
            'periodType' => $periodType,
            'filters' => [
                'date_type' => $dateType,
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
                'sale_type' => $saleType,
                'agent_id' => !empty($agentIds) ? (int)$agentIds[0] : null,
                'agent_ids' => $agentIds,
            ],
        ];
    }

    private function getAssignedCrmAdmins(array $selectedAgentIds = []): Collection
    {
        $leadEmployeeIds = Lead::query()
            ->whereNotNull('employee_id')
            ->where('employee_id', '>', 0)
            ->pluck('employee_id');
        $leadOwnerIds = Lead::query()
            ->whereNotNull('owner_id')
            ->where('owner_id', '>', 0)
            ->pluck('owner_id');
        $dealEmployeeIds = Deal::query()
            ->whereNotNull('employee_id')
            ->where('employee_id', '>', 0)
            ->pluck('employee_id');
        $dealOwnerIds = Deal::query()
            ->whereNotNull('owner_id')
            ->where('owner_id', '>', 0)
            ->pluck('owner_id');

        $assignedIds = $leadEmployeeIds
            ->merge($leadOwnerIds)
            ->merge($dealEmployeeIds)
            ->merge($dealOwnerIds)
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($assignedIds)) {
            return collect();
        }

        $query = Admin::query()
            ->active()
            ->whereIn('id', $assignedIds)
            ->orderBy('name');

        if (!empty($selectedAgentIds)) {
            $query->whereIn('id', $selectedAgentIds);
        }

        return $query->get(['id', 'name']);
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

    private function getAgentSalesData(Carbon $fromDate, Carbon $toDate, Collection $agents, string $saleType = ''): array
    {
        if ($agents->isEmpty()) {
            return [];
        }

        $orderDetailsQtySub = DB::table('order_details')
            ->select('order_id', DB::raw('SUM(qty) as order_total_qty'))
            ->groupBy('order_id');

        $data = [];

        foreach ($agents as $agent) {
            $agentId = (int)$agent->id;
            $agentOrderIds = $agentId > 0
                ? $this->getAgentOrderIds($agentId)
                : $this->getUnassignedDealOrderIds();
            if (empty($agentOrderIds)) {
                $data[(int)$agent->id] = [
                    'id' => $agentId,
                    'name' => (string)$agent->name,
                    'period_data' => [],
                ];
                continue;
            }

            $query = Order::query()
                ->from('orders')
                ->whereIn('orders.id', $agentOrderIds)
                ->where('order_status', 'delivered')
                ->whereBetween('orders.created_at', [$fromDate, $toDate]);

            if ($saleType === 'wholesale') {
                $query->where(function ($subQuery) {
                    $subQuery->where('order_amount', '>=', 10000)
                        ->orWhereHas('details.product', function ($builder) {
                            $builder->where('minimum_order_qty', '>=', 10);
                        });
                });
            } elseif ($saleType === 'retail') {
                $query->where(function ($subQuery) {
                    $subQuery->where('order_amount', '<', 10000)
                        ->whereHas('details.product', function ($builder) {
                            $builder->where('minimum_order_qty', '<', 10);
                        });
                });
            }

            $results = $query
                ->leftJoinSub($orderDetailsQtySub, 'order_detail_totals', function ($join) {
                    $join->on('order_detail_totals.order_id', '=', 'orders.id');
                })
                ->select(
                    DB::raw('DATE(orders.created_at) as report_date'),
                    DB::raw('SUM(orders.order_amount) as total_sales'),
                    DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                    DB::raw('SUM(COALESCE(order_detail_totals.order_total_qty, 0)) as total_quantity'),
                    DB::raw('CASE WHEN orders.order_amount >= 10000 THEN "wholesale" ELSE "retail" END as sale_type')
                )
                ->groupBy(DB::raw('DATE(orders.created_at)'), DB::raw('sale_type'))
                ->orderBy('report_date')
                ->get();

            $agentData = [
                'id' => $agentId,
                'name' => (string)$agent->name,
                'period_data' => [],
            ];

            foreach ($results as $row) {
                $periodKey = Carbon::parse((string)$row->report_date)->format('Y-m-d');
                $typeKey = strtolower((string)$row->sale_type) === 'wholesale' ? 'wholesale' : 'retail';
                $agentData['period_data'][$periodKey][$typeKey] = [
                    'sales' => (float)$row->total_sales,
                    'orders' => (int)$row->total_orders,
                    'quantity' => (int)$row->total_quantity,
                ];
            }

            $data[(int)$agent->id] = $agentData;
        }

        return $data;
    }

    private function getAgentOrderIds(int $agentId): array
    {
        if ($agentId <= 0) {
            return [];
        }

        return Deal::query()
            ->where(function ($query) use ($agentId) {
                $query->where('owner_id', $agentId)
                    ->orWhere('employee_id', $agentId);
            })
            ->whereNotNull('order_id')
            ->pluck('order_id')
            ->map(fn($value) => (int)$value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function getUnassignedDealOrderIds(): array
    {
        return Deal::query()
            ->where(function ($query) {
                $query->whereNull('owner_id')
                    ->orWhere('owner_id', 0);
            })
            ->where(function ($query) {
                $query->whereNull('employee_id')
                    ->orWhere('employee_id', 0);
            })
            ->whereNotNull('order_id')
            ->pluck('order_id')
            ->map(fn($value) => (int)$value)
            ->filter(fn($value) => $value > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function appendUnassignedAgentIfNeeded(Collection $agents, array $selectedAgentIds): Collection
    {
        if (!empty($selectedAgentIds)) {
            return $agents;
        }

        if (empty($this->getUnassignedDealOrderIds())) {
            return $agents;
        }

        $extended = $agents->values();
        $extended->push((object)[
            'id' => 0,
            'name' => translate('unassigned'),
        ]);

        return $extended;
    }

    private function buildPeriodBuckets(Carbon $fromDate, Carbon $toDate, string $periodType): array
    {
        $buckets = [];

        if ($periodType === 'month') {
            $period = CarbonPeriod::create($fromDate->copy()->startOfMonth(), '1 month', $toDate->copy()->endOfMonth());
            foreach ($period as $date) {
                $buckets[] = [
                    'key' => $date->format('Y-m'),
                    'label' => $date->locale(app()->getLocale())->translatedFormat('M'),
                ];
            }

            return $buckets;
        }

        $period = CarbonPeriod::create($fromDate->copy()->startOfDay(), $toDate->copy()->endOfDay());
        foreach ($period as $date) {
            $buckets[] = [
                'key' => $date->format('Y-m-d'),
                'label' => match ($periodType) {
                    'weekday' => $date->locale(app()->getLocale())->translatedFormat('l'),
                    'day' => $date->format('j'),
                    default => $date->locale(app()->getLocale())->translatedFormat('j M'),
                },
            ];
        }

        return $buckets;
    }

    private function extractAgentPeriodValues(array $dailyData, string $periodKey, string $periodType): array
    {
        $retail = ['sales' => 0, 'orders' => 0, 'quantity' => 0];
        $wholesale = ['sales' => 0, 'orders' => 0, 'quantity' => 0];

        if ($periodType === 'month') {
            foreach ($dailyData as $dateKey => $types) {
                if (!str_starts_with((string)$dateKey, $periodKey)) {
                    continue;
                }

                $retail['sales'] += (float)data_get($types, 'retail.sales', 0);
                $retail['orders'] += (int)data_get($types, 'retail.orders', 0);
                $retail['quantity'] += (int)data_get($types, 'retail.quantity', 0);

                $wholesale['sales'] += (float)data_get($types, 'wholesale.sales', 0);
                $wholesale['orders'] += (int)data_get($types, 'wholesale.orders', 0);
                $wholesale['quantity'] += (int)data_get($types, 'wholesale.quantity', 0);
            }

            return ['retail' => $retail, 'wholesale' => $wholesale];
        }

        $types = $dailyData[$periodKey] ?? [];
        $retail['sales'] = (float)data_get($types, 'retail.sales', 0);
        $retail['orders'] = (int)data_get($types, 'retail.orders', 0);
        $retail['quantity'] = (int)data_get($types, 'retail.quantity', 0);

        $wholesale['sales'] = (float)data_get($types, 'wholesale.sales', 0);
        $wholesale['orders'] = (int)data_get($types, 'wholesale.orders', 0);
        $wholesale['quantity'] = (int)data_get($types, 'wholesale.quantity', 0);

        return ['retail' => $retail, 'wholesale' => $wholesale];
    }

    private function preparePivotData(array $salesData, Collection $agents, Carbon $fromDate, Carbon $toDate, string $periodType): array
    {
        $pivotData = [];
        $periodBuckets = $this->buildPeriodBuckets($fromDate, $toDate, $periodType);

        foreach ($periodBuckets as $bucket) {
            $periodKey = (string)$bucket['key'];
            $pivotData[$periodKey] = [
                'period' => (string)$bucket['label'],
                'agents' => [],
                'totals' => [
                    'retail_sales' => 0,
                    'wholesale_sales' => 0,
                    'retail_orders' => 0,
                    'wholesale_orders' => 0,
                    'retail_quantity' => 0,
                    'wholesale_quantity' => 0,
                    'total_sales' => 0,
                    'total_orders' => 0,
                    'total_quantity' => 0,
                ],
            ];

            foreach ($agents as $agent) {
                $dailyData = data_get($salesData, (int)$agent->id . '.period_data', []);
                $metrics = $this->extractAgentPeriodValues($dailyData, $periodKey, $periodType);
                $retail = $metrics['retail'];
                $wholesale = $metrics['wholesale'];

                $pivotData[$periodKey]['agents'][(int)$agent->id] = [
                    'retail_sales' => $retail['sales'],
                    'wholesale_sales' => $wholesale['sales'],
                    'retail_orders' => $retail['orders'],
                    'wholesale_orders' => $wholesale['orders'],
                    'retail_quantity' => $retail['quantity'],
                    'wholesale_quantity' => $wholesale['quantity'],
                    'total_sales' => $retail['sales'] + $wholesale['sales'],
                    'total_orders' => $retail['orders'] + $wholesale['orders'],
                    'total_quantity' => $retail['quantity'] + $wholesale['quantity'],
                ];

                $pivotData[$periodKey]['totals']['retail_sales'] += $retail['sales'];
                $pivotData[$periodKey]['totals']['wholesale_sales'] += $wholesale['sales'];
                $pivotData[$periodKey]['totals']['retail_orders'] += $retail['orders'];
                $pivotData[$periodKey]['totals']['wholesale_orders'] += $wholesale['orders'];
                $pivotData[$periodKey]['totals']['retail_quantity'] += $retail['quantity'];
                $pivotData[$periodKey]['totals']['wholesale_quantity'] += $wholesale['quantity'];
                $pivotData[$periodKey]['totals']['total_sales'] += $retail['sales'] + $wholesale['sales'];
                $pivotData[$periodKey]['totals']['total_orders'] += $retail['orders'] + $wholesale['orders'];
                $pivotData[$periodKey]['totals']['total_quantity'] += $retail['quantity'] + $wholesale['quantity'];
            }
        }

        return $pivotData;
    }

    private function prepareChartData(array $pivotData): array
    {
        $labels = [];
        $retailData = [];
        $wholesaleData = [];

        foreach ($pivotData as $period) {
            $labels[] = (string)data_get($period, 'period', '');
            $retailData[] = (float)data_get($period, 'totals.retail_sales', 0);
            $wholesaleData[] = (float)data_get($period, 'totals.wholesale_sales', 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => translate('retail_sales'),
                    'data' => $retailData,
                    'borderColor' => '#3498db',
                    'backgroundColor' => '#3498db80',
                    'borderWidth' => 4,
                    'tension' => 0.3,
                    'fill' => true,
                ],
                [
                    'label' => translate('wholesale_sales'),
                    'data' => $wholesaleData,
                    'borderColor' => '#2ecc71',
                    'backgroundColor' => '#2ecc7180',
                    'borderWidth' => 4,
                    'tension' => 0.3,
                    'fill' => true,
                ],
            ],
        ];
    }

    private function calculateStatistics(array $pivotData, Collection $agents): array
    {
        $stats = [
            'total_sales' => 0,
            'total_orders' => 0,
            'total_quantity' => 0,
            'retail_sales' => 0,
            'wholesale_sales' => 0,
            'top_agent' => null,
            'retail_percentage' => 0,
            'wholesale_percentage' => 0,
        ];

        $agentTotals = [];
        foreach ($agents as $agent) {
            if ((int)$agent->id <= 0) {
                continue;
            }
            $agentTotals[(int)$agent->id] = [
                'name' => (string)$agent->name,
                'total' => 0,
            ];
        }

        foreach ($pivotData as $period) {
            $stats['total_sales'] += (float)data_get($period, 'totals.total_sales', 0);
            $stats['total_orders'] += (int)data_get($period, 'totals.total_orders', 0);
            $stats['total_quantity'] += (int)data_get($period, 'totals.total_quantity', 0);
            $stats['retail_sales'] += (float)data_get($period, 'totals.retail_sales', 0);
            $stats['wholesale_sales'] += (float)data_get($period, 'totals.wholesale_sales', 0);

            foreach ((array)data_get($period, 'agents', []) as $agentId => $agentRow) {
                if (!isset($agentTotals[(int)$agentId])) {
                    continue;
                }
                $agentTotals[(int)$agentId]['total'] += (float)data_get($agentRow, 'total_sales', 0);
            }
        }

        if (!empty($agentTotals)) {
            $topAgent = collect($agentTotals)->sortByDesc('total')->first();
            if (($topAgent['total'] ?? 0) > 0) {
                $stats['top_agent'] = $topAgent['name'] . ' (' . setCurrencySymbol(
                    amount: usdToDefaultCurrency(amount: (float)$topAgent['total']),
                    currencyCode: getCurrencyCode()
                ) . ')';
            }
        }

        if ($stats['total_sales'] > 0) {
            $stats['retail_percentage'] = round(($stats['retail_sales'] / $stats['total_sales']) * 100, 2);
            $stats['wholesale_percentage'] = round(($stats['wholesale_sales'] / $stats['total_sales']) * 100, 2);
        }

        return $stats;
    }
}
