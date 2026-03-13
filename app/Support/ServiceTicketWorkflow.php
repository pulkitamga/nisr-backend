<?php

namespace App\Support;

class ServiceTicketWorkflow
{
    public const STATUS_MASTER_ID = 2;
    public const STATUS_NEW = 20;
    public const STATUS_OPEN = 21;
    public const STATUS_ASSIGNED = 22;
    public const STATUS_SCHEDULED = 23;
    public const STATUS_IN_PROGRESS = 24;
    public const STATUS_COMPLETED = 25;
    public const STATUS_CLOSED = 26;

    public static function activeSlaStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_ASSIGNED,
            self::STATUS_SCHEDULED,
            self::STATUS_IN_PROGRESS,
        ];
    }

    public static function canCancelFromStatus(int $status): bool
    {
        return in_array($status, [
            self::STATUS_ASSIGNED,
            self::STATUS_SCHEDULED,
            self::STATUS_IN_PROGRESS,
        ], true);
    }
}
