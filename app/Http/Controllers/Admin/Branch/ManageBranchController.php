<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class ManageBranchController extends Controller
{

    public function vendors(Request $request)
    {
        $manager = auth('admin')->user();
        $managerBranchId = $manager?->branch_id;

        // Fetch the vendors associated with this branch
        $vendors = Seller::whereHas('branches', function($query) use ($managerBranchId) {
            $query->where('id', $managerBranchId); // id = the manager's branch_id
        });
        

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
        ->where('branch_id', $managerBranchId)
        ->select('id', 'name', 'current_stock', 'code', 'user_id')
        ->paginate(10);


        return view('admin-views.branch-management.manage-branch.vendors', compact('vendors', 'products'));
    }

     public function show($id)
    {
        // Fetch the vendor data with products and stock info
        $vendor = Seller::with(['products'])->findOrFail($id);

        // Calculate total products and stock
        $totalProducts = $vendor->products->count();
        $totalStock = $vendor->products->sum('current_stock'); // Assuming 'stock_quantity' field exists in products

        // Return view with vendor data
        return view('admin-views.branch-management.manage-branch.vendor-view', compact('vendor', 'totalProducts', 'totalStock'));
    }
}
