<?php

namespace Tests\Unit;

use App\Enums\AdminNotificationRecipientType;
use App\Enums\TicketDispatchTarget;
use PHPUnit\Framework\TestCase;

class NotificationFlagEnumsTest extends TestCase
{
    public function test_ticket_dispatch_target_values_are_stable(): void
    {
        $this->assertSame(0, TicketDispatchTarget::DepartmentHead->value);
        $this->assertSame(1, TicketDispatchTarget::Employee->value);
        $this->assertSame(2, TicketDispatchTarget::Customer->value);
    }

    public function test_admin_notification_recipient_values_are_stable(): void
    {
        $this->assertSame(1, AdminNotificationRecipientType::Employee->value);
        $this->assertSame(2, AdminNotificationRecipientType::Department->value);
        $this->assertSame(3, AdminNotificationRecipientType::Customer->value);
    }
}
