<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\ProductCompareRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductTagRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\SellerRepositoryInterface;
use App\Contracts\Repositories\TagRepositoryInterface;
use App\Contracts\Repositories\ServiceRequestRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Repositories\DealOfTheDayRepository;
use App\Repositories\WishlistRepository;
use App\Services\ProductService;
use App\Traits\ProductTrait;
use App\Traits\CommonTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Models\VehicleMake;
use App\Models\VehicleYear;
use App\Models\InboxMessage;
use App\Http\Requests\ServiceRequestFormRequest;


class ServiceDetailsController extends Controller
{
    use ProductTrait;
    use CommonTrait;

    public function __construct(
        private readonly ProductRepositoryInterface        $productRepo,
        private readonly WishlistRepository                $wishlistRepo,
        private readonly ReviewRepositoryInterface         $reviewRepo,
        private readonly OrderDetailRepositoryInterface    $orderDetailRepo,
        private readonly DealOfTheDayRepository            $dealOfTheDayRepo,
        private readonly ProductCompareRepositoryInterface $compareRepo,
        private readonly ProductTagRepositoryInterface     $productTagRepo,
        private readonly TagRepositoryInterface            $tagRepo,
        private readonly SellerRepositoryInterface         $sellerRepo,
        private readonly ProductService                    $productService,
        private readonly ServiceRequestRepositoryInterface $serviceRequestRepo

    ) {}

    /**
     * @param string $slug
     * @return View|RedirectResponse
     */
    public function index(string $slug): View|RedirectResponse
    {
        $theme_name = theme_root_path();

        return match ($theme_name) {
            'default' => self::getDefaultTheme(slug: $slug),
            'theme_aster' => self::getThemeAster(slug: $slug),
            'theme_fashion' => self::getThemeFashion(slug: $slug),
        };
    }

    public function getDefaultTheme(string $slug): View|RedirectResponse
    {
        $product = $this->productRepo->getWebFirstWhereActive(
            params: ['slug' => $slug, 'customer_id' => Auth::guard('customer')->user()->id ?? 0],
            relations: [
                'reviews' => 'reviews',
                'service' => 'service',
                'digitalVariation' => 'digitalVariation',
                'seller.shop' => 'seller.shop',
                'digitalProductAuthors' => 'digitalProductAuthors',
                'digitalProductPublishingHouse' => 'digitalProductPublishingHouse',
                'clearanceSale' => 'clearanceSale',
                'translations' => 'translations'
            ]
        );
        $servicePolicy = getWebConfig(name: 'service_policy');


        if ($product) {
            $productDetailsMeta = $product?->seoInfo;
            $productAuthorsInfo = $this->productService->getProductAuthorsInfo(product: $product);
            $productPublishingHouseInfo = $this->productService->getProductPublishingHouseInfo(product: $product);

            $overallRating = getOverallRating(reviews: $product->reviews);
            $wishlistStatus = $this->wishlistRepo->getListWhereCount(filters: ['product_id' => $product['id'], 'customer_id' => auth('customer')->id()]);
            $productReviews = $this->reviewRepo->getListWhere(
                orderBy: ['id' => 'desc'],
                filters: ['product_id' => $product['id']],
                relations: ['reply'],
                dataLimit: 2,
                offset: 1
            );

            $firstVariationQuantity = $product['current_stock'];
            if (count(json_decode($product['variation'], true)) > 0) {
                $firstVariationQuantity = json_decode($product['variation'], true)[0]['qty'];
            }
            $firstVariationQuantity = $product['product_type'] == 'physical' ? $firstVariationQuantity : 999;

            $rating = getRating(reviews: $product->reviews);
            $decimalPointSettings = getWebConfig('decimal_point_settings');
            $moreProductFromSeller = $this->productRepo->getWebListWithScope(
                orderBy: ['id' => 'desc'],
                scope: 'active',
                filters: ['product_type' => 'services'],
                whereNotIn: ['id' => [$product['id']]],
                dataLimit: 5,
                offset: 1
            );


            if ($product['added_by'] == 'seller') {
                $productsForReview = $this->productRepo->getWebListWithScope(
                    scope: 'active',
                    filters: ['added_by' => $product['added_by'], 'seller_id' => $product['user_id']],
                    withCount: ['reviews' => 'reviews']
                );
            } else {
                $productsForReview = $this->productRepo->getWebListWithScope(
                    scope: 'active',
                    filters: ['added_by' => 'in_house', 'seller_id' => $product['user_id']],
                    withCount: ['reviews' => 'reviews']
                );
            }

            $totalReviews = 0;
            foreach ($productsForReview as $item) {
                $totalReviews += $item->reviews_count;
            }
            $countOrder = $this->orderDetailRepo->getListWhereCount(filters: ['product_id' => $product['id']]);
            $countWishlist = $this->wishlistRepo->getListWhereCount(filters: ['product_id' => $product['id']]);
            $relatedProducts = $this->productRepo->getWebListWithScope(
                scope: 'active',
                filters: ['category_id' => $product['category_id']],
                whereNotIn: ['id' => [$product['id']]],
                relations: ['reviews' => 'reviews'],
                dataLimit: 12,
                offset: 1
            );
            $dealOfTheDay = $this->dealOfTheDayRepo->getFirstWhere(['product_id' => $product['id'], 'status' => 1]);
            $currentDate = date('Y-m-d');
            $sellerVacationStartDate = ($product['added_by'] == 'seller' && isset($product->seller->shop->vacation_start_date)) ? date('Y-m-d', strtotime($product->seller->shop->vacation_start_date)) : null;
            $sellerVacationEndDate = ($product['added_by'] == 'seller' && isset($product->seller->shop->vacation_end_date)) ? date('Y-m-d', strtotime($product->seller->shop->vacation_end_date)) : null;
            $sellerTemporaryClose = ($product['added_by'] == 'seller' && isset($product->seller->shop->temporary_close)) ? $product->seller->shop->temporary_close : false;

            $temporaryClose = getWebConfig('temporary_close');
            $inHouseVacation = getWebConfig('vacation_add');
            $inHouseVacationStartDate = $product['added_by'] == 'admin' ? $inHouseVacation['vacation_start_date'] : null;
            $inHouseVacationEndDate = $product['added_by'] == 'admin' ? $inHouseVacation['vacation_end_date'] : null;
            $inHouseVacationStatus = $product['added_by'] == 'admin' ? $inHouseVacation['status'] : false;
            $inHouseTemporaryClose = $product['added_by'] == 'admin' ? $temporaryClose['status'] : false;
            $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');
            $countries = $country_restrict_status ? $this->get_delivery_country_array() : COUNTRIES;

            $countriesName = [];
            $countriesCode = [];
            foreach ($countries as $country) {
                $countriesName[] = $country['name'];
                $countriesCode[] = $country['code'];
            }

            $previewFileInfo = getFileInfoFromURL(url: $product?->preview_file_full_url['path']);
            $makes = VehicleMake::with('models')->orderBy('name')->get();
            $years = VehicleYear::orderBy('year', 'desc')->get();

            return view(VIEW_FILE_NAMES['services_details'], compact(
                'product',
                'countWishlist',
                'countOrder',
                'relatedProducts',
                'dealOfTheDay',
                'currentDate',
                'sellerVacationStartDate',
                'sellerVacationEndDate',
                'sellerTemporaryClose',
                'inHouseVacationStartDate',
                'inHouseVacationEndDate',
                'inHouseVacationStatus',
                'inHouseTemporaryClose',
                'overallRating',
                'wishlistStatus',
                'productReviews',
                'rating',
                'totalReviews',
                'productsForReview',
                'moreProductFromSeller',
                'decimalPointSettings',
                'previewFileInfo',
                'productAuthorsInfo',
                'productPublishingHouseInfo',
                'firstVariationQuantity',
                'productDetailsMeta',
                'makes',
                'years',
                'country_restrict_status',
                'countries',
                'countriesName',
                'countriesCode',
                'servicePolicy'
            ));
        }

        Toastr::error(translate('not_found'));
        return back();
    }

    public function storeServiceRequest(ServiceRequestFormRequest $request)
    {
        $validated = $request->validated();

        $serviceRequest = $this->serviceRequestRepo->create($validated);

        InboxMessage::create([
            'subject'       => 'New Service Request For - ' . $serviceRequest->service->title,
            'body'          => 'A new service request has been submitted.',
            'contact_id'   => $serviceRequest->customer_id ?? null,
            'sender_name'   => $serviceRequest->customer->name ?? 'N/A',
            'sender_email'  => $serviceRequest->customer->email ?? null,
            'sender_phone'  => $serviceRequest->customer->phone ?? null,
            'pipeline'       => 'form',
            'message_type'  => 'service',
            'status'        => 'new',
            'details'       => [
                'service_id'     => $serviceRequest->service->service_id,
                'service_option' => $serviceRequest->service_option,
                'country'        => $serviceRequest->country,
                'state'          => $serviceRequest->state,
                'city'           => $serviceRequest->city,
                'area'           => $serviceRequest->area,
                'address'        => $serviceRequest->address,
                'latitude'       => $serviceRequest->latitude,
                'longitude'      => $serviceRequest->longitude,
                'vehicle_type'   => $serviceRequest->vehicle_type,
                'vehicle_make'   => $serviceRequest->vehicle_make,
                'vehicle_model'  => $serviceRequest->vehicle_model,
                'vehicle_year'   => $serviceRequest->vehicle_year,
                'vehicle_mileage' => $serviceRequest->vehicle_mileage,
                'vin'            => $serviceRequest->vin,
            ]
        ]);

        Toastr::success(translate('Service request created successfully.'));

        return redirect()->back();
    }


    public function getThemeAster(string $slug): View|RedirectResponse
    {
        $product = $this->productRepo->getWebFirstWhereActive(
            params: ['slug' => $slug, 'customer_id' => Auth::guard('customer')->user()->id ?? 0],
            relations: ['seoInfo', 'digitalVariation', 'reviews' => 'reviews', 'seller.shop' => 'seller.shop', 'wishList' => 'wishList', 'compareList' => 'compareList', 'digitalProductAuthors.author', 'digitalProductPublishingHouse.publishingHouse', 'clearanceSale' => 'clearanceSale'],
            withCount: ['orderDetails' => 'orderDetails', 'wishList' => 'wishList']
        );

        if ($product) {
            $productDetailsMeta = $product?->seoInfo;
            $productAuthorsInfo = $this->productService->getProductAuthorsInfo(product: $product);
            $productPublishingHouseInfo = $this->productService->getProductPublishingHouseInfo(product: $product);
            $currentDate = date('Y-m-d H:i:s');

            $countOrder = $product['order_details_count'];
            $countWishlist = $product['wish_list_count'];
            $wishlistStatus = $this->wishlistRepo->getCount(params: ['product_id' => $product->id, 'customer_id' => auth('customer')->id()]);
            $compareList = $this->compareRepo->getCount(params: ['product_id' => $product->id, 'customer_id' => auth('customer')->id()]);

            $relatedProducts = $this->productRepo->getWebListWithScope(
                scope: 'active',
                filters: ['category_ids' => $product['category_ids'], 'customer_id' => Auth::guard('customer')->user()->id ?? 0],
                whereNotIn: ['id' => [$product['id']]],
                relations: ['reviews' => 'reviews', 'flashDealProducts.flashDeal' => 'flashDealProducts.flashDeal', 'wishList' => 'wishList', 'compareList' => 'compareList'],
                withCount: ['reviews' => 'reviews'],
                dataLimit: 12,
                offset: 1
            );
            $relatedProducts?->map(function ($product) use ($currentDate) {
                $flash_deal_status = 0;
                $flash_deal_end_date = 0;
                if (count($product->flashDealProducts) > 0) {
                    $flash_deal = $product->flashDealProducts[0]->flashDeal;
                    if ($flash_deal) {
                        $start_date = date('Y-m-d H:i:s', strtotime($flash_deal->start_date));
                        $end_date = date('Y-m-d H:i:s', strtotime($flash_deal->end_date));
                        $flash_deal_status = $flash_deal->status == 1 && (($currentDate >= $start_date) && ($currentDate <= $end_date)) ? 1 : 0;
                        $flash_deal_end_date = $flash_deal->end_date;
                    }
                }
                $product['flash_deal_status'] = $flash_deal_status;
                $product['flash_deal_end_date'] = $flash_deal_end_date;
                return $product;
            });

            $dealOfTheDay = $this->dealOfTheDayRepo->getFirstWhere(['product_id' => $product['id'], 'status' => 1]);
            $currentDate = date('Y-m-d');
            $sellerVacationStartDate = ($product['added_by'] == 'seller' && isset($product->seller->shop->vacation_start_date)) ? date('Y-m-d', strtotime($product->seller->shop->vacation_start_date)) : null;
            $sellerVacationEndDate = ($product['added_by'] == 'seller' && isset($product->seller->shop->vacation_end_date)) ? date('Y-m-d', strtotime($product->seller->shop->vacation_end_date)) : null;
            $sellerTemporaryClose = ($product['added_by'] == 'seller' && isset($product->seller->shop->temporary_close)) ? $product->seller->shop->temporary_close : false;

            $temporaryClose = getWebConfig('temporary_close');
            $inHouseVacation = getWebConfig('vacation_add');
            $inHouseVacationStartDate = $product['added_by'] == 'admin' ? $inHouseVacation['vacation_start_date'] : null;
            $inHouseVacationEndDate = $product['added_by'] == 'admin' ? $inHouseVacation['vacation_end_date'] : null;
            $inHouseVacationStatus = $product['added_by'] == 'admin' ? $inHouseVacation['status'] : false;
            $inHouseTemporaryClose = $product['added_by'] == 'admin' ? $temporaryClose['status'] : false;

            $overallRating = getOverallRating($product['reviews']);

            $rating = getRating($product->reviews);
            $productReviews = $this->reviewRepo->getListWhere(
                orderBy: ['id' => 'desc'],
                filters: ['product_id' => $product['id']],
                relations: ['reply'],
                dataLimit: 2,
                offset: 1
            );

            $firstVariationQuantity = $product['current_stock'];
            if (count(json_decode($product['variation'], true)) > 0) {
                $firstVariationQuantity = json_decode($product['variation'], true)[0]['qty'];
            }
            $firstVariationQuantity = $product['product_type'] == 'physical' ? $firstVariationQuantity : 999;

            $decimalPointSettings = getWebConfig('decimal_point_settings');
            $moreProductFromSeller = $this->productRepo->getWebListWithScope(
                orderBy: ['id' => 'desc'],
                scope: 'active',
                filters: ['added_by' => $product['added_by'] == 'admin' ? 'in_house' : $product['added_by'], 'seller_id' => $product['user_id']],
                whereNotIn: ['id' => [$product['id']]],
                dataLimit: 5,
                offset: 1
            );

            if ($product['added_by'] == 'seller') {
                $productsForReview = $this->productRepo->getWebListWithScope(
                    scope: 'active',
                    filters: ['added_by' => $product['added_by'], 'seller_id' => $product['user_id']],
                    withCount: ['reviews' => 'reviews']
                );
            } else {
                $productsForReview = $this->productRepo->getWebListWithScope(
                    scope: 'active',
                    filters: ['added_by' => 'in_house', 'seller_id' => $product['user_id']],
                    withCount: ['reviews' => 'reviews']
                );
            }

            $totalReviews = 0;
            foreach ($productsForReview as $item) {
                $totalReviews += $item->reviews_count;
            }

            $productIds = Product::active()->where(['added_by' => $product['added_by']])
                ->where('user_id', $product['user_id'])->pluck('id')->toArray();
            $vendorReviewData = Review::active()->whereIn('product_id', $productIds);
            $ratingCount = $vendorReviewData->count();
            $avgRating = $vendorReviewData->avg('rating');

            $vendorRattingStatusPositive = 0;
            foreach ($vendorReviewData->pluck('rating') as $singleRating) {
                ($singleRating >= 4 ? ($vendorRattingStatusPositive++) : '');
            }

            $positiveReview = $ratingCount != 0 ? ($vendorRattingStatusPositive * 100) / $ratingCount : 0;
            $previewFileInfo = getFileInfoFromURL(url: $product?->preview_file_full_url['path']);

            return view(VIEW_FILE_NAMES['products_details'], compact(
                'product',
                'wishlistStatus',
                'countWishlist',
                'countOrder',
                'relatedProducts',
                'dealOfTheDay',
                'currentDate',
                'sellerVacationStartDate',
                'sellerVacationEndDate',
                'sellerTemporaryClose',
                'inHouseVacationStartDate',
                'inHouseVacationEndDate',
                'inHouseVacationStatus',
                'inHouseTemporaryClose',
                'overallRating',
                'decimalPointSettings',
                'moreProductFromSeller',
                'productsForReview',
                'totalReviews',
                'rating',
                'productReviews',
                'avgRating',
                'compareList',
                'positiveReview',
                'previewFileInfo',
                'productAuthorsInfo',
                'productPublishingHouseInfo',
                'firstVariationQuantity',
                'productDetailsMeta'
            ));
        }

        Toastr::error(translate('not_found'));
        return back();
    }

    public function getThemeFashion($slug): View|RedirectResponse
    {
        $product = $this->productRepo->getWebFirstWhereActive(
            params: ['slug' => $slug, 'customer_id' => Auth::guard('customer')->user()->id ?? 0],
            relations: ['seoInfo', 'digitalVariation', 'reviews' => 'reviews', 'seller.shop' => 'seller.shop', 'wishList' => 'wishList', 'compareList' => 'compareList', 'digitalProductAuthors' => 'digitalProductAuthors', 'digitalProductPublishingHouse' => 'digitalProductPublishingHouse', 'clearanceSale' => 'clearanceSale'],
            withCount: ['orderDetails' => 'orderDetails', 'wishList' => 'wishList']
        );

        if ($product != null) {
            $productDetailsMeta = $product?->seoInfo;
            $productAuthorsInfo = $this->productService->getProductAuthorsInfo(product: $product);
            $productPublishingHouseInfo = $this->productService->getProductPublishingHouseInfo(product: $product);
            $tags = $this->productTagRepo->getIds(fieldName: 'tag_id', filters: ['product_id' => $product['id']]);
            $this->tagRepo->incrementVisitCount(whereIn: ['id' => $tags]);

            $currentDate = date('Y-m-d H:i:s');
            $countWishlist = $product['wish_list_count'];
            $wishlistStatus = $this->wishlistRepo->getCount(params: ['product_id' => $product->id, 'customer_id' => auth('customer')->id()]);
            $compareList = $this->compareRepo->getCount(params: ['product_id' => $product->id, 'customer_id' => auth('customer')->id()]);
            $relatedProducts = $this->productRepo->getWebListWithScope(
                scope: 'active',
                filters: ['category_id' => $product['category_id'], 'customer_id' => Auth::guard('customer')->user()->id ?? 0],
                whereNotIn: ['id' => [$product['id']]],
                relations: ['reviews' => 'reviews', 'flashDealProducts.flashDeal' => 'flashDealProducts.flashDeal', 'wishList' => 'wishList', 'compareList' => 'compareList'],
                dataLimit: 'all',
            )->count();
            $sellerVacationStartDate = ($product['added_by'] == 'seller' && isset($product->seller->shop->vacation_start_date)) ? date('Y-m-d', strtotime($product->seller->shop->vacation_start_date)) : null;
            $sellerVacationEndDate = ($product['added_by'] == 'seller' && isset($product->seller->shop->vacation_end_date)) ? date('Y-m-d', strtotime($product->seller->shop->vacation_end_date)) : null;
            $sellerTemporaryClose = ($product['added_by'] == 'seller' && isset($product->seller->shop->temporary_close)) ? $product->seller->shop->temporary_close : false;

            $temporaryClose = getWebConfig(name: 'temporary_close');
            $inHouseVacation = getWebConfig(name: 'vacation_add');
            $inHouseVacationStartDate = $product['added_by'] == 'admin' ? $inHouseVacation['vacation_start_date'] : null;
            $inHouseVacationEndDate = $product['added_by'] == 'admin' ? $inHouseVacation['vacation_end_date'] : null;
            $inHouseVacationStatus = $product['added_by'] == 'admin' ? $inHouseVacation['status'] : false;
            $inHouseTemporaryClose = $product['added_by'] == 'admin' ? $temporaryClose['status'] : false;

            $overallRating = getOverallRating($product['reviews']);
            $productReviewsCount = $product->reviews->count();

            $rattingStatusPositive = $productReviewsCount != 0 ? ($product->reviews->where('rating', '>=', 4)->count() * 100) / $productReviewsCount : 0;
            $rattingStatusGood = $productReviewsCount != 0 ? ($product->reviews->where('rating', 3)->count() * 100) / $productReviewsCount : 0;
            $rattingStatusNeutral = $productReviewsCount != 0 ? ($product->reviews->where('rating', 2)->count() * 100) / $productReviewsCount : 0;
            $rattingStatusNegative = $productReviewsCount != 0 ? ($product->reviews->where('rating', '=', 1)->count() * 100) / $productReviewsCount : 0;
            $rattingStatus = [
                'positive' => $rattingStatusPositive,
                'good' => $rattingStatusGood,
                'neutral' => $rattingStatusNeutral,
                'negative' => $rattingStatusNegative,
            ];

            $rating = getRating($product->reviews);
            $productReviews = $this->reviewRepo->getListWhere(
                orderBy: ['id' => 'desc'],
                filters: ['product_id' => $product['id']],
                relations: ['reply'],
                dataLimit: 2,
                offset: 1
            );

            $firstVariationQuantity = $product['current_stock'];
            if (count(json_decode($product['variation'], true)) > 0) {
                $firstVariationQuantity = json_decode($product['variation'], true)[0]['qty'];
            }
            $firstVariationQuantity = $product['product_type'] == 'physical' ? $firstVariationQuantity : 999;

            $decimalPointSettings = getWebConfig('decimal_point_settings');
            $moreProductFromSeller = $this->productRepo->getWebListWithScope(
                orderBy: ['id' => 'desc'],
                scope: 'active',
                filters: ['added_by' => $product['added_by'] == 'admin' ? 'in_house' : $product['added_by'], 'seller_id' => $product['user_id'], 'customer_id' => Auth::guard('customer')->user()->id ?? 0],
                whereNotIn: ['id' => [$product['id']]],
                relations: ['wishList' => 'wishList'],
                dataLimit: 5,
                offset: 1
            );
            if ($product['added_by'] == 'seller') {
                $productsForReview = $this->productRepo->getWebListWithScope(
                    scope: 'active',
                    filters: ['added_by' => $product['added_by'], 'seller_id' => $product['user_id']],
                    withCount: ['reviews' => 'reviews']
                );
                $productsCount = $this->productRepo->getWebListWithScope(
                    scope: 'active',
                    filters: ['added_by' => $product['added_by'], 'seller_id' => $product['user_id']],
                    dataLimit: 'all'
                )->count();
            } else {
                $productsForReview = $this->productRepo->getWebListWithScope(
                    scope: 'active',
                    filters: ['added_by' => 'in_house', 'seller_id' => $product['user_id']],
                    withCount: ['reviews' => 'reviews']
                );
                $productsCount = $this->productRepo->getWebListWithScope(
                    scope: 'active',
                    filters: ['added_by' => 'in_house', 'seller_id' => $product['user_id']],
                    dataLimit: 'all'
                )->count();
            }
            $totalReviews = 0;
            foreach ($productsForReview as $item) {
                $totalReviews += $item->reviews_count;
            }

            $productIds = Product::active()->where(['added_by' => $product['added_by']])
                ->where('user_id', $product['user_id'])->pluck('id')->toArray();
            $vendorReviewData = Review::active()->whereIn('product_id', $productIds);
            $ratingCount = $vendorReviewData->count();
            $avgRating = $vendorReviewData->avg('rating');

            $vendorRattingStatusPositive = 0;
            foreach ($vendorReviewData->pluck('rating') as $singleRating) {
                ($singleRating >= 4 ? ($vendorRattingStatusPositive++) : '');
            }

            $positiveReview = $ratingCount != 0 ? ($vendorRattingStatusPositive * 100) / $ratingCount : 0;

            $sellerList = $this->sellerRepo->getListWithScope(
                scope: 'active',
                filters: ['category_id' => $product['category_id']],
                relations: ['shop' => 'shop', 'product.reviews' => 'product.reviews'],
                withCount: ['product' => 'product'],
                dataLimit: 'all',
            );
            $sellerList?->map(function ($seller) {
                $rating = 0;
                $count = 0;
                foreach ($seller->product as $item) {
                    foreach ($item->reviews as $review) {
                        $rating += $review->rating;
                        $count++;
                    }
                }
                $avg_rating = $rating / ($count == 0 ? 1 : $count);
                $rating_count = $count;
                $seller['average_rating'] = $avg_rating;
                $seller['rating_count'] = $rating_count;

                $product_count = $seller->product->count();
                $randomSingleProduct = Arr::random($seller->product->toArray(), $product_count < 3 ? $product_count : 3);
                $seller['product'] = $randomSingleProduct;
                return $seller;
            });
            $newSellers = $sellerList->sortByDesc('id')->take(12);
            $topRatedShops = $sellerList->where('rating_count', '!=', 0)->sortByDesc('average_rating')->take(12);

            $deliveryInfo = self::getProductDeliveryCharge(product: $product, quantity: $product['minimum_order_qty']);
            $productsThisStoreTopRated = $this->productRepo->getWebListWithScope(
                orderBy: ['reviews_count' => 'DESC'],
                scope: 'active',
                filters: ['added_by' => $product['added_by'] == 'admin' ? 'in_house' : $product['added_by'], 'seller_id' => $product['user_id'], 'customer_id' => Auth::guard('customer')->user()->id ?? 0],
                whereHas: ['reviews' => 'reviews'],
                relations: ['category' => 'category', 'rating' => 'rating', 'reviews' => 'reviews', 'wishList' => 'wishList', 'compareList' => 'compareList'],
                withCount: ['reviews' => 'reviews'],
                withSum: [['relation' => 'orderDetails', 'column' => 'qty', 'whereColumn' => 'delivery_status', 'whereValue' => 'delivered']],
                dataLimit: 12,
                offset: 1
            );

            $productsTopRated = $this->productRepo->getWebListWithScope(
                orderBy: ['reviews_count' => 'DESC'],
                scope: 'active',
                filters: ['category_id' => $product['category_id'], 'customer_id' => Auth::guard('customer')->user()->id ?? 0],
                relations: ['wishList' => 'wishList', 'compareList' => 'compareList'],
                withCount: ['reviews' => 'reviews'],
                dataLimit: 12,
                offset: 1
            );

            $productsLatest = $this->productRepo->getWebListWithScope(
                orderBy: ['id' => 'DESC'],
                scope: 'active',
                filters: ['category_id' => $product['category_id'], 'customer_id' => Auth::guard('customer')->user()->id ?? 0],
                whereNotIn: ['id' => [$product['id']]],
                relations: ['wishList' => 'wishList', 'compareList' => 'compareList'],
                dataLimit: 12,
                offset: 1
            );

            $previewFileInfo = getFileInfoFromURL(url: $product?->preview_file_full_url['path']);

            return view(VIEW_FILE_NAMES['products_details'], compact(
                'product',
                'wishlistStatus',
                'countWishlist',
                'relatedProducts',
                'currentDate',
                'sellerVacationStartDate',
                'sellerVacationEndDate',
                'rattingStatus',
                'productsLatest',
                'sellerTemporaryClose',
                'inHouseVacationStartDate',
                'inHouseVacationEndDate',
                'inHouseVacationStatus',
                'inHouseTemporaryClose',
                'positiveReview',
                'overallRating',
                'decimalPointSettings',
                'moreProductFromSeller',
                'productsForReview',
                'productsCount',
                'totalReviews',
                'rating',
                'productReviews',
                'avgRating',
                'topRatedShops',
                'newSellers',
                'deliveryInfo',
                'productsTopRated',
                'productsThisStoreTopRated',
                'previewFileInfo',
                'productAuthorsInfo',
                'productPublishingHouseInfo',
                'firstVariationQuantity',
                'productDetailsMeta'
            ));
        }

        Toastr::error(translate('not_found'));
        return back();
    }
}
