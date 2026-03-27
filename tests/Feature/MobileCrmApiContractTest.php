<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MobileCrmApiContractTest extends TestCase
{
    protected function setUp(): void
    {
        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
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

        Carbon::setTestNow(Carbon::parse('2026-03-27 12:00:00'));

        $this->createTables();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        foreach ([
            'support_ticket_convs',
            'support_ticket_status_master',
            'support_tickets',
            'inbox_activities',
            'inbox_messages',
            'contacts',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_customer_cases_endpoint_returns_the_mobile_case_contract(): void
    {
        $customer = $this->createCustomer([
            'email' => 'case-list@example.com',
            'phone' => '201011122233',
        ]);

        DB::table('support_ticket_status_master')->insert([
            'id' => 11,
            'master_id' => 1,
            'name' => 'Open',
            'status' => 'open',
            'position' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('support_tickets')->insert([
            'id' => 70,
            'customer_id' => $customer->id,
            'source_id' => 15,
            'subject' => 'Brake check booking',
            'description' => 'Need help with brake noise.',
            'priority' => 'high',
            'type' => 'support',
            'sub_type' => 'support',
            'status' => 11,
            'created_at' => '2026-03-27 12:00:00',
            'updated_at' => '2026-03-27 12:30:00',
        ]);

        DB::table('inbox_messages')->insert([
            'id' => 15,
            'contact_id' => $customer->id,
            'subject' => 'Brake check booking',
            'body' => 'Need help with brake noise.',
            'sender_name' => 'Mobile Case Tester',
            'sender_email' => $customer->email,
            'sender_phone' => $customer->phone,
            'pipeline' => 'form',
            'message_type' => 'support',
            'status' => 'converted',
            'priority' => 'high',
            'related_ticket_id' => 70,
            'details' => json_encode([
                'category' => 'support',
                'subject' => 'Brake check booking',
                'message' => 'Need help with brake noise.',
            ], JSON_THROW_ON_ERROR),
            'created_at' => '2026-03-27 12:00:00',
            'updated_at' => '2026-03-27 12:10:00',
            'deleted_at' => null,
        ]);

        DB::table('support_ticket_convs')->insert([
            'support_ticket_id' => 70,
            'admin_id' => 0,
            'admin_message' => 'We are reviewing your request.',
            'created_at' => '2026-03-27 12:45:00',
            'updated_at' => '2026-03-27 12:45:00',
        ]);

        $response = $this->actingAs($customer, 'api')
            ->withHeader('Authorization', 'Bearer testing-token')
            ->getJson('/api/v1/customer/cases');

        $response->assertOk();
        $this->assertSame([
            'cases' => [
                [
                    'id' => '15',
                    'reference' => 'CASE-15',
                    'category' => 'support',
                    'subject' => 'Brake check booking',
                    'status' => 'processing',
                    'priority' => 'high',
                    'created_at' => '2026-03-27T12:00:00+06:00',
                    'updated_at' => '2026-03-27T12:45:00+06:00',
                    'is_converted' => true,
                    'ticket_id' => '70',
                    'last_update' => '2026-03-27 12:45:00',
                    'next_step' => 'Reply on the ticket thread for updates from support.',
                ],
            ],
        ], $response->json());
    }

    public function test_contact_us_endpoint_returns_the_mobile_case_contract(): void
    {
        $customer = $this->createCustomer([
            'email' => 'contact-us@example.com',
            'phone' => '201055566677',
        ]);

        DB::table('support_ticket_status_master')->insert([
            'id' => 1,
            'master_id' => 1,
            'name' => 'Open',
            'status' => 'open',
            'position' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($customer, 'api')
            ->withHeader('Authorization', 'Bearer testing-token')
            ->postJson('/api/v1/contact-us', [
                'category' => 'support',
                'subject' => 'Battery replacement',
                'message' => 'Need to book a battery replacement.',
                'full_name' => 'Mobile Contract Tester',
                'email' => $customer->email,
                'phone' => $customer->phone,
            ]);

        $response->assertOk();
        $this->assertSame([
            'message' => 'your_message_send_successfully',
            'case' => [
                'id' => '1',
                'reference' => 'CASE-1',
                'category' => 'support',
                'subject' => 'Battery replacement',
                'status' => 'converted',
                'priority' => 'medium',
                'created_at' => '2026-03-27T12:00:00+06:00',
                'updated_at' => '2026-03-27T12:00:00+06:00',
                'is_converted' => true,
                'ticket_id' => '1',
                'last_update' => '2026-03-27 12:00:00',
                'next_step' => 'Your case has been converted to a support ticket.',
                'attachments' => [],
                'has_attachments' => false,
            ],
        ], $response->json());
    }

    private function createTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('f_name')->nullable();
            $table->string('l_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_phone_verified')->default(0);
            $table->boolean('is_email_verified')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::create('inbox_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('pipeline')->nullable();
            $table->string('message_type')->nullable();
            $table->string('status')->nullable();
            $table->string('priority')->nullable();
            $table->unsignedBigInteger('related_ticket_id')->nullable();
            $table->string('convert_type')->nullable();
            $table->string('convert_sub_type')->nullable();
            $table->text('details')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('inbox_activities', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('activity_type')->nullable();
            $table->text('details')->nullable();
            $table->string('title')->nullable();
            $table->string('subject')->nullable();
            $table->timestamp('note_date')->nullable();
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('type')->nullable();
            $table->string('sub_type')->nullable();
            $table->string('request_type')->nullable();
            $table->string('priority')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->text('attachment')->nullable();
            $table->timestamps();
        });

        Schema::create('support_ticket_status_master', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('master_id')->nullable();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('support_ticket_convs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('support_ticket_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('customer_message')->nullable();
            $table->text('admin_message')->nullable();
            $table->text('attachment')->nullable();
            $table->timestamps();
        });
    }

    private function createCustomer(array $attributes = []): User
    {
        $id = DB::table('users')->insertGetId(array_merge([
            'name' => 'Mobile Contract Tester',
            'f_name' => 'Mobile',
            'l_name' => 'Tester',
            'email' => 'mobile-' . uniqid() . '@example.com',
            'phone' => '2010' . random_int(1000000, 9999999),
            'password' => bcrypt('password'),
            'is_active' => 1,
            'is_phone_verified' => 1,
            'is_email_verified' => 1,
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return User::query()->findOrFail($id);
    }
}
