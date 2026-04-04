<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ServicePolicyRouteTest extends TestCase
{
    public function test_service_policy_route_is_registered_with_expected_uri(): void
    {
        $route = Route::getRoutes()->getByName('service-policy');

        $this->assertNotNull($route);
        $this->assertSame('service-policy', $route->uri());
    }

    public function test_service_request_form_links_to_service_policy_route(): void
    {
        $detailsView = file_get_contents(resource_path('themes/default/web-views/services/details.blade.php'));

        $this->assertIsString($detailsView);
        $this->assertStringContainsString("route('service-policy')", $detailsView);
    }
}
