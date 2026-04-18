<?php

namespace App\Http\Controllers\Web;


use App\Domain\Stock\Support\VariantMatcher;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Events\RequestProductRestockEvent;
use App\Models\BusinessSetting;
use App\Models\CartShipping;
use App\Models\DigitalProductVariation;
use App\Models\RestockProductCustomer;
use App\Services\ProductExtraChargeResolverService;
use App\Models\Shop;
use App\Models\ShippingMethodArea;
use App\Services\RestockProductService;
use App\Utils\CustomerManager;
use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Color;
use App\Models\Coupon;
use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\Product;
use App\Utils\CartManager;
use App\Utils\OrderManager;
use App\Utils\ProductManager;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private OrderDetail                                        $order_details,
        private Product                                            $product,
        private readonly RestockProductService                     $restockProductService,
        private readonly RestockProductRepositoryInterface         $restockProductRepo,
        private readonly ProductRepositoryInterface                $productRepo,
        private readonly RestockProductCustomerRepositoryInterface $restockProductCustomerRepo,
    ) {}

    public function getVariantPrice(Request $request): array
    {
        $variantMatcher = new VariantMatcher();
        $variantsMatch = fn($left, $right) => $variantMatcher->matches($left, $right);

        $string = '';
        $quantity = 0;
        $price = 0;
        $unit_price = 0;
        $discount = 0;
        $tax = 0;
        $update_tax = 0;
        $discountedUnitPrice = 0;
        $color_name = '';
        $requestQuantity = $request['quantity'];
        $product = Product::with(['digitalVariation', 'clearanceSale' => function ($query) {
            return $query->active();
        }])->where(['id' => $request['id']])->first();
        $resolvedExtraCharges = app(ProductExtraChargeResolverService::class)->resolveForProduct($product);
        $productVariationCode = $request['product_variation_code'];

        if ($request->has('color')) {
            $string = Color::where('code', $request['color'])->first()->name;
        }

        $choiceOptions = json_decode(Product::find($request->id)->choice_options);
        foreach ($choiceOptions as $key => $choice) {
            $choiceValue = $request->input($choice->name);
            if (($choiceValue === null || $choiceValue === '') && !empty($choice->title)) {
                $title = strtolower(trim((string)$choice->title));
                $aliases = array_filter([
                    $title,
                    str_replace(' ', '_', $title),
                    preg_replace('/[^a-z0-9]+/', '_', $title),
                    preg_replace('/[^a-z0-9]+/', '', $title),
                ]);

                foreach (array_values(array_unique($aliases)) as $alias) {
                    $choiceValue = $request->input($alias);
                    if ($choiceValue !== null && $choiceValue !== '') {
                        break;
                    }
                }
            }

            if ($choiceValue === null || $choiceValue === '') {
                continue;
            }

            if ($string != null && $string !== '') {
                $string .= '-' . str_replace(' ', '', (string)$choiceValue);
            } else {
                $string .= str_replace(' ', '', (string)$choiceValue);
            }
        }

        $requestQuantity = $productVariationCode != $string ? $product['minimum_order_qty'] : $request['quantity'];
        $inCartExistStatus = 0;
        $inCartExistKey = null;
        $getCartList = CartManager::getCartListQuery();
        foreach ($getCartList as $cartItem) {
            if ($cartItem['product_id'] == $product['id'] && $variantsMatch($cartItem['variant'] ?? null, $string)) {
                $inCartExistStatus = 1;
                $inCartExistKey = $cartItem['id'];
                $requestQuantity = $productVariationCode == $string ? $request['quantity'] : $cartItem['quantity'];
            }

            if ($product['product_type'] == 'digital' && $request['variant_key'] && $cartItem['variant'] == $request['variant_key']) {
                $inCartExistStatus = 1;
                $inCartExistKey = $cartItem['id'];
                $requestQuantity = $productVariationCode == $request['variant_key'] ? $request['quantity'] : $cartItem['quantity'];
            }
        }

        if ($string != null) {
            $count = count(json_decode($product->variation));
            for ($i = 0; $i < $count; $i++) {
                $variationType = json_decode($product->variation)[$i]->type ?? null;
                if ($variantsMatch($variationType, $string)) {
                    $tax = $product->tax_model == 'exclude' ? Helpers::tax_calculation(product: $product, price: json_decode($product->variation)[$i]->price, tax: $product['tax'], tax_type: $product['tax_type']) : 0;
                    $update_tax = $tax * $requestQuantity;
                    $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: json_decode($product->variation)[$i]->price);
                    $price = json_decode($product->variation)[$i]->price - $discount + $tax;
                    $discountedUnitPrice = json_decode($product->variation)[$i]->price - $discount;
                    $unit_price = json_decode($product->variation)[$i]->price;
                    $quantity = json_decode($product->variation)[$i]->qty;
                }
            }
        } else {
            $tax = $product->tax_model == 'exclude' ? Helpers::tax_calculation(product: $product, price: $product->unit_price, tax: $product['tax'], tax_type: $product['tax_type']) : 0;
            $update_tax = $tax * $requestQuantity;
            $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $product->unit_price);
            $price = $product->unit_price - $discount + $tax;
            $discountedUnitPrice = $product->unit_price - $discount;
            $unit_price = $product->unit_price;
            $quantity = $product->current_stock;
        }

        $digitalVariation = DigitalProductVariation::where(['product_id' => $product['id'], 'variant_key' => $request['variant_key']])->first();
        if ($product['product_type'] == 'digital' && $digitalVariation) {
            $tax = $product['tax_model'] == 'exclude' ? Helpers::tax_calculation(product: $product, price: $digitalVariation['price'], tax: $product['tax'], tax_type: $product['tax_type']) : 0;
            $update_tax = $tax * $requestQuantity;
            $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $digitalVariation['price']);
            $price = $digitalVariation['price'] - $discount + $tax;
            $discountedUnitPrice = $digitalVariation['price'] - $discount;
            $unit_price = $digitalVariation['price'];
            $quantity = $digitalVariation['price'];

            foreach ($getCartList as $cartItem) {
                if ($product['product_type'] == 'digital' && $cartItem['variant'] == $request['variant_key']) {
                    $string = $cartItem['variant'];
                }
            }
        }

        $deliveryInfo = [];
        $stock_limit = 0;
        if (theme_root_path() == 'theme_fashion') {
            $deliveryInfo = ProductManager::get_products_delivery_charge($product, $requestQuantity);
            $stock_limit = BusinessSetting::where('type', 'stock_limit')->first()->value;
        }

        if ($request->has('color')) {
            $color_name = Color::where(['code' => $request->color])->first()->name;
        }

        $restockRequestStatus = 0;
        if (auth('customer')->check()) {
            $restockRequestStatus = (int)($this->restockProductRepo->getListWhere(filters: [
                'customer_id' => auth('customer')->id(),
                'product_id' => $product['id'],
                'variant' => $string,
            ])->count() > 0);
        }

        $discountType = getProductPriceByType(product: $product, type: 'discount_type', result: 'string');

        $stockCheckStatus = getWebConfig(name: 'stock_check');
        $availableQuantity = $product['product_type'] == 'physical' && $stockCheckStatus == 1
            ? max(0, (int)$quantity)
            : (int)DEFAULT_STOCK;
        $safeInCartQuantity = $product['product_type'] == 'physical' && $stockCheckStatus == 1
            ? min(max(0, (int)$requestQuantity), $availableQuantity)
            : (int)$requestQuantity;
        $installationCharge = max(0, (float)$request->input('installation_charge', 0)) > 0
            ? max(0, (float)($resolvedExtraCharges['installation'] ?? 0))
            : 0;
        $replacementDiscountEnabled = (int)$request->input('replacement_discount_enabled', 0) === 1
            && max(0, (float)($resolvedExtraCharges['exchange'] ?? 0)) > 0;
        $exchangeQuantity = $replacementDiscountEnabled ? max(0, (int)$request->input('exchange_quantity', 0)) : 0;
        if ($replacementDiscountEnabled && $exchangeQuantity < 1) {
            $exchangeQuantity = 1;
        }
        if ($availableQuantity > 0) {
            $exchangeQuantity = min($exchangeQuantity, $requestQuantity);
        }
        $exchangeDiscountTotal = $replacementDiscountEnabled
            ? $exchangeQuantity * max(0, (float)($resolvedExtraCharges['exchange'] ?? 0))
            : 0;
        $linePrice = ($price * $requestQuantity) + $installationCharge - $exchangeDiscountTotal;

        return [
            'price' => webCurrencyConverter($linePrice),
            'discount' => $discountType == 'flat' ? webCurrencyConverter($discount) : getProductPriceByType(product: $product, type: 'discount', result: 'value') . '%',
            'discount_type' => $discountType,
            'discount_amount' => $discount,
            'tax' => $product['tax_model'] == 'exclude' ? webCurrencyConverter($tax) : 'incl.',
            'update_tax' => $product['tax_model'] == 'exclude' ? webCurrencyConverter($update_tax) : 'incl.', // for others theme
            'quantity' => $availableQuantity,
            'delivery_cost' => isset($deliveryInfo['delivery_cost']) ? webCurrencyConverter($deliveryInfo['delivery_cost']) : 0,
            'unit_price' => webCurrencyConverter($price), //fashion theme
            'total_unit_price' => webCurrencyConverter($unit_price), //fashion theme
            'discounted_unit_price' => webCurrencyConverter($discountedUnitPrice), //fashion theme
            'color_name' => $color_name,
            'stock_limit' => $stock_limit,

            'in_cart_status' => $inCartExistStatus,
            'in_cart_quantity' => $safeInCartQuantity,
            'in_cart_key' => $inCartExistKey,
            'variation_code' => $string,
            'product_type' => $product['product_type'],
            'restock_request_status' => $restockRequestStatus,
        ];
    }


    // public function updateShippingCost(Request $request)
    // {
    //     $shippingCost = 0;

    //     $adminShipping = \App\Models\ShippingType::where('seller_id', 0)->first();
    //     $shippingType = $adminShipping ? $adminShipping->shipping_type : null;

    //     if ($request->area_name === null || $request->area_name === 'pickup') {
    //         $cart = CartManager::getCartListQuery(type: 'checked')->first();
    //         if ($cart) {
    //             $cart->shipping_cost = 0;
    //             $cart->save();
    //         }
    //         return response()->json(['shipping_cost' => 0]);
    //     }

    //     if ($shippingType === 'area_wise') {
    //         $shippingArea = ShippingMethodArea::where('area', $request->area_name)->first();

    //         if ($shippingArea) {
    //             $shippingCost = $shippingArea->cost;

    //             $cart = CartManager::getCartListQuery(type: 'checked')->first();
    //             if ($cart) {
    //                 $cart->shipping_cost = $shippingCost;
    //                 $cart->save();
    //             }
    //         }
    //     }

    //     return response()->json(['shipping_cost' => $shippingCost]);
    // }



    public function updateShippingCost(Request $request)
    {
        $adminShipping = \App\Models\ShippingType::where('seller_id', 0)->first();
        $shippingType = $adminShipping ? $adminShipping->shipping_type : null;
        // 1. Find the Area Price (e.g., 500.00)
        $shippingArea = ShippingMethodArea::where([
            ['country',  '=', $request->country],
            ['state_id', '=', $request->state_id],
            ['city_id',  '=', $request->city_id],
            ['area',     '=', $request->area_name],
        ])->latest('id')->first();

        if ($shippingType === 'area_wise' && $shippingArea) {
            $areaCost = $shippingArea->cost;

            // 2. Use your getCartListQuery to get the 5 items
            $cartList = CartManager::getCartListQuery(type: 'checked');

            // 3. Loop and "Selective Save"
            foreach ($cartList as $key => $cartItem) {
                if ($key === 0) {
                    // First item gets the whole Area Cost
                    $cartItem->shipping_cost = $areaCost;
                } else {
                    // Items 2, 3, 4, 5 get ZERO shipping
                    $cartItem->shipping_cost = 0;
                }
                $cartItem->save();
            }
            session()->put('area_wise_shipping_resolved', true);
        } else {
            session()->forget('area_wise_shipping_resolved');
            return response()->json([
                'status' => 1,
            ]);
        }

        return response()->json([
            'status' => 1,
            'view' => view('web-views.partials._order-summary')->render(),
        ]);
    }


    public function addToCart(Request $request): JsonResponse|RedirectResponse
    {


        $cart = CartManager::add_to_cart($request);
        if ($cart['status'] == 2) {
            $cart['shippingMethodHtmlView'] = view(VIEW_FILE_NAMES['product_shipping_method_modal_view_partials'], [
                'shipping_method_list' => $cart['shipping_method_list'],
                'productData' => $request->all(),
            ])->render();
        }
        $this->refreshCouponSessionAfterCartChange();
        session()->forget('cart_shipping_cost');
        session()->forget('area_wise_shipping_resolved');


        if (isset($cart['redirect_to']) && $cart['redirect_to'] == 'checkout') {
            $redirectRouteName = $this->resolveBuyNowRedirectRoute($request);
            $cart['redirect_to_url'] = route($redirectRouteName);

            return request()->ajax()
                ? response()->json($cart)
                : redirect()->route($redirectRouteName);
        }

        if (!request()->ajax() && $cart['status'] == 0) {
            Toastr::warning($cart['message']);
            return back();
        }
        return response()->json($cart);
    }

    private function resolveBuyNowRedirectRoute(Request $request): string
    {
        if ((int)($request->input('buy_now', 0)) !== 1) {
            return 'checkout-details';
        }

        $productId = (int) $request->input('id', 0);
        if ($productId < 1) {
            return 'checkout-details';
        }

        $product = Product::query()
            ->select(['id', 'product_type', 'category_id', 'sub_category_id', 'sub_sub_category_id'])
            ->find($productId);

        if (!$product || $product->product_type !== 'physical') {
            return 'checkout-details';
        }

        $resolvedExtraCharges = app(ProductExtraChargeResolverService::class)->resolveForProduct($product);
        $hasConfigurableExtraCharges =
            max(0, (float)($resolvedExtraCharges['installation'] ?? 0)) > 0
            || max(0, (float)($resolvedExtraCharges['exchange'] ?? 0)) > 0;

        return $hasConfigurableExtraCharges ? 'shop-cart' : 'checkout-details';
    }

    public function updateNavCart(): JsonResponse
    {
        return response()->json(['data' => view(VIEW_FILE_NAMES['products_cart_partials'])->render(), 'mobile_nav' => view(VIEW_FILE_NAMES['products_mobile_nav_partials'])->render()]);
    }

    /**
     * For theme fashion floating nav
     */
    public function update_floating_nav(): JsonResponse
    {
        return response()->json(['floating_nav' => view(VIEW_FILE_NAMES['products_floating_nav_partials'])->render()]);
    }

    /**
     * removes from Cart
     */
    public function removeFromCart(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|integer|min:1',
        ]);

        $cartItem = CartManager::getOwnedCartQuery($request)
            ->where('id', $request->key)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'status' => 0,
                'message' => translate('Product_not_found_in_cart'),
            ], 404);
        }

        $removedGroupId = $cartItem->cart_group_id;
        $cartItem->delete();

        if (!Cart::where('cart_group_id', $removedGroupId)->exists()) {
            CartShipping::where('cart_group_id', $removedGroupId)->delete();
        }

        $this->refreshCouponSessionAfterCartChange();
        session()->forget('shipping_method_id');
        session()->forget('order_note');
        session()->forget('cart_shipping_cost');
        session()->forget('area_wise_shipping_resolved');


        $cart = CartManager::getOwnedCartQuery($request)
            ->select(['id', 'variant'])
            ->get();


        return response()->json([
            'data' => view(VIEW_FILE_NAMES['products_cart_details_partials'], compact('request'))->render(),
            'message' => translate('Item_has_been_removed_from_cart'),
            'cartList' => $cart,
        ]);
    }

    //updated the quantity for a cart item
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'key' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
        ]);

        $response = CartManager::update_cart_qty($request);

        $this->refreshCouponSessionAfterCartChange();
        session()->forget('cart_shipping_cost');
        session()->forget('area_wise_shipping_resolved');


        if ($response['status'] == 0) {
            return response()->json($response);
        }
        return response()->json(view(VIEW_FILE_NAMES['products_cart_details_partials'], compact('request'))->render());
    }

    //updated the quantity for a cart item
    public function updateInstalltionCharges(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer|min:1',
            'charges' => 'required|numeric|min:0',
        ]);

        $response = CartManager::update_installtion_charges($request);

        $this->refreshCouponSessionAfterCartChange();
        session()->forget('cart_shipping_cost');
        session()->forget('area_wise_shipping_resolved');


        if ($response['status'] == 0) {
            return response()->json($response);
        }
        return response()->json(view(VIEW_FILE_NAMES['products_cart_details_partials'], compact('request'))->render());
    }

    //updated the quantity for a cart item
    public function updateExchangeCharges(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer|min:1',
            'charges' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:0',
        ]);

        $response = CartManager::update_exchange_charges($request);

        $this->refreshCouponSessionAfterCartChange();
        session()->forget('cart_shipping_cost');
        session()->forget('area_wise_shipping_resolved');


        if ($response['status'] == 0) {
            return response()->json($response);
        }
        return response()->json(view(VIEW_FILE_NAMES['products_cart_details_partials'], compact('request'))->render());
    }

    // Updated the quantity for a cart item
    public function updateQuantity_guest(Request $request): JsonResponse
    {
        $response = CartManager::update_cart_qty($request);
        $cart = CartManager::getCartListQuery();
        $this->refreshCouponSessionAfterCartChange();
        session()->forget('cart_shipping_cost');
        session()->forget('area_wise_shipping_resolved');

        $product = CartManager::getOwnedCartQuery($request)
            ->where('id', $request['key'])
            ->first();

        if (!$product) {
            return response()->json([
                'status' => 0,
                'qty' => $request['quantity'],
                'message' => translate('Product_not_found_in_cart'),
            ]);
        }

        $quantity_price = webCurrencyConverter($product['price'] * (int)$product['quantity']);
        $discount_price = webCurrencyConverter(($product['price'] - $product['discount']) * (int)$product['quantity']);

        $user = auth('customer')->user();
        $summarySubTotal = 0;
        $summarySavedAmount = 0;
        if ($user && (int)$user->user_type === 1) {
            foreach ($cart as $cartItem) {
                $summarySubTotal += (float)$cartItem['price'] * (float)$cartItem['quantity'];
            }
        } else {
            $cartSummary = CartManager::getCartPriceSummary(type: 'checked');
            $summarySubTotal = (float)($cartSummary['subTotal'] ?? 0);
            $summarySavedAmount = (float)($cartSummary['totalSavedAmount'] ?? 0);
        }
        $total_price = webCurrencyConverter($summarySubTotal);
        $total_discount_price = webCurrencyConverter($summarySavedAmount);

        if ($response['status'] == 0) {
            return response()->json([
                'status' => $response['status'],
                'message' => $response['message'],
                'qty' => $response['status'] == 0 ? $response['qty'] : $request->quantity,
            ]);
        }
        /** for default theme nav cart ,showing free delivery amount */
        $free_delivery_status = [
            'amount_need' => 0,
            'percentage' => 0,
        ];
        if ($cart->isNotEmpty()) {
            $free_delivery_status = OrderManager::getFreeDeliveryOrderAmountArray($cart[0]->cart_group_id);
        }

        return response()->json([
            'status' => $response['status'],
            'message' => translate('successfully_updated!'),
            'qty' => $response['status'] == 0 ? $response['qty'] : $request->quantity,
            'total_price' => $total_price,
            'discount_price' => $discount_price,
            'quantity_price' => $quantity_price,
            'total_discount_price' => $total_discount_price,
            'free_delivery_status' => $free_delivery_status,
            'in_cart_key' => $product['id'],
        ]);
    }

    public function orderAgain(Request $request): JsonResponse
    {
        $data = OrderManager::order_again($request);
        $orderProductCount = $data['order_product_count'];
        $addToCartCount = $data['add_to_cart_count'];
        $failedAddToCartCount = $data['failedAddToCartCount'];
        $message = $failedAddToCartCount == 0 ? translate('added_to_cart_successfully!') : translate('Some_items_were_not_added_to_your_cart_because_they_are_currently_unavailable_for_purchase.!');

        if ($orderProductCount == $addToCartCount) {
            $this->refreshCouponSessionAfterCartChange();
            session()->forget('shipping_method_id');
            session()->forget('order_note');
            session()->forget('cart_shipping_cost');
            session()->forget('area_wise_shipping_resolved');


            if (auth('customer')->check()) {
                return response()->json([
                    'status' => 1,
                    'redirect_url' => route('shop-cart'),
                    'message' => $message
                ], 200);
            } else {
                return response()->json(['message' => $message], 200);
            }
        } elseif ($addToCartCount > 0) {
            if (auth('customer')->check()) {
                return response()->json([
                    'status' => 1,
                    'redirect_url' => route('shop-cart'),
                    'message' => $message
                ], 200);
            } else {
                return response()->json(['message' => $message], 200);
            }
        } else {
            if (auth('customer')->check()) {
                return response()->json([
                    'status' => 0,
                    'message' => translate('all_items_were_not_added_to_cart_as_they_are_currently_unavailable_for_purchase!')
                ], 200);
            } else {
                return response()->json([
                    'message' => translate('all_items_were_not_added_to_cart_as_they_are_currently_unavailable_for_purchase!')
                ], 403);
            }
        }
    }

    function addToCartPhysicalProduct($request, $product)
    {
        $user = Helpers::getCustomerInformation($request);
        $guestId = session('guest_id') ?? ($request->guest_id ?? 0);
        $customerId = $user == 'offline' ? $guestId : $user->id;
        $isGuest = $user == 'offline' ? 1 : 0;
        $variantMatcher = new VariantMatcher();
        $str = '';
        $variations = [];
        $price = 0;
        $discount = 0;
        if ($request->has('color')) {
            $str = Color::where('code', $request['color'])->first()->name;
            $variations['color'] = $str;
        }

        //Gets all the choice values of customer choice option and generate a string like Black-S-Cotton
        $choices = [];
        foreach (json_decode($product->choice_options) as $key => $choice) {
            $choices[$choice->name] = $request[$choice->name];
            $variations[$choice->title] = $request[$choice->name];
            if ($str != null) {
                $str .= '-' . str_replace(' ', '', $request[$choice->name]);
            } else {
                $str .= str_replace(' ', '', $request[$choice->name]);
            }
        }

        if ($str != null) {
            $count = count(json_decode($product->variation));
            for ($i = 0; $i < $count; $i++) {
                $variationType = json_decode($product->variation)[$i]->type ?? null;
                if ($variantMatcher->matches($variationType, $str)) {
                    $tax = $product->tax_model == 'exclude' ? Helpers::tax_calculation(product: $product, price: json_decode($product->variation)[$i]->price, tax: $product['tax'], tax_type: $product['tax_type']) : 0;
                    $discount = Helpers::getProductDiscount($product, json_decode($product->variation)[$i]->price);
                    $price = json_decode($product->variation)[$i]->price - $discount + $tax;
                    $quantity = json_decode($product->variation)[$i]->qty;
                }
            }
        } else {
            $tax = $product->tax_model == 'exclude' ? Helpers::tax_calculation(product: $product, price: $product->unit_price, tax: $product['tax'], tax_type: $product['tax_type']) : 0;
            $discount = Helpers::getProductDiscount($product, $product->unit_price);
            $price = $product->unit_price - $discount + $tax;
            $quantity = $product->current_stock;
        }

        $cart = Cart::where([
            'product_id' => $request['product_id'],
            'customer_id' => $customerId,
            'is_guest' => $isGuest,
            'variant' => $str
        ])->first();

        if (isset($cart) == false) {
            $editableCart = null;
            if ($request->filled('key')) {
                $editableCart = CartManager::getOwnedCartQuery($request)
                    ->where('id', $request->key)
                    ->first();
            }

            if ($str != null) {
                $count = count(json_decode($product->variation));
                for ($i = 0; $i < $count; $i++) {
                    $variationType = json_decode($product->variation)[$i]->type ?? null;
                    if ($variantMatcher->matches($variationType, $str)) {
                        $price = json_decode($product->variation)[$i]->price;
                        if (json_decode($product->variation)[$i]->qty < $request['quantity']) {
                            return [
                                'status' => 0,
                                'message' => translate('out_of_Stock') . '!'
                            ];
                        }
                    }
                }
            } else {
                $price = $product->unit_price;
            }

            $cart = $editableCart ?? new Cart();
            $cart['customer_id'] = $customerId;
            $cart['product_id'] = $product['id'];
            $cart['product_type'] = $product['product_type'];
            $cart['digital_product_type'] = $product['digital_product_type'];
            $cart['color'] = $request->has('color') ? $request['color'] : null;
            $cart['choices'] = json_encode($choices);
            $cart['variations'] = json_encode($variations);
            $cart['variant'] = $str;
            $cart['price'] = $price;
            $cart['discount'] = $discount;
            $cart['tax'] = $tax;
            $cart['quantity'] = $request['quantity'];
            $cart['tax_model'] = $product->tax_model;
            $cart['is_checked'] = 1;
            $cart['slug'] = $product['slug'];
            $cart['name'] = $product['name'];
            $cart['thumbnail'] = $product['thumbnail'];
            $cart['seller_id'] = ($product->added_by == 'admin') ? 1 : $product->user_id;
            $cart['seller_is'] = $product['added_by'];
            $cart['shop_info'] = $product->added_by == 'admin'
                ? getWebConfig(name: 'company_name')
                : Shop::where(['seller_id' => $product->user_id])->first()->name;
            $cart['shipping_cost'] = $product['product_type'] == 'physical'
                ? CartManager::get_shipping_cost_for_product_category_wise($product, $request['quantity'])
                : 0;
            $cart['is_guest'] = $isGuest;
            if (!$cart->exists) {
                $existingCartGroup = Cart::where([
                    'customer_id' => $customerId,
                    'is_guest' => $isGuest,
                    'seller_id' => ($product->added_by == 'admin') ? 1 : $product->user_id,
                    'seller_is' => $product['added_by'],
                ])->value('cart_group_id');
                $cart['cart_group_id'] = $existingCartGroup ?: CartManager::generateOpaqueCartGroupId();
            }
            $cart->save();

            return [
                'status' => 1,
                'message' => translate('successfully_added') . '!',
                'price' => webCurrencyConverter($price),
                'discount' => webCurrencyConverter($discount * $request['quantity']),
                'data' => view(VIEW_FILE_NAMES['products_cart_details_partials'], compact('request'))->render()
            ];
        } else {
            return [
                'status' => 0,
                'message' => translate('already_added') . '!'
            ];
        }
    }

    function addToCartDigitalProduct($request, $product)
    {
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

        $tax = Helpers::tax_calculation(product: $product, price: $price, tax: $product['tax'], tax_type: 'percent');
        $getProductDiscount = Helpers::getProductDiscount($product, $price);
        $cartArray = [
            'customer_id' => $customerId,
            'product_id' => $product['id'],
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
            'is_guest' => $isGuest,
        ];

        $cart = Cart::where([
            'id' => $request['id'],
            'product_id' => $request['product_id'],
            'customer_id' => $user == 'offline' ? session('guest_id') : $user->id,
            'is_guest' => $user == 'offline' ? 1 : '0',
            'variant' => $request['current_variant_key']
        ])->first();

        if (isset($cart)) {
            Cart::where(['id' => $cart['id']])->update($cartArray);
            return [
                'status' => 1,
                'message' => translate('successfully_update!'),
                'price' => webCurrencyConverter($price),
                'discount' => webCurrencyConverter($getProductDiscount),
                'data' => view(VIEW_FILE_NAMES['products_cart_details_partials'], compact('request'))->render()
            ];
        } else {
            Cart::insertGetId($cartArray);
            return [
                'status' => 1,
                'message' => translate('successfully_added') . '!',
                'price' => webCurrencyConverter($price),
                'discount' => webCurrencyConverter($getProductDiscount),
                'data' => view(VIEW_FILE_NAMES['products_cart_details_partials'], compact('request'))->render()
            ];
        }
    }

    function update_variation(Request $request)
    {
        $product = Product::where(['id' => $request['product_id']])->first();
        if ($product['product_type'] == 'digital') {
            return self::addToCartDigitalProduct($request, $product);
        } else {
            return self::addToCartPhysicalProduct($request, $product);
        }
    }

    public function remove_all_cart(Request $request)
    {
        $cartGroupIds = CartManager::getOwnedCartQuery($request)
            ->pluck('cart_group_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($cartGroupIds)) {
            CartShipping::whereIn('cart_group_id', $cartGroupIds)->delete();
        }

        CartManager::getOwnedCartQuery($request)->delete();
        $this->refreshCouponSessionAfterCartChange();
        session()->forget('shipping_method_id');
        session()->forget('order_note');
        session()->forget('cart_shipping_cost');
        session()->forget('area_wise_shipping_resolved');

        return redirect()->back();
    }


    public function updateCheckedCartItems(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation();
        Cart::where([
            'customer_id' => ($user == 'offline' ? session('guest_id') : auth('customer')->id()),
            'is_guest' => ($user == 'offline' ? 1 : '0'),
        ])->update(['is_checked' => 0]);

        if ($request['ids']) {
            Cart::where([
                'customer_id' => ($user == 'offline' ? session('guest_id') : auth('customer')->id()),
                'is_guest' => ($user == 'offline' ? 1 : '0'),
            ])->whereIn('id', $request['ids'])->update(['is_checked' => 1]);
        }

        $allCartGroupIds = Cart::where([
            'customer_id' => ($user == 'offline' ? session('guest_id') : auth('customer')->id()),
            'is_guest' => ($user == 'offline' ? 1 : '0'),
        ])->pluck('cart_group_id')->unique()->values();

        $checkedCartGroupIds = Cart::where([
            'customer_id' => ($user == 'offline' ? session('guest_id') : auth('customer')->id()),
            'is_guest' => ($user == 'offline' ? 1 : '0'),
            'is_checked' => 1,
        ])->pluck('cart_group_id')->unique()->values();

        if ($allCartGroupIds->count() > 0) {
            $deleteShippingQuery = CartShipping::whereIn('cart_group_id', $allCartGroupIds->toArray());
            if ($checkedCartGroupIds->count() > 0) {
                $deleteShippingQuery->whereNotIn('cart_group_id', $checkedCartGroupIds->toArray());
            }
            $deleteShippingQuery->delete();
        }

        $this->refreshCouponSessionAfterCartChange();
        session()->forget('cart_shipping_cost');
        session()->forget('area_wise_shipping_resolved');


        return response()->json([
            'message' => translate('Successfully_Update'),
            'htmlView' => view(VIEW_FILE_NAMES['products_cart_details_partials'], compact('request'))->render()
        ], 200);
    }

    public function addProductRestockRequest(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation();
        $product = $this->productRepo->getWebFirstWhereActive(params: ['id' => $request['id']]);
        $restockRequest = $this->restockProductRepo->updateOrCreate(params: ['product_id' => $request['id'], 'variant' => $request['product_variation_code']], value: [
            'product_id' => $request['id'],
            'variant' => $request['product_variation_code'],
        ]);
        $data = $this->restockProductService->getProductRestockRequestAddData(request: $request, restockRequest: $restockRequest);
        $checkRequest = $this->restockProductCustomerRepo->getFirstWhere(params: $data);
        if ($checkRequest) {
            return response()->json([
                'status' => 'warning',
                'message' => translate('Already_Requested'),
            ], 200);
        }

        $this->restockProductCustomerRepo->updateOrCreate(params: $data, value: $data);
        $this->restockProductRepo->updateByParams(params: ['id' => $restockRequest['id']], data: ['updated_at' => Carbon::now()]);
        CustomerManager::updateCustomerSessionData(userId: auth('customer')->id());

        // event(new RequestProductRestockEvent(key: 'message_from_customer', product: $product, restockRequest: $restockRequest));

        return response()->json([
            'status' => 'success',
            'message' => translate('Request_sent_successfully'),
            'fcm_topic' => getRestockProductFCMTopic(restockRequest: $restockRequest)
        ], 200);
    }

    private function refreshCouponSessionAfterCartChange(): void
    {
        $couponCode = session('coupon_code');
        if (!$couponCode) {
            return;
        }

        $coupon = Coupon::where(['code' => $couponCode])
            ->where('status', 1)
            ->whereDate('start_date', '<=', date('Y-m-d'))
            ->whereDate('expire_date', '>=', date('Y-m-d'))
            ->first();

        if (!$coupon) {
            $this->clearCouponSession();
            return;
        }

        $customerId = auth('customer')->id();
        if ($customerId) {
            $couponLimit = Order::where([
                'customer_id' => $customerId,
                'coupon_code' => $couponCode,
            ])->groupBy('order_group_id')->get()->count();

            if ($coupon->coupon_type != 'first_order' && $coupon->limit && $couponLimit >= $coupon->limit) {
                $this->clearCouponSession();
                return;
            }

            if ($coupon->coupon_type == 'first_order' && Order::where(['customer_id' => $customerId])->count() > 0) {
                $this->clearCouponSession();
                return;
            }

            if ($coupon->customer_id != '0' && (int)$coupon->customer_id !== (int)$customerId) {
                $this->clearCouponSession();
                return;
            }
        } elseif ($coupon->customer_id != '0') {
            $this->clearCouponSession();
            return;
        }

        $total = 0;
        $shippingFee = 0;
        foreach (CartManager::getCartListQuery(type: 'checked') as $cartItem) {
            if (!$this->isCouponApplicableToCartItem(coupon: $coupon, cartItem: $cartItem)) {
                continue;
            }

            $productSubtotal = ((float)$cartItem['price'] - (float)$cartItem['discount']) * (float)$cartItem['quantity'];
            $total += max(0, $productSubtotal);

            if ($coupon->coupon_type == 'free_delivery') {
                $shippingFee += (float)$cartItem['shipping_cost'];
            }
        }

        if ($total < (float)$coupon['min_purchase']) {
            $this->clearCouponSession();
            return;
        }

        if ($coupon['coupon_type'] == 'free_delivery') {
            $discount = $shippingFee;
        } elseif ($coupon['discount_type'] == 'percentage') {
            $discountByPercentage = ($total / 100) * (float)$coupon['discount'];
            $maxDiscount = (float)$coupon['max_discount'];
            $discount = $maxDiscount > 0 ? min($discountByPercentage, $maxDiscount) : $discountByPercentage;
        } else {
            $discount = (float)$coupon['discount'];
        }

        $discount = max(0, (float)$discount);
        if ($discount <= 0) {
            $this->clearCouponSession();
            return;
        }

        session()->put('coupon_code', $couponCode);
        session()->put('coupon_type', $coupon->coupon_type);
        session()->put('coupon_discount', $discount);
        session()->put('coupon_bearer', $coupon->coupon_bearer);
        session()->put('coupon_seller_id', $coupon->seller_id);
    }

    private function isCouponApplicableToCartItem($coupon, $cartItem): bool
    {
        return $coupon->seller_id == '0'
            || (is_null($coupon->seller_id) && $cartItem->seller_is == 'admin')
            || ($coupon->seller_id == $cartItem->seller_id && $cartItem->seller_is == 'seller');
    }

    private function clearCouponSession(): void
    {
        session()->forget('coupon_code');
        session()->forget('coupon_type');
        session()->forget('coupon_bearer');
        session()->forget('coupon_discount');
        session()->forget('coupon_seller_id');
    }
}
