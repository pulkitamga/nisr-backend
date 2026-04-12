<?php

namespace Tests\Feature;

use App\Core\BootCheck;
use App\Exceptions\AccessViolationException;
use App\Models\Admin;
use Tests\Fixtures\AccessGuardPlainAdminController;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AccessGuardEnforcementTest extends TestCase
{
    private string $stateFile;
    private string $buildMetaFile;
    private string $secretKey;

    protected function setUp(): void
    {
        parent::setUp();

        if (!function_exists('sodium_crypto_sign_keypair')) {
            $this->markTestSkipped('Sodium extension is required for access guard tests.');
        }

        $tempRoot = storage_path('framework/testing-access-guard-enforcement');
        if (!is_dir($tempRoot)) {
            mkdir($tempRoot, 0775, true);
        }

        $this->stateFile = $tempRoot . '/.runtime_state';
        $this->buildMetaFile = $tempRoot . '/build-meta.php';

        @unlink($this->stateFile);
        @unlink($this->buildMetaFile);

        $keypair = sodium_crypto_sign_keypair();
        $this->secretKey = sodium_crypto_sign_secretkey($keypair);

        config()->set('access.product', 'alnisr-test');
        config()->set('access.file', $this->stateFile);
        config()->set('access.public_key', base64_encode(sodium_crypto_sign_publickey($keypair)));
        config()->set('access.build_meta', $this->buildMetaFile);
        config()->set('access.protected_paths', ['admin', 'admin/*']);
        config()->set('access.protected_console_commands', ['queue:work', 'schedule:run']);
        config()->set('access.runtime_host', 'localhost');
        config()->set('access.runtime_ip', '127.0.0.1');

        Route::middleware(['admin', 'access'])->get('/admin/_access-check', [AccessGuardPlainAdminController::class, '__invoke']);

        $admin = new Admin();
        $admin->forceFill(['id' => 1]);
        $admin->exists = true;

        $this->actingAs($admin, 'admin');
    }

    public function test_missing_state_blocks_admin_route_with_custom_page(): void
    {
        $response = $this->withServerVariables(['HTTP_HOST' => 'localhost'])
            ->get('/admin/_access-check');

        $response->assertStatus(403);
        $response->assertSee(translate('access_restricted_message'));
        $response->assertDontSee('AccessViolationException');
    }

    public function test_missing_state_returns_generic_json_response(): void
    {
        $this->withServerVariables(['HTTP_HOST' => 'localhost'])
            ->getJson('/admin/_access-check')
            ->assertStatus(403)
            ->assertExactJson(['message' => translate('access_denied')]);
    }

    public function test_valid_state_allows_admin_route_even_without_base_admin_controller(): void
    {
        file_put_contents($this->stateFile, $this->issueState([
            'v' => 1,
            'p' => 'alnisr-test',
            'hosts' => ['localhost'],
            'nbf' => time() - 60,
            'exp' => time() + 3600,
        ]));

        $this->withServerVariables(['HTTP_HOST' => 'localhost'])
            ->get('/admin/_access-check')
            ->assertOk()
            ->assertSee('ok');
    }

    public function test_system_verify_returns_non_zero_when_state_is_missing(): void
    {
        $exitCode = Artisan::call('system:verify');

        $this->assertSame(1, $exitCode);
    }

    public function test_system_verify_returns_zero_when_state_is_valid(): void
    {
        file_put_contents($this->stateFile, $this->issueState([
            'v' => 1,
            'p' => 'alnisr-test',
            'hosts' => ['localhost'],
            'nbf' => time() - 60,
            'exp' => time() + 3600,
        ]));

        $exitCode = Artisan::call('system:verify');

        $this->assertSame(0, $exitCode);
    }

    public function test_boot_check_enforces_guarded_console_commands(): void
    {
        $originalArgv = $_SERVER['argv'] ?? null;
        $_SERVER['argv'] = ['artisan', 'queue:work'];

        try {
            $this->expectException(AccessViolationException::class);

            $this->app->make(BootCheck::class)->boot();
        } finally {
            if ($originalArgv === null) {
                unset($_SERVER['argv']);
            } else {
                $_SERVER['argv'] = $originalArgv;
            }
        }
    }

    public function test_access_violation_is_not_reported_through_the_exception_handler(): void
    {
        $handler = $this->app->make(ExceptionHandler::class);

        $this->assertFalse($handler->shouldReport(new AccessViolationException()));
    }

    private function issueState(array $payload, string $prefix = 'RTS-'): string
    {
        $payloadJson = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = sodium_crypto_sign_detached($payloadJson, $this->secretKey);

        return $prefix . $this->base64UrlEncode($payloadJson) . '.' . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
