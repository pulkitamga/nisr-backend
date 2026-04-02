<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use Illuminate\Http\Request;

class WarrantyDashboardController extends Controller
{
    private const MAX_RECENT_CLAIMS_LIMIT = 100;

    public function dashboard(Request $request)
    {
        $query = WarrantyClaim::with('warranty.user')->latest();

        if ($request->filled('searchValue')) {
            $search = $this->sanitizeSearchTerm($request->input('searchValue'));
            $query->where(function ($q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $recentClaimsLimit = $this->resolveRecentClaimsLimit($request);
        $recentClaims = $query->take($recentClaimsLimit)->get();

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

        return view('admin-views.warranty.dashboard', compact('stats', 'recentClaims', 'recentClaimsLimit'));
    }

    private function resolveRecentClaimsLimit(Request $request): int
    {
        $value = $request->input('choose_first');

        if (!is_numeric($value)) {
            return 10;
        }

        $limit = (int) $value;

        if ($limit <= 0) {
            return 10;
        }

        return min($limit, self::MAX_RECENT_CLAIMS_LIMIT);
    }

    private function sanitizeSearchTerm(?string $value): string
    {
        return mb_substr(trim((string) $value), 0, 100);
    }
}
