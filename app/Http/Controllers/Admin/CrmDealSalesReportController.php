<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ViewPaths\Admin\CrmDealSalesReport;
use App\Exports\CrmDealSalesReportExport;
use App\Http\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Departments;
use App\Services\ReportPdfService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class CrmDealSalesReportController extends BaseController
{
    private const RETAIL_PARTY_TYPE = 'contact';
    private const WHOLESALE_PARTY_TYPE = 'company';

    public function index(?Request $request, string $type = null): View
    {
        $request = $request ?? request();
        return view(CrmDealSalesReport::VIEW[VIEW], $this->buildReportData($request));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();

        return Excel::download(
            new CrmDealSalesReportExport($data),
            'crm-sales-performance-report.xlsx'
        );
    }

    public function exportPdf(Request $request): Response
    {
        $data = $this->buildReportData($request);
        $data['exportedAt'] = now();

        return app(ReportPdfService::class)->download(
            view: CrmDealSalesReport::EXPORT_PDF[VIEW],
            data: $data,
            fileName: 'crm-sales-performance-report.pdf',
            orientation: 'landscape'
        );
    }

    private function buildReportData(Request $request): array
    {
        [$fromDate, $toDate, $dateType] = $this->resolveDateRange($request);

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

        $rows = $this->getDealRows(
            fromDate: $fromDate,
            toDate: $toDate,
            departmentIds: $departmentIds,
            employeeIds: $employeeIds
        );

        $summary = [
            'won_count' => (int)$rows->sum('won_count'),
            'lost_count' => (int)$rows->sum('lost_count'),
            'retail_won_sales' => (float)$rows->sum('retail_won_sales'),
            'wholesale_won_sales' => (float)$rows->sum('wholesale_won_sales'),
            'retail_won_count' => (int)$rows->sum('retail_won_count'),
            'wholesale_won_count' => (int)$rows->sum('wholesale_won_count'),
            'retail_lost_count' => (int)$rows->sum('retail_lost_count'),
            'wholesale_lost_count' => (int)$rows->sum('wholesale_lost_count'),
            'total_deals' => (int)$rows->sum('total_deals'),
        ];
        $summary['won_sales_total'] = $summary['retail_won_sales'] + $summary['wholesale_won_sales'];

        $departmentSections = $this->buildDepartmentSections($rows);

        $chartRows = $rows->sortByDesc('total_deals')->take(12)->values();

        return [
            'departments' => $departments,
            'employees' => $employees,
            'filters' => [
                'date_type' => $dateType,
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
                'department_ids' => $departmentIds,
                'employee_ids' => $employeeIds,
            ],
            'summary' => $summary,
            'departmentSections' => $departmentSections,
            'chart' => [
                'employee_labels' => $chartRows->pluck('employee_name')->all(),
                'employee_won_counts' => $chartRows->pluck('won_count')->all(),
                'employee_lost_counts' => $chartRows->pluck('lost_count')->all(),
                'employee_retail_won_sales' => $chartRows->pluck('retail_won_sales')->map(fn($value) => round((float)$value, 2))->all(),
                'employee_wholesale_won_sales' => $chartRows->pluck('wholesale_won_sales')->map(fn($value) => round((float)$value, 2))->all(),
                'status_labels' => [translate('won'), translate('lost')],
                'status_values' => [$summary['won_count'], $summary['lost_count']],
                'sales_type_labels' => [translate('retail'), translate('wholesale')],
                'sales_type_values' => [round($summary['retail_won_sales'], 2), round($summary['wholesale_won_sales'], 2)],
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

    private function getDealRows(
        Carbon $fromDate,
        Carbon $toDate,
        array $departmentIds = [],
        array $employeeIds = []
    ): Collection {
        $rows = DB::table('deals')
            ->leftJoin('departments', 'departments.id', '=', 'deals.department_id')
            ->leftJoin('admins', 'admins.id', '=', 'deals.employee_id')
            ->whereIn('deals.status', ['won', 'lost'])
            ->whereBetween('deals.created_at', [$fromDate, $toDate])
            ->when(!empty($departmentIds), fn($query) => $query->whereIn('deals.department_id', $departmentIds))
            ->when(!empty($employeeIds), fn($query) => $query->whereIn('deals.employee_id', $employeeIds))
            ->select([
                DB::raw('COALESCE(deals.department_id, 0) as department_id'),
                DB::raw('MAX(departments.name) as department_name'),
                DB::raw('COALESCE(deals.employee_id, 0) as employee_id'),
                DB::raw('MAX(admins.name) as employee_name'),
                DB::raw("SUM(CASE WHEN deals.status = 'won' AND deals.related_party_type = '" . self::RETAIL_PARTY_TYPE . "' THEN 1 ELSE 0 END) as retail_won_count"),
                DB::raw("SUM(CASE WHEN deals.status = 'won' AND deals.related_party_type = '" . self::WHOLESALE_PARTY_TYPE . "' THEN 1 ELSE 0 END) as wholesale_won_count"),
                DB::raw("SUM(CASE WHEN deals.status = 'lost' AND deals.related_party_type = '" . self::RETAIL_PARTY_TYPE . "' THEN 1 ELSE 0 END) as retail_lost_count"),
                DB::raw("SUM(CASE WHEN deals.status = 'lost' AND deals.related_party_type = '" . self::WHOLESALE_PARTY_TYPE . "' THEN 1 ELSE 0 END) as wholesale_lost_count"),
                DB::raw("SUM(CASE WHEN deals.status = 'won' AND deals.related_party_type = '" . self::RETAIL_PARTY_TYPE . "' THEN COALESCE(deals.value, 0) ELSE 0 END) as retail_won_sales"),
                DB::raw("SUM(CASE WHEN deals.status = 'won' AND deals.related_party_type = '" . self::WHOLESALE_PARTY_TYPE . "' THEN COALESCE(deals.value, 0) ELSE 0 END) as wholesale_won_sales"),
                DB::raw("SUM(CASE WHEN deals.status = 'won' THEN 1 ELSE 0 END) as won_count"),
                DB::raw("SUM(CASE WHEN deals.status = 'lost' THEN 1 ELSE 0 END) as lost_count"),
                DB::raw('COUNT(*) as total_deals'),
            ])
            ->groupBy(DB::raw('COALESCE(deals.department_id, 0)'), DB::raw('COALESCE(deals.employee_id, 0)'))
            ->orderBy('department_name')
            ->orderBy('employee_name')
            ->get();

        return collect($rows)->map(function ($row) {
            $row->department_id = (int)$row->department_id;
            $row->department_name = $row->department_name ?: translate('unassigned');
            $row->employee_id = (int)$row->employee_id;
            $row->employee_name = $row->employee_name ?: translate('unassigned');
            $row->retail_won_count = (int)$row->retail_won_count;
            $row->wholesale_won_count = (int)$row->wholesale_won_count;
            $row->retail_lost_count = (int)$row->retail_lost_count;
            $row->wholesale_lost_count = (int)$row->wholesale_lost_count;
            $row->retail_won_sales = (float)$row->retail_won_sales;
            $row->wholesale_won_sales = (float)$row->wholesale_won_sales;
            $row->won_count = (int)$row->won_count;
            $row->lost_count = (int)$row->lost_count;
            $row->total_deals = (int)$row->total_deals;
            $row->won_sales_total = $row->retail_won_sales + $row->wholesale_won_sales;
            return $row;
        });
    }

    private function buildDepartmentSections(Collection $rows): Collection
    {
        return $rows
            ->groupBy(fn($row) => (string)$row->department_id)
            ->map(function (Collection $departmentRows) {
                return (object)[
                    'department_id' => (int)$departmentRows->first()->department_id,
                    'department_name' => (string)$departmentRows->first()->department_name,
                    'employees' => $departmentRows->sortBy('employee_name')->values(),
                    'totals' => [
                        'retail_won_sales' => (float)$departmentRows->sum('retail_won_sales'),
                        'wholesale_won_sales' => (float)$departmentRows->sum('wholesale_won_sales'),
                        'won_sales_total' => (float)$departmentRows->sum('won_sales_total'),
                        'retail_won_count' => (int)$departmentRows->sum('retail_won_count'),
                        'wholesale_won_count' => (int)$departmentRows->sum('wholesale_won_count'),
                        'retail_lost_count' => (int)$departmentRows->sum('retail_lost_count'),
                        'wholesale_lost_count' => (int)$departmentRows->sum('wholesale_lost_count'),
                        'won_count' => (int)$departmentRows->sum('won_count'),
                        'lost_count' => (int)$departmentRows->sum('lost_count'),
                        'total_deals' => (int)$departmentRows->sum('total_deals'),
                    ],
                ];
            })
            ->values();
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
