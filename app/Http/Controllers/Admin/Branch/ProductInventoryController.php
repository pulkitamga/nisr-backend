<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Exports\FormattedTableExport;
use App\Http\Controllers\Controller;
use App\Support\LocalizedExport;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class ProductInventoryController extends Controller
{

    public function productInventory(Request $request)
    {
        $products = $this->productInventoryQuery($request)
            ->paginate($this->resolveListPerPage($request))
            ->appends($request->query());

        return view('admin-views.branch-management.product-inventory.inventory', compact('products'));
    }

    public function exportProductInventory(Request $request): BinaryFileResponse
    {
        $products = $this->productInventoryQuery($request)->get();
        $rows = $products->map(function ($product) {
            return [
                $product->getTranslatedField('name'),
                (string) $product->code,
                $product->category?->getTranslatedField('name') ?? translate('not_available'),
                $product->brand?->getTranslatedField('name') ?? translate('not_available'),
                getUnitLabel($product->unit),
                (int) $product->current_stock,
                $product->status == 1 ? translate('Active') : translate('Inactive'),
                $this->mapRequestStatusLabel((int) $product->request_status),
            ];
        })->values()->all();

        return Excel::download(
            new FormattedTableExport(
                rows: $rows,
                headings: [
                    translate('Name'),
                    translate('Code'),
                    translate('Category'),
                    translate('Brand'),
                    translate('Unit'),
                    translate('Current_Stock'),
                    translate('Status'),
                    translate('Request Status'),
                ],
                title: translate('branch_product_inventory'),
                locale: LocalizedExport::locale(),
                isRtl: LocalizedExport::isRtl(),
                metaPairs: [
                    ['label' => translate('exported_at'), 'value' => LocalizedExport::exportedAtLabel()],
                    ['label' => translate('count'), 'value' => (string) count($rows)],
                ],
                filterSummary: translate('Search') . ': ' . (trim((string) $request->input('searchValue', '')) ?: translate('All')),
                columnWidths: ['A' => 28, 'B' => 16, 'C' => 20, 'D' => 20, 'E' => 14, 'F' => 14, 'G' => 14, 'H' => 18],
                centerColumns: ['F', 'G', 'H'],
                sumColumns: ['F']
            ),
            LocalizedExport::fileName(translate('branch_product_inventory'))
        );
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
