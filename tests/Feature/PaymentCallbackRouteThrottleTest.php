<?php

namespace Tests\Feature;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

class PaymentCallbackRouteThrottleTest extends TestCase
{
    public function test_paymob_callback_route_is_throttled(): void
    {
        $this->assertRouteHasMiddleware(
            RouteFacade::getRoutes()->getByName('paymob.callback'),
            'throttle:payment-callback'
        );
    }

    public function test_bosta_webhook_route_is_throttled(): void
    {
        $this->assertRouteHasMiddleware(
            RouteFacade::getRoutes()->getByName('bosta.webhook'),
            'throttle:carrier-webhook'
        );
    }

    private function assertRouteHasMiddleware(?Route $route, string $middleware): void
    {
        $this->assertNotNull($route);
        $this->assertContains($middleware, $route->gatherMiddleware());
    }
}
