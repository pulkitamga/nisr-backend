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

        Route::middleware('web')
            ->get('/get-areas', [ShippingAjaxController::class, 'getAreas'])
            ->name('get.areas');
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
