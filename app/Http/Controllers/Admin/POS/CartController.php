<?php

namespace App\Http\Controllers\Admin\POS;

use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Enums\SessionKey;
use App\Enums\ViewPaths\Admin\Cart;
use App\Enums\ViewPaths\Admin\POS;
use App\Http\Controllers\BaseController;
use App\Services\CartService;
use App\Services\POSService;
use App\Traits\CalculatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CartController extends BaseController
{
    use CalculatorTrait;

    /**
     * @param ProductRepositoryInterface $productRepo
     * @param ColorRepositoryInterface $colorRepo
     * @param CustomerRepositoryInterface $customerRepo
     * @param CartService $cartService
     * @param POSService $POSService
     */
    public function __construct(
        private readonly ProductRepositoryInterface  $productRepo,
        private readonly ColorRepositoryInterface    $colorRepo,
        private readonly CustomerRepositoryInterface $customerRepo,
        private readonly CartService                 $cartService,
        private readonly POSService                  $POSService,
    ) {}

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        // TODO: Implement index() method.
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getVariantPrice(Request $request): JsonResponse
    {
        $product = $this->productRepo->getFirstWhere(params: ['id' => $request['id']], relations: ['digitalVariation', 'clearanceSale' => function ($query) {
            return $query->active();
        }]);
        $colorName = $this->colorRepo->getFirstWhere(['code' => $request['color']])->name ?? null;
        $data = $this->cartService->getVariantData(
            request: $request,
            product: $product,
            colorName: $colorName
        );
        return response()->json($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateQuantity(Request $request): JsonResponse
    {
        $activeBranchId = (int)($request['branch_id'] ?? session(SessionKey::POS_BRANCH_ID) ?? 1);
        if ($activeBranchId <= 0) {
            $activeBranchId = 1;
        }
        session()->put(SessionKey::POS_BRANCH_ID, $activeBranchId);
        $cartId = $this->getOrCreateBranchCartId($activeBranchId, (string)($request['cart_id'] ?? ''));
        if ($request['quantity'] > 0) {
            $product = $this->productRepo->getFirstWhere(params: ['id' => $request['key']], relations: ['clearanceSale' => function ($query) {
                return $query->active();
            }]);
            $quantity = $this->cartService->getQuantityAndUpdateTime(
                request: $request,
                product: $product,
                branchId: $activeBranchId,
                cartId: $cartId,
            );
            $cartItems = $this->getCartData(cartName: $cartId);
            if ($product['product_type'] == 'physical' && $quantity < 0) {
                return response()->json([
                    'qty' => $quantity,
                    'productType' => $product['product_type'],
                    'view' => view(Cart::CART[VIEW], compact('cartId', 'cartItems'))->render()
                ]);
            } else {
                return response()->json([
                    'quantityUpdate' => 1,
                    'view' => view(Cart::CART[VIEW], compact('cartId', 'cartItems'))->render()
                ]);
            }
        } else {
            $cartItems = $this->getCartData(cartName: $cartId);
            return response()->json([
                'upQty' => 'zeroNegative',
                'view' => view(Cart::CART[VIEW], compact('cartId', 'cartItems'))->render()
            ]);
        }
    }

    public function addToCart(Request $request): JsonResponse

    {
        $activeBranchId = (int)($request['branch_id'] ?? session(SessionKey::POS_BRANCH_ID) ?? 1);
        if ($activeBranchId <= 0) {
            $activeBranchId = 1;
        }
        session()->put(SessionKey::POS_BRANCH_ID, $activeBranchId);
        $cartId = $this->getOrCreateBranchCartId($activeBranchId, (string)($request['cart_id'] ?? ''));
        $product = $this->productRepo->getFirstWhere(params: ['id' => $request['id']], relations: ['digitalVariation', 'clearanceSale' => function ($query) {
            return $query->active();
        }]);
        $colorName = $this->colorRepo->getFirstWhere(['code' => $request['color']])->name ?? null;
        $variations = [];
        if (!empty($colorName)) {
            $variations['color'] = $colorName;
        }
        $variant = $this->cartService->makeVariation(
            request: $request,
            colorName: $colorName,
            choiceOptions: json_decode($product['choice_options'])
        );
        if ($product['product_type'] == 'digital' && $request->has('variant_key')) {
            foreach ($product['digitalVariation'] as $digitalVariation) {
                if ($digitalVariation['variant_key'] == $request['variant_key']) {
                    $variant = $digitalVariation['variant_key'];
                }
            }
        }
        foreach (json_decode($product['choice_options']) as $choice) {
            $choiceValue = $request[$choice->name] ?? null;
            if (!is_null($choiceValue) && $choiceValue !== '') {
                $variations[$choice->title] = $choiceValue;
            }
        }
        $price = $product['unit_price'];
        $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $price);
        $cartData = session($cartId);

        $exchangeCharge = $request['exchange_charge'] ?? 0;
        $installationCharge = $request['installation_charge'] ?? 0;


        $exchangeTotel = $exchangeCharge;
        $installationTotel = $installationCharge;
        $requestedLineKey = trim((string)($request['line_key'] ?? ''));
        $lineKey = $requestedLineKey !== '' ? $requestedLineKey : (string)Str::uuid();
        $quantityForUpdate = max(1, (int)($request['quantity_in_cart'] ?? $request['quantity'] ?? 1));
        $matchedCartIndex = null;
        if ($cartId && session()->has($cartId) && is_array($cartData) && count($cartData) > 0) {
            foreach ($cartData as $key => $cartItem) {
                if (!is_array($cartItem) || (int)($cartItem['id'] ?? 0) !== (int)$request['id']) {
                    continue;
                }

                $cartItemLineKey = trim((string)($cartItem['line_key'] ?? ''));
                $cartItemVariant = trim((string)($cartItem['variant'] ?? ''));
                $isSameVariant = $cartItemVariant === trim((string)$variant);
                if (!$isSameVariant) {
                    continue;
                }

                if ($requestedLineKey !== '' && $cartItemLineKey !== $requestedLineKey) {
                    continue;
                }

                $matchedCartIndex = $key;
                if ($cartItemLineKey !== '') {
                    $lineKey = $cartItemLineKey;
                }
                break;
            }
        }

        if (!is_null($matchedCartIndex)) {
            if ($variant != null) {
                $price = $this->cartService->getVariationPrice(variation: json_decode($product['variation']), variant: $variant);
                $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $price);
            }
            if ($product['product_type'] == 'digital' && $request->has('variant_key')) {
                foreach ($product['digitalVariation'] as $digitalVariation) {
                    if ($digitalVariation['variant_key'] == $request['variant_key']) {
                        $variant = $digitalVariation['variant_key'];
                        $price = $digitalVariation['price'];
                        $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $price);
                    }
                }
            }
            $currentQty = $this->cartService->checkCurrentStock(
                variant: $variant,
                variation: (array)json_decode($product['variation'] ?? '[]'),
                productQty: (int)$product['current_stock'],
                quantity: $quantityForUpdate,
                branchId: (int)($request['branch_id'] ?? 0),
                productId: (int)$product['id'],
                productType: (string)$product['product_type'],
                productBranchId: (int)($product['branch_id'] ?? 0),
            );
            if ($product['product_type'] == 'physical' && $currentQty < 0) {
                $cartItems = $this->getCartData(cartName: $cartId);
                return response()->json([
                    'data' => 0,
                    'view' => view(Cart::CART[VIEW], compact('cartId', 'cartItems'))->render()
                ]);
            }
            $cartItem = $this->cartService->addCartDataOnSession(
                product: $product,
                quantity: $quantityForUpdate,
                price: $price,
                discount: $discount,
                variant: $variant,
                variations: $variations,
                extra: [
                    'exchange_charge' => $exchangeTotel,
                    'installation_charge' => $installationTotel,
                    'branch_id' => $activeBranchId,
                    'line_key' => $lineKey,
                ],
                cartId: $cartId,
            );
            unset($cartData[$matchedCartIndex]);
            $cartData[] = $cartItem;
            session([$cartId => $cartData]);
            $getCurrentCustomerData = $this->getCustomerDataFromSessionForPOS();
            $summaryData = array_merge($this->POSService->getSummaryData($activeBranchId), $getCurrentCustomerData);
            $cartItems = $this->getCartData(cartName: $cartId);
            Log::info('POS_CART_ADD_UPDATE_LINE', [
                'cart_id' => $cartId,
                'session_id' => session()->getId(),
                'session_current_user' => (string)(session(SessionKey::CURRENT_USER) ?? ''),
                'request_product_id' => (int)$request['id'],
                'request_variant' => (string)$variant,
                'request_line_key' => $requestedLineKey,
                'matched_line_key' => $lineKey,
                'qty' => $quantityForUpdate,
            ]);
            return response()->json([
                'data' => 1,
                'inCartData' => 1,
                'requestQuantity' => $quantityForUpdate,
                'view' => view(Cart::SUMMARY[VIEW], compact('summaryData', 'cartItems', 'installationTotel', 'exchangeTotel'))->render()
            ]);
        }
        if ($variant != null) {
            $price = $this->cartService->getVariationPrice(variation: json_decode($product['variation']), variant: $variant);
            $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $price);
        }

        if ($product['product_type'] == 'digital' && $request->has('variant_key')) {
            foreach ($product['digitalVariation'] as $digitalVariation) {
                if ($digitalVariation['variant_key'] == $request['variant_key']) {
                    $variant = $digitalVariation['variant_key'];
                    $price = $digitalVariation['price'];
                    $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $price);
                }
            }
        }
        $currentQty = $this->cartService->checkCurrentStock(
            variant: $variant,
            variation: (array)json_decode($product['variation'] ?? '[]'),
            productQty: (int)$product['current_stock'],
            quantity: (int)$request['quantity'],
            branchId: (int)($request['branch_id'] ?? 0),
            productId: (int)$product['id'],
            productType: (string)$product['product_type'],
            productBranchId: (int)($product['branch_id'] ?? 0),
        );
        if ($product['product_type'] == 'physical' && $currentQty < 0) {
            $cartItems = $this->getCartData(cartName: $cartId);
            return response()->json([
                'data' => 0,
                'view' => view(Cart::CART[VIEW], compact('cartId', 'cartItems'))->render()
            ]);
        }
        $sessionData = $this->cartService->addCartDataOnSession(
            product: $product,
            quantity: $request['quantity'],
            price: $price,
            discount: $discount,
            variant: $variant,
            variations: $variations,
            extra: [
                'exchange_charge' => $exchangeTotel,
                'installation_charge' => $installationTotel,
                'branch_id' => $activeBranchId,
                'line_key' => $lineKey,

            ],
            cartId: $cartId,
        );
        $cartSnapshot = collect((array)session($cartId, []))
            ->filter(fn($row) => is_array($row))
            ->map(fn($row) => [
                'id' => (int)($row['id'] ?? 0),
                'variant' => (string)($row['variant'] ?? ''),
                'line_key' => (string)($row['line_key'] ?? ''),
                'qty' => (int)($row['quantity'] ?? 0),
            ])
            ->values()
            ->all();
        Log::info('POS_CART_ADD_NEW_LINE', [
            'cart_id' => $cartId,
            'session_id' => session()->getId(),
            'session_current_user' => (string)(session(SessionKey::CURRENT_USER) ?? ''),
            'request_product_id' => (int)$request['id'],
            'request_variant' => (string)$variant,
            'generated_line_key' => $lineKey,
            'qty' => (int)$request['quantity'],
            'cart_snapshot' => $cartSnapshot,
        ]);
        $cartItems = $this->getCartData(cartName: $cartId);

        return response()->json([
            'data' => $sessionData,
            'view' => view(Cart::CART[VIEW], compact('cartId', 'cartItems', 'installationTotel', 'exchangeTotel'))->render()
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeCart(Request $request): JsonResponse
    {
        $activeBranchId = (int)($request['branch_id'] ?? session(SessionKey::POS_BRANCH_ID) ?? 1);
        if ($activeBranchId <= 0) {
            $activeBranchId = 1;
        }
        session()->put(SessionKey::POS_BRANCH_ID, $activeBranchId);
        $cartId = $this->getOrCreateBranchCartId($activeBranchId, (string)($request['cart_id'] ?? ''));
        $cart = session($cartId);
        $cartKeeper = [];
        $lineKey = trim((string)($request['line_key'] ?? ''));
        if (session()->has($cartId) && count($cart) > 0) {
            foreach ($cart as $cartItem) {
                if (is_array($cartItem)) {
                    $itemLineKey = trim((string)($cartItem['line_key'] ?? ''));
                    if ($lineKey !== '' && $itemLineKey !== '') {
                        if ($itemLineKey !== $lineKey) {
                            $cartKeeper[] = $cartItem;
                        }
                        continue;
                    }

                    if ($cartItem['id'] != $request['id']) {
                        $cartKeeper[] = $cartItem;
                    } else {
                        if ($cartItem['variant'] != $request['variant']) {
                            $cartKeeper[] = $cartItem;
                        }
                    }
                }
            }
        }
        session()->put($cartId, $cartKeeper);
        $cartItems = $this->getCartData(cartName: $cartId);
        return response()->json(
            ['view' => view(Cart::CART[VIEW], compact('cartId', 'cartItems'))->render()]
        );
    }

    /**
     * @return RedirectResponse
     */
    public function clearSessionCartIds(): RedirectResponse
    {
        $branchId = (int)request()->input('branch_id', 0);
        if ($branchId <= 0) {
            $branchId = (int)(session(SessionKey::POS_BRANCH_ID) ?? 1);
        }
        if ($branchId <= 0) {
            $branchId = 1;
        }
        session()->put(SessionKey::POS_BRANCH_ID, $branchId);

        $cartNames = array_values(array_filter((array)(session(SessionKey::CART_NAME) ?? []), function ($cartName) use ($branchId) {
            return !$this->cartService->cartBelongsToBranch((string)$cartName, $branchId);
        }));

        foreach ((array)(session(SessionKey::CART_NAME) ?? []) as $cartName) {
            if ($this->cartService->cartBelongsToBranch((string)$cartName, $branchId)) {
                session()->forget($cartName);
            }
        }
        session()->put(SessionKey::CART_NAME, $cartNames);
        session()->forget(SessionKey::CURRENT_USER);

        $newCartId = $this->cartService->generateWalkingCustomerCartId($branchId);
        session()->put(SessionKey::CURRENT_USER, $newCartId);
        $cartNames[] = $newCartId;
        session()->put(SessionKey::CART_NAME, array_values(array_unique($cartNames)));
        session()->put($newCartId, []);

        return redirect()->route('admin.pos.index', ['branch_id' => $branchId, 'cart_id' => $newCartId]);
    }

    /**
     * @return JsonResponse
     */
    public function getCartIds(): JsonResponse
    {
        session()->put(SessionKey::POS_BRANCH_ID, (int)(request()->input('branch_id', session(SessionKey::POS_BRANCH_ID) ?? 1)));
        $this->getOrCreateBranchCartId(
            (int)(session(SessionKey::POS_BRANCH_ID) ?? 1),
            (string)(request()->input('cart_id', ''))
        );
        $this->cartService->getCartKeeper();
        $getCurrentCustomerData = $this->getCustomerDataFromSessionForPOS();
        $summaryData = array_merge(
            $this->POSService->getSummaryData((int)(session(SessionKey::POS_BRANCH_ID) ?? 1)),
            $getCurrentCustomerData
        );
        $cartItems = $this->getCartData(cartName: session(SessionKey::CURRENT_USER));
        return response()->json([
            'view' => view(Cart::SUMMARY[VIEW], compact('summaryData', 'cartItems'))->render(),
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function emptyCart(): JsonResponse
    {
        session()->put(SessionKey::POS_BRANCH_ID, (int)(request()->input('branch_id', session(SessionKey::POS_BRANCH_ID) ?? 1)));
        $cartId = $this->getOrCreateBranchCartId(
            (int)(session(SessionKey::POS_BRANCH_ID) ?? 1),
            (string)(request()->input('cart_id', ''))
        );
        session()->forget($cartId);
        $this->cartService->getNewCartSession(cartId: $cartId);
        $getCurrentCustomerData = $this->getCustomerDataFromSessionForPOS();
        $summaryData = array_merge(
            $this->POSService->getSummaryData((int)(session(SessionKey::POS_BRANCH_ID) ?? 1)),
            $getCurrentCustomerData
        );
        $cartItems = $this->getCartData(cartName: $cartId);
        return response()->json([
            'view' => view(Cart::SUMMARY[VIEW], compact('summaryData', 'cartItems'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function changeCart(Request $request): RedirectResponse
    {
        $branchId = (int)$request->input('branch_id', session(SessionKey::POS_BRANCH_ID) ?? 1);
        if ($branchId <= 0) {
            $branchId = 1;
        }
        session()->put(SessionKey::POS_BRANCH_ID, $branchId);

        if (!$this->cartService->cartBelongsToBranch((string)$request['cart_id'], $branchId)) {
            Toastr::error(translate('invalid_request'));
            return redirect()->route('admin.pos.index', ['branch_id' => $branchId]);
        }
        $this->cartService->customerOnHoldStatus(status: true);
        session()->put(SessionKey::CURRENT_USER, $request['cart_id']);
        $this->cartService->customerOnHoldStatus(status: false);
        Toastr::success($request['cart_id'] . ' ' . translate('order_is_now_resumed'));

        return redirect()->route('admin.pos.index', ['branch_id' => $branchId, 'cart_id' => (string)$request['cart_id']]);
    }

    /**
     * @return RedirectResponse
     */
    public function addNewCartId(): RedirectResponse
    {
        $branchId = (int)request()->input('branch_id', session(SessionKey::POS_BRANCH_ID) ?? 1);
        if ($branchId <= 0) {
            $branchId = 1;
        }
        session()->put(SessionKey::POS_BRANCH_ID, $branchId);
        $cart = session(session(SessionKey::CURRENT_USER));
        if (session()->has(session(SessionKey::CURRENT_USER)) && count($cart) > 0) {
            Toastr::success(translate('this_order_is_now_on_hold'));
        }
        $this->cartService->customerOnHoldStatus(status: true);
        $this->cartService->getNewCartId();
        $newCartId = (string)(session(SessionKey::CURRENT_USER) ?? '');

        return redirect()->route('admin.pos.index', ['branch_id' => $branchId, 'cart_id' => $newCartId]);
    }

    /**
     * @return array
     */
    protected function getCustomerDataFromSessionForPOS(): array
    {
        if (Str::contains(session(SessionKey::CURRENT_USER), 'walking-customer')) {
            $currentCustomer = translate('walking_customer');
            $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => '0']);
        } else {
            $userId = explode('-', session(SessionKey::CURRENT_USER))[2];
            $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => $userId]);
            $currentCustomer = $currentCustomerData['f_name'] . ' ' . $currentCustomerData['l_name'] . ' (' . $currentCustomerData['phone'] . ')';
        }
        return [
            'currentCustomer' => $currentCustomer,
            'currentCustomerData' => $currentCustomerData
        ];
    }

    /**
     * @param string $cartName
     * @return array
     * @function getCustomerCartData ,used for process data
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

    protected function calculateCartItemsData(string $cartName, array $customerCartData): array
    {
        $cartItemValue = [];
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
        if (session()->get($cartName)) {
            foreach (session()->get($cartName) as $cartItem) {
                if (is_array($cartItem)) {
                    $product = $this->productRepo->getFirstWhere(params: ['id' => $cartItem['id']], relations: ['clearanceSale' => function ($query) {
                        return $query->active();
                    }]);
                    if (!$product) {
                        continue;
                    }
                    $cartSubTotalCalculation = $this->cartService->getCartSubtotalCalculation(
                        product: $product,
                        cartItem: $cartItem,
                        calculation: $subTotalCalculation
                    );
                    if ($cartItem['customerId'] == $customerCartData[$cartName]['customerId']) {
                        $cartItem['productSubtotal'] = $cartSubTotalCalculation['productSubtotal'];
                        $subTotalCalculation['customerOnHold'] = $cartItem['customerOnHold'];
                        $cartItemValue[] = $cartItem;

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
        $totalCalculation = $this->cartService->getTotalCalculation(
            subTotalCalculation: $subTotalCalculation,
            cartName: $cartName
        );
        return [
            'countItem' => $subTotalCalculation['countItem'],
            'total' => $totalCalculation['total'],
            'subtotal' => $subTotalCalculation['subtotal'],
            'taxCalculate' => $subTotalCalculation['taxCalculate'],
            'totalTaxShow' => $subTotalCalculation['totalTaxShow'],
            'totalTax' => $subTotalCalculation['totalTax'],
            'discountOnProduct' => $subTotalCalculation['discountOnProduct'],
            'productSubtotal' => $subTotalCalculation['productSubtotal'],
            'cartItemValue' => $cartItemValue,
            'couponDiscount' => $totalCalculation['couponDiscount'],
            'extraDiscount' => $totalCalculation['extraDiscount'],
            'customerOnHold' => $subTotalCalculation['customerOnHold'] ?? false,
        ];
    }

    protected function getCartData(string $cartName): array
    {
        $customerCartData = $this->getCustomerCartData(cartName: $cartName);
        $cartItemData = $this->calculateCartItemsData(cartName: $cartName, customerCartData: $customerCartData);
        return array_merge($customerCartData[$cartName], $cartItemData);
    }

    protected function getOrCreateBranchCartId(int $branchId, string $requestedCartId = ''): string
    {
        if ($branchId <= 0) {
            $branchId = 1;
        }

        $cartNames = (array)(session(SessionKey::CART_NAME) ?? []);
        $requestedCartId = trim($requestedCartId);
        if (
            $requestedCartId !== ''
            && $this->cartService->cartBelongsToBranch($requestedCartId, $branchId)
        ) {
            if (!in_array($requestedCartId, $cartNames, true)) {
                $cartNames[] = $requestedCartId;
                session()->put(SessionKey::CART_NAME, $cartNames);
            }
            if (!session()->has($requestedCartId) || !is_array(session($requestedCartId))) {
                session()->put($requestedCartId, []);
            }
            session()->put(SessionKey::CURRENT_USER, $requestedCartId);
            return $requestedCartId;
        }

        $currentCartId = (string)(session(SessionKey::CURRENT_USER) ?? '');
        if (
            $this->cartService->cartBelongsToBranch($currentCartId, $branchId)
            && session()->has($currentCartId)
            && is_array(session($currentCartId))
        ) {
            return $currentCartId;
        }

        $newCartId = $this->cartService->generateWalkingCustomerCartId($branchId);
        session()->put(SessionKey::CURRENT_USER, $newCartId);
        if (!in_array($newCartId, $cartNames, true)) {
            $cartNames[] = $newCartId;
            session()->put(SessionKey::CART_NAME, $cartNames);
        }
        if (!session()->has($newCartId) || !is_array(session($newCartId))) {
            session()->put($newCartId, []);
        }

        return $newCartId;
    }
}
