<?php

namespace Tests\Feature;

use App\Services\PrioritySetupService;
use Illuminate\Http\Request;
use Tests\TestCase;

class PrioritySetupServiceTest extends TestCase
{
    public function test_it_builds_blog_category_priority_payload(): void
    {
        $service = new PrioritySetupService();
        $request = new Request([
            'default_sorting_status' => '1',
            'custom_sorting_status' => '1',
            'sort_by' => 'most_clicked',
        ]);

        $this->assertSame([
            'default_sorting_status' => '1',
            'custom_sorting_status' => '1',
            'sort_by' => 'most_clicked',
        ], $service->updateBlogCategoryPrioritySetupData($request));
    }

    public function test_it_builds_blog_priority_payload_with_defaults(): void
    {
        $service = new PrioritySetupService();
        $request = new Request([
            'sort_by' => 'a_to_z',
        ]);

        $this->assertSame([
            'default_sorting_status' => 0,
            'custom_sorting_status' => 0,
            'sort_by' => 'a_to_z',
        ], $service->updateBlogPrioritySetupData($request));
    }
}
