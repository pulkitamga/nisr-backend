<?php

namespace App\Services;
use App\Enums\GlobalConstant;


class VehicleService
{
    public static function getVehicleData(): array
    {
        return [
            'years' =>  GlobalConstant::YEAR_RANGE,

            'vehicles' => [
                'BMW' => ['X5', 'X6', '3 Series', '5 Series'],
                'Audi' => ['A4', 'A6', 'Q7'],
                'Tesla' => ['Model S', 'Model X', 'Model 3'],
                'Toyota' => ['Corolla', 'Camry', 'Hilux'],
            ]
        ];
    }
}
