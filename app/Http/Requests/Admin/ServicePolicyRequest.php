<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $value
 */
class ServicePolicyRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lang' => 'required|array|min:1',
            'title' => 'required|array|min:1',
            'title.*' => 'required|string|max:255',
            'value' => 'required|array|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => translate('the_title_field_is_required'),
            'value.required' => translate('the_value_field_is_required'),
        ];
    }

}
