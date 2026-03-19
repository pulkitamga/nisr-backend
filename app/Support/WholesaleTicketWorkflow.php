<?php

namespace App\Support;

use App\Enums\SupportTicketStatusGroup;

class WholesaleTicketWorkflow
{
    public const STATUS_MASTER_ID = SupportTicketStatusGroup::Wholesale->value;
    public const STATUS_NEW = 56;
    public const STATUS_OPEN = 57;
    public const STATUS_ASSIGNED = 58;
    public const STATUS_IN_PROGRESS = 59;
    public const STATUS_RESOLVED = 60;
    public const STATUS_CLOSED = 61;
    public const STATUS_CANCELLED = 62;

    public static function followUpRequiredStatuses(): array
    {
        return [self::STATUS_IN_PROGRESS];
    }

    public static function customerNotifiableStatuses(): array
    {
        return [self::STATUS_RESOLVED, self::STATUS_CLOSED];
    }

    public static function cronReminderStatuses(): array
    {
        return [self::STATUS_OPEN, self::STATUS_ASSIGNED];
    }
}
