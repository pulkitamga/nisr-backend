<?php

namespace App\Services;

use App\Models\Branch;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;

class BranchService
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
     * @return array
     */
    public function getFreeDeliveryOverAmountData(object $request): array
    {
        return [
            'free_delivery_status' => $request['free_delivery_status'] == 'on' ? 1 : 0,
            'free_delivery_over_amount' => currencyConverter($request['free_delivery_over_amount'], 'usd'),
        ];
    }

    /**
     * @return array[minimum_order_amount: float|int]
     */
    public function getMinimumOrderAmount(object $request): array
    {
        return [
            'minimum_order_amount' => currencyConverter($request['minimum_order_amount'], 'usd')
        ];
    }

    /**
     * @param object $request
     * @param object $branch
     * @return array
     */
    public function getVendorDataForUpdate(object $request, object $branch): array
    {
        $image = $request['image'] ? $this->update(dir: 'seller/', oldImage: $branch['image'], format: 'webp', image: $request->file('image')) : $branch['image'];
        return [
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'phone' => $request['phone'],
            'image' => $image,
        ];
    }


    public function getAddData(object $request): array
    {
        $requestLanguages = collect(data_get($request, 'lang', []))
            ->filter(fn ($locale) => is_string($locale) && $locale !== '')
            ->values();
        $defaultLocale = $requestLanguages->contains(config('app.locale'))
            ? config('app.locale')
            : ($requestLanguages->first() ?? 'en');
        $defaultLangIndex = $requestLanguages->search($defaultLocale);
        $defaultLangIndex = $defaultLangIndex === false ? 0 : $defaultLangIndex;

        return [
            'branch_name'       => $request['branch_name'][$defaultLangIndex],
            'branch_country'    => $request['branch_country'],
            'branch_state'    => $request['branch_state'],
            'branch_address'    => $request['branch_address'][$defaultLangIndex],
            'branch_zipcode'    => $request['zipcode'],
            'sun_branch_hours_from' => $request['sun_branch_hours_from'],
            'sun_branch_hours_to'   => $request['sun_branch_hours_to'],
            'mon_branch_hours_from' => $request['mon_branch_hours_from'],
            'mon_branch_hours_to'   => $request['mon_branch_hours_to'],
            'tue_branch_hours_from' => $request['tue_branch_hours_from'],
            'tue_branch_hours_to'   => $request['tue_branch_hours_to'],
            'wed_branch_hours_from' => $request['wed_branch_hours_from'],
            'wed_branch_hours_to'   => $request['wed_branch_hours_to'],
            'thu_branch_hours_from' => $request['thu_branch_hours_from'],
            'thu_branch_hours_to'   => $request['thu_branch_hours_to'],
            'fri_branch_hours_from' => $request['fri_branch_hours_from'],
            'fri_branch_hours_to'   => $request['fri_branch_hours_to'],
            'sat_branch_hours_from' => $request['sat_branch_hours_from'],
            'sat_branch_hours_to'   => $request['sat_branch_hours_to'],
            'branch_latitude'   => $request['branch_latitude'],
            'branch_longitude'  => $request['branch_longitude'],
            'phone'             => $request['phone'],
            'email'             => $request['email'],
            'status'            => $request['status'] == 'active' ? 'active' : 'inactive',
            'shipping_method_city'  => $request['shipping_method_city'],
            'manager_id'        => $request['manager_id'],
        ];
    }

    public function syncAreaRelations(Branch $branch, object $request): void
    {
        $branch->shippingAreas()->sync($this->normalizeAreaIds(data_get($request, 'shipping_methods_area', [])));
        $branch->deliveryRestrictions()->sync($this->normalizeAreaIds(data_get($request, 'delivery_restriction', [])));
    }

    private function normalizeAreaIds(mixed $areaIds): array
    {
        return collect(is_array($areaIds) ? $areaIds : [$areaIds])
            ->map(fn ($id) => (int)$id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function getAddManager(object $request): array
    {
        return [
            'name'              => $request['name'],
            'phone'             => $request['phone'],
            'email'             => $request['email'],
            'branch_id'         => $request['branch_id'],
            'status'            => $request['status'] == 'inactive' ? 'inactive' : 'active',
        ];
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
