<?php

namespace App\Http\Requests\Admin;

use App\Support\AdminPermissionRegistry;
use App\Http\Requests\Request;
use App\Traits\ResponseHandler;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Class YourModel
 *
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class CustomRoleRequest extends Request
{
    use ResponseHandler;
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role');
        if (is_object($roleId) && isset($roleId->id)) {
            $roleId = $roleId->id;
        }

        $nameRule = Rule::unique('roles', 'name')
            ->where(fn($query) => $query->where('guard_name', AdminPermissionRegistry::guard()));
        if ($roleId) {
            $nameRule = $nameRule->ignore($roleId);
        }

        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_\- ]+$/', $nameRule],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => [
                'required',
                'string',
                Rule::exists('permissions', 'name')
                    ->where(fn($query) => $query->where('guard_name', AdminPermissionRegistry::guard())),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => translate('the_Role_field_is_required!'),
            'name.unique' => translate('this_role_already_exists'),
            'name.regex' => translate('role_name_must_be_english_only'),
            'permissions.required' => translate('select_minimum_one_permission'),
            'permissions.array' => translate('select_minimum_one_permission'),
            'permissions.min' => translate('select_minimum_one_permission'),
            'permissions.*.exists' => translate('invalid_permission_selected'),
        ];
    }
}
