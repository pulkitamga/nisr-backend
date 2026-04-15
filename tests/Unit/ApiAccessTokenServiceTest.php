<?php

namespace Tests\Unit;

use App\Exceptions\AuthTokenIssueException;
use App\Services\ApiAccessTokenService;
use Tests\TestCase;

class ApiAccessTokenServiceTest extends TestCase
{
    public function test_it_returns_the_generated_access_token(): void
    {
        $user = new class {
            public function createToken(string $tokenName): object
            {
                return (object) ['accessToken' => 'header.payload.signature'];
            }

            public function getKey(): int
            {
                return 1;
            }
        };

        $service = new ApiAccessTokenService();

        $this->assertSame('header.payload.signature', $service->issueForUser($user));
    }

    public function test_it_wraps_passport_failures_in_a_domain_exception(): void
    {
        $user = new class {
            public function createToken(string $tokenName): object
            {
                throw new \RuntimeException('Personal access client not found. Please create one.');
            }

            public function getKey(): int
            {
                return 99;
            }
        };

        $service = new ApiAccessTokenService();

        try {
            $service->issueForUser($user);
            $this->fail('Expected AuthTokenIssueException was not thrown.');
        } catch (AuthTokenIssueException $exception) {
            $this->assertSame('Personal access client not found. Please create one.', $exception->reason());
            $this->assertStringContainsString(
                translate('authentication_service_is_temporarily_unavailable'),
                $exception->getMessage()
            );
        }
    }
}
