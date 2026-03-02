<?php

namespace App\Http\Requests\Admin;

use App\Support\AdminPermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminUpdateRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleRule = Rule::exists('roles', 'id')
            ->where(fn($query) => $query->where('guard_name', AdminPermissionRegistry::guard()));
        if (Schema::hasColumn('roles', 'status')) {
            $roleRule = $roleRule->where(fn($query) => $query->where('status', 1));
        }

        $rules = [
            'name' => 'required',
            'role_id' => ['required', $roleRule],
            'is_supervisor' => 'nullable|boolean',
            'is_department_head' => 'nullable|boolean',
            'email' => [
                'required',
                'email',
                Rule::unique('admins', 'email')->ignore($this->route('id')),
            ],
        ];
        if ($this['password']) {
            $rules['password'] = 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W)(?!.*\s).{8,}$/|same:confirm_password';
        }
        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => translate('name_is_required'),
            'role_id.required' => translate('role_id_is_required'),
            'email.required' => translate('email_is_required'),
            'email.email' => translate('email_must_be_valid'),
            'email.unique' => translate('email_already_taken'),
            'password.regex' => translate('The_password_must_be_at_least_8_characters_long_and_contain_at_least_one_uppercase_letter').','.translate('_one_lowercase_letter').','.translate('_one_digit_').','.translate('_one_special_character').','.translate('_and_no_spaces').'.',
        ];
    }

}
