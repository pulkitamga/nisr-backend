<?php

namespace App\Http\Controllers\Admin\Crm;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Departments;
use App\Models\InboxMessage;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Exports\CRMAnalyticsExport;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;


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
            ->where('admins.admin_role_id', '!=', 1)
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

        $employeeCount = Admin::where('admin_role_id', '!=', 1)->count();
        $activeEmployees = Admin::where('admin_role_id', '!=', 1)
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

    $pdf = Pdf::loadView('admin-views.crm.export-pdf', [
        'data' => $dailyData
    ])
    ->setPaper('a4', 'landscape')
    ->setOptions([
        'defaultFont' => 'DejaVu Sans',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true
    ]);

    return $pdf->download('crm-report.pdf');
}


}
