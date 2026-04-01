<?php

namespace Tests\Feature;

use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VehicleTranslationLocaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('translations');
        Schema::dropIfExists('vehicle_models');
        Schema::dropIfExists('vehicle_makes');

        Schema::create('vehicle_makes', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_models', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('make_id')->nullable();
            $table->string('name')->nullable();
            $table->string('year')->nullable();
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
        Schema::dropIfExists('vehicle_models');
        Schema::dropIfExists('vehicle_makes');

        parent::tearDown();
    }

    public function test_vehicle_labels_follow_active_locale_and_unit_labels_are_translated(): void
    {
        $make = VehicleMake::query()->create(['name' => 'Toyota']);
        $model = VehicleModel::query()->create([
            'make_id' => $make->id,
            'name' => 'Corolla',
        ]);

        DB::table('translations')->insert([
            [
                'translationable_type' => VehicleMake::class,
                'translationable_id' => $make->id,
                'locale' => 'ar',
                'key' => 'name',
                'value' => 'تويوتا',
            ],
            [
                'translationable_type' => VehicleModel::class,
                'translationable_id' => $model->id,
                'locale' => 'ar',
                'key' => 'name',
                'value' => 'كورولا',
            ],
        ]);

        $loadedMake = VehicleMake::query()->firstOrFail();
        $loadedModel = VehicleModel::query()->firstOrFail();

        App::setLocale('ar');

        $this->assertSame('تويوتا', $loadedMake->name);
        $this->assertSame('كورولا', $loadedModel->name);
        $this->assertSame('تويوتا', getVehicleMakeLabel('Toyota'));
        $this->assertSame('كورولا', getVehicleModelLabel('Corolla'));
        $this->assertSame('وحدة', getUnitLabel('pc'));
    }
}
