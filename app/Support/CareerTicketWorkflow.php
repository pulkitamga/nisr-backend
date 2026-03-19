<?php

namespace App\Support;

use App\Enums\SupportTicketStatusGroup;

class CareerTicketWorkflow
{
    public const STATUS_MASTER_ID = SupportTicketStatusGroup::Career->value;
    public const STATUS_NEW = 27;
    public const STATUS_OPEN = 28;
    public const STATUS_ASSIGNED = 29;
    public const STATUS_SCREENING = 30;
    public const STATUS_INTERVIEW = 31;
    public const STATUS_OFFER = 32;
    public const STATUS_HIRED = 33;
    public const STATUS_REJECTED = 34;
    public const STATUS_CLOSED = 35;

    public static function nextStatusMap(): array
    {
        return [
            self::STATUS_NEW => self::STATUS_ASSIGNED,
            self::STATUS_OPEN => self::STATUS_ASSIGNED,
            self::STATUS_ASSIGNED => self::STATUS_SCREENING,
            self::STATUS_SCREENING => self::STATUS_INTERVIEW,
            self::STATUS_INTERVIEW => self::STATUS_OFFER,
            self::STATUS_OFFER => self::STATUS_CLOSED,
        ];
    }
}
