<?php

namespace App\Services;

use App\Domain\Stock\Support\VariantMatcher;
use App\Enums\SessionKey;
use App\Models\Branch;
use App\Models\ManageBranchProductStock;
use App\Traits\CalculatorTrait;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CartService
{
    use CalculatorTrait;

    private ?VariantMatcher $variantMatcher = null;

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
        $requestedCartId = trim((string)($this->getRequestValue($request, 'cart_id') ?? ''));
        $activeCartId = $requestedCartId !== '' && session()->has($requestedCartId)
            ? $requestedCartId
            : (string)(session(SessionKey::CURRENT_USER) ?? '');
        $cartData = $activeCartId !== '' ? (array)session()->get($activeCartId, []) : [];
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

    public function getUserId(): int
    {
        $userId = 0;
        if (Str::contains(session(SessionKey::CURRENT_USER), 'saved-customer')) {
            $userId = explode('-', session(SessionKey::CURRENT_USER))[2];
        }
        return $userId;
    }

    public function getUserType(): string
    {
        $userType = 'walking-customer';
        if (Str::contains(session(SessionKey::CURRENT_USER), 'saved-customer')) {
            $userType = 'saved-customer';
        }
        return $userType;
    }

    public function getNewCartSession(string|int $cartId): void
    {
        $activeCartId = (string)$cartId;
        if (session()->has(SessionKey::CURRENT_USER)) {
            $activeCartId = (string)session(SessionKey::CURRENT_USER);
        } else {
            session()->put(SessionKey::CURRENT_USER, $activeCartId);
        }

        if (!session()->has(SessionKey::CART_NAME)) {
            session()->put(SessionKey::CART_NAME, []);
        }
        if (!in_array($activeCartId, session(SessionKey::CART_NAME) ?? [], true)) {
            session()->push(SessionKey::CART_NAME, $activeCartId);
        }
        if (!session()->has($activeCartId) || !is_array(session($activeCartId))) {
            session()->put($activeCartId, []);
        }
    }

    public function getCartKeeper(): void
    {
        $cartId = session(SessionKey::CURRENT_USER);
        $cart = session($cartId);
        $cartKeeper = [];
        if (session()->has($cartId) && count($cart) > 0) {
            foreach ($cart as $cartItem) {
                $cartKeeper[] = $cartItem;
            }
        }
        session()->put(session(SessionKey::CURRENT_USER), $cartKeeper);
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
        $resolvedCartId = trim((string)($cartId ?? session(SessionKey::CURRENT_USER) ?? ''));
        if ($resolvedCartId === '') {
            $resolvedCartId = $this->generateWalkingCustomerCartId($this->getActivePosBranchId());
            session()->put(SessionKey::CURRENT_USER, $resolvedCartId);
            if (!in_array($resolvedCartId, session(SessionKey::CART_NAME) ?? [], true)) {
                session()->push(SessionKey::CART_NAME, $resolvedCartId);
            }
        }
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
            'customerId' => $this->getUserId(),
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
        if (session()->has($resolvedCartId)) {
            $keeper = [];
            foreach ((array)session($resolvedCartId, []) as $item) {
                $keeper[] = $item;
            }
            $keeper[] = $sessionData;

            if (!isset(session()->get($resolvedCartId)['add_to_cart_time'])) {
                $keeper += ['add_to_cart_time' => Carbon::now()];
            }
            session()->put($resolvedCartId, $keeper);
        } else {
            session()->put($resolvedCartId, [$sessionData] + ['add_to_cart_time' => Carbon::now()]);
        }

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
        $resolvedCartId = trim((string)($cartId ?? session(SessionKey::CURRENT_USER) ?? ''));
        $cart = $resolvedCartId !== '' ? (array)session($resolvedCartId, []) : [];
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
        if ($resolvedCartId !== '') {
            session()->put($resolvedCartId, $keeper);
        }
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
        session()->put(SessionKey::CURRENT_USER, $cartId);
        if (!in_array($cartId, session(SessionKey::CART_NAME) ?? [], true)) {
            session()->push(SessionKey::CART_NAME, $cartId);
        }
        session()->put($cartId, []);
    }

    public function generateWalkingCustomerCartId(?int $branchId = null): string
    {
        $branchId = $this->normalizeBranchId($branchId);
        $suffix = '-b' . $branchId;
        do {
            $cartId = 'walking-customer-' . Str::lower(Str::random(16)) . $suffix;
        } while (
            session()->has($cartId) ||
            in_array($cartId, session(SessionKey::CART_NAME) ?? [], true)
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
        $taxCalculate = $product['tax_model'] == 'include' ? 0 : $this->getTaxAmount($cartItem['price'], $product['tax']) * $cartItem['quantity'];
        $discount = getProductPriceByType(product: $product, type: 'discounted_amount', result: 'value', price: $cartItem['price'], from: 'panel');
        $productSubtotal = (($cartItem['price'] - $discount) * $cartItem['quantity']) - ($product['tax_model'] == 'include' ? $taxCalculate : 0);
        return [
            'countItem' => 1,
            'totalQuantity' => $cartItem['quantity'],
            'taxCalculate' => $taxCalculate,
            'totalTaxShow' => $taxCalculate,
            'totalTax' => $taxCalculate,
            'totalIncludeTax' => $product['tax_model'] == 'include' ? $this->getTaxAmount($cartItem['price'], $product['tax']) * $cartItem['quantity'] : 0,
            'productSubtotal' => $productSubtotal,
            'subtotal' => $productSubtotal - ($cartItem['tax_model'] == 'include' ? $taxCalculate : 0),
            'discountOnProduct' => $discount * $cartItem['quantity'],
        ];
    }

    public function getTotalCalculation(array $subTotalCalculation, string $cartName): array
    {
        $total = $subTotalCalculation['subtotal'];
        $extraDiscount = session()->get($cartName)['ext_discount'] ?? 0;
        $extraDiscountType = session()->get($cartName)['ext_discount_type'] ?? 'amount';
        if ($extraDiscountType == 'percent' && $extraDiscount > 0) {
            $extraDiscount = (($subTotalCalculation['subtotal'] + $subTotalCalculation['discountOnProduct'] - $subTotalCalculation['totalIncludeTax']) * $extraDiscount) / 100;
        }
        if ($extraDiscount) {
            $total -= $extraDiscount;
        }
        $couponDiscount = 0;
        if (isset(session()->get($cartName)['coupon_discount'])) {
            $couponDiscount = session()->get($cartName)['coupon_discount'];
        }
        return [
            'total' => $total,
            'couponDiscount' => $couponDiscount,
            'extraDiscount' => $extraDiscount
        ];
    }

    public function customerOnHoldStatus($status): void
    {
        $cart = session(session(SessionKey::CURRENT_USER));
        $cartKeeper = [];
        if (session()->has(session(SessionKey::CURRENT_USER)) && count($cart) > 0) {
            foreach ($cart as $cartItem) {
                if (is_array($cartItem)) {
                    $cartItem['customerOnHold'] = $status;
                }
                $cartKeeper[] = $cartItem;
            }
        }
        session()->put(session(SessionKey::CURRENT_USER), $cartKeeper);
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
        $cart = session($cartId);
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
            session()->forget(session($customerId));
            $this->getNewCartId();
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

    private function getVariantMatcher(): VariantMatcher
    {
        if (is_null($this->variantMatcher)) {
            $this->variantMatcher = new VariantMatcher();
        }

        return $this->variantMatcher;
    }
}
