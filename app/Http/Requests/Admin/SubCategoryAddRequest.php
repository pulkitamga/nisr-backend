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
class SubCategoryAddRequest extends FormRequest
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
            'priority' => 'required',
            'parent_id'=>'required'
        ];
    }

    public function messages(): array
    {
        $englishFieldKey = $this->getEnglishFieldKey('name');

        return [
            'name.required' => translate('category_name_is_required'),
            $englishFieldKey . '.required' => translate('The_name_in_english_is_required'),
            'priority.required' => translate('category_priority_is_required'),
            'parent_id.required' => translate('Main_Category_is_required'),
        ];
    }

}
