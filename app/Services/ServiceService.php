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
    private function normalizeServiceIdValue(mixed $serviceId): ?string
    {
        if (is_array($serviceId)) {
            foreach ($serviceId as $candidate) {
                $resolvedCandidate = $this->normalizeServiceIdValue($candidate);

                if ($resolvedCandidate !== null && $resolvedCandidate !== '') {
                    return $resolvedCandidate;
                }
            }

            return null;
        }

        if (!is_scalar($serviceId)) {
            return null;
        }

        $normalizedValue = trim((string) $serviceId);

        return $normalizedValue !== '' ? $normalizedValue : null;
    }

    private function normalizeIncludedParts(?string $partsIncluded): string
    {
        $parts = array_values(array_filter(
            array_map('trim', explode(',', (string)$partsIncluded)),
            static fn(string $part): bool => $part !== ''
        ));

        return json_encode($parts);
    }

    public function getServiceData(Request $request, int $productId): array
    {

        $enIndex = getDefaultLanguageIndex($request);

        return [
            'product_id' => $productId,
            'service_id' => $this->normalizeServiceIdValue($request->input('service_id')),
            'title' => $request['service_tittle'][$enIndex],
            'base_price_inshop' => $request->input('base_price_inshop'),
            'base_price_mobile' => $request->input('base_price_mobile'),
            'parts_cost' => $request->input('parts_cost'),
            'included_km_mobile' => $request->input('included_km_mobile'),
            'travel_fee_per_km' => $request->input('travel_fee_per_km'),
            'labor_hours' => $request->input('labor_hours'),
            'parts_included' => $this->normalizeIncludedParts($request['parts_included'][$enIndex] ?? null),
            'call_center_flag' => $request->has('call_center_flag') ? 1 : 0,
        ];
    }
    public function getUpdateServiceData(object $request): array
    {
        $enIndex = getDefaultLanguageIndex($request);

        $dataArray = [
            'service_id' => $this->normalizeServiceIdValue($request->input('service_id')),
            'title' => $request['service_tittle'][$enIndex],
            'base_price_inshop' => $request->input('base_price_inshop'),
            'base_price_mobile' => $request->input('base_price_mobile'),
            'parts_cost' => $request->input('parts_cost'),
            'included_km_mobile' => $request->input('included_km_mobile'),
            'travel_fee_per_km' => $request->input('travel_fee_per_km'),
            'labor_hours' => $request->input('labor_hours'),
            'parts_included' => $this->normalizeIncludedParts($request['parts_included'][$enIndex] ?? null),

            'call_center_flag' => $request->has('call_center_flag') ? 1 : 0,
        ];

        return $dataArray;
    }
}
