<?php

namespace Tests\Unit;

use App\Models\Color;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductServiceNormalizationTest extends TestCase
{
    public function test_it_copies_service_title_and_description_into_product_fields(): void
    {
        $service = new ProductService(new Color());
        $request = new Request([
            'product_type' => 'services',
            'name' => ['', ''],
            'description' => ['<p><br></p>', '<p><br></p>'],
            'service_tittle' => ['Synthetic Oil Change', 'تغيير زيت صناعي'],
            'service_description' => ['<p>English service description</p>', '<p>وصف الخدمة</p>'],
        ]);

        $method = new \ReflectionMethod(ProductService::class, 'normalizeServiceDescription');
        $method->setAccessible(true);
        $method->invoke($service, $request);

        $this->assertSame(
            ['Synthetic Oil Change', 'تغيير زيت صناعي'],
            $request->input('name')
        );
        $this->assertSame(
            ['<p>English service description</p>', '<p>وصف الخدمة</p>'],
            $request->input('description')
        );
    }

    public function test_it_generates_a_service_product_code_when_the_form_does_not_send_one(): void
    {
        $service = new ProductService(new Color());
        $request = new Request([
            'product_type' => 'services',
            'service_id' => 'SRV-OIL-SYN',
            'name' => ['Synthetic Oil Change'],
            'lang' => ['en'],
            'code' => null,
        ]);

        $method = new \ReflectionMethod(ProductService::class, 'resolveProductCode');
        $method->setAccessible(true);
        $generatedCode = $method->invoke($service, $request);

        $this->assertMatchesRegularExpression('/^[A-Z0-9]{6,20}$/', (string) $generatedCode);
        $this->assertStringStartsWith('SRVOILSYN', (string) $generatedCode);
    }

    public function test_it_preserves_existing_product_code_when_editing_a_service_without_code_input(): void
    {
        $service = new ProductService(new Color());
        $request = new Request([
            'product_type' => 'services',
            'service_id' => 'SRV-OIL-SYN',
            'name' => ['Synthetic Oil Change'],
            'lang' => ['en'],
            'code' => null,
        ]);

        $method = new \ReflectionMethod(ProductService::class, 'resolveProductCode');
        $method->setAccessible(true);
        $resolvedCode = $method->invoke($service, $request, 'SRV-EXIST-001');

        $this->assertSame('SRV-EXIST-001', $resolvedCode);
    }
}
