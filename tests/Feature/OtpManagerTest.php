<?php

namespace Tests\Feature;

use App\Support\OtpManager;
use Tests\TestCase;

class OtpManagerTest extends TestCase
{
    private bool $originalOtpTestMode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalOtpTestMode = (bool) config('otp.test_mode_enabled');
    }

    protected function tearDown(): void
    {
        config(['otp.test_mode_enabled' => $this->originalOtpTestMode]);

        parent::tearDown();
    }

    public function test_it_uses_fixed_test_tokens_when_test_mode_is_enabled(): void
    {
        config(['otp.test_mode_enabled' => true]);

        $this->assertTrue(OtpManager::testModeEnabled());
        $this->assertSame('1234', OtpManager::numericToken(4));
        $this->assertSame('0000', OtpManager::warrantyToken());
        $this->assertTrue(OtpManager::matchesWarrantyToken('0000'));
    }

    public function test_it_uses_random_tokens_when_test_mode_is_disabled(): void
    {
        config(['otp.test_mode_enabled' => false]);

        $fourDigit = OtpManager::numericToken(4);
        $this->assertFalse(OtpManager::testModeEnabled());
        $this->assertMatchesRegularExpression('/^\d{4}$/', $fourDigit);
        $this->assertMatchesRegularExpression('/^\d{4}$/', OtpManager::warrantyToken());
        $this->assertFalse(OtpManager::matchesWarrantyToken('0000'));
    }
}
