<?php

namespace Tests\Feature;

use App\Services\SEOSettingsService;
use Tests\TestCase;

class SEOSettingsServiceTest extends TestCase
{
    public function test_it_maps_blog_meta_page_to_the_frontend_blog_route(): void
    {
        $service = new SEOSettingsService();

        $pages = $service->getRobotsMetaContentPages();

        $this->assertSame(route('frontend.blog.index'), $pages['blog']['route']);
    }
}
