<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\Product;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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

        $vendors = Seller::query()
            ->when(
                !$isSuperAdmin,
                fn($query) => $query->whereHas('branches', function ($branchQuery) use ($managerBranchId) {
                    $branchQuery->where('id', $managerBranchId);
                })
            );
        

        // Apply filters for vendors by name, email, or mobile
        if ($request->has('searchValue')) {
            $vendors = $vendors->where(function ($query) use ($request) {
                $query->where('f_name', 'like', '%' . $request->searchValue . '%')
                    ->orWhere('l_name', 'like', '%' . $request->searchValue . '%')
                    ->orWhere('email', 'like', '%' . $request->searchValue . '%')
                    ->orWhere('phone', 'like', '%' . $request->searchValue . '%');
            });
        }

        // Get the filtered vendors
        $vendors = $vendors->get();

        // Now fetch the products of these vendors in the manager's branch
        $products = Product::with(['seller:id,f_name,l_name'])
        ->when(
            !$isSuperAdmin,
            fn($query) => $query->where('branch_id', $managerBranchId)
        )
        ->select('id', 'name', 'current_stock', 'code', 'user_id')
        ->paginate(10);


        return view('admin-views.branch-management.manage-branch.vendors', compact('vendors', 'products'));
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
}
