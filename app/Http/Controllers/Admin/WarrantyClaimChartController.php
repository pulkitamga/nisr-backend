<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarrantyClaim;
use App\Models\Branch;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\WarrantyClaimsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class WarrantyClaimChartController extends Controller
{
    public function index(Request $request)
    {
        $dates = $this->parseDateRange($request);
        $startDate = $dates['start'];
        $endDate   = $dates['end'];

        $branches = Branch::select('id', 'branch_name as name')->get();

        $products = Product::whereHas('warranties', function ($query) {
            $query->where('status', 'active');
        })->select('id', 'name')->get();

        $cards      = $this->getCardsData($startDate, $endDate, $request);
        $chartData  = $this->prepareChartData($startDate, $endDate, $request);
        $claims     = $this->getFilteredQuery($startDate, $endDate, $request)->paginate(15);

        return view('admin-views.warranty.claim-chart', compact(
            'startDate',
            'endDate',
            'cards',
            'chartData',
            'claims',
            'branches',
            'products'
        ));
    }

    public function getChartData(Request $request)
    {
        $dates = $this->parseDateRange($request);
        $start = $dates['start'];
        $end   = $dates['end'];

        return response()->json([
            'cards' => $this->getCardsData($start, $end, $request),
            'chart' => $this->prepareChartData($start, $end, $request),
        ]);
    }

    public function getTableData(Request $request)
    {
        $dates = $this->parseDateRange($request);
        $start = $dates['start'];
        $end   = $dates['end'];

        $query = $this->getFilteredQuery($start, $end, $request);
        $claims = $query->paginate(15);

        $formattedClaims = $claims->map(function ($claim) {
            return [
                'id'            => $claim->id,
                'claim_number'  => $claim->claim_number,
                'serial_number' => $claim->serial_number,
                'status'        => $claim->status,
                'customer'      => $claim->warranty?->user?->name ?? $claim->warranty?->activated_by_name ?? '',
                'submitted_at'  => $claim->submitted_at?->format('Y-m-d H:i A'),
                'resolution_due' => $claim->resolution_due?->format('Y-m-d H:i A') ?? '-',
                'view_url'      => route('admin.warranty.claim.view', $claim->id),
                'product_name'  => $claim->warranty?->product?->name ?? '-',
            ];
        });

        return response()->json([
            'current_page' => $claims->currentPage(),
            'data'         => $formattedClaims,
            'from'         => $claims->firstItem(),
            'last_page'    => $claims->lastPage(),
            'per_page'     => $claims->perPage(),
            'to'           => $claims->lastItem(),
            'total'        => $claims->total(),
            'prev_page_url' => $claims->previousPageUrl(),
            'next_page_url' => $claims->nextPageUrl(),
        ]);
    }

    private function parseDateRange(Request $request)
    {
        if ($request->filled('date_range')) {
            [$start, $end] = explode(' - ', $request->date_range);
            $start = Carbon::createFromFormat('m/d/Y', trim($start));
            $end   = Carbon::createFromFormat('m/d/Y', trim($end));
        } else {
            $end   = Carbon::today();
            $start = Carbon::today()->subDays(6);
        }
        return ['start' => $start, 'end' => $end];
    }

    private function getFilteredQuery($start, $end, Request $request = null)
    {
        $query = WarrantyClaim::with('warranty.user', 'warranty.product', 'branch')
            ->whereBetween('submitted_at', [$start, $end->copy()->endOfDay()])
            ->orderBy('submitted_at', 'desc');

        if ($request) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
            if ($request->filled('status') && $request->status != 'all') {
                $query->where('status', $request->status);
            }
            if ($request->filled('product_id')) {
                $query->whereHas('warranty', function ($q) use ($request) {
                    $q->where('product_id', $request->product_id);
                });
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('claim_number', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%");
                });
            }
        }
        return $query;
    }

    private function getCardsData($start, $end, Request $request = null)
    {
        $query = WarrantyClaim::whereBetween('submitted_at', [$start, $end->copy()->endOfDay()]);

        if ($request) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
            if ($request->filled('product_id')) {
                $query->whereHas('warranty', function ($q) use ($request) {
                    $q->where('product_id', $request->product_id);
                });
            }
        }

        return [
            'total'    => (clone $query)->count(),
            'new'      => (clone $query)->where('status', 'new')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'pending'  => (clone $query)->whereIn('status', [
                'rma_issued',
                'received',
                'repair_pending',
                'replacement_pending',
                'qc_pending',
                'shipped_ready',
                'dispatched',
                'waiting_customer',
                'waiting_parts',
                'waiting_payment'
            ])->count(),
        ];
    }

    private function prepareChartData($start, $end, Request $request = null)
    {
        $statuses = [
            'new',
            'approved',
            'rma_issued',
            'received',
            'repair_pending',
            'replacement_pending',
            'qc_pending',
            'dispatched',
            'resolved',
            'rejected',
            'closed'
        ];

        $period = \Carbon\CarbonPeriod::create($start, $end);
        $labels = [];
        $dateKeys = [];
        foreach ($period as $date) {
            $labels[] = $date->format('d M');
            $dateKeys[] = $date->format('Y-m-d');
        }

        $query = WarrantyClaim::select(
            DB::raw('DATE(submitted_at) as date'),
            'status',
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('submitted_at', [$start, $end->copy()->endOfDay()]);

        if ($request) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
            if ($request->filled('product_id')) {
                $query->whereHas('warranty', function ($q) use ($request) {
                    $q->where('product_id', $request->product_id);
                });
            }
        }

        $raw = $query->groupBy('date', 'status')
            ->get()
            ->groupBy('date');

        $datasets = [];
        foreach ($statuses as $status) {
            $data = array_map(function ($date) use ($raw, $status) {
                $dayData = $raw->get($date);
                if ($dayData) {
                    $record = $dayData->firstWhere('status', $status);
                    return $record ? (int) $record->count : 0;
                }
                return 0;
            }, $dateKeys);

            $datasets[] = [
                'label' => translate($status),
                'data'  => $data,
                'backgroundColor' => $this->getStatusColor($status),
            ];
        }

        return [
            'labels'   => $labels,
            'datasets' => $datasets,
        ];
    }

    private function getStatusColor($status)
    {
        $colors = [
            'new'               => '#3498db',
            'approved'          => '#2ecc71',
            'rma_issued'        => '#f39c12',
            'received'          => '#9b59b6',
            'repair_pending'    => '#e67e22',
            'replacement_pending' => '#1abc9c',
            'qc_pending'        => '#f1c40f',
            'dispatched'        => '#34495e',
            'resolved'          => '#27ae60',
            'rejected'          => '#e74c3c',
            'closed'            => '#7f8c8d',
        ];
        return $colors[$status] ?? '#95a5a6';
    }

    public function exportExcel(Request $request)
    {
        $locale = session('locale', config('app.locale'));
        return Excel::download(new WarrantyClaimsExport($request, $locale), 'warranty-claims.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $locale = session('locale', config('app.locale'));
        app()->setLocale($locale);

        $dates = $this->parseDateRange($request);
        $start = $dates['start'];
        $end   = $dates['end'];

        $query = $this->getFilteredQuery($start, $end, $request);
        $claims = $query->get();

        $cards = $this->getCardsData($start, $end, $request);

        $dailyBreakdown = $this->getDailyBreakdown($start, $end, $request);

        $filters = [
            'date_range' => $request->date_range ?? $start->format('d M Y') . ' - ' . $end->format('d M Y'),
            'branch'     => $request->branch_id ? Branch::find($request->branch_id)->branch_name : 'All',
            'status'     => $request->status ?? 'All',
            'product'    => $request->product_id ? Product::find($request->product_id)->name : 'All',
            'search'     => $request->search ?? '',
        ];

        $pdf = Pdf::loadView('admin-views.warranty.pdf-claims', compact('claims', 'cards', 'dailyBreakdown', 'filters', 'start', 'end'));
        return $pdf->download('warranty-claims.pdf');
    }

    private function getDailyBreakdown($start, $end, Request $request = null)
    {
        $statuses = [
            'new',
            'approved',
            'rma_issued',
            'received',
            'repair_pending',
            'replacement_pending',
            'qc_pending',
            'dispatched',
            'resolved',
            'rejected',
            'closed',
            'waiting_customer',
            'waiting_parts',
            'waiting_payment'
        ];

        $period = \Carbon\CarbonPeriod::create($start, $end);
        $dates = [];
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = [
                'date'          => $date->format('d M Y'),
                'total'         => 0,
                'new'           => 0,
                'approved'      => 0,
                'rma_issued'    => 0,
                'received'      => 0,
                'repair_pending' => 0,
                'replacement_pending' => 0,
                'qc_pending'    => 0,
                'dispatched'    => 0,
                'resolved'      => 0,
                'rejected'      => 0,
                'closed'        => 0,
                'waiting_customer' => 0,
                'waiting_parts' => 0,
                'waiting_payment' => 0,
            ];
        }

        $query = WarrantyClaim::select(
            DB::raw('DATE(submitted_at) as date'),
            'status',
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('submitted_at', [$start, $end->copy()->endOfDay()])
            ->groupBy('date', 'status');

        if ($request) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
            if ($request->filled('product_id')) {
                $query->whereHas('warranty', function ($q) use ($request) {
                    $q->where('product_id', $request->product_id);
                });
            }
        }

        $results = $query->get();

        foreach ($results as $row) {
            $dateKey = $row->date;
            if (isset($dates[$dateKey])) {
                $status = $row->status;
                if (in_array($status, array_keys($dates[$dateKey]))) {
                    $dates[$dateKey][$status] = (int) $row->count;
                    $dates[$dateKey]['total'] += (int) $row->count;
                }
            }
        }

        return array_values($dates);
    }
}