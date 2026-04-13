<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\WholeSalerBusiness;
use App\Services\LeadConvertService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeadConvertServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        $database = (string)($_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '');
        if ($database === '' || $database === ':memory:') {
            $database = basename(getcwd());
        }

        putenv('DB_CONNECTION=mysql');
        putenv("DB_DATABASE={$database}");
        $_SERVER['DB_CONNECTION'] = 'mysql';
        $_ENV['DB_CONNECTION'] = 'mysql';
        $_SERVER['DB_DATABASE'] = $database;
        $_ENV['DB_DATABASE'] = $database;

        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => $database,
        ]);
    }

    protected function connectionsToTransact(): array
    {
        return ['mysql'];
    }

    public function test_it_allows_multiple_open_deals_for_wholesale_purchase_request_leads(): void
    {
        $admin = $this->createAdmin();
        $business = $this->createWholesalerBusiness();

        $firstLead = $this->createWholesalePurchaseLead($business->id, $admin->id, 1001);
        $secondLead = $this->createWholesalePurchaseLead($business->id, $admin->id, 1002);

        $service = app(LeadConvertService::class);

        $firstDeal = $service->convert($firstLead, [
            'party_type' => 'company',
            'party_id' => $business->id,
            'owner_id' => $admin->id,
            'employee_id' => $admin->id,
            'value' => 120.50,
        ]);

        $secondDeal = $service->convert($secondLead, [
            'party_type' => 'company',
            'party_id' => $business->id,
            'owner_id' => $admin->id,
            'employee_id' => $admin->id,
            'value' => 240.75,
        ]);

        $this->assertNotSame($firstDeal->id, $secondDeal->id);
        $this->assertSame(2, Deal::query()
            ->where('related_party_type', 'company')
            ->where('related_party_id', $business->id)
            ->where('status', 'open')
            ->count());
    }

    public function test_it_still_blocks_multiple_open_deals_for_non_purchase_request_company_leads(): void
    {
        $admin = $this->createAdmin();
        $business = $this->createWholesalerBusiness();

        $firstLead = $this->createCompanyLead($business->id, $admin->id);
        $secondLead = $this->createCompanyLead($business->id, $admin->id);

        $service = app(LeadConvertService::class);

        $service->convert($firstLead, [
            'party_type' => 'company',
            'party_id' => $business->id,
            'owner_id' => $admin->id,
            'employee_id' => $admin->id,
            'value' => 90,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('still open');

        $service->convert($secondLead, [
            'party_type' => 'company',
            'party_id' => $business->id,
            'owner_id' => $admin->id,
            'employee_id' => $admin->id,
            'value' => 110,
        ]);
    }

    private function createAdmin(): Admin
    {
        $admin = Admin::query()->create([
            'name' => 'Lead Convert Admin',
            'phone' => '1555000000',
            'email' => 'lead-convert-admin-' . uniqid() . '@example.com',
            'password' => bcrypt('Password@123'),
            'status' => 1,
        ]);

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    private function createWholesalerBusiness(): WholeSalerBusiness
    {
        $now = now();
        $userId = DB::table('users')->insertGetId([
            'name' => 'Wholesale Contact',
            'f_name' => 'Wholesale',
            'l_name' => 'Contact',
            'phone' => '2011' . random_int(1000000, 9999999),
            'image' => 'def.png',
            'email' => 'lead-convert-wholesale-' . uniqid() . '@example.com',
            'user_type' => 1,
            'password' => bcrypt('Password@123'),
            'is_active' => 1,
            'app_language' => 'en',
            'wholesaler_status' => 1,
            'wholesaler_discount' => 10.00,
            'tier' => 'gold',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $businessId = DB::table('wholesaler_businesses')->insertGetId([
            'wholesaler_id' => $userId,
            'company_name' => 'Lead Convert Wholesale ' . uniqid(),
            'trade_name' => 'Trade ' . uniqid(),
            'registration_number' => 'REG-' . uniqid(),
            'tax_id' => 'TAX-' . uniqid(),
            'vat_number' => 'VAT-' . uniqid(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return WholeSalerBusiness::query()->findOrFail($businessId);
    }

    private function createWholesalePurchaseLead(int $businessId, int $adminId, int $poId): Lead
    {
        return $this->createLead($businessId, $adminId, $poId);
    }

    private function createCompanyLead(int $businessId, int $adminId): Lead
    {
        return $this->createLead($businessId, $adminId, null);
    }

    private function createLead(int $businessId, int $adminId, ?int $poId): Lead
    {
        $now = now();

        return Lead::query()->create([
            'party_type' => 'wholesale',
            'po_id' => $poId,
            'company_id' => $businessId,
            'department_id' => 1,
            'employee_id' => $adminId,
            'owner_id' => $adminId,
            'source_id' => $poId ?? random_int(2000, 9000),
            'status' => 'new',
            'priority' => 'high',
            'response_due' => $now->copy()->addDay(),
            'resolution_due' => $now->copy()->addDays(3),
        ]);
    }
}
