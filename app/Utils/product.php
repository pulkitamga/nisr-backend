<?php

use App\Models\Product;
use App\Models\Review;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Utils\ProductManager;
use Illuminate\Support\Facades\Cache;

if (!function_exists('getOverallRating')) {
    function getOverallRating(object|array $reviews): array
    {
        $totalRating = count($reviews);
        $rating = 0;
        foreach ($reviews as $key => $review) {
            $rating += $review->rating;
        }
        if ($totalRating == 0) {
            $overallRating = 0;
        } else {
            $overallRating = number_format($rating / $totalRating, 2);
        }

        return [$overallRating, $totalRating];
    }
}

if (!function_exists('getRating')) {
    function getRating(object|array $reviews): array
    {
        $rating5 = 0;
        $rating4 = 0;
        $rating3 = 0;
        $rating2 = 0;
        $rating1 = 0;
        foreach ($reviews as $key => $review) {
            if ($review->rating == 5) {
                $rating5 += 1;
            }
            if ($review->rating == 4) {
                $rating4 += 1;
            }
            if ($review->rating == 3) {
                $rating3 += 1;
            }
            if ($review->rating == 2) {
                $rating2 += 1;
            }
            if ($review->rating == 1) {
                $rating1 += 1;
            }
        }
        return [$rating5, $rating4, $rating3, $rating2, $rating1];
    }
}

if (!function_exists('getProductDiscount')) {
    /**
     * @param object|array $product
     * @param string|float|int $price
     * @return float
     */
    function getProductDiscount(object|array $product, string|float|int $price): float
    {
        $discount = 0;
        if ($product['discount_type'] == 'percent') {
            $discount = ($price * $product['discount']) / 100;
        } elseif ($product['discount_type'] == 'flat') {
            $discount = $product['discount'];
        }

        return floatval($discount);
    }
}

if (!function_exists('getPriceRangeWithDiscount')) {
    function getPriceRangeWithDiscount(array|object $product, string|null $type = 'web'): float|string
    {
        $productUnitPrice = $product->unit_price;
        $variations = json_decode($product->variation ?? '[]', true);

        // foreach (json_decode($product->variation) as $key => $variation) {
        //     if ($key == 0) {
        //         $productUnitPrice = $variation->price;
        //     }
        // }
        if (is_array($variations) && count($variations) > 0) {
            $productUnitPrice = $variations[0]['price'] ?? $productUnitPrice;
        }

        if ($product->digitalVariation && count($product->digitalVariation) > 0) {
            $digitalVariations = $product->digitalVariation->toArray();
            $productUnitPrice = $digitalVariations[0]['price'];
        }

        if ($type == 'panel') {
            if (isset($product['clearanceSale']) && $product['clearanceSale']) {
                $discountAmount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $productUnitPrice, from: 'panel');
                $productDiscountedPrice = setCurrencySymbol(amount: usdToDefaultCurrency(amount: $productUnitPrice - $discountAmount), currencyCode: getCurrencyCode());
                return '<span class="discounted-unit-price fs-24 font-bold">' . $productDiscountedPrice . '</span>' . '<del class="product-total-unit-price align-middle text-muted fs-18 font-semibold">' . setCurrencySymbol(amount: usdToDefaultCurrency(amount: $productUnitPrice), currencyCode: getCurrencyCode()) . '</del>';
            } elseif ($product->discount > 0) {
                $amount = $productUnitPrice - getProductDiscount(product: $product, price: $productUnitPrice);
                $productDiscountedPrice = setCurrencySymbol(amount: usdToDefaultCurrency(amount: $amount), currencyCode: getCurrencyCode());
                return '<span class="discounted-unit-price fs-24 font-bold">' . $productDiscountedPrice . '</span>' . '<del class="product-total-unit-price align-middle text-muted fs-18 font-semibold">' . setCurrencySymbol(amount: usdToDefaultCurrency(amount: $productUnitPrice), currencyCode: getCurrencyCode()) . '</del>';
            } else {
                return '<span class="discounted-unit-price fs-24 font-bold">' . setCurrencySymbol(amount: usdToDefaultCurrency(amount: $productUnitPrice), currencyCode: getCurrencyCode()) . '</span>';
            }
        } else {
            if (isset($product['clearanceSale']) && $product['clearanceSale']) {
                $discountAmount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $productUnitPrice);
                $productDiscountedPrice = webCurrencyConverter(amount: $productUnitPrice - $discountAmount);
                return '<span class="discounted-unit-price fs-24 font-bold">' . $productDiscountedPrice . '</span>' . '<del class="product-total-unit-price align-middle text-muted fs-18 font-semibold">' . webCurrencyConverter(amount: $productUnitPrice) . '</del>';
            } elseif ($product->discount > 0) {
                $productDiscountedPrice = webCurrencyConverter(amount: $productUnitPrice - getProductDiscount(product: $product, price: $productUnitPrice));
                return '<span class="discounted-unit-price fs-24 font-bold">' . $productDiscountedPrice . '</span>' . '<del class="product-total-unit-price align-middle text-muted fs-18 font-semibold">' . webCurrencyConverter(amount: $productUnitPrice) . '</del>';
            } else {
                return '<span class="discounted-unit-price fs-24 font-bold">' . webCurrencyConverter(amount: $productUnitPrice) . '</span>';
            }
        }
    }
}

if (!function_exists('getRatingCount')) {
    function getRatingCount($product_id, $rating)
    {
        return Review::where(['product_id' => $product_id, 'rating' => $rating])->whereNull('delivery_man_id')->count();
    }
}

if (!function_exists('units')) {
    function units(): array
    {
        return ['pc', 'kg', 'gms', 'ltrs', 'pair', 'oz', 'lb'];
    }
}

if (!function_exists('getUnitLabel')) {
    function getUnitLabel(?string $unit): string
    {
        $unit = trim((string)$unit);

        if ($unit === '') {
            return '';
        }

        $normalizedUnit = strtolower($unit);
        $translationKey = 'unit_label_' . $normalizedUnit;
        $translatedValue = translate($translationKey);

        return $translatedValue !== $translationKey ? $translatedValue : $unit;
    }
}

if (!function_exists('getTranslatedLookupLabel')) {
    function getTranslatedLookupLabel(string $modelClass, string $column, string $translationKey, ?string $value): string
    {
        $value = trim((string)$value);

        if ($value === '') {
            return '';
        }

        $locale = app()->getLocale();
        $cacheKey = implode('|', [$modelClass, $column, $translationKey, $locale, $value]);
        static $cache = [];

        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $record = $modelClass::withoutGlobalScopes()
            ->with('translations')
            ->where($column, $value)
            ->first();

        return $cache[$cacheKey] = $record
            ? $record->getTranslatedField($translationKey, $locale, $record->getRawOriginal($column))
            : $value;
    }
}

if (!function_exists('getVehicleMakeLabel')) {
    function getVehicleMakeLabel(?string $value): string
    {
        return getTranslatedLookupLabel(\App\Models\VehicleMake::class, 'name', 'name', $value);
    }
}

if (!function_exists('getVehicleModelLabel')) {
    function getVehicleModelLabel(?string $value): string
    {
        return getTranslatedLookupLabel(\App\Models\VehicleModel::class, 'name', 'name', $value);
    }
}
if (!function_exists('getVendorProductsCount')) {
    function getVendorProductsCount(string $type): int
    {
        return match ($type) {
            'new-product' => \Illuminate\Support\Facades\DB::table('products')
                ->where('added_by', 'seller')
                ->whereNull('deleted_at')
                ->where('request_status', 0)
                ->count(),
            'product-updated-request' => \Illuminate\Support\Facades\DB::table('products')
                ->where('added_by', 'seller')
                ->whereNull('deleted_at')
                ->whereNotNull('is_shipping_cost_updated')
                ->where('is_shipping_cost_updated', 0)
                ->count(),
            'approved' => \Illuminate\Support\Facades\DB::table('products')
                ->where('added_by', 'seller')
                ->whereNull('deleted_at')
                ->where('request_status', 1)
                ->count(),
            'denied' => \Illuminate\Support\Facades\DB::table('products')
                ->where('added_by', 'seller')
                ->whereNull('deleted_at')
                ->where('request_status', 2)
                ->where('status', 0)
                ->count(),
        };
    }
}
if (!function_exists('getAdminProductsCount')) {
    function getAdminProductsCount(string $type): int
    {
        return match ($type) {
            'all' => \Illuminate\Support\Facades\DB::table('products')
                ->where('added_by', 'admin')
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->where('request_status', 1)
                ->where(function ($query) {
                    $query->whereIn('product_type', ['digital', 'services'])
                        ->orWhere('current_stock', '>', 0);
                })
                ->count(),
            'new-product' => \Illuminate\Support\Facades\DB::table('products')
                ->where('added_by', 'admin')
                ->whereNull('deleted_at')
                ->where('request_status', 0)
                ->count(),
            'product-updated-request' => \Illuminate\Support\Facades\DB::table('products')
                ->where('added_by', 'admin')
                ->whereNull('deleted_at')
                ->whereNotNull('is_shipping_cost_updated')
                ->where('is_shipping_cost_updated', 0)
                ->count(),
            'approved' => \Illuminate\Support\Facades\DB::table('products')
                ->where('added_by', 'admin')
                ->whereNull('deleted_at')
                ->where('request_status', 1)
                ->count(),
            'denied' => \Illuminate\Support\Facades\DB::table('products')
                ->where('added_by', 'admin')
                ->whereNull('deleted_at')
                ->where('request_status', 2)
                ->where('status', 0)
                ->count(),
        };
    }
}


if (!function_exists('getRestockProductFCMTopic')) {
    function getRestockProductFCMTopic(array|object $restockRequest): string
    {
        return 'restock_' . $restockRequest['id'] . '_product_restock_' . $restockRequest->product_id . '_topic';
    }
}


if (!function_exists('isProductInWishList')) {
    function isProductInWishList(string|int $productId): bool
    {
        if (session('wish_list') && in_array($productId, session('wish_list'))) {
            return true;
        }
        return false;
    }
}

if (!function_exists('isProductInCompareList')) {
    function isProductInCompareList(string|int $productId): bool
    {
        if (session('compare_list') && in_array($productId, session('compare_list'))) {
            return true;
        }
        return false;
    }
}


if (!function_exists('getFeaturedDealsProductList')) {
    function getFeaturedDealsProductList()
    {
        $cacheKey = 'cache_for_Featured_deals_products_list_' . getDefaultLanguage();
        $cacheKeys = Cache::get(CACHE_FOR_FEATURED_DEAL_PRODUCTS_LIST, []);
        if (!in_array($cacheKey, $cacheKeys)) {
            $cacheKeys[] = $cacheKey;
            Cache::put(CACHE_FOR_FEATURED_DEAL_PRODUCTS_LIST, $cacheKeys, CACHE_FOR_3_HOURS);
        }

        return Cache::remember($cacheKey, CACHE_FOR_3_HOURS, function () {
            $featuredDealID = FlashDeal::where(['deal_type' => 'feature_deal', 'status' => 1])
                ->whereDate('start_date', '<=', date('Y-m-d'))
                ->whereDate('end_date', '>=', date('Y-m-d'))->pluck('id')->first();
            $featuredDealProductIDs = $featuredDealID ? FlashDealProduct::where('flash_deal_id', $featuredDealID)->pluck('product_id')->toArray() : [];
            return ProductManager::getPriorityWiseFeatureDealQuery(
                query: Product::active()->with(['category', 'clearanceSale' => function ($query) {
                    return $query->active();
                }])->whereIn('id', $featuredDealProductIDs),
                dataLimit: 'all'
            );
        });
    }
}
