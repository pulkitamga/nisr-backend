<?php

namespace App\Http\Requests\Admin;

use App\Traits\ValidatesEnglishMultilingualInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


/**
 * @property int $id
 * @property string $name
 * @property string $image
 * @property int $status
 */
class BrandUpdateRequest extends FormRequest
{
    use ValidatesEnglishMultilingualInput;

    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $englishFieldKey = $this->getEnglishFieldKey('name');

        return [
            'name' => 'required|array',
            'image' => 'image',
            $englishFieldKey => [
                'required',Rule::unique('brands', 'name')->ignore($this->route('id')),
            ],
        ];
    }

    public function messages(): array
    {
        $englishFieldKey = $this->getEnglishFieldKey('name');

        return [
            'name.required' => translate('the_name_field_is_required'),
            $englishFieldKey . '.required' => translate('The_name_in_english_is_required'),
            $englishFieldKey . '.unique' => translate('The_brand_has_already_been_taken'),
        ];
    }

}
