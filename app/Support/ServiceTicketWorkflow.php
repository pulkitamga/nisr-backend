<?php

namespace App\Support;

class ServiceTicketWorkflow
{
    public const STATUS_MASTER_ID = 2;
    public const STATUS_NEW = 20;
    public const STATUS_ASSIGNED = 21;
    public const STATUS_SCHEDULED = 22;
    public const STATUS_READY_TO_START = 23;
    public const STATUS_IN_PROGRESS = 24;
    public const STATUS_QA_PENDING = 25;
    public const STATUS_CLOSED = 26;

    public static function activeSlaStatuses(): array
    {
        return [
            self::STATUS_ASSIGNED,
            self::STATUS_SCHEDULED,
            self::STATUS_READY_TO_START,
            self::STATUS_IN_PROGRESS,
        ];
    }

    public static function canCancelFromStatus(int $status): bool
    {
        return in_array($status, [
            self::STATUS_SCHEDULED,
            self::STATUS_READY_TO_START,
            self::STATUS_IN_PROGRESS,
        ], true);
    }
}
