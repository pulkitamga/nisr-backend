<?php

namespace Tests\Feature;

use App\Exceptions\AccessViolationException;
use App\Services\AccessGuard;
use Illuminate\Http\Request;
use Tests\TestCase;

class AccessGuardTest extends TestCase
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

        $tempRoot = storage_path('framework/testing-access-guard-service');
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
        config()->set('access.runtime_host', 'example.com');
        config()->set('access.runtime_ip', '203.0.113.10');
    }

    public function test_it_validates_an_rts_state_using_hosts(): void
    {
        file_put_contents($this->stateFile, $this->issueState([
            'v' => 1,
            'p' => 'alnisr-test',
            'hosts' => ['example.com'],
            'ips' => ['203.0.113.10'],
            'nbf' => time() - 60,
            'exp' => time() + 3600,
        ]));

        $this->bindRequest('https://example.com/admin', '203.0.113.10');

        $this->assertTrue(app(AccessGuard::class)->validate());
    }

    public function test_it_accepts_lic_prefix_and_domains_fallback(): void
    {
        config()->set('access.runtime_host', 'licensed.example.com');

        file_put_contents($this->stateFile, $this->issueState([
            'v' => 1,
            'p' => 'alnisr-test',
            'domains' => ['licensed.example.com'],
            'nbf' => time() - 60,
            'exp' => time() + 3600,
        ], 'LIC-'));

        $this->bindRequest('https://licensed.example.com/admin', '203.0.113.10');

        $this->assertTrue(app(AccessGuard::class)->validate());
    }

    public function test_it_accepts_ip_that_matches_cidr_restriction(): void
    {
        config()->set('access.runtime_ip', '10.10.55.2');

        file_put_contents($this->stateFile, $this->issueState([
            'v' => 1,
            'p' => 'alnisr-test',
            'hosts' => ['example.com'],
            'cidrs' => ['10.10.0.0/16'],
            'nbf' => time() - 60,
            'exp' => time() + 3600,
        ]));

        $this->bindRequest('https://example.com/admin', '10.10.55.2');

        $this->assertTrue(app(AccessGuard::class)->validate());
    }

    public function test_it_prefers_configured_runtime_ip_over_request_ip_for_http_validation(): void
    {
        config()->set('access.runtime_ip', '203.0.113.10');

        file_put_contents($this->stateFile, $this->issueState([
            'v' => 1,
            'p' => 'alnisr-test',
            'hosts' => ['example.com'],
            'ips' => ['203.0.113.10'],
            'nbf' => time() - 60,
            'exp' => time() + 3600,
        ]));

        $this->bindRequest('https://example.com/admin', '198.51.100.25');

        $this->assertTrue(app(AccessGuard::class)->validate());
    }

    public function test_it_rejects_build_or_customer_marker_mismatch_when_build_meta_exists(): void
    {
        file_put_contents($this->buildMetaFile, <<<PHP
<?php

return [
    'customer_id' => 'customer-a',
    'build_id' => 'build-a',
    'encrypted' => false,
];
PHP);

        file_put_contents($this->stateFile, $this->issueState([
            'v' => 1,
            'p' => 'alnisr-test',
            'hosts' => ['example.com'],
            'build' => 'build-b',
            'customer' => 'customer-b',
            'nbf' => time() - 60,
            'exp' => time() + 3600,
        ]));

        $this->bindRequest('https://example.com/admin', '203.0.113.10');

        try {
            app(AccessGuard::class)->validate();
            $this->fail('Expected access violation was not thrown.');
        } catch (AccessViolationException $exception) {
            $this->assertSame('Build marker mismatch.', $exception->reason());
        }
    }

    private function bindRequest(string $url, string $ip): void
    {
        $request = Request::create($url, 'GET', [], [], [], [
            'REMOTE_ADDR' => $ip,
        ]);

        $this->app->instance('request', $request);
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
