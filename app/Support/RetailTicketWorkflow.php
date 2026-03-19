<?php

namespace App\Support;

use App\Enums\SupportTicketStatusGroup;

class RetailTicketWorkflow
{
    public const STATUS_MASTER_ID = SupportTicketStatusGroup::Retail->value;
    public const STATUS_NEW = 43;
    public const STATUS_OPEN = 44;
    public const STATUS_ASSIGNED = 45;
    public const STATUS_IN_PROGRESS = 46;
    public const STATUS_RESOLVED = 47;
    public const STATUS_CLOSED = 48;
    public const STATUS_CANCELLED = 49;
    public const STATUS_RETURN_REQUESTED = 50;
    public const STATUS_RMA_ISSUED = 51;
    public const STATUS_RMA_RECEIVED = 52;
    public const STATUS_REFUND_APPROVED = 53;
    public const STATUS_REFUND_REJECTED = 54;
    public const STATUS_REFUND_POSTED = 55;

    public static function followUpRequiredStatuses(): array
    {
        return [self::STATUS_IN_PROGRESS];
    }

    public static function reminderCycleRequiredStatuses(): array
    {
        return [self::STATUS_REFUND_REJECTED];
    }
}
