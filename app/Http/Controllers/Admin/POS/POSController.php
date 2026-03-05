<?php

namespace App\Http\Controllers\Admin\POS;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\CouponRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\DeliveryZipCodeRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Domain\Stock\Support\VariantMatcher;
use App\Enums\ViewPaths\Admin\POS;
use App\Http\Controllers\BaseController;
use App\Services\CartService;
use App\Services\PosCartStateService;
use App\Services\POSService;
use App\Traits\CalculatorTrait;
use App\Traits\CommonTrait;
use App\Utils\OrderManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Branch;
use App\Models\ManageExtraCharge;
use App\Models\ManageBranchProductStock;




class POSController extends BaseController
{
    use CalculatorTrait, CommonTrait;

    /**
     * @param CategoryRepositoryInterface $categoryRepo
     * @param ProductRepositoryInterface $productRepo
     * @param CustomerRepositoryInterface $customerRepo
     * @param OrderRepositoryInterface $orderRepo
     * @param CouponRepositoryInterface $couponRepo
     * @param CartService $cartService
     * @param POSService $POSService
     * @param DeliveryZipCodeRepositoryInterface $deliveryZipCodeRepo
     */
    public function __construct(
        private readonly CategoryRepositoryInterface        $categoryRepo,
        private readonly ProductRepositoryInterface         $productRepo,
        private readonly CustomerRepositoryInterface        $customerRepo,
        private readonly OrderRepositoryInterface           $orderRepo,
        private readonly CouponRepositoryInterface          $couponRepo,
        private readonly CartService                        $cartService,
        private readonly PosCartStateService                $posCartStateService,
        private readonly POSService                         $POSService,
        private readonly DeliveryZipCodeRepositoryInterface $deliveryZipCodeRepo,
        private readonly VariantMatcher                     $variantMatcher,
    ) {}

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        $branchId = $request->input('branch_id');


        return $this->getPOSView(request: $request);
    }

    public function getPOSView(object $request): View
    {
        $categoryId = $request['category_id'];
        $branchId = (int)($request['branch_id'] ?? 1);
        if ($branchId <= 0) {
            $branchId = 1;
        }
        $categories = $this->categoryRepo->getListWhere(orderBy: ['id' => 'desc'], filters: ['position' => 0]);

        $searchValue = $request['searchValue'] ?? null;
        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $productFilters = [
            'added_by' => 'in_house',
            'category_id' => $categoryId,
            'code' => $searchValue,
            'product_type' => 'physical',
            'status' => 1,
        ];

        if ((int)$branchId !== 1 && !empty($branchId)) {
            $availableProductIds = ManageBranchProductStock::query()
                ->where('branch_id', (int)$branchId)
                ->whereNotNull('product_id')
                ->groupBy('product_id')
                ->havingRaw('SUM(current_stock) > 0')
                ->pluck('product_id')
                ->toArray();

            $productFilters['productIds'] = $availableProductIds;
        }

        $products = $this->productRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $searchValue,
            filters: $productFilters,
            relations: ['clearanceSale' => function ($query) {
                return $query->active();
            }],
            dataLimit: 'all',
        );

        if ((int)$branchId === 1 || empty($branchId)) {
            $products = $products->filter(function ($product) {
                return $product->current_stock > 0;
            })->values();
        }


        $page = request()->get('page', 1);
        $totalProducts = $products->count();

        $products = new LengthAwarePaginator(
            $products->forPage($page, $dataLimit)->values(),
            $totalProducts,
            $dataLimit,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $cartId = $this->ensureCurrentPosCartState(
            branchId: $branchId,
            requestedCartId: (string)$request->input('cart_id', '')
        );
        $customers = $this->customerRepo->getListWhereNotIn(ids: [0])
            ->filter(fn($c) => $c->id != 0 && $c->user_type != 1)
            ->values();
        $getCurrentCustomerData = $this->getCustomerDataByCartIdForPOS($cartId);
        $summaryData = array_merge(
            $this->POSService->getSummaryData(
                branchId: $branchId,
                activeCartId: $cartId,
                actorType: 'admin',
                actorId: (int)auth('admin')->id()
            ),
            $getCurrentCustomerData
        );
        $cartItems = $this->getCartData(cartName: $cartId, branchId: $branchId);
        $lastOrderId = (int)($request->input('last_order_id') ?? 0);
        $order = $lastOrderId > 0
            ? $this->orderRepo->getFirstWhere(params: ['id' => $lastOrderId])
            : null;
        $totalHoldOrder = $summaryData['totalHoldOrders'];
        $countries = getWebConfig(name: 'delivery_country_restriction') ? $this->get_delivery_country_array() : COUNTRIES;
        $zipCodes = getWebConfig(name: 'delivery_zip_code_area_restriction') ? $this->deliveryZipCodeRepo->getListWhere(dataLimit: 'all') : 0;
        $branch = Branch::find($branchId);

        return view(POS::INDEX[VIEW], compact(
            'branch',
            'branchId',
            'categories',
            'categoryId',
            'products',
            'cartId',
            'customers',
            'searchValue',
            'summaryData',
            'cartItems',
            'order',
            'totalHoldOrder',
            'countries',
            'zipCodes'
        ));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changeCustomer(Request $request): JsonResponse
    {
        $context = $this->getValidatedWriteContext($request);
        $branchId = $context['branch_id'];
        $currentCartId = $context['cart_id'];

        if ((int)$request['user_id'] !== 0) {
            $cartId = $this->cartService->makeSavedCustomerCartId((int)$request['user_id'], $branchId);
        } elseif (Str::contains($currentCartId, 'walking-customer-')) {
            $cartId = $currentCartId;
        } else {
            $cartId = $this->cartService->generateWalkingCustomerCartId($branchId);
        }

        $this->posCartStateService->ensureCart(
            cartId: $cartId,
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );

        $this->POSService->UpdateSessionWhenCustomerChange(
            cartId: $cartId,
            branchId: $branchId,
            currentCartId: $currentCartId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );
        $getCurrentCustomerData = $this->getCustomerDataByCartIdForPOS($cartId);
        $summaryData = array_merge(
            $this->POSService->getSummaryData(
                branchId: $branchId,
                activeCartId: $cartId,
                actorType: 'admin',
                actorId: (int)auth('admin')->id()
            ),
            $getCurrentCustomerData
        );
        $cartItems = $this->getCartData(cartName: $cartId, branchId: $branchId);
        return response()->json([
            'cart_id' => $cartId,
            'view' => view(POS::SUMMARY[VIEW], compact('summaryData', 'cartItems', 'cartId'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDiscount(Request $request): JsonResponse
    {
        $context = $this->getValidatedWriteContext($request);
        $branchId = $context['branch_id'];
        $cartId = $context['cart_id'];
        if ($request['type'] == 'percent' && ($request['discount'] < 0 || $request['discount'] > 100)) {
            $cartItems = $this->getCartData(cartName: $cartId, branchId: $branchId);
            $text = $request['discount'] < 0
                ? 'Extra_discount_can_not_be_less_than_0_percent'
                : 'Extra_discount_can_not_be_more_than_100_percent';
            Toastr::error(translate($text));
            return response()->json([
                'extraDiscount' => "amount_low",
                'view' => view(POS::CART[VIEW], compact('cartId', 'cartItems'))->render()
            ]);
        }
        $cart = $this->posCartStateService->getPayload(
            cartId: $cartId,
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );
        if (is_array($cart) && count($cart) > 0) {
            $totalProductPrice = 0;
            $productDiscount = 0;
            $productTax = 0;
            $couponDiscount = $cart['coupon_discount'] ?? 0;
            $includeTax = 0;

            foreach ($cart as $item) {
                if (is_array($item)) {
                    $product = $this->productRepo->getFirstWhere(params: ['id' => $item['id']], relations: ['clearanceSale' => function ($query) {
                        return $query->active();
                    }]);
                    $totalProductPrice += $item['price'] * $item['quantity'];
                    $productDiscount += $item['discount'] * $item['quantity'];
                    $productTax += $this->getTaxAmount($item['price'], $product['tax']) * $item['quantity'];
                    if ($product['tax_model'] == 'include') {
                        $includeTax += $productTax;
                    }
                }
            }
            if ($request['type'] == 'percent') {
                $extraDiscount = (($totalProductPrice - $includeTax) / 100) * $request['discount'];
            } else {
                $extraDiscount = $request['discount'];
            }
            $total = $totalProductPrice - $productDiscount + $productTax - $couponDiscount - $extraDiscount - $includeTax;
            if ($total < 0) {
                $cartItems = $this->getCartData(cartName: $cartId, branchId: $branchId);
                return response()->json([
                    'extraDiscount' => "amount_low",
                    'view' => view(POS::CART[VIEW], compact('cartId', 'cartItems'))->render()
                ]);
            } else {
                $cart['ext_discount'] = $request['type'] == 'percent' ? $request['discount'] : currencyConverter(amount: $request['discount']);
                $cart['ext_discount_type'] = $request['type'];
                $this->posCartStateService->putPayload(
                    cartId: $cartId,
                    branchId: $branchId,
                    payload: $cart,
                    actorType: 'admin',
                    actorId: (int)auth('admin')->id()
                );
                $cartItems = $this->getCartData(cartName: $cartId, branchId: $branchId);
                return response()->json([
                    'extraDiscount' => "success",
                    'view' => view(POS::CART[VIEW], compact('cartId', 'cartItems'))->render()
                ]);
            }
        } else {
            $cartItems = $this->getCartData(cartName: $cartId, branchId: $branchId);
            return response()->json([
                'extraDiscount' => "empty",
                'view' => view(POS::CART[VIEW], compact('cartId', 'cartItems'))->render()
            ]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCouponDiscount(Request $request): JsonResponse
    {
        $context = $this->getValidatedWriteContext($request);
        $branchId = $context['branch_id'];
        $cartId = $context['cart_id'];
        $userId = $this->cartService->getUserId($cartId);
        if ($userId != 0) {
            $usedCoupon = $this->orderRepo->getListWhere(filters: ['customer_type' => 'customer', 'coupon_code' => $request['coupon_code']])->count();
            $coupon = $this->couponRepo->getFirstWhereFilters(
                filters: [
                    'code' => $request['coupon_code'],
                    'added_by' => 'admin',
                    'limit' => $usedCoupon,
                    'start_date' => now(),
                    'expire_date' => now(),
                    'status' => 1
                ]
            );
        } else {
            $coupon = $this->couponRepo->getFirstWhereFilters(
                filters: [
                    'code' => $request['coupon_code'],
                    'added_by' => 'admin',
                    'start_date' => now(),
                    'expire_date' => now(),
                    'status' => 1
                ]
            );
        }

        if (!$coupon || $coupon['coupon_type'] == 'free_delivery' || $coupon['coupon_type'] == 'first_order') {
            $cartItems = $this->getCartData(cartName: $cartId, branchId: $branchId);
            return response()->json([
                'coupon' => 'coupon_invalid',
                'view' => view(POS::CART[VIEW], compact('cartId', 'cartItems'))->render()
            ]);
        }

        $carts = $this->posCartStateService->getPayload(
            cartId: $cartId,
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );
        $totalProductPrice = 0;
        $productDiscount = 0;
        $productTax = 0;
        if (($coupon['customer_id'] == '0' || $coupon['customer_id'] == $userId)) {
            if ($carts != null) {
                foreach ($carts as $cart) {
                    if (is_array($cart)) {
                        $product = $this->productRepo->getFirstWhere(params: ['id' => $cart['id']], relations: ['clearanceSale' => function ($query) {
                            return $query->active();
                        }]);
                        $totalProductPrice += $cart['price'] * $cart['quantity'];
                        $productDiscount += $cart['discount'] * $cart['quantity'];
                        $productTax += ($this->getTaxAmount($cart['price'], $product['tax'])) * $cart['quantity'];
                    }
                }
                if ($totalProductPrice >= $coupon['min_purchase']) {
                    $calculation = $this->POSService->getCouponCalculation(coupon: $coupon, totalProductPrice: $totalProductPrice, productDiscount: $productDiscount, productTax: $productTax);
                    $total = $calculation['total'];
                    $discount = $calculation['discount'];
                    if ($total < 0) {
                        $cartItems = $this->getCartData(cartName: $cartId, branchId: $branchId);
                        return response()->json([
                            'coupon' => "amount_low",
                            'view' => view(POS::CART[VIEW], compact('cartId', 'cartItems'))->render()
                        ]);
                    }

                    $this->POSService->putCouponDataOnSession(
                        cartId: $cartId,
                        discount: $discount,
                        couponTitle: $coupon['title'],
                        couponBearer: $coupon['coupon_bearer'],
                        couponCode: $request['coupon_code'],
                        branchId: $branchId,
                        actorType: 'admin',
                        actorId: (int)auth('admin')->id(),
                    );

                    $cartItems = $this->getCartData(cartName: $cartId, branchId: $branchId);
                    return response()->json([
                        'coupon' => 'success',
                        'view' => view(POS::CART[VIEW], compact('cartId', 'cartItems'))->render()
                    ]);
                }
            } else {
                $cartItems = $this->getCartData(cartName: $cartId, branchId: $branchId);
                return response()->json([
                    'coupon' => 'cart_empty',
                    'view' => view(POS::CART[VIEW], compact('cartId', 'cartItems'))->render()
                ]);
            }
        }
        $cartItems = $this->getCartData(cartName: $cartId, branchId: $branchId);
        return response()->json([
            'coupon' => 'coupon_invalid',
            'view' => view(POS::CART[VIEW], compact('cartId', 'cartItems'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getQuickView(Request $request): JsonResponse
    {
        $branchId = (int)($request->branch_id ?? 0);
        Log::info('QuickView Request', ['product_id' => $request->product_id, 'branch_id' => $branchId]);

        $product = $this->productRepo->getFirstWhereWithCount(
            params: ['id' => $request['product_id']],
            withCount: ['reviews'],
            relations: ['brand', 'category', 'rating', 'tags', 'digitalVariation', 'clearanceSale' => fn($q) => $q->active()]
        );
        if (!$product) {
            return response()->json([
                'success' => 0,
                'message' => translate('product_not_found'),
            ]);
        }

        $product->selected_branch_id = $branchId;

        if ($branchId > 0 && !empty($product->choice_options)) {
            $choiceOptions = json_decode($product->choice_options, true) ?? [];
            $branchVariationRows = ManageBranchProductStock::query()
                ->where('branch_id', $branchId)
                ->where('product_id', $product->id)
                ->where('current_stock', '>', 0)
                ->get(['variation_type', 'variation_key']);

            $branchVariations = [];
            foreach ($branchVariationRows as $branchVariationRow) {
                foreach ([$branchVariationRow->variation_type, $branchVariationRow->variation_key] as $variationValue) {
                    $rawValue = trim((string)$variationValue);
                    if ($rawValue === '' || $this->variantMatcher->isDefault($rawValue)) {
                        continue;
                    }
                    $branchVariations[] = $rawValue;
                }
            }
            $branchVariations = array_values(array_unique($branchVariations));

            $hasSingleChoiceOption = count($choiceOptions) === 1;
            foreach ($choiceOptions as $key => $choice) {
                $existingOptions = $choice['options'] ?? [];
                if (empty($existingOptions) || empty($branchVariations)) {
                    continue;
                }

                $filteredOptions = array_values(array_filter($existingOptions, function ($option) use ($branchVariations) {
                    foreach ($branchVariations as $branchVariation) {
                        if ($this->variantMatcher->matches($option, $branchVariation)) {
                            return true;
                        }
                    }
                    return false;
                }));

                if (!empty($filteredOptions)) {
                    $choiceOptions[$key]['options'] = $filteredOptions;
                    continue;
                }

                if ($hasSingleChoiceOption) {
                    $dynamicBranchOptions = array_values(array_unique(array_filter(array_map(function ($branchVariation) use ($existingOptions) {
                        foreach ($existingOptions as $existingOption) {
                            if ($this->variantMatcher->matches($existingOption, $branchVariation)) {
                                return $existingOption;
                            }
                        }
                        return $branchVariation;
                    }, $branchVariations))));

                    if (!empty($dynamicBranchOptions)) {
                        $choiceOptions[$key]['options'] = $dynamicBranchOptions;
                    }
                }
            }

            $product->filtered_choice_options = json_encode($choiceOptions);
        }

        $charges = ManageExtraCharge::whereIn('type', ['exchange', 'installation'])
            ->where('status', 1)
            ->whereIn('category_id', [$product->category_id, $product->sub_category_id, $product->sub_sub_category_id])
            ->get();

        $extraCharges = [];
        foreach ($charges as $charge) {
            $extraCharges[$charge->type] = $charge->charges;
        }
        $extraCharges['exchange'] = $extraCharges['exchange'] ?? 0;
        $extraCharges['installation'] = $extraCharges['installation'] ?? 0;
        $product->extraCharges = $extraCharges;

        return response()->json([
            'success' => 1,
            'view' => view(POS::QUICK_VIEW[VIEW], compact('product'))->render(),
            'extraCharges' => $extraCharges,
            'product' => $product,
        ]);
    }




    /**
     * @return array
     */
    protected function getCustomerDataByCartIdForPOS(string $cartId): array
    {
        $cartId = trim($cartId);
        if ($cartId === '' || Str::contains($cartId, 'walking-customer')) {
            $currentCustomerInfo = ['customerName' => translate('walking_customer')];
            $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => '0']);
        } else {
            $userId = (int)(explode('-', $cartId)[2] ?? 0);
            $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => $userId]);
            if ($currentCustomerData) {
                $currentCustomerInfo = $this->cartService->getCustomerInfo(currentCustomerData: $currentCustomerData, customerId: $userId);
            } else {
                $currentCustomerInfo = ['customerName' => translate('walking_customer')];
                $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => '0']);
            }
        }
        return [
            'currentCustomer' => $currentCustomerInfo['customerName'],
            'currentCustomerData' => $currentCustomerData
        ];
    }

    /**
     * @param string $cartName
     * @return array
     */
    protected function getCustomerCartData(string $cartName): array
    {
        $customerCartData = [];
        if (Str::contains($cartName, 'walking-customer')) {
            $currentCustomerInfo = [
                'customerName' => translate('walking_customer'),
                'customerPhone' => "",
            ];
            $customerId = 0;
        } else {
            $customerId = explode('-', $cartName)[2];
            $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => $customerId]);
            $currentCustomerInfo = $this->cartService->getCustomerInfo(currentCustomerData: $currentCustomerData, customerId: $customerId);
        }
        $customerCartData[$cartName] = [
            'customerName' => $currentCustomerInfo['customerName'],
            'customerPhone' => $currentCustomerInfo['customerPhone'],
            'customerId' => $customerId,
        ];
        return $customerCartData;
    }

    protected function calculateCartItemsData(string $cartName, array $customerCartData, int $branchId): array
    {
        $cartItemValue = [];
        $installationTotal = 0.0;
        $exchangeTotal = 0.0;
        $hasIncludeTaxModel = false;
        $hasExcludeTaxModel = false;
        $subTotalCalculation = [
            'countItem' => 0,
            'totalQuantity' => 0,
            'taxCalculate' => 0,
            'totalTaxShow' => 0,
            'totalTax' => 0,
            'totalIncludeTax' => 0,
            'subtotal' => 0,
            'discountOnProduct' => 0,
            'productSubtotal' => 0,
        ];
        $cartPayload = $this->posCartStateService->getPayload(
            cartId: $cartName,
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );
        if (!empty($cartPayload)) {
            foreach ($cartPayload as $cartItem) {
                if (is_array($cartItem)) {
                    $product = $this->productRepo->getFirstWhere(params: ['id' => $cartItem['id']], relations: ['clearanceSale' => function ($query) {
                        return $query->active();
                    }]);
                    if ($product) {
                        $lineTaxModel = strtolower((string)($product['tax_model'] ?? ($cartItem['tax_model'] ?? 'exclude')));
                        if ($lineTaxModel === 'include') {
                            $hasIncludeTaxModel = true;
                        } else {
                            $hasExcludeTaxModel = true;
                        }
                        $cartSubTotalCalculation = $this->cartService->getCartSubtotalCalculation(
                            product: $product,
                            cartItem: $cartItem,
                            calculation: $subTotalCalculation
                        );
                        if ($cartItem['customerId'] == $customerCartData[$cartName]['customerId']) {
                            $cartItem['productSubtotal'] = $cartSubTotalCalculation['productSubtotal'];
                            $installationTotal += (float)($cartItem['installation_charge'] ?? 0) * (int)($cartItem['quantity'] ?? 0);
                            $exchangeTotal += (float)($cartItem['exchange_charge'] ?? 0);
                            $cartItemValue[] = $cartItem;
                            $subTotalCalculation['customerOnHold'] = $cartItem['customerOnHold'];

                            $subTotalCalculation['countItem'] += $cartSubTotalCalculation['countItem'];
                            $subTotalCalculation['totalQuantity'] += $cartSubTotalCalculation['totalQuantity'];
                            $subTotalCalculation['taxCalculate'] += $cartSubTotalCalculation['taxCalculate'];
                            $subTotalCalculation['totalTaxShow'] += $cartSubTotalCalculation['totalTaxShow'];
                            $subTotalCalculation['totalTax'] += $cartSubTotalCalculation['totalTax'];
                            $subTotalCalculation['totalIncludeTax'] += $cartSubTotalCalculation['totalIncludeTax'];
                            $subTotalCalculation['productSubtotal'] += $cartSubTotalCalculation['productSubtotal'];
                            $subTotalCalculation['subtotal'] += $cartSubTotalCalculation['subtotal'];
                            $subTotalCalculation['discountOnProduct'] += $cartSubTotalCalculation['discountOnProduct'];
                        }
                    }
                }
            }
        }

        $taxModel = ($hasIncludeTaxModel && !$hasExcludeTaxModel) ? 'include' : 'exclude';
        $summary = OrderManager::calculatePosRetailVatSummary(
            itemPrice: (float)$subTotalCalculation['subtotal'] + (float)$subTotalCalculation['discountOnProduct'],
            itemDiscount: (float)$subTotalCalculation['discountOnProduct'],
            extraDiscountInput: abs((float)($cartPayload['ext_discount'] ?? 0)),
            extraDiscountType: (string)($cartPayload['ext_discount_type'] ?? 'amount'),
            couponDiscount: abs((float)($cartPayload['coupon_discount'] ?? 0)),
            totalInstallationPrice: $installationTotal,
            totalExchangePrice: $exchangeTotal,
            taxModel: $taxModel
        );

        $totalCalculation = $this->cartService->getTotalCalculation(
            subTotalCalculation: $subTotalCalculation,
            cartName: $cartName,
            installationCharge: $installationTotal,
            exchangeCharge: $exchangeTotal
        );
        return [
            'countItem' => $subTotalCalculation['countItem'],
            'total' => $summary['totalAmount'],
            'subtotal' => $subTotalCalculation['subtotal'],
            'taxableBase' => $summary['taxableBase'],
            'subTotalWithVat' => $summary['subTotalWithVat'],
            'taxCalculate' => $summary['taxTotal'],
            'totalTaxShow' => $summary['taxTotal'],
            'totalTax' => $summary['taxTotal'],
            'discountOnProduct' => $subTotalCalculation['discountOnProduct'],
            'productSubtotal' => $subTotalCalculation['productSubtotal'],
            'cartItemValue' => $cartItemValue,
            'customerOnHold' => $subTotalCalculation['customerOnHold'] ?? false,
            'couponDiscount' => abs((float)($cartPayload['coupon_discount'] ?? 0)),
            'extraDiscount' => $summary['extraDiscount'],
            'totalInstallationPrice' => $installationTotal,
            'totalExchangePrice' => $exchangeTotal,
            'legacyTotalBeforeVat' => $totalCalculation['total'],
        ];
    }

    protected function getCartData(string $cartName, int $branchId): array
    {
        $customerCartData = $this->getCustomerCartData(cartName: $cartName);
        $cartItemData = $this->calculateCartItemsData(cartName: $cartName, customerCartData: $customerCartData, branchId: $branchId);
        return array_merge($customerCartData[$cartName], $cartItemData);
    }

    protected function ensureCurrentPosCartState(int $branchId = 1, string $requestedCartId = ''): string
    {
        if ($branchId <= 0) {
            $branchId = 1;
        }

        $requestedCartId = trim($requestedCartId);
        if (
            $requestedCartId !== ''
            && $this->cartService->cartBelongsToBranch($requestedCartId, $branchId)
        ) {
            $this->posCartStateService->ensureCart(
                cartId: $requestedCartId,
                branchId: $branchId,
                actorType: 'admin',
                actorId: (int)auth('admin')->id()
            );
            return $requestedCartId;
        }

        $cartId = $this->cartService->generateWalkingCustomerCartId($branchId);
        $this->posCartStateService->ensureCart(
            cartId: $cartId,
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );

        return $cartId;
    }

    private function getValidatedWriteContext(Request $request): array
    {
        $branchId = (int)$request->input('branch_id', 0);
        $cartId = trim((string)$request->input('cart_id', ''));
        if ($branchId <= 0 || $cartId === '' || !$this->cartService->cartBelongsToBranch($cartId, $branchId)) {
            throw ValidationException::withMessages([
                'cart_id' => [translate('invalid_request')],
            ]);
        }

        $this->posCartStateService->assertCart(
            cartId: $cartId,
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );

        return [
            'branch_id' => $branchId,
            'cart_id' => $cartId,
        ];
    }

    public function getSearchedProductsView(Request $request): JsonResponse
    {
        $branchId = (int)($request['branch_id'] ?? 0);
        $products = $this->productRepo->getListWithScope(
            scope: 'active',
            filters: [
                'added_by' => 'in_house',
                'keywords' => $request['name'],
                'search_from' => 'pos'
            ],
            dataLimit: 'all'
        );

        if ($branchId > 1) {
            $availableProductIds = ManageBranchProductStock::query()
                ->where('branch_id', $branchId)
                ->whereNotNull('product_id')
                ->groupBy('product_id')
                ->havingRaw('SUM(current_stock) > 0')
                ->pluck('product_id')
                ->all();

            $products = $products->whereIn('id', $availableProductIds)->values();
        } else {
            $products = $products->filter(fn($product) => (int)($product->current_stock ?? 0) > 0)->values();
        }

        $data = [
            'count' => $products->count(),
            'result' => view(POS::SEARCH[VIEW], compact('products'))->render()
        ];
        if ($products->count() > 0) {
            $data += ['id' => $products[0]->id];
        }

        return response()->json($data);
    }
}
