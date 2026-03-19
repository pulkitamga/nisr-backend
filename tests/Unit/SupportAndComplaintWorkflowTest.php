<?php

namespace Tests\Unit;

use App\Support\ComplaintTicketWorkflow;
use App\Support\RetailTicketWorkflow;
use App\Support\SupportTicketLifecycle;
use PHPUnit\Framework\TestCase;

class SupportAndComplaintWorkflowTest extends TestCase
{
    public function test_support_lifecycle_constants_are_stable(): void
    {
        $this->assertSame(1, SupportTicketLifecycle::STATUS_MASTER_ID);
        $this->assertSame(1, SupportTicketLifecycle::STATUS_NEW);
        $this->assertSame(2, SupportTicketLifecycle::STATUS_OPEN);
        $this->assertSame(3, SupportTicketLifecycle::STATUS_ASSIGNED);
        $this->assertSame(4, SupportTicketLifecycle::STATUS_TRIAGE);
        $this->assertSame(5, SupportTicketLifecycle::STATUS_IN_PROGRESS);
        $this->assertSame(19, SupportTicketLifecycle::STATUS_CLOSED);
    }

    public function test_complaint_workflow_constants_are_stable(): void
    {
        $this->assertSame(4, ComplaintTicketWorkflow::STATUS_MASTER_ID);
        $this->assertSame(36, ComplaintTicketWorkflow::STATUS_NEW);
        $this->assertSame(37, ComplaintTicketWorkflow::STATUS_OPEN);
        $this->assertSame(38, ComplaintTicketWorkflow::STATUS_ASSIGNED);
        $this->assertSame(39, ComplaintTicketWorkflow::STATUS_IN_PROGRESS);
        $this->assertSame(40, ComplaintTicketWorkflow::STATUS_WAITING);
        $this->assertSame(41, ComplaintTicketWorkflow::STATUS_RESOLVED);
        $this->assertSame(42, ComplaintTicketWorkflow::STATUS_CLOSED);
        $this->assertSame([41, 42], ComplaintTicketWorkflow::customerNotifiableStatuses());
        $this->assertSame([37, 38], ComplaintTicketWorkflow::cronReminderStatuses());
    }

    public function test_retail_workflow_constants_are_stable(): void
    {
        $this->assertSame(5, RetailTicketWorkflow::STATUS_MASTER_ID);
        $this->assertSame(43, RetailTicketWorkflow::STATUS_NEW);
        $this->assertSame(44, RetailTicketWorkflow::STATUS_OPEN);
        $this->assertSame(45, RetailTicketWorkflow::STATUS_ASSIGNED);
        $this->assertSame(46, RetailTicketWorkflow::STATUS_IN_PROGRESS);
        $this->assertSame(48, RetailTicketWorkflow::STATUS_CLOSED);
        $this->assertSame(51, RetailTicketWorkflow::STATUS_RMA_ISSUED);
        $this->assertSame(52, RetailTicketWorkflow::STATUS_RMA_RECEIVED);
        $this->assertSame(54, RetailTicketWorkflow::STATUS_REFUND_REJECTED);
        $this->assertSame([46], RetailTicketWorkflow::followUpRequiredStatuses());
        $this->assertSame([54], RetailTicketWorkflow::reminderCycleRequiredStatuses());
    }
}
