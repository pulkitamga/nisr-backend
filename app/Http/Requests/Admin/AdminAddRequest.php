<?php

namespace App\Http\Requests\Admin;

use App\Support\AdminPermissionRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminAddRequest extends FormRequest
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

        return [
            'name' => 'required',
            'role_id' => ['required', $roleRule],
            'image' => 'required',
            'email' => 'required|email|unique:admins',
            'password' => 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W)(?!.*\s).{8,}$/|same:confirm_password',
            'phone'=>'required|min:4|max:20',
            'is_supervisor' => 'nullable|boolean',
            'is_department_head' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => translate('name_is_required'),
            'role_id.required' => translate('role_id_is_required'),
            'image.required' => translate('image_is_required'),
            'email.required' => translate('email_is_required'),
            'email.email' => translate('email_must_be_valid'),
            'email.unique' => translate('email_already_in_use'),
            'password.required' => translate('password_is_required'),
            'password.regex' => translate('The_password_must_be_at_least_8_characters_long_and_contain_at_least_one_uppercase_letter').','.translate('_one_lowercase_letter').','.translate('_one_digit_').','.translate('_one_special_character').','.translate('_and_no_spaces').'.',
            'phone.required' => translate('phone_is_required'),
            'phone.max' => translate('please_ensure_your_phone_number_is_valid_and_does_not_exceed_20_characters'),
            'phone.min' => translate('phone_number_with_a_minimum_length_requirement_of_4_characters'),
        ];
    }

}
