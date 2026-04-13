<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Service;
use App\Models\Translation;
use Tests\TestCase;

class ProductServiceTitleTest extends TestCase
{
    public function test_it_prefers_service_title_over_product_name_when_no_service_translation_exists(): void
    {
        $product = new Product();
        $product->setRawAttributes([
            'name' => 'Battery Product Name',
            'product_type' => 'services',
        ], true);

        $product->setRelation('translations', collect([
            new Translation([
                'locale' => 'ar',
                'key' => 'name',
                'value' => 'اسم المنتج',
            ]),
        ]));

        $service = new Service();
        $service->setRawAttributes([
            'title' => 'DIN100B',
        ], true);
        $service->setRelation('translations', collect());

        $product->setRelation('service', $service);

        $this->assertSame('DIN100B', $product->getServiceTitle('en', $product->name));
        $this->assertNotSame('Battery Product Name', $product->getServiceTitle('en', $product->name));
    }

    public function test_it_prefers_product_service_translation_for_localized_service_title(): void
    {
        $product = new Product();
        $product->setRawAttributes([
            'name' => 'Battery Product Name',
            'product_type' => 'services',
        ], true);

        $product->setRelation('translations', collect([
            new Translation([
                'locale' => 'ar',
                'key' => 'service_tittle',
                'value' => 'خدمة DIN100B',
            ]),
        ]));

        $service = new Service();
        $service->setRawAttributes([
            'title' => 'DIN100B',
        ], true);
        $service->setRelation('translations', collect());

        $product->setRelation('service', $service);

        $this->assertSame('خدمة DIN100B', $product->getServiceTitle('ar', $product->name));
        $this->assertNotSame('اسم المنتج', $product->getServiceTitle('ar', $product->name));
    }
}
