<?php

namespace App\Services;

class ShippingMethodService
{
    /**
     * @param object $request
     * @param string $addedBy
     * @return array
     */
    public function addShippingMethodData(object $request, string $addedBy): array
    {
        return [
            'creator_id' => $addedBy == 'seller' ? auth('seller')->id() : auth('admin')->id(),
            'creator_type' => $addedBy,
            'title' => $request['title'][getDefaultLanguageIndex($request)],
            'duration' => $request['duration'][getDefaultLanguageIndex($request)],
            'cost' => currencyConverter($request['cost']),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
