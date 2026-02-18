<?php

namespace App\Utils;

use App\Models\DigitalProductVariation;
use App\Models\ShippingMethod;
use App\Utils\Helpers;
use App\Models\Cart;
use App\Models\CartShipping;
use App\Models\CategoryShippingCost;
use App\Models\Color;
use App\Models\ManageBranchProductStock;
use App\Models\Product;
use App\Models\ShippingType;
use App\Models\Shop;
use Illuminate\Support\Str;

class CartManager
{
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

                $cart->cart_group_id = isset($databaseCart) ? $databaseCart['cart_group_id'] : str_replace('guest', $user->id, $cart['cart_group_id']);
                $cart->customer_id = $user->id;
                $cart->is_guest = 0;
                $cart->save();
            }
        }
    }

    public static function getCartListQuery($groupId = null, $type = null)
    {


        return Cart::with(['product' => function ($query) {
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
            ->get()?->each(function ($item) {
                $item['discount'] = getProductPriceByType(product: $item['product'], type: 'discounted_amount', result: 'value', price: $item['price']);
            });
    }


    public static function getCartListGroupQuery($groupId = null, $type = null)
    {
        return Cart::with(['product' => function ($query) {
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
            ->get()?->each(function ($item) {
                $item['discount'] = getProductPriceByType(
                    product: $item['product'],
                    type: 'discounted_amount',
                    result: 'value',
                    price: $item['price']
                );

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
        return Cart::when(($groupId == null && $type != 'checked'), function ($query) use ($request) {
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
            $productIds = $cart->pluck('product_id')->filter()->unique()->values()->all();
            $products = Product::active()->whereIn('id', $productIds)->get()->keyBy('id');

            $cart->each(function ($item) use ($products) {
                $product = $products->get($item['product_id']);
                if ($product) {
                    $item->setRelation('product', $product);
                    $item['discount'] = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $item['price']);
                } else {
                    $item['discount'] = (float)($item['discount'] ?? 0);
                }
            });
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
            'shippingNotice' => $isAreaWiseShippingPending ? 'Cost will be determined later based on location.' : null,
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
        // dd($stockCheckStatus);
        if (($stockCheckStatus == 1) && ($product['product_type'] == 'physical') && ($product['current_stock'] < $request['quantity'])) {
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

        // Check the string and decreases quantity for the stock
        if (($stockCheckStatus == 1) && $string != null) {
            $count = count(json_decode($product->variation));
            for ($i = 0; $i < $count; $i++) {
                if (json_decode($product->variation)[$i]->type == $string) {
                    $price = json_decode($product->variation)[$i]->price;
                    if (json_decode($product->variation)[$i]->qty < $request['quantity']) {
                        return ['status' => 0, 'message' => translate('out_of_stock!')];
                    }
                }
            }
        } else {
            $price = $product->unit_price;
        }

        $getProductDiscount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $price);
        $taxablePrice = max(0, $price - $getProductDiscount);
        $tax = Helpers::tax_calculation(product: $product, price: $taxablePrice, tax: $product['tax'], tax_type: 'percent');

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
        ];

        $cartCheck = Cart::where(['customer_id' => $customerId, 'is_guest' => $isGuest, 'seller_id' => ($product->added_by == 'admin') ? 1 : $product->user_id, 'seller_is' => $product->added_by])->first();
        if ($cartCheck) {
            $cartArray['cart_group_id'] = $cartCheck['cart_group_id'];
        } else {
            $cartArray['cart_group_id'] = ($user == 'offline' ? 'guest' : $user->id) . '-' . Str::random(5) . '-' . time();
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
            $productTotalPrice = (($price - $getProductDiscount) * $request['quantity']) + $calculateTax;
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
        ];

        $cartCheck = Cart::where(['customer_id' => $customerId, 'is_guest' => $isGuest, 'seller_id' => ($product->added_by == 'admin') ? 1 : $product->user_id, 'seller_is' => $product->added_by])->first();
        if ($cartCheck) {
            $cartArray['cart_group_id'] = $cartCheck['cart_group_id'];
        } else {
            $cartArray['cart_group_id'] = ($user == 'offline' ? 'guest' : $user->id) . '-' . Str::random(5) . '-' . time();
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
            $productTotalPrice = (($price - $getProductDiscount) * $request['quantity']) + $calculateTax;
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
        $user = Helpers::getCustomerInformation($request);
        $guest_id = session('guest_id') ?? ($request->guest_id ?? 0);
        $status = 1;
        $qty = 0;
        $cart = Cart::where(['id' => $request->key, 'customer_id' => ($user == 'offline' ? $guest_id : $user->id)])->first();

        if (!$cart) {
            return [
                'status' => 0,
                'qty' => $request['quantity'],
                'message' => translate('Product_not_found_in_cart'),
            ];
        }

        $product = Product::find($cart['product_id']);
        $count = count(json_decode($product->variation));
        /* if ($count) {
            for ($i = 0; $i < $count; $i++) {
                if (json_decode($product->variation)[$i]->type == $cart['variant']) {
                    if (json_decode($product->variation)[$i]->qty < $request->quantity) {
                        $status = 0;
                        $qty = $cart['quantity'];
                    }
                }
            }
        } else if (($product['product_type'] == 'physical') && $product['current_stock'] < $request->quantity) {
            $status = 0;
            $qty = $cart['quantity'];
        }*/

        if ($status) {
            $qty = $request->quantity;
            $cart['quantity'] = $request->quantity;
            $cart['shipping_cost'] = $product->product_type == 'physical' ? CartManager::get_shipping_cost_for_product_category_wise($product, $request->quantity) : 0;
        }

        $cart->save();

        if ($request['buy_now'] == 1) {
            Cart::where(['customer_id' => ($user == 'offline' ? $guest_id : $user->id), 'is_guest' => ($user == 'offline' ? 1 : 0)])
                ->update(['is_checked' => 0]);
            Cart::where(['id' => $request->key, 'customer_id' => ($user == 'offline' ? $guest_id : $user->id)])->update(['is_checked' => 1]);
        }

        return [
            'status' => $status,
            'qty' => $qty,
            'message' => $status == 1 ? translate('successfully_updated!') : translate('sorry_stock_is_limited')
        ];
    }

    public static function update_installtion_charges($request): array
    {

        $user = Helpers::getCustomerInformation($request);
        $guest_id = session('guest_id') ?? ($request->guest_id ?? 0);
        $status = 1;
        $installtion_charge = 0;
        $cart = Cart::where(['id' => $request->cart_id, 'customer_id' => ($user == 'offline' ? $guest_id : $user->id)])->first();

        if (!$cart) {
            return [
                'status' => 0,
                'installtion_charge' => $request['charges'],
                'message' => translate('Product_not_found_in_cart'),
            ];
        }

        if ($status) {
            $charges = $request->charges;
            $cart['installtion_charges'] = $request->charges;
        }

        $cart->save();
        return [
            'status' => $status,
            'charges' => $charges,
            'message' => $status == 1 ? translate('successfully_updated!') : translate('installtion_charges_not_updated')
        ];
    }

    public static function update_exchange_charges($request): array
    {
        $user = Helpers::getCustomerInformation($request);
        $guest_id = session('guest_id') ?? ($request->guest_id ?? 0);
        $status = 1;
        $qty = 0;
        $cart = Cart::where(['id' => $request->cart_id, 'customer_id' => ($user == 'offline' ? $guest_id : $user->id)])->first();

        if (!$cart) {
            return [
                'status' => 0,
                'qty' => $request['qty'],
                'charges' => $request['charges'],
                'message' => translate('Product_not_found_in_cart'),
            ];
        }

        if ($status) {
            $qty = max(0, (int)$request->qty);
            $maxExchangeQty = max(0, (int)$cart['quantity']);
            $qty = min($qty, $maxExchangeQty);

            $charges = max(0, (float)$request->charges);
            if ($qty === 0) {
                $charges = 0;
            }

            $cart['exchange_charges'] = $charges;
            $cart['exchange_qty'] = $qty;
        }

        $cart->save();
        return [
            'status' => $status,
            'charges' => $charges,
            'qty' => $qty,
            'message' => $status == 1 ? translate('successfully_updated!') : translate('installtion_charges_not_updated')
        ];
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
        $status = true;
        $stockCheckStatus = getWebConfig(name: 'stock_check');
        foreach ($carts as $cart) {
            if ($cart->product) {
                $product = $cart->product;
                $count = count(json_decode($product->variation, true) ?? []);
                if ($count) {
                    for ($i = 0; $i < $count; $i++) {
                        if (json_decode($product->variation)[$i]->type == $cart['variant']) {
                            if ($stockCheckStatus == 1 && json_decode($product->variation)[$i]->qty < $cart->quantity) {
                                $status = false;
                            }
                        }
                    }
                } else if ($stockCheckStatus == 1 && ($product['product_type'] == 'physical') && $product['current_stock'] < $cart->quantity) {
                    $status = false;
                }
            } else {
                $status = false;
            }
        }
        return $status;
    }

    public static function product_stock_check_by_branch($carts, ?int $branchId): bool
    {
        $stockCheckStatus = getWebConfig(name: 'stock_check');
        if ($stockCheckStatus != 1) {
            return true;
        }

        $branchId = (int)$branchId;
        if ($branchId <= 0) {
            $branchId = self::resolveFulfillmentBranchId();
        }

        if ($branchId <= 0) {
            return false;
        }

        $requiredStock = [];
        foreach ($carts as $cart) {
            if (!$cart->product) {
                return false;
            }

            if ($cart->product_type !== 'physical') {
                continue;
            }

            $variantType = self::normalizeVariantType($cart['variant'] ?? null);
            $key = $cart['product_id'] . '|' . ($variantType ?? '__default__');

            if (!isset($requiredStock[$key])) {
                $requiredStock[$key] = [
                    'product_id' => (int)$cart['product_id'],
                    'variant_type' => $variantType,
                    'qty' => 0,
                ];
            }
            $requiredStock[$key]['qty'] += (int)$cart['quantity'];
        }

        foreach ($requiredStock as $requiredRow) {
            $stockQuery = ManageBranchProductStock::query()
                ->where('branch_id', $branchId)
                ->where('product_id', $requiredRow['product_id']);

            if (!is_null($requiredRow['variant_type'])) {
                $variantType = $requiredRow['variant_type'];
                $stockQuery->where(function ($query) use ($variantType) {
                    $query->where('variation_type', $variantType)
                        ->orWhere('variation_key', $variantType);
                });
            } else {
                $stockQuery->where(function ($query) {
                    $query->whereNull('variation_type')
                        ->orWhere('variation_type', '')
                        ->orWhere('variation_type', 'No Variation');
                });
            }

            $availableQty = (int)$stockQuery->sum('current_stock');
            if ($availableQty < (int)$requiredRow['qty']) {
                return false;
            }
        }

        return true;
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

    private static function normalizeVariantType(mixed $variant): ?string
    {
        if (is_null($variant)) {
            return null;
        }

        if (is_array($variant)) {
            $value = trim((string)($variant['type'] ?? ''));
            return $value !== '' ? $value : null;
        }

        $variantString = trim((string)$variant);
        if ($variantString === '' || strtolower($variantString) === 'null') {
            return null;
        }

        if (str_starts_with($variantString, '{')) {
            $decoded = json_decode($variantString, true);
            if (is_array($decoded)) {
                $decodedType = trim((string)($decoded['type'] ?? ''));
                return $decodedType !== '' ? $decodedType : null;
            }
        }

        return $variantString;
    }
}
