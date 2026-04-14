<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\City;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class StateCityViewRegressionTest extends TestCase
{
    public function test_city_column_falls_back_when_area_city_relation_is_missing(): void
    {
        $area = new Area(['name' => 'Nasr City']);
        $area->setRelation('city', null);

        $html = Blade::render("{{ \$area->city?->getTranslatedField('name') ?? '-' }}", [
            'area' => $area,
        ]);

        $this->assertSame('-', trim($html));
    }

    public function test_state_column_falls_back_when_city_state_relation_is_missing(): void
    {
        $city = new City(['name' => 'Cairo']);
        $city->setRelation('state', null);

        $html = Blade::render("{{ \$city->state?->getTranslatedField('name') ?? '-' }}", [
            'city' => $city,
        ]);

        $this->assertSame('-', trim($html));
    }
}
