<?php

namespace Tests\Unit;

use App\Exports\LeadsExport;
use App\Models\Admin;
use App\Models\Departments;
use App\Models\InboxMessage;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LeadsExportTest extends TestCase
{
    public function test_it_localizes_lead_export_headings_and_preserves_phone_text(): void
    {
        app()->setLocale('en');

        $lead = new Lead([
            'party_type' => 'company',
            'priority' => 'high',
            'status' => 'new',
            'created_at' => now(),
        ]);
        $lead->setRelation('inboxMessages', new Collection([
            new InboxMessage([
                'subject' => 'New wholesale inquiry',
                'sender_name' => 'Ahmed Ali',
                'sender_email' => 'ahmed@example.com',
                'sender_phone' => '+201234567890',
            ]),
        ]));
        $lead->setRelation('owner', new Admin(['name' => 'Owner User']));
        $lead->setRelation('department', new Departments(['name' => 'Sales']));
        $lead->setRelation('employee', new Admin(['name' => 'Assigned User']));

        $export = new LeadsExport(collect([$lead]), new Request([
            'searchValue' => 'Ahmed',
            'status' => 'new',
        ]));

        $this->assertSame('Lead List', $export->titleLabel());
        $this->assertSame('Party Name', $export->headings()[3]);
        $this->assertSame('="+201234567890"', $export->rows()[0][5]);
    }
}
