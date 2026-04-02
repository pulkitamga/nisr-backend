<?php

namespace Tests\Feature;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Models\Admin;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Support\AdminPermissionRegistry;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WarrantyAdminListShellTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        $database = (string) ($_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '');
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

        $this->mock(AdminNotificationRepositoryInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('notifyRecipients')->andReturn(new Collection());
            $mock->shouldReceive('getForEmployee')->andReturn(new Collection());
            $mock->shouldReceive('getForUser')->andReturn(new Collection());
        });

        $this->mock(TranslationRepositoryInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('add')->andReturn(true);
        });

        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    protected function connectionsToTransact(): array
    {
        return ['mysql'];
    }

    public function test_claim_list_renders_crm_toolbar_and_export_uses_active_filters(): void
    {
        $this->signInWarrantyAdmin([
            'crm_section.warranty_claim_list',
            'crm_section.warranty_claim_export',
        ]);

        $customer = $this->createWarrantyCustomer();
        $warranty = $this->createWarranty([
            'serial_number' => 'WR-CLAIM-' . uniqid(),
            'final_user_id' => $customer->id,
            'activated_by_name' => 'Claim Owner',
            'status' => 'active',
        ]);

        $newClaimNumber = 'CLM-NEW-' . uniqid();
        $closedClaimNumber = 'CLM-CLOSED-' . uniqid();

        WarrantyClaim::query()->create([
            'warranty_id' => $warranty->id,
            'serial_number' => $warranty->serial_number,
            'claim_number' => $newClaimNumber,
            'status' => 'new',
            'submitted_at' => now()->subDay(),
            'resolution_due' => now()->addDays(5),
        ]);

        WarrantyClaim::query()->create([
            'warranty_id' => $warranty->id,
            'serial_number' => 'WR-CLAIM-CLOSED-' . uniqid(),
            'claim_number' => $closedClaimNumber,
            'status' => 'closed',
            'submitted_at' => now()->subDays(2),
            'resolution_due' => now()->addDays(3),
        ]);

        $response = $this->get(route('admin.warranty.claim.all', [
            'status' => 'new',
            'searchValue' => $newClaimNumber,
            'choose_first' => 1,
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('id="warranty-claim-toolbar"', $html);
        $this->assertStringContainsString('data-base-url="' . route('admin.warranty.claim.export') . '"', $html);
        $this->assertStringContainsString(translate('Rows_to_show'), $html);
        $this->assertStringContainsString('crm-row-actions', $html);
        $this->assertStringContainsString('claim-row-actions-', $html);
        $this->assertStringContainsString('aria-label="' . e(translate('More actions')) . '"', $html);
        $this->assertStringContainsString('assets/back-end/js/admin/warranty-claims.js', $html);
        $this->assertStringNotContainsString('const claimListI18n =', $html);

        $export = $this->get(route('admin.warranty.claim.export', [
            'status' => 'new',
            'searchValue' => $newClaimNumber,
            'choose_first' => 1,
        ]));

        $export->assertOk();
        $csv = $export->streamedContent();

        $this->assertStringContainsString($newClaimNumber, $csv);
        $this->assertStringNotContainsString($closedClaimNumber, $csv);
    }

    public function test_warranty_dashboard_toolbar_filters_recent_claims_server_side(): void
    {
        $this->signInWarrantyAdmin([
            'warranty_section.warranty_dashboard',
        ]);

        $customer = $this->createWarrantyCustomer();
        $warranty = $this->createWarranty([
            'serial_number' => 'WR-DASH-' . uniqid(),
            'final_user_id' => $customer->id,
            'activated_by_name' => 'Dashboard Owner',
            'status' => 'active',
        ]);

        $visibleClaimNumber = 'CLM-DASH-VISIBLE-' . uniqid();
        $hiddenClaimNumber = 'CLM-DASH-HIDDEN-' . uniqid();

        WarrantyClaim::query()->create([
            'warranty_id' => $warranty->id,
            'serial_number' => $warranty->serial_number,
            'claim_number' => $visibleClaimNumber,
            'status' => 'new',
            'submitted_at' => now()->subHour(),
            'resolution_due' => now()->addDays(3),
        ]);

        WarrantyClaim::query()->create([
            'warranty_id' => $warranty->id,
            'serial_number' => 'WR-DASH-HIDDEN-' . uniqid(),
            'claim_number' => $hiddenClaimNumber,
            'status' => 'closed',
            'submitted_at' => now()->subHours(2),
            'resolution_due' => now()->addDays(2),
        ]);

        $response = $this->get(route('admin.warranty.dashboard', [
            'status' => 'new',
            'searchValue' => $visibleClaimNumber,
            'choose_first' => 1,
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('id="warranty-dashboard-toolbar"', $html);
        $this->assertStringContainsString(route('admin.warranty.claim.all'), $html);
        $this->assertStringContainsString($visibleClaimNumber, $html);
        $this->assertStringNotContainsString($hiddenClaimNumber, $html);
        $this->assertStringNotContainsString("$('.form-control').on('keyup'", $html);
    }

    public function test_claim_view_uses_primary_action_and_single_close_overflow_path(): void
    {
        $this->signInWarrantyAdmin([
            'crm_section.warranty_claim_view',
        ]);

        $customer = $this->createWarrantyCustomer();
        $warranty = $this->createWarranty([
            'serial_number' => 'WR-VIEW-' . uniqid(),
            'final_user_id' => $customer->id,
            'activated_by_name' => 'Claim Detail Owner',
            'status' => 'active',
        ]);

        $claim = WarrantyClaim::query()->create([
            'warranty_id' => $warranty->id,
            'serial_number' => $warranty->serial_number,
            'claim_number' => 'CLM-VIEW-' . uniqid(),
            'status' => 'new',
            'submitted_at' => now()->subHour(),
            'resolution_due' => now()->addDays(5),
            'description' => 'Battery issue',
        ]);

        $response = $this->get(route('admin.warranty.claim.view', $claim->id));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('crm-row-actions', $html);
        $this->assertStringContainsString('claim-detail-actions-' . $claim->id, $html);
        $this->assertStringContainsString(translate('Decide'), $html);
        $this->assertStringContainsString('aria-label="' . e(translate('More actions')) . '"', $html);
        $this->assertSame(1, substr_count($html, 'data-target="#closeModal"'));
        $this->assertStringContainsString('assets/back-end/js/admin/warranty-claims.js', $html);
        $this->assertStringNotContainsString('const claimWorkflowI18n =', $html);
    }

    public function test_activation_list_renders_crm_toolbar_and_export_uses_method_filter(): void
    {
        $this->signInWarrantyAdmin([
            'warranty_section.warranty_activation_list',
            'warranty_section.warranty_manual_activation',
        ]);

        $manualSerial = 'ACT-MANUAL-' . uniqid();
        $orderSerial = 'ACT-ORDER-' . uniqid();

        $this->createWarranty([
            'serial_number' => $manualSerial,
            'status' => 'active',
            'activation_method' => 'admin_manual',
            'activated_by_name' => 'Manual Customer',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(11),
        ]);

        $this->createWarranty([
            'serial_number' => $orderSerial,
            'status' => 'active',
            'activation_method' => 'order_activation',
            'activated_by_name' => 'Order Customer',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(11),
        ]);

        $response = $this->get(route('admin.warranty.activation.list', [
            'method' => 'admin_manual',
            'searchValue' => $manualSerial,
            'choose_first' => 1,
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('id="warranty-activation-toolbar"', $html);
        $this->assertStringContainsString(route('admin.warranty.activation.manual.view'), $html);
        $this->assertStringContainsString('data-base-url="' . route('admin.warranty.activation.export') . '"', $html);

        $export = $this->get(route('admin.warranty.activation.export', [
            'method' => 'admin_manual',
            'searchValue' => $manualSerial,
            'choose_first' => 1,
        ]));

        $export->assertOk();
        $csv = $export->streamedContent();

        $this->assertStringContainsString($manualSerial, $csv);
        $this->assertStringNotContainsString($orderSerial, $csv);
    }

    public function test_blacklist_and_import_pages_render_crm_toolbar_shell(): void
    {
        $this->signInWarrantyAdmin([
            'warranty_section.warranty_blacklist',
            'warranty_section.warranty_import',
            'warranty_section.warranty_import_history',
        ]);

        $blacklistSerial = 'BL-' . uniqid();

        DB::table('blacklists')->insert([
            'serial_number' => $blacklistSerial,
            'reason' => 'Duplicate claim',
            'blacklisted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $importListSerial = 'IMPORT-LIST-' . uniqid();
        $this->createWarranty([
            'serial_number' => $importListSerial,
            'status' => 'preactivated',
            'created_at' => now()->startOfDay(),
            'updated_at' => now()->startOfDay(),
        ]);

        $blacklistResponse = $this->get(route('admin.warranty.blacklist', [
            'searchValue' => $blacklistSerial,
            'choose_first' => 1,
        ]));
        $blacklistResponse->assertOk();
        $blacklistHtml = $blacklistResponse->getContent();
        $this->assertStringContainsString('id="warranty-blacklist-toolbar"', $blacklistHtml);
        $this->assertStringContainsString('data-base-url="' . route('admin.warranty.blacklist.export') . '"', $blacklistHtml);
        $this->assertStringContainsString(route('admin.warranty.blacklist.add'), $blacklistHtml);

        $importResponse = $this->get(route('admin.warranty.import', [
            'searchValue' => now()->toDateString(),
            'choose_first' => 1,
        ]));
        $importResponse->assertOk();
        $importHtml = $importResponse->getContent();
        $this->assertStringContainsString('id="warranty-import-history-toolbar"', $importHtml);
        $this->assertStringContainsString('data-base-url="' . route('admin.warranty.import-history.export') . '"', $importHtml);

        $historyResponse = $this->get(route('admin.warranty.import-history', [
            'searchValue' => now()->toDateString(),
            'choose_first' => 1,
        ]));
        $historyResponse->assertOk();
        $historyHtml = $historyResponse->getContent();
        $this->assertStringContainsString('id="warranty-import-history-toolbar"', $historyHtml);
        $this->assertStringContainsString(route('admin.warranty.import'), $historyHtml);
    }

    public function test_import_history_details_uses_server_side_search_without_live_row_filter(): void
    {
        $this->signInWarrantyAdmin([
            'warranty_section.warranty_import_history',
        ]);

        $importDate = now()->subDay()->startOfDay();
        $visibleSerial = 'DETAIL-SERIAL-A-' . uniqid();
        $hiddenSerial = 'DETAIL-SERIAL-B-' . uniqid();

        $this->createWarranty([
            'serial_number' => $visibleSerial,
            'status' => 'preactivated',
            'created_at' => $importDate,
            'updated_at' => $importDate,
        ]);

        $this->createWarranty([
            'serial_number' => $hiddenSerial,
            'status' => 'preactivated',
            'created_at' => $importDate,
            'updated_at' => $importDate,
        ]);

        $response = $this->get(route('admin.warranty.history-details', [
            'date' => $importDate->toDateString(),
            'searchValue' => $visibleSerial,
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString($visibleSerial, $html);
        $this->assertStringNotContainsString($hiddenSerial, $html);
        $this->assertStringNotContainsString("$('input[name=\"searchValue\"]').on('input'", $html);
    }

    public function test_import_history_export_uses_search_filter(): void
    {
        $this->signInWarrantyAdmin([
            'warranty_section.warranty_import_history',
        ]);

        $firstDate = now()->subDays(1)->startOfDay();
        $secondDate = now()->subDays(3)->startOfDay();

        $importSerialA = 'IMPORT-A-' . uniqid();
        $importSerialB = 'IMPORT-B-' . uniqid();
        $this->createWarranty([
            'serial_number' => $importSerialA,
            'status' => 'preactivated',
            'created_at' => $firstDate,
            'updated_at' => $firstDate,
        ]);

        $this->createWarranty([
            'serial_number' => $importSerialB,
            'status' => 'preactivated',
            'created_at' => $secondDate,
            'updated_at' => $secondDate,
        ]);

        $response = $this->get(route('admin.warranty.import-history.export', [
            'searchValue' => $firstDate->toDateString(),
            'choose_first' => 1,
        ]));

        $response->assertOk();
        $csv = $response->streamedContent();

        $this->assertStringContainsString($firstDate->toDateString(), $csv);
        $this->assertStringNotContainsString($secondDate->toDateString(), $csv);
    }

    public function test_serial_transaction_list_renders_crm_toolbar_and_export_uses_filters(): void
    {
        $this->signInWarrantyAdmin([
            'warranty_section.warranty_serial_transaction',
        ]);

        $fromBranchId = $this->createBranch('Origin Branch');
        $toBranchId = $this->createBranch('Destination Branch');

        DB::table('serial_transfer_histories')->insert([
            [
                'serial_number' => 'SERIAL-TX-001',
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'transfer_type' => 'branch_to_branch',
                'transferred_at' => now()->subDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'serial_number' => 'SERIAL-TX-OLD',
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'transfer_type' => 'branch_to_branch',
                'transferred_at' => now()->subMonths(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->get(route('admin.warranty.serial-transaction.list', [
            'from_branch' => $fromBranchId,
            'transfer_type' => 'branch_to_branch',
            'date_type' => 'this_month',
            'search' => 'SERIAL-TX',
            'choose_first' => 1,
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('id="warranty-transaction-toolbar"', $html);
        $this->assertStringContainsString('data-base-url="' . route('admin.warranty.serial-transaction.export') . '"', $html);
        $this->assertStringContainsString(translate('search_by_serial_no'), $html);
        $this->assertStringContainsString('assets/back-end/js/admin/warranty-transactions.js', $html);
        $this->assertStringContainsString('assets/back-end/css/warranty-transactions.css', $html);
        $this->assertStringNotContainsString("$(document).on('click', '.view-history-btn'", $html);

        $export = $this->get(route('admin.warranty.serial-transaction.export', [
            'from_branch' => $fromBranchId,
            'transfer_type' => 'branch_to_branch',
            'date_type' => 'this_month',
            'search' => 'SERIAL-TX',
            'choose_first' => 1,
        ]));

        $export->assertOk();
        $csv = $export->streamedContent();

        $this->assertStringContainsString('SERIAL-TX-001', $csv);
        $this->assertStringNotContainsString('SERIAL-TX-OLD', $csv);

        $modalHtml = app(\App\Http\Controllers\Admin\WarrantyTransferController::class)->historyModal('SERIAL-TX-001');
        $this->assertStringContainsString(translate('Branch → Branch'), $modalHtml);
        $this->assertStringNotContainsString("ucwords(str_replace('_', ' ', \$h->transfer_type))", $modalHtml);
    }

    private function signInWarrantyAdmin(array $permissions): Admin
    {
        $guard = AdminPermissionRegistry::guard();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, $guard);
        }

        $role = Role::findOrCreate('Warranty Test Role ' . uniqid(), $guard);
        $role->syncPermissions($permissions);

        $admin = Admin::query()->create([
            'name' => 'Warranty Test Admin',
            'phone' => '1' . random_int(100000000, 999999999),
            'email' => 'warranty-admin-' . uniqid() . '@example.com',
            'password' => bcrypt('Password@123'),
            'status' => 1,
        ]);
        $admin->assignRole($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    private function createWarrantyCustomer(): User
    {
        return User::query()->create([
            'name' => 'Warranty Customer',
            'f_name' => 'Warranty',
            'l_name' => 'Customer',
            'phone' => '2012' . random_int(1000000, 9999999),
            'image' => 'def.png',
            'email' => 'warranty-customer-' . uniqid() . '@example.com',
            'user_type' => 0,
            'password' => bcrypt('Password@123'),
            'is_active' => 1,
            'app_language' => 'en',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createWarranty(array $overrides = []): Warranty
    {
        return Warranty::query()->create(array_merge([
            'serial_number' => 'WR-' . uniqid(),
            'status' => 'preactivated',
            'warranty_months' => 12,
            'activation_method' => 'admin_manual',
            'activated_by_name' => 'Warranty User',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(11),
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    private function createBranch(string $name): int
    {
        return (int) DB::table('branches')->insertGetId([
            'branch_name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
