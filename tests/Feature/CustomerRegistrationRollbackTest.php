<?php

namespace Tests\Feature;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\LoginSetupRepositoryInterface;
use App\Contracts\Repositories\PhoneOrEmailVerificationRepositoryInterface;
use App\Exceptions\AuthTokenIssueException;
use App\Http\Controllers\RestAPI\v1\auth\CustomerAPIAuthController;
use App\Models\User;
use App\Services\ApiAccessTokenService;
use App\Services\Web\CustomerAuthService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;
use Tests\TestCase;

class CustomerRegistrationRollbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Schema::dropIfExists('login_setups');
        Schema::dropIfExists('users');

        Schema::create('login_setups', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('f_name')->nullable();
            $table->string('l_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('password');
            $table->string('temporary_token')->nullable();
            $table->string('referral_code')->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_phone_verified')->default(false);
            $table->boolean('is_email_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('login_hit_count')->default(0);
            $table->boolean('is_temp_blocked')->default(false);
            $table->timestamp('temp_block_time')->nullable();
            $table->string('login_medium')->nullable();
            $table->string('app_language')->nullable();
            $table->integer('user_type')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Cache::flush();

        Schema::dropIfExists('login_setups');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_register_rolls_back_the_created_user_when_token_issuance_fails(): void
    {
        $customerRepo = $this->mock(CustomerRepositoryInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('add')
                ->once()
                ->andReturnUsing(function (array $data) {
                    DB::table('users')->insert(array_merge([
                        'is_active' => 1,
                        'is_phone_verified' => 0,
                        'is_email_verified' => 0,
                        'login_hit_count' => 0,
                        'is_temp_blocked' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], $data));

                    return User::query()->where('email', $data['email'])->first();
                });
        });

        $phoneOrEmailVerificationRepo = $this->mock(PhoneOrEmailVerificationRepositoryInterface::class);
        $loginSetupRepo = $this->mock(LoginSetupRepositoryInterface::class);
        $customerAuthService = $this->mock(CustomerAuthService::class);

        $this->mock(ApiAccessTokenService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('issueForUser')
                ->once()
                ->andThrow(new AuthTokenIssueException('Token service unavailable', 'Personal access client not found.'));
        });

        $controller = new CustomerAPIAuthController(
            $customerRepo,
            $phoneOrEmailVerificationRepo,
            $loginSetupRepo,
            $customerAuthService,
        );

        $request = Request::create('/api/v1/auth/register', 'POST', [
            'f_name' => 'Test',
            'l_name' => 'User',
            'email' => 'rollback@example.com',
            'phone' => '01234567890',
            'password' => '123456',
        ]);

        try {
            $controller->register($request);
            $this->fail('Expected AuthTokenIssueException was not thrown.');
        } catch (AuthTokenIssueException) {
            $this->assertDatabaseMissing('users', ['email' => 'rollback@example.com']);
        }
    }

    public function test_auth_token_issue_exception_renders_as_a_json_503_for_api_requests(): void
    {
        Route::post('/api/_token-issue-test', function () {
            throw new AuthTokenIssueException('Authentication service is temporarily unavailable. Contact with the administrator.');
        });

        $this->postJson('/api/_token-issue-test')
            ->assertStatus(503)
            ->assertExactJson([
                'message' => 'Authentication service is temporarily unavailable. Contact with the administrator.',
            ]);
    }
}
