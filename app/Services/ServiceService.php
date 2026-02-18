<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\ViewPaths\Admin\Product;
use App\Events\RestockProductNotificationEvent;
use App\Models\Color;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Boolean;
use Rap2hpoutre\FastExcel\FastExcel;
use function React\Promise\all;
use Illuminate\Http\Request;


class ServiceService
{

    public function getServiceData(Request $request, int $productId): array
    {

        $enIndex = array_search('en', $request['lang']);

        return [
            'product_id' => $productId,
            'service_id' => $request->input('service_id'),
            'title' => $request['service_tittle'][$enIndex],
            'base_price_inshop' => $request->input('base_price_inshop'),
            'base_price_mobile' => $request->input('base_price_mobile'),
            'parts_cost' => $request->input('parts_cost'),
            'included_km_mobile' => $request->input('included_km_mobile'),
            'travel_fee_per_km' => $request->input('travel_fee_per_km'),
            'labor_hours' => $request->input('labor_hours'),
            'parts_included' => json_encode(explode(',', $request['parts_included'][$enIndex])),
            'call_center_flag' => $request->has('call_center_flag') ? 1 : 0,
        ];
    }
    public function getUpdateServiceData(object $request): array
    {
        $enIndex = array_search('en', $request['lang']);

        $dataArray = [
            'service_id' => $request->input('service_id'),
            'title' => $request['service_tittle'][$enIndex],
            'base_price_inshop' => $request->input('base_price_inshop'),
            'base_price_mobile' => $request->input('base_price_mobile'),
            'parts_cost' => $request->input('parts_cost'),
            'included_km_mobile' => $request->input('included_km_mobile'),
            'travel_fee_per_km' => $request->input('travel_fee_per_km'),
            'labor_hours' => $request->input('labor_hours'),
            'parts_included' => json_encode(
                array_map('trim', explode(',', $request['parts_included'][$enIndex]))
            ),

            'call_center_flag' => $request->has('call_center_flag') ? 1 : 0,
        ];

        return $dataArray;
    }
}
