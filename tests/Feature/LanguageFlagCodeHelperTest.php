<?php

namespace Tests\Feature;

use Tests\TestCase;

class LanguageFlagCodeHelperTest extends TestCase
{
    public function test_language_flag_code_is_normalized_to_lowercase(): void
    {
        $this->assertSame('ar', getLanguageFlagCode([
            'code' => 'AR',
            'country_code' => 'AR',
        ]));

        $this->assertSame('eg', getLanguageFlagCode([
            'code' => 'AR',
            'country_code' => 'EG',
        ]));

        $this->assertSame('en', getLanguageFlagCode([]));
    }
}
