<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\UcmController;
use App\Http\Controllers\UcmWebhookController;
use App\Models\CrmCall;
use App\Services\UcmApiService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class UcmLiveCallsFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->createCrmCallsTable();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        Mockery::close();
        Schema::dropIfExists('crm_calls');

        parent::tearDown();
    }

    public function test_it_uses_recent_webhook_calls_when_webhooks_are_alive(): void
    {
        Cache::put('ucm:webhook:last_heartbeat', now()->toIso8601String(), now()->addMinute());

        \DB::table('crm_calls')->insert([
            [
                'call_id' => 'recent-ringing',
                'ucm_channel' => 'PJSIP/100-0001',
                'src_number' => '100',
                'dst_number' => '200',
                'direction' => 'inbound',
                'status' => 'ringing',
                'call_date' => now(),
                'started_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'call_id' => 'old-ongoing',
                'ucm_channel' => 'PJSIP/101-0002',
                'src_number' => '101',
                'dst_number' => '201',
                'direction' => 'inbound',
                'status' => 'ongoing',
                'call_date' => now()->subMinutes(10),
                'started_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
                'created_at' => now()->subMinutes(10),
            ],
            [
                'call_id' => 'recent-completed',
                'ucm_channel' => 'PJSIP/102-0003',
                'src_number' => '102',
                'dst_number' => '202',
                'direction' => 'inbound',
                'status' => 'completed',
                'call_date' => now(),
                'started_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        ]);

        $controller = app(UcmController::class);
        $method = new \ReflectionMethod($controller, 'getCachedLiveCalls');
        $method->setAccessible(true);

        $calls = $method->invoke($controller);

        $this->assertCount(1, $calls);
        $this->assertSame('recent-ringing', $calls[0]['call_id']);
        $this->assertSame('PJSIP/100-0001', $calls[0]['channel']);
        $this->assertTrue(UcmWebhookController::isWebhookAlive(30));
    }

    public function test_it_uses_cached_snapshot_during_transport_failure_cooldown(): void
    {
        $cachedSnapshot = [[
            'call_id' => 'cached-call',
            'channel' => 'PJSIP/300-0009',
            'caller' => '300',
            'callee' => '400',
            'status' => 'ringing',
            'direction' => 'inbound',
        ]];

        Cache::put('ucm:alive_calls:snapshot:v2', $cachedSnapshot, now()->addSeconds(10));

        $mockUcm = Mockery::mock(UcmApiService::class);
        $mockUcm->shouldReceive('isAvailable')->once()->andReturnTrue();
        $mockUcm->shouldReceive('isInTransportFailureCooldown')->once()->andReturnTrue();
        $mockUcm->shouldNotReceive('getLiveCalls');
        app()->instance(UcmApiService::class, $mockUcm);

        $controller = app(UcmController::class);
        $method = new \ReflectionMethod($controller, 'fetchLiveCallsFromUcm');
        $method->setAccessible(true);

        $calls = $method->invoke($controller);

        $this->assertSame($cachedSnapshot, $calls);
    }

    private function createCrmCallsTable(): void
    {
        Schema::dropIfExists('crm_calls');

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
