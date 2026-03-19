<?php

namespace Tests\Unit;

use App\Support\WholesaleTicketWorkflow;
use PHPUnit\Framework\TestCase;

class WholesaleTicketWorkflowTest extends TestCase
{
    public function test_wholesale_workflow_constants_are_stable(): void
    {
        $this->assertSame(6, WholesaleTicketWorkflow::STATUS_MASTER_ID);
        $this->assertSame(56, WholesaleTicketWorkflow::STATUS_NEW);
        $this->assertSame(57, WholesaleTicketWorkflow::STATUS_OPEN);
        $this->assertSame(58, WholesaleTicketWorkflow::STATUS_ASSIGNED);
        $this->assertSame(59, WholesaleTicketWorkflow::STATUS_IN_PROGRESS);
        $this->assertSame(60, WholesaleTicketWorkflow::STATUS_RESOLVED);
        $this->assertSame(61, WholesaleTicketWorkflow::STATUS_CLOSED);
        $this->assertSame(62, WholesaleTicketWorkflow::STATUS_CANCELLED);
    }

    public function test_wholesale_workflow_rules_are_stable(): void
    {
        $this->assertSame([WholesaleTicketWorkflow::STATUS_IN_PROGRESS], WholesaleTicketWorkflow::followUpRequiredStatuses());
        $this->assertSame(
            [WholesaleTicketWorkflow::STATUS_RESOLVED, WholesaleTicketWorkflow::STATUS_CLOSED],
            WholesaleTicketWorkflow::customerNotifiableStatuses()
        );
        $this->assertSame(
            [WholesaleTicketWorkflow::STATUS_OPEN, WholesaleTicketWorkflow::STATUS_ASSIGNED],
            WholesaleTicketWorkflow::cronReminderStatuses()
        );
    }
}
