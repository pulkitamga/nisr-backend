<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
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

    public function test_translate_helper_applies_placeholder_replacements(): void
    {
        App::setLocale('en');
        session()->put('local', 'en');
        session()->put('locale', 'en');

        $translated = translate('The_password_must_be_at_least :min_characters', ['min' => 8]);

        $this->assertSame('The password must be at least 8 characters', $translated);
    }

    public function test_translate_helper_uses_updated_locale_without_stale_cache(): void
    {
        App::setLocale('en');
        session()->put('local', 'en');
        session()->put('locale', 'en');

        $english = translate('order_number_status_status', ['order' => 15, 'status' => 'closed']);

        App::setLocale('ar');
        session()->put('local', 'ar');
        session()->put('locale', 'ar');

        $arabic = translate('order_number_status_status', ['order' => 15, 'status' => 'مغلق']);

        $this->assertSame('Order #15 status closed', $english);
        $this->assertSame('الطلب رقم 15 حالته مغلق', $arabic);
    }
}
