<?php

namespace Tests\Feature;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\RestAPI\v1\WarrantyActivationApiController;
use App\Http\Controllers\Web\WarrantyActivationController;
use App\Services\FirebaseService;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class WarrantyActivationTestOtpTest extends TestCase
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

        Mockery::close();

        parent::tearDown();
    }

    public function test_web_controller_uses_0000_outside_live_mode(): void
    {
        config(['otp.test_mode_enabled' => true]);
        $controller = $this->makeWebController();

        $this->assertTrue($this->invokePrivateMethod($controller, 'isWarrantyTestOtpAllowed'));
        $this->assertSame('0000', $this->invokePrivateMethod($controller, 'generateWarrantyOtp'));
    }

    public function test_web_controller_disables_test_otp_in_live_mode(): void
    {
        config(['otp.test_mode_enabled' => false]);
        $controller = $this->makeWebController();

        $this->assertFalse($this->invokePrivateMethod($controller, 'isWarrantyTestOtpAllowed'));
        $this->assertNotSame('0000', $this->invokePrivateMethod($controller, 'generateWarrantyOtp'));
    }

    public function test_api_controller_uses_0000_outside_live_mode(): void
    {
        config(['otp.test_mode_enabled' => true]);
        $controller = $this->makeApiController();

        $this->assertTrue($this->invokePrivateMethod($controller, 'isWarrantyTestOtpAllowed'));
        $this->assertSame('0000', $this->invokePrivateMethod($controller, 'generateWarrantyOtp'));
    }

    public function test_api_controller_disables_test_otp_in_live_mode(): void
    {
        config(['otp.test_mode_enabled' => false]);
        $controller = $this->makeApiController();

        $this->assertFalse($this->invokePrivateMethod($controller, 'isWarrantyTestOtpAllowed'));
        $this->assertNotSame('0000', $this->invokePrivateMethod($controller, 'generateWarrantyOtp'));
    }

    private function makeWebController(): WarrantyActivationController
    {
        return new WarrantyActivationController(
            Mockery::mock(BusinessSettingRepositoryInterface::class),
            Mockery::mock(FirebaseService::class),
        );
    }

    private function makeApiController(): WarrantyActivationApiController
    {
        return new WarrantyActivationApiController(
            Mockery::mock(BusinessSettingRepositoryInterface::class),
            Mockery::mock(FirebaseService::class),
        );
    }

    private function invokePrivateMethod(object $instance, string $methodName): mixed
    {
        $method = new ReflectionMethod($instance, $methodName);
        $method->setAccessible(true);

        return $method->invoke($instance);
    }
}
