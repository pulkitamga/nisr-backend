<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\Cms\ContentManagementController;
use App\Support\CmsContentSanitizer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class CmsSecurityRegressionTest extends TestCase
{
    public function test_rich_text_sanitizer_removes_scripts_event_handlers_and_dangerous_links(): void
    {
        $payload = '<p onclick="alert(1)">Safe</p><script>alert(2)</script><a href="javascript:alert(3)">Link</a>';

        $sanitized = CmsContentSanitizer::sanitizeRichText($payload);

        $this->assertStringNotContainsString('<script', $sanitized);
        $this->assertStringNotContainsString('onclick=', $sanitized);
        $this->assertStringNotContainsString('javascript:', $sanitized);
        $this->assertStringContainsString('<p>Safe</p>', $sanitized);
    }

    public function test_link_sanitizer_blocks_dangerous_protocols_and_normalizes_bare_hostnames(): void
    {
        $this->assertSame('', CmsContentSanitizer::sanitizeLink('javascript:alert(1)'));
        $this->assertSame('https://www.elnisr.online', CmsContentSanitizer::sanitizeLink('www.elnisr.online'));
        $this->assertSame('/contact-us', CmsContentSanitizer::sanitizeLink('/contact-us'));
    }

    public function test_content_management_controller_rejects_path_traversal_slug(): void
    {
        $controller = new ContentManagementController();

        $this->expectException(NotFoundHttpException::class);

        $controller->edit('../blog');
    }
}
