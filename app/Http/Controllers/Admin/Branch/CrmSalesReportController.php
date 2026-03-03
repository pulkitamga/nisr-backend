<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CrmSalesReportController extends Controller
{
    /**
     * Display CRM sales report view
     */
    public function index()
    {
        // Get sales agents (assuming admin_role_id 3 is for sales agents)
        $agents = Admin::where('admin_role_id', 3)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        // Get years for dropdown
        $currentYear = date('Y');
        $years = [];
        for ($y = $currentYear - 5; $y <= $currentYear; $y++) {
            $years[] = $y;
        }

        // Get months for dropdown
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = Carbon::create()->month($m)->format('F');
        }

        return view('admin-views.branch-management.crm-sales-report', compact('agents', 'years', 'months'));
    }

    /**
     * Get CRM sales data via AJAX
     */
    public function getSalesData(Request $request)
    {
        Log::info('CRM Sales data request received:', $request->all());

        try {
            $validator = Validator::make($request->all(), [
                'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
                'month' => 'nullable|integer|min:1|max:12',
                'agent_ids' => 'nullable|array',
                'sale_type' => 'nullable|in:retail,wholesale'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $year = $request->year;
            $month = $request->month;
            $agentIds = $request->agent_ids ?? [];
            $saleType = $request->sale_type;

            // Get agents
            $agentsQuery = Admin::where('admin_role_id', 3)->where('status', 1);
            if (!empty($agentIds)) {
                $agentsQuery->whereIn('id', $agentIds);
            }
            $agents = $agentsQuery->get();

            // Get sales data
            $salesData = $this->getAgentSalesData($year, $month, $agents, $saleType);

            // Prepare pivot table data
            $pivotData = $this->preparePivotData($salesData, $agents, $year, $month);

            // Calculate statistics
            $statistics = $this->calculateStatistics($salesData, $agents);

            // Prepare chart data
            $chartData = $this->prepareChartData($pivotData, $agents, $month ? 'daily' : 'monthly');

            return response()->json([
                'success' => true,
                'agents' => $agents,
                'pivotData' => $pivotData,
                'chartData' => $chartData,
                'statistics' => $statistics,
                'periodType' => $month ? 'daily' : 'monthly'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getSalesData:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get agent sales data
     */
    private function getAgentSalesData($year, $month, $agents, $saleType = null)
    {
        $data = [];
        $orderDetailsQtySub = DB::table('order_details')
            ->select('order_id', DB::raw('SUM(qty) as order_total_qty'))
            ->groupBy('order_id');

        foreach ($agents as $agent) {
            // Build query for this agent
            $query = Order::where('sales_agent_id', $agent->id)
                ->whereYear('created_at', $year)
                ->where('order_status', 'delivered');

            if ($month) {
                $query->whereMonth('created_at', $month);
            }

            if ($saleType) {
                if ($saleType === 'wholesale') {
                    $query->where(function ($subQuery) {
                        $subQuery->where('order_amount', '>=', 10000)
                            ->orWhereHas('details.product', function ($q) {
                                $q->where('minimum_order_qty', '>=', 10);
                            });
                    });
                } else {
                    $query->where(function ($subQuery) {
                        $subQuery->where('order_amount', '<', 10000)
                            ->whereHas('details.product', function ($q) {
                                $q->where('minimum_order_qty', '<', 10);
                            });
                    });
                }
            }

            // Group by period
            if ($month) {
                $results = $query
                    ->leftJoinSub($orderDetailsQtySub, 'order_detail_totals', function ($join) {
                        $join->on('order_detail_totals.order_id', '=', 'orders.id');
                    })
                    ->select(
                        DB::raw('DAY(orders.created_at) as period'),
                        DB::raw('SUM(orders.order_amount) as total_sales'),
                        DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                        DB::raw('SUM(COALESCE(order_detail_totals.order_total_qty, 0)) as total_quantity'),
                        DB::raw('CASE
                            WHEN orders.order_amount >= 10000 THEN "wholesale"
                            ELSE "retail"
                        END as sale_type')
                    )
                    ->groupBy('period', DB::raw('sale_type'))
                    ->orderBy('period')
                    ->get();
            } else {
                $results = $query
                    ->leftJoinSub($orderDetailsQtySub, 'order_detail_totals', function ($join) {
                        $join->on('order_detail_totals.order_id', '=', 'orders.id');
                    })
                    ->select(
                        DB::raw('MONTH(orders.created_at) as period'),
                        DB::raw('SUM(orders.order_amount) as total_sales'),
                        DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                        DB::raw('SUM(COALESCE(order_detail_totals.order_total_qty, 0)) as total_quantity'),
                        DB::raw('CASE
                            WHEN orders.order_amount >= 10000 THEN "wholesale"
                            ELSE "retail"
                        END as sale_type')
                    )
                    ->groupBy('period', DB::raw('sale_type'))
                    ->orderBy('period')
                    ->get();
            }

            // Process results
            $agentData = [
                'id' => $agent->id,
                'name' => $agent->name,
                'period_data' => []
            ];

            foreach ($results as $row) {
                $agentData['period_data'][$row->period][$row->sale_type] = [
                    'sales' => $row->total_sales,
                    'orders' => $row->total_orders,
                    'quantity' => $row->total_quantity
                ];
            }

            $data[$agent->id] = $agentData;
        }

        return $data;
    }

    /**
     * Prepare pivot table data
     */
    private function preparePivotData($salesData, $agents, $year, $month)
    {
        $pivotData = [];

        // Determine periods
        if ($month) {
            // Daily view
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
            $periods = range(1, $daysInMonth);
        } else {
            // Monthly view
            $periods = range(1, 12);
        }

        // Initialize pivot data structure
        foreach ($periods as $period) {
            if ($month) {
                $periodLabel = sprintf('%02d', $period);
            } else {
                $periodLabel = Carbon::create($year, $period, 1)->format('M');
            }

            $pivotData[$period] = [
                'period' => $periodLabel,
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
                    'total_quantity' => 0
                ]
            ];

            foreach ($agents as $agent) {
                $agentData = $salesData[$agent->id]['period_data'][$period] ?? [];

                $retail = $agentData['retail'] ?? ['sales' => 0, 'orders' => 0, 'quantity' => 0];
                $wholesale = $agentData['wholesale'] ?? ['sales' => 0, 'orders' => 0, 'quantity' => 0];

                $pivotData[$period]['agents'][$agent->id] = [
                    'retail_sales' => $retail['sales'],
                    'wholesale_sales' => $wholesale['sales'],
                    'retail_orders' => $retail['orders'],
                    'wholesale_orders' => $wholesale['orders'],
                    'retail_quantity' => $retail['quantity'],
                    'wholesale_quantity' => $wholesale['quantity'],
                    'total_sales' => $retail['sales'] + $wholesale['sales'],
                    'total_orders' => $retail['orders'] + $wholesale['orders'],
                    'total_quantity' => $retail['quantity'] + $wholesale['quantity']
                ];

                // Update totals
                $pivotData[$period]['totals']['retail_sales'] += $retail['sales'];
                $pivotData[$period]['totals']['wholesale_sales'] += $wholesale['sales'];
                $pivotData[$period]['totals']['retail_orders'] += $retail['orders'];
                $pivotData[$period]['totals']['wholesale_orders'] += $wholesale['orders'];
                $pivotData[$period]['totals']['retail_quantity'] += $retail['quantity'];
                $pivotData[$period]['totals']['wholesale_quantity'] += $wholesale['quantity'];
                $pivotData[$period]['totals']['total_sales'] += $retail['sales'] + $wholesale['sales'];
                $pivotData[$period]['totals']['total_orders'] += $retail['orders'] + $wholesale['orders'];
                $pivotData[$period]['totals']['total_quantity'] += $retail['quantity'] + $wholesale['quantity'];
            }
        }

        return $pivotData;
    }

    /**
     * Prepare chart data
     */
    private function prepareChartData($pivotData, $agents, $periodType)
    {
        $data = [
            'labels' => [],
            'datasets' => []
        ];

        // Extract labels
        foreach ($pivotData as $period) {
            $data['labels'][] = $period['period'];
        }

        // Prepare retail dataset
        $retailData = [];
        $wholesaleData = [];

        foreach ($pivotData as $period) {
            $retailData[] = $period['totals']['retail_sales'];
            $wholesaleData[] = $period['totals']['wholesale_sales'];
        }

        $data['datasets'][] = [
            'label' => 'Retail Sales',
            'data' => $retailData,
            'borderColor' => '#3498db',
            'backgroundColor' => '#3498db80',
            'borderWidth' => 4,
            'tension' => 0.3,
            'fill' => true
        ];

        $data['datasets'][] = [
            'label' => 'Wholesale Sales',
            'data' => $wholesaleData,
            'borderColor' => '#2ecc71',
            'backgroundColor' => '#2ecc7180',
            'borderWidth' => 4,
            'tension' => 0.3,
            'fill' => true
        ];

        return $data;
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($salesData, $agents)
    {
        $stats = [
            'total_sales' => 0,
            'total_orders' => 0,
            'total_quantity' => 0,
            'retail_sales' => 0,
            'wholesale_sales' => 0,
            'top_agent' => null,
            'retail_percentage' => 0,
            'wholesale_percentage' => 0
        ];

        $agentTotals = [];

        foreach ($agents as $agent) {
            if (isset($salesData[$agent->id])) {
                $agentTotal = [
                    'retail_sales' => 0,
                    'wholesale_sales' => 0,
                    'total_sales' => 0
                ];

                foreach ($salesData[$agent->id]['period_data'] as $periodData) {
                    $agentTotal['retail_sales'] += $periodData['retail']['sales'] ?? 0;
                    $agentTotal['wholesale_sales'] += $periodData['wholesale']['sales'] ?? 0;
                    $agentTotal['total_sales'] += ($periodData['retail']['sales'] ?? 0) + ($periodData['wholesale']['sales'] ?? 0);
                }

                $agentTotals[$agent->id] = [
                    'name' => $agent->name,
                    'total' => $agentTotal['total_sales']
                ];

                $stats['total_sales'] += $agentTotal['total_sales'];
                $stats['retail_sales'] += $agentTotal['retail_sales'];
                $stats['wholesale_sales'] += $agentTotal['wholesale_sales'];
            }
        }

        // Find top agent
        if (!empty($agentTotals)) {
            $topAgent = collect($agentTotals)->sortByDesc('total')->first();
            $stats['top_agent'] = $topAgent['name'] . ' (' . setCurrencySymbol(
                amount: usdToDefaultCurrency(amount: $topAgent['total']),
                currencyCode: getCurrencyCode()
            ) . ')';
        }

        // Calculate percentages
        if ($stats['total_sales'] > 0) {
            $stats['retail_percentage'] = round(($stats['retail_sales'] / $stats['total_sales']) * 100, 2);
            $stats['wholesale_percentage'] = round(($stats['wholesale_sales'] / $stats['total_sales']) * 100, 2);
        }

        return $stats;
    }
}
