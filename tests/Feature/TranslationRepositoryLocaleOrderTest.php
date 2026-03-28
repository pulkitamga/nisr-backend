<?php

namespace Tests\Feature;

use App\Models\Translation;
use App\Repositories\TranslationRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TranslationRepositoryLocaleOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('translations');
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
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('translations');

        parent::tearDown();
    }

    public function test_repository_uses_app_locale_as_base_even_when_arabic_is_first_in_request(): void
    {
        config()->set('app.locale', 'en');

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
}
