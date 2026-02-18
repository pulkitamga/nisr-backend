<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockTransfers;
use App\Models\StockTransferProduct;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class StockTransferReportController extends Controller
{
    /**
     * Display the stock transfer report view
     */
    public function index()
    {
        $branches = Branch::where('status', 'active')->get();
        
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
        
        return view('admin-views.branch-management.stock-transfer-report', compact('branches', 'years', 'months'));
    }

    /**
     * Get stock transfer data via AJAX
     */
    public function getTransferData(Request $request)
    {
        Log::info('Stock transfer data request received:', $request->all());
        
        try {
            $validator = Validator::make($request->all(), [
                'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
                'month' => 'nullable|integer|min:1|max:12',
                'from_branch_id' => 'nullable|exists:branches,id',
                'to_branch_id' => 'nullable|exists:branches,id',
                'status' => 'nullable|in:pending,approved,rejected'
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
            $fromBranchId = $request->from_branch_id;
            $toBranchId = $request->to_branch_id;
            $status = $request->status;
            
            // Build query
            $query = StockTransfers::with([
                'fromBranch',
                'toBranch',
                'products.product',
                'products.category'
            ]);
            
            // Filter by year and month
            if ($month) {
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
                $query->whereBetween('transfer_date', [$startDate, $endDate]);
            } else {
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
                $query->whereBetween('transfer_date', [$startDate, $endDate]);
            }
            
            // Filter by from branch
            if ($fromBranchId) {
                $query->where('from_branch_id', $fromBranchId);
            }
            
            // Filter by to branch
            if ($toBranchId) {
                $query->where('to_branch_id', $toBranchId);
            }
            
            // Filter by status
            if ($status) {
                $query->whereHas('products', function($q) use ($status) {
                    $q->where('status', $status);
                });
            }
            
            // Get transfers
            $transfers = $query->orderBy('transfer_date', 'desc')->get();
            
            // Process data for chart
            $chartData = $this->prepareChartData($transfers, $month ? 'daily' : 'monthly');
            
            // Calculate statistics
            $statistics = $this->calculateStatistics($transfers);
            
            return response()->json([
                'success' => true,
                'transfers' => $transfers,
                'chartData' => $chartData,
                'statistics' => $statistics,
                'periodType' => $month ? 'daily' : 'monthly'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getTransferData:', [
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
     * Prepare chart data
     */
    private function prepareChartData($transfers, $periodType)
    {
        $data = [
            'labels' => [],
            'datasets' => []
        ];
        
        // Group transfers by period
        if ($periodType === 'daily') {
            $grouped = $transfers->groupBy(function($item) {
                return Carbon::parse($item->transfer_date)->format('d M');
            });
            
            foreach ($grouped as $day => $dayTransfers) {
                $data['labels'][] = $day;
            }
        } else {
            // Group by month
            $grouped = $transfers->groupBy(function($item) {
                return Carbon::parse($item->transfer_date)->format('M');
            });
            
            // Ensure all months
            $allMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($allMonths as $month) {
                $data['labels'][] = $month;
            }
        }
        
        // Prepare datasets for different statuses
        $statuses = ['pending', 'approved', 'rejected'];
        $colors = ['#f39c12', '#2ecc71', '#e74c3c'];
        
        foreach ($statuses as $index => $status) {
            $statusData = [];
            
            if ($periodType === 'daily') {
                foreach ($grouped as $day => $dayTransfers) {
                    $count = $dayTransfers->filter(function($transfer) use ($status) {
                        return $transfer->products->where('status', $status)->count() > 0;
                    })->count();
                    $statusData[] = $count;
                }
            } else {
                foreach ($allMonths as $month) {
                    $monthTransfers = $transfers->filter(function($transfer) use ($month) {
                        return Carbon::parse($transfer->transfer_date)->format('M') === $month;
                    });
                    
                    $count = $monthTransfers->filter(function($transfer) use ($status) {
                        return $transfer->products->where('status', $status)->count() > 0;
                    })->count();
                    $statusData[] = $count;
                }
            }
            
            $data['datasets'][] = [
                'label' => ucfirst($status),
                'data' => $statusData,
                'backgroundColor' => $colors[$index] . '80',
                'borderColor' => $colors[$index],
                'borderWidth' => 3,
                'tension' => 0.1
            ];
        }
        
        return $data;
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($transfers)
    {
        $stats = [
            'total_transfers' => $transfers->count(),
            'pending_transfers' => 0,
            'approved_transfers' => 0,
            'rejected_transfers' => 0,
            'total_quantity' => 0,
            'top_from_branch' => null,
            'top_to_branch' => null
        ];
        
        $fromBranchCounts = [];
        $toBranchCounts = [];
        
        foreach ($transfers as $transfer) {
            // Count by status
            foreach ($transfer->products as $product) {
                switch ($product->status) {
                    case 'pending':
                        $stats['pending_transfers']++;
                        break;
                    case 'approved':
                        $stats['approved_transfers']++;
                        $stats['total_quantity'] += $product->quantity;
                        break;
                    case 'rejected':
                        $stats['rejected_transfers']++;
                        break;
                }
            }
            
            // Count by from branch
            if ($transfer->fromBranch) {
                $fromBranchCounts[$transfer->fromBranch->id] = [
                    'name' => $transfer->fromBranch->branch_name,
                    'count' => ($fromBranchCounts[$transfer->fromBranch->id]['count'] ?? 0) + 1
                ];
            }
            
            // Count by to branch
            if ($transfer->toBranch) {
                $toBranchCounts[$transfer->toBranch->id] = [
                    'name' => $transfer->toBranch->branch_name,
                    'count' => ($toBranchCounts[$transfer->toBranch->id]['count'] ?? 0) + 1
                ];
            }
        }
        
        // Find top branches
        if (!empty($fromBranchCounts)) {
            $topFrom = collect($fromBranchCounts)->sortByDesc('count')->first();
            $stats['top_from_branch'] = $topFrom['name'] . ' (' . $topFrom['count'] . ' transfers)';
        }
        
        if (!empty($toBranchCounts)) {
            $topTo = collect($toBranchCounts)->sortByDesc('count')->first();
            $stats['top_to_branch'] = $topTo['name'] . ' (' . $topTo['count'] . ' transfers)';
        }
        
        return $stats;
    }
}