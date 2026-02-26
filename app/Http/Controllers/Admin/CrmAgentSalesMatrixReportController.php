<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ViewPaths\Admin\CrmAgentSalesMatrixReport;
use App\Exports\CrmAgentSalesMatrixReportExport;
use App\Http\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Departments;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $pdf = Pdf::loadView(CrmAgentSalesMatrixReport::EXPORT_PDF[VIEW], $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('crm-agent-sales-matrix-report.pdf');
    }

    private function buildReportData(Request $request): array
    {
        [$fromDate, $toDate] = $this->resolveDateRange($request);

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

        $rows = $this->getMonthlyEmployeeRows(
            fromDate: $fromDate,
            toDate: $toDate,
            departmentIds: $departmentIds,
            employeeIds: $employeeIds
        );

        $employeeListForMatrix = $this->resolveEmployeeListForMatrix($rows, $employees, $employeeIds);
        $monthlyRows = $this->buildMonthlyMatrix(
            rows: $rows,
            fromDate: $fromDate,
            toDate: $toDate,
            employeesForMatrix: $employeeListForMatrix
        );

        $summary = $this->buildSummary($monthlyRows, $employeeListForMatrix);

        return [
            'departments' => $departments,
            'employees' => $employees,
            'employeesForMatrix' => $employeeListForMatrix,
            'filters' => [
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
                'department_ids' => $departmentIds,
                'employee_ids' => $employeeIds,
            ],
            'monthlyRows' => $monthlyRows,
            'summary' => $summary,
        ];
    }

    private function resolveDateRange(Request $request): array
    {
        $from = $request->input('from');
        $to = $request->input('to');

        try {
            $fromDate = $from ? Carbon::parse($from)->startOfDay() : now()->startOfYear()->startOfDay();
        } catch (\Throwable) {
            $fromDate = now()->startOfYear()->startOfDay();
        }

        try {
            $toDate = $to ? Carbon::parse($to)->endOfDay() : now()->endOfYear()->endOfDay();
        } catch (\Throwable) {
            $toDate = now()->endOfYear()->endOfDay();
        }

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        return [$fromDate, $toDate];
    }

    private function getMonthlyEmployeeRows(
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
                DB::raw("DATE_FORMAT(deals.created_at, '%Y-%m-01') as report_month"),
                DB::raw('COALESCE(deals.employee_id, 0) as employee_id'),
                DB::raw("MAX(COALESCE(admins.name, '')) as employee_name"),
                DB::raw("SUM(CASE WHEN deals.related_party_type = '" . self::RETAIL_PARTY_TYPE . "' THEN COALESCE(odq.total_qty, 0) ELSE 0 END) as retail_batteries"),
                DB::raw("COUNT(DISTINCT CASE WHEN deals.related_party_type = '" . self::RETAIL_PARTY_TYPE . "' THEN deals.related_party_id END) as retail_customers"),
                DB::raw("SUM(CASE WHEN deals.related_party_type = '" . self::WHOLESALE_PARTY_TYPE . "' THEN COALESCE(wpq.total_qty, 0) ELSE 0 END) as wholesale_batteries"),
                DB::raw("COUNT(DISTINCT CASE WHEN deals.related_party_type = '" . self::WHOLESALE_PARTY_TYPE . "' THEN deals.related_party_id END) as wholesale_customers"),
            ])
            ->groupBy(DB::raw("DATE_FORMAT(deals.created_at, '%Y-%m-01')"), DB::raw('COALESCE(deals.employee_id, 0)'))
            ->orderBy('report_month')
            ->orderBy('employee_name')
            ->get();

        return collect($rows)->map(function ($row) {
            $row->report_month = (string)$row->report_month;
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

    private function buildMonthlyMatrix(
        Collection $rows,
        Carbon $fromDate,
        Carbon $toDate,
        Collection $employeesForMatrix
    ): Collection {
        $rowsByMonth = $rows
            ->groupBy('report_month')
            ->map(fn(Collection $monthRows) => $monthRows->keyBy('employee_id'));

        $months = [];
        $period = CarbonPeriod::create($fromDate->copy()->startOfMonth(), '1 month', $toDate->copy()->startOfMonth());
        foreach ($period as $monthDate) {
            $monthKey = $monthDate->format('Y-m-01');
            $monthLabel = $monthDate->locale(app()->getLocale())->translatedFormat('M Y');

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
                $source = $rowsByMonth->get($monthKey)?->get($employee->id);
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
