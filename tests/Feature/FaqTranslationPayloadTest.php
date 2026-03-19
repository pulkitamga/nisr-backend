<?php

namespace Tests\Feature;

use App\Http\Controllers\RestAPI\v1\GeneralController;
use App\Models\HelpTopic;
use App\Models\Translation;
use App\Traits\CacheManagerTrait;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FaqTranslationPayloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
        Cache::forget(CACHE_HELP_TOPICS_TABLE);
    }

    public function test_cached_faq_payload_keeps_all_translation_rows(): void
    {
        $faq = HelpTopic::query()->create([
            'type' => 'default',
            'question' => 'English question',
            'answer' => 'English answer',
            'status' => 1,
            'ranking' => 1,
        ]);

        Translation::query()->insert([
            [
                'translationable_type' => HelpTopic::class,
                'translationable_id' => $faq->id,
                'locale' => 'en',
                'key' => 'question',
                'value' => 'English question',
            ],
            [
                'translationable_type' => HelpTopic::class,
                'translationable_id' => $faq->id,
                'locale' => 'ar',
                'key' => 'question',
                'value' => 'سؤال عربي',
            ],
            [
                'translationable_type' => HelpTopic::class,
                'translationable_id' => $faq->id,
                'locale' => 'ar',
                'key' => 'answer',
                'value' => 'إجابة عربية',
            ],
        ]);

        $cacheReader = new class {
            use CacheManagerTrait;
        };

        $result = $cacheReader->cacheHelpTopicTable();

        $this->assertCount(1, $result);
        $this->assertNotNull($result->first()->translations);
        $this->assertTrue(
            $result->first()->translations->contains(
                fn (Translation $translation) => $translation->locale === 'ar'
                    && $translation->key === 'question'
                    && $translation->value === 'سؤال عربي'
            )
        );
        $this->assertTrue(
            $result->first()->translations->contains(
                fn (Translation $translation) => $translation->locale === 'ar'
                    && $translation->key === 'answer'
                    && $translation->value === 'إجابة عربية'
            )
        );
    }

    public function test_faq_endpoint_returns_translation_rows(): void
    {
        $faq = HelpTopic::query()->create([
            'type' => 'default',
            'question' => 'English question',
            'answer' => 'English answer',
            'status' => 1,
            'ranking' => 1,
        ]);

        Translation::query()->create([
            'translationable_type' => HelpTopic::class,
            'translationable_id' => $faq->id,
            'locale' => 'ar',
            'key' => 'question',
            'value' => 'سؤال عربي',
        ]);

        $response = (new GeneralController())->faq();
        $payload = $response->getData(true);

        $this->assertCount(1, $payload);
        $this->assertArrayHasKey('translations', $payload[0]);
        $this->assertSame('ar', $payload[0]['translations'][0]['locale']);
        $this->assertSame('سؤال عربي', $payload[0]['translations'][0]['value']);
    }

    private function createTables(): void
    {
        if (!Schema::hasTable('help_topics')) {
            Schema::create('help_topics', function (Blueprint $table) {
                $table->id();
                $table->string('type')->nullable();
                $table->string('question')->nullable();
                $table->text('answer')->nullable();
                $table->integer('status')->default(1);
                $table->integer('ranking')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->string('translationable_type');
                $table->unsignedBigInteger('translationable_id');
                $table->string('locale');
                $table->string('key');
                $table->string('item_index')->nullable();
                $table->text('value')->nullable();
            });
        }
    }
}
