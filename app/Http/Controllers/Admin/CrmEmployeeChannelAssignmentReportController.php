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
    private const WHOLESALE_SUB_TYPES = ['wholesale', 'company', 'trader', 'dealer'];

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

        return app(ReportPdfService::class)->download(
            view: CrmEmployeeChannelAssignmentReport::EXPORT_PDF[VIEW],
            data: $data,
            fileName: 'crm-employee-channel-assignment-report.pdf',
            orientation: 'landscape'
        );
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

        $departments = Departments::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $employees = Admin::query()
            ->select('id', 'name', 'department_id')
            ->where('status', 1)
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

        $employeesForMatrix = $this->resolveEmployeeListForMatrix($rows, $employees, $employeeIds);
        $monthlyRows = $this->buildPeriodMatrix(
            rows: $rows,
            fromDate: $fromDate,
            toDate: $toDate,
            employeesForMatrix: $employeesForMatrix,
            periodStrategy: $periodStrategy
        );
        $summary = $this->buildSummary($monthlyRows, $employeesForMatrix);

        return [
            'departments' => $departments,
            'employees' => $employees,
            'employeesForMatrix' => $employeesForMatrix,
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
                'totals' => $monthlyRows->map(fn($row) => (int)$row->totals['total_count'])->all(),
                'retail' => $monthlyRows->map(fn($row) => (int)$row->totals['retail_count'])->all(),
                'wholesale' => $monthlyRows->map(fn($row) => (int)$row->totals['wholesale_count'])->all(),
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
        $storedChannels = DB::table('inbox_messages')
            ->whereNotNull('pipeline')
            ->whereRaw("TRIM(pipeline) <> ''")
            ->distinct()
            ->pluck('pipeline')
            ->filter()
            ->map(fn($value) => strtolower(trim((string)$value)))
            ->values();

        return collect(self::DEFAULT_CHANNELS)
            ->merge($storedChannels)
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
        $wholesaleCondition = $this->wholesaleConditionSql();

        $rows = DB::table('inbox_messages')
            ->leftJoin('admins', 'admins.id', '=', 'inbox_messages.employee_id')
            ->leftJoin('users', 'users.id', '=', 'inbox_messages.contact_id')
            ->whereBetween('inbox_messages.created_at', [$fromDate, $toDate])
            ->whereNotNull('inbox_messages.employee_id')
            ->where('inbox_messages.employee_id', '>', 0)
            ->when(!empty($departmentIds), fn($query) => $query->whereIn('inbox_messages.department_id', $departmentIds))
            ->when(!empty($employeeIds), fn($query) => $query->whereIn('inbox_messages.employee_id', $employeeIds))
            ->when(!empty($channels), fn($query) => $query->whereIn(DB::raw("LOWER(COALESCE(inbox_messages.pipeline, ''))"), $channels))
            ->select([
                DB::raw('DATE(inbox_messages.created_at) as report_date'),
                DB::raw('COALESCE(inbox_messages.employee_id, 0) as employee_id'),
                DB::raw("MAX(COALESCE(admins.name, '')) as employee_name"),
                DB::raw("SUM(CASE WHEN {$wholesaleCondition} THEN 0 ELSE 1 END) as retail_count"),
                DB::raw("SUM(CASE WHEN {$wholesaleCondition} THEN 1 ELSE 0 END) as wholesale_count"),
                DB::raw('COUNT(*) as total_count'),
            ])
            ->groupBy(DB::raw('DATE(inbox_messages.created_at)'), DB::raw('COALESCE(inbox_messages.employee_id, 0)'))
            ->orderBy('report_date')
            ->orderBy('employee_name')
            ->get();

        return collect($rows)->map(function ($row) {
            $row->report_date = (string)$row->report_date;
            $row->employee_id = (int)$row->employee_id;
            $row->employee_name = (string)($row->employee_name ?: translate('unassigned'));
            $row->retail_count = (int)$row->retail_count;
            $row->wholesale_count = (int)$row->wholesale_count;
            $row->total_count = (int)$row->total_count;
            return $row;
        });
    }

    private function wholesaleConditionSql(): string
    {
        $subTypeList = implode("','", self::WHOLESALE_SUB_TYPES);

        return "LOWER(COALESCE(inbox_messages.convert_sub_type, '')) IN ('{$subTypeList}')"
            . " OR COALESCE(users.user_type, 0) = 1"
            . " OR COALESCE(users.wholesaler_status, 0) = 1"
            . " OR LOWER(COALESCE(users.wholesaler_status, '')) = 'approved'";
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
        array $periodStrategy
    ): Collection {
        $rowsByPeriod = $rows
            ->groupBy(fn($row) => $this->resolvePeriodKey(Carbon::parse((string)$row->report_date), $periodStrategy))
            ->map(function (Collection $periodRows) {
                return $periodRows
                    ->groupBy('employee_id')
                    ->map(function (Collection $employeeRows) {
                        return (object)[
                            'retail_count' => (int)$employeeRows->sum('retail_count'),
                            'wholesale_count' => (int)$employeeRows->sum('wholesale_count'),
                            'total_count' => (int)$employeeRows->sum('total_count'),
                        ];
                    });
            });

        $months = [];
        $periodSequence = $this->buildPeriodSequence($fromDate, $toDate, $periodStrategy);
        foreach ($periodSequence as $periodEntry) {
            $monthKey = (string)$periodEntry['key'];
            $monthLabel = (string)$periodEntry['label'];

            $monthEmployeeRows = [];
            $monthTotals = [
                'retail_count' => 0,
                'wholesale_count' => 0,
                'total_count' => 0,
            ];

            foreach ($employeesForMatrix as $employee) {
                $source = $rowsByPeriod->get($monthKey)?->get((int)$employee->id);
                $retailCount = (int)($source->retail_count ?? 0);
                $wholesaleCount = (int)($source->wholesale_count ?? 0);
                $totalCount = (int)($source->total_count ?? 0);

                $monthEmployeeRows[(int)$employee->id] = [
                    'retail_count' => $retailCount,
                    'wholesale_count' => $wholesaleCount,
                    'total_count' => $totalCount,
                ];

                $monthTotals['retail_count'] += $retailCount;
                $monthTotals['wholesale_count'] += $wholesaleCount;
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

    private function buildSummary(Collection $monthlyRows, Collection $employeesForMatrix): array
    {
        $grand = [
            'retail_count' => 0,
            'wholesale_count' => 0,
            'total_count' => 0,
        ];

        foreach ($monthlyRows as $monthRow) {
            $grand['retail_count'] += $monthRow->totals['retail_count'];
            $grand['wholesale_count'] += $monthRow->totals['wholesale_count'];
            $grand['total_count'] += $monthRow->totals['total_count'];
        }

        $perEmployee = [];
        foreach ($employeesForMatrix as $employee) {
            $retailCount = 0;
            $wholesaleCount = 0;
            $totalCount = 0;

            foreach ($monthlyRows as $monthRow) {
                $cell = $monthRow->employees[(int)$employee->id] ?? null;
                $retailCount += (int)($cell['retail_count'] ?? 0);
                $wholesaleCount += (int)($cell['wholesale_count'] ?? 0);
                $totalCount += (int)($cell['total_count'] ?? 0);
            }

            $perEmployee[] = (object)[
                'employee_id' => (int)$employee->id,
                'employee_name' => (string)$employee->name,
                'retail_count' => $retailCount,
                'wholesale_count' => $wholesaleCount,
                'total_count' => $totalCount,
            ];
        }

        return [
            'grand' => $grand,
            'active_employees' => collect($perEmployee)->where('total_count', '>', 0)->count(),
            'per_employee' => collect($perEmployee)->sortByDesc('total_count')->values(),
        ];
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
