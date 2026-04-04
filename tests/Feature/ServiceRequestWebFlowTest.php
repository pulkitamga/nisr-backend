<?php

namespace Tests\Feature;

use App\Contracts\Repositories\ServiceRequestRepositoryInterface;
use App\Models\InboxMessage;
use App\Models\Service;
use App\Models\SupportTicket;
use App\Models\SupportTicketStatusMaster;
use App\Models\User;
use App\Services\ServiceRequestSubmissionService;
use App\Services\ServiceWorkflowNotificationService;
use App\Support\ServiceTicketWorkflow;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServiceRequestWebFlowTest extends TestCase
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
            'session.driver' => 'array',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createTestSchema();

        Route::middleware('web')->post('/service-request', [\App\Http\Controllers\Web\ServiceDetailsController::class, 'storeServiceRequest'])
            ->name('service.request.store');
        Route::middleware('web')->get('/account-tickets', fn () => 'tickets')->name('account-tickets');
    }

    public function test_store_service_request_redirects_back_with_success_and_creates_linked_records(): void
    {
        SupportTicketStatusMaster::query()->create([
            'id' => ServiceTicketWorkflow::STATUS_NEW,
            'master_id' => ServiceTicketWorkflow::STATUS_MASTER_ID,
            'name' => 'new',
            'status' => 'active',
            'position' => 1,
        ]);

        $customer = User::query()->create([
            'f_name' => 'Mona',
            'l_name' => 'Ali',
            'email' => 'mona@example.com',
            'phone' => '201111111111',
            'password' => 'secret',
        ]);

        $service = Service::query()->create([
            'service_id' => 'SRV-500',
            'title' => 'Full Service',
            'base_price_inshop' => 200,
            'base_price_mobile' => 300,
            'included_km_mobile' => 10,
            'travel_fee_per_km' => 10,
            'parts_included' => ['oil filter'],
            'call_center_flag' => false,
        ]);

        $workflowNotifier = $this->createMock(ServiceWorkflowNotificationService::class);
        $workflowNotifier->expects($this->once())->method('notify');

        $submissionService = new ServiceRequestSubmissionService(
            app(ServiceRequestRepositoryInterface::class),
            $workflowNotifier
        );

        $controller = app()->make(\App\Http\Controllers\Web\ServiceDetailsController::class, [
            'serviceRequestSubmissionService' => $submissionService,
        ]);
        app()->instance(\App\Http\Controllers\Web\ServiceDetailsController::class, $controller);

        $response = $this->actingAs($customer, 'customer')
            ->from('/service/test-service')
            ->post(route('service.request.store'), [
                'service_id' => $service->id,
                'service_option' => 'in_shop',
                'agree_terms' => 1,
                'vehicle_make' => 'Toyota',
                'vehicle_model' => 'Corolla',
                'vehicle_year' => 2024,
                'vehicle_mileage' => 15000,
                'problem_description' => 'Battery warning light stays on after startup.',
                'notes' => 'Customer can arrive after 4 PM.',
            ]);

        $response->assertRedirect('/service/test-service');

        $ticket = SupportTicket::query()->firstOrFail();
        $this->assertSame(0, (int) $ticket->request_type);
        $this->assertSame('service', $ticket->type);
        $this->assertSame('service', $ticket->sub_type);
        $this->assertSame($service->id, (int) $ticket->service_id);
        $this->assertSame(1, InboxMessage::query()->count());
        $inboxMessage = InboxMessage::query()->firstOrFail();
        $this->assertSame('New Service Request For - Full Service', $inboxMessage->subject);
        $this->assertSame('A new service request has been submitted.', $inboxMessage->body);
        $this->assertSame('Battery warning light stays on after startup.', $inboxMessage->details['problem_description'] ?? null);
        $this->assertSame('Customer can arrive after 4 PM.', $inboxMessage->details['notes'] ?? null);
        $this->assertStringContainsString('Problem description: Battery warning light stays on after startup.', (string) $ticket->description);
        $this->assertStringContainsString('Notes: Customer can arrive after 4 PM.', (string) $ticket->description);
    }

    public function test_invalid_mobile_service_request_redirects_back_with_errors_and_old_input(): void
    {
        $customer = User::query()->create([
            'f_name' => 'Mona',
            'l_name' => 'Ali',
            'email' => 'mona@example.com',
            'phone' => '201111111111',
            'password' => 'secret',
        ]);

        $service = Service::query()->create([
            'service_id' => 'SRV-500',
            'title' => 'Full Service',
            'base_price_inshop' => 200,
            'base_price_mobile' => 300,
            'included_km_mobile' => 10,
            'travel_fee_per_km' => 10,
            'parts_included' => ['oil filter'],
            'call_center_flag' => false,
        ]);

        $response = $this->actingAs($customer, 'customer')
            ->from('/service/test-service')
            ->post(route('service.request.store'), [
                'service_id' => $service->id,
                'service_option' => 'mobile',
                'agree_terms' => 1,
                'vehicle_type' => 'Sedan',
                'vehicle_mileage' => 15000,
                'vin' => 'VIN-100',
            ]);

        $response->assertRedirect('/service/test-service');
        $response->assertSessionHasErrors(['country', 'state', 'city', 'area', 'address']);
        $response->assertSessionHasInput('service_option', 'mobile');
        $response->assertSessionHasInput('vehicle_type', 'Sedan');
        $response->assertSessionHasInput('vehicle_mileage', 15000);
        $response->assertSessionHasInput('vin', 'VIN-100');
    }

    private function createTestSchema(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->timestamps();
        });

        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->longText('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
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
            $table->string('title')->nullable();
            $table->decimal('base_price_inshop', 24, 2)->default(0);
            $table->decimal('base_price_mobile', 24, 2)->default(0);
            $table->integer('included_km_mobile')->nullable();
            $table->decimal('travel_fee_per_km', 24, 2)->default(0);
            $table->text('parts_included')->nullable();
            $table->boolean('call_center_flag')->default(false);
            $table->timestamps();
        });

        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable();
            $table->foreignId('customer_id')->nullable();
            $table->string('service_option')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_year')->nullable();
            $table->integer('vehicle_mileage')->nullable();
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
            $table->text('details')->nullable();
            $table->string('status')->default('new');
            $table->string('convert_type')->nullable();
            $table->string('convert_sub_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('support_ticket_status_master', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('master_id')->nullable();
            $table->string('name');
            $table->string('status')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->integer('request_type')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->integer('source_id')->default(0);
            $table->string('subject')->nullable();
            $table->string('type')->nullable();
            $table->string('sub_type')->nullable();
            $table->string('priority')->default('low');
            $table->string('description')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });
    }
}
