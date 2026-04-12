<?php

namespace Tests\Unit;

use App\Exports\WarrantyClaimListExport;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WholeSalerBusiness;
use Carbon\Carbon;
use Tests\TestCase;

class WarrantyClaimListExportTest extends TestCase
{
    public function test_it_maps_claim_rows_for_english_exports(): void
    {
        $claim = $this->makeClaimFixture();
        $export = new WarrantyClaimListExport(
            claims: collect([$claim]),
            locale: 'en',
            isRtl: false,
            title: 'Claims list',
            dateRangeLabel: '2026-04-01 - 2026-04-12',
            filterSummary: 'Date range: all | Status: all | Search: -',
            exportedAt: Carbon::parse('2026-04-12 13:14:25')
        );

        $this->assertSame('A6', $export->startCell());
        $this->assertSame('Distributor', $export->headings()[6]);
        $this->assertSame('SLA result', $export->headings()[14]);

        $row = $export->map($claim);

        $this->assertSame(1, $row[0]);
        $this->assertSame('CLM-001', $row[1]);
        $this->assertSame('SERIAL-001', $row[2]);
        $this->assertSame('Resolved', $row[3]);
        $this->assertSame('John Doe', $row[4]);
        $this->assertSame('Brake Pad', $row[5]);
        $this->assertSame('Distributor One', $row[6]);
        $this->assertSame('Alexandria', $row[7]);
        $this->assertSame('Mobile app', $row[8]);
        $this->assertSame('2026-04-10 10:30', $row[9]);
        $this->assertSame('2026-04-13 18:00', $row[10]);
        $this->assertSame('2026-04-12 09:00', $row[11]);
        $this->assertSame(2, $row[12]);
        $this->assertSame('Tech User', $row[13]);
        $this->assertSame('Within SLA', $row[14]);
    }

    public function test_it_localizes_headings_for_arabic_exports(): void
    {
        $claim = $this->makeClaimFixture();
        $export = new WarrantyClaimListExport(
            claims: collect([$claim]),
            locale: 'ar',
            isRtl: true,
            title: 'قائمة المطالبات',
            dateRangeLabel: '2026-04-01 - 2026-04-12',
            filterSummary: 'نطاق التاريخ: الكل | الحالة: الكل | بحث: -',
            exportedAt: Carbon::parse('2026-04-12 13:14:25')
        );

        $this->assertSame('الموزع', $export->headings()[6]);
        $this->assertSame('نتيجة اتفاقية مستوى الخدمة', $export->headings()[14]);
    }

    private function makeClaimFixture(): WarrantyClaim
    {
        $customer = new User([
            'f_name' => 'John',
            'l_name' => 'Doe',
            'name' => 'John Doe',
        ]);

        $technician = new User([
            'name' => 'Tech User',
        ]);

        $warranty = new Warranty([
            'activation_method' => 'mobile_app',
            'activated_by_name' => 'Fallback Customer',
        ]);
        $warranty->setRelation('user', $customer);
        $warranty->setRelation('product', new Product([
            'name' => 'Brake Pad',
        ]));
        $warranty->setRelation('distributor', new WholeSalerBusiness([
            'company_name' => 'Distributor One',
        ]));

        $claim = new WarrantyClaim([
            'claim_number' => 'CLM-001',
            'serial_number' => 'SERIAL-001',
            'status' => 'resolved',
            'submitted_at' => Carbon::parse('2026-04-10 10:30:00'),
            'resolution_due' => Carbon::parse('2026-04-13 18:00:00'),
            'resolved_at' => Carbon::parse('2026-04-12 09:00:00'),
            'reopen_count' => 2,
        ]);
        $claim->setRelation('warranty', $warranty);
        $claim->setRelation('branch', new Branch([
            'branch_name' => 'Alexandria',
        ]));
        $claim->setRelation('technician', $technician);

        return $claim;
    }
}
