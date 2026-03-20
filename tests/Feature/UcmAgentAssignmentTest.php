<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\UcmController;
use App\Models\Admin;
use App\Models\CrmCall;
use App\Services\UcmApiService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class UcmAgentAssignmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->createAdminsTable();
        $this->createUsersTable();
        $this->createCrmCallsTable();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        Mockery::close();
        Schema::dropIfExists('crm_calls');
        Schema::dropIfExists('users');
        Schema::dropIfExists('admins');

        parent::tearDown();
    }

    public function test_calls_marks_recent_webhook_call_as_mine_when_admin_phone_matches_call_number(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Agent One',
            'phone' => '101',
            'email' => 'agent-one@example.com',
            'password' => bcrypt('secret'),
            'status' => 1,
        ]);

        $this->actingAs($admin, 'admin');

        Cache::put('ucm:webhook:last_heartbeat', now()->toIso8601String(), now()->addMinute());

        CrmCall::query()->create([
            'call_id' => 'call-101',
            'ucm_channel' => 'PJSIP/101-0001',
            'src_number' => '0501234567',
            'dst_number' => '101',
            'direction' => 'inbound',
            'status' => 'ringing',
            'call_date' => now(),
            'started_at' => now(),
        ]);

        CrmCall::query()->create([
            'call_id' => 'call-202',
            'ucm_channel' => 'PJSIP/202-0002',
            'src_number' => '0507654321',
            'dst_number' => '202',
            'direction' => 'inbound',
            'status' => 'ringing',
            'call_date' => now(),
            'started_at' => now(),
        ]);

        $response = app(UcmController::class)->calls();
        $calls = $response->getData(true);

        $mine = collect($calls)->firstWhere('call_id', 'call-101');
        $other = collect($calls)->firstWhere('call_id', 'call-202');

        $this->assertTrue($mine['is_mine']);
        $this->assertFalse($other['is_mine']);
    }

    public function test_accept_assigns_answering_admin_to_call(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Agent Two',
            'phone' => '102',
            'email' => 'agent-two@example.com',
            'password' => bcrypt('secret'),
            'status' => 1,
        ]);

        $this->actingAs($admin, 'admin');

        CrmCall::query()->create([
            'call_id' => 'call-accept',
            'ucm_channel' => 'PJSIP/102-0003',
            'src_number' => '0501234567',
            'dst_number' => '102',
            'direction' => 'inbound',
            'status' => 'ringing',
            'call_date' => now()->subSeconds(15),
            'started_at' => now()->subSeconds(15),
        ]);

        $mockUcm = Mockery::mock(UcmApiService::class);
        $mockUcm->shouldReceive('acceptCall')
            ->once()
            ->with('PJSIP/102-0003')
            ->andReturn(['status' => 0]);
        app()->instance(UcmApiService::class, $mockUcm);

        $response = app(UcmController::class)->accept(new Request([
            'channel' => 'PJSIP/102-0003',
            'call_id' => 'call-accept',
        ]));

        $call = CrmCall::query()->where('call_id', 'call-accept')->firstOrFail();

        $this->assertTrue($response->getData(true)['ok']);
        $this->assertSame('ongoing', $call->status);
        $this->assertSame($admin->id, $call->agent_id);
        $this->assertNotNull($call->answered_at);
    }

    private function createAdminsTable(): void
    {
        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->boolean('status')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    private function createUsersTable(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    private function createCrmCallsTable(): void
    {
        Schema::create('crm_calls', function (Blueprint $table): void {
            $table->id();
            $table->string('call_id')->nullable();
            $table->string('ucm_channel')->nullable();
            $table->string('ucm_peer_channel')->nullable();
            $table->string('ucm_uniqueid')->nullable();
            $table->string('ucm_bridge_id')->nullable();
            $table->string('src_number')->nullable();
            $table->string('dst_number')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->dateTime('call_date')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('answered_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->integer('call_duration')->nullable();
            $table->text('call_notes')->nullable();
            $table->json('raw_payload')->nullable();
            $table->string('direction')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }
}
