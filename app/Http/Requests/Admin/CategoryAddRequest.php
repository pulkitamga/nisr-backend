<?php

namespace App\Http\Requests\Admin;

use App\Traits\ValidatesEnglishMultilingualInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $icon
 * @property int $parent_id
 * @property int $position
 * @property int $home_status
 * @property int $priority
 */
class CategoryAddRequest extends FormRequest
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
            $englishFieldKey => 'required',
            'image' => 'required',
            'priority'=>'required'
        ];
    }

    public function messages(): array
    {
        $englishFieldKey = $this->getEnglishFieldKey('name');

        return [
            'name.required' => translate('category_name_is_required'),
            $englishFieldKey . '.required' => translate('The_name_in_english_is_required'),
            'image.required' => translate('category_image_is_required'),
            'priority.required' => translate('category_priority_is_required'),
        ];
    }

}
