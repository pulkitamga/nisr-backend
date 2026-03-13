<?php

namespace App\Http\Controllers\Admin\POS;

use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Enums\ViewPaths\Admin\Cart;
use App\Enums\ViewPaths\Admin\POS;
use App\Http\Controllers\BaseController;
use App\Services\CartService;
use App\Services\ProductExtraChargeResolverService;
use App\Services\PosCartStateService;
use App\Services\POSService;
use App\Traits\CalculatorTrait;
use App\Utils\OrderManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
        private readonly PosCartStateService         $posCartStateService,
        private readonly POSService                  $POSService,
        private readonly ProductExtraChargeResolverService $productExtraChargeResolverService,
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
        $context = $this->validateWriteContext($request, true);
        $activeBranchId = $context['branch_id'];
        $cartId = $context['cart_id'];
        $requestedQuantity = (int)$request['quantity'];

        if ($requestedQuantity > 0) {
            $cartData = $this->posCartStateService->getPayload(
                cartId: $cartId,
                branchId: $activeBranchId,
                actorType: 'admin',
                actorId: (int)auth('admin')->id()
            );
            $requestedLineKey = trim((string)($request['line_key'] ?? ''));
            $requestedVariant = trim((string)($request['variant'] ?? ''));

            foreach ((array)$cartData as $cartItem) {
                if (!is_array($cartItem)) {
                    continue;
                }

                $sameProduct = (int)($cartItem['id'] ?? 0) === (int)$request['key'];
                if (!$sameProduct) {
                    continue;
                }

                $sameLine = $requestedLineKey !== ''
                    ? trim((string)($cartItem['line_key'] ?? '')) === $requestedLineKey
                    : trim((string)($cartItem['variant'] ?? '')) === $requestedVariant;
                if (!$sameLine) {
                    continue;
                }

                $lineExchangeQty = max(0, (int)($cartItem['exchange_quantity'] ?? 0));
                if ($lineExchangeQty > $requestedQuantity) {
                    $cartItems = $this->getCartData(cartName: $cartId);
                    return response()->json([
                        'exchangeQtyInvalid' => 1,
                        'message' => translate('Exchange qty cannot exceed product quantity.'),
                        'view' => view(Cart::CART[VIEW], compact('cartId', 'cartItems'))->render(),
                    ]);
                }
                break;
            }
        }

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
        $context = $this->validateWriteContext($request, true);
        $activeBranchId = $context['branch_id'];
        $cartId = $context['cart_id'];
        $idempotencyKey = trim((string)$request->input('idempotency_key', ''));
        if ($idempotencyKey === '') {
            throw ValidationException::withMessages([
                'idempotency_key' => [translate('invalid_request')],
            ]);
        }
        $cachedResponse = $this->getIdempotentResponse('admin_add_to_cart', $idempotencyKey);
        if (!is_null($cachedResponse)) {
            return response()->json($cachedResponse);
        }

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
        $cartData = $this->posCartStateService->getPayload(
            cartId: $cartId,
            branchId: $activeBranchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );

        $requestedLineKey = trim((string)($request['line_key'] ?? ''));
        $lineKey = $requestedLineKey !== '' ? $requestedLineKey : (string)Str::uuid();
        $quantityForUpdate = max(1, (int)($request['quantity_in_cart'] ?? $request['quantity'] ?? 1));

        $resolvedExtraCharges = $this->productExtraChargeResolverService->resolveForProduct($product);
        $resolvedInstallationCharge = max(0, (float)($resolvedExtraCharges['installation'] ?? 0));
        $resolvedExchangeCharge = max(0, (float)($resolvedExtraCharges['exchange'] ?? 0));

        $isInstallationRequested = max(0, (float)$request->input('installation_charge', 0)) > 0;
        $installationTotel = ($isInstallationRequested && $resolvedInstallationCharge > 0)
            ? $resolvedInstallationCharge
            : 0.0;

        $exchangeQuantity = max(0, (int)$request->input('exchange_quantity', 0));
        $exchangeTotel = 0.0;
        $isReplacementDiscountEnabled = (int)$request->input('replacement_discount_enabled', 0) === 1
            && $resolvedExchangeCharge > 0;

        if ($isReplacementDiscountEnabled) {
            if ($exchangeQuantity < 1) {
                throw ValidationException::withMessages([
                    'exchange_quantity' => [translate('Exchange qty must be at least 1 when Replacement Discount is enabled.')],
                ]);
            }
            if ($exchangeQuantity > $quantityForUpdate) {
                throw ValidationException::withMessages([
                    'exchange_quantity' => [translate('Exchange qty cannot exceed product quantity.')],
                ]);
            }
            $exchangeTotel = $exchangeQuantity * $resolvedExchangeCharge;
        } else {
            $exchangeQuantity = 0;
            $exchangeTotel = 0.0;
        }

        $matchedCartIndex = null;
        if ($cartId && is_array($cartData) && count($cartData) > 0) {
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
                return $this->cacheIdempotentResponse('admin_add_to_cart', $idempotencyKey, [
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
                    'exchange_quantity' => $exchangeQuantity,
                    'installation_charge' => $installationTotel,
                    'branch_id' => $activeBranchId,
                    'line_key' => $lineKey,
                ],
                cartId: $cartId,
            );
            unset($cartData[$matchedCartIndex]);
            $cartData[] = $cartItem;
            $this->posCartStateService->putPayload(
                cartId: $cartId,
                branchId: $activeBranchId,
                payload: $cartData,
                actorType: 'admin',
                actorId: (int)auth('admin')->id()
            );
            $getCurrentCustomerData = $this->getCustomerDataByCartIdForPOS($cartId);
            $summaryData = array_merge(
                $this->POSService->getSummaryData(
                    branchId: $activeBranchId,
                    activeCartId: $cartId,
                    actorType: 'admin',
                    actorId: (int)auth('admin')->id()
                ),
                $getCurrentCustomerData
            );
            $cartItems = $this->getCartData(cartName: $cartId);
            Log::info('POS_CART_ADD_UPDATE_LINE', [
                'cart_id' => $cartId,
                'request_product_id' => (int)$request['id'],
                'request_variant' => (string)$variant,
                'request_line_key' => $requestedLineKey,
                'matched_line_key' => $lineKey,
                'qty' => $quantityForUpdate,
            ]);
            return $this->cacheIdempotentResponse('admin_add_to_cart', $idempotencyKey, [
                'data' => 1,
                'inCartData' => 1,
                'requestQuantity' => $quantityForUpdate,
                'view' => view(Cart::SUMMARY[VIEW], compact('summaryData', 'cartItems', 'installationTotel', 'exchangeTotel', 'cartId'))->render()
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
            return $this->cacheIdempotentResponse('admin_add_to_cart', $idempotencyKey, [
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
                'exchange_quantity' => $exchangeQuantity,
                'installation_charge' => $installationTotel,
                'branch_id' => $activeBranchId,
                'line_key' => $lineKey,

            ],
            cartId: $cartId,
        );
        $cartSnapshot = collect((array)$this->posCartStateService->getPayload(
            cartId: $cartId,
            branchId: $activeBranchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        ))
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
            'request_product_id' => (int)$request['id'],
            'request_variant' => (string)$variant,
            'generated_line_key' => $lineKey,
            'qty' => (int)$request['quantity'],
            'cart_snapshot' => $cartSnapshot,
        ]);
        $cartItems = $this->getCartData(cartName: $cartId);

        return $this->cacheIdempotentResponse('admin_add_to_cart', $idempotencyKey, [
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
        $context = $this->validateWriteContext($request, true);
        $activeBranchId = $context['branch_id'];
        $cartId = $context['cart_id'];
        $cart = $this->posCartStateService->getPayload(
            cartId: $cartId,
            branchId: $activeBranchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );
        $cartKeeper = [];
        $lineKey = trim((string)($request['line_key'] ?? ''));
        if (count($cart) > 0) {
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
        $this->posCartStateService->putPayload(
            cartId: $cartId,
            branchId: $activeBranchId,
            payload: $cartKeeper,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );
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
        try {
            $context = $this->validateWriteContext(request(), true);
        } catch (ValidationException) {
            Toastr::error(translate('invalid_request'));
            $fallbackBranchId = max(1, (int)request()->input('branch_id', 1));
            return redirect()->route('admin.pos.index', ['branch_id' => $fallbackBranchId]);
        }
        $branchId = $context['branch_id'];
        $cartId = $context['cart_id'];

        $this->posCartStateService->deleteCart(
            cartId: $cartId,
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );

        $newCartId = $this->cartService->generateWalkingCustomerCartId($branchId);
        $this->posCartStateService->ensureCart(
            cartId: $newCartId,
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );

        return redirect()->route('admin.pos.index', ['branch_id' => $branchId, 'cart_id' => $newCartId]);
    }

    /**
     * @return JsonResponse
     */
    public function getCartIds(): JsonResponse
    {
        $branchId = (int)request()->input('branch_id', 0);
        if ($branchId <= 0) {
            $branchId = 1;
        }
        $cartId = trim((string)request()->input('cart_id', ''));
        if ($cartId !== '') {
            try {
                $this->validateWriteContext(new Request([
                    'branch_id' => $branchId,
                    'cart_id' => $cartId,
                ]), true);
            } catch (ValidationException) {
                $cartId = '';
            }
        }
        if ($cartId === '') {
            $cartId = $this->cartService->generateWalkingCustomerCartId($branchId);
            $this->posCartStateService->ensureCart(
                cartId: $cartId,
                branchId: $branchId,
                actorType: 'admin',
                actorId: (int)auth('admin')->id()
            );
        } else {
            $this->posCartStateService->assertCart(
                cartId: $cartId,
                branchId: $branchId,
                actorType: 'admin',
                actorId: (int)auth('admin')->id()
            );
        }

        $this->cartService->getCartKeeper(
            cartId: $cartId,
            branchId: $branchId
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
        $cartItems = $this->getCartData(cartName: $cartId);
        return response()->json([
            'view' => view(Cart::SUMMARY[VIEW], compact('summaryData', 'cartItems', 'cartId'))->render(),
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function emptyCart(): JsonResponse
    {
        $context = $this->validateWriteContext(request(), true);
        $cartId = $context['cart_id'];
        $branchId = $context['branch_id'];

        $this->posCartStateService->putPayload(
            cartId: $cartId,
            branchId: $branchId,
            payload: [],
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
        $cartItems = $this->getCartData(cartName: $cartId);
        return response()->json([
            'view' => view(Cart::SUMMARY[VIEW], compact('summaryData', 'cartItems', 'cartId'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function changeCart(Request $request): RedirectResponse
    {
        $branchId = (int)$request->input('branch_id', 1);
        if ($branchId <= 0) {
            $branchId = 1;
        }
        $cartId = trim((string)$request->input('cart_id', ''));
        if ($cartId === '') {
            Toastr::error(translate('invalid_request'));
            return redirect()->route('admin.pos.index', ['branch_id' => $branchId]);
        }
        if (!$this->cartService->cartBelongsToBranch($cartId, $branchId)) {
            Toastr::error(translate('invalid_request'));
            return redirect()->route('admin.pos.index', ['branch_id' => $branchId]);
        }

        try {
            $this->posCartStateService->assertCart(
                cartId: $cartId,
                branchId: $branchId,
                actorType: 'admin',
                actorId: (int)auth('admin')->id()
            );
        } catch (ValidationException) {
            Toastr::error(translate('invalid_request'));
            return redirect()->route('admin.pos.index', ['branch_id' => $branchId]);
        }
        $this->cartService->customerOnHoldStatus(status: false, cartId: $cartId, branchId: $branchId);
        Toastr::success($request['cart_id'] . ' ' . translate('order_is_now_resumed'));

        return redirect()->route('admin.pos.index', ['branch_id' => $branchId, 'cart_id' => $cartId]);
    }

    /**
     * @return RedirectResponse
     */
    public function addNewCartId(): RedirectResponse
    {
        try {
            $context = $this->validateWriteContext(request(), true);
        } catch (ValidationException) {
            Toastr::error(translate('invalid_request'));
            $fallbackBranchId = max(1, (int)request()->input('branch_id', 1));
            return redirect()->route('admin.pos.index', ['branch_id' => $fallbackBranchId]);
        }
        $branchId = $context['branch_id'];
        $currentCartId = $context['cart_id'];

        $currentPayload = $this->posCartStateService->getPayload(
            cartId: $currentCartId,
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );
        if (count(array_filter($currentPayload, fn($item) => is_array($item) && isset($item['id']))) > 0) {
            $this->cartService->customerOnHoldStatus(status: true, cartId: $currentCartId, branchId: $branchId);
            Toastr::success(translate('this_order_is_now_on_hold'));
        }
        $newCartId = $this->cartService->generateWalkingCustomerCartId($branchId);
        $this->posCartStateService->ensureCart(
            cartId: $newCartId,
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );

        return redirect()->route('admin.pos.index', ['branch_id' => $branchId, 'cart_id' => $newCartId]);
    }

    /**
     * @return array
     */
    protected function getCustomerDataFromSessionForPOS(): array
    {
        return $this->getCustomerDataByCartIdForPOS((string)request()->input('cart_id', ''));
    }

    protected function getCustomerDataByCartIdForPOS(string $cartId): array
    {
        $cartId = trim($cartId);
        if ($cartId === '' || Str::contains($cartId, 'walking-customer')) {
            $currentCustomer = translate('walking_customer');
            $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => '0']);
        } else {
            $segments = explode('-', $cartId);
            $userId = (int)($segments[2] ?? 0);
            $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => $userId]);
            if ($currentCustomerData) {
                $currentCustomer = $currentCustomerData['f_name'] . ' ' . $currentCustomerData['l_name'] . ' (' . $currentCustomerData['phone'] . ')';
            } else {
                $currentCustomer = translate('walking_customer');
                $currentCustomerData = $this->customerRepo->getFirstWhere(params: ['id' => '0']);
            }
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
        $branchId = (int)request()->input('branch_id', 0);
        if ($branchId <= 0 && preg_match('/-b(\d+)$/', $cartName, $matches)) {
            $branchId = (int)($matches[1] ?? 0);
        }
        if ($branchId <= 0) {
            $branchId = 1;
        }

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
                    if (!$product) {
                        continue;
                    }
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
                        $subTotalCalculation['customerOnHold'] = $cartItem['customerOnHold'];
                        $installationTotal += (float)($cartItem['installation_charge'] ?? 0) * (int)($cartItem['quantity'] ?? 0);
                        $exchangeTotal += (float)($cartItem['exchange_charge'] ?? 0);
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
            'couponDiscount' => abs((float)($cartPayload['coupon_discount'] ?? 0)),
            'extraDiscount' => $summary['extraDiscount'],
            'customerOnHold' => $subTotalCalculation['customerOnHold'] ?? false,
            'totalInstallationPrice' => $installationTotal,
            'totalExchangePrice' => $exchangeTotal,
            'legacyTotalBeforeVat' => $totalCalculation['total'],
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

        $availableCarts = $this->posCartStateService->listCartIdsByBranch(
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id(),
            nonEmptyOnly: false
        );
        if (!empty($availableCarts)) {
            return (string)$availableCarts[0];
        }

        $newCartId = $this->cartService->generateWalkingCustomerCartId($branchId);
        $this->posCartStateService->ensureCart(
            cartId: $newCartId,
            branchId: $branchId,
            actorType: 'admin',
            actorId: (int)auth('admin')->id()
        );

        return $newCartId;
    }

    private function validateWriteContext(Request $request, bool $mustExistCart): array
    {
        $branchId = (int)$request->input('branch_id', 0);
        $cartId = trim((string)$request->input('cart_id', ''));
        if ($branchId <= 0 || $cartId === '' || !$this->cartService->cartBelongsToBranch($cartId, $branchId)) {
            throw ValidationException::withMessages([
                'cart_id' => [translate('invalid_request')],
            ]);
        }

        if ($mustExistCart) {
            $this->posCartStateService->assertCart(
                cartId: $cartId,
                branchId: $branchId,
                actorType: 'admin',
                actorId: (int)auth('admin')->id()
            );
        } else {
            $this->posCartStateService->ensureCart(
                cartId: $cartId,
                branchId: $branchId,
                actorType: 'admin',
                actorId: (int)auth('admin')->id()
            );
        }

        return [
            'branch_id' => $branchId,
            'cart_id' => $cartId,
        ];
    }

    private function getIdempotentResponse(string $action, string $idempotencyKey): ?array
    {
        $cacheKey = $this->buildIdempotencyCacheKey($action, $idempotencyKey);
        $cached = Cache::get($cacheKey);
        return is_array($cached) ? $cached : null;
    }

    private function cacheIdempotentResponse(string $action, string $idempotencyKey, array $response): JsonResponse
    {
        $cacheKey = $this->buildIdempotencyCacheKey($action, $idempotencyKey);
        Cache::put($cacheKey, $response, now()->addMinutes(5));
        return response()->json($response);
    }

    private function buildIdempotencyCacheKey(string $action, string $idempotencyKey): string
    {
        return 'pos:idem:admin:'
            . (int)auth('admin')->id()
            . ':'
            . sha1(trim($action) . '|' . trim($idempotencyKey));
    }
}
