<?php

namespace Tests\Unit;

use App\Exports\CareerTicketExport;
use Illuminate\Http\Request;
use Tests\TestCase;

class CareerTicketExportTest extends TestCase
{
    public function test_it_localizes_career_ticket_headings_and_title(): void
    {
        app()->setLocale('en');

        $export = new CareerTicketExport(new Request());

        $this->assertSame('Career Tickets', $export->titleLabel());
        $this->assertSame('Candidate name', $export->headings()[2]);
        $this->assertSame('Talent pool consent', $export->headings()[8]);
    }
}
