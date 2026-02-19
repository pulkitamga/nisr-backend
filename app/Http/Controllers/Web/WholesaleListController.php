<?php

namespace App\Http\Controllers\Web;

use App\Domain\Stock\Support\VariantMatcher;
use App\Models\Author;
use App\Models\BusinessSetting;
use App\Models\PublishingHouse;
use App\Utils\BrandManager;
use App\Utils\CategoryManager;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Utils\ProductManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use App\Models\Product;
use App\Contracts\Repositories\VehicleMakeRepositoryInterface;
use App\Contracts\Repositories\VehicleModelRepositoryInterface;
use App\Models\VehicleYear;
use Illuminate\Support\Facades\Log;


class WholesaleListController extends Controller
{

    public function __construct(
        private readonly VehicleModelRepositoryInterface             $vehicleModelRepo,
        private readonly VehicleMakeRepositoryInterface             $vehicleMakeRepo,
    ) {}


    public function wholesale_products(Request $request)
    {;
        $themeName = theme_root_path();

        return match ($themeName) {
            'default' => self::default_theme($request),
            'theme_aster' => self::theme_aster($request),
            'theme_fashion' => self::theme_fashion($request),
        };
    }

    public function default_theme(Request $request): View|JsonResponse|Redirector|RedirectResponse
    {
        $request->merge(['product_type' => 'physical']);

        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
        $activeBrands = BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();
        $makes = $this->vehicleMakeRepo->all();
        $models = $this->vehicleModelRepo->all();
        $years = VehicleYear::orderBy('year')->pluck('year');

        $data = self::getProductListRequestData(request: $request);
        $isWholesaler = auth('customer')->check() && auth('customer')->user()->wholesaler_status == 1;

        $hasFilter = $request->filled('make') || $request->filled('model') || $request->filled('year');

        if ($hasFilter) {

            $query = Product::query()
                ->where('product_type', 'physical')
                ->active();

            if ($request->filled('make')) {
                $query->whereJsonContains('match_makes', $request->make);
            }

            if ($request->filled('model')) {
                $query->whereJsonContains('match_models', $request->model);
            }

            if ($request->filled('year')) {
                $query->whereJsonContains('match_years', (string)$request->year);
            }

            if ($isWholesaler) {
                $query->whereHas('wholesaleProducts');
            }

            $products = $query->paginate(20)->appends($data);
        } else {

            $productListData = ProductManager::getProductListData(request: $request);
            if ($isWholesaler) {
                $productListData = $productListData->filter(function ($product) {
                    return $product->wholesaleProducts()->exists();
                });
            }

            $products = $productListData instanceof \Illuminate\Database\Eloquent\Builder
                ? $productListData->paginate(20)->appends($data)
                : (new \Illuminate\Pagination\LengthAwarePaginator(
                    $productListData->forPage(\request('page', 1), 20),
                    $productListData->count(),
                    20,
                    \request('page', 1),
                    ['path' => \request()->url(), 'query' => \request()->query()]
                ));
        }

        if ($isWholesaler) {
            $variantMatcher = new VariantMatcher();
            foreach ($products as $product) {
                $wholesale = $product->wholesaleProducts()->first();

                if (!$wholesale) {
                    $originalPrice = $product->unit_price;
                } else {

                    $variationType = trim((string)($wholesale->resolved_variation_type ?? ''));
                    if ($variationType === '') {
                        $originalPrice = $product->unit_price;
                    } else {
                        $variations = json_decode($product->variation ?? '[]', true);

                        $matchedPrice = null;
                        foreach ($variations as $var) {
                            if (
                                isset($var['price'])
                                && $variantMatcher->matches($var['type'] ?? null, $variationType)
                            ) {
                                $matchedPrice = (float)$var['price'];
                                break;
                            }
                        }

                        if ($matchedPrice !== null) {
                            $originalPrice = $matchedPrice;
                        } else {
                            $originalPrice = $product->unit_price;
                        }
                    }
                    $tier = $wholesale->price_list_for_user()->first();

                    if ($tier) {

                        $discountType = $product->discount_type;

                        if ($discountType === 'flat') {
                            $product->discount = $tier->discount > 0 ? round($originalPrice / 100) * $tier->discount : 0;
                        } elseif ($discountType === 'percent') {
                            $product->discount = $tier->discount;
                        } else {
                            $product->discount = 0;
                        }

                        $product->unit_price = $originalPrice;
                    } else {
                        $product->price = $originalPrice;
                        $product->discount = 0;
                    }
                }
            }
        }
        if ($request->ajax()) {
            return response()->json([
                'total_product' => $products->total(),
                'view' => view('web-views.products._ajax-products', compact('products'))->render()
            ], 200);
        }

        // NORMAL VIEW
        return view(VIEW_FILE_NAMES['wholesale_view_page'], [
            'products' => $products,
            'data' => $data,
            'activeBrands' => $activeBrands,
            'categories' => $categories,
            'makes' => $makes,
            'models' => $models,
            'years' => $years,
        ]);
    }



    public function theme_aster($request): View|JsonResponse|Redirector|RedirectResponse
    {
        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
        $activeBrands = BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();

        $data = self::getProductListRequestData(request: $request);
        if ($request['data_from'] == 'category' && $request['category_id']) {
            $data['brand_name'] = Category::find((int)$request['category_id'])->name;
        }
        if ($request['data_from'] == 'brand') {
            $brandData = Brand::active()->find((int)$request['brand_id']);
            if ($brandData) {
                $data['brand_name'] = $brandData->name;
            } else {
                if ($request->ajax()) {
                    return response()->json(['message' => translate('not_found')], 200);
                }
                Toastr::warning(translate('not_found'));
                return redirect('/');
            }
        }

        $productListData = ProductManager::getProductListData(request: $request);
        $ratings = self::getProductsRatingOneToFiveAsArray(productQuery: $productListData);
        $products = $productListData->paginate(20)->appends($data);
        $getProductIds = $products->pluck('id')->toArray();

        if ($request['ratings'] != null) {
            $products = $products->map(function ($product) use ($request) {
                $product->rating = $product->rating->pluck('average')[0];
                return $product;
            });
            $products = $products->where('rating', '>=', $request['ratings'])
                ->where('rating', '<', $request['ratings'] + 1)
                ->paginate(20)->appends($data);
        }

        if ($request->ajax()) {
            return response()->json([
                'total_product' => $products->total(),
                'view' => view(VIEW_FILE_NAMES['products__ajax_partials'], ['products' => $products, 'product_ids' => $getProductIds])->render(),
            ], 200);
        }

        return view(VIEW_FILE_NAMES['products_view_page'], [
            'products' => $products,
            'data' => $data,
            'ratings' => $ratings,
            'product_ids' => $getProductIds,
            'activeBrands' => $activeBrands,
            'categories' => $categories,
        ]);
    }

    public function theme_fashion(Request $request): View|JsonResponse|Redirector|RedirectResponse
    {
        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
        $activeBrands = BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();
        $banner = BusinessSetting::where(['type' => 'banner_product_list_page'])->whereJsonContains('value', ['status' => '1'])->first();
        $singlePageProductCount = 25;

        $data = self::getProductListRequestData(request: $request);
        if ($request['data_from'] == 'brand') {
            $brand_data = Brand::active()->find((int)$request['brand_id']);
            if (!$brand_data) {
                Toastr::warning(translate('not_found'));
                return redirect('/');
            }
        }

        $tagCategory = [];
        if ($request['data_from'] == 'category' && $request['category_id']) {
            $tagCategory = Category::where('id', $request['category_id'])->select('id', 'name')->get();
        }

        $tagPublishingHouse = [];
        if (($request->has('publishing_house_id')) && !empty($request['publishing_house_id'])) {
            $tagPublishingHouse = PublishingHouse::where('id', $request['publishing_house_id'])->select('id', 'name')->get();
        }

        $tagProductAuthors = [];
        if (($request->has('author_id')) && !empty($request['author_id'])) {
            $tagProductAuthors = Author::where('id', $request['author_id'])->select('id', 'name')->get();
        }

        $tagBrand = [];
        if ($request['data_from'] == 'brand') {
            $tagBrand = Brand::where('id', $request['brand_id'])->select('id', 'name')->get();
        }

        $productListData = ProductManager::getProductListData(request: $request);
        $products = $productListData->paginate($singlePageProductCount)->appends($data);
        $paginate_count = ceil(($products->total() / $singlePageProductCount));
        $getProductIds = $products->pluck('id')->toArray();

        if ($request['ratings'] != null) {
            $products = $products->map(function ($product) use ($request) {
                $product->rating = $product->rating->pluck('average')[0];
                return $product;
            });
            $products = $products->where('rating', '>=', $request['ratings'])
                ->where('rating', '<', $request['ratings'] + 1)
                ->paginate($singlePageProductCount)->appends($data);
        }

        $allProductsColorList = ProductManager::getProductsColorsArray();

        if ($request->ajax()) {
            return response()->json([
                'total_product' => $products->total(),
                'view' => view(VIEW_FILE_NAMES['products__ajax_partials'], [
                    'products' => $products,
                    'product_ids' => $getProductIds,
                    'paginate_count' => $paginate_count,
                    'singlePageProductCount' => $singlePageProductCount,
                ])->render(),
            ], 200);
        }

        return view(VIEW_FILE_NAMES['products_view_page'], [
            'products' => $products,
            'tag_category' => $tagCategory,
            'tagPublishingHouse' => $tagPublishingHouse,
            'tagProductAuthors' => $tagProductAuthors,
            'tag_brand' => $tagBrand,
            'activeBrands' => $activeBrands,
            'categories' => $categories,
            'allProductsColorList' => $allProductsColorList,
            'banner' => $banner,
            'product_ids' => $getProductIds,
            'paginate_count' => $paginate_count,
            'singlePageProductCount' => $singlePageProductCount,
            'data' => $data
        ]);
    }

    function getProductsRatingOneToFiveAsArray($productQuery): array
    {
        $rating_1 = 0;
        $rating_2 = 0;
        $rating_3 = 0;
        $rating_4 = 0;
        $rating_5 = 0;

        foreach ($productQuery as $rating) {
            if (isset($rating->rating[0]['average']) && ($rating->rating[0]['average'] > 0 && $rating->rating[0]['average'] < 2)) {
                $rating_1 += 1;
            } elseif (isset($rating->rating[0]['average']) && ($rating->rating[0]['average'] >= 2 && $rating->rating[0]['average'] < 3)) {
                $rating_2 += 1;
            } elseif (isset($rating->rating[0]['average']) && ($rating->rating[0]['average'] >= 3 && $rating->rating[0]['average'] < 4)) {
                $rating_3 += 1;
            } elseif (isset($rating->rating[0]['average']) && ($rating->rating[0]['average'] >= 4 && $rating->rating[0]['average'] < 5)) {
                $rating_4 += 1;
            } elseif (isset($rating->rating[0]['average']) && ($rating->rating[0]['average'] == 5)) {
                $rating_5 += 1;
            }
        }

        return [
            'rating_1' => $rating_1,
            'rating_2' => $rating_2,
            'rating_3' => $rating_3,
            'rating_4' => $rating_4,
            'rating_5' => $rating_5,
        ];
    }

    public static function getProductListRequestData($request): array
    {
        return [
            'id' => $request['id'],
            'name' => $request['name'],
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'data_from' => $request['data_from'],
            'offer_type' => $request['offer_type'],
            'sort_by' => $request['sort_by'],
            'page_no' => $request['page'],
            'min_price' => $request['min_price'],
            'max_price' => $request['max_price'],
            'product_type' => $request['product_type'],
            'shop_id' => $request['shop_id'],
            'author_id' => $request['author_id'],
            'publishing_house_id' => $request['publishing_house_id'],
            'search_category_value' => $request['search_category_value'],
            'product_name' => $request['product_name'],
            'page' => $request['page'] ?? 1,
        ];
    }
}
