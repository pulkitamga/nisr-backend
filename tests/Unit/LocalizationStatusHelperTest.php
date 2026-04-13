<?php

namespace Tests\Unit;

use Tests\TestCase;

use function App\Utils\support_ticket_status_label;
use function App\Utils\warranty_claim_status_label;

class LocalizationStatusHelperTest extends TestCase
{
    public function test_support_ticket_status_helper_returns_arabic_translation(): void
    {
        app()->setLocale('ar');

        $this->assertSame('مغلق', support_ticket_status_label('Closed'));
        $this->assertSame('قيد التنفيذ', support_ticket_status_label('in_progress'));
        $this->assertSame('غير معروف', support_ticket_status_label(null));
    }

    public function test_warranty_claim_status_helper_returns_arabic_translation(): void
    {
        app()->setLocale('ar');

        $this->assertSame('بانتظار الاستبدال', warranty_claim_status_label('replacement_pending'));
        $this->assertSame('قيد المراجعة', warranty_claim_status_label('triage pending'));
        $this->assertSame('غير معروف', warranty_claim_status_label(''));
    }
}
