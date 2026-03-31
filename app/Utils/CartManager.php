<?php

namespace App\Utils;

use App\Domain\Stock\DTO\StockValidationContext;
use App\Domain\Stock\Enums\StockChannel;
use App\Domain\Stock\StockAvailabilityService;
use App\Domain\Stock\Support\VariantMatcher;
use App\Services\ProductExtraChargeResolverService;
use App\Models\DigitalProductVariation;
use App\Models\ShippingMethod;
use App\Utils\Helpers;
use App\Models\Cart;
use App\Models\CartShipping;
use App\Models\CategoryShippingCost;
use App\Models\Color;
use App\Models\Product;
use App\Models\ShippingType;
use App\Models\Shop;
use App\Models\ManageBranchProductStock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CartManager
{
    public static function resolveCartOwnerContext($request = null): array
    {
        $user = Helpers::getCustomerInformation($request);
        $guestId = session('guest_id') ?? ($request->guest_id ?? 0);

        return [
            'user' => $user,
            'customer_id' => $user == 'offline' ? (int)$guestId : (int)$user->id,
            'is_guest' => $user == 'offline' ? 1 : 0,
        ];
    }

    public static function getOwnedCartQuery($request = null)
    {
        $context = self::resolveCartOwnerContext($request);

        return Cart::query()->where([
            'customer_id' => $context['customer_id'],
            'is_guest' => $context['is_guest'],
        ]);
    }

    public static function generateOpaqueCartGroupId(): string
    {
        return (string) Str::uuid();
    }

    private static function isLegacyCartGroupId(?string $cartGroupId): bool
    {
        if (!$cartGroupId) {
            return true;
        }

        return preg_match('/^(guest|\d+)-/i', $cartGroupId) === 1;
    }

    private static function resolveCartGroupIdForOwner(
        int $customerId,
        int $isGuest,
        int $sellerId,
        string $sellerIs,
        ?string $preferredGroupId = null
    ): string {
        if ($preferredGroupId && !self::isLegacyCartGroupId($preferredGroupId)) {
            return $preferredGroupId;
        }

        $existingCartGroup = Cart::query()
            ->where([
                'customer_id' => $customerId,
                'is_guest' => $isGuest,
                'seller_id' => $sellerId,
                'seller_is' => $sellerIs,
            ])
            ->value('cart_group_id');

        if ($existingCartGroup) {
            return $existingCartGroup;
        }

        return self::generateOpaqueCartGroupId();
    }

    private static function resolveCurrentCartUnitPrice(Product $product, string $variant = ''): float
    {
        $variant = trim($variant);

        if ($product->product_type === 'digital' && $variant !== '') {
            $digitalVariation = DigitalProductVariation::query()
                ->where([
                    'product_id' => $product->id,
                    'variant_key' => $variant,
                ])
                ->first();

            if ($digitalVariation) {
                return (float) $digitalVariation->price;
            }
        }

        if ($variant !== '' && !empty($product->variation)) {
            $variantMatcher = new VariantMatcher();
            foreach (json_decode($product->variation ?? '[]') as $variation) {
                if ($variantMatcher->matches($variation->type ?? null, $variant)) {
                    return (float) ($variation->price ?? $product->unit_price);
                }
            }
        }

        return (float) $product->unit_price;
    }

    public static function refreshCartItemPricing(Cart $cart, ?Product $product = null, bool $persist = true): Cart
    {
        $product = $product ?: Product::query()->find($cart->product_id);
        if (!$product) {
            return $cart;
        }

        $price = self::resolveCurrentCartUnitPrice($product, (string) ($cart->variant ?? ''));
        $discount = getProductPriceByType(
            product: $product,
            type: 'discounted_amount',
            result: 'value',
            price: $price
        );
        $taxablePrice = max(0, $price - $discount);
        $tax = Helpers::tax_calculation(
            product: $product,
            price: $taxablePrice,
            tax: $product['tax'],
            tax_type: 'percent'
        );
        $shippingType = (string) ($cart->shipping_type ?? '');
        if ($product->product_type !== 'physical') {
            $shippingCost = 0;
        } elseif ($shippingType === 'area_wise') {
            // Preserve resolved area-wise delivery costs already selected for this cart group.
            $shippingCost = max(0, (float) ($cart->shipping_cost ?? 0));
        } elseif ($shippingType === 'order_wise') {
            $shippingCost = 0;
        } else {
            $shippingCost = self::get_shipping_cost_for_product_category_wise($product, (int) ($cart->quantity ?? 0));
        }

        $hasChanged = (float) $cart->price !== (float) $price
            || (float) $cart->discount !== (float) $discount
            || (float) $cart->tax !== (float) $tax
            || (float) $cart->shipping_cost !== (float) $shippingCost;

        $cart->price = $price;
        $cart->discount = $discount;
        $cart->tax = $tax;
        $cart->shipping_cost = $shippingCost;
        $cart->setRelation('product', $product);

        if ($persist && $hasChanged) {
            $cart->save();
        }

        return $cart;
    }

    private static function refreshCartCollectionPricing($cartItems)
    {
        return $cartItems?->each(function ($item) {
            if ($item instanceof Cart) {
                self::refreshCartItemPricing($item, $item->getRelationValue('product'));
            }
        });
    }

    public static function cartListSessionToDatabase($request = null): void
    {
        $user = Helpers::getCustomerInformation($request);
        if (session()->has('guest_id') || (!is_null($request) && !is_null($request->guest_id))) {
            $guestId = session('guest_id') ?? $request->guest_id;
            $cartList = Cart::where(['is_guest' => 1, 'customer_id' => $guestId])->get();
            foreach ($cartList as $cart) {
                $databaseCart = Cart::where([
                    'customer_id' => $user->id,
                    'seller_id' => $cart['seller_id'],
                    'seller_is' => $cart['seller_is']
                ])->first();

                Cart::where([
                    'customer_id' => $user->id,
                    'product_id' => $cart['product_id'],
                    'variant' => $cart['variant'],
                    'seller_id' => $cart['seller_id'],
                    'seller_is' => $cart['seller_is']
                ])->delete();

                $cart->cart_group_id = isset($databaseCart)
                    ? $databaseCart['cart_group_id']
                    : self::resolveCartGroupIdForOwner(
                        customerId: (int) $user->id,
                        isGuest: 0,
                        sellerId: (int) $cart['seller_id'],
                        sellerIs: (string) $cart['seller_is'],
                        preferredGroupId: self::isLegacyCartGroupId($cart['cart_group_id']) ? null : (string) $cart['cart_group_id']
                    );
                $cart->customer_id = $user->id;
                $cart->is_guest = 0;
                $cart->save();
            }
        }
    }

    public static function getCartListQuery($groupId = null, $type = null)
    {
        $cartItems = Cart::with(['product' => function ($query) {
            return $query->active()->with(['clearanceSale' => function ($query) {
                return $query->active();
            }]);
        }])
            ->whereHas('product', function ($query) {
                return $query->active();
            })


            ->when($groupId == null, function ($query) {
                return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids());
            })
            ->when($groupId, function ($query) use ($groupId) {
                return $query->where('cart_group_id', $groupId);
            })
            ->when($type == 'checked', function ($query) {
                return $query->where(['is_checked' => 1]);
            })
            ->get();

        return self::refreshCartCollectionPricing($cartItems);
    }


    public static function getCartListGroupQuery($groupId = null, $type = null)
    {
        $cartItems = Cart::with(['product' => function ($query) {
            return $query->active()
                ->with([
                    'clearanceSale' => function ($query) {
                        return $query->active();
                    },
                    'category.extraCharges' => function ($query) {
                        return $query->whereIn('type', ['exchange', 'installation'])->where('status', 1);
                    },
                    'subCategory.extraCharges' => function ($query) {
                        return $query->whereIn('type', ['exchange', 'installation'])->where('status', 1);
                    },
                    'subSubCategory.extraCharges' => function ($query) {
                        return $query->whereIn('type', ['exchange', 'installation'])->where('status', 1);
                    },
                ]);
        }])
            ->whereHas('product', function ($query) {
                return $query->active();
            })
            ->when($groupId == null, function ($query) {
                return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids());
            })
            ->when($groupId, function ($query) use ($groupId) {
                return $query->where('cart_group_id', $groupId);
            })
            ->when($type == 'checked', function ($query) {
                return $query->where(['is_checked' => 1]);
            })
            ->get();

        return self::refreshCartCollectionPricing($cartItems)?->each(function ($item) {
                $product = $item['product'];
                $category = $product->category;
                $subCategory = $product->subCategory;
                $subSubCategory = $product->subSubCategory;

                $extraCharges = $category?->extraCharges?->isNotEmpty()
                    ? $category->extraCharges
                    : ($subCategory?->extraCharges?->isNotEmpty()
                        ? $subCategory->extraCharges
                        : ($subSubCategory?->extraCharges?->isNotEmpty()
                            ? $subSubCategory->extraCharges
                            : collect([])));

                $item['exchange_charge'] = $extraCharges->where('type', 'exchange')->first()->charges ?? 0;
                $item['installation_charge'] = $extraCharges->where('type', 'installation')->first()->charges ?? 0;
            })->groupBy('cart_group_id');
    }


    public static function get_cart_for_api($request, $groupId = null, $type = null)
    {
        $cartItems = Cart::with(['product' => function ($query) {
            return $query->active()->with(['clearanceSale' => function ($query) {
                return $query->active();
            }]);
        }])
            ->whereHas('product', function ($query) {
                return $query->active();
            })
            ->when(($groupId == null && $type != 'checked'), function ($query) use ($request) {
            return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids(request: $request));
        })
            ->when(($groupId == null && $type == 'checked'), function ($query) use ($request) {
                return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids(request: $request, type: 'checked'));
            })
            ->when($groupId, function ($query) use ($groupId) {
                return $query->where('cart_group_id', $groupId);
            })
            ->when($type == 'checked', function ($query) {
                return $query->where(['is_checked' => 1]);
            })
            ->get();

        return self::refreshCartCollectionPricing($cartItems);
    }

    public static function get_cart_group_ids($request = null, $type = null)
    {
        $user = Helpers::getCustomerInformation($request);

        return Cart::whereHas('product', function ($query) {
            return $query->active();
        })->when($user == 'offline', function ($query) use ($request) {
            return $query->where(['customer_id' => session('guest_id') ?? ($request->guest_id ?? 0), 'is_guest' => 1]);
        })->when($user != 'offline', function ($query) use ($user) {
            return $query->where(['customer_id' => $user->id, 'is_guest' => '0']);
        })
            ->when($type == 'checked', function ($query) {
                return $query->where(['is_checked' => 1]);
            })
            ->groupBy('cart_group_id')
            ->pluck('cart_group_id')
            ->toArray();
    }

    public static function get_shipping_cost($groupId = null, $type = null, $request = null)
    {
        if (self::isPickupDeliveryType($request)) {
            return 0;
        }

        $cost = 0;

        $cartShippingCost = Cart::where(['product_type' => 'physical'])
            ->whereHas('product', function ($query) {
                return $query->active();
            })->when(($groupId == null && $type != 'checked'), function ($query) use ($request) {
                return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids(request: $request));
            })
            ->when(($groupId == null && $type == 'checked'), function ($query) use ($request) {
                return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids(request: $request, type: 'checked'));
            })
            ->when($groupId != null, function ($query) use ($groupId) {
                return $query->where(['cart_group_id' => $groupId]);
            })
            ->when($type == 'checked', function ($query) {
                return $query->where(['is_checked' => 1]);
            })->sum('shipping_cost');

        $orderWiseShippingCostData = CartShipping::whereHas('cart', function ($query) use ($type) {
            return $query->where(['product_type' => 'physical'])->whereHas('product', function ($query) {
                return $query->active();
            })->when($type == 'checked', function ($query) {
                return $query->where(['is_checked' => 1]);
            });
        })->when(($groupId == null && $type != 'checked'), function ($query) use ($request) {
            return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids(request: $request));
        })
            ->when(($groupId == null && $type == 'checked'), function ($query) use ($request) {
                return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids(request: $request, type: 'checked'));
            })
            ->when(($groupId != null), function ($query) use ($groupId) {
                return $query->where('cart_group_id', $groupId);
            });

        if ($groupId == null) {
            $orderWiseShippingCost = $orderWiseShippingCostData->sum('shipping_cost');
        } else {
            $data = $orderWiseShippingCostData->first();
            $orderWiseShippingCost = isset($data) ? $data->shipping_cost : 0;
        }
        return ($orderWiseShippingCost + $cartShippingCost);
    }

    private static function isPickupDeliveryType($request = null): bool
    {
        $deliveryType = $request ? ($request['delivery_type'] ?? null) : null;
        if (!$deliveryType && session()->has('delivery_type')) {
            $deliveryType = session('delivery_type');
        }

        return $deliveryType === 'pickup';
    }

    private static function hasAreaWiseShippingPending($groupId = null, $type = null, $request = null): bool
    {
        if (self::isPickupDeliveryType($request)) {
            return false;
        }

        $hasAreaWiseShipping = Cart::where(['product_type' => 'physical'])
            ->whereHas('product', function ($query) {
                return $query->active();
            })
            ->when(($groupId == null && $type != 'checked'), function ($query) use ($request) {
                return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids(request: $request));
            })
            ->when(($groupId == null && $type == 'checked'), function ($query) use ($request) {
                return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids(request: $request, type: 'checked'));
            })
            ->when($groupId != null, function ($query) use ($groupId) {
                return $query->where(['cart_group_id' => $groupId]);
            })
            ->when($type == 'checked', function ($query) {
                return $query->where(['is_checked' => 1]);
            })
            ->where('shipping_type', 'area_wise')
            ->exists();

        if (!$hasAreaWiseShipping) {
            return false;
        }

        if ($request) {
            $areaWiseResolved = $request['area_wise_shipping_resolved'] ?? $request['is_area_wise_shipping_resolved'] ?? null;
            if (!is_null($areaWiseResolved)) {
                return !self::normalizeBooleanValue($areaWiseResolved);
            }

            $hasAppliedAreaWiseCost = Cart::where(['product_type' => 'physical'])
                ->whereHas('product', function ($query) {
                    return $query->active();
                })
                ->when(($groupId == null && $type != 'checked'), function ($query) use ($request) {
                    return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids(request: $request));
                })
                ->when(($groupId == null && $type == 'checked'), function ($query) use ($request) {
                    return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids(request: $request, type: 'checked'));
                })
                ->when($groupId != null, function ($query) use ($groupId) {
                    return $query->where(['cart_group_id' => $groupId]);
                })
                ->when($type == 'checked', function ($query) {
                    return $query->where(['is_checked' => 1]);
                })
                ->where('shipping_type', 'area_wise')
                ->where('shipping_cost', '>', 0)
                ->exists();

            if ($hasAppliedAreaWiseCost) {
                return false;
            }
        }

        return !(bool)session('area_wise_shipping_resolved', false);
    }

    private static function normalizeBooleanValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    public static function order_wise_shipping_discount()
    {
        if (auth('customer')->check()) {
            $shippingMethod = getWebConfig(name: 'shipping_method');
            $cartGroupIds = CartManager::get_cart_group_ids();

            $amount = 0;
            if (count($cartGroupIds) > 0) {

                foreach ($cartGroupIds as $cart) {
                    $cartData = Cart::whereHas('product', function ($query) {
                        return $query->active();
                    })->where('cart_group_id', $cart)->first();
                    if ($shippingMethod == 'inhouse_shipping') {
                        $adminShipping = \App\Models\ShippingType::where('seller_id', 0)->first();
                        $shippingType = isset($adminShipping) == true ? $adminShipping->shipping_type : 'order_wise';
                    } else {
                        if ($cartData->seller_is == 'admin') {
                            $adminShipping = \App\Models\ShippingType::where('seller_id', 0)->first();
                            $shippingType = isset($adminShipping) == true ? $adminShipping->shipping_type : 'order_wise';
                        } else {
                            $sellerShipping = \App\Models\ShippingType::where('seller_id', $cartData->seller_id)->first();
                            $shippingType = isset($sellerShipping) == true ? $sellerShipping->shipping_type : 'order_wise';
                        }
                    }

                    if ($shippingType == 'order_wise' && session('coupon_type') == 'free_delivery' && (session('coupon_seller_id') == '0' || (is_null(session('coupon_seller_id')) && $cartData->seller_is == 'admin') || (session('coupon_seller_id') == $cartData->seller_id && $cartData->seller_is == 'seller'))) {
                        $amount += CartManager::get_shipping_cost(groupId: $cart, type: 'checked');
                    }
                }
            }

            return $amount;
        }
    }

    public static function getTaxBreakdownFromAmount(float $amount, float $taxRate, string $taxModel): array
    {
        $amount = max(0, $amount);
        $taxRate = max(0, $taxRate);

        if ($taxModel == 'include') {
            $netAmount = $taxRate > 0 ? ($amount / (1 + ($taxRate / 100))) : $amount;
            $vatAmount = $amount - $netAmount;
            return [
                'net' => $netAmount,
                'vat' => $vatAmount,
                'gross' => $amount,
            ];
        }

        $vatAmount = $taxRate > 0 ? ($amount * ($taxRate / 100)) : 0;
        return [
            'net' => $amount,
            'vat' => $vatAmount,
            'gross' => $amount + $vatAmount,
        ];
    }

    private static function getTaxRateForCartItem($cartItem): float
    {
        $taxRate = (float)data_get($cartItem, 'product.tax', 0);
        if ($taxRate > 0) {
            return $taxRate;
        }

        $unitPrice = (float)($cartItem['price'] ?? 0);
        $unitTax = (float)($cartItem['tax'] ?? 0);
        if ($unitPrice <= 0 || $unitTax <= 0) {
            return 0;
        }

        if (($cartItem['tax_model'] ?? 'exclude') == 'include') {
            $netPrice = $unitPrice - $unitTax;
            return $netPrice > 0 ? ($unitTax / $netPrice) * 100 : 0;
        }

        return ($unitTax / $unitPrice) * 100;
    }

    public static function getCartPriceSummary($cartGroupId = null, $type = 'checked', ?float $couponDiscount = null, ?string $couponType = null, $request = null): array
    {
        $checkedType = $type == 'checked' ? 'checked' : null;
        $isApiHttpRequest = $request instanceof \Illuminate\Http\Request && $request->is('api/*');
        $shouldUseApiCartSource = !is_null($request)
            && ($isApiHttpRequest || isset($request['payment_request_from']) || isset($request['customer_id']) || isset($request['guest_id']) || isset($request['is_guest']));

        if ($shouldUseApiCartSource) {
            $cart = CartManager::get_cart_for_api(request: $request, groupId: $cartGroupId, type: $checkedType);
        } else {
            $cart = CartManager::getCartListQuery(groupId: $cartGroupId, type: $checkedType);
        }

        $isPickupDelivery = self::isPickupDeliveryType($request);
        $isAreaWiseShippingPending = self::hasAreaWiseShippingPending(groupId: $cartGroupId, type: $checkedType, request: $request);

        $itemPrice = 0;
        $productDiscount = 0;
        $installationCharge = 0;
        $exchangeCharge = 0;
        $lineAmountTotal = 0;
        $lineItems = [];

        foreach ($cart as $item) {
            $quantity = (float)$item['quantity'];
            $unitPrice = (float)$item['price'];
            $discountPerUnit = (float)$item['discount'];

            $lineAmount = max(0, ($unitPrice - $discountPerUnit) * $quantity);
            $lineAmountTotal += $lineAmount;

            $itemPrice += $unitPrice * $quantity;
            $productDiscount += $discountPerUnit * $quantity;
            $installationCharge += (float)($item['installtion_charges'] ?? 0);
            $exchangeCharge += (float)($item['exchange_charges'] ?? 0) * (float)($item['exchange_qty'] ?? 0);

            $lineItems[] = [
                'amount' => $lineAmount,
                'tax_rate' => self::getTaxRateForCartItem($item),
                'tax_model' => $item['tax_model'] ?? 'exclude',
            ];
        }

        $couponDiscount = is_null($couponDiscount)
            ? (float)($request ? ($request['coupon_discount'] ?? 0) : (session()->has('coupon_discount') ? session('coupon_discount') : 0))
            : max(0, (float)$couponDiscount);
        $couponType = $couponType ?? ($request ? ($request['coupon_type'] ?? null) : session('coupon_type'));

        $couponDiscountOnProduct = $couponType == 'free_delivery' ? 0 : min($couponDiscount, $lineAmountTotal);
        $couponDiscountOnShipping = $couponType == 'free_delivery' ? $couponDiscount : 0;

        $remainingCouponDiscount = $couponDiscountOnProduct;
        $netBeforeVat = 0;
        $vatTotal = 0;
        $subTotalWithVat = 0;
        $lineItemCount = count($lineItems);
        foreach ($lineItems as $index => $lineItem) {
            $lineCouponDiscount = 0;
            if ($couponDiscountOnProduct > 0 && $lineAmountTotal > 0) {
                if ($index == ($lineItemCount - 1)) {
                    $lineCouponDiscount = $remainingCouponDiscount;
                } else {
                    $lineCouponDiscount = round(($lineItem['amount'] / $lineAmountTotal) * $couponDiscountOnProduct, 4);
                    $lineCouponDiscount = min($lineCouponDiscount, $remainingCouponDiscount);
                }
            }
            $remainingCouponDiscount -= $lineCouponDiscount;

            $lineAmountAfterCoupon = max(0, $lineItem['amount'] - $lineCouponDiscount);
            $taxBreakdown = self::getTaxBreakdownFromAmount(
                amount: $lineAmountAfterCoupon,
                taxRate: (float)$lineItem['tax_rate'],
                taxModel: $lineItem['tax_model']
            );
            $netBeforeVat += $taxBreakdown['net'];
            $vatTotal += $taxBreakdown['vat'];
            $subTotalWithVat += $taxBreakdown['gross'];
        }

        $shippingCost = (float)CartManager::get_shipping_cost(groupId: $cartGroupId, type: $checkedType, request: $request);
        $shippingCostSavedForFreeDelivery = (float)CartManager::getShippingCostSavedForFreeDelivery(groupId: $cartGroupId, type: $checkedType);
        $orderWiseShippingDiscount = $cartGroupId ? 0 : (float)(CartManager::order_wise_shipping_discount() ?? 0);

        if ($isPickupDelivery || $isAreaWiseShippingPending) {
            $shippingCost = 0;
            $shippingCostSavedForFreeDelivery = 0;
            $orderWiseShippingDiscount = 0;
        }

        if ($couponType == 'free_delivery') {
            $shippingTotal = $shippingCost;
        } else {
            $shippingTotal = max(0, $shippingCost - $shippingCostSavedForFreeDelivery);
        }
        $couponDiscountOnShipping = min($couponDiscountOnShipping, $shippingTotal);

        $totalAmount = $subTotalWithVat
            + $shippingTotal
            + $installationCharge
            - $exchangeCharge
            - $couponDiscountOnShipping
            - $orderWiseShippingDiscount;
        $totalAmount = max(0, $totalAmount);

        $totalSavedAmount = $productDiscount
            + $couponDiscountOnProduct
            + $couponDiscountOnShipping
            + $shippingCostSavedForFreeDelivery;

        return [
            'itemPrice' => $itemPrice,
            'productDiscount' => $productDiscount,
            'couponDiscount' => $couponDiscount,
            'couponDiscountOnProduct' => $couponDiscountOnProduct,
            'couponDiscountOnShipping' => $couponDiscountOnShipping,
            'netBeforeVat' => $netBeforeVat,
            'vatTotal' => $vatTotal,
            'subTotal' => $subTotalWithVat,
            'shippingTotal' => $shippingTotal,
            'shippingCostSavedForFreeDelivery' => $shippingCostSavedForFreeDelivery,
            'orderWiseShippingDiscount' => $orderWiseShippingDiscount,
            'installationCharge' => $installationCharge,
            'exchangeCharge' => $exchangeCharge,
            'totalAmount' => $totalAmount,
            'totalSavedAmount' => $totalSavedAmount,
            'isPickupDelivery' => $isPickupDelivery,
            'isAreaWiseShippingPending' => $isAreaWiseShippingPending,
            'shippingNotice' => $isAreaWiseShippingPending ? translate('shipping_cost_determined_later_by_location') : null,
        ];
    }

    public static function cart_total($cart)
    {
        $total = 0;
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $product_subtotal = $item['price'] * $item['quantity'];
                $total += $product_subtotal;
            }
        }
        return $total;
    }

    public static function getCartListTotalAppliedDiscount($cart): float|int
    {
        $total = 0;
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $discount = getProductPriceByType(product: $item['product'], type: 'discounted_amount', result: 'value', price: $item['price']);
                $productSubtotal = ($item['price'] - $discount) * $item['quantity'];
                $total += $productSubtotal;
            }
        }
        return $total;
    }

    public static function cart_total_with_tax($cart)
    {
        $total = 0;
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $product_subtotal = ($item['price'] * $item['quantity']) + ($item['tax'] * $item['quantity']);
                $total += $product_subtotal;
            }
        }
        return $total;
    }

    public static function cart_grand_total($cartGroupId = null, $type = null)
    {
        if ($type == 'checked') {
            $cart = CartManager::getCartListQuery(groupId: $cartGroupId, type: 'checked');
            $shippingCost = CartManager::get_shipping_cost(groupId: $cartGroupId, type: 'checked');
        } else {
            $cart = CartManager::getCartListQuery(groupId: $cartGroupId);
            $shippingCost = CartManager::get_shipping_cost(groupId: $cartGroupId);
        }
        $total = 0;
        $tax = 0;
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $tax = $item['tax_model'] == 'include' ? 0 : $item['tax'];
                $discount = getProductPriceByType(product: $item['product'], type: 'discounted_amount', result: 'value', price: $item['price']);
                $productSubtotal = ($item['price'] * $item['quantity']) + ($tax * $item['quantity']) + ($item['installtion_charges']) - ($item['exchange_charges'] * $item['exchange_qty']) - $discount * $item['quantity'];
                $total += $productSubtotal;
            }
            $total += $shippingCost;
        }
        return $total;
    }

    public static function api_cart_grand_total($request, $cart_group_id = null)
    {
        $cart = CartManager::get_cart_for_api(request: $request, groupId: $cart_group_id, type: 'checked');
        $shipping_cost = CartManager::get_shipping_cost(groupId: $cart_group_id, type: 'checked', request: $request);
        $total = 0;
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $tax = $item['tax_model'] == 'include' ? 0 : $item['tax'];
                $product_subtotal = ($item['price'] * $item['quantity'])
                    + ($tax * $item['quantity'])
                    - $item['discount'] * $item['quantity'];
                $total += $product_subtotal;
            }
            $total += $shipping_cost;
        }
        return $total;
    }

    public static function getCartGrandTotalWithoutShippingCharge($cartGroupId = null, $type = null): float|int
    {
        if ($type) {
            $cart = CartManager::getCartListQuery(groupId: $cartGroupId, type: 'checked');
        } else {
            $cart = CartManager::getCartListQuery(groupId: $cartGroupId);
        }
        $total = 0;
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $tax = $item['tax_model'] == 'include' ? 0 : $item['tax'];
                $productSubtotal = ($item['price'] * $item['quantity']) + ($tax * $item['quantity']) - $item['discount'] * $item['quantity'];
                $total += $productSubtotal;
            }
        }
        return $total;
    }

    public static function cart_clean($request = null): void
    {
        $cartGroupIDs = CartManager::get_cart_group_ids(request: $request, type: 'checked');
        self::cartCleanByCartGroupIds(cartGroupIDs: $cartGroupIDs);
    }

    public static function cartCleanByCartGroupIds($cartGroupIDs): void
    {
        CartShipping::whereIn('cart_group_id', $cartGroupIDs)->delete();
        Cart::whereIn('cart_group_id', $cartGroupIDs)->where(['is_checked' => 1])->delete();

        session()->forget('coupon_code');
        session()->forget('coupon_type');
        session()->forget('coupon_bearer');
        session()->forget('coupon_discount');
        session()->forget('payment_method');
        session()->forget('shipping_method_id');
        session()->forget('billing_address_id');
        session()->forget('order_id');
        session()->forget('cart_group_id');
        session()->forget('order_note');
        session()->forget('area_wise_shipping_resolved');
    }

    public static function cart_clean_for_api_digital_payment($data): void
    {
        $cartIds = Cart::when($data['request']['is_guest'], function ($query) use ($data) {
            return $query->where(['is_guest' => 1]);
        })->when(!($data['request']['is_guest']), function ($query) use ($data) {
            return $query->where(['is_guest' => 0]);
        })->where(['customer_id' => $data['request']['customer_id'], 'is_checked' => 1])
            ->groupBy('cart_group_id')->pluck('cart_group_id')->toArray();

        CartShipping::whereIn('cart_group_id', $cartIds)->delete();
        Cart::when($data['request']['is_guest'], function ($query) use ($data) {
            return $query->where(['is_guest' => '1']);
        })->when(!($data['request']['is_guest']), function ($query) use ($data) {
            return $query->where(['is_guest' => '0']);
        })->where(['customer_id' => $data['request']['customer_id'], 'is_checked' => 1])
            ->delete();
    }

    public static function addToCartPhysicalProduct($request, $product, $shippingType, $sellerShippingList): array
    {
        $price = 0;
        $string = '';
        $variations = [];

        $user = Helpers::getCustomerInformation($request);
        $guestId = session('guest_id') ?? ($request->guest_id ?? 0);

        $stockCheckStatus = getWebConfig(name: 'stock_check');
        $productVariations = json_decode($product->variation ?? '[]');
        $variationCount = count($productVariations ?? []);
        $hasProductVariations = $variationCount > 0;

        if (
            ($stockCheckStatus == 1) &&
            ($product['product_type'] == 'physical') &&
            !$hasProductVariations &&
            ((int)$product['current_stock'] < (int)$request['quantity'])
        ) {
            return ['status' => 0, 'message' => translate('out_of_stock!')];
        }

        if ($product['minimum_order_qty'] > $request['quantity']) {
            return ['status' => 0, 'message' => translate('Minimum_order_quantity') . ' ' . $product['minimum_order_qty']];
        }

        if ($user == 'offline') {
            $customerId = $guestId;
            $isGuest = 1;
        } else {
            $customerId = $user->id;
            $isGuest = 0;
        }

        if ($request->has('color')) {
            $string .= Color::where(['code' => $request['color']])->first()->name;
            $variations['color'] = $string;
        }

        // Gets all the choice values of customer choice option and generate a string like Black-S-Cotton
        $choices = [];
        foreach (json_decode($product->choice_options) as $key => $choice) {
            $choices[$choice->name] = $request[$choice->name];
            $variations[$choice->title] = $request[$choice->name];
            if ($string != null) {
                $string .= '-' . str_replace(' ', '', $request[$choice->name]);
            } else {
                $string .= str_replace(' ', '', $request[$choice->name]);
            }
        }

        if (!($request['buy_now'])) {
            if ($request['shipping_method_exist'] && $request->has('product_variation_code') && $request['product_variation_code']) {
                $string = str_replace(' ', '', $request['product_variation_code']);
            }
        }

        $cartArray = [
            'color' => $request['color'] ?? null,
            'product_id' => $product['id'],
            'product_type' => $product['product_type'],
            'choices' => json_encode($choices),
            'variations' => json_encode($variations),
            'variant' => $string,
        ];

        // For variant products, validate availability against selected variant stock.
        if ($hasProductVariations && $string != null && trim((string)$string) !== '') {
            $matchedVariation = null;
            $variantMatcher = new VariantMatcher();
            foreach ($productVariations as $variationRow) {
                if ($variantMatcher->matches($variationRow->type ?? null, $string)) {
                    $matchedVariation = $variationRow;
                    break;
                }
            }

            if ($matchedVariation) {
                $price = (float)$matchedVariation->price;
                if (($stockCheckStatus == 1) && ((int)$matchedVariation->qty < (int)$request['quantity'])) {
                    return ['status' => 0, 'message' => translate('out_of_stock!')];
                }
            } else {
                if ($stockCheckStatus == 1) {
                    return ['status' => 0, 'message' => translate('out_of_stock!')];
                }
                $price = $product->unit_price;
            }
        } else {
            $price = $product->unit_price;
        }

        $getProductDiscount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $price);
        $taxablePrice = max(0, $price - $getProductDiscount);
        $tax = Helpers::tax_calculation(product: $product, price: $taxablePrice, tax: $product['tax'], tax_type: 'percent');
        $extraChargeData = self::resolveRequestedExtraCharges(
            product: $product,
            request: $request,
            quantity: (int)$request['quantity']
        );
        if ($extraChargeData['status'] === 0) {
            return $extraChargeData;
        }

        $cartArray += [
            'customer_id' => ($user == 'offline' ? $guestId : $user->id),
            'product_id' => $request['id'],
            'product_type' => $product['product_type'],
            'quantity' => $request['quantity'],
            'price' => $price,
            'tax' => $tax,
            'tax_model' => $product->tax_model,
            'discount' => $getProductDiscount,
            'is_checked' => 1,
            'slug' => $product['slug'],
            'name' => $product['name'],
            'thumbnail' => $product['thumbnail'],
            'seller_id' => ($product->added_by == 'admin') ? 1 : $product->user_id,
            'seller_is' => $product['added_by'],
            'created_at' => now(),
            'updated_at' => now(),
            'shop_info' => $product->added_by == 'admin' ? getWebConfig(name: 'company_name') : Shop::where(['seller_id' => $product->user_id])->first()->name,
            'shipping_cost' => $product->product_type == 'physical' ? CartManager::get_shipping_cost_for_product_category_wise($product, $request['quantity']) : 0,
            'shipping_type' => $shippingType,
            'is_guest' => ($user == 'offline' ? 1 : 0),
            'installtion_charges' => $extraChargeData['installation_charge'],
            'exchange_qty' => $extraChargeData['exchange_qty'],
            'exchange_charges' => $extraChargeData['exchange_charge'],
        ];

        $cartCheck = Cart::where(['customer_id' => $customerId, 'is_guest' => $isGuest, 'seller_id' => ($product->added_by == 'admin') ? 1 : $product->user_id, 'seller_is' => $product->added_by])->first();
        if ($cartCheck) {
            $cartArray['cart_group_id'] = $cartCheck['cart_group_id'];
        } else {
            $cartArray['cart_group_id'] = self::resolveCartGroupIdForOwner(
                customerId: $customerId,
                isGuest: $isGuest,
                sellerId: ($product->added_by == 'admin') ? 1 : (int) $product->user_id,
                sellerIs: (string) $product['added_by']
            );
        }

        $cart = Cart::where(['product_id' => $request['id'], 'customer_id' => $customerId, 'is_guest' => $isGuest, 'variant' => $string])->first();
        if ($cart) {
            $cartArray['cart_group_id'] = $cart['cart_group_id'];
            Cart::where(['id' => $cart['id']])->update($cartArray);
        } else {
            $cartID = Cart::insertGetId($cartArray);
            $cart = Cart::where(['id' => $cartID])->first();
        }

        if ($request['buy_now'] == 1) {
            $calculateTax = $product['tax_model'] == 'exclude' ? ($tax * $request['quantity']) : 0;
            $productTotalPrice = (($price - $getProductDiscount) * $request['quantity'])
                + $calculateTax
                + $extraChargeData['installation_charge']
                - ($extraChargeData['exchange_charge'] * $extraChargeData['exchange_qty']);
            $verifyStatus = OrderManager::checkSingleProductMinimumOrderAmountVerify(request: $request, product: $product, totalAmount: $productTotalPrice);
            if ($verifyStatus['status'] == 0) {
                return ['status' => 0, 'message' => $verifyStatus['message']];
            }

            Cart::where(['customer_id' => ($user == 'offline' ? $guestId : $user->id), 'is_guest' => ($user == 'offline' ? 1 : 0)])
                ->update(['is_checked' => 0]);

            Cart::where(['id' => $cart['id']])->update(['is_checked' => 1]);

            if ($product['product_type'] == 'digital') {
                return [
                    'status' => 1,
                    'redirect_to' => 'checkout',
                    'cart' => $cart,
                    'message' => translate('successfully_added') . '!',
                ];
            }

            if ($product['product_type'] == 'physical' && $shippingType == 'order_wise') {
                if ($request['shipping_method_exist'] && $request['shipping_method_id'] && count($sellerShippingList) > 0) {
                    $cart->update(['is_checked' => 1]);
                    $cartGroupIds = Cart::where(['customer_id' => ($user == 'offline' ? $guestId : $user->id), 'is_guest' => ($user == 'offline' ? 1 : 0)])
                        ->pluck('cart_group_id');
                    if (count($cartGroupIds) > 0) {
                        CartShipping::whereIn('cart_group_id', $cartGroupIds)->delete();
                    }

                    $shipping = CartShipping::where(['cart_group_id' => $cart['cart_group_id']])->first();
                    if (!isset($shipping)) {
                        $shipping = new CartShipping();
                    }
                    $getShippingCost = ShippingMethod::find($request['shipping_method_id']);
                    if (!$getShippingCost) {
                        return ['status' => 0, 'message' => translate('Selected_shipping_method_not_found')];
                    }
                    $shipping['cart_group_id'] = $cart['cart_group_id'];
                    $shipping['shipping_method_id'] = $request['shipping_method_id'];
                    $shipping['shipping_cost'] = $getShippingCost->cost ?? 0;
                    $shipping->save();

                    $cart['free_delivery_order_amount'] = OrderManager::getFreeDeliveryOrderAmountArray($cart['cart_group_id']);

                    return [
                        'status' => 1,
                        'redirect_to' => 'checkout',
                        'cart' => $cart,
                        'cart_shipping_cost' => $getShippingCost->cost ?? 0,
                        'message' => translate('successfully_added') . '!',
                    ];
                }
                return [
                    'status' => $sellerShippingList && count($sellerShippingList) > 0 ? 2 : 0,
                    'message' => $sellerShippingList && count($sellerShippingList) > 0 ? translate('Please_select_shipping_method') : translate('Shipping_Not_Available_for_this_Shop'),
                    'shipping_method_list' => $sellerShippingList,
                ];
            } elseif ($product['product_type'] == 'physical' && ($shippingType == 'category_wise' || $shippingType == 'product_wise')) {
                $cart->update([
                    'is_checked' => 1,
                    'shipping_cost' => CartManager::get_shipping_cost_for_product_category_wise($product, $request->quantity) ?? 0,
                ]);

                $cart['free_delivery_order_amount'] = OrderManager::getFreeDeliveryOrderAmountArray($cart['cart_group_id']);

                return [
                    'status' => 1,
                    'redirect_to' => 'checkout',
                    'cart' => $cart,
                    'cart_shipping_cost' => $getShippingCost->cost ?? 0,
                    'message' => translate('successfully_added') . '!',
                ];
            }
            $cart->update(['is_checked' => 1]);
        }

        if ($product->product_type == 'physical') {
            $cart['free_delivery_order_amount'] = OrderManager::getFreeDeliveryOrderAmountArray($cart['cart_group_id']);
        }

        return [
            'status' => 1,
            'in_cart_key' => $cart['id'],
            'cart' => $cart,
            'message' => translate('successfully_added') . '!',
            'product_variant_type' => count(json_decode($product['variation'], true)) > 0 ? 'multi_variant' : 'single_variant',
        ];
    }

    public static function addToCartDigitalProduct($request, $product, $shippingType, $sellerShippingList): array
    {
        if ($product['minimum_order_qty'] > $request['quantity']) {
            return ['status' => 0, 'message' => translate('Minimum_order_quantity') . ' ' . $product['minimum_order_qty']];
        }

        $price = $product->unit_price;
        $digitalVariation = DigitalProductVariation::where(['product_id' => $product['id'], 'variant_key' => $request['variant_key']])->first();
        if ($request['variant_key'] && $digitalVariation) {
            $price = $digitalVariation['price'];
        }
        $user = Helpers::getCustomerInformation($request);
        $guestId = session('guest_id') ?? ($request->guest_id ?? 0);

        if ($user == 'offline') {
            $customerId = $guestId;
            $isGuest = 1;
        } else {
            $customerId = $user->id;
            $isGuest = 0;
        }

        $getProductDiscount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $price);
        $taxablePrice = max(0, $price - $getProductDiscount);
        $tax = Helpers::tax_calculation(product: $product, price: $taxablePrice, tax: $product['tax'], tax_type: 'percent');
        $extraChargeData = self::resolveRequestedExtraCharges(
            product: $product,
            request: $request,
            quantity: (int)$request['quantity']
        );
        if ($extraChargeData['status'] === 0) {
            return $extraChargeData;
        }
        $cartArray = [
            'customer_id' => $customerId,
            'product_id' => $request['id'],
            'product_type' => $product['product_type'],
            'digital_product_type' => $product['digital_product_type'],
            'choices' => json_encode([]),
            'variations' => json_encode([]),
            'variant' => $request['variant_key'],
            'quantity' => $request['quantity'],
            'price' => $price,
            'tax' => $tax,
            'tax_model' => $product['tax_model'],
            'discount' => $getProductDiscount,
            'is_checked' => 1,
            'slug' => $product['slug'],
            'name' => $product['name'],
            'thumbnail' => $product['thumbnail'],
            'seller_id' => ($product->added_by == 'admin') ? 1 : $product->user_id,
            'seller_is' => $product['added_by'],
            'created_at' => now(),
            'updated_at' => now(),
            'shop_info' => $product->added_by == 'admin' ? getWebConfig(name: 'company_name') : Shop::where(['seller_id' => $product->user_id])->first()->name,
            'shipping_cost' => $product['product_type'] == 'physical' ? CartManager::get_shipping_cost_for_product_category_wise($product, $request['quantity']) : 0,
            'shipping_type' => $shippingType,
            'is_guest' => $isGuest,
            'installtion_charges' => $extraChargeData['installation_charge'],
            'exchange_qty' => $extraChargeData['exchange_qty'],
            'exchange_charges' => $extraChargeData['exchange_charge'],
        ];

        $cartCheck = Cart::where(['customer_id' => $customerId, 'is_guest' => $isGuest, 'seller_id' => ($product->added_by == 'admin') ? 1 : $product->user_id, 'seller_is' => $product->added_by])->first();
        if ($cartCheck) {
            $cartArray['cart_group_id'] = $cartCheck['cart_group_id'];
        } else {
            $cartArray['cart_group_id'] = self::resolveCartGroupIdForOwner(
                customerId: $customerId,
                isGuest: $isGuest,
                sellerId: ($product->added_by == 'admin') ? 1 : (int) $product->user_id,
                sellerIs: (string) $product['added_by']
            );
        }

        $cart = Cart::where(['product_id' => $request->id, 'customer_id' => $customerId, 'is_guest' => $isGuest, 'variant' => $request['variant_key']])->first();
        if ($cart) {
            Cart::where(['id' => $cart['id']])->update($cartArray);
        } else {
            $cartID = Cart::insertGetId($cartArray);
            $cart = Cart::where(['id' => $cartID])->first();
        }

        if ($request['buy_now'] == 1) {
            $calculateTax = $product['tax_model'] == 'exclude' ? ($tax * $request['quantity']) : 0;
            $productTotalPrice = (($price - $getProductDiscount) * $request['quantity'])
                + $calculateTax
                + $extraChargeData['installation_charge']
                - ($extraChargeData['exchange_charge'] * $extraChargeData['exchange_qty']);
            $verifyStatus = OrderManager::checkSingleProductMinimumOrderAmountVerify(request: $request, product: $product, totalAmount: $productTotalPrice);
            if ($verifyStatus['status'] == 0) {
                return ['status' => 0, 'message' => $verifyStatus['message']];
            }

            Cart::where(['customer_id' => ($user == 'offline' ? $guestId : $user->id), 'is_guest' => ($user == 'offline' ? 1 : 0)])
                ->update(['is_checked' => 0]);

            Cart::where(['id' => $cart['id']])->update(['is_checked' => 1]);

            if ($product['product_type'] == 'digital') {
                return [
                    'status' => 1,
                    'redirect_to' => 'checkout',
                    'cart' => $cart,
                    'message' => translate('successfully_added') . '!',
                ];
            }
        }

        return [
            'status' => 1,
            'in_cart_key' => $cart['id'],
            'cart' => $cart,
            'message' => translate('successfully_added') . '!',
            'product_variant_type' => count(json_decode($product['variation'], true)) > 0 ? 'multi_variant' : 'single_variant',
        ];
    }

    private static function resolveRequestedExtraCharges(Product $product, $request, int $quantity): array
    {
        $resolvedExtraCharges = app(ProductExtraChargeResolverService::class)->resolveForProduct($product);
        $resolvedInstallationCharge = max(0, (float)($resolvedExtraCharges['installation'] ?? 0));
        $resolvedExchangeCharge = max(0, (float)($resolvedExtraCharges['exchange'] ?? 0));

        $isInstallationRequested = max(0, (float)$request->input('installation_charge', 0)) > 0;
        $installationCharge = ($isInstallationRequested && $resolvedInstallationCharge > 0)
            ? $resolvedInstallationCharge
            : 0.0;

        $exchangeQuantity = max(0, (int)$request->input('exchange_quantity', 0));
        $exchangeCharge = 0.0;
        $isReplacementDiscountEnabled = (int)$request->input('replacement_discount_enabled', 0) === 1
            && $resolvedExchangeCharge > 0;

        if ($isReplacementDiscountEnabled) {
            if ($exchangeQuantity < 1) {
                return [
                    'status' => 0,
                    'message' => translate('Exchange qty must be at least 1 when Replacement Discount is enabled.'),
                ];
            }

            if ($exchangeQuantity > $quantity) {
                return [
                    'status' => 0,
                    'message' => translate('Exchange qty cannot exceed product quantity.'),
                ];
            }

            $exchangeCharge = $resolvedExchangeCharge;
        } else {
            $exchangeQuantity = 0;
        }

        return [
            'status' => 1,
            'installation_charge' => $installationCharge,
            'exchange_charge' => $exchangeCharge,
            'exchange_qty' => $exchangeQuantity,
        ];
    }

    public static function add_to_cart($request, $from_api = false): array
    {
        $product = Product::with(['digitalVariation', 'clearanceSale' => function ($query) {
            return $query->active();
        }])->where(['id' => $request['id']])->first();

        $shippingMethod = getWebConfig(name: 'shipping_method');
        $adminShipping = ShippingType::where('seller_id', 0)->first();
        $sellerShippingList = null;
        if ($shippingMethod == 'inhouse_shipping') {
            $shippingType = isset($adminShipping) == true ? $adminShipping->shipping_type : 'order_wise';
            $sellerShippingList = $shippingType == 'order_wise' ? ShippingMethod::where(['status' => 1])->where(['creator_type' => 'admin'])->get() : null;
        } else {
            if ($product->added_by == 'admin') {
                $shippingType = isset($adminShipping) == true ? $adminShipping->shipping_type : 'order_wise';
                $sellerShippingList = $shippingType == 'order_wise' ? ShippingMethod::where(['status' => 1])->where(['creator_type' => 'admin'])->get() : null;
            } else {
                $sellerShipping = ShippingType::where('seller_id', $product['user_id'])->first();
                $shippingType = isset($sellerShipping) == true ? $sellerShipping->shipping_type : 'order_wise';
                $sellerShippingList = ShippingMethod::where(['status' => 1])->where(['creator_id' => $product->user_id, 'creator_type' => 'seller'])->get();
            }
        }



        if ($product['product_type'] == 'digital') {
            return self::addToCartDigitalProduct($request, $product, $shippingType, $sellerShippingList);
        } else {
            return self::addToCartPhysicalProduct($request, $product, $shippingType, $sellerShippingList);
        }
    }

    public static function update_cart_qty($request): array
    {
        $requestedQuantity = filter_var($request->quantity, FILTER_VALIDATE_INT);
        if ($requestedQuantity === false || $requestedQuantity < 1) {
            return [
                'status' => 0,
                'qty' => $request['quantity'],
                'message' => translate('product_quantity_can_not_be_zero_or_less_than_zero_in_cart'),
            ];
        }

        $context = self::resolveCartOwnerContext($request);
        if ($context['customer_id'] < 1) {
            return [
                'status' => 0,
                'qty' => $request['quantity'],
                'message' => translate('Product_not_found_in_cart'),
            ];
        }

        return DB::transaction(function () use ($request, $context, $requestedQuantity) {
            $cart = self::getOwnedCartQuery($request)
                ->where('id', $request->key)
                ->lockForUpdate()
                ->first();

            if (!$cart) {
                return [
                    'status' => 0,
                    'qty' => $request['quantity'],
                    'message' => translate('Product_not_found_in_cart'),
                ];
            }

            $product = Product::query()->lockForUpdate()->find($cart['product_id']);
            if (!$product) {
                return [
                    'status' => 0,
                    'qty' => $cart['quantity'],
                    'message' => translate('Product_not_found_in_cart'),
                ];
            }

            if ((int)($cart['exchange_qty'] ?? 0) > $requestedQuantity) {
                return [
                    'status' => 0,
                    'qty' => $cart['quantity'],
                    'message' => translate('Exchange qty cannot exceed product quantity.'),
                ];
            }

            if (
                $product->product_type === 'physical'
                && (int)getWebConfig(name: 'stock_check') === 1
                && self::getAvailableCartStock($product, (string)($cart['variant'] ?? '')) < $requestedQuantity
            ) {
                return [
                    'status' => 0,
                    'qty' => $cart['quantity'],
                    'message' => translate('sorry_stock_is_limited'),
                ];
            }

            $cart['quantity'] = $requestedQuantity;
            self::refreshCartItemPricing($cart, $product, false);
            $cart->save();

            if ((int)($request['buy_now'] ?? 0) === 1) {
                self::getOwnedCartQuery($request)->update(['is_checked' => 0]);
                self::getOwnedCartQuery($request)
                    ->where('id', $cart->id)
                    ->update(['is_checked' => 1]);
            }

            return [
                'status' => 1,
                'qty' => $requestedQuantity,
                'message' => translate('successfully_updated!'),
            ];
        });
    }

    public static function update_installtion_charges($request): array
    {
        $charges = is_numeric($request->charges ?? null) ? (float)$request->charges : null;
        if ($charges === null || $charges < 0) {
            return [
                'status' => 0,
                'charges' => $request['charges'],
                'message' => translate('installtion_charges_not_updated'),
            ];
        }

        $cart = self::getOwnedCartQuery($request)
            ->where('id', $request->cart_id)
            ->first();

        if (!$cart) {
            return [
                'status' => 0,
                'charges' => $request['charges'],
                'message' => translate('Product_not_found_in_cart'),
            ];
        }

        self::refreshCartItemPricing($cart);

        $maxAllowedCharges = max(0, ((float)$cart['price']) * ((int)$cart['quantity']));
        if ($charges > $maxAllowedCharges) {
            return [
                'status' => 0,
                'charges' => $cart['installtion_charges'],
                'message' => translate('installation_charges_can_not_exceed_product_total'),
            ];
        }

        $cart['installtion_charges'] = $charges;
        $cart->save();

        return [
            'status' => 1,
            'charges' => $charges,
            'message' => translate('successfully_updated!'),
        ];
    }

    public static function update_exchange_charges($request): array
    {
        $cart = self::getOwnedCartQuery($request)
            ->where('id', $request->cart_id)
            ->first();

        if (!$cart) {
            return [
                'status' => 0,
                'qty' => $request['qty'],
                'charges' => $request['charges'],
                'message' => translate('Product_not_found_in_cart'),
            ];
        }

        self::refreshCartItemPricing($cart);

        $requestedQty = (int)$request->qty;
        $requestedCharges = max(0, (float)$request->charges);
        $productQty = max(0, (int)$cart['quantity']);
        $maxAllowedCharges = max(0, ((float)$cart['price']) * $productQty);

        if ($requestedQty < 0) {
            return [
                'status' => 0,
                'qty' => $cart['exchange_qty'],
                'charges' => $cart['exchange_charges'],
                'message' => translate('Exchange qty cannot be negative.'),
            ];
        }

        if ($requestedQty > $productQty) {
            return [
                'status' => 0,
                'qty' => $cart['exchange_qty'],
                'charges' => $cart['exchange_charges'],
                'message' => translate('Exchange qty cannot exceed product quantity.'),
            ];
        }

        if ($requestedCharges > 0 && $requestedQty < 1) {
            return [
                'status' => 0,
                'qty' => $cart['exchange_qty'],
                'charges' => $cart['exchange_charges'],
                'message' => translate('Exchange qty must be at least 1 when Replacement Discount is enabled.'),
            ];
        }

        if ($requestedCharges > $maxAllowedCharges) {
            return [
                'status' => 0,
                'qty' => $cart['exchange_qty'],
                'charges' => $cart['exchange_charges'],
                'message' => translate('exchange_charges_can_not_exceed_product_total'),
            ];
        }

        $qty = max(0, $requestedQty);
        $charges = $qty > 0 ? $requestedCharges : 0;
        $cart['exchange_charges'] = $charges;
        $cart['exchange_qty'] = $qty;
        $cart->save();

        return [
            'status' => 1,
            'charges' => $charges,
            'qty' => $qty,
            'message' => translate('successfully_updated!')
        ];
    }

    private static function getAvailableCartStock(Product $product, string $variant): int
    {
        $availableStock = max(0, (int)($product->current_stock ?? 0));
        $variant = trim($variant);

        if ($variant === '' || empty($product->variation)) {
            return $availableStock;
        }

        $variantMatcher = new VariantMatcher();
        foreach (json_decode($product->variation ?? '[]') as $variation) {
            if ($variantMatcher->matches($variation->type ?? null, $variant)) {
                return max(0, (int)($variation->qty ?? 0));
            }
        }

        return $availableStock;
    }

    public static function get_shipping_cost_for_product_category_wise($product, $qty)
    {
        $shippingMethod = getWebConfig(name: 'shipping_method');
        $cost = 0;

        if ($shippingMethod == 'inhouse_shipping') {
            $admin_shipping = ShippingType::where('seller_id', 0)->first();
            $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
        } else {
            if ($product->added_by == 'admin') {
                $admin_shipping = ShippingType::where('seller_id', 0)->first();
                $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
            } else {
                $seller_shipping = ShippingType::where('seller_id', $product->user_id)->first();
                $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
            }
        }
        if ($shipping_type == 'area_wise') {
            return 0.00;
        }

        if ($shipping_type == 'order_wise') {
            // If the type is order_wise, we return 0.00 immediately.
            // This prevents per-product shipping costs from stacking up.
            return 0.00;
        }

        if ($shipping_type == 'category_wise') {
            $categoryID = 0;
            foreach (json_decode($product->category_ids) as $ct) {
                if ($ct->position == 1) {
                    $categoryID = $ct->id;
                }
            }

            if ($shippingMethod == 'inhouse_shipping') {
                $category_shipping_cost = CategoryShippingCost::where('seller_id', 0)->where('category_id', $categoryID)->first();
            } else {
                if ($product->added_by == 'admin') {
                    $category_shipping_cost = CategoryShippingCost::where('seller_id', 0)->where('category_id', $categoryID)->first();
                } else {
                    $category_shipping_cost = CategoryShippingCost::where('seller_id', $product->user_id)->where('category_id', $categoryID)->first();
                }
            }

            if (isset($category_shipping_cost->multiply_qty) && $category_shipping_cost->multiply_qty == 1) {
                $cost = $qty * $category_shipping_cost->cost;
            } else {
                $cost = $category_shipping_cost->cost ?? 0;
            }
        } else if ($shipping_type == 'product_wise') {
            // For product_wise shipping, check product's multiply_qty
            if ($product->multiply_qty == 1) {
                $cost = $qty * $product->shipping_cost;
            } else {
                $cost = $product->shipping_cost;
            }
        } else {
            // For order_wise shipping
            // First check if shipping should be multiplied by quantity
            if ($product->multiply_qty == 1) {
                // Multiply shipping cost by quantity
                $cost = $qty * ($product->shipping_cost ?? 0);

                // If product shipping cost is 0, try to get from shipping methods
                if ($cost == 0 && $product->free_shipping != 1) {
                    if ($product->added_by == 'admin') {
                        $shipping_method = ShippingMethod::where([
                            'creator_id' => 1,
                            'creator_type' => 'admin',
                            'status' => 1
                        ])->first();
                    } else {
                        $shipping_method = ShippingMethod::where([
                            'creator_id' => $product->user_id,
                            'creator_type' => 'seller',
                            'status' => 1
                        ])->first();
                    }

                    if ($shipping_method) {
                        // Also check if shipping method has multiply_qty
                        if (isset($shipping_method->multiply_qty) && $shipping_method->multiply_qty == 1) {
                            $cost = $qty * $shipping_method->cost;
                        } else {
                            $cost = $shipping_method->cost;
                        }
                    }
                }
            } else {
                // Don't multiply by quantity
                $cost = $product->shipping_cost ?? 0;

                // If product shipping cost is 0, try to get from shipping methods
                if ($cost == 0 && $product->free_shipping != 1) {
                    if ($product->added_by == 'admin') {
                        $shipping_method = ShippingMethod::where([
                            'creator_id' => 1,
                            'creator_type' => 'admin',
                            'status' => 1
                        ])->first();
                    } else {
                        $shipping_method = ShippingMethod::where([
                            'creator_id' => $product->user_id,
                            'creator_type' => 'seller',
                            'status' => 1
                        ])->first();
                    }

                    if ($shipping_method) {
                        $cost = $shipping_method->cost;
                    }
                }
            }
        }

        return $cost;
    }
    // public static function get_shipping_cost_for_product_category_wise($product, $qty)
    // {
    //     $shippingMethod = getWebConfig(name: 'shipping_method');
    //     $cost = 0;

    //     if ($shippingMethod == 'inhouse_shipping') {
    //         $admin_shipping = ShippingType::where('seller_id', 0)->first();
    //         $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
    //     } else {
    //         if ($product->added_by == 'admin') {
    //             $admin_shipping = ShippingType::where('seller_id', 0)->first();
    //             $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
    //         } else {
    //             $seller_shipping = ShippingType::where('seller_id', $product->user_id)->first();
    //             $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
    //         }
    //     }

    //     if ($shipping_type == 'category_wise') {
    //         $categoryID = 0;
    //         foreach (json_decode($product->category_ids) as $ct) {
    //             if ($ct->position == 1) {
    //                 $categoryID = $ct->id;
    //             }
    //         }

    //         if ($shippingMethod == 'inhouse_shipping') {
    //             $category_shipping_cost = CategoryShippingCost::where('seller_id', 0)->where('category_id', $categoryID)->first();
    //         } else {
    //             if ($product->added_by == 'admin') {
    //                 $category_shipping_cost = CategoryShippingCost::where('seller_id', 0)->where('category_id', $categoryID)->first();
    //             } else {
    //                 $category_shipping_cost = CategoryShippingCost::where('seller_id', $product->user_id)->where('category_id', $categoryID)->first();
    //             }
    //         }

    //         if (isset($category_shipping_cost->multiply_qty) && $category_shipping_cost->multiply_qty == 1) {
    //             $cost = $qty * $category_shipping_cost->cost;
    //         } else {
    //             $cost = $category_shipping_cost->cost ?? 0;
    //         }
    //     } else if ($shipping_type == 'product_wise') {
    //         if ($product->multiply_qty == 1) {
    //             $cost = $qty * $product->shipping_cost;
    //         } else {
    //             $cost = $product->shipping_cost;
    //         }
    //     } else {
    //         $cost = 0;
    //     }

    //     return $cost;
    // }

    public static function getShippingCostSavedForFreeDelivery($groupId = null, $type = null)
    {
        $costSaved = 0;

        $cartGroup = Cart::where(['product_type' => 'physical'])
            ->whereHas('product', function ($query) {
                return $query->active();
            })->when($groupId != null, function ($query) use ($groupId) {
                return $query->where('cart_group_id', $groupId);
            })
            ->when(($groupId == null && $type != 'checked'), function ($query) {
                return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids());
            })
            ->when(($groupId == null && $type == 'checked'), function ($query) {
                return $query->whereIn('cart_group_id', CartManager::get_cart_group_ids(type: 'checked'));
            })
            ->when($type == 'checked', function ($query) {
                return $query->where(['is_checked' => 1]);
            })->get()->groupBy('cart_group_id');

        foreach ($cartGroup as $cart) {
            if ($cart->count() > 0) {
                $freeDeliveryCheck = OrderManager::getFreeDeliveryOrderAmountArray($cart[0]->cart_group_id);
                $costSaved += $freeDeliveryCheck['shipping_cost_saved'];
            }
        }

        return $costSaved;
    }

    public static function product_stock_check($carts): bool
    {
        $context = new StockValidationContext(
            channel: StockChannel::RETAIL,
            deliveryType: 'delivery',
        );
        return self::validateStockWithFeatureFlag($carts, $context);
    }

    public static function product_stock_check_by_branch($carts, ?int $branchId): bool
    {
        $context = new StockValidationContext(
            channel: StockChannel::RETAIL,
            deliveryType: 'pickup',
            branchId: (int)$branchId,
        );
        return self::validateStockWithFeatureFlag($carts, $context);
    }

    public static function resolveFulfillmentBranchId($request = null): int
    {
        $deliveryType = $request ? ($request['delivery_type'] ?? null) : null;
        if (!$deliveryType && session()->has('delivery_type')) {
            $deliveryType = session('delivery_type');
        }

        if ($deliveryType === 'pickup') {
            $pickupBranchId = (int)($request ? ($request['pickup_branch_id'] ?? null) : null);
            if ($pickupBranchId <= 0) {
                $pickupBranchId = (int)(session('pickup_branch_id') ?? 0);
            }
            if ($pickupBranchId > 0) {
                return $pickupBranchId;
            }
        }

        $candidates = [
            $request ? ($request['transfer_from_branch'] ?? null) : null,
            $request ? ($request['nearest_branch'] ?? null) : null,
            session('nearest_branch'),
            session('pickup_branch_id'),
        ];

        foreach ($candidates as $candidate) {
            $branchId = (int)$candidate;
            if ($branchId > 0) {
                return $branchId;
            }
        }

        return 1;
    }

    public static function resolvePickupBranchIdForStockCheck($request = null): int
    {
        $pickupBranchId = (int)($request ? ($request['pickup_branch_id'] ?? null) : 0);
        if ($pickupBranchId <= 0) {
            $pickupBranchId = (int)(session('pickup_branch_id') ?? 0);
        }

        return $pickupBranchId > 0 ? $pickupBranchId : 0;
    }

    private static function validateStockWithFeatureFlag($carts, StockValidationContext $context): bool
    {
        $refactorEnabled = self::isStockValidationRefactorEnabled();
        $mirrorModeEnabled = self::isStockValidationMirrorModeEnabled();

        if ($mirrorModeEnabled) {
            $service = new StockAvailabilityService();
            $refactorResult = $service->validate($carts, $context)->passed();
            $legacyResult = self::legacyProductStockCheck($carts, $context);

            if ($refactorResult !== $legacyResult) {
                self::incrementMetricCounter('stock_check_mirror_mismatch_total');
                Log::warning('stock_check_mirror_mismatch', [
                    'channel' => $context->channel->value,
                    'delivery_type' => $context->deliveryType,
                    'branch_id' => $context->branchId,
                    'legacy_result' => $legacyResult,
                    'refactor_result' => $refactorResult,
                ]);
            }

            // Mirror mode enforces legacy result while comparing against refactor checker.
            return $legacyResult;
        }

        if ($refactorEnabled) {
            $service = new StockAvailabilityService();
            return $service->validate($carts, $context)->passed();
        }

        return self::legacyProductStockCheck($carts, $context);
    }

    private static function isStockValidationRefactorEnabled(): bool
    {
        $raw = getWebConfig(name: 'stock_validation_refactor_enabled');
        if (is_null($raw) || $raw === '') {
            return true;
        }

        return (int)$raw === 1;
    }

    private static function isStockValidationMirrorModeEnabled(): bool
    {
        $raw = getWebConfig(name: 'stock_validation_refactor_mirror_mode');
        if (is_null($raw) || $raw === '') {
            return false;
        }

        return (int)$raw === 1;
    }

    private static function legacyProductStockCheck($carts, StockValidationContext $context): bool
    {
        if ((int)getWebConfig(name: 'stock_check') !== 1) {
            return true;
        }

        foreach ($carts as $cart) {
            if (self::isWholesaleCartGroup($cart)) {
                continue;
            }

            $product = $cart->product ?? Product::find($cart->product_id ?? 0);
            if (!$product || $product->product_type !== 'physical') {
                continue;
            }

            $requiredQty = max(0, (int)($cart->quantity ?? 0));
            if ($requiredQty <= 0) {
                continue;
            }

            $variant = self::extractVariantFromCart($cart);
            $availableQty = $context->deliveryType === 'pickup'
                ? self::legacyAvailableBranchQty(
                    productId: (int)$product->id,
                    branchId: (int)($context->branchId ?? 0),
                    variant: $variant
                )
                : self::legacyAvailableGlobalQty($product, $variant);

            if ($availableQty < $requiredQty) {
                return false;
            }
        }

        return true;
    }

    private static function legacyAvailableGlobalQty(Product $product, ?string $variant): int
    {
        $variationRows = json_decode((string)($product->variation ?? '[]'), true);
        $hasVariationRows = is_array($variationRows) && count($variationRows) > 0;

        if ($hasVariationRows && !is_null($variant)) {
            foreach ($variationRows as $variationRow) {
                if (trim((string)($variationRow['type'] ?? '')) === $variant) {
                    return max(0, (int)($variationRow['qty'] ?? 0));
                }
            }
            return 0;
        }

        return max(0, (int)($product->current_stock ?? 0));
    }

    private static function legacyAvailableBranchQty(int $productId, int $branchId, ?string $variant): int
    {
        if ($branchId <= 0) {
            return 0;
        }

        $query = ManageBranchProductStock::query()
            ->where('branch_id', $branchId)
            ->where('product_id', $productId);

        if (!is_null($variant)) {
            $query->where(function ($stockQuery) use ($variant) {
                $stockQuery->where('variation_type', $variant)
                    ->orWhere('variation_key', $variant);
            });
        } else {
            $query->where(function ($stockQuery) {
                $stockQuery->whereNull('variation_type')->orWhere('variation_type', '');
            });
        }

        return max(0, (int)($query->value('current_stock') ?? 0));
    }

    private static function extractVariantFromCart(mixed $cart): ?string
    {
        $rawVariant = null;
        if (is_array($cart)) {
            $rawVariant = $cart['variant'] ?? null;
        } elseif (is_object($cart)) {
            $rawVariant = $cart->variant ?? null;
        }

        $variant = trim((string)($rawVariant ?? ''));
        if ($variant === '') {
            return null;
        }

        if (str_starts_with($variant, '{')) {
            $decoded = json_decode($variant, true);
            $type = trim((string)($decoded['type'] ?? ''));
            return $type !== '' ? $type : null;
        }

        return $variant;
    }

    private static function isWholesaleCartGroup(mixed $cart): bool
    {
        $cartGroupId = '';
        if (is_array($cart)) {
            $cartGroupId = (string)($cart['cart_group_id'] ?? '');
        } elseif (is_object($cart)) {
            $cartGroupId = (string)($cart->cart_group_id ?? '');
        }

        if ($cartGroupId === '') {
            return false;
        }

        $cartGroupId = strtolower(trim($cartGroupId));
        return str_starts_with($cartGroupId, 'wh-') || str_starts_with($cartGroupId, 'wholesale_');
    }

    private static function incrementMetricCounter(string $metricKey, int $amount = 1): void
    {
        if ($amount <= 0) {
            return;
        }

        try {
            if (!Cache::has($metricKey)) {
                Cache::forever($metricKey, 0);
            }
            Cache::increment($metricKey, $amount);
        } catch (\Throwable $exception) {
            Log::debug('stock_metric_increment_failed', [
                'metric_key' => $metricKey,
                'error' => $exception->getMessage(),
            ]);
        }
    }

}
