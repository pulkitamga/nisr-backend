<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Tests\TestCase;

class TranslateHelperReadOnlyTest extends TestCase
{
    public function test_translate_helper_does_not_write_missing_keys_to_language_files(): void
    {
        $path = base_path('resources/lang/en/new-messages.php');
        $originalContents = file_get_contents($path);
        $key = 'codex_runtime_missing_key_' . Str::random(12);

        try {
            $translated = translate($key);

            $this->assertSame(formatTranslationFallback($key), $translated);
            $this->assertSame($originalContents, file_get_contents($path));
        } finally {
            file_put_contents($path, $originalContents);
        }
    }
}
