<?php

namespace Tests\Unit;

use App\Exports\CustomerReviewListExport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class CustomerReviewListExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('translations');
        Schema::dropIfExists('business_settings');

        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->text('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
            $table->integer('item_index')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('business_settings');

        parent::tearDown();
    }

    public function test_admin_export_hides_store_column_in_single_vendor_mode(): void
    {
        $this->seedBusinessMode('single');

        $export = new CustomerReviewListExport($this->exportData());
        $styles = $export->styles((new Spreadsheet())->getActiveSheet());
        $html = $export->view()->render();

        $this->assertSame(['A' => 15, 'B' => 30, 'F' => 30], $export->columnWidths());
        $this->assertArrayHasKey('A1:F3', $styles);
        $this->assertStringNotContainsString((string) translate('store_Name'), $html);
    }

    public function test_admin_export_keeps_store_column_in_multi_vendor_mode(): void
    {
        $this->seedBusinessMode('multi');

        $export = new CustomerReviewListExport($this->exportData());
        $styles = $export->styles((new Spreadsheet())->getActiveSheet());
        $html = $export->view()->render();

        $this->assertSame(['A' => 15, 'B' => 30, 'G' => 30], $export->columnWidths());
        $this->assertArrayHasKey('A1:G3', $styles);
        $this->assertStringContainsString((string) translate('store_Name'), $html);
    }

    private function seedBusinessMode(string $mode): void
    {
        DB::table('business_settings')->insert([
            [
                'type' => 'business_mode',
                'value' => $mode,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'pnc_language',
                'value' => json_encode(['en', 'ar']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function exportData(): array
    {
        return [
            'data-from' => 'admin',
            'reviews' => collect(),
            'product_name' => 'all_products',
            'customer_name' => 'all_customers',
            'from' => null,
            'to' => null,
            'status' => null,
            'key' => '',
        ];
    }
}
