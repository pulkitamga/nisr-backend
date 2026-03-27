<?php

namespace App\Services;

use App\Domain\Stock\Support\VariantMatcher;
use App\Enums\SessionKey;
use App\Traits\CalculatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;
use Throwable;

class POSService
{
    use CalculatorTrait;

    public function __construct(
        private readonly VariantMatcher $variantMatcher,
        private readonly PosCartStateService $posCartStateService
    ) {}

    public function getTotalHoldOrders(
        ?int $branchId = null,
        ?string $actorType = null,
        ?int $actorId = null
    ): int
    {
        $totalHoldOrders = 0;
        $branchId = $this->normalizeBranchId($branchId ?? $this->getActivePosBranchId());
        $cartIds = $this->posCartStateService->listCartIdsByBranch(
            branchId: $branchId,
            actorType: $actorType,
            actorId: $actorId,
            nonEmptyOnly: true
        );
        foreach ($cartIds as $item) {
            try {
                $cartData = $this->posCartStateService->getPayload(
                    cartId: (string)$item,
                    branchId: $branchId,
                    actorType: $actorType,
                    actorId: $actorId
                );
            } catch (Throwable) {
                $cartData = [];
            }
            $cartLineItems = $this->getCartLineItems(cartData: is_array($cartData) ? $cartData : []);
            if (count($cartLineItems) > 0 && (($cartLineItems[0]['customerOnHold'] ?? false) === true)) {
                $totalHoldOrders++;
            }
        }
        return $totalHoldOrders;
    }

    public function getCartNames(
        ?int $branchId = null,
        ?string $actorType = null,
        ?int $actorId = null
    ): array
    {
        $branchId = $this->normalizeBranchId($branchId ?? $this->getActivePosBranchId());
        return $this->posCartStateService->listCartIdsByBranch(
            branchId: $branchId,
            actorType: $actorType,
            actorId: $actorId,
            nonEmptyOnly: true
        );
    }

    public function syncCartForCustomerChange(
        string $cartId,
        ?int $branchId = null,
        ?string $currentCartId = null,
        ?string $actorType = null,
        ?int $actorId = null
    ): void
    {
        $resolvedBranchId = $this->normalizeBranchId($branchId);
        $cartId = trim($cartId);
        if ($cartId === '') {
            return;
        }

        $targetState = $this->posCartStateService->ensureCart(
            cartId: $cartId,
            branchId: $resolvedBranchId,
            actorType: $actorType,
            actorId: $actorId
        );

        $sourceCartId = trim((string)($currentCartId ?? ''));
        if ($sourceCartId === '' || $sourceCartId === $cartId) {
            return;
        }

        try {
            $cart = $this->posCartStateService->getPayload(
                cartId: $sourceCartId,
                branchId: $resolvedBranchId,
                actorType: $targetState->actor_type,
                actorId: (int)$targetState->actor_id
            );
        } catch (Throwable) {
            $cart = [];
        }

        if (!is_array($cart) || empty($cart)) {
            return;
        }

        $cartKeeper = [];
        foreach ($cart as $cartItem) {
            if (is_array($cartItem)) {
                $cartItem['customerId'] = $this->resolveCustomerIdFromCartId(cartId: $cartId);
            }
            $cartKeeper[] = $cartItem;
        }

        $this->posCartStateService->putPayload(
            cartId: $cartId,
            branchId: $resolvedBranchId,
            payload: $cartKeeper,
            actorType: $targetState->actor_type,
            actorId: (int)$targetState->actor_id
        );
    }

    public function checkConditions(
        float $amount,
        float $paidAmount = null,
        ?string $cartId = null,
        ?int $branchId = null,
        ?string $actorType = null,
        ?int $actorId = null
    ): bool
    {
        $condition = false;
        $resolvedCartId = trim((string)($cartId ?? session(SessionKey::POS_CART_ID) ?? ''));
        $resolvedBranchId = $this->normalizeBranchId($branchId ?? $this->getActivePosBranchId());

        if ($resolvedCartId === '') {
            Toastr::error(translate('cart_empty_warning'));
            return true;
        }

        try {
            $cartData = $this->posCartStateService->getPayload(
                cartId: $resolvedCartId,
                branchId: $resolvedBranchId,
                actorType: $actorType,
                actorId: $actorId
            );
        } catch (Throwable) {
            $cartData = [];
        }

        if (!is_array($cartData) || count($this->getCartLineItems(cartData: $cartData)) < 1) {
            Toastr::error(translate('cart_empty_warning'));
            return true;
        }

        if ($amount <= 0) {
            Toastr::error(translate('amount_cannot_be_lees_then_0'));
            return true;
        }
        if (!is_null($paidAmount) && $paidAmount < $amount) {
            Toastr::error(translate('paid_amount_is_less_than_total_amount'));
            return true;
        }
        return $condition;
    }

    public function getCouponCalculation(
        object $coupon,
        float $totalProductPrice,
        float $productDiscount,
        float $productTax,
        ?string $cartId = null,
        ?int $branchId = null,
        ?string $actorType = null,
        ?int $actorId = null
    ): array
    {
        $extraDiscount = 0;
        if ($coupon['discount_type'] === 'percentage') {
            $discount = min((($totalProductPrice / 100) * $coupon['discount']), $coupon['max_discount']);
        } else {
            $discount = $coupon['discount'];
        }

        $resolvedCartId = trim((string)($cartId ?? session(SessionKey::POS_CART_ID) ?? ''));
        $resolvedBranchId = $this->normalizeBranchId($branchId ?? $this->getActivePosBranchId());
        $cartData = [];
        if ($resolvedCartId !== '') {
            try {
                $cartData = $this->posCartStateService->getPayload(
                    cartId: $resolvedCartId,
                    branchId: $resolvedBranchId,
                    actorType: $actorType,
                    actorId: $actorId
                );
            } catch (Throwable) {
                $cartData = [];
            }
        }
        if (is_array($cartData) && isset($cartData['ext_discount_type']) && (float)($cartData['ext_discount'] ?? 0) > 0) {
            $extraDiscount = $this->getDiscountAmount(
                price: $totalProductPrice,
                discount: (float)$cartData['ext_discount'],
                discountType: (string)$cartData['ext_discount_type']
            );
        }
        $total = $totalProductPrice - $productDiscount + $productTax - $discount - $extraDiscount;
        return [
            'total' => $total,
            'discount' => $discount,
        ];
    }

    private function getCartLineItems(array $cartData): array
    {
        return collect($cartData)
            ->filter(fn($item) => is_array($item) && isset($item['id']))
            ->values()
            ->all();
    }

    private function resolveCustomerIdFromCartId(string $cartId): string
    {
        if (Str::contains($cartId, 'walking-customer')) {
            return '0';
        }

        $segments = explode('-', $cartId);
        return isset($segments[2]) ? (string)$segments[2] : '0';
    }

    public function putCouponDataOnCart(
        $cartId,
        $discount,
        $couponTitle,
        $couponBearer,
        $couponCode,
        ?int $branchId = null,
        ?string $actorType = null,
        ?int $actorId = null
    ): void
    {
        $resolvedCartId = trim((string)$cartId);
        $resolvedBranchId = $this->normalizeBranchId($branchId ?? $this->getActivePosBranchId());
        $cart = [];
        if ($resolvedCartId !== '') {
            try {
                $cart = $this->posCartStateService->getPayload(
                    cartId: $resolvedCartId,
                    branchId: $resolvedBranchId,
                    actorType: $actorType,
                    actorId: $actorId
                );
            } catch (Throwable) {
                $cart = [];
            }
        }

        $cart['coupon_code'] = $couponCode;
        $cart['coupon_discount'] = $discount;
        $cart['coupon_title'] = $couponTitle;
        $cart['coupon_bearer'] = $couponBearer;
        $this->posCartStateService->putPayload(
            cartId: $resolvedCartId,
            branchId: $resolvedBranchId,
            payload: $cart,
            actorType: $actorType,
            actorId: $actorId
        );
    }

    public function getVariantData(string $type, array $variation, int $quantity): array
    {
        $variationData = [];
        foreach ($variation as $variant) {
            if ($this->variantMatcher->matches($type, $variant['type'] ?? null)) {
                $variant['qty'] -= $quantity;
            }
            $variationData[] = $variant;
        }
        return $variationData;
    }

    public function getSummaryData(
        ?int $branchId = null,
        ?string $activeCartId = null,
        ?string $actorType = null,
        ?int $actorId = null
    ): array
    {
        $branchId = $this->normalizeBranchId($branchId ?? $this->getActivePosBranchId());
        return [
            'cartName' => $this->posCartStateService->listCartIdsByBranch(
                branchId: $branchId,
                actorType: $actorType,
                actorId: $actorId,
                nonEmptyOnly: false
            ),
            'currentUser' => trim((string)($activeCartId ?? '')),
            'totalHoldOrders' => $this->getTotalHoldOrders($branchId, $actorType, $actorId),
            'cartNames' => $this->getCartNames($branchId, $actorType, $actorId),
        ];
    }

    private function getActivePosBranchId(): int
    {
        return $this->normalizeBranchId((int)(session(SessionKey::POS_BRANCH_ID) ?? 0));
    }

    private function normalizeBranchId(?int $branchId): int
    {
        $resolved = (int)($branchId ?? 0);
        return $resolved > 0 ? $resolved : 1;
    }

    private function cartBelongsToBranch(string $cartId, int $branchId): bool
    {
        $cartId = trim($cartId);
        if ($cartId === '') {
            return false;
        }

        $suffix = '-b' . $branchId;
        if (str_ends_with($cartId, $suffix)) {
            return true;
        }

        return $branchId === 1 && !preg_match('/-b\d+$/', $cartId);
    }
}
