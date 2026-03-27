<?php

namespace App\Http\Controllers\Vendor\POS;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\DigitalProductVariationRepositoryInterface;
use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StorageRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Enums\SessionKey;
use App\Enums\ViewPaths\Vendor\POSOrder;
use App\Events\DigitalProductDownloadEvent;
use App\Http\Controllers\BaseController;
use App\Services\CartService;
use App\Services\InventoryMutationService;
use App\Services\OrderDetailsService;
use App\Services\OrderService;
use App\Services\POSService;
use App\Services\PosCartStateService;
use App\Traits\CalculatorTrait;
use App\Traits\CustomerTrait;
use App\Utils\OrderManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class POSOrderController extends BaseController
{
    use CustomerTrait;
    use CalculatorTrait;


    /**
     * @param ProductRepositoryInterface $productRepo
     * @param CustomerRepositoryInterface $customerRepo
     * @param OrderRepositoryInterface $orderRepo
     * @param OrderDetailRepositoryInterface $orderDetailRepo
     * @param VendorRepositoryInterface $vendorRepo
     * @param DigitalProductVariationRepositoryInterface $digitalProductVariationRepo
     * @param StorageRepositoryInterface $storageRepo
     * @param POSService $POSService
     * @param CartService $cartService
     * @param OrderDetailsService $orderDetailsService
     * @param OrderService $orderService
     */
    public function __construct(
        private readonly ProductRepositoryInterface                 $productRepo,
        private readonly CustomerRepositoryInterface                $customerRepo,
        private readonly OrderRepositoryInterface                   $orderRepo,
        private readonly OrderDetailRepositoryInterface             $orderDetailRepo,
        private readonly VendorRepositoryInterface                  $vendorRepo,
        private readonly DigitalProductVariationRepositoryInterface $digitalProductVariationRepo,
        private readonly StorageRepositoryInterface                 $storageRepo,
        private readonly POSService                                 $POSService,
        private readonly CartService                                $cartService,
        private readonly PosCartStateService                        $posCartStateService,
        private readonly InventoryMutationService                   $inventoryMutationService,
        private readonly OrderDetailsService                        $orderDetailsService,
        private readonly OrderService                               $orderService,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View|Collection|LengthAwarePaginator|callable|RedirectResponse|null
     */
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getOrderDetails(id: $type);
    }

    /**
     * @param string $id
     * @return View|RedirectResponse
     */
    public function getOrderDetails(string $id): View|RedirectResponse
    {
        $vendorId = auth('seller')->id();
        $vendor = $this->vendorRepo->getFirstWhere(params: ['id' => $vendorId]);
        $getPOSStatus = getWebConfig('seller_pos');
        if ($vendor['pos_status'] == 0 || $getPOSStatus == 0) {
            Toastr::warning(translate('access_denied!!'));
            return redirect()->back();
        }
        $order = $this->orderRepo->getFirstWhere(params: ['id' => $id], relations: ['details', 'shipping', 'seller']);
        return view(POSOrder::ORDER_DETAILS[VIEW], compact('order'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function placeOrder(Request $request): JsonResponse
    {
        $cartId = session(SessionKey::POS_CART_ID);
        $cart = $this->getCartPayload($cartId);
        $cartLineItems = $this->getValidatedCartLineItems(is_array($cart) ? $cart : []);
        $amount = $this->getOrderAmount($cartId, $cartLineItems);
        $paymentType = $this->validatePaymentType($request->input('type', 'cash'));
        $paidAmount = $paymentType === 'cash' ? (float)($request['paid_amount'] ?? 0) : null;
        $condition = $this->POSService->checkConditions(amount: $amount, paidAmount: $paidAmount);
        if ($condition) {
            return response()->json();
        }
        $userId = $this->cartService->getUserId();
        if ($paymentType == 'wallet' && $userId != 0) {
            $customerBalance = $this->customerRepo->getFirstWhere(params: ['id' => $userId]) ?? 0;
            if ($customerBalance['wallet_balance'] >= $amount) {
                $this->createWalletTransaction(user_id: $userId, amount: floatval($amount), transaction_type: 'order_place', reference: 'order_place_in_pos');
            } else {
                Toastr::error(translate('need_Sufficient_Amount_Balance'));
                return response()->json();
            }
        }
        $checkProductTypeDigital = $this->cartService->checkProductTypeDigital(cartId: $cartId);
        if ($userId == 0 && $checkProductTypeDigital) {
            return response()->json(['checkProductTypeForWalkingCustomer' => true, 'message' => translate('To_order_digital_product') . ',' . translate('_kindly_fill_up_the_“Add_New_Customer”_form') . '.']);
        }

        $requestedBranchId = (int)($request['branch_id'] ?? 0);
        $resolvedBranchId = $requestedBranchId > 0 ? $requestedBranchId : null;
        $orderId = OrderManager::getNextOrderId();
        foreach ($cartLineItems as $item) {
            if (is_array($item)) {
                $product = $this->productRepo->getFirstWhere(params: ['id' => $item['id']], relations: ['clearanceSale' => function ($query) {
                    return $query->active();
                }]);
                if ($product) {
                    $lineUnitPrice = (float)($item['price'] ?? 0);

                    $digitalProductVariation = $this->digitalProductVariationRepo->getFirstWhere(params: ['product_id' => $item['id'], 'variant_key' => $item['variant']], relations: ['storage']);
                    if ($product['product_type'] == 'digital' && $digitalProductVariation) {
                        $lineUnitPrice = (float)$digitalProductVariation['price'];

                        if ($product['digital_product_type'] == 'ready_product') {
                            $getStoragePath = $this->storageRepo->getFirstWhere(params: [
                                'data_id' => $digitalProductVariation['id'],
                                "data_type" => "App\Models\DigitalProductVariation",
                            ]);
                            $product['digital_file_ready'] = $digitalProductVariation['file'];
                            $product['storage_path'] = $getStoragePath ? $getStoragePath['value'] : 'public';
                        }
                    } elseif ($product['digital_product_type'] == 'ready_product' && !empty($product['digital_file_ready'])) {
                        $product['storage_path'] = $product['digital_file_ready_storage_type'] ?? 'public';
                    }

                    $lineDiscount = max(0, (float)($item['discount'] ?? 0));
                    $lineInstallationCharge = (float)($item['installation_charge'] ?? 0);
                    $lineExchangeCharge = (float)($item['exchange_charge'] ?? 0);
                    $lineTaxRate = max(0, (float)($product['tax'] ?? 0));
                    $taxableUnitAmount = max(0, $lineUnitPrice - $lineDiscount);
                    if ((string)($product['tax_model'] ?? 'exclude') === 'include') {
                        $tax = $lineTaxRate > 0
                            ? ($taxableUnitAmount * $lineTaxRate) / (100 + $lineTaxRate)
                            : 0.0;
                    } else {
                        $tax = $this->getTaxAmount($taxableUnitAmount, $lineTaxRate);
                    }
                    $price = $lineUnitPrice;

                    $orderDetail = $this->orderDetailsService->getPOSOrderDetailsData(
                        orderId: $orderId, item: $item,
                        product: $product,
                        price: $price,
                        tax: $tax,
                        exchangeCharge: $lineExchangeCharge,
                        installationCharge: $lineInstallationCharge,
                    );
                    if ($product['product_type'] == 'physical') {
                        $stockMutation = $this->inventoryMutationService->decreaseForPosLine(
                            productId: (int)$item['id'],
                            qty: (int)$item['quantity'],
                            variant: $item['variant'] ?? null,
                            branchId: $resolvedBranchId,
                            sellerId: (int)auth('seller')->id(),
                            referenceId: (int)$orderId,
                            context: 'Vendor POS'
                        );

                        if (!($stockMutation['status'] ?? false)) {
                            Toastr::error($stockMutation['message'] ?? 'Stock not available for selected product/variation.');
                            return response()->json();
                        }

                        if (isset($stockMutation['branchId']) && (int)$stockMutation['branchId'] > 0) {
                            $resolvedBranchId = (int)$stockMutation['branchId'];
                        }
                    }
                    $this->orderDetailRepo->add(data: $orderDetail);
                }
            }
        }

        $order = $this->orderService->getPOSOrderData(
            orderId: $orderId,
            cart: $cart,
            amount: $amount,
            paidAmount: $paymentType == 'cash' ? $paidAmount : $amount,
            paymentType: $paymentType,
            addedBy: 'seller',
            userId: $userId,
            branchId: (float)($resolvedBranchId ?? 1),
        );
        $this->orderRepo->add(data: $order);
        if ($checkProductTypeDigital) {
            $order = $this->orderRepo->getFirstWhere(params: ['id' => $orderId], relations: ['details.productAllStatus']);
            $data = [
                'userName' => $order->customer->f_name,
                'userType' => 'customer',
                'templateName' => 'digital-product-download',
                'order' => $order,
                'subject' => translate('download_Digital_Product'),
                'title' => translate('Congratulations') . '!',
                'emailId' => $order->customer['email'],

            ];
            event(new DigitalProductDownloadEvent(email: $order->customer['email'], data: $data));
        }
        $this->putCartPayload($cartId, []);
        session(['last_order' => $orderId]);
        $this->cartService->getNewCartId();
        Toastr::success(translate('order_placed_successfully'));
        return response()->json();
    }

    private function validatePaymentType(mixed $paymentType): string
    {
        $normalizedPaymentType = trim((string)$paymentType);
        if (!in_array($normalizedPaymentType, ['cash', 'card', 'wallet'], true)) {
            throw ValidationException::withMessages([
                'type' => [translate('invalid_request')],
            ]);
        }

        return $normalizedPaymentType;
    }

    private function getValidatedCartLineItems(array $cart): array
    {
        $validatedItems = [];

        foreach ($cart as $item) {
            if (!is_array($item)) {
                continue;
            }

            $productId = (int)($item['id'] ?? 0);
            $quantity = max(0, (int)($item['quantity'] ?? 0));
            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $product = $this->productRepo->getFirstWhere(params: ['id' => $productId], relations: ['clearanceSale' => function ($query) {
                return $query->active();
            }]);
            if (!$product) {
                throw new \RuntimeException(translate('Product_not_found_in_cart'));
            }

            $variant = (string)($item['variant'] ?? '');
            $unitPrice = (float)($product['unit_price'] ?? 0);
            if ($variant !== '') {
                $variantPrice = (float)$this->cartService->getVariationPrice(
                    variation: json_decode($product['variation'] ?? '[]'),
                    variant: $variant
                );
                if ($variantPrice > 0) {
                    $unitPrice = $variantPrice;
                }
            }

            $digitalProductVariation = $this->digitalProductVariationRepo->getFirstWhere(
                params: ['product_id' => $product['id'], 'variant_key' => $variant]
            );
            if ($product['product_type'] == 'digital' && $digitalProductVariation) {
                $unitPrice = (float)($digitalProductVariation['price'] ?? $unitPrice);
            }

            $validatedItems[] = array_merge($item, [
                'price' => $unitPrice,
                'discount' => max(0, (float)getProductPriceByType(
                    product: $product,
                    type: 'discounted_amount',
                    result: 'value',
                    price: $unitPrice,
                    from: 'panel'
                )),
                'tax_model' => (string)($product['tax_model'] ?? 'exclude'),
            ]);
        }

        if (count($validatedItems) === 0) {
            throw new \RuntimeException(translate('cart_empty_warning'));
        }

        return $validatedItems;
    }

    private function getOrderAmount(string $cartId, array $cartLineItems): float
    {
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

        foreach ($cartLineItems as $lineItem) {
            $product = $this->productRepo->getFirstWhere(params: ['id' => $lineItem['id']], relations: ['clearanceSale' => function ($query) {
                return $query->active();
            }]);
            if (!$product) {
                continue;
            }

            $cartSubTotalCalculation = $this->cartService->getCartSubtotalCalculation(
                product: $product,
                cartItem: $lineItem,
                calculation: $subTotalCalculation
            );
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

        $totalCalculation = $this->cartService->getTotalCalculation(
            subTotalCalculation: $subTotalCalculation,
            cartName: $cartId
        );

        return (float)($totalCalculation['totalAmount'] ?? 0);
    }

    public function cancelOrder(Request $request): JsonResponse
    {
        $cartId = trim((string)$request->input('cart_id', ''));
        if ($cartId !== '') {
            $this->posCartStateService->deleteCart(
                cartId: $cartId,
                branchId: $this->resolveBranchIdFromCartId($cartId),
                actorType: 'seller',
                actorId: (int)auth('seller')->id()
            );
        }
        $totalHoldOrders = $this->POSService->getTotalHoldOrders(actorType: 'seller', actorId: (int)auth('seller')->id());
        $cartNames = $this->POSService->getCartNames(actorType: 'seller', actorId: (int)auth('seller')->id());
        $cartItems = $this->getHoldOrderCalculationData(cartNames: $cartNames);
        return response()->json([
            'message' => $cartId . ' ' . translate('order_is_cancel'),
            'status' => 'success',
            'view' => view(POSOrder::CANCEL_ORDER[VIEW], compact('cartItems', 'totalHoldOrders'))->render(),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllHoldOrdersView(Request $request): JsonResponse
    {
        $cartNames = $this->POSService->getCartNames(actorType: 'seller', actorId: (int)auth('seller')->id());
        $cartItems = $this->getHoldOrderCalculationData(cartNames: $cartNames);
        $totalHoldOrders = $this->POSService->getTotalHoldOrders(actorType: 'seller', actorId: (int)auth('seller')->id());
        if (!empty($request['customer'])) {
            $searchValue = strtolower($request['customer']);
            $filteredItems = collect($cartItems)->filter(function ($item) use ($searchValue) {
                return str_contains(strtolower($item['customerName']), $searchValue) !== false;
            });
            $cartItems = $filteredItems->all();
        }
        return response()->json([
            'flag' => 'inactive',
            'totalHoldOrders' => $totalHoldOrders,
            'view' => view(POSOrder::HOLD_ORDERS[VIEW], compact('totalHoldOrders', 'cartItems'))->render(),
        ]);
    }

    /**
     * @param array $cartNames
     * @return array
     */
    protected function getHoldOrderCalculationData(array $cartNames): array
    {
        $CustomerCartData = [];
        foreach ($cartNames as $cartId) {
            $CustomerData = $this->getCustomerCartData(cartName: $cartId);
            $CartItemData = $this->calculateCartItemsData(cartName: $cartId, customerCartData: $CustomerData);
            $CustomerCartData[$cartId] = array_merge($CustomerData[$cartId], $CartItemData);
        }
        return $CustomerCartData;
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
        $cartPayload = $this->getCartPayload($cartName);
        if (!empty($cartPayload)) {
            foreach ($cartPayload as $cartItem) {
                if (is_array($cartItem)) {
                    $product = $this->productRepo->getFirstWhere(params: ['id' => $cartItem['id']], relations: ['clearanceSale' => function ($query) {
                        return $query->active();
                    }]);
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
            subTotalCalculation: $subTotalCalculation, cartName: $cartName
        );
        return [
            'countItem' => $subTotalCalculation['countItem'],
            'total' => (float)($totalCalculation['totalAmount'] ?? 0),
            'subtotal' => $subTotalCalculation['subtotal'],
            'taxableBase' => (float)($totalCalculation['taxableBase'] ?? 0),
            'subTotalWithVat' => (float)($totalCalculation['subTotalWithVat'] ?? 0),
            'taxCalculate' => (float)($totalCalculation['taxTotal'] ?? 0),
            'totalTaxShow' => (float)($totalCalculation['taxTotal'] ?? 0),
            'totalTax' => (float)($totalCalculation['taxTotal'] ?? 0),
            'discountOnProduct' => $subTotalCalculation['discountOnProduct'],
            'productSubtotal' => $subTotalCalculation['productSubtotal'],
            'cartItemValue' => $cartItemValue,
            'couponDiscount' => $totalCalculation['couponDiscount'],
            'extraDiscount' => $totalCalculation['extraDiscount'],
            'customerOnHold' => $subTotalCalculation['customerOnHold'] ?? false,
            'legacyTotalBeforeVat' => $totalCalculation['total'],
        ];
    }

    private function resolveBranchIdFromCartId(?string $cartId): int
    {
        $cartId = trim((string)$cartId);
        if (preg_match('/-b(\d+)$/', $cartId, $matches)) {
            return max(1, (int)($matches[1] ?? 1));
        }

        return 1;
    }

    private function getCartPayload(?string $cartId): array
    {
        $resolvedCartId = trim((string)$cartId);
        if ($resolvedCartId === '') {
            return [];
        }

        return $this->posCartStateService->getPayload(
            cartId: $resolvedCartId,
            branchId: $this->resolveBranchIdFromCartId($resolvedCartId),
            actorType: 'seller',
            actorId: (int)auth('seller')->id()
        );
    }

    private function putCartPayload(?string $cartId, array $payload): void
    {
        $resolvedCartId = trim((string)$cartId);
        if ($resolvedCartId === '') {
            return;
        }

        $this->posCartStateService->putPayload(
            cartId: $resolvedCartId,
            branchId: $this->resolveBranchIdFromCartId($resolvedCartId),
            payload: $payload,
            actorType: 'seller',
            actorId: (int)auth('seller')->id()
        );
    }

}
