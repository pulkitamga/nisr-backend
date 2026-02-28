<?php

namespace Tests\Unit;

use App\Support\ServiceTicketWorkflow;
use PHPUnit\Framework\TestCase;

class ServiceTicketWorkflowTest extends TestCase
{
    public function test_service_status_constants_are_stable(): void
    {
        $this->assertSame(2, ServiceTicketWorkflow::STATUS_MASTER_ID);
        $this->assertSame(20, ServiceTicketWorkflow::STATUS_NEW);
        $this->assertSame(21, ServiceTicketWorkflow::STATUS_ASSIGNED);
        $this->assertSame(22, ServiceTicketWorkflow::STATUS_SCHEDULED);
        $this->assertSame(23, ServiceTicketWorkflow::STATUS_READY_TO_START);
        $this->assertSame(24, ServiceTicketWorkflow::STATUS_IN_PROGRESS);
        $this->assertSame(25, ServiceTicketWorkflow::STATUS_QA_PENDING);
        $this->assertSame(26, ServiceTicketWorkflow::STATUS_CLOSED);
    }

    public function test_active_sla_statuses_include_only_expected_stages(): void
    {
        $this->assertSame(
            [
                ServiceTicketWorkflow::STATUS_ASSIGNED,
                ServiceTicketWorkflow::STATUS_SCHEDULED,
                ServiceTicketWorkflow::STATUS_READY_TO_START,
                ServiceTicketWorkflow::STATUS_IN_PROGRESS,
            ],
            ServiceTicketWorkflow::activeSlaStatuses()
        );
    }

    public function test_cancel_is_allowed_only_in_service_execution_stages(): void
    {
        $this->assertFalse(ServiceTicketWorkflow::canCancelFromStatus(ServiceTicketWorkflow::STATUS_NEW));
        $this->assertFalse(ServiceTicketWorkflow::canCancelFromStatus(ServiceTicketWorkflow::STATUS_ASSIGNED));
        $this->assertTrue(ServiceTicketWorkflow::canCancelFromStatus(ServiceTicketWorkflow::STATUS_SCHEDULED));
        $this->assertTrue(ServiceTicketWorkflow::canCancelFromStatus(ServiceTicketWorkflow::STATUS_READY_TO_START));
        $this->assertTrue(ServiceTicketWorkflow::canCancelFromStatus(ServiceTicketWorkflow::STATUS_IN_PROGRESS));
        $this->assertFalse(ServiceTicketWorkflow::canCancelFromStatus(ServiceTicketWorkflow::STATUS_QA_PENDING));
        $this->assertFalse(ServiceTicketWorkflow::canCancelFromStatus(ServiceTicketWorkflow::STATUS_CLOSED));
    }
}

