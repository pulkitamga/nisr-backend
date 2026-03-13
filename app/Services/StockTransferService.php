<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;

class StockTransferService
{
    use FileManagerTrait;
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
    public function getAddData(object $request): array
    {
        return [
            'from_branch_id' => $request['from_branch_id'],
            'to_branch_id' => $request['to_branch_id'],
            'transfer_date' => $request['transfer_date'],
        ];
    }

    public function getAddTransferProducts(array $products, int $stockTransferId): array
    {
        $processedProducts = [];

        foreach ($products as $product) {
            $processedProducts[] = [
                'stock_transfers_id' => $stockTransferId,
                'product_id' => $product['product_id'],
                'category_id' => $product['category_id'] ?? null, // Optional field
                'attribute' => isset($product['attribute_id']) ? '' . $product['attribute_id'] . '' : null, // Optional field
                'quantity' => $product['product_qty'] ?? 0,
                'status' => 'transferred'
            ];
        }
        return $processedProducts;
    }

    public function getUpdateManager(object $request): array
    {
        return [
            'name'              => $request['name'],
            'phone'             => $request['phone'],
        ];
    }
    public function getAddDataToLogin(object $request): array
    {
        return [
            'name'              => $request['name'],
            'phone'             => $request['phone'],
            'branch_id'         => $request['branch_id'],
            'email'             => $request['email'],
            'password'          => bcrypt($request['password']),

        ];
    }
}
