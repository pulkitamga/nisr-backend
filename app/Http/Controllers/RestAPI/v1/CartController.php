<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Domain\Stock\Support\VariantMatcher;
use App\Events\RequestProductRestockEvent;
use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\City;
use App\Models\Cart;
use App\Models\CartShipping;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethodArea;
use App\Services\RestockProductService;
use App\Utils\CartManager;
use App\Utils\Helpers;
use App\Utils\OrderManager;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function __construct(
        private Order                                              $order,
        private readonly RestockProductService                     $restockProductService,
        private readonly ProductRepositoryInterface                $productRepo,
        private readonly RestockProductRepositoryInterface         $restockProductRepo,
        private readonly RestockProductCustomerRepositoryInterface $restockProductCustomerRepo,
    ) {}

    public function getCartList(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        $cart = Cart::whereHas('product', function ($query) {
            return $query->active();
        })
            ->with(['shop', 'product' => function ($query) {
                return $query->with(['clearanceSale' => function ($query) {
                    return $query->active();
                }]);
            }])
            ->when($user == 'offline', function ($query) use ($request) {
                return $query->where(['customer_id' => $request->guest_id, 'is_guest' => 1]);
            })
            ->when($user != 'offline', function ($query) use ($user) {
                return $query->where(['customer_id' => $user->id, 'is_guest' => '0']);
            })->get();

        if ($cart) {
            foreach ($cart as $key => $value) {
                if (!isset($value['product'])) {
                    $cart_data = Cart::find($value['id']);
                    $cart_data->delete();

                    unset($cart[$key]);
                    continue;
                }

                CartManager::refreshCartItemPricing($value, $value->product);
            }

            $variantMatcher = new VariantMatcher();
            $cart->map(function ($data) use ($request, $variantMatcher) {

                $product = Product::with([
                    'category.extraCharges' => function ($query) {
                        $query->whereIn('type', ['exchange', 'installation'])->where('status', 1);
                    },
                    'subCategory.extraCharges' => function ($query) {
                        $query->whereIn('type', ['exchange', 'installation'])->where('status', 1);
                    },
                    'subSubCategory.extraCharges' => function ($query) {
                        $query->whereIn('type', ['exchange', 'installation'])->where('status', 1);
                    },
                ])->active()->find($data->product_id);

                if ($product) {
                    $data['is_product_available'] = 1;
                } else {
                    $data['is_product_available'] = 0;
                }

                $data['choices'] = json_decode($data['choices']);
                $data['variations'] = json_decode($data['variations']);

                // MIN ORDER CHECK
                $data['minimum_order_amount_info'] =
                    OrderManager::verifyCartListMinimumOrderAmount($request, $data['cart_group_id'])['minimum_order_amount'];

                // FREE DELIVERY CHECK
                $cart_group = Cart::where(['product_type' => 'physical'])
                    ->where('cart_group_id', $data['cart_group_id'])
                    ->get()
                    ->groupBy('cart_group_id');

                if (isset($cart_group[$data['cart_group_id']])) {
                    $data['free_delivery_order_amount'] = OrderManager::getFreeDeliveryOrderAmountArray($data['cart_group_id']);
                } else {
                    $data['free_delivery_order_amount'] = [
                        'status' => 0,
                        'amount' => 0,
                        'percentage' => 0,
                        'shipping_cost_saved' => 0,
                    ];
                }

                // STOCK
                $data['product']['total_current_stock'] =
                    $product->current_stock ?? 0;

                if (!empty($product->variation)) {
                    foreach (json_decode($product->variation) as $var) {
                        if ($variantMatcher->matches($data['variant'] ?? null, $var->type ?? null)) {
                            $data['product']['total_current_stock'] = $var->qty;
                        }
                    }
                }

                // DISCOUNT
                $data['discount'] = getProductPriceByType(
                    product: $data['product'],
                    type: 'discounted_amount',
                    result: 'value',
                    price: $data['price']
                );
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

                $data['exchange_charge'] = $extraCharges->where('type', 'exchange')->first()->charges ?? 0;
                $data['installation_charge'] = $extraCharges->where('type', 'installation')->first()->charges ?? 0;

                unset($data['product']['variation']);
                return $data;
            });
        }

        return response()->json($cart, 200);
    }

    public function addToCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'quantity' => 'required',
        ], [
            'id.required' => translate('Product ID is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)]);
        }

        $cart = CartManager::add_to_cart($request);
        return response()->json($cart, 200);
    }

    // public function updateShippingCost(Request $request)
    // {
    //     $adminShipping = \App\Models\ShippingType::where('seller_id', 0)->first();
    //     $shippingType = $adminShipping ? $adminShipping->shipping_type : null;

    //     if ($shippingType !== 'area_wise') {
    //         return response()->json([
    //             'status' => 0,
    //             'message' => 'method is not an area wise'
    //         ], 200);
    //     }
    //     // 1. Precise Lookup
    //     $shippingArea = ShippingMethodArea::where([
    //         ['country',  '=', $request->country],
    //         ['state_id', '=', $request->state_id],
    //         ['city_id',  '=', $request->city_id],
    //         ['area',     '=', $request->area_name],
    //         ['status',   '=', 1]
    //     ])->latest('id')->first();


    //     // Get the current checked cart items
    //     $cartList = CartManager::get_cart_for_api($request, type: 'checked');
    //     $totalShippingCost = 0;

    //     if ($shippingArea) {
    //         /** * CASE A: Area-wise match found.
    //          * Apply flat cost to first item, zero out others.
    //          */
    //         $totalShippingCost = (float)$shippingArea->cost;

    //         foreach ($cartList as $key => $cartItem) {
    //             $cartItem->shipping_cost = ($key === 0) ? $totalShippingCost : 0;
    //             $cartItem->save();
    //         }
    //     } else {
    //         foreach ($cartList as $cartItem) {
    //             // We assume 'shipping_cost' in the DB holds the default product shipping cost
    //             $totalShippingCost += $cartItem->shipping_cost;
    //         }
    //     }

    //     // 4. Manually Calculate Subtotal and Tax since they aren't in your CartManager
    //     $cart = CartManager::get_cart_for_api($request, type: 'checked');
    //     $sub_total = 0;
    //     $total_tax = 0;

    //     foreach ($cart as $item) {
    //         $sub_total += ($item['price'] * $item['quantity']);
    //         $total_tax += ($item['tax_model'] == 'include' ? 0 : ($item['tax'] * $item['quantity']));
    //     }

    //     // 5. Return JSON using your existing api_cart_grand_total
    //     return response()->json([
    //         'status'         => 1,
    //         'shipping_cost'  => $totalShippingCost,
    //         // 'total_tax'      => (float)$total_tax,
    //         // 'sub_total'      => (float)$sub_total,
    //         // 'total_amount'   => (float)CartManager::api_cart_grand_total($request),
    //     ], 200);
    // }

    public function updateShippingCost(Request $request)
    {
        $adminShipping = \App\Models\ShippingType::where('seller_id', 0)->first();
        $shippingType = $adminShipping ? $adminShipping->shipping_type : 'order_wise';

        $cartList = CartManager::get_cart_for_api($request, type: 'checked');
        if ($cartList->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'No checked cart items found'
            ], 200);
        }

        $deliveryType = strtolower(trim((string)$request->input('delivery_type', 'delivery')));

        // Handle pickup — zero cost for all methods
        if ($deliveryType === 'pickup') {
            foreach ($cartList as $cartItem) {
                $cartItem->shipping_cost = 0;
                $cartItem->save();
            }
            return response()->json([
                'status' => 1,
                'shipping_cost' => 0,
                'shipping_type' => $shippingType,
                'is_pickup_delivery' => true,
                'message' => 'Pickup selected. No delivery cost applied.'
            ], 200);
        }

        // ---------- ORDER-WISE ----------
        if ($shippingType === 'order_wise') {
            $shippingMethod = \App\Models\ShippingMethod::where(['creator_id' => 1, 'creator_type' => 'admin', 'status' => 1])->first();
            $totalCost = $shippingMethod ? (float)$shippingMethod->cost : 0;

            // Apply flat cost to first item, zero out others
            foreach ($cartList as $key => $cartItem) {
                $cartItem->shipping_cost = ($key === 0) ? $totalCost : 0;
                $cartItem->save();
            }

            return response()->json([
                'status' => 1,
                'shipping_cost' => $totalCost,
                'shipping_type' => $shippingType,
            ], 200);
        }

        // ---------- CATEGORY-WISE ----------
        if ($shippingType === 'category_wise') {
            $totalCost = 0;
            foreach ($cartList as $cartItem) {
                $product = Product::find($cartItem->product_id);
                if ($product) {
                    $catCost = \App\Models\CategoryShippingCost::where(['seller_id' => 0, 'category_id' => $product->category_id])->first();
                    $itemCost = $catCost ? (float)$catCost->cost : 0;
                    $qty = $cartItem->quantity ?? 1;
                    $multiply = ($catCost && $catCost->multiply_qty != 0);
                    $finalItemCost = $multiply ? ($itemCost * $qty) : $itemCost;
                    $cartItem->shipping_cost = $finalItemCost;
                    $cartItem->save();
                    $totalCost += $finalItemCost;
                } else {
                    $cartItem->shipping_cost = 0;
                    $cartItem->save();
                }
            }

            return response()->json([
                'status' => 1,
                'shipping_cost' => $totalCost,
                'shipping_type' => $shippingType,
            ], 200);
        }

        // ---------- PRODUCT-WISE ----------
        if ($shippingType === 'product_wise') {
            $totalCost = 0;
            foreach ($cartList as $cartItem) {
                $product = Product::find($cartItem->product_id);
                if ($product) {
                    $qty = $cartItem->quantity ?? 1;
                    $itemCost = (float)$product->shipping_cost;
                    $multiply = $product->multiply_qty != 0;
                    $finalItemCost = $multiply ? ($itemCost * $qty) : $itemCost;
                    $cartItem->shipping_cost = $finalItemCost;
                    $cartItem->save();
                    $totalCost += $finalItemCost;
                } else {
                    $cartItem->shipping_cost = 0;
                    $cartItem->save();
                }
            }

            return response()->json([
                'status' => 1,
                'shipping_cost' => $totalCost,
                'shipping_type' => $shippingType,
            ], 200);
        }

        // ---------- AREA-WISE (default) ----------
        $areaInput = $request->input('area', $request->input('area_name'));
        $stateInput = $request->input('state', $request->input('state_name'));
        $cityInput = $request->input('city', $request->input('city_name'));

        if (!$request->country || (!$stateInput && !$request->input('state_id')) || (!$cityInput && !$request->input('city_id')) || !$areaInput) {
            foreach ($cartList as $cartItem) {
                $cartItem->shipping_cost = 0;
                $cartItem->save();
            }
            return response()->json([
                'status' => 1,
                'shipping_cost' => 0,
                'shipping_type' => $shippingType,
                'is_area_wise_shipping_pending' => true,
                'shipping_notice' => translate('shipping_cost_determined_later_by_location'),
                'message' => 'Country, state, city and area are required for area-wise delivery'
            ], 200);
        }

        $countryName = trim($request->country);
        $countryCode = null;
        foreach (COUNTRIES as $c) {
            if (strcasecmp($c['name'], $countryName) === 0) {
                $countryCode = $c['code'];
                break;
            }
        }
        if (!$countryCode) {
            return response()->json(['status' => 0, 'message' => 'Invalid country name'], 200);
        }

        $stateName = trim(strtolower((string)$stateInput));
        $cityName  = trim(strtolower((string)$cityInput));
        $areaName  = trim(strtolower((string)$areaInput));

        $stateId = (int)$request->input('state_id', 0);
        if ($stateId <= 0) {
            $state = State::whereRaw('LOWER(TRIM(name)) = ?', [$stateName])->where('country', $countryCode)->first();
            $stateId = (int)($state?->id ?? 0);
        } else {
            $state = State::where('id', $stateId)->where('country', $countryCode)->first();
            $stateId = (int)($state?->id ?? 0);
        }
        if (!$stateId) {
            return response()->json(['status' => 0, 'message' => 'This state does not exist in ' . $countryName], 200);
        }

        $cityId = (int)$request->input('city_id', 0);
        if ($cityId <= 0) {
            $city = City::whereRaw('LOWER(TRIM(name)) = ?', [$cityName])->where('state_id', $stateId)->first();
            $cityId = (int)($city?->id ?? 0);
        } else {
            $city = City::where('id', $cityId)->where('state_id', $stateId)->first();
            $cityId = (int)($city?->id ?? 0);
        }
        if (!$cityId) {
            return response()->json(['status' => 0, 'message' => 'Invalid city'], 200);
        }

        $shippingArea = ShippingMethodArea::where('country', $countryCode)
            ->where('state_id', $stateId)
            ->where('city_id', $cityId)
            ->whereRaw('LOWER(TRIM(area)) = ?', [$areaName])
            ->where('status', 1)
            ->latest('id')
            ->first();

        if ($shippingArea) {
            $totalShippingCost = (float)$shippingArea->cost;
            foreach ($cartList as $key => $cartItem) {
                $cartItem->shipping_cost = ($key === 0) ? $totalShippingCost : 0;
                $cartItem->save();
            }
            return response()->json([
                'status' => 1,
                'shipping_cost' => $totalShippingCost,
                'shipping_type' => $shippingType,
            ], 200);
        }

        foreach ($cartList as $cartItem) {
            $cartItem->shipping_cost = 0;
            $cartItem->save();
        }
        return response()->json([
            'status' => 1,
            'shipping_cost' => 0,
            'shipping_type' => $shippingType,
            'is_area_wise_shipping_pending' => true,
            'shipping_notice' => translate('shipping_cost_determined_later_by_location'),
        ], 200);
    }

    public function update_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|integer|min:1',
            'quantity' => 'required|integer|min:1',
        ], [
            'key.required' => translate('Cart key or ID is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)]);
        }

        $response = CartManager::update_cart_qty($request);
        return response()->json($response);
    }


    public function updateInstallationCharges(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer|exists:carts,id',
            'charges' => 'required|numeric|min:0|max:999999.99',
        ]);

        $response = CartManager::update_installtion_charges($request);

        if ($response['status'] == 0) {
            return response()->json($response, 400);
        }

        return response()->json([
            'status' => 1,
            'message' => $response['message'],
            'charges' => $response['charges'],
        ], 200);
    }

    // public function updateInstallationCharges(Request $request)
    // {
    //     $request->validate([
    //         'cart_id' => 'required|integer|exists:carts,id',
    //         'charges' => 'required|numeric|min:0',
    //     ]);

    //     $customerId = auth()->id()
    //         ?? ($request->guest_id ?? session('guest_id'));

    //     $updated = Cart::where('id', $request->cart_id)
    //         ->where('customer_id', $customerId)
    //         ->update([
    //             'installtion_charges' => $request->charges,
    //             'updated_at' => now(),
    //         ]);

    //     if (!$updated) {
    //         return response()->json([
    //             'status' => 0,
    //             'charges' => $request->charges,
    //             'message' => 'Product not found in cart',
    //         ], 400);
    //     }

    //     return response()->json([
    //         'status' => 1,
    //         'charges' => $request->charges,
    //         'message' => 'Successfully updated',
    //     ]);
    // }

    public function updateExchangeCharges(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|integer|exists:carts,id',
            'charges' => 'required|numeric|min:0|max:999999.99',
            'qty' => 'required|integer|min:0',
        ]);

        $response = CartManager::update_exchange_charges($request);

        if ($response['status'] == 0) {
            return response()->json($response, 400);
        }

        return response()->json([
            'status' => 1,
            'message' => $response['message'],
            'charges' => $response['charges'],
            'qty' => $response['qty'],
        ], 200);
    }

    // public function updateExchangeCharges(Request $request)
    // {
    //     $request->validate([
    //         'cart_id' => 'required|integer|exists:carts,id',
    //         'charges' => 'required|numeric|min:0',
    //         'qty' => 'required|integer|min:0',
    //     ]);

    //     $userId = auth()->id();
    //     $guestId = $request->guest_id ?? session('guest_id');

    //     $customerId = $userId ?? $guestId;

    //     $updated = Cart::where('id', $request->cart_id)
    //         ->where('customer_id', $customerId)
    //         ->update([
    //             'exchange_charges' => $request->charges,
    //             'exchange_qty' => $request->qty,
    //             'updated_at' => now(),
    //         ]);

    //     if (!$updated) {
    //         return response()->json([
    //             'status' => 0,
    //             'charges' => $request->charges,
    //             'qty' => $request->qty,
    //             'message' => 'Product not found in cart',
    //         ], 400);
    //     }

    //     return response()->json([
    //         'status' => 1,
    //         'charges' => $request->charges,
    //         'qty' => $request->qty,
    //         'message' => 'Successfully updated',
    //     ]);
    // }

    public function remove_from_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required'
        ], [
            'key.required' => translate('Cart key or ID is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)]);
        }

        $user = Helpers::getCustomerInformation($request);
        $cartItem = Cart::where([
            'id' => $request->key,
            'customer_id' => ($user == 'offline' ? (session('guest_id') ?? $request->guest_id) : $user->id),
            'is_guest' => ($user == 'offline' ? 1 : '0'),
        ])->first();

        if (!$cartItem) {
            return response()->json(translate('Product_not_found_in_cart'), 404);
        }

        $removedGroupId = $cartItem->cart_group_id;
        $cartItem->delete();

        if (!Cart::where('cart_group_id', $removedGroupId)->exists()) {
            CartShipping::where('cart_group_id', $removedGroupId)->delete();
        }

        return response()->json(translate('successfully_removed'));
    }

    public function remove_all_from_cart(Request $request)
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
        return response()->json(translate('successfully_removed'));
    }

    public function updateCheckedCartItems(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        $customerId = $user == 'offline' ? ($request->guest_id ?? session('guest_id')) : $user->id;
        $isGuest = $user == 'offline' ? 1 : 0;
        $ids = collect($request->input('ids', []))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int)$id)
            ->unique()
            ->values()
            ->all();

        if (empty($customerId) || empty($ids)) {
            return response()->json(translate('Successfully_Update'), 200);
        }

        $cartQuery = Cart::where([
            'customer_id' => $customerId,
            'is_guest' => $isGuest,
        ])->whereIn('id', $ids);

        if ($request['action'] == 'unchecked') {
            $cartQuery->update(['is_checked' => 0]);
        } elseif ($request['action'] == 'checked') {
            $cartQuery->update(['is_checked' => 1]);
        }

        $allCartGroupIds = Cart::where([
            'customer_id' => $customerId,
            'is_guest' => $isGuest,
        ])->pluck('cart_group_id')->unique()->values();

        $checkedCartGroupIds = Cart::where([
            'customer_id' => $customerId,
            'is_guest' => $isGuest,
            'is_checked' => 1,
        ])->pluck('cart_group_id')->unique()->values();

        if ($allCartGroupIds->count() > 0) {
            $deleteShippingQuery = CartShipping::whereIn('cart_group_id', $allCartGroupIds->toArray());
            if ($checkedCartGroupIds->count() > 0) {
                $deleteShippingQuery->whereNotIn('cart_group_id', $checkedCartGroupIds->toArray());
            }
            $deleteShippingQuery->delete();
        }

        return response()->json(translate('Successfully_Update'), 200);
    }

    public function addProductRestockRequest(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        $product = $this->productRepo->getWebFirstWhereActive(params: ['id' => $request['id']]);

        if ($product && $user != 'offline') {
            $variationCode = '';
            if ($request->has('color')) {
                $variationCode .= Color::where(['code' => $request['color']])->first()->name;
            }

            foreach (json_decode($product['choice_options']) as $key => $choice) {
                if ($variationCode != null) {
                    $variationCode .= '-' . str_replace(' ', '', $request[$choice->name]);
                } else {
                    $variationCode .= str_replace(' ', '', $request[$choice->name]);
                }
            }

            $restockRequest = $this->restockProductRepo->updateOrCreate(params: ['product_id' => $request['id'], 'variant' => $variationCode], value: [
                'product_id' => $request['id'],
                'variant' => $variationCode,
            ]);
            $restockData = [
                'restock_product_id' => $restockRequest ? $restockRequest['id'] : 0,
                'customer_id' => $user->id,
                'variant' => $variationCode,
            ];
            $checkRequest = $this->restockProductCustomerRepo->getFirstWhere(params: $restockData);
            if ($checkRequest) {
                return response()->json([
                    'status' => 'warning',
                    'message' => translate('Already_Requested'),
                ], 200);
            }
            $this->restockProductCustomerRepo->updateOrCreate(params: $restockData, value: $restockData);
            $this->restockProductRepo->updateByParams(params: ['id' => $restockRequest['id']], data: ['updated_at' => Carbon::now()]);
            if ($product['added_by'] == 'seller' && $product?->seller?->cm_firebase_token) {
                $this->sendRestockProductNotificationToAuthor($restockRequest);
            }

            return response()->json([
                'message' => translate('Request_sent_successfully'),
                'topic' => getRestockProductFCMTopic(restockRequest: $restockRequest)
            ], 200);
        }

        return response()->json(['message' => translate('Invalid_product')], 403);
    }

    public function sendRestockProductNotificationToAuthor(mixed $product): void
    {
        $filters = [
            'added_by' => $product['added_by'] == 'seller' ? $product['added_by'] : 'in_house',
            'seller_id' => $product['user_id'],
        ];

        $restockProductList = $this->restockProductRepo->getListWhere(filters: $filters, dataLimit: 'all')->groupBy('product_id');
        $data = [];
        if (count($restockProductList) == 1) {
            $firstProduct = $this->restockProductRepo->getListWhere(orderBy: ['updated_at' => 'desc'], filters: $filters, relations: ['product'], dataLimit: 5)->first();
            $count = $firstProduct?->restock_product_customers_count ?? 0;
            $data = [
                'title' => $firstProduct?->product?->name ?? '',
                'body' => $count < 100 ? translate('This_product_has') . ' ' . $count . ' ' . translate('restock_request') : translate('This_product_has') . ' 99+ ' . translate('restock_request'),
                'image' => getStorageImages(path: $firstProduct?->product?->thumbnail_full_url ?? '', type: 'product'),
                'firebase_token' => $product?->seller?->cm_firebase_token
            ];
        } elseif (count($restockProductList) > 1) {
            $data = [
                'title' => translate('Restock_Request'),
                'body' => (count($restockProductList) < 100 ? count($restockProductList) : '99 +') . ' ' . translate('more_products_have_restock_request'),
                'image' => dynamicAsset(path: 'public/assets/back-end/img/icons/restock-request-icon.svg'),
                'firebase_token' => $product?->seller?->cm_firebase_token
            ];
        }

        event(new RequestProductRestockEvent(key: 'message_from_customer', data: $data));
    }
}
