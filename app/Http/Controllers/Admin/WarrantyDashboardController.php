<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class WarrantyDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = WarrantyClaim::with('warranty.user')->latest();

        // Search by claim number or serial
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $recentClaims = $query->take(10)->get();

        $slaTrackedClaims = WarrantyClaim::whereNotNull('resolution_due');
        $slaTotal = (clone $slaTrackedClaims)->count();
        $slaWithin = (clone $slaTrackedClaims)
            ->where(function ($q) {
                $q->where(function ($resolved) {
                    $resolved->whereNotNull('resolved_at')
                        ->whereColumn('resolved_at', '<=', 'resolution_due');
                })->orWhere(function ($open) {
                    $open->whereNull('resolved_at')
                        ->where('resolution_due', '>', now());
                });
            })
            ->count();

        $stats = [
            'active_count' => Warranty::where('status', 'active')->where('end_date', '>', now())->count(),
            'expired_count' => Warranty::where('status', 'active')->where('end_date', '<=', now())->count(),
            'claims_open' => WarrantyClaim::whereNotIn('status', ['resolved', 'closed', 'rejected'])->count(),
            'sla_compliance' => $slaTotal > 0 ? ($slaWithin / $slaTotal) * 100 : 0,
        ];

        return view('admin-views.warranty.dashboard', compact('stats', 'recentClaims'));
    }
}
