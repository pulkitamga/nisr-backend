<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderDetail;


class ProductInventoryController extends Controller
{

    public function productInventory()
    {
        $branchId = Auth::guard('admin')->user()->branch_id;

        $products = Product::with(['branch', 'category', 'subCategory', 'subSubCategory', 'brand'])
            ->where('branch_id', $branchId)
            ->get([
                'id',
                'added_by',
                'name',
                'code',
                'branch_id',
                'category_id',
                'sub_category_id',
                'sub_sub_category_id',
                'brand_id',
                'unit',
                'product_type',
                'details',
                'unit_price',
                'purchase_price',
                'tax',
                'tax_type',
                'tax_model',
                'discount',
                'current_stock',
                'minimum_order_qty',
                'status',
                'request_status',
                'shipping_cost',
                'images'
            ]);

        return view('admin-views.branch-management.product-inventory.inventory', compact('products'));
    }


    public function totelSale(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $branch_id = $admin->branch_id;

        // Filtered Orders for current branch
        $orders = Order::with(['details.product', 'customer'])
            ->where('pickup_from_branch', $branch_id)
            ->latest()
            ->paginate(20);

        return view('admin-views.branch-management.product-inventory.sales-track', compact('orders'));
    }
}
