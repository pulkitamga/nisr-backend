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
            ->where('admins.role_id', '!=', 1)
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
            $startDate = $request->input('start_date', Carbon::today()->subDay(6)->toDateString());
            $endDate = $request->input('end_date', Carbon::today()->toDateString());
            $departmentId = $request->input('department_id');
            $messageType = $request->input('message_type');
            $status = $request->input('status');
           $pipeline = $request->input('pipeline');
            // Base query
            $query = InboxMessage::query()
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            // Apply filters
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

            // Get daily statistics
            $dailyStats = $query->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
              DB::raw('SUM(CASE WHEN department_id IS NOT NULL AND department_id != 0 THEN 1 ELSE 0 END) as assigned'),
              DB::raw('SUM(CASE WHEN department_id IS NULL OR department_id = 0 THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as converted'),
                DB::raw('SUM(CASE WHEN status = "ignored" THEN 1 ELSE 0 END) as ignored'),
                DB::raw('SUM(CASE WHEN status = "spam" THEN 1 ELSE 0 END) as spam')
            )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Fill missing dates with zeros
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $allDates = collect();

            for ($date = $start; $date->lte($end); $date->addDay()) {
                $dateStr = $date->toDateString();
                $stat = $dailyStats->firstWhere('date', $dateStr);

                $allDates->push([
                    'date' => $dateStr,
                    'total' => $stat->total ?? 0,
                    'assigned' => $stat->assigned ?? 0,
                    'pending' => $stat->pending ?? 0,
                    'converted' => $stat->converted ?? 0,
                    'ignored' => $stat->ignored ?? 0,
                    'spam' => $stat->spam ?? 0
                ]);
            }

            // Prepare chart labels (dates)
            $labels = $allDates->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->format('M d');
            })->toArray();

            $datasets = [
                [
                    'key' => 'total',
                    'label' => 'Total Messages',
                    'data' => $allDates->pluck('total')->toArray(),
                    'backgroundColor' => '#3498db'
                ],
                [
                    'key' => 'assigned',
                    'label' => 'Assigned',
                    'data' => $allDates->pluck('assigned')->toArray(),
                    'backgroundColor' => '#2ecc71'
                ],
                [
                    'key' => 'pending',
                    'label' => 'Pending',
                    'data' => $allDates->pluck('pending')->toArray(),
                    'backgroundColor' => '#f39c12'
                ],
                [
                    'key' => 'converted',
                    'label' => 'Converted',
                    'data' => $allDates->pluck('converted')->toArray(),
                    'backgroundColor' => '#9b59b6'
                ],
                [
                    'key' => 'ignored',
                    'label' => 'Ignored',
                    'data' => $allDates->pluck('ignored')->toArray(),
                    'backgroundColor' => '#e74c3c'
                ],
                [
                    'key' => 'spam',
                    'label' => 'Spam',
                    'data' => $allDates->pluck('spam')->toArray(),
                    'backgroundColor' => '#B2BEB5'
                ]
            ];

            // Summary statistics
            $summary = [
                'total' => $allDates->sum('total'),
                'assigned' => $allDates->sum('assigned'),
                'pending' => $allDates->sum('pending'),
                'converted' => $allDates->sum('converted'),
                'ignored' => $allDates->sum('ignored'),
                'spam' => $allDates->sum('spam')
            ];

            // Legend data
            $legend = [
                ['key' => 'total', 'label' => 'Total Messages', 'color' => '#3498db'],
                ['key' => 'assigned', 'label' => 'Assigned', 'color' => '#2ecc71'],
                ['key' => 'pending', 'label' => 'Pending', 'color' => '#f39c12'],
                ['key' => 'converted', 'label' => 'Converted', 'color' => '#9b59b6'],
                ['key' => 'ignored', 'label' => 'Ignored', 'color' => '#e74c3c'],
                ['key' => 'spam', 'label' => 'Spam', 'color' => '#95a5a6']

            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'datasets' => $datasets,
                    'summary' => $summary,
                    'daily_stats' => $allDates,
                    'legend' => $legend
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

        $employeeCount = Admin::where('role_id', '!=', 1)->count();
        $activeEmployees = Admin::where('role_id', '!=', 1)
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
    
    private function baseMessageQuery(Request $request)
{
    $startDate = $request->start_date;
    $endDate   = $request->end_date;

    return InboxMessage::whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
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
    $data = $this->baseMessageQuery($request)
        ->selectRaw('
            DATE(created_at) as date,
            COUNT(*) as total,
            SUM(department_id IS NOT NULL AND department_id != 0) as assigned,
            SUM(department_id IS NULL OR department_id = 0) as pending,
            SUM(status = "converted") as converted,
            SUM(status = "ignored") as ignored,
            SUM(status = "spam") as spam
        ')
        ->groupBy('date')
        ->orderBy('date')
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
    // Get daily aggregated data
    $dailyData = $this->baseMessageQuery($request)
        ->selectRaw('
            DATE(created_at) as date,
            COUNT(*) as total,
            SUM(department_id IS NOT NULL AND department_id != 0) as assigned,
            SUM(department_id IS NULL OR department_id = 0) as pending,
            SUM(status = "converted") as converted,
            SUM(status = "ignored") as ignored,
            SUM(status = "spam") as spam
        ')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    // Get detailed messages data (optional)
    $detailedData = $this->baseMessageQuery($request)
        ->with('department')
        ->orderBy('created_at', 'desc')
        ->limit(100) // Limit to 100 records for PDF
        ->get();

    // Calculate summary statistics
    $summary = [
        'total' => $dailyData->sum('total'),
        'assigned' => $dailyData->sum('assigned'),
        'pending' => $dailyData->sum('pending'),
        'converted' => $dailyData->sum('converted'),
        'ignored' => $dailyData->sum('ignored'),
        'spam' => $dailyData->sum('spam')
    ];

    // Resolve department name
    $departmentName = 'All';
    if ($request->department_id) {
        $department = Departments::find($request->department_id);
        $departmentName = $department ? $department->name : 'All';
    }

    $filters = [
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'department' => $departmentName,
        'pipeline' => $request->pipeline ?? 'All',
        'status' => $request->status ?? 'All',
        'message_type' => $request->message_type ?? 'All',
        'generated_at' => now()->format('d M Y h:i A'),
        'summary' => $summary
    ];

    $pdf = Pdf::loadView('admin-views.crm.export-pdf', [
        'data' => $dailyData,
        'detailed_data' => $detailedData, // Optional
        'filters' => $filters
    ])
    ->setPaper('a4', 'landscape')
    ->setOptions([
        'defaultFont' => 'DejaVu Sans',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true
    ]);

    // Generate filename with filters
    $filename = "crm-analytics-{$request->start_date}-to-{$request->end_date}";
    if ($request->department_id) {
        $filename .= "-{$departmentName}";
    }
    $filename = str_replace(' ', '-', $filename) . '.pdf';

    return $pdf->download($filename);
}

}

