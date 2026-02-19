<?php

namespace App\Http\Controllers\Admin\WholeSaler;

use App\Http\Requests\Admin\WholeSaleProductAddRequrest;
use App\Contracts\Repositories\WholesaleproductsRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Enums\ViewPaths\Admin\WholeSalesProducts;
use App\Http\Controllers\BaseController;
use App\Models\WholeSaleProducts;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Services\WholeSaleProductsService;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;
use Illuminate\Http\JsonResponse;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\WholesaleProductPriceRange;
use App\Models\WholesaleTier;
use App\Models\Product;
use App\Enums\WebConfigKey;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductListWithPriceRangeExport;
use Illuminate\Support\Facades\Log;

class WholeSaleProductController extends BaseController
{
    use PaginatorTrait;
    use CommonTrait;

    public function __construct(private readonly WholesaleproductsRepositoryInterface  $wholesaleproductrepo,  private readonly CategoryRepositoryInterface $categoryRepo,         private readonly ProductRepositoryInterface                 $productRepo,) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        $current_date = date('Y-m-d');
        $wholesale_products = $this->wholesaleproductrepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['price_list', 'product', 'category', 'subcategory'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );

        return view(WholeSalesProducts::LIST[VIEW], compact('wholesale_products'));
    }

    public function getAddView(Request $request): View
    {
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]);

        $activeTiers = WholesaleTier::where('is_active', 1)
            ->orderBy('rank')
            ->orderBy('id')
            ->get();
        $defaultTiers = $activeTiers->take(3); // First 3
        $remainingTiers = $activeTiers->slice(3)->values(); // ensure keys reset
        return view(WholeSalesProducts::ADD[VIEW], compact('categories', 'subCategory', 'defaultTiers', 'remainingTiers'));
    }
    public function add(WholeSaleProductAddRequrest $request, WholeSaleProductsService $service): JsonResponse
    {
        Log::info('the request is ', ['request' => $request->all()]);

        $dataArray = $service->getAddData(request: $request);
        $existsQuery = WholeSaleProducts::where([
            'product_id' => $dataArray['product_id'],
            'status'     => 0
        ]);

        $variationType = trim((string)($dataArray['variation_type'] ?? ''));
        $variationKey = trim((string)($dataArray['variation_key'] ?? ''));

        if ($variationType !== '' || $variationKey !== '') {
            $existsQuery->where(function ($query) use ($variationType, $variationKey) {
                if ($variationType !== '') {
                    $query->where('variation_type', $variationType);
                } else {
                    $query->where(function ($subQuery) {
                        $subQuery->whereNull('variation_type')->orWhere('variation_type', '');
                    });
                }

                if ($variationKey !== '') {
                    $query->where('variation_key', $variationKey);
                } else {
                    $query->where(function ($subQuery) {
                        $subQuery->whereNull('variation_key')->orWhere('variation_key', '');
                    });
                }
            });
        } else {
            $existsQuery->where(function ($query) {
                $query->whereNull('variation_type')->orWhere('variation_type', '');
            })->where(function ($query) {
                $query->whereNull('variation_key')->orWhere('variation_key', '');
            });
        }

        if ($existsQuery->exists()) {
            return response()->json([
                'error' => translate('This product with selected variation already exists in wholesale stock!')
            ], 422);
        }

        $savedRequest = $this->wholesaleproductrepo->add(data: $dataArray);

        if ($request->has('min_qty') && is_array($request->min_qty)) {
            $priceRanges = [];

            foreach ($request->min_qty as $index => $minQty) {
                $unitPrice = $request->unit_price[$index] ?? 0;
                $discountPercent = $request->discount[$index] ?? 0;
                $finalPrice = $unitPrice - (($unitPrice * $discountPercent) / 100);

                $priceRanges[] = [
                    'wholesale_id'     => $savedRequest->id,
                    'tier'             => $request->tier[$index] ?? null,
                    'min_qty'          => $minQty,
                    'max_qty'          => $request->max_qty[$index] ?? null,
                    'price_per_piece'  => round($finalPrice, 2),
                    'discount'         => $discountPercent,
                    'status'           => 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            usort($priceRanges, function ($a, $b) {
                return $a['min_qty'] <=> $b['min_qty'];
            });
            WholesaleProductPriceRange::insert($priceRanges);
        }

        return response()->json([
            'message' => translate('Product added to wholesale successfully!')
        ]);
    }

    public function getProductView(string|int $id): View
    {
        $ProductData = $this->wholesaleproductrepo->getFirstWhere(params: ['id' => $id], relations: ['price_list', 'product', 'category', 'subcategory']);
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = [];
        return view(WholeSalesProducts::PRODUCT_VIEW[VIEW], compact('ProductData', 'categories', 'subCategory'));
    }

    public function getUpdateView(string|int $id): View
    {
        $ProductData = $this->wholesaleproductrepo->getFirstWhere(params: ['id' => $id], relations: ['price_list', 'product', 'category', 'subcategory']);
        $get_sub_category = $ProductData->sub_category_id
            ? $this->categoryRepo->getFirstWhere(params: ['id' => $ProductData->sub_category_id])
            : "";

        $get_product = $ProductData->product_id
            ? $this->productRepo->getFirstWhere(params: ['id' => $ProductData->product_id])
            : "";

        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = [];

        $activeTiers = WholesaleTier::where('is_active', 1)
            ->orderBy('rank')
            ->orderBy('id')
            ->get();
        $usedTierNames = collect($ProductData->price_list)->pluck('tier')->filter()->unique();
        $defaultTiers = $activeTiers->filter(function ($tier) use ($usedTierNames) {
            return $usedTierNames->contains($tier->name);
        })->values();

        $remainingTiers = $activeTiers->filter(function ($tier) use ($usedTierNames) {
            return !$usedTierNames->contains($tier->name);
        })->values();

        $tierRankByName = WholesaleTier::withTrashed()
            ->pluck('rank', 'name')
            ->mapWithKeys(fn($rank, $name) => [mb_strtolower((string)$name) => (int)$rank]);

        $sortedPriceList = collect($ProductData->price_list)
            ->sortBy(function ($price) use ($tierRankByName) {
                return $tierRankByName[mb_strtolower((string)$price->tier)] ?? PHP_INT_MAX;
            })
            ->values();

        return view(WholeSalesProducts::UPDATE_VIEW[VIEW], compact(
            'ProductData',
            'categories',
            'subCategory',
            'get_sub_category',
            'get_product',
            'defaultTiers',
            'remainingTiers',
            'sortedPriceList'
        ));
    }


    public function update(Request $request, $id)
    {

        $price_range = WholesaleProductPriceRange::where('wholesale_id', $request->primary_id);

        if ($price_range->exists()) {
            $price_range->delete();
        }
        if ($request->has('min_qty') && is_array($request->min_qty)) {
            $priceRanges = [];
            foreach ($request->min_qty as $index => $minQty) {
                $priceRanges[] = [
                    'wholesale_id'     => $request->primary_id,
                    'tier'             => $request->tier[$index] ?? null,
                    'min_qty'          => $minQty,
                    'max_qty'          => $request->max_qty[$index] ?? 0,
                    'price_per_piece'  => $request->final_price[$index] ?? 0,
                    'discount'         => $request->discount[$index] ?? 0,
                    'status'           => 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            WholesaleProductPriceRange::insert($priceRanges);
        }

        Toastr::success(translate('Prices Update successfully!'));

        return back();
    }

    public function exportProductWithPrices(Request $request): BinaryFileResponse
    {
        $wholesale_products_with_prices = $this->wholesaleproductrepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['price_list', 'product', 'category', 'subcategory'],
            dataLimit: 'all'
        );
        $data = [];
        $total_price_range_rows = 0;
        foreach ($wholesale_products_with_prices as $price) {
            $primary_id = $price->id;
            $product_id = $price->product_id;
            $category_id = $price->category_id;
            $sub_category_id = $price->sub_category_id;
            $attribute_id  = $price->attribute_id;

            if ($category_id) {
                $get_category = $this->categoryRepo->getFirstWhere(params: ['id' => $category_id]);
            }
            if ($sub_category_id) {
                $get_sub_category = $this->categoryRepo->getFirstWhere(params: ['id' => $sub_category_id]);
            }
            if ($product_id) {
                $get_product = $this->productRepo->getFirstWhere(params: ['id' => $product_id]);
            }
            $price_range = WholesaleProductPriceRange::where('wholesale_id', $price->id)->get();
            $total_price_range_rows += $price_range->count();

            // product details with price range 
            $data[] = [
                'primary_id'        => $price->id,
                'product_name'      => $get_product->name,
                'category_name'     => $get_category->name,
                'sub_category_name' => $get_sub_category->name,
                'attribute_id'      => $price->attribute_id,
                'price_ranges'      => $price_range->toArray(),
            ];
        }
        $data[] = [
            'total_rows' => $total_price_range_rows,
        ];
        return Excel::download(new ProductListWithPriceRangeExport($data), 'product-list.xlsx');
    }

    public function getWholesalerBusinessRequests(Request $request): view
    {
        $current_date = date('Y-m-d');
        $wholesale_products = $this->wholesaleproductrepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['price_list', 'product', 'category', 'subcategory'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );

        return view(WholeSalesProducts::LIST[VIEW], compact('wholesale_products'));
    }

    public function destroy($id)
    {
        $product = WholeSaleProducts::findOrFail($id);
        $product->delete();
        Toastr::success(translate('Product deleted successfully!'));
        return back();
    }


    public function toggleStatusProduct($id)
    {

        $product = WholeSaleProducts::findOrFail($id);
        $product->status = !$product->status;
        $product->save();

        return response()->json(['message' => 'status updated successfully.']);
    }

    public function getVariationsWithPrice($id)
    {
        $product = Product::findOrFail($id);

        // Step 1: Get choice_options → title mapping
        $choiceOptions = json_decode($product->choice_options, true) ?? [];
        $titleMap = []; // attrId => title (lowercase)

        foreach ($choiceOptions as $choice) {
            $attrId = $choice['name']; // "choice_1", "choice_2"
            $attrId = str_replace('choice_', '', $attrId); // "1", "2", "3"
            $title = strtolower($choice['title'] ?? 'attr' . $attrId);
            $options = $choice['options'] ?? [];

            // Map each option value to its ID
            foreach ($options as $optionValue) {
                $titleMap[$optionValue] = $title; // e.g. "10" => "water", "20" => "test"
            }
        }

        // Step 2: Process variations
        $variations = json_decode($product->variation, true) ?? [];
        $formattedVariations = [];

        foreach ($variations as $v) {
            $type = $v['type'] ?? ''; // "10-20-30"
            $parts = explode('-', $type); // ["10", "20", "30"]

            $keyParts = [];

            foreach ($parts as $partIndex => $part) {
                $title = $titleMap[$part] ?? ('option_' . ($partIndex + 1));
                $keyParts[] = "{$title}:{$part}";
            }

            $variationKey = !empty($keyParts) ? implode(' | ', $keyParts) : null;

            $formattedVariations[] = [
                'type' => $type,
                'variation_key' => $variationKey,
                'price' => $v['price'] ?? $product->unit_price,
            ];
        }

        return response()->json([
            'variations' => $formattedVariations,
            'unit_price' => $product->unit_price,
        ]);
    }
}
