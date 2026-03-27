<?php

namespace Tests\Feature;

use App\Http\Middleware\APIGuestMiddleware;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class APIGuestMiddlewareTest extends TestCase
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

        Schema::dropIfExists('guest_users');

        Schema::create('guest_users', function (Blueprint $table): void {
            $table->id();
            $table->string('ip_address')->nullable();
            $table->string('fcm_token')->nullable();
            $table->timestamps();
        });

        Route::middleware(APIGuestMiddleware::class)->any('/_test/api-guest', function () {
            return response()->json([
                'guest_id' => request('guest_id'),
                'is_guest' => request('is_guest'),
                'payment_request_from' => request('payment_request_from'),
            ]);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('guest_users');
        parent::tearDown();
    }

    public function test_it_creates_guest_context_when_guest_id_is_missing(): void
    {
        $response = $this->getJson('/_test/api-guest');

        $response->assertOk()
            ->assertJsonPath('is_guest', true)
            ->assertJsonPath('payment_request_from', 'app');

        $guestId = (int) $response->json('guest_id');

        $this->assertGreaterThan(0, $guestId);
        $this->assertDatabaseHas('guest_users', ['id' => $guestId]);
    }

    public function test_it_backfills_unknown_guest_id_with_a_real_guest_record(): void
    {
        $response = $this->getJson('/_test/api-guest?guest_id=999999');

        $response->assertOk()
            ->assertJsonPath('is_guest', true)
            ->assertJsonPath('payment_request_from', 'app');

        $guestId = (int) $response->json('guest_id');

        $this->assertGreaterThan(0, $guestId);
        $this->assertDatabaseHas('guest_users', ['id' => $guestId]);
        $this->assertSame(1, DB::table('guest_users')->count());
    }
}
