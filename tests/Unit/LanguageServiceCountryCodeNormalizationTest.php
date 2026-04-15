<?php

namespace Tests\Unit;

use App\Services\LanguageService;
use Illuminate\Http\Request;
use Tests\TestCase;

class LanguageServiceCountryCodeNormalizationTest extends TestCase
{
    public function test_status_updates_normalize_legacy_arabic_country_code_to_egypt(): void
    {
        $service = app(LanguageService::class);
        $languageSetting = new \ArrayObject([
            'value' => json_encode([
                [
                    'id' => 1,
                    'name' => 'Arabic',
                    'code' => 'ar',
                    'country_code' => 'ar',
                    'direction' => 'rtl',
                    'status' => 1,
                    'default' => false,
                ],
            ]),
        ], \ArrayObject::ARRAY_AS_PROPS);

        $result = $service->getStatusData(new Request([
            'code' => 'ar',
        ]), $languageSetting);

        $this->assertSame('eg', $result[0]['country_code']);
        $this->assertSame(0, $result[0]['status']);
    }
}
