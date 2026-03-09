<?php

namespace App\Http\Controllers\Admin\Crm;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Deal;
use App\Models\Departments;
use App\Models\InboxMessage;
use App\Models\Lead;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Services\ReportPdfService;
use Illuminate\Support\Facades\DB;
use App\Exports\CRMAnalyticsExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Support\AdminPermissionRegistry;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;


class DashboardChartController extends Controller
{
    public function messageStats(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->subDays(6)->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());
        $departmentId = $request->input('department_id');
        $pipeline = $request->input('pipeline');

        $query = InboxMessage::query()
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        if ($pipeline) {
          $query->where('pipeline', $pipeline); 
}

        $totalMessages = $query->count();

        $assignedMessages = clone $query;
        $assignedCount = $assignedMessages
            ->whereNotNull('department_id')
            ->where('department_id', '!=', 0)
            ->count();


        $pendingCount = $totalMessages - $assignedCount;

        $statusBreakdown = InboxMessage::select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->when($departmentId, function ($q) use ($departmentId) {
                return $q->where('department_id', $departmentId);
            })
            ->when($pipeline, fn($q) => $q->where('pipeline', $pipeline))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $departmentBreakdown = Departments::select(
            'departments.id',
            'departments.name',
            DB::raw('COUNT(inbox_messages.id) as message_count')
        )
            ->leftJoin('inbox_messages', function ($join) use ($startDate, $endDate) {
                $join->on('departments.id', '=', 'inbox_messages.department_id')
                    ->whereBetween('inbox_messages.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->groupBy('departments.id', 'departments.name')
            ->get();

        $employeeStats = Admin::select(
            'admins.id',
            'admins.name',
            'departments.name as department_name',
            DB::raw('COUNT(inbox_messages.id) as assigned_count')
        )
            ->leftJoin('departments', 'admins.department_id', '=', 'departments.id')
            ->leftJoin('inbox_messages', function ($join) use ($startDate, $endDate) {
                $join->on('admins.id', '=', 'inbox_messages.employee_id')
                    ->whereBetween('inbox_messages.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->whereDoesntHave('roles', function ($query) {
                $query->where('roles.name', AdminPermissionRegistry::superAdminRole())
                    ->where('roles.guard_name', config('permissions_admin.guard', 'admin'));
            })
            ->groupBy('admins.id', 'admins.name', 'departments.name')
            ->get();

        $monthlyTrend = InboxMessage::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN department_id IS NOT NULL AND department_id != 0 THEN 1 ELSE 0 END) as assigned'),
            DB::raw('SUM(CASE WHEN department_id IS NULL OR department_id = 0 THEN 1 ELSE 0 END) as pending')

        )
            ->whereBetween('created_at', [
                Carbon::now()->subMonths(6)->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])
            ->when($departmentId, function ($q) use ($departmentId) {
                return $q->where('department_id', $departmentId);
            })
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total' => $totalMessages,
                    'assigned' => $assignedCount,
                    'pending' => $pendingCount,
                    'assigned_percentage' => $totalMessages > 0 ? round(($assignedCount / $totalMessages) * 100, 2) : 0,
                ],
                'status_breakdown' => $statusBreakdown,
                'department_breakdown' => $departmentBreakdown,
                'employee_stats' => $employeeStats,
                'monthly_trend' => $monthlyTrend,
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'department_id' => $departmentId
                ]
            ]
        ]);
    }
    public function getChartData(Request $request)
{
    try {

        $startDate   = $request->input('start_date', Carbon::today()->subDay(6)->toDateString());
        $endDate     = $request->input('end_date', Carbon::today()->toDateString());
        $departmentId = $request->input('department_id');
        $messageType  = $request->input('message_type');
        $status       = $request->input('status');
        $pipeline     = $request->input('pipeline');
        $groupBy      = $request->input('group_by', 'daily');

        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        $allData = collect();

        /*
        |--------------------------------------------------------------------------
        | DAILY GROUPING
        |--------------------------------------------------------------------------
        */
        if ($groupBy === 'daily') {

            $query = InboxMessage::whereBetween('created_at', [$start, $end]);

            if ($departmentId) $query->where('department_id', $departmentId);
            if ($messageType)  $query->where('convert_sub_type', $messageType);
            if ($status)       $query->where('status', $status);
            if ($pipeline)     $query->where('pipeline', $pipeline);

            $stats = $query->select(
                DB::raw('DATE(created_at) as period'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN department_id IS NOT NULL AND department_id != 0 THEN 1 ELSE 0 END) as assigned'),
                DB::raw('SUM(CASE WHEN department_id IS NULL OR department_id = 0 THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as converted'),
                DB::raw('SUM(CASE WHEN status = "ignored" THEN 1 ELSE 0 END) as ignored'),
                DB::raw('SUM(CASE WHEN status = "spam" THEN 1 ELSE 0 END) as spam')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

            // Fill missing days
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

                $dateStr = $date->toDateString();
                $row = $stats->firstWhere('period', $dateStr);

                $allData->push([
                    'period'    => $dateStr,
                    'total'     => $row->total ?? 0,
                    'assigned'  => $row->assigned ?? 0,
                    'pending'   => $row->pending ?? 0,
                    'converted' => $row->converted ?? 0,
                    'ignored'   => $row->ignored ?? 0,
                    'spam'      => $row->spam ?? 0,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | WEEKLY GROUPING (Week 1, Week 2 inside selected range)
        |--------------------------------------------------------------------------
        */
        elseif ($groupBy === 'weekly') {

            $weekCounter = 1;

            while ($start->lte($end)) {

                $weekStart = $start->copy();
                $weekEnd   = $start->copy()->addDays(6);

                if ($weekEnd->gt($end)) {
                    $weekEnd = $end->copy();
                }

                $query = InboxMessage::whereBetween('created_at', [$weekStart, $weekEnd]);

                if ($departmentId) $query->where('department_id', $departmentId);
                if ($messageType)  $query->where('convert_sub_type', $messageType);
                if ($status)       $query->where('status', $status);
                if ($pipeline)     $query->where('pipeline', $pipeline);

                $stats = $query->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN department_id IS NOT NULL AND department_id != 0 THEN 1 ELSE 0 END) as assigned,
                    SUM(CASE WHEN department_id IS NULL OR department_id = 0 THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as converted,
                    SUM(CASE WHEN status = "ignored" THEN 1 ELSE 0 END) as ignored,
                    SUM(CASE WHEN status = "spam" THEN 1 ELSE 0 END) as spam
                ')->first();

                $allData->push([
                    'period'    => "Week {$weekCounter}",
                    'total'     => $stats->total ?? 0,
                    'assigned'  => $stats->assigned ?? 0,
                    'pending'   => $stats->pending ?? 0,
                    'converted' => $stats->converted ?? 0,
                    'ignored'   => $stats->ignored ?? 0,
                    'spam'      => $stats->spam ?? 0,
                ]);

                $start->addDays(7);
                $weekCounter++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | MONTHLY GROUPING
        |--------------------------------------------------------------------------
        */
        elseif ($groupBy === 'monthly') {

            $query = InboxMessage::whereBetween('created_at', [$start, $end]);

            if ($departmentId) $query->where('department_id', $departmentId);
            if ($messageType)  $query->where('convert_sub_type', $messageType);
            if ($status)       $query->where('status', $status);
            if ($pipeline)     $query->where('pipeline', $pipeline);

            $allData = $query->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN department_id IS NOT NULL AND department_id != 0 THEN 1 ELSE 0 END) as assigned'),
                DB::raw('SUM(CASE WHEN department_id IS NULL OR department_id = 0 THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as converted'),
                DB::raw('SUM(CASE WHEN status = "ignored" THEN 1 ELSE 0 END) as ignored'),
                DB::raw('SUM(CASE WHEN status = "spam" THEN 1 ELSE 0 END) as spam')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | PREPARE RESPONSE
        |--------------------------------------------------------------------------
        */

        $labels = $allData->pluck('period')->toArray();

        $datasets = [
            ['key'=>'total','label'=>'Total','data'=>$allData->pluck('total'),'backgroundColor'=>'#3498db'],
            ['key'=>'assigned','label'=>'Assigned','data'=>$allData->pluck('assigned'),'backgroundColor'=>'#2ecc71'],
            ['key'=>'pending','label'=>'Pending','data'=>$allData->pluck('pending'),'backgroundColor'=>'#f39c12'],
            ['key'=>'converted','label'=>'Converted','data'=>$allData->pluck('converted'),'backgroundColor'=>'#9b59b6'],
            ['key'=>'ignored','label'=>'Ignored','data'=>$allData->pluck('ignored'),'backgroundColor'=>'#e74c3c'],
            ['key'=>'spam','label'=>'Spam','data'=>$allData->pluck('spam'),'backgroundColor'=>'#95a5a6'],
        ];
        $legend = collect($datasets)->map(fn($dataset) => [
            'key' => $dataset['key'],
            'label' => $dataset['label'],
            'color' => $dataset['backgroundColor'],
        ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'labels'   => $labels,
                'datasets' => $datasets,
                'legend' => $legend,
                'summary'  => [
                    'total'     => $allData->sum('total'),
                    'assigned'  => $allData->sum('assigned'),
                    'pending'   => $allData->sum('pending'),
                    'converted' => $allData->sum('converted'),
                    'ignored'   => $allData->sum('ignored'),
                    'spam'      => $allData->sum('spam'),
                ],
                'daily_stats' => $allData
            ]
        ]);

    } catch (\Exception $e) {

        Log::error('Chart data error: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error loading chart data'
        ], 500);
    }
}


    public function getDashboardStats()
    {
        $today = Carbon::today();

        $todayStats = [
            'total_today' => InboxMessage::whereDate('created_at', $today)->count(),
            'assigned_today' => InboxMessage::whereDate('created_at', $today)
                ->whereNotNull('department_id')
                ->where('department_id', '!=', 0)
                ->count(),
            'pending_today' => InboxMessage::whereDate('created_at', $today)
                ->where(function ($q) {
                    $q->whereNull('department_id')
                        ->orWhere('department_id', 0);
                })->count(),
        ];

        $employeeCount = Admin::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('roles.name', AdminPermissionRegistry::superAdminRole())
                    ->where('roles.guard_name', config('permissions_admin.guard', 'admin'));
            })
            ->count();
        $activeEmployees = Admin::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('roles.name', AdminPermissionRegistry::superAdminRole())
                    ->where('roles.guard_name', config('permissions_admin.guard', 'admin'));
            })
            ->where('status', 1)
            ->count();

        return response()->json([
            'today_stats' => $todayStats,
            'employee_counts' => [
                'total_employees' => $employeeCount,
                'active_employees' => $activeEmployees,
                'inactive_employees' => $employeeCount - $activeEmployees
            ]
        ]);
    }
    public function chartView()
    {
        $departments = Departments::all();
        return view('admin-views.crm.charts', compact('departments'));
    }

    public function insightsReport(Request $request): View|BinaryFileResponse|Response
    {
        [$snapshotFrom, $snapshotTo] = $this->resolveInsightsDateRange($request);
        $filters = [
            'date_type' => (string)$request->input('date_type', 'this_year'),
            'from' => $snapshotFrom->toDateString(),
            'to' => $snapshotTo->toDateString(),
            'department_id' => (int)$request->input('department_id', 0),
            'owner_id' => (int)$request->input('owner_id', 0),
            'message_status' => (string)$request->input('message_status', ''),
            'deal_status' => (string)$request->input('deal_status', ''),
        ];
        $trendGrouping = $this->resolveInsightsTrendGrouping($snapshotFrom, $snapshotTo);
        $periodKeys = $this->buildInsightsPeriodKeys($snapshotFrom, $snapshotTo, $trendGrouping['unit']);

        $messageQuery = InboxMessage::query()->whereBetween('created_at', [$snapshotFrom, $snapshotTo]);
        if ($filters['department_id'] > 0) {
            $messageQuery->where('department_id', $filters['department_id']);
        }
        if ($filters['message_status'] !== '') {
            $messageQuery->where('status', $filters['message_status']);
        }

        $leadQuery = Lead::query()->whereBetween('created_at', [$snapshotFrom, $snapshotTo]);
        $dealQuery = Deal::query()->whereBetween('created_at', [$snapshotFrom, $snapshotTo]);
        if ($filters['owner_id'] > 0) {
            $dealQuery->where('owner_id', $filters['owner_id']);
        }
        if ($filters['deal_status'] !== '') {
            $dealQuery->where('status', $filters['deal_status']);
        }

        $messageCount = (clone $messageQuery)->count();
        $newMessages = (clone $messageQuery)->where('status', 'new')->count();
        $convertedMessages = (clone $messageQuery)->where('status', 'converted')->count();
        $spamMessages = (clone $messageQuery)->where('status', 'spam')->count();

        $leadCount = (clone $leadQuery)->count();
        $qualifiedLeads = (clone $leadQuery)->where('status', 'qualified')->count();
        $convertedLeads = (clone $leadQuery)->where('status', 'converted')->count();

        $dealCount = (clone $dealQuery)->count();
        $openDeals = (clone $dealQuery)->where('status', 'open')->count();
        $wonDeals = (clone $dealQuery)->where('status', 'won')->count();
        $lostDeals = (clone $dealQuery)->where('status', 'lost')->count();
        $totalDealValue = (float)(clone $dealQuery)->sum(DB::raw('COALESCE(value, 0)'));
        $avgDealValue = $dealCount > 0 ? $totalDealValue / $dealCount : 0;
        $leadToDealRate = $leadCount > 0 ? ($dealCount / $leadCount) * 100 : 0;
        $dealWinRate = $dealCount > 0 ? ($wonDeals / $dealCount) * 100 : 0;
        $messageConversionRate = $messageCount > 0 ? ($convertedMessages / $messageCount) * 100 : 0;

        $messageTrendRows = InboxMessage::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['department_id'] > 0, fn($query) => $query->where('department_id', $filters['department_id']))
            ->when($filters['message_status'] !== '', fn($query) => $query->where('status', $filters['message_status']))
            ->selectRaw($trendGrouping['select'] . ' as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->get();
        $leadTrendRows = Lead::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->selectRaw($trendGrouping['select'] . ' as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->get();
        $dealTrendRows = Deal::query()
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['owner_id'] > 0, fn($query) => $query->where('owner_id', $filters['owner_id']))
            ->when($filters['deal_status'] !== '', fn($query) => $query->where('status', $filters['deal_status']))
            ->selectRaw($trendGrouping['select'] . ' as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->get();
        $wonDealTrendRows = Deal::query()
            ->where('status', 'won')
            ->whereBetween('created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['owner_id'] > 0, fn($query) => $query->where('owner_id', $filters['owner_id']))
            ->selectRaw($trendGrouping['select'] . ' as period_key, COUNT(*) as total')
            ->groupBy('period_key')
            ->get();

        $messageTrendMap = $messageTrendRows->pluck('total', 'period_key')->all();
        $leadTrendMap = $leadTrendRows->pluck('total', 'period_key')->all();
        $dealTrendMap = $dealTrendRows->pluck('total', 'period_key')->all();
        $wonDealTrendMap = $wonDealTrendRows->pluck('total', 'period_key')->all();

        $trendLabels = [];
        $messageTrend = [];
        $leadTrend = [];
        $dealTrend = [];
        $wonDealTrend = [];
        foreach ($periodKeys as $periodKey) {
            $trendLabels[] = $this->formatInsightsPeriodLabel($periodKey, $trendGrouping['unit']);
            $messageTrend[] = (int)($messageTrendMap[$periodKey] ?? 0);
            $leadTrend[] = (int)($leadTrendMap[$periodKey] ?? 0);
            $dealTrend[] = (int)($dealTrendMap[$periodKey] ?? 0);
            $wonDealTrend[] = (int)($wonDealTrendMap[$periodKey] ?? 0);
        }

        $dealStageRows = (clone $dealQuery)
            ->selectRaw("COALESCE(NULLIF(stage, ''), 'unassigned') as stage_name")
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw("COALESCE(NULLIF(stage, ''), 'unassigned')"))
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $messageStatusRows = (clone $messageQuery)
            ->selectRaw("COALESCE(NULLIF(status, ''), 'new') as status_name")
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw("COALESCE(NULLIF(status, ''), 'new')"))
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $topOwners = Deal::query()
            ->leftJoin('admins', 'admins.id', '=', 'deals.owner_id')
            ->whereBetween('deals.created_at', [$snapshotFrom, $snapshotTo])
            ->when($filters['owner_id'] > 0, fn($query) => $query->where('deals.owner_id', $filters['owner_id']))
            ->when($filters['deal_status'] !== '', fn($query) => $query->where('deals.status', $filters['deal_status']))
            ->selectRaw('deals.owner_id')
            ->selectRaw("COALESCE(admins.name, 'Unassigned') as owner_name")
            ->selectRaw('COUNT(*) as deals_count')
            ->selectRaw('SUM(COALESCE(deals.value, 0)) as total_value')
            ->groupBy('deals.owner_id', 'admins.name')
            ->orderByDesc('total_value')
            ->limit(8)
            ->get();

        $kpi = [
            'message_count' => $messageCount,
            'new_messages' => $newMessages,
            'converted_messages' => $convertedMessages,
            'spam_messages' => $spamMessages,
            'lead_count' => $leadCount,
            'qualified_leads' => $qualifiedLeads,
            'converted_leads' => $convertedLeads,
            'deal_count' => $dealCount,
            'open_deals' => $openDeals,
            'won_deals' => $wonDeals,
            'lost_deals' => $lostDeals,
            'total_deal_value' => $totalDealValue,
            'avg_deal_value' => $avgDealValue,
            'lead_to_deal_rate' => $leadToDealRate,
            'deal_win_rate' => $dealWinRate,
            'message_conversion_rate' => $messageConversionRate,
        ];

        $trendChartData = [
            'labels' => $trendLabels,
            'messages' => $messageTrend,
            'leads' => $leadTrend,
            'deals' => $dealTrend,
            'won_deals' => $wonDealTrend,
        ];

        $dealStageChartData = [
            'labels' => $dealStageRows->pluck('stage_name')->map(fn($value) => ucwords(str_replace('_', ' ', (string)$value)))->values()->all(),
            'counts' => $dealStageRows->pluck('total')->map(fn($value) => (int)$value)->values()->all(),
        ];

        $messageStatusChartData = [
            'labels' => $messageStatusRows->pluck('status_name')->map(fn($value) => ucwords(str_replace('_', ' ', (string)$value)))->values()->all(),
            'counts' => $messageStatusRows->pluck('total')->map(fn($value) => (int)$value)->values()->all(),
        ];

        $insights = $this->buildCrmInsights(
            kpi: $kpi,
            trendLabels: $trendLabels,
            dealTrend: $dealTrend,
            wonDealTrend: $wonDealTrend,
            dealStageRows: $dealStageRows->toArray()
        );

        $download = (string)$request->input('download', '');
        if ($download === 'excel') {
            $rows = $topOwners->map(function ($owner) {
                $avgValue = (int)$owner->deals_count > 0 ? (float)$owner->total_value / (int)$owner->deals_count : 0;
                return [
                    (string)$owner->owner_name,
                    (int)$owner->deals_count,
                    round((float)$owner->total_value, 2),
                    round($avgValue, 2),
                ];
            })->values()->all();

            return Excel::download(new class($rows) implements FromArray, WithHeadings {
                public function __construct(private readonly array $rows) {}
                public function array(): array { return $this->rows; }
                public function headings(): array { return ['Owner', 'Deals', 'Total Value', 'Avg Value']; }
            }, 'crm-insights-report.xlsx');
        }
        
        $trendChart = $request->input('trend_chart');
        $stageChart = $request->input('stage_chart');
        $statusChart = $request->input('status_chart');
        if ($download === 'pdf') {
            $isRtl = app()->getLocale() === 'ar' || session('direction') === 'rtl';
            return app(ReportPdfService::class)->download(
                view: 'admin-views.crm.reports.insights-pdf',
                data: compact('kpi', 'topOwners', 'snapshotFrom', 'snapshotTo', 'isRtl','trendChart','stageChart','statusChart'),
                fileName: 'crm-insights-report.pdf'
            );
        }

        $departments = Departments::query()->orderBy('name')->get(['id', 'name']);
        $owners = $this->getAssignedCrmOwners();

        return view('admin-views.crm.reports.insights', compact(
            'kpi',
            'trendChartData',
            'dealStageChartData',
            'messageStatusChartData',
            'topOwners',
            'insights',
            'snapshotFrom',
            'snapshotTo',
            'filters',
            'departments',
            'owners'
        ));
    }

    private function resolveInsightsDateRange(Request $request): array
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

    private function resolveInsightsTrendGrouping(Carbon $fromDate, Carbon $toDate): array
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

    private function buildInsightsPeriodKeys(Carbon $fromDate, Carbon $toDate, string $unit): array
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

    private function formatInsightsPeriodLabel(string $periodKey, string $unit): string
    {
        if ($unit === 'day') {
            return Carbon::parse($periodKey)->format('M d');
        }
        if ($unit === 'week') {
            [$year, $week] = explode('-W', $periodKey);
            return 'W' . $week . ' ' . $year;
        }

        return Carbon::createFromFormat('Y-m', $periodKey)->format('M Y');
    }

    private function getAssignedCrmOwners()
    {
        $leadOwnerIds = Lead::query()
            ->whereNotNull('owner_id')
            ->where('owner_id', '>', 0)
            ->pluck('owner_id');
        $dealOwnerIds = Deal::query()
            ->whereNotNull('owner_id')
            ->where('owner_id', '>', 0)
            ->pluck('owner_id');

        $ownerIds = $leadOwnerIds
            ->merge($dealOwnerIds)
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($ownerIds)) {
            return collect();
        }

        return Admin::query()
            ->active()
            ->whereIn('id', $ownerIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
    
    private function resolveDateRange(Request $request)
{
    $rangeType = $request->input('range_type', null);

    switch ($rangeType) {

        case 'today':
            $startDate = Carbon::today();
            $endDate   = Carbon::today();
            break;

        case 'this_week':
            $startDate = Carbon::now()->startOfWeek();
            $endDate   = Carbon::now()->endOfWeek();
            break;

        case 'this_month':
            $startDate = Carbon::now()->startOfMonth();
            $endDate   = Carbon::now()->endOfMonth();
            break;

        case 'last_month':
            $startDate = Carbon::now()->subMonth()->startOfMonth();
            $endDate   = Carbon::now()->subMonth()->endOfMonth();
            break;

        case 'custom':
            $startDate = Carbon::parse($request->start_date);
            $endDate   = Carbon::parse($request->end_date);
            break;

        default:
            // fallback to manual dateRange picker
            $startDate = $request->start_date
                ? Carbon::parse($request->start_date)
                : Carbon::today()->subDays(6);

            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)
                : Carbon::today();
    }

    return [
        'start' => $startDate->startOfDay(),
        'end'   => $endDate->endOfDay(),
    ];
}

private function baseMessageQuery(Request $request)
{
    $dates = $this->resolveDateRange($request);

    return InboxMessage::whereBetween('created_at', [
            $dates['start'],
            $dates['end']
        ])
        ->when($request->department_id, fn($q) =>
            $q->where('department_id', $request->department_id)
        )
        ->when($request->message_type, fn($q) =>
            $q->where('convert_sub_type', $request->message_type)
        )
        ->when($request->status, fn($q) =>
            $q->where('status', $request->status)
        )
        ->when($request->pipeline, fn($q) =>
            $q->where('pipeline', $request->pipeline)
        );
}


public function exportExcel(Request $request)
{
    $groupBy = $request->input('group_by', 'daily');
    $periodExpr = match ($groupBy) {
        'weekly' => 'DATE_FORMAT(created_at, "%x-W%v")',
        'monthly' => 'DATE_FORMAT(created_at, "%Y-%m")',
        default => 'DATE(created_at)',
    };

    $data = $this->baseMessageQuery($request)
        ->selectRaw("
            {$periodExpr} as period,
            COUNT(*) as total,
            SUM(department_id IS NOT NULL AND department_id != 0) as assigned,
            SUM(department_id IS NULL OR department_id = 0) as pending,
            SUM(status = \"converted\") as converted,
            SUM(status = \"ignored\") as ignored,
            SUM(status = \"spam\") as spam
        ")
        ->groupBy('period')
        ->orderBy('period')
        ->get();

    // Calculate summary statistics
    $summary = [
        'total' => $data->sum('total'),
        'assigned' => $data->sum('assigned'),
        'pending' => $data->sum('pending'),
        'converted' => $data->sum('converted'),
        'ignored' => $data->sum('ignored'),
        'spam' => $data->sum('spam')
    ];

    // Resolve names for filters
    $departmentName = 'All';
    if ($request->department_id) {
        $department = Departments::find($request->department_id);
        $departmentName = $department ? $department->name : 'All';
    }

    // Generate filename with filters
    $dateStr = now()->format('Y-m-d');
    $filename = "crm-analytics-{$dateStr}";
    
    if ($request->department_id) {
        $filename .= "-" . str_replace(' ', '-', $departmentName);
    }
    if ($request->pipeline) {
        $filename .= "-{$request->pipeline}";
    }
    if ($request->status) {
        $filename .= "-{$request->status}";
    }
    
    $filename = $filename . '.xlsx';

    return Excel::download(
        new CRMAnalyticsExport(
            $data,
            [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'department' => $departmentName,
                'pipeline' => $request->pipeline ?? 'All',
                'status' => $request->status ?? 'All',
                'message_type' => $request->message_type ?? 'All',
                'generated_at' => now()->format('d M Y h:i A'),
                'summary' => $summary // 👈 Add this line
            ]
        ),
        $filename
    );
}


public function exportPdf(Request $request)
{
    // ✅ 1. Get language from request
    $language = $request->get('lang', app()->getLocale());

    // ✅ 2. Set locale BEFORE anything else
    app()->setLocale($language);
    \Carbon\Carbon::setLocale($language);

    // DEBUG (optional – remove later)
    // dd(app()->getLocale());

    $startDate = $request->input('start_date');
    $endDate   = $request->input('end_date');
    $departmentId = $request->input('department_id');
    $messageType = $request->input('message_type');
    $status = $request->input('status');
    $pipeline = $request->input('pipeline');

    $query = InboxMessage::query()
        ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);

    if ($departmentId) {
        $query->where('department_id', $departmentId);
    }

    if ($messageType) {
        $query->where('convert_sub_type', $messageType);
    }

    if ($status) {
        $query->where('status', $status);
    }

    if ($pipeline) {
        $query->where('pipeline', $pipeline);
    }

    $dailyData = $query->select(
        DB::raw('DATE(created_at) as date'),
        DB::raw('COUNT(*) as total')
    )
    ->groupBy('date')
    ->orderBy('date')
    ->get();

    return app(ReportPdfService::class)->download(
        view: 'admin-views.crm.export-pdf',
        data: ['data' => $dailyData],
        fileName: 'crm-report.pdf',
        orientation: 'landscape'
    );
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

    private function buildCrmInsights(
        array $kpi,
        array $trendLabels,
        array $dealTrend,
        array $wonDealTrend,
        array $dealStageRows
    ): array {
        if (($kpi['message_count'] ?? 0) === 0 && ($kpi['lead_count'] ?? 0) === 0 && ($kpi['deal_count'] ?? 0) === 0) {
            return [translate('no_crm_activity_found_in_last_90_days')];
        }

        $insights = [];
        $insights[] = strtr(translate('crm_insight_conversion_and_win_rate'), [
            ':lead_to_deal_rate' => number_format((float)$kpi['lead_to_deal_rate'], 1),
            ':deal_win_rate' => number_format((float)$kpi['deal_win_rate'], 1),
        ]);
        $insights[] = strtr(translate('crm_insight_message_conversion'), [
            ':message_conversion_rate' => number_format((float)$kpi['message_conversion_rate'], 1),
            ':new_messages' => number_format((int)$kpi['new_messages']),
        ]);
        $insights[] = strtr(translate('crm_insight_pipeline_value'), [
            ':total_deal_value' => $this->formatMoney((float)$kpi['total_deal_value']),
            ':deal_count' => number_format((int)$kpi['deal_count']),
        ]);

        $maxDeals = max($dealTrend);
        if ($maxDeals > 0) {
            $bestMonthIndex = array_search($maxDeals, $dealTrend, true);
            if ($bestMonthIndex !== false && isset($trendLabels[$bestMonthIndex])) {
                $wonInBestMonth = (int)($wonDealTrend[$bestMonthIndex] ?? 0);
                $insights[] = strtr(translate('crm_insight_peak_deal_month'), [
                    ':period' => $trendLabels[$bestMonthIndex],
                    ':deals' => (string)$maxDeals,
                    ':won' => (string)$wonInBestMonth,
                ]);
            }
        }

        if (!empty($dealStageRows)) {
            $topStage = $dealStageRows[0];
            $stageName = ucwords(str_replace('_', ' ', (string)data_get($topStage, 'stage_name', 'unassigned')));
            $stageCount = (int)data_get($topStage, 'total', 0);
            $insights[] = strtr(translate('crm_insight_top_deal_stage'), [
                ':stage_name' => $stageName,
                ':deals' => (string)$stageCount,
            ]);
        }

        return $insights;
    }

    private function formatMoney(float $value): string
    {
        return number_format($value, 2);
    }

}
