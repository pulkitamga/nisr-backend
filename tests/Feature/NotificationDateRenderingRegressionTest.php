<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class NotificationDateRenderingRegressionTest extends TestCase
{
    public function test_notification_date_snippet_renders_without_unqualified_carbon_errors(): void
    {
        $html = Blade::render(
            <<<'BLADE'
@php($createdAt = \Carbon\Carbon::parse($created_at))
{!! $createdAt->diffInDays(\Carbon\Carbon::now()) < 7
    ? formatDateTimeForDisplay($createdAt, 'D h:i A')
    : formatDateTimeForDisplay($createdAt, 'd M Y h:i A') !!}
BLADE,
            ['created_at' => '2026-04-10 10:30:00']
        );

        $this->assertNotSame('', trim($html));
    }
}
