<?php

namespace Tests\Feature;

use App\Models\Translation;
use App\Repositories\TranslationRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TranslationRepositoryLocaleOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('translations');

        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->text('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
            $table->integer('item_index')->nullable();
            $table->timestamps();
        });

        DB::table('business_settings')->insert([
            'type' => 'pnc_language',
            'value' => json_encode(['en', 'ar']),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('translations');

        parent::tearDown();
    }

    public function test_repository_uses_pnc_default_language_as_base_even_when_arabic_is_first_in_request(): void
    {
        $request = new Request([
            'lang' => ['ar', 'en'],
            'title' => ['العنوان', 'Title'],
            'description' => ['الوصف', 'Description'],
        ]);

        $repository = new TranslationRepository(new Translation());

        $repository->add($request, 'App\\Models\\CareerBenefits', 6);

        $storedTranslations = Translation::query()
            ->orderBy('key')
            ->get(['locale', 'key', 'value'])
            ->map(fn ($translation) => [
                'locale' => $translation->locale,
                'key' => $translation->key,
                'value' => $translation->value,
            ])
            ->all();

        $this->assertSame([
            ['locale' => 'ar', 'key' => 'description', 'value' => 'الوصف'],
            ['locale' => 'ar', 'key' => 'title', 'value' => 'العنوان'],
        ], $storedTranslations);
    }

    public function test_configured_default_language_uses_pnc_language_instead_of_app_locale(): void
    {
        config(['app.locale' => 'ar']);

        $this->assertSame('en', getConfiguredDefaultLanguage());
    }

    public function test_configured_language_codes_normalize_structured_language_entries(): void
    {
        DB::table('business_settings')
            ->where('type', 'pnc_language')
            ->update([
                'value' => json_encode([
                    [
                        'id' => 1,
                        'name' => 'english',
                        'direction' => 'ltr',
                        'code' => 'en',
                        'status' => 1,
                        'default' => true,
                    ],
                    [
                        'id' => 2,
                        'name' => 'عربي',
                        'direction' => 'rtl',
                        'code' => 'ar',
                        'status' => 1,
                        'default' => false,
                    ],
                ]),
                'updated_at' => now(),
            ]);

        $this->assertSame(['en', 'ar'], getConfiguredLanguageCodes());
        $this->assertSame('en', getConfiguredDefaultLanguage());
    }

    public function test_direction_helper_uses_language_configuration_when_session_direction_is_missing(): void
    {
        DB::table('business_settings')->insert([
            'type' => 'language',
            'value' => json_encode([
                [
                    'id' => 1,
                    'name' => 'english',
                    'direction' => 'ltr',
                    'code' => 'en',
                    'status' => 1,
                    'default' => true,
                ],
                [
                    'id' => 2,
                    'name' => 'عربي',
                    'direction' => 'rtl',
                    'code' => 'ar',
                    'status' => 1,
                    'default' => false,
                ],
            ]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session()->forget('direction');
        session()->put('local', 'ar');
        session()->put('locale', 'ar');
        app()->setLocale('ar');

        $this->assertSame('rtl', get_direction());
    }

    public function test_array_based_updates_save_non_default_locale_even_when_default_locale_is_absent_from_request(): void
    {
        $request = new Request([
            'lang' => ['ar'],
            'index' => 0,
            'title' => ['عنوان عربي'],
            'description' => ['وصف عربي'],
        ]);

        $repository = new TranslationRepository(new Translation());

        $repository->updateArrayBasedSectionTranslations($request, 'App\\Models\\HomePageSection', 6);

        $storedTranslations = Translation::query()
            ->orderBy('key')
            ->get(['locale', 'key', 'item_index', 'value'])
            ->map(fn ($translation) => [
                'locale' => $translation->locale,
                'key' => $translation->key,
                'item_index' => $translation->item_index,
                'value' => $translation->value,
            ])
            ->all();

        $this->assertSame([
            ['locale' => 'ar', 'key' => 'description', 'item_index' => '0', 'value' => 'وصف عربي'],
            ['locale' => 'ar', 'key' => 'title', 'item_index' => '0', 'value' => 'عنوان عربي'],
        ], $storedTranslations);
    }
}
