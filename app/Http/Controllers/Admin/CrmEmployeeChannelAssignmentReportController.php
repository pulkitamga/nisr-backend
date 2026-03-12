<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ViewPaths\Admin\CrmEmployeeChannelAssignmentReport;
use App\Exports\CrmEmployeeChannelAssignmentReportExport;
use App\Http\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Departments;
use App\Services\ReportPdfService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class CrmEmployeeChannelAssignmentReportController extends BaseController
{
    private const DEFAULT_CHANNELS = ['phone', 'social', 'chat', 'email', 'form'];

    public function index(?Request $request, string $type = null): View|EloquentCollection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        $request = $request ?? request();
        return view(CrmEmployeeChannelAssignmentReport::VIEW[VIEW], $this->buildReportData($request));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();

        return Excel::download(
            new CrmEmployeeChannelAssignmentReportExport($data),
            'crm-employee-channel-assignment-report.xlsx'
        );
    }

    public function exportPdf(Request $request): Response
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();

        // Get chart image from request (sent via POST/GET from frontend)
        $data['channelChart'] = $request->input('channel_chart');

        return app(ReportPdfService::class)->download(
            view: CrmEmployeeChannelAssignmentReport::EXPORT_PDF[VIEW],
            data: $data,
            fileName: 'crm-employee-channel-assignment-report.pdf',
            orientation: 'landscape'
        );
    }

    private function chartImage($config)
    {
        $url = "https://quickchart.io/chart?width=700&height=350&c=" . urlencode(json_encode($config));

        try {
            $image = file_get_contents($url);
            return 'data:image/png;base64,' . base64_encode($image);
        } catch (\Exception $e) {
            return null;
        }
    }
    private function buildReportData(Request $request): array
    {
        [$fromDate, $toDate, $dateType] = $this->resolveDateRange($request);
        $periodStrategy = $this->resolvePeriodStrategy($fromDate, $toDate);

        $departmentIds = $this->normalizeMultiIds($request->input('department_ids', $request->input('department_id', [])));
        $employeeIds = $this->normalizeMultiIds($request->input('employee_ids', $request->input('employee_id', [])));
        $availableChannels = $this->getAvailableChannels();
        $channels = $this->normalizeChannels(
            $request->input('channels', $request->input('channel', [])),
            $availableChannels
        );

        $assignedDepartmentIds = $this->getAssignedDepartmentIds(
            fromDate: $fromDate,
            toDate: $toDate,
            channels: $channels
        );
        $assignedEmployeeIds = $this->getAssignedEmployeeIds(
            fromDate: $fromDate,
            toDate: $toDate,
            channels: $channels,
            departmentIds: $departmentIds
        );

        $departments = Departments::query()
            ->select('id', 'name')
            ->when(!empty($assignedDepartmentIds), fn($query) => $query->whereIn('id', $assignedDepartmentIds))
            ->when(empty($assignedDepartmentIds), fn($query) => $query->whereRaw('1 = 0'))
            ->orderBy('name')
            ->get();

        $employees = Admin::query()
            ->select('id', 'name', 'department_id')
            ->where('status', 1)
            ->when(!empty($assignedEmployeeIds), fn($query) => $query->whereIn('id', $assignedEmployeeIds))
            ->when(empty($assignedEmployeeIds), fn($query) => $query->whereRaw('1 = 0'))
            ->when(!empty($departmentIds), fn($query) => $query->whereIn('department_id', $departmentIds))
            ->orderBy('name')
            ->get();

        $rows = $this->getRows(
            fromDate: $fromDate,
            toDate: $toDate,
            departmentIds: $departmentIds,
            employeeIds: $employeeIds,
            channels: $channels
        );
        $displayChannels = $this->resolveDisplayChannels($rows, $channels, $availableChannels);
        $counterChannels = collect($availableChannels)->values()->all();
        $channelLabels = collect($counterChannels)
            ->mapWithKeys(fn(string $channel) => [$channel => $this->getChannelLabel($channel)])
            ->all();
        $counterTotals = $this->buildCounterTotals($rows, $counterChannels);

        $employeesForMatrix = $this->resolveEmployeeListForMatrix($rows, $employees, $employeeIds);
        $monthlyRows = $this->buildPeriodMatrix(
            rows: $rows,
            fromDate: $fromDate,
            toDate: $toDate,
            employeesForMatrix: $employeesForMatrix,
            periodStrategy: $periodStrategy,
            displayChannels: $displayChannels
        );
        $summary = $this->buildSummary($monthlyRows, $employeesForMatrix, $displayChannels);

        return [
            'departments' => $departments,
            'employees' => $employees,
            'employeesForMatrix' => $employeesForMatrix,
            'displayChannels' => $displayChannels,
            'counterChannels' => $counterChannels,
            'channelLabels' => $channelLabels,
            'counterTotals' => $counterTotals,
            'channelOptions' => collect($availableChannels)->map(fn(string $channel) => (object)[
                'value' => $channel,
                'label' => $this->getChannelLabel($channel),
            ])->values(),
            'filters' => [
                'date_type' => $dateType,
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
                'department_ids' => $departmentIds,
                'employee_ids' => $employeeIds,
                'channels' => $channels,
                'period_type' => $periodStrategy['type'],
            ],
            'monthlyRows' => $monthlyRows,
            'summary' => $summary,
            'chart' => [
                'labels' => $monthlyRows->pluck('month_label')->all(),
                'series' => collect($displayChannels)->map(function (string $channel) use ($monthlyRows, $channelLabels) {
                    return [
                        'name' => (string)($channelLabels[$channel] ?? $channel),
                        'data' => $monthlyRows->map(fn($row) => (int)($row->totals['channels'][$channel] ?? 0))->all(),
                    ];
                })->values()->all(),
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

    private function getAvailableChannels(): array
    {
        $inboxChannels = DB::table('inbox_messages')
            ->whereNotNull('pipeline')
            ->whereRaw("TRIM(pipeline) <> ''")
            ->distinct()
            ->pluck('pipeline')
            ->filter()
            ->map(fn($value) => strtolower(trim((string)$value)))
            ->values();

        $leadChannels = DB::table('leads')
            ->whereNotNull('utm_source')
            ->whereRaw("TRIM(utm_source) <> ''")
            ->distinct()
            ->pluck('utm_source')
            ->filter()
            ->map(fn($value) => strtolower(trim((string)$value)))
            ->values();

        $ticketChannels = DB::table('support_tickets')
            ->selectRaw($this->ticketChannelSql() . ' as channel')
            ->whereRaw($this->ticketChannelSql() . " <> ''")
            ->distinct()
            ->pluck('channel')
            ->filter()
            ->map(fn($value) => strtolower(trim((string)$value)))
            ->values();

        return collect(self::DEFAULT_CHANNELS)
            ->merge($inboxChannels)
            ->merge($leadChannels)
            ->merge($ticketChannels)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function getRows(
        Carbon $fromDate,
        Carbon $toDate,
        array $departmentIds = [],
        array $employeeIds = [],
        array $channels = []
    ): Collection {
        $rows = collect()
            ->merge($this->getInboxRows($fromDate, $toDate, $departmentIds, $employeeIds, $channels))
            ->merge($this->getLeadRows($fromDate, $toDate, $departmentIds, $employeeIds, $channels))
            ->merge($this->getDealRows($fromDate, $toDate, $departmentIds, $employeeIds, $channels))
            ->merge($this->getTicketRows($fromDate, $toDate, $departmentIds, $employeeIds, $channels));

        return $rows
            ->groupBy(fn($row) => $row->report_date . '|' . $row->employee_id . '|' . $row->channel)
            ->map(function (Collection $group) {
                return (object)[
                    'report_date' => (string)$group->first()->report_date,
                    'employee_id' => (int)$group->first()->employee_id,
                    'employee_name' => (string)$group->first()->employee_name,
                    'channel' => (string)$group->first()->channel,
                    'total_count' => (int)$group->sum('total_count'),
                ];
            })
            ->values()
            ->sortBy(fn($row) => $row->report_date . '|' . strtolower((string)$row->employee_name) . '|' . $row->channel)
            ->map(function ($row) {
                $row->report_date = (string)$row->report_date;
                $row->employee_id = (int)$row->employee_id;
                $row->employee_name = (string)($row->employee_name ?: translate('unassigned'));
                $row->channel = strtolower(trim((string)$row->channel));
                $row->total_count = (int)$row->total_count;
                return $row;
            });
    }

    private function getInboxRows(
        Carbon $fromDate,
        Carbon $toDate,
        array $departmentIds = [],
        array $employeeIds = [],
        array $channels = []
    ): Collection {
        $channelSql = $this->inboxChannelSql();

        $rows = DB::table('inbox_messages')
            ->leftJoin('admins', 'admins.id', '=', 'inbox_messages.employee_id')
            ->whereBetween('inbox_messages.created_at', [$fromDate, $toDate])
            ->whereNotNull('inbox_messages.employee_id')
            ->where('inbox_messages.employee_id', '>', 0)
            ->when(!empty($departmentIds), fn($query) => $query->whereIn('inbox_messages.department_id', $departmentIds))
            ->when(!empty($employeeIds), fn($query) => $query->whereIn('inbox_messages.employee_id', $employeeIds))
            ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($channelSql), $channels))
            ->select([
                DB::raw('DATE(inbox_messages.created_at) as report_date'),
                DB::raw('inbox_messages.employee_id as employee_id'),
                DB::raw("MAX(COALESCE(admins.name, '')) as employee_name"),
                DB::raw($channelSql . ' as channel'),
                DB::raw('COUNT(*) as total_count'),
            ])
            ->groupBy(DB::raw('DATE(inbox_messages.created_at)'), 'inbox_messages.employee_id', DB::raw($channelSql))
            ->get();

        return collect($rows);
    }

    private function getLeadRows(
        Carbon $fromDate,
        Carbon $toDate,
        array $departmentIds = [],
        array $employeeIds = [],
        array $channels = []
    ): Collection {
        $channelSql = $this->leadChannelSql('leads');

        $rows = DB::table('leads')
            ->leftJoin('admins', 'admins.id', '=', 'leads.employee_id')
            ->whereBetween('leads.created_at', [$fromDate, $toDate])
            ->whereNotNull('leads.employee_id')
            ->where('leads.employee_id', '>', 0)
            ->when(!empty($departmentIds), fn($query) => $query->whereIn('leads.department_id', $departmentIds))
            ->when(!empty($employeeIds), fn($query) => $query->whereIn('leads.employee_id', $employeeIds))
            ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($channelSql), $channels))
            ->select([
                DB::raw('DATE(leads.created_at) as report_date'),
                DB::raw('leads.employee_id as employee_id'),
                DB::raw("MAX(COALESCE(admins.name, '')) as employee_name"),
                DB::raw($channelSql . ' as channel'),
                DB::raw('COUNT(*) as total_count'),
            ])
            ->groupBy(DB::raw('DATE(leads.created_at)'), 'leads.employee_id', DB::raw($channelSql))
            ->get();

        return collect($rows);
    }

    private function getDealRows(
        Carbon $fromDate,
        Carbon $toDate,
        array $departmentIds = [],
        array $employeeIds = [],
        array $channels = []
    ): Collection {
        $channelSql = $this->dealChannelSql();

        $rows = DB::table('deals')
            ->leftJoin('admins', 'admins.id', '=', 'deals.employee_id')
            ->whereBetween('deals.created_at', [$fromDate, $toDate])
            ->whereNotNull('deals.employee_id')
            ->where('deals.employee_id', '>', 0)
            ->when(!empty($departmentIds), fn($query) => $query->whereIn('deals.department_id', $departmentIds))
            ->when(!empty($employeeIds), fn($query) => $query->whereIn('deals.employee_id', $employeeIds))
            ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($channelSql), $channels))
            ->select([
                DB::raw('DATE(deals.created_at) as report_date'),
                DB::raw('deals.employee_id as employee_id'),
                DB::raw("MAX(COALESCE(admins.name, '')) as employee_name"),
                DB::raw($channelSql . ' as channel'),
                DB::raw('COUNT(*) as total_count'),
            ])
            ->groupBy(DB::raw('DATE(deals.created_at)'), 'deals.employee_id', DB::raw($channelSql))
            ->get();

        return collect($rows);
    }

    private function getTicketRows(
        Carbon $fromDate,
        Carbon $toDate,
        array $departmentIds = [],
        array $employeeIds = [],
        array $channels = []
    ): Collection {
        $channelSql = $this->ticketChannelSql();

        $rows = DB::table('support_tickets')
            ->leftJoin('admins', 'admins.id', '=', 'support_tickets.employee_id')
            ->whereBetween('support_tickets.created_at', [$fromDate, $toDate])
            ->whereNotNull('support_tickets.employee_id')
            ->where('support_tickets.employee_id', '>', 0)
            ->when(!empty($departmentIds), fn($query) => $query->whereIn('support_tickets.department_id', $departmentIds))
            ->when(!empty($employeeIds), fn($query) => $query->whereIn('support_tickets.employee_id', $employeeIds))
            ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($channelSql), $channels))
            ->select([
                DB::raw('DATE(support_tickets.created_at) as report_date'),
                DB::raw('support_tickets.employee_id as employee_id'),
                DB::raw("MAX(COALESCE(admins.name, '')) as employee_name"),
                DB::raw($channelSql . ' as channel'),
                DB::raw('COUNT(*) as total_count'),
            ])
            ->groupBy(DB::raw('DATE(support_tickets.created_at)'), 'support_tickets.employee_id', DB::raw($channelSql))
            ->get();

        return collect($rows);
    }

    private function getAssignedDepartmentIds(Carbon $fromDate, Carbon $toDate, array $channels = []): array
    {
        $ids = collect();

        $ids = $ids
            ->merge(
                DB::table('inbox_messages')
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->whereNotNull('employee_id')
                    ->where('employee_id', '>', 0)
                    ->whereNotNull('department_id')
                    ->where('department_id', '>', 0)
                    ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($this->inboxChannelSql()), $channels))
                    ->pluck('department_id')
            )
            ->merge(
                DB::table('leads')
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->whereNotNull('employee_id')
                    ->where('employee_id', '>', 0)
                    ->whereNotNull('department_id')
                    ->where('department_id', '>', 0)
                    ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($this->leadChannelSql('leads')), $channels))
                    ->pluck('department_id')
            )
            ->merge(
                DB::table('deals')
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->whereNotNull('employee_id')
                    ->where('employee_id', '>', 0)
                    ->whereNotNull('department_id')
                    ->where('department_id', '>', 0)
                    ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($this->dealChannelSql()), $channels))
                    ->pluck('department_id')
            )
            ->merge(
                DB::table('support_tickets')
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->whereNotNull('employee_id')
                    ->where('employee_id', '>', 0)
                    ->whereNotNull('department_id')
                    ->where('department_id', '>', 0)
                    ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($this->ticketChannelSql()), $channels))
                    ->pluck('department_id')
            );

        return $ids
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function getAssignedEmployeeIds(
        Carbon $fromDate,
        Carbon $toDate,
        array $channels = [],
        array $departmentIds = []
    ): array {
        $ids = collect();

        $ids = $ids
            ->merge(
                DB::table('inbox_messages')
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->whereNotNull('employee_id')
                    ->where('employee_id', '>', 0)
                    ->when(!empty($departmentIds), fn($query) => $query->whereIn('department_id', $departmentIds))
                    ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($this->inboxChannelSql()), $channels))
                    ->pluck('employee_id')
            )
            ->merge(
                DB::table('leads')
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->whereNotNull('employee_id')
                    ->where('employee_id', '>', 0)
                    ->when(!empty($departmentIds), fn($query) => $query->whereIn('department_id', $departmentIds))
                    ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($this->leadChannelSql('leads')), $channels))
                    ->pluck('employee_id')
            )
            ->merge(
                DB::table('deals')
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->whereNotNull('employee_id')
                    ->where('employee_id', '>', 0)
                    ->when(!empty($departmentIds), fn($query) => $query->whereIn('department_id', $departmentIds))
                    ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($this->dealChannelSql()), $channels))
                    ->pluck('employee_id')
            )
            ->merge(
                DB::table('support_tickets')
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->whereNotNull('employee_id')
                    ->where('employee_id', '>', 0)
                    ->when(!empty($departmentIds), fn($query) => $query->whereIn('department_id', $departmentIds))
                    ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw($this->ticketChannelSql()), $channels))
                    ->pluck('employee_id')
            );

        return $ids
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function inboxChannelSql(): string
    {
        return "LOWER(TRIM(COALESCE(inbox_messages.pipeline, '')))";
    }

    private function leadChannelSql(string $leadTable = 'leads'): string
    {
        return "LOWER(TRIM(COALESCE(NULLIF({$leadTable}.utm_source, ''), (SELECT im.pipeline FROM inbox_messages im WHERE im.related_lead_id = {$leadTable}.id AND im.pipeline IS NOT NULL AND TRIM(im.pipeline) <> '' ORDER BY im.id DESC LIMIT 1), '')))";
    }

    private function dealChannelSql(): string
    {
        return "LOWER(TRIM(COALESCE((SELECT l.utm_source FROM leads l WHERE l.id = deals.lead_id LIMIT 1), (SELECT im.pipeline FROM inbox_messages im WHERE im.related_lead_id = deals.lead_id AND im.pipeline IS NOT NULL AND TRIM(im.pipeline) <> '' ORDER BY im.id DESC LIMIT 1), '')))";
    }

    private function ticketChannelSql(): string
    {
        return "LOWER(TRIM(COALESCE((SELECT im.pipeline FROM inbox_messages im WHERE im.related_ticket_id = support_tickets.id AND im.pipeline IS NOT NULL AND TRIM(im.pipeline) <> '' ORDER BY im.id DESC LIMIT 1), 'form')))";
    }

    private function resolveEmployeeListForMatrix(
        Collection $rows,
        Collection $allEmployees,
        array $employeeIds
    ): Collection {
        if (!empty($employeeIds)) {
            return $allEmployees
                ->whereIn('id', $employeeIds)
                ->map(fn($employee) => (object)[
                    'id' => (int)$employee->id,
                    'name' => (string)$employee->name,
                ])
                ->sortBy('name')
                ->values();
        }

        return $rows
            ->groupBy('employee_id')
            ->map(fn(Collection $group) => (object)[
                'id' => (int)$group->first()->employee_id,
                'name' => (string)$group->first()->employee_name,
            ])
            ->sortBy('name')
            ->values();
    }

    private function buildPeriodMatrix(
        Collection $rows,
        Carbon $fromDate,
        Carbon $toDate,
        Collection $employeesForMatrix,
        array $periodStrategy,
        array $displayChannels
    ): Collection {
        $rowsByPeriod = $rows
            ->groupBy(fn($row) => $this->resolvePeriodKey(Carbon::parse((string)$row->report_date), $periodStrategy))
            ->map(function (Collection $periodRows) {
                return $periodRows
                    ->groupBy('employee_id')
                    ->map(function (Collection $employeeRows) {
                        return $employeeRows
                            ->groupBy('channel')
                            ->map(fn(Collection $channelRows) => (int)$channelRows->sum('total_count'));
                    });
            });

        $months = [];
        $periodSequence = $this->buildPeriodSequence($fromDate, $toDate, $periodStrategy);
        foreach ($periodSequence as $periodEntry) {
            $monthKey = (string)$periodEntry['key'];
            $monthLabel = (string)$periodEntry['label'];

            $monthEmployeeRows = [];
            $monthTotals = [
                'channels' => collect($displayChannels)->mapWithKeys(fn(string $channel) => [$channel => 0])->all(),
                'total_count' => 0,
            ];

            foreach ($employeesForMatrix as $employee) {
                $sourceChannels = $rowsByPeriod->get($monthKey)?->get((int)$employee->id) ?? collect();
                $channelCounts = collect($displayChannels)->mapWithKeys(
                    fn(string $channel) => [$channel => (int)($sourceChannels[$channel] ?? 0)]
                )->all();
                $totalCount = (int)collect($channelCounts)->sum();

                $monthEmployeeRows[(int)$employee->id] = [
                    'channels' => $channelCounts,
                    'total_count' => $totalCount,
                ];

                foreach ($displayChannels as $channel) {
                    $monthTotals['channels'][$channel] += (int)$channelCounts[$channel];
                }
                $monthTotals['total_count'] += $totalCount;
            }

            $months[] = (object)[
                'month_key' => $monthKey,
                'month_label' => $monthLabel,
                'employees' => $monthEmployeeRows,
                'totals' => $monthTotals,
            ];
        }

        return collect($months);
    }

    private function resolvePeriodStrategy(Carbon $fromDate, Carbon $toDate): array
    {
        $daysDifference = $fromDate->diffInDays($toDate);

        if ($daysDifference > 60) {
            return ['type' => 'month'];
        }

        if ($daysDifference <= 7) {
            return ['type' => 'weekday'];
        }

        if ($daysDifference <= 31) {
            return ['type' => 'day'];
        }

        return ['type' => 'date'];
    }

    private function buildPeriodSequence(Carbon $fromDate, Carbon $toDate, array $periodStrategy): array
    {
        $periodType = (string)($periodStrategy['type'] ?? 'date');
        $sequence = [];

        if ($periodType === 'month') {
            $period = CarbonPeriod::create($fromDate->copy()->startOfMonth(), '1 month', $toDate->copy()->startOfMonth());
            foreach ($period as $date) {
                $sequence[] = [
                    'key' => $date->format('Y-m'),
                    'label' => $date->locale(app()->getLocale())->translatedFormat('M'),
                ];
            }

            return $sequence;
        }

        $period = CarbonPeriod::create($fromDate->copy()->startOfDay(), $toDate->copy()->startOfDay());
        foreach ($period as $date) {
            $sequence[] = [
                'key' => $date->format('Y-m-d'),
                'label' => $this->resolvePeriodLabel($date, $periodType),
            ];
        }

        return $sequence;
    }

    private function resolvePeriodKey(Carbon $date, array $periodStrategy): string
    {
        return (string)match ((string)($periodStrategy['type'] ?? 'date')) {
            'month' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    private function resolvePeriodLabel(Carbon $date, string $periodType): string
    {
        return match ($periodType) {
            'weekday' => $date->locale(app()->getLocale())->translatedFormat('l'),
            'day' => $date->format('j'),
            default => $date->locale(app()->getLocale())->translatedFormat('j M'),
        };
    }

    private function buildSummary(Collection $monthlyRows, Collection $employeesForMatrix, array $displayChannels): array
    {
        $grand = [
            'channels' => collect($displayChannels)->mapWithKeys(fn(string $channel) => [$channel => 0])->all(),
            'total_count' => 0,
        ];

        foreach ($monthlyRows as $monthRow) {
            foreach ($displayChannels as $channel) {
                $grand['channels'][$channel] += (int)($monthRow->totals['channels'][$channel] ?? 0);
            }
            $grand['total_count'] += $monthRow->totals['total_count'];
        }

        $perEmployee = [];
        foreach ($employeesForMatrix as $employee) {
            $channelCounts = collect($displayChannels)->mapWithKeys(fn(string $channel) => [$channel => 0])->all();
            $totalCount = 0;

            foreach ($monthlyRows as $monthRow) {
                $cell = $monthRow->employees[(int)$employee->id] ?? null;
                foreach ($displayChannels as $channel) {
                    $channelCounts[$channel] += (int)($cell['channels'][$channel] ?? 0);
                }
                $totalCount += (int)($cell['total_count'] ?? 0);
            }

            $perEmployee[] = (object)[
                'employee_id' => (int)$employee->id,
                'employee_name' => (string)$employee->name,
                'channels' => $channelCounts,
                'total_count' => $totalCount,
            ];
        }

        return [
            'grand' => $grand,
            'active_employees' => collect($perEmployee)->where('total_count', '>', 0)->count(),
            'per_employee' => collect($perEmployee)->sortByDesc('total_count')->values(),
        ];
    }

    private function resolveDisplayChannels(Collection $rows, array $selectedChannels, array $availableChannels): array
    {
        $channelsInRows = $rows
            ->pluck('channel')
            ->map(fn($value) => strtolower(trim((string)$value)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($selectedChannels)) {
            return collect($selectedChannels)
                ->filter(fn(string $channel) => in_array($channel, $channelsInRows, true))
                ->values()
                ->all();
        }

        return collect($availableChannels)
            ->filter(fn(string $channel) => in_array($channel, $channelsInRows, true))
            ->values()
            ->all();
    }

    private function buildCounterTotals(Collection $rows, array $counterChannels): array
    {
        $totals = collect($counterChannels)
            ->mapWithKeys(fn(string $channel) => [$channel => 0])
            ->all();

        foreach ($rows as $row) {
            $channel = strtolower(trim((string)($row->channel ?? '')));
            if ($channel !== '' && array_key_exists($channel, $totals)) {
                $totals[$channel] += (int)($row->total_count ?? 0);
            }
        }

        return $totals;
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

    private function normalizeChannels(mixed $input, array $availableChannels): array
    {
        if ($input === null || $input === '' || $input === 'all') {
            return [];
        }

        if (!is_array($input)) {
            $input = is_string($input) ? explode(',', $input) : [$input];
        }

        return collect($input)
            ->map(fn($value) => strtolower(trim((string)$value)))
            ->filter()
            ->when(!empty($availableChannels), fn(Collection $collection) => $collection->filter(
                fn(string $value) => in_array($value, $availableChannels, true)
            ))
            ->unique()
            ->values()
            ->all();
    }

    private function getChannelLabel(string $channel): string
    {
        return match ($channel) {
            'phone' => translate('channel_phone'),
            'social' => translate('channel_social'),
            'chat' => translate('channel_chat'),
            'email' => translate('channel_email'),
            'form' => translate('channel_form'),
            default => ucwords(str_replace(['-', '_'], ' ', $channel)),
        };
    }
}
