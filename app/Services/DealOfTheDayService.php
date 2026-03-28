<?php

namespace App\Services;

use App\Traits\FileManagerTrait;

class DealOfTheDayService
{
    use FileManagerTrait;

    public function getAddData(object $request, object $product): array
    {
        return [
            'title' => $request['title'][getDefaultLanguageIndex($request)],
            'discount' => $product['discount_type'] == 'amount' ? usdToDefaultCurrency(amount:$product['discount']) : $product['discount'],
            'discount_type' => $product['discount_type'],
            'product_id' => $request['product_id'],
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function getUpdateData(object $request, object $product): array
    {
        return [
            'title' => $request['title'][getDefaultLanguageIndex($request)],
            'discount' => $product['discount_type'] == 'amount' ? usdToDefaultCurrency(amount:$product['discount']) : $product['discount'],
            'discount_type' => $product['discount_type'],
            'product_id' => $request['product_id'],
            'status' => 0,
            'updated_at' => now(),
        ];
    }



}
