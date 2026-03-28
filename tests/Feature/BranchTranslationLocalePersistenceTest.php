<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Translation;
use App\Repositories\TranslationRepository;
use App\Services\BranchService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BranchTranslationLocalePersistenceTest extends TestCase
{
    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        parent::setUp();

        config([
            'app.locale' => 'ar',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createTestSchema();
    }

    public function test_branch_translation_repository_skips_configured_default_locale_not_hardcoded_english(): void
    {
        $request = request()->replace([
            'lang' => ['ar', 'en'],
            'branch_name' => ['فرع أكتوبر', 'October Branch'],
            'branch_address' => ['الحي السادس', 'Sixth District'],
            'branch_country' => 'EG',
            'branch_state' => 'Cairo',
            'zipcode' => '12345',
            'sun_branch_hours_from' => '09:00',
            'sun_branch_hours_to' => '17:00',
            'mon_branch_hours_from' => '09:00',
            'mon_branch_hours_to' => '17:00',
            'tue_branch_hours_from' => '09:00',
            'tue_branch_hours_to' => '17:00',
            'wed_branch_hours_from' => '09:00',
            'wed_branch_hours_to' => '17:00',
            'thu_branch_hours_from' => '09:00',
            'thu_branch_hours_to' => '17:00',
            'fri_branch_hours_from' => '09:00',
            'fri_branch_hours_to' => '17:00',
            'sat_branch_hours_from' => '09:00',
            'sat_branch_hours_to' => '17:00',
            'branch_latitude' => '30.0444',
            'branch_longitude' => '31.2357',
            'phone' => '01000000000',
            'email' => 'october@example.com',
            'status' => 'active',
            'shipping_method_city' => 1,
            'manager_id' => 22,
        ]);

        $branchPayload = (new BranchService())->getAddData($request);

        $branch = Branch::query()->create($branchPayload);

        $repository = new TranslationRepository(new Translation());
        $repository->add($request, Branch::class, $branch->id);

        $translations = Translation::query()
            ->where('translationable_type', Branch::class)
            ->where('translationable_id', $branch->id)
            ->orderBy('key')
            ->get(['locale', 'key', 'value'])
            ->map(fn (Translation $translation) => [
                'locale' => $translation->locale,
                'key' => $translation->key,
                'value' => $translation->value,
            ])
            ->all();

        $this->assertSame('فرع أكتوبر', $branch->branch_name);
        $this->assertSame('الحي السادس', $branch->branch_address);
        $this->assertSame([
            ['locale' => 'en', 'key' => 'branch_address', 'value' => 'Sixth District'],
            ['locale' => 'en', 'key' => 'branch_name', 'value' => 'October Branch'],
        ], $translations);
    }

    public function test_branch_translation_repository_uses_configured_english_locale_even_when_arabic_is_first_in_request(): void
    {
        config(['app.locale' => 'en']);

        $request = request()->replace([
            'lang' => ['ar', 'en'],
            'branch_name' => ['اكتوبر', 'October'],
            'branch_address' => ['ميدان الحصري', 'El Hosary Square'],
            'branch_country' => 'EG',
            'branch_state' => 'Cairo',
            'zipcode' => '12345',
            'sun_branch_hours_from' => '09:00',
            'sun_branch_hours_to' => '17:00',
            'mon_branch_hours_from' => '09:00',
            'mon_branch_hours_to' => '17:00',
            'tue_branch_hours_from' => '09:00',
            'tue_branch_hours_to' => '17:00',
            'wed_branch_hours_from' => '09:00',
            'wed_branch_hours_to' => '17:00',
            'thu_branch_hours_from' => '09:00',
            'thu_branch_hours_to' => '17:00',
            'fri_branch_hours_from' => '09:00',
            'fri_branch_hours_to' => '17:00',
            'sat_branch_hours_from' => '09:00',
            'sat_branch_hours_to' => '17:00',
            'branch_latitude' => '30.0444',
            'branch_longitude' => '31.2357',
            'phone' => '01000000000',
            'email' => 'october@example.com',
            'status' => 'active',
            'shipping_method_city' => 1,
            'manager_id' => 22,
        ]);

        $branchPayload = (new BranchService())->getAddData($request);

        $branch = Branch::query()->create($branchPayload);

        $repository = new TranslationRepository(new Translation());
        $repository->add($request, Branch::class, $branch->id);

        $translations = Translation::query()
            ->where('translationable_type', Branch::class)
            ->where('translationable_id', $branch->id)
            ->orderBy('key')
            ->get(['locale', 'key', 'value'])
            ->map(fn (Translation $translation) => [
                'locale' => $translation->locale,
                'key' => $translation->key,
                'value' => $translation->value,
            ])
            ->all();

        $this->assertSame('October', $branch->branch_name);
        $this->assertSame('El Hosary Square', $branch->branch_address);
        $this->assertSame([
            ['locale' => 'ar', 'key' => 'branch_address', 'value' => 'ميدان الحصري'],
            ['locale' => 'ar', 'key' => 'branch_name', 'value' => 'اكتوبر'],
        ], $translations);
    }

    private function createTestSchema(): void
    {
        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('branch_name')->nullable();
            $table->string('branch_country')->nullable();
            $table->string('branch_state')->nullable();
            $table->string('branch_address')->nullable();
            $table->string('branch_zipcode')->nullable();
            $table->string('sun_branch_hours_from')->nullable();
            $table->string('sun_branch_hours_to')->nullable();
            $table->string('mon_branch_hours_from')->nullable();
            $table->string('mon_branch_hours_to')->nullable();
            $table->string('tue_branch_hours_from')->nullable();
            $table->string('tue_branch_hours_to')->nullable();
            $table->string('wed_branch_hours_from')->nullable();
            $table->string('wed_branch_hours_to')->nullable();
            $table->string('thu_branch_hours_from')->nullable();
            $table->string('thu_branch_hours_to')->nullable();
            $table->string('fri_branch_hours_from')->nullable();
            $table->string('fri_branch_hours_to')->nullable();
            $table->string('sat_branch_hours_from')->nullable();
            $table->string('sat_branch_hours_to')->nullable();
            $table->string('branch_latitude')->nullable();
            $table->string('branch_longitude')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('shipping_method_city')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
}
