<?php

namespace App\Support;

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

    public static function isWithinActivationWindow(?int $deliveredDays, int $activationWindowDays): bool
    {
        return $deliveredDays !== null && $deliveredDays <= $activationWindowDays;
    }

    public static function canActivate(
        bool $isTraceable,
        bool $isDeliveredItem,
        bool $withinActivationWindow,
        int $remainingCount,
    ): bool {
        return $isTraceable
            && $isDeliveredItem
            && $withinActivationWindow
            && $remainingCount > 0;
    }

    public static function supportMessage(
        bool $isTraceable,
        bool $isDeliveredItem,
        bool $withinActivationWindow,
        int $remainingCount,
    ): string {
        if (!$isTraceable) {
            return translate('serial_based_warranty_activation_not_supported');
        }

        if (!$isDeliveredItem) {
            return translate('available_after_delivery');
        }

        if (!$withinActivationWindow) {
            return translate('warranty_activation_window_closed_for_this_item');
        }

        if ($remainingCount <= 0) {
            return translate('all_warranty_units_for_this_item_are_already_activated');
        }

        return translate('warranty_activation_window_open_for_this_delivered_item');
    }
}
