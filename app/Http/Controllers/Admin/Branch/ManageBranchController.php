<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManageBranchController extends Controller
{

    public function vendors(Request $request)
    {
        $manager = auth('admin')->user();
        $managerBranchId = $manager?->branch_id;
        $isSuperAdmin = $manager?->isSuperAdmin() === true;

        if (!$manager) {
            abort(403);
        }

        if (!$isSuperAdmin && !$managerBranchId) {
            Toastr::error(translate('branch_manager_must_be_assigned_to_view_vendors'));

            return redirect()->route('admin.branch.index');
        }

        $vendors = $this->vendorsQuery($request, $isSuperAdmin, $managerBranchId)
            ->paginate($this->resolveListPerPage($request))
            ->appends($request->query());

        return view('admin-views.branch-management.manage-branch.vendors', compact('vendors'));
    }

    public function exportVendors(Request $request): StreamedResponse|RedirectResponse
    {
        $manager = auth('admin')->user();
        $managerBranchId = $manager?->branch_id;
        $isSuperAdmin = $manager?->isSuperAdmin() === true;

        if (!$manager) {
            abort(403);
        }

        if (!$isSuperAdmin && !$managerBranchId) {
            Toastr::error(translate('branch_manager_must_be_assigned_to_view_vendors'));

            return redirect()->route('admin.branch.index');
        }

        $vendors = $this->vendorsQuery($request, $isSuperAdmin, $managerBranchId)->get();

        return response()->streamDownload(function () use ($vendors) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                translate('Vendor'),
                translate('email'),
                translate('Phone'),
                translate('Status'),
            ]);

            foreach ($vendors as $vendor) {
                fputcsv($handle, [
                    trim($vendor->f_name . ' ' . $vendor->l_name),
                    $vendor->email,
                    $vendor->phone,
                    translate($vendor->status),
                ]);
            }

            fclose($handle);
        }, 'branch-vendors.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

     public function show($id): View|RedirectResponse
    {
        $manager = auth('admin')->user();
        if (!$manager) {
            abort(403);
        }

        $managerBranchId = (int)($manager->branch_id ?? 0);
        $isSuperAdmin = $manager->isSuperAdmin();

        if (!$isSuperAdmin && $managerBranchId <= 0) {
            Toastr::error(translate('branch_manager_must_be_assigned_to_view_vendors'));

            return redirect()->route('admin.branch.vendors');
        }

        $vendor = Seller::query()->findOrFail($id);

        if (!$isSuperAdmin && !$vendor->branches()->whereKey($managerBranchId)->exists()) {
            Toastr::error(translate('you_are_not_authorized_to_view_this_vendor'));

            return redirect()->route('admin.branch.vendors');
        }

        $vendor->load([
            'products' => fn($query) => $query->when(
                !$isSuperAdmin,
                fn($productQuery) => $productQuery->where('branch_id', $managerBranchId)
            ),
        ]);

        // Calculate total products and stock
        $totalProducts = $vendor->products->count();
        $totalStock = $vendor->products->sum('current_stock'); // Assuming 'stock_quantity' field exists in products

        // Return view with vendor data
        return view('admin-views.branch-management.manage-branch.vendor-view', compact('vendor', 'totalProducts', 'totalStock'));
    }

    private function vendorsQuery(Request $request, bool $isSuperAdmin, int|string|null $managerBranchId): Builder
    {
        $searchValue = $this->sanitizeSearchTerm($request->input('searchValue'));

        return Seller::query()
            ->when(
                !$isSuperAdmin,
                fn(Builder $query) => $query->whereHas('branches', function (Builder $branchQuery) use ($managerBranchId) {
                    $branchQuery->where('id', $managerBranchId);
                })
            )
            ->when($searchValue !== '', function (Builder $query) use ($searchValue) {
                $query->where(function (Builder $innerQuery) use ($searchValue) {
                    $innerQuery
                        ->where('f_name', 'like', '%' . $searchValue . '%')
                        ->orWhere('l_name', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%')
                        ->orWhere('phone', 'like', '%' . $searchValue . '%');
                });
            })
            ->orderByDesc('id');
    }

    private function resolveListPerPage(Request $request): int
    {
        if ($request->filled('choose_first') && (int) $request->choose_first > 0) {
            return (int) $request->choose_first;
        }

        return (int) (getWebConfig('pagination_limit') ?? 10);
    }

    private function sanitizeSearchTerm(?string $value): string
    {
        return mb_substr(trim((string) $value), 0, 100);
    }
}
