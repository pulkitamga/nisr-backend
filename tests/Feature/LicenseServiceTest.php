<?php

namespace Tests\Feature;

use App\Services\LicenseService;
use Illuminate\Http\Request;
use RuntimeException;
use Tests\TestCase;

class LicenseServiceTest extends TestCase
{
    private const PRODUCT = 'alnisr2';

    protected function setUp(): void
    {
        parent::setUp();

        if (!function_exists('sodium_crypto_sign_keypair')) {
            $this->markTestSkipped('Sodium extension is required for license tests.');
        }
    }

    public function test_it_validates_a_signed_license_from_file(): void
    {
        [$publicKey, $privateKey] = $this->generateKeypair();
        $license = $this->signLicense($this->payload([
            'domains' => ['example.com'],
            'ips' => ['203.0.113.10'],
        ]), $privateKey);

        $licenseFile = $this->writeLicenseFile($license);

        try {
            $this->configureLicense($licenseFile);
            $this->bindRequest('https://example.com/admin', '203.0.113.10');

            $service = $this->makeService(base64_encode($publicKey));
            $this->assertTrue($service->validate());
        } finally {
            @unlink($licenseFile);
        }
    }

    public function test_it_rejects_invalid_signature(): void
    {
        [, $privateKey] = $this->generateKeypair();
        [$secondPublicKey] = $this->generateKeypair();

        $license = $this->signLicense($this->payload(), $privateKey);
        $licenseFile = $this->writeLicenseFile($license);

        try {
            $this->configureLicense($licenseFile);
            $this->bindRequest('https://example.com/admin', '203.0.113.10');

            $service = $this->makeService(base64_encode($secondPublicKey));

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('License signature is invalid.');

            $service->validate();
        } finally {
            @unlink($licenseFile);
        }
    }

    public function test_it_rejects_domain_mismatch(): void
    {
        [$publicKey, $privateKey] = $this->generateKeypair();
        $license = $this->signLicense($this->payload([
            'domains' => ['licensed.example.com'],
        ]), $privateKey);

        $licenseFile = $this->writeLicenseFile($license);

        try {
            $this->configureLicense($licenseFile);
            $this->bindRequest('https://example.com/admin', '203.0.113.10');

            $service = $this->makeService(base64_encode($publicKey));

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Current domain is not allowed by license.');

            $service->validate();
        } finally {
            @unlink($licenseFile);
        }
    }

    public function test_it_accepts_ip_that_matches_cidr_restriction(): void
    {
        [$publicKey, $privateKey] = $this->generateKeypair();
        $license = $this->signLicense($this->payload([
            'cidrs' => ['10.10.0.0/16'],
        ]), $privateKey);

        $licenseFile = $this->writeLicenseFile($license);

        try {
            $this->configureLicense($licenseFile);
            $this->bindRequest('https://example.com/admin', '10.10.55.2');

            $service = $this->makeService(base64_encode($publicKey));
            $this->assertTrue($service->validate());
        } finally {
            @unlink($licenseFile);
        }
    }

    private function makeService(string $publicKeyBase64): LicenseService
    {
        return new class($publicKeyBase64) extends LicenseService {
            public function __construct(private string $publicKeyBase64)
            {
            }

            protected function shouldBypassValidation(): bool
            {
                return false;
            }

            protected function verifySignature(string $payloadJson, string $signature): bool
            {
                $publicKey = base64_decode($this->publicKeyBase64, true);

                if ($publicKey === false) {
                    return false;
                }

                return sodium_crypto_sign_verify_detached($signature, $payloadJson, $publicKey);
            }

            protected function violation(string $reason)
            {
                throw new RuntimeException($reason);
            }
        };
    }

    private function configureLicense(string $licenseFile): void
    {
        $this->app['config']->set('license.mode', 'production');
        $this->app['config']->set('license.product', self::PRODUCT);
        $this->app['config']->set('license.file', $licenseFile);
        $this->app['config']->set('license.key', 'DEV-UNLICENSED');
    }

    private function bindRequest(string $url, string $ip): void
    {
        $request = Request::create($url, 'GET', [], [], [], [
            'REMOTE_ADDR' => $ip,
        ]);

        $this->app->instance('request', $request);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'v' => 1,
            'p' => self::PRODUCT,
            'domains' => [],
            'ips' => [],
            'cidrs' => [],
            'instance' => null,
            'iat' => time() - 60,
            'nbf' => time() - 60,
            'exp' => time() + 3600,
        ], $overrides);
    }

    private function signLicense(array $payload, string $privateKey): string
    {
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $this->assertNotFalse($jsonPayload);

        $signature = sodium_crypto_sign_detached($jsonPayload, $privateKey);

        return 'LIC-' . $this->b64url($jsonPayload) . '.' . $this->b64url($signature);
    }

    private function writeLicenseFile(string $license): string
    {
        $path = tempnam(sys_get_temp_dir(), 'license_');
        $this->assertNotFalse($path);

        file_put_contents($path, $license);

        return $path;
    }

    private function generateKeypair(): array
    {
        $keypair = sodium_crypto_sign_keypair();

        return [
            sodium_crypto_sign_publickey($keypair),
            sodium_crypto_sign_secretkey($keypair),
        ];
    }

    private function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
