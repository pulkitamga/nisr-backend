<?php

namespace Tests\Feature;

use App\Http\Controllers\RestAPI\v1\WarrantyPolicyApiController;
use App\Http\Controllers\Web\WarrantyPolicyController;
use App\Models\Policy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class WarrantyPolicyControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('VIEW_FILE_NAMES')) {
            define('VIEW_FILE_NAMES', require base_path('resources/themes/default/file_names.php'));
        }

        View::addLocation(base_path('resources/themes/default'));

        Schema::dropIfExists('translations');
        Schema::dropIfExists('policies');

        Schema::create('policies', function (Blueprint $table): void {
            $table->id();
            $table->string('version')->nullable();
            $table->date('effective_date')->nullable();
            $table->longText('content_html')->nullable();
            $table->longText('content_text')->nullable();
            $table->string('slug')->unique();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('translations');
        Schema::dropIfExists('policies');

        parent::tearDown();
    }

    public function test_web_warranty_policy_show_uses_translation_table_without_locale_column(): void
    {
        App::setLocale('ar');

        $policy = Policy::query()->create([
            'version' => '2.0',
            'effective_date' => '2026-03-10',
            'content_html' => '<p>English warranty policy</p>',
            'content_text' => 'English warranty policy',
            'slug' => 'warranty-policy-2-0',
            'published_at' => now(),
        ]);

        DB::table('translations')->insert([
            'translationable_type' => Policy::class,
            'translationable_id' => $policy->id,
            'locale' => 'ar',
            'key' => 'value',
            'value' => '<p>سياسة الضمان العربية</p>',
        ]);

        $view = (new WarrantyPolicyController())->show(Request::create('/warranty-policy', 'GET', ['locale' => 'ar']));
        $data = $view->getData();

        $this->assertSame($policy->id, $data['policy']->id);
        $this->assertSame('<p>سياسة الضمان العربية</p>', $data['policyValue']);
    }

    public function test_api_warranty_policy_show_returns_requested_locale_content_without_locale_column(): void
    {
        $policy = Policy::query()->create([
            'version' => '2.1',
            'effective_date' => '2026-03-11',
            'content_html' => '<p>English warranty policy</p>',
            'content_text' => 'English warranty policy',
            'slug' => 'warranty-policy-2-1',
            'published_at' => now(),
        ]);

        DB::table('translations')->insert([
            'translationable_type' => Policy::class,
            'translationable_id' => $policy->id,
            'locale' => 'ar',
            'key' => 'value',
            'value' => '<p>سياسة الضمان العربية</p>',
        ]);

        $response = (new WarrantyPolicyApiController())->show(
            Request::create('/api/v1/warranty-policy', 'GET', ['locale' => 'ar'])
        );

        $payload = $response->getData(true);

        $this->assertTrue($payload['success']);
        $this->assertSame('ar', $payload['policy']['locale']);
        $this->assertSame('<p>سياسة الضمان العربية</p>', $payload['policy']['content_html']);
        $this->assertSame('سياسة الضمان العربية', $payload['policy']['content_text']);
    }
}
