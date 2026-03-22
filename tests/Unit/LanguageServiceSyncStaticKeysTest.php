<?php

namespace Tests\Unit;

use App\Services\LanguageService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LanguageServiceSyncStaticKeysTest extends TestCase
{
    private string $tempPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempPath = base_path('tmp/translation-sync-' . uniqid('', true));
        File::makeDirectory($this->tempPath . '/scan', 0777, true);
        File::makeDirectory($this->tempPath . '/lang/en', 0777, true);
        File::makeDirectory($this->tempPath . '/lang/ar', 0777, true);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tempPath);

        parent::tearDown();
    }

    public function test_sync_static_translate_keys_adds_missing_literal_keys_only(): void
    {
        file_put_contents(
            $this->tempPath . '/scan/example.php',
            <<<'PHP'
<?php

echo translate('Alpha_Key');
echo translate("Beta_Key");
echo translate($dynamicKey);
echo translate("Gamma_{$dynamicValue}");
PHP
        );

        file_put_contents(
            $this->tempPath . '/lang/en/messages.php',
            "<?php\n\nreturn ['Existing_Key' => 'Existing Key'];\n"
        );
        file_put_contents(
            $this->tempPath . '/lang/en/new-messages.php',
            "<?php\n\nreturn ['Pending_Key' => 'Pending Key'];\n"
        );
        file_put_contents(
            $this->tempPath . '/lang/ar/messages.php',
            "<?php\n\nreturn [];\n"
        );
        file_put_contents(
            $this->tempPath . '/lang/ar/new-messages.php',
            "<?php\n\nreturn [];\n"
        );

        $result = app(LanguageService::class)->syncStaticTranslateKeys(
            locales: ['en', 'ar'],
            scanPaths: [$this->tempPath . '/scan'],
            languageRoot: $this->tempPath . '/lang'
        );

        $enNewMessages = include $this->tempPath . '/lang/en/new-messages.php';
        $arNewMessages = include $this->tempPath . '/lang/ar/new-messages.php';

        $this->assertSame(4, $result['created']);
        $this->assertArrayHasKey('Alpha_Key', $enNewMessages);
        $this->assertArrayHasKey('Beta_Key', $enNewMessages);
        $this->assertArrayHasKey('Pending_Key', $enNewMessages);
        $this->assertArrayNotHasKey('Gamma_{$dynamicValue}', $enNewMessages);
        $this->assertSame('Alpha Key', $enNewMessages['Alpha_Key']);
        $this->assertSame('Beta Key', $arNewMessages['Beta_Key']);
    }
}
