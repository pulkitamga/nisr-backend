<?php

namespace App\Services;

use App\Domain\Stock\Support\VariantMatcher;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;

class StockRequestService
{
    use FileManagerTrait;

    public function __construct(private readonly VariantMatcher $variantMatcher) {}
    /**
     * @param string $email
     * @param string $password
     * @param string|bool|null $rememberToken
     * @return bool
     */
    public function isLoginSuccessful(string $email, string $password, string|null|bool $rememberToken): bool
    {
        if (auth('seller')->attempt(['email' => $email, 'password' => $password], $rememberToken)) {
            return true;
        }
        return false;
    }

    /**
     * @param int $branchId
     * @return array
     */
    public function getInitialWalletData(int $branchId): array
    {
        return [
            'seller_id' => $branchId,
            'withdrawn' => 0,
            'commission_given' => 0,
            'total_earning' => 0,
            'pending_withdraw' => 0,
            'delivery_charge_earned' => 0,
            'collected_cash' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function logout(): void
    {
        auth()->guard('seller')->logout();
        session()->invalidate();
    }

    /**
     * @param object $request
     * @param object $branch
     * @return array
     */    
    public function getAddData(object $request):array
    {
        return [
            'from_branch_id' => $request['from_branch_id'],
            'to_branch_id' => $request['to_branch_id'],
            'transfer_date' => $request['transfer_date'],
        ];
    }
  public function getAddRequestProducts(array $products, int $stockRequestId): array
{
    $processedProducts = [];

    foreach ($products as $product) {
        $productId = $product['product_id'];
        $variationType = $product['variation_type'] ?? null;

        $productModel = \App\Models\Product::find($productId);
        $variations = $productModel->variation ? json_decode($productModel->variation, true) : [];

        $variationKey = null;
        $attributes = null;

        if ($variationType && is_array($variations)) {
            $selected = collect($variations)->first(fn($row) => $this->variantMatcher->matches($variationType, $row['type'] ?? null));
            $variationKey = $selected['variation_key'] ?? null;
            $attributes = $selected['attributes'] ?? null;
            $variationType = $selected['type'] ?? $variationType;
        }

        $processedProducts[] = [
            'stock_requests_id' => $stockRequestId,
            'product_id'        => $productId,
            'category_id'       => $product['category_id'] ?? null,
            'variation_type'    => $variationType,
            'variation_key'     => $variationKey,
            'attributes'        => $attributes,
            'quantity'          => $product['product_qty'] ?? 0,
            'status'            => 'pending',
        ];
    }

    return $processedProducts;
}

    public function getUpdateManager(object $request):array
    {
        return [
            'name'              => $request['name'],
            'phone'             => $request['phone'],
        ];
    }
     public function getAddDataToLogin(object $request):array
    {
        return [
            'name'              => $request['name'],
            'phone'             => $request['phone'],
            'branch_id'         => $request['branch_id'],
            'admin_role_id'     => 2,
            'email'             => $request['email'],
            'password'          => bcrypt($request['password']),
             
        ];
    }
}
