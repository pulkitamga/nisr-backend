<?php

namespace Tests\Unit;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Http\Controllers\Admin\Blog\BlogController;
use App\Models\Storage as StorageModel;
use Illuminate\Http\Request;
use Modules\Blog\app\Models\Blog;
use Modules\Blog\app\Models\BlogCategory;
use Modules\Blog\app\Models\BlogSeo;
use Modules\Blog\app\Services\Frontend\FrontendBlogService;
use Tests\TestCase;

class AdminBlogControllerTest extends TestCase
{
    public function test_get_slug_supports_locale_keyed_blog_form_payloads(): void
    {
        $controller = new BlogController(
            blogCategory: $this->mock(BlogCategory::class),
            blog: $this->mock(Blog::class),
            blogSeo: $this->mock(BlogSeo::class),
            storageModel: $this->mock(StorageModel::class),
            businessSettingRepo: $this->mock(BusinessSettingRepositoryInterface::class),
            frontendBlogService: $this->mock(FrontendBlogService::class),
        );

        $request = new Request([
            'lang' => [
                'en' => 'en',
                'ar' => 'ar',
            ],
            'title' => [
                'en' => 'Battery Care Guide',
                'ar' => 'دليل العناية بالبطارية',
            ],
        ]);

        $slug = $controller->getSlug($request);

        $this->assertMatchesRegularExpression('/^battery-care-guide-[A-Za-z0-9]{6}$/', $slug);
    }
}
