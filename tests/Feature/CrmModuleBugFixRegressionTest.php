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
use App\Models\InboxTask;
use App\Models\Lead;
use App\Models\SupportTicketStatusMaster;
use App\Models\User;
use App\Services\Crm\EscalationService;
use App\Services\SlaService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
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

        Cache::put('pnc_language', ['en', 'ar']);
    }

    protected function tearDown(): void
    {
        Cache::forget('pnc_language');

        parent::tearDown();
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

    public function test_inbox_list_renders_default_status_selection_and_primary_subject_link(): void
    {
        $this->actingAs($this->createAdmin('inbox-toolbar-admin'), 'admin');

        $message = $this->createInboxMessage([
            'subject' => 'Toolbar subject',
            'status' => 'new',
        ]);

        $view = $this->makeInboxController()->getListView(
            Request::create('/admin/crm/inbox', 'GET', [
                'choose_first' => 50,
            ])
        );

        view()->share('errors', new ViewErrorBag());
        $html = $view->render();

        $this->assertStringContainsString('option value="new" selected', $html);
        $this->assertStringContainsString(route('admin.crm.message.show', $message->id), $html);
        $this->assertStringContainsString('Toolbar subject', $html);
        $this->assertStringContainsString('id="crm-inbox-toolbar"', $html);
        $this->assertStringContainsString('data-crm-export-button="true"', $html);
        $this->assertStringContainsString('crm-row-actions__toggle', $html);
        $this->assertStringContainsString('addMessageModal', $html);
        $this->assertStringContainsString('window.crmUiText', $html);
        $this->assertStringContainsString(translate('Please select at least one message!'), $html);
        $this->assertStringContainsString(translate('More actions'), $html);
        $this->assertStringContainsString(translate('No Owner'), $html);
    }

    public function test_complete_task_returns_message_copy_for_wrong_inbox_message(): void
    {
        $admin = $this->createAdmin('inbox-task-complete-admin');
        $this->actingAs($admin, 'admin');

        $message = $this->createInboxMessage();
        $otherMessage = $this->createInboxMessage([
            'subject' => 'Other inbox message',
        ]);

        $task = InboxTask::create([
            'message_id' => $otherMessage->id,
            'employee_id' => $admin->id,
            'department_id' => null,
            'name' => 'Follow up',
            'description' => 'Mismatch task',
            'due_date' => now()->addDay(),
            'status' => 'pending',
        ]);

        $response = $this->makeInboxController()->completeTask(
            Request::create('/admin/crm/inbox/task/complete', 'POST'),
            $message->id,
            $task->id
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(translate('Task does not belong to this message!'), $response->getData(true)['message']);
    }

    public function test_lead_list_renders_primary_subject_link(): void
    {
        $this->actingAs($this->createAdmin('lead-toolbar-admin'), 'admin');

        $lead = $this->createLead();
        $this->createInboxMessage([
            'related_lead_id' => $lead->id,
            'subject' => 'Lead record subject',
            'status' => 'new',
        ]);

        $view = $this->makeLeadController()->getListView(
            Request::create('/admin/crm/lead', 'GET', [
                'choose_first' => 50,
            ])
        );

        view()->share('errors', new ViewErrorBag());
        $html = $view->render();

        $this->assertStringContainsString(route('admin.crm.lead.show', $lead->id), $html);
        $this->assertStringContainsString('Lead record subject', $html);
        $this->assertStringContainsString('id="crm-lead-toolbar"', $html);
        $this->assertStringContainsString('data-crm-export-button="true"', $html);
        $this->assertStringContainsString('crm-row-actions__toggle', $html);
        $this->assertStringContainsString('window.crmUiText', $html);
        $this->assertStringContainsString('assets/back-end/js/admin/lead.js', $html);
        $this->assertStringContainsString('id="getUserOrdersRoute"', $html);
        $this->assertStringContainsString(translate('Please select at least one message!'), $html);
        $this->assertStringContainsString(translate('No Owner'), $html);
        $this->assertStringContainsString(translate('No Department'), $html);
        $this->assertStringContainsString(translate('No Employee'), $html);
        $this->assertStringNotContainsString(translate('Assign before Convert'), $html);
        $this->assertStringNotContainsString('const convertSelectPartyMessage', $html);
        $this->assertStringNotContainsString("$(document).on(\"click\", \".disqualify-btn\"", $html);
    }

    public function test_support_ticket_list_renders_overflow_toggle_and_assignment_chip(): void
    {
        $admin = $this->createAdmin('support-toolbar-admin');

        $this->actingAs($admin, 'admin');

        $status = new SupportTicketStatusMaster();
        $status->forceFill([
            'id' => 1,
            'name' => 'New',
            'master_id' => 1,
            'status' => 'active',
        ]);

        $ticket = new Fluent([
            'id' => 98765,
            'subject' => 'Support queue subject',
            'customer' => new Fluent([
                'f_name' => 'Support',
                'l_name' => 'Customer',
                'email' => 'support-customer@example.com',
            ]),
            'priority' => 'medium',
            'status_details' => $status,
            'created_at' => now(),
            'employee_id' => null,
            'department' => new Fluent([
                'id' => null,
                'head_id' => $admin->id,
            ]),
            'department_id' => null,
            'status' => 1,
            'follow_up_date' => null,
        ]);

        $tickets = new LengthAwarePaginator(
            [$ticket],
            1,
            15,
            1,
            ['path' => '/admin/support-ticket/view/support']
        );

        view()->share('errors', new ViewErrorBag());
        $html = view('admin-views.crm.tickets.support', [
            'tickets' => $tickets,
            'aAllStatus' => collect([$status]),
            'aInProgressStatus' => collect([$status]),
            'getDepartment' => collect(),
            'employees' => collect(),
            'services' => collect(),
            'status' => 'support',
        ])->render();

        $this->assertStringContainsString(route('admin.support-ticket.details', $ticket->id), $html);
        $this->assertStringContainsString('Support queue subject', $html);
        $this->assertStringContainsString('id="crm-support-ticket-toolbar"', $html);
        $this->assertStringContainsString('data-crm-export-button="true"', $html);
        $this->assertStringContainsString('crm-row-actions__toggle', $html);
        $this->assertStringContainsString(translate('More actions'), $html);
        $this->assertStringContainsString(translate('No Employee'), $html);
    }

    public function test_service_ticket_list_loads_shared_service_module_without_inline_flow_script(): void
    {
        $admin = $this->createAdmin('service-toolbar-admin');

        $this->actingAs($admin, 'admin');

        $status = new SupportTicketStatusMaster();
        $status->forceFill([
            'id' => 2,
            'name' => 'New',
            'master_id' => 2,
            'status' => 'active',
        ]);

        $service = new Fluent([
            'id' => 5,
            'title' => 'Oil Change',
            'base_price_inshop' => 100,
            'base_price_mobile' => 140,
            'parts_cost' => 10,
            'travel_fee_per_km' => 2,
            'included_km_mobile' => 5,
            'labor_hours' => 1,
        ]);

        $ticket = new Fluent([
            'id' => 67890,
            'subject' => 'Service queue subject',
            'customer' => new Fluent([
                'f_name' => 'Service',
                'l_name' => 'Customer',
                'email' => 'service-customer@example.com',
            ]),
            'priority' => 'medium',
            'status_details' => $status,
            'created_at' => now(),
            'employee_id' => null,
            'department_id' => null,
            'status' => \App\Support\ServiceTicketWorkflow::STATUS_NEW,
            'service' => $service,
            'service_id' => 5,
            'latestServiceJob' => null,
            'relatedInboxMessage' => null,
        ]);

        $tickets = new LengthAwarePaginator(
            [$ticket],
            1,
            15,
            1,
            ['path' => '/admin/support-ticket/view/service']
        );

        view()->share('errors', new ViewErrorBag());
        $html = view('admin-views.crm.tickets.service', [
            'tickets' => $tickets,
            'aAllStatus' => collect([$status]),
            'aInProgressStatus' => collect([$status]),
            'getDepartment' => collect(),
            'employees' => collect([new Fluent(['id' => 1, 'name' => 'Technician'])]),
            'services' => collect([$service]),
        ])->render();

        $this->assertStringContainsString(route('admin.support-ticket.service.singleTicket', $ticket->id), $html);
        $this->assertStringContainsString('Service queue subject', $html);
        $this->assertStringContainsString('id="crm-service-ticket-toolbar"', $html);
        $this->assertStringContainsString('assets/back-end/js/admin/service-ticket.js', $html);
        $this->assertStringContainsString('id="service-ticket-force-close"', $html);
        $this->assertStringContainsString('id="service-ticket-action-cannot-be-undone"', $html);
        $this->assertStringNotContainsString("const actionsWithConfirmation = ['start-job', 'complete-job', 'close-ticket', 'cancel-ticket'];", $html);
        $this->assertStringNotContainsString("wireLanguageTabs('.estimate-language-tab', '.estimate-language-form', 'esti');", $html);
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

    public function test_lead_employee_and_owner_loaders_keep_department_head(): void
    {
        [$department, $head, $supervisor, $employee] = $this->seedAssignmentDirectory();
        $controller = $this->makeLeadController($this->makeAdminRepositoryForAssignment([$head, $supervisor, $employee]));

        $employeeResponse = $controller->getEmployeesByDepartment(Request::create('/admin/crm/lead/get-employee', 'GET', [
            'department_id' => $department->id,
            'head_id' => $head->id,
            'assignment' => 'employee',
        ]));
        $ownerResponse = $controller->getEmployeesByDepartment(Request::create('/admin/crm/lead/get-employee', 'GET', [
            'department_id' => $department->id,
            'head_id' => $head->id,
            'assignment' => 'owner',
        ]));

        $this->assertEqualsCanonicalizing([$head->id, $supervisor->id, $employee->id], collect($employeeResponse->getData(true))->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$head->id, $supervisor->id], collect($ownerResponse->getData(true))->pluck('id')->all());
    }

    public function test_inbox_employee_and_owner_loaders_keep_department_head(): void
    {
        [$department, $head, $supervisor, $employee] = $this->seedAssignmentDirectory();
        $controller = $this->makeInboxController($this->makeAdminRepositoryForAssignment([$head, $supervisor, $employee]));

        $employeeResponse = $controller->getEmployeesByDepartment(Request::create('/admin/crm/inbox/get-employee', 'GET', [
            'department_id' => $department->id,
            'head_id' => $head->id,
            'assignment' => 'employee',
        ]));
        $ownerResponse = $controller->getEmployeesByDepartment(Request::create('/admin/crm/inbox/get-employee', 'GET', [
            'department_id' => $department->id,
            'head_id' => $head->id,
            'assignment' => 'owner',
        ]));

        $this->assertEqualsCanonicalizing([$head->id, $supervisor->id, $employee->id], collect($employeeResponse->getData(true))->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$head->id, $supervisor->id], collect($ownerResponse->getData(true))->pluck('id')->all());
    }

    public function test_deal_employee_and_owner_loaders_keep_department_head(): void
    {
        [$department, $head, $supervisor, $employee] = $this->seedAssignmentDirectory();
        $controller = $this->makeDealController($this->makeAdminRepositoryForAssignment([$head, $supervisor, $employee]));

        $employeeResponse = $controller->getEmployeesByDepartment(Request::create('/admin/crm/deals/get-employee', 'GET', [
            'department_id' => $department->id,
            'head_id' => $head->id,
            'assignment' => 'employee',
        ]));
        $ownerResponse = $controller->getEmployeesByDepartment(Request::create('/admin/crm/deals/get-employee', 'GET', [
            'department_id' => $department->id,
            'head_id' => $head->id,
            'assignment' => 'owner',
        ]));

        $this->assertEqualsCanonicalizing([$head->id, $supervisor->id, $employee->id], collect($employeeResponse->getData(true))->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$head->id, $supervisor->id], collect($ownerResponse->getData(true))->pluck('id')->all());
    }

    public function test_lead_owner_action_carries_department_id_for_owner_loader(): void
    {
        $template = file_get_contents(resource_path('views/admin-views/crm/leads.blade.php'));

        $this->assertIsString($template);
        $this->assertStringContainsString('class="dropdown-item assign-owner-btn"', $template);
        $this->assertStringContainsString('data-department-id="{{ $msg->department_id ?? \'\' }}"', $template);
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

    private function makeLeadController(?AdminRepositoryInterface $adminRepo = null): LeadController
    {
        $departmentRepo = $this->createMock(DepartmentRepositoryInterface::class);
        $departmentRepo->method('getListWhere')->willReturn(new EloquentCollection());

        if ($adminRepo === null) {
            $adminRepo = $this->createMock(AdminRepositoryInterface::class);
            $adminRepo->method('getEmployeeListWhere')->willReturn(collect());
        }

        return new LeadController(
            $this->createMock(SupportTicketRepositoryInterface::class),
            $this->createMock(SupportTicketConvRepositoryInterface::class),
            $departmentRepo,
            $adminRepo,
            $this->createMock(AdminNotificationRepositoryInterface::class),
            $this->createMock(EscalationService::class),
        );
    }

    private function makeDealController(?AdminRepositoryInterface $adminRepo = null): DealController
    {
        $departmentRepo = $this->createMock(DepartmentRepositoryInterface::class);
        $departmentRepo->method('getListWhere')->willReturn(new EloquentCollection());

        if ($adminRepo === null) {
            $adminRepo = $this->createMock(AdminRepositoryInterface::class);
            $adminRepo->method('getEmployeeListWhere')->willReturn(collect());
        }

        return new DealController(
            $this->createMock(SupportTicketRepositoryInterface::class),
            $this->createMock(SupportTicketConvRepositoryInterface::class),
            $departmentRepo,
            $adminRepo,
            $this->createMock(AdminNotificationRepositoryInterface::class),
            $this->createMock(EscalationService::class),
        );
    }

    private function makeInboxController(?AdminRepositoryInterface $adminRepo = null): InboxMessageController
    {
        $departmentRepo = $this->createMock(DepartmentRepositoryInterface::class);
        $departmentRepo->method('getListWhere')->willReturn(new EloquentCollection());

        if ($adminRepo === null) {
            $adminRepo = $this->createMock(AdminRepositoryInterface::class);
            $adminRepo->method('getEmployeeListWhere')->willReturn(collect());
        }

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

    private function makeAdminRepositoryForAssignment(array $admins): AdminRepositoryInterface
    {
        $adminRepo = $this->createMock(AdminRepositoryInterface::class);
        $adminRepo->method('getEmployeeListWhere')
            ->willReturnCallback(function (array $orderBy = [], ?string $searchValue = null, array $filters = []) use ($admins) {
                return collect($admins)
                    ->when(!empty($filters['department_id']), fn($collection) => $collection->where('department_id', (int)$filters['department_id']))
                    ->sortByDesc('id')
                    ->values();
            });

        return $adminRepo;
    }

    private function seedAssignmentDirectory(): array
    {
        $head = $this->createAdmin('assignment-loader-head');
        $department = $this->createDepartment('Assignment Loader Department', $head);
        $supervisor = $this->createAdmin('assignment-loader-supervisor', $department->id);
        $employee = $this->createAdmin('assignment-loader-employee', $department->id);
        $employee->forceFill(['is_supervisor' => false])->save();

        return [$department, $head->fresh(), $supervisor->fresh(), $employee->fresh()];
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
