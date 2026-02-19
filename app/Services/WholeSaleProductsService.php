<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\WholeSaleProducts;


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
