<?php

namespace App\Http\Requests\Admin;

use App\Traits\ValidatesEnglishMultilingualInput;
use Illuminate\Foundation\Http\FormRequest;

class ShippingMethodRequest extends FormRequest
{
    use ValidatesEnglishMultilingualInput;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules():array
    {
        $englishTitleKey = $this->getEnglishFieldKey('title');
        $englishDurationKey = $this->getEnglishFieldKey('duration');

        return [
            'title' => 'required|array',
            'duration' => 'required|array',
            'cost' => 'numeric',
            $englishTitleKey => 'required|string|max:200',
            $englishDurationKey => 'required|string',
        ];
    }
    /**
     * @return array
     * Get the validation error message
     */
    public function messages(): array
    {
        return [
            'title.required'=>translate('the_title_field_is_required'),
            'duration.required'=>translate('the_duration_field_is_required'),
            'cost.numeric'=>translate('the_cost_must_be_a_number'),
            $this->getEnglishFieldKey('title') . '.required' => translate('The_title_in_english_is_required'),
            $this->getEnglishFieldKey('duration') . '.required' => translate('The_duration_in_english_is_required'),
        ];
    }
}
