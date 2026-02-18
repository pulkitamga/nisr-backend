<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;

class ExtraChargesService
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
    
    public function getAddData(object $request):array
    {
        return [
            'category_id'   => $request['category'],
            'charges'       => $request['charges'],
            'type'          => $request['type'],
            'status'        => 1
        ];
    }
}
