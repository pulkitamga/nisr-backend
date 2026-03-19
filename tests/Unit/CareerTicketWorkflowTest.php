<?php

namespace Tests\Unit;

use App\Support\CareerTicketWorkflow;
use PHPUnit\Framework\TestCase;

class CareerTicketWorkflowTest extends TestCase
{
    public function test_career_status_constants_are_stable(): void
    {
        $this->assertSame(3, CareerTicketWorkflow::STATUS_MASTER_ID);
        $this->assertSame(27, CareerTicketWorkflow::STATUS_NEW);
        $this->assertSame(28, CareerTicketWorkflow::STATUS_OPEN);
        $this->assertSame(29, CareerTicketWorkflow::STATUS_ASSIGNED);
        $this->assertSame(30, CareerTicketWorkflow::STATUS_SCREENING);
        $this->assertSame(31, CareerTicketWorkflow::STATUS_INTERVIEW);
        $this->assertSame(32, CareerTicketWorkflow::STATUS_OFFER);
        $this->assertSame(33, CareerTicketWorkflow::STATUS_HIRED);
        $this->assertSame(34, CareerTicketWorkflow::STATUS_REJECTED);
        $this->assertSame(35, CareerTicketWorkflow::STATUS_CLOSED);
    }

    public function test_next_status_map_matches_career_progression(): void
    {
        $this->assertSame([
            CareerTicketWorkflow::STATUS_NEW => CareerTicketWorkflow::STATUS_ASSIGNED,
            CareerTicketWorkflow::STATUS_OPEN => CareerTicketWorkflow::STATUS_ASSIGNED,
            CareerTicketWorkflow::STATUS_ASSIGNED => CareerTicketWorkflow::STATUS_SCREENING,
            CareerTicketWorkflow::STATUS_SCREENING => CareerTicketWorkflow::STATUS_INTERVIEW,
            CareerTicketWorkflow::STATUS_INTERVIEW => CareerTicketWorkflow::STATUS_OFFER,
            CareerTicketWorkflow::STATUS_OFFER => CareerTicketWorkflow::STATUS_CLOSED,
        ], CareerTicketWorkflow::nextStatusMap());
    }
}
