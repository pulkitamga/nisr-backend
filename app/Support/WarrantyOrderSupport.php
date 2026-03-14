<?php

namespace App\Support;

use Carbon\Carbon;

class WarrantyOrderSupport
{
    public static function isDeliveredItem($order, $detail): bool
    {
        if (strtolower((string)($order->order_status ?? '')) !== 'delivered') {
            return false;
        }

        if (strtolower((string)($order->order_type ?? '')) === 'pos') {
            return true;
        }

        return strtolower((string)($detail->delivery_status ?? '')) === 'delivered';
    }

    public static function resolvePurchaseDate($order, $detail = null): Carbon
    {
        $source = $order->created_at
            ?? $detail?->created_at
            ?? now();

        return Carbon::parse($source);
    }

    public static function canActivate(
        bool $isWarrantyEnabled,
        bool $isDeliveredItem,
        int $remainingCount,
    ): bool {
        return $isWarrantyEnabled
            && $isDeliveredItem
            && $remainingCount > 0;
    }

    public static function supportMessage(
        bool $isWarrantyEnabled,
        bool $isDeliveredItem,
        int $remainingCount,
    ): string {
        if (!$isWarrantyEnabled) {
            return translate('no_warranty');
        }

        if (!$isDeliveredItem) {
            return translate('available_after_delivery');
        }

        if ($remainingCount <= 0) {
            return translate('all_warranty_units_for_this_item_are_already_activated');
        }

        return translate('warranty_ready_for_activation_for_this_delivered_item');
    }
}
