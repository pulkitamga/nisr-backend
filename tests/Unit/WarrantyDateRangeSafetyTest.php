<?php

namespace Tests\Unit;

use App\Exports\WarrantyClaimsExport;
use App\Http\Controllers\Admin\WarrantyClaimChartController;
use App\Http\Controllers\Admin\WarrantyController;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

class WarrantyDateRangeSafetyTest extends TestCase
{
    public function test_admin_warranty_controller_falls_back_for_invalid_custom_dates(): void
    {
        $request = Request::create('/admin/warranty/report/analytics', 'GET', [
            'date_type' => 'custom_date',
            'from' => 'not-a-date',
            'to' => 'still-not-a-date',
        ]);

        [$fromDate, $toDate] = $this->invokePrivateMethod(new WarrantyController(), 'resolveAnalyticsDateRange', [$request]);

        $this->assertTrue($fromDate->lte($toDate));
        $this->assertSame(now()->subDays(29)->toDateString(), $fromDate->toDateString());
        $this->assertSame(now()->toDateString(), $toDate->toDateString());
    }

    public function test_claim_chart_controller_falls_back_for_invalid_custom_dates(): void
    {
        $request = Request::create('/admin/warranty/claim-chart', 'GET', [
            'date_type' => 'custom_date',
            'from' => 'bad-start',
            'to' => 'bad-end',
        ]);

        $dates = $this->invokePrivateMethod(new WarrantyClaimChartController(), 'parseDateRange', [$request]);

        $this->assertTrue($dates['start']->lte($dates['end']));
        $this->assertSame(now()->subDays(29)->toDateString(), $dates['start']->toDateString());
        $this->assertSame(now()->toDateString(), $dates['end']->toDateString());
    }

    public function test_warranty_claims_export_falls_back_for_invalid_custom_dates(): void
    {
        $export = new WarrantyClaimsExport(
            Request::create('/admin/warranty/claim-export-excel', 'GET', [
                'date_type' => 'custom_date',
                'from' => 'invalid',
                'to' => 'also-invalid',
            ]),
            'en'
        );

        [$fromDate, $toDate] = $this->invokePrivateMethod($export, 'resolveDateRange');

        $this->assertTrue($fromDate->lte($toDate));
        $this->assertSame(now()->subDays(29)->toDateString(), $fromDate->toDateString());
        $this->assertSame(now()->toDateString(), $toDate->toDateString());
    }

    private function invokePrivateMethod(object $instance, string $methodName, array $arguments = []): mixed
    {
        $method = new ReflectionMethod($instance, $methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($instance, $arguments);
    }
}
