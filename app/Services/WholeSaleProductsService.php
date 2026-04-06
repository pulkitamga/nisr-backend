<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Product;
use App\Models\WholeSaleProducts;
use InvalidArgumentException;


class WholeSaleProductsService
{
    use FileManagerTrait;
    /**
     * @param string $email
     * @param string $password
     * @param string|bool|null $rememberToken
     * @return bool
     */


    public function logout(): void
    {
        auth()->guard('seller')->logout();
        session()->invalidate();
    }

  public function getAddData(object $request): array
{
    $variationType = trim((string)($request->variation_type ?? ''));
    $variationKey = trim((string)($request->variation_key ?? ''));

    if ($variationType === '') {
        $variationType = WholeSaleProducts::extractTypeFromVariationKey($variationKey) ?? '';
    }

    $normalizedVariationKey = WholeSaleProducts::normalizeVariationKey(
        $variationType !== '' ? $variationType : null,
        $variationKey !== '' ? $variationKey : null
    );

    return [
        'category_id'       => $request->category_id,
        'sub_category_id'   => $request->sub_category_id,
        'product_id'        => $request->product_id,
        'variation_type'    => $variationType !== '' ? $variationType : null,
        'variation_key'     => $normalizedVariationKey,
        'tax'               => $request->tax ?? '0',
    ];
}
    public function addProductRangePrices(array $min_qty, int $product_id): array
    {
        $processPrices = [];

        foreach ($min_qty as $range) {
            $processPrices[] = [
                'product_id' => $product_id,
                'min_qty' => $range['min_qty'] ?? 0,
                'max_qty' => $range['max_qty'] ?? 0,
                'price_per_piece' => $range['price_per_piece'] ?? 0,
                'status' => 0
            ];
        }
        return $processPrices;
    }

    public function buildValidatedPriceRanges(
        Product $product,
        ?string $variationType,
        ?string $variationKey,
        array $payload
    ): array {
        $minQtys = array_values($payload['min_qty'] ?? []);

        if ($minQtys === []) {
            return [];
        }

        $tiers = array_values($payload['tier'] ?? []);
        $maxQtys = array_values($payload['max_qty'] ?? []);
        $discounts = array_values($payload['discount'] ?? []);
        $expectedCount = count($minQtys);

        if (
            count($tiers) !== $expectedCount
            || count($maxQtys) !== $expectedCount
            || count($discounts) !== $expectedCount
        ) {
            throw new InvalidArgumentException(translate('wholesale_price_rows_are_incomplete'));
        }

        $unitPrice = round((float) $product->getVariationPrice($variationType, $variationKey), 2);
        $priceRanges = [];

        foreach ($minQtys as $index => $minQtyRaw) {
            $tier = trim((string) ($tiers[$index] ?? ''));
            $discount = (float) ($discounts[$index] ?? 0);
            $minQty = filter_var($minQtyRaw, FILTER_VALIDATE_INT);
            $maxQtyRaw = $maxQtys[$index] ?? null;
            $maxQty = null;

            if ($tier === '') {
                throw new InvalidArgumentException(translate('wholesale_price_rows_are_incomplete'));
            }

            if ($minQty === false || $minQty < 1) {
                throw new InvalidArgumentException(translate('minimum_quantity_must_be_at_least_1'));
            }

            if ($maxQtyRaw !== null && $maxQtyRaw !== '') {
                $maxQty = filter_var($maxQtyRaw, FILTER_VALIDATE_INT);

                if ($maxQty === false || $maxQty <= $minQty) {
                    throw new InvalidArgumentException(translate('maximum_quantity_must_be_greater_than_minimum_quantity'));
                }
            }

            if ($discount < 0 || $discount > 100) {
                throw new InvalidArgumentException(translate('discount_must_be_between_0_and_100'));
            }

            $priceRanges[] = [
                'tier' => $tier,
                'min_qty' => $minQty,
                'max_qty' => $maxQty,
                'price_per_piece' => round($unitPrice - (($unitPrice * $discount) / 100), 2),
                'discount' => $discount,
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        usort($priceRanges, fn(array $left, array $right) => $left['min_qty'] <=> $right['min_qty']);

        for ($index = 0; $index < count($priceRanges) - 1; $index++) {
            $currentMax = $priceRanges[$index]['max_qty'] ?? PHP_INT_MAX;
            $nextMin = $priceRanges[$index + 1]['min_qty'];

            if ($nextMin <= $currentMax) {
                throw new InvalidArgumentException(translate('wholesale_price_ranges_cannot_overlap'));
            }
        }

        return $priceRanges;
    }


    public function getMerchantProfile(int $userId): array
    {
        $user = User::with([
            'wholesalerBusiness',
            'wholesalerBusiness.wholesaleProducts.category',
            'wholesalerBusiness.wholesaleProducts.subcategory',
            'wholesalerBusiness.wholesaleProducts.price_list'
        ])->findOrFail($userId);

        return [
            'merchant_id'     => $user->id,
            'merchant_name'   => $user->name,
            'tier'            => $user->tier ?? 'N/A', // Update later based on logic
            'business_name'   => $user->wholesalerBusiness->company_name ?? 'N/A',
            'trade_name'      => $user->wholesalerBusiness->trade_name ?? 'N/A',
            'registration_no' => $user->wholesalerBusiness->registration_number ?? 'N/A',
            'tax_id'          => $user->wholesalerBusiness->tax_id ?? 'N/A',
            'vat_no'          => $user->wholesalerBusiness->vat_number ?? 'N/A',
            'products'        => $user->wholesalerBusiness->wholesaleProducts->map(function ($product) {
                return [
                    'product_id'   => $product->product_id,
                    'category'     => $product->category->name ?? 'N/A',
                    'subcategory'  => $product->subcategory->name ?? 'N/A',
                    'MOQ'          => $product->price_list->min('min_qty') ?? 'N/A',
                ];
            })->toArray(),
        ];
    }
}
