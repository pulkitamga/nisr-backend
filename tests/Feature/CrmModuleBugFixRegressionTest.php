<?php

namespace Tests\Feature;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Http\Controllers\Admin\Crm\DealController;
use App\Http\Controllers\Admin\Crm\InboxMessageController;
use App\Http\Controllers\Admin\Crm\LeadController;
use App\Models\Admin;
use App\Models\Deal;
use App\Models\Departments;
use App\Models\Escalation;
use App\Models\InboxMessage;
use App\Models\Lead;
use App\Models\User;
use App\Services\Crm\EscalationService;
use App\Services\SlaService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrmModuleBugFixRegressionTest extends TestCase
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

    public function test_lead_search_treats_like_wildcards_as_literals(): void
    {
        $this->actingAs($this->createAdmin('lead-search-admin'), 'admin');

        $plainUser = $this->createCustomer('regular-lead', 'Regular User');
        $percentUser = $this->createCustomer('percent-lead', 'Percent % User');

        $plainLead = $this->createLead([
            'contact_id' => $plainUser->id,
        ]);
        $percentLead = $this->createLead([
            'contact_id' => $percentUser->id,
        ]);

        $view = $this->makeLeadController()->getListView(
            Request::create('/admin/crm/lead', 'GET', [
                'searchValue' => '%',
                'status' => 'all',
                'choose_first' => 50,
            ])
        );

        $leadIds = collect($view->getData()['lead']->items())->pluck('id')->all();

        $this->assertNotContains($plainLead->id, $leadIds);
        $this->assertSame([$percentLead->id], $leadIds);
    }

    public function test_inbox_search_treats_like_wildcards_as_literals(): void
    {
        $this->actingAs($this->createAdmin('inbox-search-admin'), 'admin');

        $plainUser = $this->createCustomer('regular-inbox', 'Regular User');
        $percentUser = $this->createCustomer('percent-inbox', 'Percent % User');

        $plainMessage = $this->createInboxMessage([
            'contact_id' => $plainUser->id,
            'sender_name' => 'Plain Sender',
            'sender_email' => 'plain-sender@example.com',
        ]);
        $percentMessage = $this->createInboxMessage([
            'contact_id' => $percentUser->id,
            'sender_name' => 'Linked Sender',
            'sender_email' => 'linked-sender@example.com',
        ]);

        $view = $this->makeInboxController()->getListView(
            Request::create('/admin/crm/inbox', 'GET', [
                'searchValue' => '%',
                'status' => 'all',
                'choose_first' => 50,
            ])
        );

        $messageIds = collect($view->getData()['messages']->items())->pluck('id')->all();

        $this->assertNotContains($plainMessage->id, $messageIds);
        $this->assertSame([$percentMessage->id], $messageIds);
    }

    public function test_unassigned_lead_disqualify_is_forbidden_for_non_superadmin(): void
    {
        $admin = $this->createAdmin('lead-auth-admin');
        $lead = $this->createLead([
            'department_id' => null,
            'employee_id' => null,
            'owner_id' => null,
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->makeLeadController()->disqualify(
            Request::create('/admin/crm/lead/disqualify', 'POST', [
                'message_id' => $lead->id,
            ])
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['status']);
    }

    public function test_deal_department_update_requires_manager_access(): void
    {
        $head = $this->createAdmin('deal-head');
        $department = $this->createDepartment('Managed Department', $head);
        $targetDepartment = $this->createDepartment('Target Department');
        $deal = $this->createDeal([
            'department_id' => $department->id,
        ]);
        $unauthorizedAdmin = $this->createAdmin('deal-outsider');

        $this->actingAs($unauthorizedAdmin, 'admin');

        $response = $this->makeDealController()->updateTicketDepartment(
            Request::create('/admin/crm/deals/wholesale/update-department', 'POST', [
                'ticket_id' => $deal->id,
                'department_id' => $targetDepartment->id,
                'priority' => 'high',
            ])
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame((int)$department->id, (int)$deal->fresh()->department_id);
    }

    public function test_assign_employee_rejects_cross_department_assignment(): void
    {
        $head = $this->createAdmin('assignment-head');
        $dealDepartment = $this->createDepartment('Deal Department', $head);
        $otherDepartment = $this->createDepartment('Other Department');
        $deal = $this->createDeal([
            'department_id' => $dealDepartment->id,
        ]);
        $employee = $this->createAdmin('other-department-employee', $otherDepartment->id);

        $this->actingAs($head->fresh(), 'admin');

        $response = $this->makeDealController()->assignEmployee(
            Request::create('/admin/crm/deals/wholesale/assign-employee', 'POST', [
                'ticket_id' => $deal->id,
                'employee_id' => $employee->id,
            ])
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('Employee must belong to the selected department.', $response->getData(true)['message']);
        $this->assertNull($deal->fresh()->employee_id);
    }

    public function test_lead_file_upload_rejects_spoofed_image_content(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin('lead-file-admin');
        $lead = $this->createLead();
        $file = $this->createSpoofedUpload('malicious.jpg', '<script>alert(1)</script>');

        $this->actingAs($admin, 'admin');

        try {
            $this->makeLeadController()->storeFile(
                Request::create('/admin/crm/lead/file', 'POST', [], [], ['file' => $file]),
                $lead->id
            );
            $this->fail('Expected the spoofed lead upload to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('file', $exception->errors());
        }
    }

    public function test_deal_file_upload_rejects_spoofed_image_content(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin('deal-file-admin');
        $deal = $this->createDeal();
        $file = $this->createSpoofedUpload('malicious.jpg', '<script>alert(1)</script>');

        $this->actingAs($admin, 'admin');

        try {
            $this->makeDealController()->storeFile(
                Request::create('/admin/crm/deal/file', 'POST', [], [], ['file' => $file]),
                $deal->id
            );
            $this->fail('Expected the spoofed deal upload to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('file', $exception->errors());
        }
    }

    public function test_resolving_escalation_preserves_original_escalated_timestamp(): void
    {
        $admin = $this->createAdmin('escalation-admin');
        $lead = $this->createLead([
            'escalated_at' => now()->subDay(),
            'escalated_by' => $admin->id,
        ]);
        $originalEscalatedAt = $lead->escalated_at?->toDateTimeString();

        $escalationId = DB::table('escalations')->insertGetId([
            'escalatable_id' => $lead->id,
            'escalatable_type' => Lead::class,
            'escalated_by' => $admin->id,
            'reason' => 'Regression test',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = new EscalationService($this->createMock(AdminNotificationRepositoryInterface::class));
        $service->transitionEscalationStatus(Escalation::query()->findOrFail($escalationId), $admin->id, 'resolved');

        $this->assertSame(
            $originalEscalatedAt,
            Lead::query()->findOrFail($lead->id)->escalated_at?->toDateTimeString()
        );
    }

    private function makeLeadController(): LeadController
    {
        $departmentRepo = $this->createMock(DepartmentRepositoryInterface::class);
        $departmentRepo->method('getListWhere')->willReturn(new EloquentCollection());

        $adminRepo = $this->createMock(AdminRepositoryInterface::class);
        $adminRepo->method('getEmployeeListWhere')->willReturn(collect());

        return new LeadController(
            $this->createMock(SupportTicketRepositoryInterface::class),
            $this->createMock(SupportTicketConvRepositoryInterface::class),
            $departmentRepo,
            $adminRepo,
            $this->createMock(AdminNotificationRepositoryInterface::class),
            $this->createMock(EscalationService::class),
        );
    }

    private function makeDealController(): DealController
    {
        $departmentRepo = $this->createMock(DepartmentRepositoryInterface::class);
        $departmentRepo->method('getListWhere')->willReturn(new EloquentCollection());

        $adminRepo = $this->createMock(AdminRepositoryInterface::class);
        $adminRepo->method('getEmployeeListWhere')->willReturn(collect());

        return new DealController(
            $this->createMock(SupportTicketRepositoryInterface::class),
            $this->createMock(SupportTicketConvRepositoryInterface::class),
            $departmentRepo,
            $adminRepo,
            $this->createMock(AdminNotificationRepositoryInterface::class),
            $this->createMock(EscalationService::class),
        );
    }

    private function makeInboxController(): InboxMessageController
    {
        $departmentRepo = $this->createMock(DepartmentRepositoryInterface::class);
        $departmentRepo->method('getListWhere')->willReturn(new EloquentCollection());

        $adminRepo = $this->createMock(AdminRepositoryInterface::class);
        $adminRepo->method('getEmployeeListWhere')->willReturn(collect());

        return new InboxMessageController(
            $this->createMock(SupportTicketRepositoryInterface::class),
            $this->createMock(SupportTicketConvRepositoryInterface::class),
            $departmentRepo,
            $adminRepo,
            $this->createMock(SlaService::class),
        );
    }

    private function createAdmin(string $prefix, ?int $departmentId = null): Admin
    {
        return Admin::query()->create([
            'name' => ucfirst(str_replace('-', ' ', $prefix)),
            'email' => $prefix . '-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'department_id' => $departmentId,
            'is_supervisor' => true,
            'status' => 1,
        ]);
    }

    private function createDepartment(string $name, ?Admin $head = null): Departments
    {
        $department = Departments::query()->create([
            'name' => $name . ' ' . uniqid(),
            'description' => $name . ' description',
            'head_id' => $head?->id,
            'status' => 1,
        ]);

        if ($head) {
            $head->forceFill(['department_id' => $department->id])->save();
        }

        return $department;
    }

    private function createCustomer(string $prefix, string $name): User
    {
        [$firstName, $lastName] = array_pad(explode(' ', $name, 2), 2, '');

        return User::query()->create([
            'name' => $name,
            'f_name' => $firstName,
            'l_name' => $lastName,
            'email' => $prefix . '-' . uniqid() . '@example.com',
            'phone' => '2010' . random_int(1000000, 9999999),
            'password' => bcrypt('password'),
            'is_active' => 1,
            'user_type' => 0,
        ]);
    }

    private function createLead(array $attributes = []): Lead
    {
        $id = DB::table('leads')->insertGetId(array_merge([
            'party_type' => 'retail',
            'contact_id' => null,
            'department_id' => null,
            'employee_id' => null,
            'owner_id' => null,
            'status' => 'new',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return Lead::query()->findOrFail($id);
    }

    private function createDeal(array $attributes = []): Deal
    {
        $relatedPartyId = $attributes['related_party_id'] ?? $this->createCustomer('deal-party', 'Deal Party')->id;

        $id = DB::table('deals')->insertGetId(array_merge([
            'related_party_type' => 'contact',
            'related_party_id' => $relatedPartyId,
            'stage' => 'register',
            'department_id' => null,
            'priority' => 'low',
            'status' => 'open',
            'quotation_status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return Deal::query()->findOrFail($id);
    }

    private function createInboxMessage(array $attributes = []): InboxMessage
    {
        $id = DB::table('inbox_messages')->insertGetId(array_merge([
            'subject' => 'CRM message',
            'body' => 'Body content',
            'contact_id' => null,
            'sender_name' => 'Sender',
            'sender_email' => 'sender-' . uniqid() . '@example.com',
            'sender_phone' => '2011' . random_int(1000000, 9999999),
            'pipeline' => 'email',
            'status' => 'new',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return InboxMessage::query()->findOrFail($id);
    }

    private function createSpoofedUpload(string $originalName, string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'crm-upload-');
        file_put_contents($path, $contents);

        return new UploadedFile(
            $path,
            $originalName,
            'text/plain',
            null,
            true
        );
    }
}
