<?php

namespace Tests\Unit;

use App\Exports\SupportTicketExport;
use Illuminate\Http\Request;
use Tests\TestCase;

class SupportTicketExportTest extends TestCase
{
    public function test_it_localizes_complaint_ticket_title_and_headings(): void
    {
        app()->setLocale('en');

        $export = new SupportTicketExport(new Request(), 'complaint');

        $this->assertSame('Complaint ticket', $export->titleLabel());
        $this->assertSame('Subject', $export->headings()[1]);
        $this->assertSame('Assigned employee', $export->headings()[8]);
    }

    public function test_it_includes_service_column_for_service_ticket_exports(): void
    {
        app()->setLocale('en');

        $export = new SupportTicketExport(new Request(), 'service');

        $this->assertContains('Service', $export->headings());
        $this->assertSame('Service ticket', $export->titleLabel());
    }
}
