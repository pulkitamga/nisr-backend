<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ViewPaths\Admin\CrmAgentSalesMatrixReport;
use App\Exports\CrmAgentSalesMatrixReportExport;
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

class CrmAgentSalesMatrixReportController extends BaseController
{
    private const RETAIL_PARTY_TYPE = 'contact';
    private const WHOLESALE_PARTY_TYPE = 'company';

    public function index(?Request $request, string $type = null): View|EloquentCollection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        $request = $request ?? request();
        return view(CrmAgentSalesMatrixReport::VIEW[VIEW], $this->buildReportData($request));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();

        return Excel::download(
            new CrmAgentSalesMatrixReportExport($data),
            'crm-agent-sales-matrix-report.xlsx'
        );
    }

    public function exportPdf(Request $request): Response
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();
        $data['report_title'] = translate('crm_agent_sales_matrix_report');
        return app(ReportPdfService::class)->download(
            view: CrmAgentSalesMatrixReport::EXPORT_PDF[VIEW],
            data: $data,
            fileName: 'crm-agent-sales-matrix-report.pdf',
            orientation: 'landscape'
        );
    }

    private function buildReportData(Request $request): array
    {
        [$fromDate, $toDate, $dateType] = $this->resolveDateRange($request);
        $periodStrategy = $this->resolvePeriodStrategy($fromDate, $toDate);

        $departmentIds = $this->normalizeMultiIds($request->input('department_ids', $request->input('department_id', [])));
        $employeeIds = $this->normalizeMultiIds($request->input('employee_ids', $request->input('employee_id', [])));

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

        $rows = $this->getEmployeeRows(
            fromDate: $fromDate,
            toDate: $toDate,
            departmentIds: $departmentIds,
            employeeIds: $employeeIds
        );

        $employeeListForMatrix = $this->resolveEmployeeListForMatrix($rows, $employees, $employeeIds);
        $monthlyRows = $this->buildPeriodMatrix(
            rows: $rows,
            fromDate: $fromDate,
            toDate: $toDate,
            employeesForMatrix: $employeeListForMatrix,
            periodStrategy: $periodStrategy
        );

        $summary = $this->buildSummary($monthlyRows, $employeeListForMatrix);

        return [
            'departments' => $departments,
            'employees' => $employees,
            'employeesForMatrix' => $employeeListForMatrix,
            'filters' => [
                'date_type' => $dateType,
                'from' => $fromDate->locale(app()->getLocale())->translatedFormat('d F Y'),
                'to' => $toDate->locale(app()->getLocale())->translatedFormat('d F Y'),
                'department_ids' => $departmentIds,
                'employee_ids' => $employeeIds,
                'period_type' => $periodStrategy['type'],
            ],
            'monthlyRows' => $monthlyRows,
            'summary' => $summary,
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

    private function getEmployeeRows(
        Carbon $fromDate,
        Carbon $toDate,
        array $departmentIds = [],
        array $employeeIds = []
    ): Collection {
        $orderQtySub = DB::table('order_details')
            ->selectRaw('order_id, SUM(qty) as total_qty')
            ->groupBy('order_id');

        $wholesaleQtySub = DB::table('wholesale_purchase_order_items')
            ->selectRaw('wholesale_order_id, SUM(product_quantity) as total_qty')
            ->groupBy('wholesale_order_id');

        $rows = DB::table('deals')
            ->leftJoin('admins', 'admins.id', '=', 'deals.employee_id')
            ->leftJoinSub($orderQtySub, 'odq', function ($join) {
                $join->on('odq.order_id', '=', 'deals.order_id');
            })
            ->leftJoinSub($wholesaleQtySub, 'wpq', function ($join) {
                $join->on('wpq.wholesale_order_id', '=', 'deals.po_id');
            })
            ->where('deals.status', 'won')
            ->whereBetween('deals.created_at', [$fromDate, $toDate])
            ->when(!empty($departmentIds), fn($query) => $query->whereIn('deals.department_id', $departmentIds))
            ->when(!empty($employeeIds), fn($query) => $query->whereIn('deals.employee_id', $employeeIds))
            ->select([
                DB::raw('DATE(deals.created_at) as report_date'),
                DB::raw('COALESCE(deals.employee_id, 0) as employee_id'),
                DB::raw("MAX(COALESCE(admins.name, '')) as employee_name"),
                DB::raw("SUM(CASE WHEN deals.related_party_type = '" . self::RETAIL_PARTY_TYPE . "' THEN COALESCE(odq.total_qty, 0) ELSE 0 END) as retail_batteries"),
                DB::raw("COUNT(DISTINCT CASE WHEN deals.related_party_type = '" . self::RETAIL_PARTY_TYPE . "' THEN deals.related_party_id END) as retail_customers"),
                DB::raw("SUM(CASE WHEN deals.related_party_type = '" . self::WHOLESALE_PARTY_TYPE . "' THEN COALESCE(wpq.total_qty, 0) ELSE 0 END) as wholesale_batteries"),
                DB::raw("COUNT(DISTINCT CASE WHEN deals.related_party_type = '" . self::WHOLESALE_PARTY_TYPE . "' THEN deals.related_party_id END) as wholesale_customers"),
            ])
            ->groupBy(DB::raw('DATE(deals.created_at)'), DB::raw('COALESCE(deals.employee_id, 0)'))
            ->orderBy('report_date')
            ->orderBy('employee_name')
            ->get();

        return collect($rows)->map(function ($row) {
            $row->report_date = (string)$row->report_date;
            $row->employee_id = (int)$row->employee_id;
            $row->employee_name = (string)($row->employee_name ?: translate('unassigned'));
            $row->retail_batteries = (int)$row->retail_batteries;
            $row->retail_customers = (int)$row->retail_customers;
            $row->wholesale_batteries = (int)$row->wholesale_batteries;
            $row->wholesale_customers = (int)$row->wholesale_customers;
            return $row;
        });
    }

    private function resolveEmployeeListForMatrix(
        Collection $rows,
        Collection $allEmployees,
        array $employeeIds
    ): Collection {
        if (!empty($employeeIds)) {
            $filtered = $allEmployees
                ->whereIn('id', $employeeIds)
                ->map(fn($employee) => (object)[
                    'id' => (int)$employee->id,
                    'name' => (string)$employee->name,
                ])
                ->values();

            if ($rows->contains(fn($row) => (int)$row->employee_id === 0)) {
                $filtered->push((object)['id' => 0, 'name' => translate('unassigned')]);
            }

            return $filtered->sortBy('name')->values();
        }

        $fromRows = $rows
            ->groupBy('employee_id')
            ->map(function (Collection $group) {
                return (object)[
                    'id' => (int)$group->first()->employee_id,
                    'name' => (string)$group->first()->employee_name,
                ];
            })
            ->values();

        return $fromRows->sortBy('name')->values();
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
                            'retail_batteries' => (int)$employeeRows->sum('retail_batteries'),
                            'retail_customers' => (int)$employeeRows->sum('retail_customers'),
                            'wholesale_batteries' => (int)$employeeRows->sum('wholesale_batteries'),
                            'wholesale_customers' => (int)$employeeRows->sum('wholesale_customers'),
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
                'retail_batteries' => 0,
                'retail_customers' => 0,
                'wholesale_batteries' => 0,
                'wholesale_customers' => 0,
                'total_batteries' => 0,
                'total_customers' => 0,
            ];

            foreach ($employeesForMatrix as $employee) {
                $source = $rowsByPeriod->get($monthKey)?->get((int)$employee->id);
                $retailBatteries = (int)($source->retail_batteries ?? 0);
                $retailCustomers = (int)($source->retail_customers ?? 0);
                $wholesaleBatteries = (int)($source->wholesale_batteries ?? 0);
                $wholesaleCustomers = (int)($source->wholesale_customers ?? 0);

                $monthEmployeeRows[(int)$employee->id] = [
                    'retail_batteries' => $retailBatteries,
                    'retail_customers' => $retailCustomers,
                    'wholesale_batteries' => $wholesaleBatteries,
                    'wholesale_customers' => $wholesaleCustomers,
                    'total_batteries' => $retailBatteries + $wholesaleBatteries,
                    'total_customers' => $retailCustomers + $wholesaleCustomers,
                ];

                $monthTotals['retail_batteries'] += $retailBatteries;
                $monthTotals['retail_customers'] += $retailCustomers;
                $monthTotals['wholesale_batteries'] += $wholesaleBatteries;
                $monthTotals['wholesale_customers'] += $wholesaleCustomers;
                $monthTotals['total_batteries'] += $retailBatteries + $wholesaleBatteries;
                $monthTotals['total_customers'] += $retailCustomers + $wholesaleCustomers;
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
            'retail_batteries' => 0,
            'retail_customers' => 0,
            'wholesale_batteries' => 0,
            'wholesale_customers' => 0,
            'total_batteries' => 0,
            'total_customers' => 0,
        ];

        foreach ($monthlyRows as $monthRow) {
            $grand['retail_batteries'] += $monthRow->totals['retail_batteries'];
            $grand['retail_customers'] += $monthRow->totals['retail_customers'];
            $grand['wholesale_batteries'] += $monthRow->totals['wholesale_batteries'];
            $grand['wholesale_customers'] += $monthRow->totals['wholesale_customers'];
            $grand['total_batteries'] += $monthRow->totals['total_batteries'];
            $grand['total_customers'] += $monthRow->totals['total_customers'];
        }

        $perEmployee = [];
        foreach ($employeesForMatrix as $employee) {
            $retailBatteries = 0;
            $retailCustomers = 0;
            $wholesaleBatteries = 0;
            $wholesaleCustomers = 0;

            foreach ($monthlyRows as $monthRow) {
                $cell = $monthRow->employees[(int)$employee->id] ?? null;
                $retailBatteries += (int)($cell['retail_batteries'] ?? 0);
                $retailCustomers += (int)($cell['retail_customers'] ?? 0);
                $wholesaleBatteries += (int)($cell['wholesale_batteries'] ?? 0);
                $wholesaleCustomers += (int)($cell['wholesale_customers'] ?? 0);
            }

            $perEmployee[] = (object)[
                'employee_id' => (int)$employee->id,
                'employee_name' => (string)$employee->name,
                'retail_batteries' => $retailBatteries,
                'wholesale_batteries' => $wholesaleBatteries,
                'total_batteries' => $retailBatteries + $wholesaleBatteries,
                'retail_customers' => $retailCustomers,
                'wholesale_customers' => $wholesaleCustomers,
                'total_customers' => $retailCustomers + $wholesaleCustomers,
            ];
        }

        return [
            'grand' => $grand,
            'per_employee' => collect($perEmployee)->sortByDesc('total_batteries')->values(),
            'batteries_by_type' => [
                'retail' => $grand['retail_batteries'],
                'wholesale' => $grand['wholesale_batteries'],
                'total' => $grand['total_batteries'],
            ],
            'customers_by_type' => [
                'retail' => $grand['retail_customers'],
                'wholesale' => $grand['wholesale_customers'],
                'total' => $grand['total_customers'],
            ],
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
}
