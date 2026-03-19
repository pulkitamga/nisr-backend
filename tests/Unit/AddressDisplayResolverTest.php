<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\City;
use App\Models\State;
use App\Support\AddressDisplayResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AddressDisplayResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
    }

    public function test_it_resolves_country_codes_and_numeric_location_ids(): void
    {
        app()->setLocale('en');

        $state = State::query()->create([
            'country' => 'Egypt',
            'name' => 'Alexandria',
        ]);

        $city = City::query()->create([
            'state_id' => $state->id,
            'name' => 'Alexandria',
        ]);

        $area = Area::query()->create([
            'city_id' => $city->id,
            'name' => 'Roushdy',
        ]);

        $resolved = AddressDisplayResolver::resolve((object)[
            'country' => 'EG',
            'state' => (string)$state->id,
            'city' => (string)$city->id,
            'area' => (string)$area->id,
        ]);

        $this->assertSame('Egypt', $resolved['country']);
        $this->assertSame('Alexandria', $resolved['state']);
        $this->assertSame('Alexandria', $resolved['city']);
        $this->assertSame('Roushdy', $resolved['area']);
    }

    public function test_it_translates_supported_location_names_for_active_locale(): void
    {
        app()->setLocale('ar');

        $resolved = AddressDisplayResolver::resolve((object)[
            'country' => 'EG',
            'state' => 'Alexandria',
            'city' => 'Alexandria',
            'area' => 'Roushdy',
        ]);

        $this->assertSame('مصر', $resolved['country']);
        $this->assertSame('الإسكندرية', $resolved['state']);
        $this->assertSame('الإسكندرية', $resolved['city']);
        $this->assertSame('Roushdy', $resolved['area']);
    }

    private function createTables(): void
    {
        if (!Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->id();
                $table->string('country')->nullable();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('state_id')->nullable();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('areas')) {
            Schema::create('areas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('city_id')->nullable();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }
    }
}
