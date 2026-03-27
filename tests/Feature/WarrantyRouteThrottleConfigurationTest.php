<?php

namespace Tests\Feature;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

class WarrantyRouteThrottleConfigurationTest extends TestCase
{
    public function test_web_warranty_claim_and_lookup_routes_are_throttled(): void
    {
        $this->assertRouteHasMiddleware(
            RouteFacade::getRoutes()->getByName('warranty.claim.store'),
            'throttle:warranty-claim-create'
        );

        $this->assertRouteHasMiddleware(
            RouteFacade::getRoutes()->getByName('warranty.lookup.submit'),
            'throttle:warranty-lookup'
        );

        $this->assertRouteHasMiddleware(
            RouteFacade::getRoutes()->getByName('warranty.lookup.verify'),
            'throttle:warranty-lookup'
        );

        $this->assertRouteHasMiddleware(
            RouteFacade::getRoutes()->getByName('customer.warranty-claim-payment-request'),
            'throttle:warranty-claim-payment'
        );

        $this->assertRouteHasMiddleware(
            RouteFacade::getRoutes()->getByName('pay-warranty-claim'),
            'throttle:warranty-claim-payment'
        );
    }

    public function test_api_warranty_claim_and_lookup_routes_are_throttled(): void
    {
        $this->assertRouteHasMiddleware(
            $this->findRoute(['POST'], 'api/v1/warranty/claim'),
            'throttle:warranty-claim-create'
        );

        $this->assertRouteHasMiddleware(
            $this->findRoute(['POST'], 'api/v1/warranty/lookup'),
            'throttle:warranty-lookup'
        );

        $this->assertRouteHasMiddleware(
            $this->findRoute(['POST'], 'api/v1/warranty/lookup/verify'),
            'throttle:warranty-lookup'
        );

        $this->assertRouteHasMiddleware(
            $this->findRoute(['POST'], 'api/v1/customer/warranty-claims/{claim_number}/payment-request'),
            'throttle:warranty-claim-payment'
        );
    }

    private function findRoute(array $methods, string $uri): Route
    {
        foreach (RouteFacade::getRoutes() as $route) {
            if ($route->uri() === $uri && $route->methods() === array_merge($methods, ['HEAD'])) {
                return $route;
            }

            if ($route->uri() === $uri && $route->methods() === $methods) {
                return $route;
            }
        }

        $this->fail("Route not found for [{$uri}]");
    }

    private function assertRouteHasMiddleware(?Route $route, string $middleware): void
    {
        $this->assertNotNull($route);
        $this->assertContains($middleware, $route->gatherMiddleware());
    }
}
