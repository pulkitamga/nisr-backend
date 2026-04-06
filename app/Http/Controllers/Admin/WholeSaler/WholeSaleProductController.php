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
use InvalidArgumentException;

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
        $wholesale_products = $this->wholesaleproductrepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['price_list', 'product.translations', 'category.translations', 'subcategory.translations'],
            dataLimit: $this->resolveListPerPage($request)
        );

        return view(WholeSalesProducts::LIST[VIEW], compact('wholesale_products'));
    }

    public function getAddView(Request $request): View
    {
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]);

        $activeTiers = WholesaleTier::where('is_active', 1)
            ->with('translations')
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
        $product = Product::query()->findOrFail($dataArray['product_id']);
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

        try {
            $priceRanges = $service->buildValidatedPriceRanges(
                product: $product,
                variationType: $dataArray['variation_type'] ?? null,
                variationKey: $dataArray['variation_key'] ?? null,
                payload: $request->only(['tier', 'min_qty', 'max_qty', 'discount'])
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], 422);
        }

        $savedRequest = $this->wholesaleproductrepo->add(data: $dataArray);

        if ($priceRanges !== []) {
            $priceRanges = array_map(function (array $row) use ($savedRequest) {
                $row['wholesale_id'] = $savedRequest->id;

                return $row;
            }, $priceRanges);

            WholesaleProductPriceRange::insert($priceRanges);
        }

        return response()->json([
            'message' => translate('Product added to wholesale successfully!')
        ]);
    }

    public function getProductView(string|int $id): View
    {
        $ProductData = $this->wholesaleproductrepo->getFirstWhere(params: ['id' => $id], relations: ['price_list', 'product.translations', 'category.translations', 'subcategory.translations']);
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = [];
        return view(WholeSalesProducts::PRODUCT_VIEW[VIEW], compact('ProductData', 'categories', 'subCategory'));
    }

    public function getUpdateView(string|int $id): View
    {
        $ProductData = $this->wholesaleproductrepo->getFirstWhere(params: ['id' => $id], relations: ['price_list', 'product.translations', 'category.translations', 'subcategory.translations']);
        $get_sub_category = $ProductData->sub_category_id
            ? $this->categoryRepo->getFirstWhere(params: ['id' => $ProductData->sub_category_id], relations: ['translations'])
            : "";

        $get_product = $ProductData->product_id
            ? $this->productRepo->getFirstWhere(params: ['id' => $ProductData->product_id], relations: ['translations'])
            : "";

        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = [];

        $activeTiers = WholesaleTier::where('is_active', 1)
            ->with('translations')
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
        $validated = $request->validate([
            'tax' => 'nullable|numeric|min:0|max:100',
            'tier' => 'required|array|min:1',
            'min_qty' => 'required|array|min:1',
            'min_qty.*' => 'required|integer|min:1',
            'max_qty' => 'nullable|array',
            'max_qty.*' => 'nullable|integer',
            'discount' => 'nullable|array',
            'discount.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $wholesaleProduct = WholeSaleProducts::with('product')->findOrFail($request->primary_id);

        $wholesaleProduct->tax = app(WholeSaleProductsService::class)->normalizeTaxValue($validated['tax'] ?? 0);
        $wholesaleProduct->save();

        try {
            $priceRanges = app(WholeSaleProductsService::class)->buildValidatedPriceRanges(
                product: $wholesaleProduct->product,
                variationType: $wholesaleProduct->variation_type,
                variationKey: $wholesaleProduct->variation_key,
                payload: $request->only(['tier', 'min_qty', 'max_qty', 'discount'])
            );
        } catch (InvalidArgumentException $exception) {
            Toastr::error($exception->getMessage());

            return back()->withInput();
        }

        $price_range = WholesaleProductPriceRange::where('wholesale_id', $request->primary_id);

        if ($price_range->exists()) {
            $price_range->delete();
        }
        if ($priceRanges !== []) {
            $priceRanges = array_map(function (array $row) use ($request) {
                $row['wholesale_id'] = $request->primary_id;

                return $row;
            }, $priceRanges);

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
            relations: ['price_list', 'product.translations', 'category.translations', 'subcategory.translations'],
            dataLimit: 'all'
        );
        $data = [];
        $total_price_range_rows = 0;
        foreach ($wholesale_products_with_prices as $price) {
            $price_range = WholesaleProductPriceRange::where('wholesale_id', $price->id)->get();
            $total_price_range_rows += $price_range->count();

            $data[] = [
                'primary_id'        => $price->id,
                'product_name'      => $price->product?->name,
                'category_name'     => $price->category?->name,
                'sub_category_name' => $price->subcategory?->name,
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
            relations: ['price_list', 'product.translations', 'category.translations', 'subcategory.translations'],
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

    private function resolveListPerPage(Request $request): int
    {
        if ($request->filled('choose_first') && (int)$request->choose_first > 0) {
            return (int)$request->choose_first;
        }

        return (int)(getWebConfig(name: WebConfigKey::PAGINATION_LIMIT) ?? 10);
    }
}
