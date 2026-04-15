<?php

namespace Tests\Feature;

use Tests\TestCase;

class LanguageFlagCodeHelperTest extends TestCase
{
    public function test_language_flag_code_uses_country_code_when_present(): void
    {
        $this->assertSame('eg', getLanguageFlagCode([
            'code' => 'AR',
            'country_code' => 'EG',
        ]));

        $this->assertSame('en', getLanguageFlagCode([]));
    }

    public function test_language_flag_code_defaults_arabic_to_egypt_when_country_code_is_missing_or_language_like(): void
    {
        $this->assertSame('eg', getLanguageFlagCode([
            'code' => 'AR',
            'country_code' => 'AR',
        ]));

        $this->assertSame('eg', getLanguageFlagCode([
            'code' => 'AR',
        ]));

        $this->assertSame('eg', getLanguageCountryCode([
            'code' => 'ar',
            'country_code' => 'ar',
        ]));
    }
}
