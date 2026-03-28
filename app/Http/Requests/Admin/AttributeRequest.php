<?php

namespace App\Http\Requests\Admin;

use App\Traits\ResponseHandler;
use App\Traits\ValidatesEnglishMultilingualInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

/**
 * Class Attribute
 *
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class AttributeRequest extends FormRequest
{
    use ResponseHandler, ValidatesEnglishMultilingualInput;

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
            $englishFieldKey => 'required'
        ];
    }

    public function messages(): array
    {
        $englishFieldKey = $this->getEnglishFieldKey('name');

        return [
            'name.required' => translate('the_name_field_is_required!'),
            $englishFieldKey . '.required' => translate('The_name_in_english_is_required') . '!',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $this->validateEnglishMultilingualFields($validator, [
                    'name' => ['message' => translate('The_name_in_english_is_required') . '!'],
                ]);
            }
        ];
    }

}
