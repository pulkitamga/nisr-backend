<?php

namespace App\Services;

class ShippingMethodAreaService
{
    /**
     * @param object $request
     * @param string $addedBy
     * @return array
     */
    public function addShippingMethodAreaData(object $request, string $addedBy): array
    {
        return [
            'creator_id' => $addedBy == 'seller' ? auth('seller')->id() : auth('admin')->id(),
            'creator_type' => $addedBy,
            'country' => $request['country'],
            'state_id' => $request['state'],
            'city_id' => $request['city'],
            'area' => $request['area'],
            'duration' => $request['duration'],
            'cost' => $request['cost'] ? currencyConverter($request['cost']) : 0,
            'coordinates' =>  $request['coordinates'],
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
