<?php

namespace App\Support;

use App\Enums\SupportTicketStatusGroup;

class SupportTicketLifecycle
{
    public const STATUS_MASTER_ID = SupportTicketStatusGroup::Support->value;
    public const STATUS_NEW = 1;
    public const STATUS_OPEN = 2;
    public const STATUS_ASSIGNED = 3;
    public const STATUS_TRIAGE = 4;
    public const STATUS_IN_PROGRESS = 5;
    public const STATUS_CLOSED = 19;

    public static function defaultStatusFlow(): array
    {
        return [
            'new' => 'open',
            'open' => 'closed',
            'closed' => 'open',
        ];
    }
}
