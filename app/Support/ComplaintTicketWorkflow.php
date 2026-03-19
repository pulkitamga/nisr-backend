<?php

namespace App\Support;

use App\Enums\SupportTicketStatusGroup;

class ComplaintTicketWorkflow
{
    public const STATUS_MASTER_ID = SupportTicketStatusGroup::Complaint->value;
    public const STATUS_NEW = 36;
    public const STATUS_OPEN = 37;
    public const STATUS_ASSIGNED = 38;
    public const STATUS_IN_PROGRESS = 39;
    public const STATUS_WAITING = 40;
    public const STATUS_RESOLVED = 41;
    public const STATUS_CLOSED = 42;

    public static function customerNotifiableStatuses(): array
    {
        return [self::STATUS_RESOLVED, self::STATUS_CLOSED];
    }

    public static function cronReminderStatuses(): array
    {
        return [self::STATUS_OPEN, self::STATUS_ASSIGNED];
    }
}
