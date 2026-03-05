<?php

namespace App\Services;

use App\Domain\Stock\Support\VariantMatcher;
use App\Enums\SessionKey;
use App\Models\Branch;
use App\Models\ManageBranchProductStock;
use App\Models\PosCartState;
use App\Traits\CalculatorTrait;
use App\Utils\OrderManager;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CartService
{
    use CalculatorTrait;

    private ?VariantMatcher $variantMatcher = null;

    public function __construct(
        private readonly PosCartStateService $posCartStateService
    ) {}

    /**
     * @param object $request
     * @param object $product
     * @param string|null $colorName
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getVariantData(object $request, object $product, string $colorName = null): array
    {
        $quantity = 0;
        $price = 0;
        $unitPrice = 0;
        $discount = 0;
        $tax = 0;
        $branchId = (int)($this->getRequestValue($request, 'branch_id') ?? 0);
        $variation = $this->makeVariation(
            request: $request,
            colorName: $colorName,
            choiceOptions: json_decode($product['choice_options'])
        );

        if ($variation != null) {
            $count = count(json_decode($product->variation));
            for ($i = 0; $i < $count; $i++) {
                $variationType = json_decode($product->variation)[$i]->type ?? null;
                if ($this->variantsMatch($variationType, $variation)) {
                    $variationPrice = json_decode($product->variation)[$i]->price;
                    $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $variationPrice, from: 'panel');
                    $tax = $product->tax_model == 'exclude' ? $this->getTaxAmount(price: $variationPrice, tax: $product['tax']) : 0;
                    $price = $variationPrice - $discount + $tax;
                    $unitPrice = $variationPrice;
                    $quantity = json_decode($product->variation)[$i]->qty;
                }
            }
        } else {
            $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $product['unit_price'], from: 'panel');
            $tax = $product->tax_model == 'exclude' ? $this->getTaxAmount(price: $product->unit_price, tax: $product['tax']) : 0;
            $price = $product['unit_price'] - $discount + $tax;
            $unitPrice = $product['unit_price'];
            $quantity = $product['current_stock'];
        }

        $requestQuantity = (int)$request['quantity'];

        $inCartStatus = 0;
        $activeCartId = trim((string)($this->getRequestValue($request, 'cart_id') ?? ''));
        $cartData = [];
        if ($activeCartId !== '' && $this->cartBelongsToBranch($activeCartId, $branchId)) {
            try {
                $cartData = $this->getCartPayloadById($activeCartId, $branchId);
            } catch (Throwable) {
                $cartData = [];
            }
        }
        $inCartData = null;
        $requestedLineKey = trim((string)($this->getRequestValue($request, 'line_key') ?? ''));

        if ($product['product_type'] == 'digital' && $request->has('variant_key')) {
            $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $product['unit_price'], from: 'panel');
            $tax = $product['tax_model'] == 'exclude' ? $this->getTaxAmount(price: $product['unit_price'], tax: $product['tax']) : 0;
            $quantity = $product['current_stock'];
            foreach ($product['digitalVariation'] as $variant) {
                if ($variant['variant_key'] == $request->variant_key) {
                    $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $variant['price'], from: 'panel');
                    $tax = $product['tax_model'] == 'exclude' ? $this->getTaxAmount(price: $variant['price'], tax: $product['tax']) : 0;
                    $price = $variant['price'] - $discount + $tax;
                    $unitPrice = $variant['price'];
                    $variation = $variant['variant_key'];
                }
            }
        }

        if ($product['product_type'] === 'physical') {
            $quantity = max(0, $this->checkCurrentStock(
                variant: $variation !== '' ? $variation : null,
                variation: (array)json_decode($product['variation'] ?? '[]'),
                productQty: (int)$product['current_stock'],
                quantity: 0,
                branchId: $branchId > 0 ? $branchId : null,
                productId: (int)$product['id'],
                productType: (string)$product['product_type'],
                productBranchId: (int)($product['branch_id'] ?? 0),
            ));
        }

        $matchedCartLine = null;
        foreach ($cartData as $cart) {
            if (!is_array($cart) || (int)($cart['id'] ?? 0) !== (int)$product['id']) {
                continue;
            }

            if (!$this->variantsMatch($cart['variant'] ?? null, $variation)) {
                continue;
            }

            $cartLineKey = trim((string)($cart['line_key'] ?? ''));
            if ($requestedLineKey !== '' && $cartLineKey !== $requestedLineKey) {
                continue;
            }

            $matchedCartLine = $cart;
            if ($requestedLineKey !== '' || $cartLineKey !== '') {
                break;
            }
        }

        if (is_array($matchedCartLine)) {
            $inCartStatus = 1;
            $cartDiscount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $matchedCartLine['price'], from: 'panel');
            $price = ($matchedCartLine['price'] - $cartDiscount + $tax);
            $inCartData = [
                'price' => usdToDefaultCurrency(amount: $price * $matchedCartLine['quantity']),
                'discount' => usdToDefaultCurrency($cartDiscount),
                'tax' => $product->tax_model == 'exclude' ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $tax * $matchedCartLine['quantity']), currencyCode: getCurrencyCode()) : 'incl.',
                'quantity' => (int)$matchedCartLine['quantity'],
                'variant' => (string)($matchedCartLine['variant'] ?? ''),
                'id' => (int)$matchedCartLine['id'],
                'line_key' => (string)($matchedCartLine['line_key'] ?? ''),
            ];
            $requestQuantity = (int)($request['quantity_in_cart'] ?? $matchedCartLine['quantity']);
        }
        $discountType = getProductPriceByType(product: $product, type: 'discount_type', result: 'string');

        return [
            'price' => setCurrencySymbol(amount: usdToDefaultCurrency(amount: $price * $requestQuantity)),
            'discount' => usdToDefaultCurrency($discount),
            'discount_amount' => $discount,
            'discount_type' => $discountType,
            'discount_text' => $discountType == 'flat' ? translate('save') .' '. usdToDefaultCurrency($discount) : getProductPriceByType(product: $product, type: 'discount', result: 'value').'% '. translate('off'),
            'tax' => $product->tax_model == 'exclude' ? setCurrencySymbol(amount: usdToDefaultCurrency(amount: $tax * $requestQuantity), currencyCode: getCurrencyCode()) : 'incl.',
            'quantity' => $product['product_type'] == 'physical' ? $quantity : 100,
            'inCartStatus' => $inCartStatus,
            'inCartData' => $inCartData,
            'requestQuantity' => $requestQuantity,
            'total_unit_price' => setCurrencySymbol(amount: usdToDefaultCurrency(amount: $unitPrice)),
            'discounted_unit_price' => setCurrencySymbol(amount: usdToDefaultCurrency(amount: $unitPrice - $discount)),
        ];
    }

    public function makeVariation(object $request, string|null $colorName, array $choiceOptions): string
    {
        $variation = '';
        if ($colorName) {
            $variation = $colorName;
        }
        foreach ($choiceOptions as $key => $choice) {
            $choiceValue = $this->resolveChoiceValue($request, $choice);
            if ($choiceValue === null || $choiceValue === '') {
                continue;
            }

            $normalizedChoiceValue = str_replace(' ', '', (string)$choiceValue);
            if ($variation != null && $variation !== '') {
                $variation .= '-' . $normalizedChoiceValue;
            } else {
                $variation .= $normalizedChoiceValue;
            }
        }
        return $variation;
    }

    public function getUserId(?string $cartId = null): int
    {
        $userId = 0;
        $resolvedCartId = trim((string)($cartId ?? session(SessionKey::CURRENT_USER) ?? ''));
        if (Str::contains($resolvedCartId, 'saved-customer')) {
            $segments = explode('-', $resolvedCartId);
            $userId = (int)($segments[2] ?? 0);
        }
        return $userId;
    }

    public function getUserType(?string $cartId = null): string
    {
        $userType = 'walking-customer';
        $resolvedCartId = trim((string)($cartId ?? session(SessionKey::CURRENT_USER) ?? ''));
        if (Str::contains($resolvedCartId, 'saved-customer')) {
            $userType = 'saved-customer';
        }
        return $userType;
    }

    public function getNewCartSession(string|int $cartId): void
    {
        $activeCartId = trim((string)$cartId);
        if ($activeCartId === '') {
            throw ValidationException::withMessages([
                'cart_id' => [translate('invalid_request')],
            ]);
        }

        $this->posCartStateService->ensureCart(
            cartId: $activeCartId,
            branchId: $this->resolveBranchIdFromCartId($activeCartId, null)
        );
    }

    public function getCartKeeper(?string $cartId = null, ?int $branchId = null): void
    {
        $resolvedCartId = trim((string)($cartId ?? session(SessionKey::CURRENT_USER) ?? ''));
        if ($resolvedCartId === '') {
            return;
        }

        $resolvedBranchId = $this->resolveBranchIdFromCartId($resolvedCartId, $branchId);
        $cart = $this->getCartPayloadById($resolvedCartId, $resolvedBranchId);
        $cartKeeper = [];
        foreach ($cart as $cartItem) {
            if (is_array($cartItem)) {
                $cartKeeper[] = $cartItem;
            }
        }

        if (!isset($cartKeeper['add_to_cart_time'])) {
            $cartKeeper['add_to_cart_time'] = Carbon::now();
        }

        $this->putCartPayloadById($resolvedCartId, $resolvedBranchId, $cartKeeper);
    }

    public function getVariationPrice(array $variation, string $variant): float
    {
        $count = count($variation);
        $price = 0;
        for ($i = 0; $i < $count; $i++) {
            $variationType = $variation[$i]->type ?? null;
            if ($this->variantsMatch($variationType, $variant)) {
                $price = $variation[$i]->price;
            }
        }
        return $price;
    }

    public function getVariationQuantity(array $variation, string $variant): int
    {
        $count = count($variation);
        $productQuantity = 0;
        for ($i = 0; $i < $count; $i++) {
            $variationType = $variation[$i]->type ?? null;
            if ($this->variantsMatch($variationType, $variant)) {
                $productQuantity = $variation[$i]->qty;
            }
        }
        return $productQuantity;
    }

    public function getCurrentQuantity($variation, $variant, $quantity): int
    {
        $productQuantity = $this->getVariationQuantity($variation, $variant);
        return $productQuantity - $quantity;
    }

    public function addCartDataOnSession(
        object $product,
        int $quantity,
        float $price,
        float $discount,
        string $variant,
        array $variations,
        array $extra = [],
        ?string $cartId = null
    ): array
    {
        $resolvedCartId = trim((string)($cartId ?? ''));
        if ($resolvedCartId === '') {
            throw ValidationException::withMessages([
                'cart_id' => [translate('invalid_request')],
            ]);
        }

        $resolvedBranchId = $this->resolveBranchIdFromCartId(
            $resolvedCartId,
            (int)($extra['branch_id'] ?? 0)
        );

        $lineKey = $extra['line_key'] ?? $this->makeCartLineKey(
            productId: (int)$product['id'],
            variant: $variant,
            branchId: (int)($extra['branch_id'] ?? 0),
            installationCharge: (float)($extra['installation_charge'] ?? 0),
            exchangeCharge: (float)($extra['exchange_charge'] ?? 0),
        );
        $sessionData = [
            'id' => $product['id'],
            'line_key' => $lineKey,
            'customerId' => $this->getUserId($resolvedCartId),
            'customerOnHold' => false,
            'quantity' => $quantity,
            'price' => $price,
            'name' => $product['name'],
            'productType' => $product['product_type'],
            'image' => $product->thumbnail_full_url,
            'discount' => $discount,
            'tax_model' => $product['tax_model'],
            'variant' => $variant,
            'variations' => $variations,
        ];
        if (!empty($extra)) {
            $sessionData = array_merge($sessionData, $extra);
        }

        $keeper = [];
        foreach ($this->getCartPayloadById($resolvedCartId, $resolvedBranchId) as $item) {
            if (is_array($item)) {
                $keeper[] = $item;
            }
        }
        $keeper[] = $sessionData;
        if (!isset($keeper['add_to_cart_time'])) {
            $keeper['add_to_cart_time'] = Carbon::now();
        }
        $this->putCartPayloadById($resolvedCartId, $resolvedBranchId, $keeper);

        return $sessionData;
    }

    public function getQuantityAndUpdateTime(
        object $request,
        object $product,
        ?int $branchId = null,
        ?int $sellerId = null,
        ?string $cartId = null
    ): int
    {
        $quantity = 0;
        $resolvedCartId = trim((string)($cartId ?? ''));
        if ($resolvedCartId === '') {
            throw ValidationException::withMessages([
                'cart_id' => [translate('invalid_request')],
            ]);
        }

        $resolvedBranchIdForCart = $this->resolveBranchIdFromCartId($resolvedCartId, $branchId);
        $cart = $this->getCartPayloadById($resolvedCartId, $resolvedBranchIdForCart);
        $keeper = [];
        $requestedVariant = trim((string)($request['variant'] ?? ''));
        $requestedQuantity = (int)$request['quantity'];
        $requestedLineKey = trim((string)($request['line_key'] ?? ''));

        foreach ($cart as $item) {
            if (is_array($item)) {
                $sameProduct = (int)($item['id'] ?? 0) === (int)$request['key'];
                $itemVariant = trim((string)($item['variant'] ?? ''));
                $lineKeyCheck = $requestedLineKey !== ''
                    ? trim((string)($item['line_key'] ?? '')) === $requestedLineKey
                    : false;
                $variantCheck = $requestedVariant !== ''
                    ? $this->variantsMatch($itemVariant, $requestedVariant)
                    : $this->normalizeVariantToken($itemVariant) === null;

                $shouldUpdateLine = $requestedLineKey !== ''
                    ? $lineKeyCheck
                    : ($sameProduct && $variantCheck);

                if ($shouldUpdateLine) {
                    $resolvedBranchId = (int)($branchId ?? 0);
                    if ($resolvedBranchId <= 0 && isset($item['branch_id'])) {
                        $resolvedBranchId = (int)$item['branch_id'];
                    }

                    $quantity = $this->checkCurrentStock(
                        variant: $itemVariant !== '' ? $itemVariant : null,
                        variation: json_decode($product['variation']),
                        productQty: (int)$product['current_stock'],
                        quantity: $requestedQuantity,
                        branchId: $resolvedBranchId > 0 ? $resolvedBranchId : null,
                        productId: (int)$product['id'],
                        productType: (string)$product['product_type'],
                        sellerId: $sellerId,
                        productBranchId: (int)($product['branch_id'] ?? 0),
                    );

                    if ($product['product_type'] == 'physical' && $quantity < 0) {
                        return $quantity;
                    }
                    $item['quantity'] = $requestedQuantity;
                }
                $keeper[] = $item;
            }
        }
        $keeper += ['add_to_cart_time' => Carbon::now()];
        $this->putCartPayloadById($resolvedCartId, $resolvedBranchIdForCart, $keeper);
        return $quantity;
    }

    public function makeCartLineKey(
        int $productId,
        ?string $variant = null,
        ?int $branchId = null,
        float $installationCharge = 0,
        float $exchangeCharge = 0,
    ): string {
        $variantToken = $this->normalizeVariantToken(trim((string)($variant ?? ''))) ?? '__default__';
        $raw = implode('|', [
            $productId,
            $variantToken,
            (int)($branchId ?? 0),
            number_format($installationCharge, 4, '.', ''),
            number_format($exchangeCharge, 4, '.', ''),
        ]);

        return sha1($raw);
    }

    public function getNewCartId(): void
    {
        $cartId = $this->generateWalkingCustomerCartId($this->getActivePosBranchId());
        $this->posCartStateService->ensureCart(
            cartId: $cartId,
            branchId: $this->resolveBranchIdFromCartId($cartId, null)
        );
        // Backward-compatible read fallback for modules not migrated yet.
        session()->put(SessionKey::CURRENT_USER, $cartId);
    }

    public function generateWalkingCustomerCartId(?int $branchId = null): string
    {
        $branchId = $this->normalizeBranchId($branchId);
        $suffix = '-b' . $branchId;
        do {
            $cartId = 'walking-customer-' . Str::lower(Str::random(16)) . $suffix;
        } while (
            PosCartState::query()->where('cart_id', $cartId)->exists()
        );

        return $cartId;
    }

    public function makeSavedCustomerCartId(int $customerId, ?int $branchId = null): string
    {
        $branchId = $this->normalizeBranchId($branchId);
        return 'saved-customer-' . max(0, $customerId) . '-b' . $branchId;
    }

    public function cartBelongsToBranch(?string $cartId, ?int $branchId = null): bool
    {
        $cartId = trim((string)$cartId);
        if ($cartId === '') {
            return false;
        }

        $branchId = $this->normalizeBranchId($branchId);
        $suffix = '-b' . $branchId;
        if (str_ends_with($cartId, $suffix)) {
            return true;
        }

        return $branchId === 1 && !preg_match('/-b\d+$/', $cartId);
    }

    public function getActivePosBranchId(): int
    {
        return $this->normalizeBranchId((int)(session(SessionKey::POS_BRANCH_ID) ?? 0));
    }

    private function normalizeBranchId(?int $branchId): int
    {
        $resolved = (int)($branchId ?? 0);
        if ($resolved <= 0) {
            $resolved = (int)(session(SessionKey::POS_BRANCH_ID) ?? 1);
        }

        return $resolved > 0 ? $resolved : 1;
    }

    public function getCartSubtotalCalculation(object $product, array $cartItem, array $calculation): array
    {
        $unitPrice = max(0, (float)($cartItem['price'] ?? 0));
        $quantity = max(0, (int)($cartItem['quantity'] ?? 0));
        $discount = max(0, (float)($cartItem['discount'] ?? 0));
        if ($discount <= 0) {
            $discount = max(0, (float)getProductPriceByType(
                product: $product,
                type: 'discounted_amount',
                result: 'value',
                price: $unitPrice,
                from: 'panel'
            ));
        }

        // Tax must be calculated after product discount.
        $taxableUnitAmount = max(0, $unitPrice - $discount);
        $taxRate = max(0, (float)($product['tax'] ?? 0));
        $taxModel = (string)($product['tax_model'] ?? ($cartItem['tax_model'] ?? 'exclude'));

        if ($taxModel == 'include') {
            $unitIncludedTax = $taxRate > 0
                ? ($taxableUnitAmount * $taxRate) / (100 + $taxRate)
                : 0;

            $taxCalculate = 0;
            $totalIncludeTax = $unitIncludedTax * $quantity;
            $productSubtotal = $taxableUnitAmount * $quantity; // gross (tax incl.)
        } else {
            $unitTax = $this->getTaxAmount($taxableUnitAmount, $taxRate);
            $taxCalculate = $unitTax * $quantity;
            $totalIncludeTax = 0;
            $productSubtotal = $taxableUnitAmount * $quantity; // net (tax excl.)
        }

        return [
            'countItem' => 1,
            'totalQuantity' => $quantity,
            'taxCalculate' => $taxCalculate,
            'totalTaxShow' => $taxCalculate,
            'totalTax' => $taxCalculate,
            'totalIncludeTax' => $totalIncludeTax,
            'productSubtotal' => $productSubtotal,
            'subtotal' => $productSubtotal,
            'discountOnProduct' => $discount * $quantity,
        ];
    }

    public function getTotalCalculation(
        array $subTotalCalculation,
        string $cartName,
        float $installationCharge = 0.0,
        float $exchangeCharge = 0.0
    ): array
    {
        $payload = $this->getCartPayloadById(
            cartId: $cartName,
            branchId: $this->resolveBranchIdFromCartId($cartName, null)
        );

        $itemPrice = (float)$subTotalCalculation['subtotal'] + (float)$subTotalCalculation['discountOnProduct'];
        $itemDiscount = (float)$subTotalCalculation['discountOnProduct'];
        $legacyExtraDiscount = abs((float)($payload['ext_discount'] ?? 0));
        $legacyExtraDiscountType = (string)($payload['ext_discount_type'] ?? 'amount');
        if ($legacyExtraDiscountType === 'percent' && $legacyExtraDiscount > 0) {
            $legacyExtraDiscount = (($subTotalCalculation['subtotal'] + $subTotalCalculation['discountOnProduct'] - $subTotalCalculation['totalIncludeTax']) * $legacyExtraDiscount) / 100;
        }
        $legacyTotal = (float)$subTotalCalculation['subtotal'] - $legacyExtraDiscount;

        $hasIncludeTaxModel = false;
        $hasExcludeTaxModel = false;
        foreach ($payload as $payloadItem) {
            if (!is_array($payloadItem)) {
                continue;
            }
            $lineTaxModel = strtolower((string)($payloadItem['tax_model'] ?? 'exclude'));
            if ($lineTaxModel === 'include') {
                $hasIncludeTaxModel = true;
            } else {
                $hasExcludeTaxModel = true;
            }
        }
        $taxModel = ($hasIncludeTaxModel && !$hasExcludeTaxModel) ? 'include' : 'exclude';

        $summary = OrderManager::calculatePosRetailVatSummary(
            itemPrice: $itemPrice,
            itemDiscount: $itemDiscount,
            extraDiscountInput: abs((float)($payload['ext_discount'] ?? 0)),
            extraDiscountType: (string)($payload['ext_discount_type'] ?? 'amount'),
            couponDiscount: abs((float)($payload['coupon_discount'] ?? 0)),
            totalInstallationPrice: $installationCharge,
            totalExchangePrice: $exchangeCharge,
            taxModel: $taxModel
        );

        $couponDiscount = abs((float)($payload['coupon_discount'] ?? 0));
        $extraDiscount = (float)$legacyExtraDiscount;

        return [
            'total' => $legacyTotal,
            'couponDiscount' => $couponDiscount,
            'extraDiscount' => $extraDiscount,
            'taxableBase' => (float)$summary['taxableBase'],
            'taxTotal' => (float)$summary['taxTotal'],
            'subTotalWithVat' => (float)$summary['subTotalWithVat'],
            'totalAmount' => (float)$summary['totalAmount'],
        ];
    }

    public function customerOnHoldStatus($status, ?string $cartId = null, ?int $branchId = null): void
    {
        $resolvedCartId = trim((string)($cartId ?? session(SessionKey::CURRENT_USER) ?? ''));
        if ($resolvedCartId === '') {
            return;
        }

        $resolvedBranchId = $this->resolveBranchIdFromCartId($resolvedCartId, $branchId);
        $cart = $this->getCartPayloadById($resolvedCartId, $resolvedBranchId);
        $cartKeeper = [];
        if (count($cart) > 0) {
            foreach ($cart as $cartItem) {
                if (is_array($cartItem)) {
                    $cartItem['customerOnHold'] = $status;
                }
                $cartKeeper[] = $cartItem;
            }
        }
        $this->putCartPayloadById($resolvedCartId, $resolvedBranchId, $cartKeeper);
    }

    public function checkCurrentStock(
        ?string $variant,
        array $variation,
        int $productQty,
        int $quantity,
        ?int $branchId = null,
        ?int $productId = null,
        string $productType = 'physical',
        ?int $sellerId = null,
        ?int $productBranchId = null
    ): int
    {
        if ($productType === 'physical' && (int)($productId ?? 0) > 0) {
            $resolvedBranchId = $this->resolvePosBranchId(
                branchId: $branchId,
                productBranchId: $productBranchId,
                sellerId: $sellerId
            );

            if ($resolvedBranchId > 0) {
                $availableQuantity = $this->getBranchStockQuantity(
                    branchId: $resolvedBranchId,
                    productId: (int)$productId,
                    variant: $variant
                );
                return $availableQuantity - $quantity;
            }
        }

        if ($variant !== null && trim((string)$variant) !== '') {
            return $this->getCurrentQuantity(variation: $variation, variant: $variant, quantity: $quantity);
        }

        return $productQty - $quantity;
    }

    public function checkProductTypeDigital(string|int $cartId): bool
    {
        $resolvedCartId = trim((string)$cartId);
        if ($resolvedCartId === '') {
            return false;
        }
        $cart = $this->getCartPayloadById(
            cartId: $resolvedCartId,
            branchId: $this->resolveBranchIdFromCartId($resolvedCartId, null)
        );
        $isDigitalProduct = false;
        foreach ($cart as $item) {
            if (is_array($item) && $item['productType'] == 'digital') {
                $isDigitalProduct = true;
            }
        }
        return $isDigitalProduct;
    }

    public function getCustomerInfo(object|null $currentCustomerData, int $customerId): array
    {
        if ($currentCustomerData) {
            $customerName = $currentCustomerData['f_name'] . ' ' . $currentCustomerData['l_name'];
            $customerPhone = $currentCustomerData['phone'];
        } else {
            $customerName = "";
            $customerPhone = "";
        }
        return [
            'customerName' => $customerName,
            'customerPhone' => $customerPhone
        ];
    }

    private function resolveChoiceValue(object $request, mixed $choice): ?string
    {
        $choiceName = (string)($choice->name ?? $choice['name'] ?? '');
        $choiceTitle = (string)($choice->title ?? $choice['title'] ?? '');
        $value = $this->getRequestValue($request, $choiceName);

        if ($value === null || $value === '') {
            $titleLower = strtolower(trim($choiceTitle));
            $aliases = array_filter([
                $titleLower,
                str_replace(' ', '_', $titleLower),
                preg_replace('/[^a-z0-9]+/', '_', $titleLower),
                preg_replace('/[^a-z0-9]+/', '', $titleLower),
            ]);

            foreach (array_values(array_unique($aliases)) as $alias) {
                $value = $this->getRequestValue($request, (string)$alias);
                if ($value !== null && $value !== '') {
                    break;
                }
            }
        }

        if ($value === null) {
            return null;
        }

        return trim((string)$value);
    }

    private function getRequestValue(object $request, string $key): mixed
    {
        if ($key === '') {
            return null;
        }

        if (method_exists($request, 'input')) {
            $value = $request->input($key);
            if (!is_null($value)) {
                return $value;
            }
        }

        if (isset($request[$key])) {
            return $request[$key];
        }

        if (isset($request->$key)) {
            return $request->$key;
        }

        return null;
    }

    private function variantsMatch(mixed $left, mixed $right): bool
    {
        return $this->getVariantMatcher()->matches($left, $right);
    }

    private function normalizeVariantToken(string $variant): ?string
    {
        return $this->getVariantMatcher()->canonical($variant);
    }

    private function resolvePosBranchId(?int $branchId = null, ?int $productBranchId = null, ?int $sellerId = null): int
    {
        $providedBranchId = (int)($branchId ?? 0);
        if ($providedBranchId > 0) {
            return $providedBranchId;
        }

        $productBranchId = (int)($productBranchId ?? 0);
        if ($productBranchId > 0) {
            return $productBranchId;
        }

        $sellerId = (int)($sellerId ?? 0);
        if ($sellerId > 0) {
            $sellerBranchId = (int)(Branch::query()
                ->where('vendor_id', $sellerId)
                ->where('status', 'active')
                ->orderBy('id')
                ->value('id') ?? 0);

            if ($sellerBranchId > 0) {
                return $sellerBranchId;
            }
        }

        return 1;
    }

    private function getBranchStockQuantity(int $branchId, int $productId, ?string $variant): int
    {
        $branchStocks = ManageBranchProductStock::query()
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->get(['variation_type', 'variation_key', 'current_stock']);

        if ($branchStocks->isEmpty()) {
            return 0;
        }

        $normalizedVariant = $this->normalizeVariantToken(trim((string)($variant ?? '')));
        if ($normalizedVariant === null) {
            return (int)$branchStocks->filter(function ($stockRow) {
                return $this->isDefaultVariationValue($stockRow->variation_type ?? null)
                    || $this->isDefaultVariationValue($stockRow->variation_key ?? null);
            })->sum('current_stock');
        }

        return (int)$branchStocks->filter(function ($stockRow) use ($variant, $normalizedVariant) {
            $variationType = trim((string)($stockRow->variation_type ?? ''));
            $variationKey = trim((string)($stockRow->variation_key ?? ''));

            return $this->variantsMatch($variationType, $normalizedVariant)
                || $this->variantsMatch($variationKey, $normalizedVariant)
                || $this->variantsMatch($variationType, $variant)
                || $this->variantsMatch($variationKey, $variant);
        })->sum('current_stock');
    }

    private function isDefaultVariationValue(mixed $value): bool
    {
        $raw = trim((string)($value ?? ''));
        if ($raw === '') {
            return true;
        }

        return $this->normalizeVariantToken($raw) === null;
    }

    private function getCartPayloadById(string $cartId, ?int $branchId): array
    {
        $resolvedCartId = trim($cartId);
        if ($resolvedCartId === '') {
            return [];
        }

        $resolvedBranchId = $this->resolveBranchIdFromCartId($resolvedCartId, $branchId);
        try {
            return $this->posCartStateService->getPayload(
                cartId: $resolvedCartId,
                branchId: $resolvedBranchId
            );
        } catch (Throwable) {
            return [];
        }
    }

    private function putCartPayloadById(string $cartId, ?int $branchId, array $payload): void
    {
        $resolvedCartId = trim($cartId);
        if ($resolvedCartId === '') {
            return;
        }

        $resolvedBranchId = $this->resolveBranchIdFromCartId($resolvedCartId, $branchId);
        $this->posCartStateService->putPayload(
            cartId: $resolvedCartId,
            branchId: $resolvedBranchId,
            payload: $payload
        );
    }

    private function resolveBranchIdFromCartId(string $cartId, ?int $branchId): int
    {
        $requestedBranchId = (int)($branchId ?? 0);
        if ($requestedBranchId > 0) {
            return $requestedBranchId;
        }

        if (preg_match('/-b(\d+)$/', trim($cartId), $matches)) {
            $fromCartId = (int)($matches[1] ?? 0);
            if ($fromCartId > 0) {
                return $fromCartId;
            }
        }

        return $this->normalizeBranchId($branchId);
    }

    private function getVariantMatcher(): VariantMatcher
    {
        if (is_null($this->variantMatcher)) {
            $this->variantMatcher = new VariantMatcher();
        }

        return $this->variantMatcher;
    }
}
