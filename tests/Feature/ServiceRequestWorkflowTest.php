<?php

namespace Tests\Feature;

use App\Contracts\Repositories\ServiceRequestRepositoryInterface;
use App\Http\Controllers\RestAPI\v1\ServiceRequestController;
use App\Http\Requests\ServiceRequestFormRequest;
use App\Models\InboxMessage;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\SupportTicket;
use App\Models\SupportTicketConv;
use App\Models\Translation;
use App\Models\User;
use App\Models\VehicleMake;
use App\Models\VehicleYear;
use App\Services\ServiceRequestSubmissionService;
use App\Services\TicketConvert;
use App\Services\ServiceWorkflowNotificationService;
use App\Support\ServiceTicketWorkflow;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServiceRequestWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createTestSchema();
    }

    public function test_create_returns_created_ticket_summary_for_valid_request(): void
    {
        DB::table('support_ticket_status_master')->insert([
            'id' => ServiceTicketWorkflow::STATUS_NEW,
            'master_id' => ServiceTicketWorkflow::STATUS_MASTER_ID,
            'name' => 'new',
            'status' => 'active',
            'position' => 1,
        ]);

        $customer = User::query()->create([
            'f_name' => 'Nour',
            'l_name' => 'Hassan',
            'email' => 'nour@example.com',
            'phone' => '201111111111',
            'password' => 'secret',
        ]);

        $service = Service::query()->create([
            'service_id' => 'SRV-200',
            'title' => 'Battery Check',
            'base_price_inshop' => 90,
            'base_price_mobile' => 130,
            'included_km_mobile' => 20,
            'travel_fee_per_km' => 4,
            'parts_included' => ['battery'],
            'call_center_flag' => false,
        ]);

        $workflowNotifier = $this->createMock(ServiceWorkflowNotificationService::class);
        $workflowNotifier->expects($this->once())->method('notify');

        $submissionService = $this->makeSubmissionService($workflowNotifier);

        $request = $this->getMockBuilder(ServiceRequestFormRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validated', 'user', 'input'])
            ->getMock();

        $request->expects($this->once())
            ->method('validated')
            ->willReturn([
                'service_id' => $service->id,
                'service_option' => 'in_shop',
                'vehicle_type' => 'Sedan',
                'vehicle_make_id' => 10,
                'vehicle_make' => 'Toyota',
                'vehicle_model_id' => 20,
                'vehicle_model' => 'Corolla',
                'vehicle_year_id' => 30,
                'vehicle_year' => 2023,
                'vehicle_mileage' => 22000,
                'vin' => 'VIN-200',
                'problem_description' => 'Battery warning light stays on.',
                'notes' => 'Customer will arrive after 4 PM.',
            ]);
        $request->expects($this->once())
            ->method('user')
            ->willReturn($customer);
        $request->expects($this->exactly(2))
            ->method('input')
            ->willReturnMap([
                ['service_id', null, $service->id],
                ['service_reference', null, null],
            ]);

        $response = (new ServiceRequestController($submissionService))->create($request);
        $payload = $response->getData(true);

        $this->assertSame(201, $response->status());
        $this->assertSame('Service request created successfully.', $payload['message']);
        $this->assertSame(1, $payload['ticket_id']);
        $this->assertSame('Battery Check', $payload['ticket']['service']['title']);
        $this->assertSame('in_shop', $payload['ticket']['service_option']);
        $this->assertSame('In Shop', $payload['ticket']['service_option_label']);
        $this->assertSame('New', $payload['ticket']['status_label']);
        $this->assertSame('Toyota', $payload['ticket']['vehicle']['make']);
        $this->assertSame(10, $payload['ticket']['vehicle']['make_id']);
        $this->assertNull($payload['ticket']['location']);
        $this->assertSame(
            'Battery warning light stays on.',
            $payload['ticket']['problem_description']
        );
        $this->assertSame(
            'Customer will arrive after 4 PM.',
            $payload['ticket']['notes']
        );
        $this->assertTrue($payload['ticket']['can_reply']);
        $this->assertSame(1, SupportTicket::query()->count());
        $this->assertSame(1, ServiceRequest::query()->count());
    }

    public function test_create_requires_authenticated_customer(): void
    {
        $submissionService = $this->createMock(ServiceRequestSubmissionService::class);
        $submissionService->expects($this->never())->method('submit');

        $request = $this->getMockBuilder(ServiceRequestFormRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['user'])
            ->getMock();

        $request->expects($this->once())
            ->method('user')
            ->willReturn(null);

        $response = (new ServiceRequestController($submissionService))->create($request);
        $payload = $response->getData(true);

        $this->assertSame(401, $response->status());
        $this->assertSame('Please login first', $payload['message']);
    }

    public function test_submission_service_creates_linked_ticket_and_inbox_message(): void
    {
        DB::table('support_ticket_status_master')->insert([
            'id' => ServiceTicketWorkflow::STATUS_NEW,
            'master_id' => ServiceTicketWorkflow::STATUS_MASTER_ID,
            'name' => 'new',
            'status' => 'active',
            'position' => 1,
        ]);

        $customer = User::query()->create([
            'f_name' => 'Ahmed',
            'l_name' => 'Ali',
            'email' => 'ahmed@example.com',
            'phone' => '201234567890',
            'password' => 'secret',
        ]);

        $service = Service::query()->create([
            'service_id' => 'SRV-100',
            'title' => 'Oil Change',
            'base_price_inshop' => 120,
            'base_price_mobile' => 180,
            'included_km_mobile' => 15,
            'travel_fee_per_km' => 3,
            'parts_included' => ['oil'],
            'call_center_flag' => false,
        ]);

        $workflowNotifier = $this->createMock(ServiceWorkflowNotificationService::class);
        $workflowNotifier
            ->expects($this->once())
            ->method('notify')
            ->with(
                $this->callback(fn ($ticket) => $ticket instanceof SupportTicket
                    && (int) $ticket->customer_id === (int) $customer->id
                    && (int) $ticket->service_id === (int) $service->id),
                'ticket_created',
                'Service Ticket Created',
                $this->stringContains('service ticket'),
                null,
                [['type' => 'customer', 'id' => $customer->id]]
            );

        $submissionService = $this->makeSubmissionService($workflowNotifier);

        $ticket = $submissionService->submit([
            'service_id' => $service->id,
            'service_option' => 'mobile',
            'country' => 'Egypt',
            'state' => 'Cairo',
            'city' => 'Nasr City',
            'area' => 'Zone 1',
            'address' => 'Street 10',
            'latitude' => '30.0444',
            'longitude' => '31.2357',
            'vehicle_type' => 'SUV',
            'vehicle_make_id' => 11,
            'vehicle_make' => 'Toyota',
            'vehicle_model_id' => 21,
            'vehicle_model' => 'Fortuner',
            'vehicle_year_id' => 31,
            'vehicle_year' => 2024,
            'vehicle_mileage' => 15000,
            'vin' => 'VIN-123',
            'problem_description' => 'Oil leak under the engine.',
            'notes' => 'Please call before arrival.',
        ], $customer);

        $this->assertNotNull($ticket->id);
        $this->assertSame($service->id, (int) $ticket->service_id);
        $this->assertSame($customer->id, (int) $ticket->customer_id);
        $this->assertSame('service', $ticket->type);
        $this->assertSame(0, $ticket->request_type);
        $this->assertSame(ServiceTicketWorkflow::STATUS_NEW, (int) $ticket->status);

        $this->assertSame(1, ServiceRequest::query()->count());
        $this->assertSame(1, SupportTicket::query()->count());
        $this->assertSame(1, InboxMessage::query()->count());

        $inboxMessage = InboxMessage::query()->firstOrFail();
        $this->assertSame($ticket->id, (int) $inboxMessage->related_ticket_id);
        $this->assertSame('ticket', $inboxMessage->convert_type);
        $this->assertSame('service', $inboxMessage->convert_sub_type);
        $this->assertSame($service->id, (int) ($inboxMessage->details['service_id'] ?? 0));
        $this->assertSame('mobile', $inboxMessage->details['service_option'] ?? null);
        $this->assertSame(11, $inboxMessage->details['vehicle_make_id'] ?? null);
        $this->assertSame('VIN-123', $inboxMessage->details['vin'] ?? null);
        $this->assertSame('Street 10', $inboxMessage->details['address'] ?? null);
        $this->assertSame(
            'Oil leak under the engine.',
            $inboxMessage->details['problem_description'] ?? null
        );
        $this->assertSame(
            'Please call before arrival.',
            $inboxMessage->details['notes'] ?? null
        );
        $this->assertArrayHasKey('service_request_id', $inboxMessage->details);
        $this->assertStringContainsString(
            'Problem description: Oil leak under the engine.',
            (string) $ticket->description
        );
        $this->assertStringContainsString(
            'Notes: Please call before arrival.',
            (string) $ticket->description
        );
    }

    public function test_submission_service_still_returns_ticket_when_notification_dispatch_fails(): void
    {
        DB::table('support_ticket_status_master')->insert([
            'id' => ServiceTicketWorkflow::STATUS_NEW,
            'master_id' => ServiceTicketWorkflow::STATUS_MASTER_ID,
            'name' => 'new',
            'status' => 'active',
            'position' => 1,
        ]);

        $customer = User::query()->create([
            'f_name' => 'Nadia',
            'l_name' => 'Saber',
            'email' => 'nadia@example.com',
            'phone' => '201234567891',
            'password' => 'secret',
        ]);

        $service = Service::query()->create([
            'service_id' => 'SRV-101',
            'title' => 'Battery Replacement',
            'base_price_inshop' => 180,
            'base_price_mobile' => 240,
            'included_km_mobile' => 15,
            'travel_fee_per_km' => 5,
            'parts_included' => ['battery'],
            'call_center_flag' => false,
        ]);

        $workflowNotifier = $this->createMock(ServiceWorkflowNotificationService::class);
        $workflowNotifier
            ->expects($this->once())
            ->method('notify')
            ->willThrowException(new \RuntimeException('Notification channel unavailable.'));

        Log::shouldReceive('warning')
            ->once()
            ->with('Service request notification dispatch failed', \Mockery::on(function (array $context) use ($customer): bool {
                return ($context['customer_id'] ?? null) === $customer->id
                    && ($context['error'] ?? null) === 'Notification channel unavailable.';
            }));

        $submissionService = $this->makeSubmissionService($workflowNotifier);

        $ticket = $submissionService->submit([
            'service_id' => $service->id,
            'service_option' => 'in_shop',
            'vehicle_type' => 'Sedan',
            'vehicle_make' => 'Toyota',
            'vehicle_model' => 'Corolla',
            'vehicle_year' => 2024,
            'vehicle_mileage' => 12000,
        ], $customer);

        $this->assertNotNull($ticket->id);
        $this->assertSame($service->id, (int) $ticket->service_id);
        $this->assertSame($customer->id, (int) $ticket->customer_id);
        $this->assertSame(1, ServiceRequest::query()->count());
        $this->assertSame(1, SupportTicket::query()->count());
        $this->assertSame(1, InboxMessage::query()->count());
    }

    public function test_reference_data_returns_vehicle_options_and_catalogs(): void
    {
        $make = VehicleMake::query()->create(['name' => 'Toyota']);
        DB::table('vehicle_models')->insert([
            'make_id' => $make->id,
            'name' => 'Corolla',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        VehicleYear::query()->create(['year' => 2025]);

        $controller = $this->makeController();
        $response = $controller->referenceData();
        $payload = $response->getData(true);

        $this->assertSame(200, $response->status());
        $this->assertCount(2, $payload['service_options']);
        $this->assertSame('in_shop', $payload['service_options'][0]['key']);
        $this->assertContains('SUV', $payload['vehicle_types']);
        $this->assertSame('Toyota', $payload['makes'][0]['name']);
        $this->assertSame('Corolla', $payload['makes'][0]['models'][0]['name']);
        $this->assertSame(2025, $payload['years'][0]['year']);
    }

    public function test_catalog_service_titles_prefer_service_title_not_product_name_for_mobile_contract(): void
    {
        $product = new Product();
        $product->setRawAttributes([
            'id' => 400072,
            'name' => 'Eco-Friendly Trade-In & Recycling',
            'slug' => 'eco-friendly-trade-in-recycling',
            'details' => '<p>Product description.</p>',
        ], true);

        $product->setRelation('translations', collect([
            new Translation([
                'locale' => 'ar',
                'key' => 'name',
                'value' => 'الاسم الخاص بالمنتج',
            ]),
            new Translation([
                'locale' => 'en',
                'key' => 'name',
                'value' => 'Eco-Friendly Trade-In & Recycling',
            ]),
            new Translation([
                'locale' => 'ar',
                'key' => 'service_tittle',
                'value' => 'خدمة الصيانة الشاملة والتشخيص',
            ]),
        ]));

        $service = new Service();
        $service->setRawAttributes([
            'id' => 88,
            'service_id' => 'SRV-400072',
            'title' => 'Full Service',
        ], true);

        $service->setRelation('translations', collect());

        $product->setRelation('service', $service);

        $controller = $this->makeController();
        $method = new \ReflectionMethod($controller, 'formatCatalogProduct');
        $method->setAccessible(true);

        app()->setLocale('ar');
        $arabicPayload = $method->invoke($controller, $product);

        $this->assertSame('خدمة الصيانة الشاملة والتشخيص', $arabicPayload['service']['title']);
        $this->assertSame('خدمة الصيانة الشاملة والتشخيص', $arabicPayload['service']['title_ar']);
        $this->assertSame('Full Service', $arabicPayload['service']['title_en']);
        $this->assertSame('خدمة الصيانة الشاملة والتشخيص', $arabicPayload['service']['translations'][0]['value']);
        $this->assertSame('title', $arabicPayload['service']['translations'][0]['key']);
        $this->assertNotSame('Eco-Friendly Trade-In & Recycling', $arabicPayload['service']['title']);
        $this->assertNotSame('الاسم الخاص بالمنتج', $arabicPayload['service']['title_ar']);

        app()->setLocale('en');
        $englishPayload = $method->invoke($controller, $product);

        $this->assertSame('Full Service', $englishPayload['service']['title']);
        $this->assertSame('خدمة الصيانة الشاملة والتشخيص', $englishPayload['service']['title_ar']);
        $this->assertSame('Full Service', $englishPayload['service']['title_en']);
        $this->assertNotSame('Eco-Friendly Trade-In & Recycling', $englishPayload['service']['title']);
    }

    public function test_show_returns_formatted_service_request_details(): void
    {
        DB::table('support_ticket_status_master')->insert([
            'id' => ServiceTicketWorkflow::STATUS_NEW,
            'master_id' => ServiceTicketWorkflow::STATUS_MASTER_ID,
            'name' => 'new',
            'status' => 'active',
            'position' => 1,
        ]);

        $customer = User::query()->create([
            'f_name' => 'Layla',
            'l_name' => 'Omar',
            'email' => 'layla@example.com',
            'phone' => '201000000010',
            'password' => 'secret',
        ]);

        $service = Service::query()->create([
            'service_id' => 'SRV-300',
            'title' => 'Brake Inspection',
            'base_price_inshop' => 200,
            'base_price_mobile' => 260,
            'included_km_mobile' => 15,
            'travel_fee_per_km' => 3,
            'parts_included' => ['pads'],
            'call_center_flag' => false,
        ]);

        $ticket = SupportTicket::query()->create([
            'service_id' => $service->id,
            'customer_id' => $customer->id,
            'subject' => 'Brake Inspection',
            'type' => 'service',
            'sub_type' => 'service',
            'request_type' => 0,
            'priority' => 'medium',
            'description' => 'Customer requested a brake inspection.',
            'status' => ServiceTicketWorkflow::STATUS_NEW,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        InboxMessage::query()->create([
            'subject' => 'Brake Inspection',
            'body' => 'A new service request has been submitted.',
            'contact_id' => $customer->id,
            'sender_name' => 'Layla Omar',
            'sender_email' => $customer->email,
            'sender_phone' => $customer->phone,
            'pipeline' => 'form',
            'message_type' => 'service',
            'related_ticket_id' => $ticket->id,
            'details' => [
                'service_request_id' => 44,
                'service_id' => $service->id,
                'service_option' => 'mobile',
                'vehicle_make_id' => 55,
                'country' => 'Egypt',
                'state' => 'Cairo',
                'city' => 'Nasr City',
                'area' => 'Zone 2',
                'address' => 'Street 20',
                'latitude' => '30.0500',
                'longitude' => '31.2400',
                'vehicle_type' => 'SUV',
                'vehicle_make' => 'Toyota',
                'vehicle_model_id' => 66,
                'vehicle_model' => 'Prado',
                'vehicle_year_id' => 77,
                'vehicle_year' => 2024,
                'vehicle_mileage' => 12000,
                'vin' => 'VIN-300',
                'problem_description' => 'Brakes squeal at low speed.',
                'notes' => 'Customer requests weekend slot.',
            ],
            'status' => 'converted',
            'convert_type' => 'ticket',
            'convert_sub_type' => 'service',
        ]);

        SupportTicketConv::query()->create([
            'support_ticket_id' => $ticket->id,
            'admin_id' => 0,
            'customer_message' => 'Can you confirm the mobile appointment window?',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = Request::create('/api/v1/customer/service-request/' . $ticket->id, 'GET');
        $request->setUserResolver(fn () => $customer);

        $response = $this->makeController()->show($request, $ticket->id);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->status());
        $this->assertSame($ticket->id, $payload['id']);
        $this->assertSame('Brake Inspection', $payload['service']['title']);
        $this->assertSame('mobile', $payload['service_option']);
        $this->assertSame('New', $payload['status_label']);
        $this->assertSame('SUV', $payload['vehicle']['type']);
        $this->assertSame(55, $payload['vehicle']['make_id']);
        $this->assertSame(
            'Brakes squeal at low speed.',
            $payload['vehicle']['problem_description']
        );
        $this->assertSame(
            'Customer requests weekend slot.',
            $payload['notes']
        );
        $this->assertSame('Street 20', $payload['location']['address']);
        $this->assertCount(2, $payload['messages']);
        $this->assertSame(
            'Can you confirm the mobile appointment window?',
            $payload['messages'][1]['customer_message']
        );
        $this->assertSame(44, $payload['service_request']['service_request_id']);
        $this->assertSame('Mobile Service', $payload['service_request']['service_option_label']);
        $this->assertSame(
            'Brakes squeal at low speed.',
            $payload['service_request']['problem_description']
        );
        $this->assertSame(
            'Customer requests weekend slot.',
            $payload['service_request']['notes']
        );
        $this->assertSame([], $payload['activities']);
        $this->assertSame([], $payload['invoices']);
    }

    public function test_reply_adds_customer_message_for_open_service_request(): void
    {
        $customer = User::query()->create([
            'f_name' => 'Sara',
            'l_name' => 'Khaled',
            'email' => 'sara@example.com',
            'phone' => '201000000001',
            'password' => 'secret',
        ]);

        DB::table('support_ticket_status_master')->insert([
            'id' => ServiceTicketWorkflow::STATUS_NEW,
            'master_id' => ServiceTicketWorkflow::STATUS_MASTER_ID,
            'name' => 'new',
            'status' => 'active',
            'position' => 1,
        ]);

        $ticket = SupportTicket::query()->create([
            'customer_id' => $customer->id,
            'subject' => 'Open request',
            'type' => 'service',
            'sub_type' => 'service',
            'request_type' => 0,
            'priority' => 'medium',
            'description' => 'Initial request',
            'status' => ServiceTicketWorkflow::STATUS_NEW,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = Request::create('/api/v1/customer/service-request/' . $ticket->id . '/reply', 'POST', [
            'message' => 'Please confirm the appointment time.',
        ]);
        $request->setUserResolver(fn () => $customer);

        $response = $this->makeController()->reply($request, $ticket->id);
        $payload = $response->getData(true);

        $this->assertSame(200, $response->status());
        $this->assertSame('Reply sent successfully.', $payload['message']);
        $this->assertSame(1, SupportTicketConv::query()->count());
        $this->assertSame(
            'Please confirm the appointment time.',
            SupportTicketConv::query()->firstOrFail()->customer_message
        );
    }

    public function test_reply_rejects_closed_service_request(): void
    {
        $customer = User::query()->create([
            'f_name' => 'Mona',
            'l_name' => 'Ibrahim',
            'email' => 'mona@example.com',
            'phone' => '201000000002',
            'password' => 'secret',
        ]);

        DB::table('support_ticket_status_master')->insert([
            'id' => ServiceTicketWorkflow::STATUS_CLOSED,
            'master_id' => ServiceTicketWorkflow::STATUS_MASTER_ID,
            'name' => 'closed',
            'status' => 'active',
            'position' => 99,
        ]);

        $ticket = SupportTicket::query()->create([
            'customer_id' => $customer->id,
            'subject' => 'Closed request',
            'type' => 'service',
            'sub_type' => 'service',
            'request_type' => 0,
            'priority' => 'medium',
            'description' => 'Initial request',
            'status' => ServiceTicketWorkflow::STATUS_CLOSED,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = Request::create('/api/v1/customer/service-request/' . $ticket->id . '/reply', 'POST', [
            'message' => 'Can this be reopened?',
        ]);
        $request->setUserResolver(fn () => $customer);

        $response = $this->makeController()->reply($request, $ticket->id);
        $payload = $response->getData(true);

        $this->assertSame(422, $response->status());
        $this->assertSame('Closed service requests cannot be updated.', $payload['message']);
        $this->assertSame(0, SupportTicketConv::query()->count());
    }

    public function test_ticket_convert_preserves_service_ticket_contract_for_service_messages(): void
    {
        DB::table('support_ticket_status_master')->insert([
            'id' => ServiceTicketWorkflow::STATUS_NEW,
            'master_id' => ServiceTicketWorkflow::STATUS_MASTER_ID,
            'name' => 'new',
            'status' => 'active',
            'position' => 1,
        ]);

        $customer = User::query()->create([
            'f_name' => 'Sara',
            'l_name' => 'Hassan',
            'email' => 'sara@example.com',
            'phone' => '201000000000',
            'password' => 'secret',
        ]);

        $service = Service::query()->create([
            'service_id' => 'SRV-777',
            'title' => 'Brake Inspection',
            'base_price_inshop' => 80,
            'base_price_mobile' => 120,
            'included_km_mobile' => 10,
            'travel_fee_per_km' => 5,
            'parts_included' => ['pads'],
            'call_center_flag' => false,
        ]);

        $message = InboxMessage::query()->create([
            'subject' => 'Need brake inspection',
            'body' => 'Customer requested a brake inspection.',
            'contact_id' => $customer->id,
            'message_type' => 'service',
            'details' => ['service_id' => $service->id],
            'status' => 'new',
        ]);

        $ticket = TicketConvert::fromInboxMessage($message, 'service');

        $this->assertSame('service', $ticket->type);
        $this->assertSame('service', $ticket->sub_type);
        $this->assertSame(0, (int) $ticket->request_type);
        $this->assertSame($service->id, (int) $ticket->service_id);
        $this->assertSame(ServiceTicketWorkflow::STATUS_NEW, (int) $ticket->status);
    }

    private function makeController(): ServiceRequestController
    {
        return new ServiceRequestController(
            $this->createMock(ServiceRequestSubmissionService::class)
        );
    }

    private function makeSubmissionService(
        ServiceWorkflowNotificationService $workflowNotifier
    ): ServiceRequestSubmissionService {
        return new ServiceRequestSubmissionService(
            $this->makeServiceRequestRepository(),
            $workflowNotifier
        );
    }

    private function makeServiceRequestRepository(): ServiceRequestRepositoryInterface
    {
        return new class implements ServiceRequestRepositoryInterface {
            public function create(array $data): ServiceRequest
            {
                return ServiceRequest::query()->create($data);
            }
        };
    }

    private function createTestSchema(): void
    {
        foreach ([
            'activity_log',
            'service_invoices',
            'service_jobs',
            'support_ticket_convs',
            'support_tickets',
            'inbox_messages',
            'service_requests',
            'support_ticket_status_master',
            'vehicle_models',
            'vehicle_makes',
            'vehicle_years',
            'services',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('event')->nullable();
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->text('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
        });

        Schema::create('service_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('technician_id')->nullable();
            $table->string('status')->nullable();
            $table->string('service_mode')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('service_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('job_id')->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payment_link')->nullable();
            $table->timestamp('payment_link_expires_at')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('f_name')->nullable();
            $table->string('l_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_id')->nullable();
            $table->string('title');
            $table->decimal('base_price_inshop', 10, 2)->nullable();
            $table->decimal('base_price_mobile', 10, 2)->nullable();
            $table->decimal('parts_cost', 10, 2)->nullable();
            $table->integer('included_km_mobile')->nullable();
            $table->decimal('travel_fee_per_km', 10, 2)->nullable();
            $table->decimal('labor_hours', 10, 2)->nullable();
            $table->text('parts_included')->nullable();
            $table->boolean('call_center_flag')->default(false);
            $table->timestamps();
        });

        Schema::create('vehicle_makes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('make_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('vehicle_years', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale')->nullable();
            $table->string('key')->nullable();
            $table->unsignedInteger('item_index')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('support_ticket_status_master', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedInteger('master_id')->nullable();
            $table->string('name');
            $table->string('status')->nullable();
            $table->unsignedInteger('position')->nullable();
        });

        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('service_option');
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('vehicle_type');
            $table->string('vehicle_make');
            $table->string('vehicle_model');
            $table->integer('vehicle_year');
            $table->integer('vehicle_mileage');
            $table->string('vin')->nullable();
            $table->text('problem_description')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('inbox_messages', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('pipeline')->nullable();
            $table->string('message_type')->nullable();
            $table->unsignedBigInteger('related_ticket_id')->nullable();
            $table->json('details')->nullable();
            $table->string('status')->nullable();
            $table->string('convert_type')->nullable();
            $table->string('convert_sub_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('type')->nullable();
            $table->string('sub_type')->nullable();
            $table->integer('request_type')->nullable();
            $table->string('priority')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('status')->nullable();
            $table->json('attachment')->nullable();
            $table->timestamps();
        });

        Schema::create('support_ticket_convs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_ticket_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('customer_message')->nullable();
            $table->text('admin_message')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->json('attachment')->nullable();
            $table->timestamps();
        });
    }
};
