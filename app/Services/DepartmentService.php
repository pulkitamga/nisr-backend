<?php

namespace App\Services;

use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;

class DepartmentService
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

    public function logout(): void
    {
        auth()->guard('seller')->logout();
        session()->invalidate();
    }    
    
    public function getAddData(object $request):array
    {
        $data = [
            'name' => $request['name'],
            'status' => $request['status'] == '0' ? '0' : '1',
        ];

        // Department head is now managed from Employee form.
        // Keep backward compatibility if head_id is still posted from legacy UI.
        if (method_exists($request, 'has') && $request->has('head_id')) {
            $data['head_id'] = !empty($request['head_id']) ? (int)$request['head_id'] : null;
        }

        return $data;
    }

    public function getAddDepartmentUsers(object $request, int $iDepartmentId):array
    {
        return [
            'user_type' => $request['user_type'],
            'name' => $request['user_name'],
            'email' => $request['email'],
            'email_verified_at' => date('Y-m-d H:i:s'),
            'department_id' => $iDepartmentId,
            'password' => bcrypt($request['password']),
            'status' => $request['status'] == 'inactive' ? 'inactive' : 'active',
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
