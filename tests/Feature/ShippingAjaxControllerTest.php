<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\ShippingAjaxController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ShippingAjaxControllerTest extends TestCase
{
    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('pdo_sqlite extension is not available in this environment.');
        }

        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translationable_type');
            $table->unsignedBigInteger('translationable_id');
            $table->string('locale');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('city_id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('country')->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('state_id');
            $table->string('name');
            $table->timestamps();
        });

        Route::middleware('web')
            ->get('/get-states', [ShippingAjaxController::class, 'getStates'])
            ->name('get.states');
        Route::middleware('web')
            ->get('/get-cities', [ShippingAjaxController::class, 'getCities'])
            ->name('get.cities');
        Route::middleware('web')
            ->get('/get-areas', [ShippingAjaxController::class, 'getAreas'])
            ->name('get.areas');
    }

    public function test_get_states_returns_all_states_from_business_settings_for_the_selected_country(): void
    {
        DB::table('states')->insert([
            ['id' => 10, 'country' => 'EG', 'name' => 'Alexandria'],
            ['id' => 11, 'country' => 'EG', 'name' => 'Greater Cairo'],
            ['id' => 12, 'country' => 'SA', 'name' => 'Riyadh'],
        ]);

        $response = $this->getJson(route('get.states', ['country' => 'EG']));

        $response->assertOk()
            ->assertJson([
                'states' => [
                    ['id' => 10, 'name' => 'Alexandria'],
                    ['id' => 11, 'name' => 'Greater Cairo'],
                ],
            ])
            ->assertJsonMissing([
                'id' => 12,
                'name' => 'Riyadh',
            ]);
    }

    public function test_get_cities_returns_all_cities_from_business_settings_for_the_selected_state(): void
    {
        DB::table('cities')->insert([
            ['id' => 20, 'state_id' => 10, 'name' => 'Alexandria'],
            ['id' => 21, 'state_id' => 10, 'name' => 'Borg El Arab'],
            ['id' => 22, 'state_id' => 11, 'name' => '6th of October'],
        ]);

        $response = $this->getJson(route('get.cities', ['state_id' => 10]));

        $response->assertOk()
            ->assertJson([
                'cities' => [
                    ['id' => 20, 'name' => 'Alexandria'],
                    ['id' => 21, 'name' => 'Borg El Arab'],
                ],
            ])
            ->assertJsonMissing([
                'id' => 22,
                'name' => '6th of October',
            ]);
    }

    public function test_get_areas_returns_all_admin_defined_areas_for_the_selected_city(): void
    {
        DB::table('areas')->insert([
            ['city_id' => 10, 'name' => 'As Soyouf'],
            ['city_id' => 10, 'name' => 'Sidi Bishr'],
            ['city_id' => 22, 'name' => 'Nasr City'],
        ]);

        $response = $this->getJson(route('get.areas', ['city_id' => 10]));

        $response->assertOk()
            ->assertJson([
                'areas' => [
                    ['id' => 1, 'name' => 'As Soyouf'],
                    ['id' => 2, 'name' => 'Sidi Bishr'],
                ],
            ])
            ->assertJsonMissing([
                'id' => 3,
                'name' => 'Nasr City',
            ]);
    }
}
