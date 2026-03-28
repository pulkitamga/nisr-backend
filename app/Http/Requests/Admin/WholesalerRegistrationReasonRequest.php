<?php

namespace App\Http\Requests\Admin;

use App\Traits\ValidatesEnglishMultilingualInput;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WholesalerRegistrationReasonRequest extends FormRequest
{
    use ValidatesEnglishMultilingualInput;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $englishTitleKey = $this->getEnglishFieldKey('title');

        return [
            'title' => ['required', 'array'],
            $englishTitleKey => ['required', 'string'],
            'priority' => ['required', 'integer'],
        ];
    }
    public function messages(): array
    {
        return [
            'title.required' => translate('the_title_field_is_required'),
            $this->getEnglishFieldKey('title') . '.required' => translate('The_title_in_english_is_required'),
            'priority.required' => translate('please_select_priority'),
        ];
    }
}
