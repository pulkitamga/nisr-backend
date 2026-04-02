<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ProductInventoryController extends Controller
{

    public function productInventory(Request $request)
    {
        $products = $this->productInventoryQuery($request)
            ->paginate($this->resolveListPerPage($request))
            ->appends($request->query());

        return view('admin-views.branch-management.product-inventory.inventory', compact('products'));
    }

    public function exportProductInventory(Request $request): StreamedResponse
    {
        $products = $this->productInventoryQuery($request)->get();

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                translate('Name'),
                translate('Code'),
                translate('Category'),
                translate('Brand'),
                translate('Unit'),
                translate('Current Stock'),
                translate('Status'),
                translate('Request Status'),
            ]);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->getTranslatedField('name'),
                    $product->code,
                    $product->category?->getTranslatedField('name') ?? translate('not_available'),
                    $product->brand?->getTranslatedField('name') ?? translate('not_available'),
                    getUnitLabel($product->unit),
                    (string) $product->current_stock,
                    $product->status == 1 ? translate('active') : translate('inactive'),
                    $this->mapRequestStatusLabel((int) $product->request_status),
                ]);
            }

            fclose($handle);
        }, 'branch-product-inventory.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function productInventoryQuery(Request $request): Builder
    {
        $branchId = (int) (Auth::guard('admin')->user()?->branch_id ?? 0);
        $searchValue = $this->sanitizeSearchTerm($request->input('searchValue'));

        return Product::query()
            ->with(['branch', 'category.translations', 'subCategory.translations', 'subSubCategory.translations', 'brand.translations'])
            ->when($branchId > 0, fn(Builder $query) => $query->where('branch_id', $branchId))
            ->when($branchId <= 0, fn(Builder $query) => $query->whereRaw('1 = 0'))
            ->when($searchValue !== '', function (Builder $query) use ($searchValue) {
                $query->where(function (Builder $innerQuery) use ($searchValue) {
                    $innerQuery
                        ->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('code', 'like', "%{$searchValue}%");
                });
            })
            ->orderByDesc('id')
            ->select([
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

    private function mapRequestStatusLabel(int $requestStatus): string
    {
        return match ($requestStatus) {
            0 => translate('Pending'),
            1 => translate('Approved'),
            2 => translate('Denied'),
            default => translate('not_available'),
        };
    }
}
