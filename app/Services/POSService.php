<?php

namespace App\Services;

use App\Domain\Stock\Support\VariantMatcher;
use App\Enums\SessionKey;
use App\Traits\CalculatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;

class POSService
{
    use CalculatorTrait;

    public function __construct(private readonly VariantMatcher $variantMatcher) {}

    public function getTotalHoldOrders(?int $branchId = null): int
    {
        $totalHoldOrders = 0;
        $branchId = $this->normalizeBranchId($branchId ?? $this->getActivePosBranchId());
        if (session()->has(SessionKey::CART_NAME)) {
            foreach (session(SessionKey::CART_NAME) as $item) {
                if (!$this->cartBelongsToBranch((string)$item, $branchId)) {
                    continue;
                }
                $cartData = session()->has($item) ? session($item) : [];
                $cartLineItems = $this->getCartLineItems(cartData: is_array($cartData) ? $cartData : []);
                if (count($cartLineItems) > 0) {
                    if (($cartLineItems[0]['customerOnHold'] ?? false) === true) {
                        $totalHoldOrders++;
                    }
                }
            }
        }
        return $totalHoldOrders;
    }

    public function getCartNames(?int $branchId = null): array
    {
        $cartNames = [];
        $branchId = $this->normalizeBranchId($branchId ?? $this->getActivePosBranchId());
        if (session()->has(SessionKey::CART_NAME)) {
            foreach (session(SessionKey::CART_NAME) as $item) {
                if (!$this->cartBelongsToBranch((string)$item, $branchId)) {
                    continue;
                }
                $cartData = session()->has($item) ? session($item) : [];
                $cartLineItems = $this->getCartLineItems(cartData: is_array($cartData) ? $cartData : []);
                if (count($cartLineItems) > 0) {
                    $cartNames[] = $item;
                }
            }
        }
        return $cartNames;
    }

    public function UpdateSessionWhenCustomerChange(string $cartId, ?int $branchId = null): void
    {
        $resolvedBranchId = $this->normalizeBranchId($branchId);
        session()->put(SessionKey::POS_BRANCH_ID, $resolvedBranchId);
        $cartNames = session(SessionKey::CART_NAME) ?? [];
        if (!is_array($cartNames)) {
            $cartNames = [];
        }
        if (!in_array($cartId, $cartNames, true)) {
            $cartNames[] = $cartId;
            session()->put(SessionKey::CART_NAME, $cartNames);
        }
        $currentCartId = session(SessionKey::CURRENT_USER);
        $cart = is_string($currentCartId) ? session($currentCartId, []) : [];
        $cartKeeper = [];
        if (is_array($cart) && count($cart) > 0) {
            foreach ($cart as $cartItem) {
                if (is_array($cartItem)) {
                    $cartItem['customerId'] = $this->resolveCustomerIdFromCartId(cartId: $cartId);
                }
                $cartKeeper[] = $cartItem;
            }
        }
        session()->put($cartId, $cartKeeper);
        session()->put(SessionKey::CURRENT_USER, $cartId);
    }

    public function checkConditions(float $amount, float $paidAmount = null): bool
    {
        $condition = false;
        $cartId = session(SessionKey::CURRENT_USER);
        if (session()->has($cartId)) {
            $cartData = session()->get($cartId);
            if (!is_array($cartData) || count($this->getCartLineItems(cartData: $cartData)) < 1) {
                Toastr::error(translate('cart_empty_warning'));
                return true;
            }
        } else {
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

    public function getCouponCalculation(object $coupon, float $totalProductPrice, float $productDiscount, float $productTax): array
    {
        $extraDiscount = 0;
        if ($coupon['discount_type'] === 'percentage') {
            $discount = min((($totalProductPrice / 100) * $coupon['discount']), $coupon['max_discount']);
        } else {
            $discount = $coupon['discount'];
        }

        $cartId = session(SessionKey::CURRENT_USER);
        $cartData = is_string($cartId) ? session($cartId, []) : [];
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

    public function putCouponDataOnSession($cartId, $discount, $couponTitle, $couponBearer, $couponCode): void
    {
        $cart = session($cartId, collect([]));
        $cart['coupon_code'] = $couponCode;
        $cart['coupon_discount'] = $discount;
        $cart['coupon_title'] = $couponTitle;
        $cart['coupon_bearer'] = $couponBearer;
        session()->put($cartId, $cart);
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

    public function getSummaryData(?int $branchId = null): array
    {
        $branchId = $this->normalizeBranchId($branchId ?? $this->getActivePosBranchId());
        return [
            'cartName' => session(SessionKey::CART_NAME),
            'currentUser' => session(SessionKey::CURRENT_USER),
            'totalHoldOrders' => $this->getTotalHoldOrders($branchId),
            'cartNames' => $this->getCartNames($branchId),
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
