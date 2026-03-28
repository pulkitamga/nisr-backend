<?php

namespace Tests\Feature;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Http\Controllers\Admin\Cms\AboutController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AboutCmsEnglishValidationTest extends TestCase
{
    public function test_store_requires_english_heading_for_hero_section(): void
    {
        $controller = new AboutController(
            $this->createMock(ProductRepositoryInterface::class),
            $this->createMock(TranslationRepositoryInterface::class),
        );

        $request = Request::create('/admin/content-management/about-us/store/hero', 'POST', [
            'lang' => ['ar', 'en'],
            'heading' => ['عنوان', ''],
            'subheading' => ['فرعي', 'Subtitle'],
        ]);

        try {
            $controller->store($request, 'hero');
            $this->fail('Expected validation exception was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                translate('The_heading_in_english_is_required'),
                $exception->errors()['heading.1'][0] ?? null
            );
        }
    }

    public function test_update_requires_english_subheading_for_hero_section(): void
    {
        $controller = new AboutController(
            $this->createMock(ProductRepositoryInterface::class),
            $this->createMock(TranslationRepositoryInterface::class),
        );

        $request = Request::create('/admin/content-management/about-us/update/hero/6', 'PUT', [
            'lang' => ['ar', 'en'],
            'heading' => ['عنوان', 'Heading'],
            'subheading' => ['فرعي', ''],
        ]);

        try {
            $controller->update($request, 'hero', 6);
            $this->fail('Expected validation exception was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                translate('The_subheading_in_english_is_required'),
                $exception->errors()['subheading.1'][0] ?? null
            );
        }
    }
}
