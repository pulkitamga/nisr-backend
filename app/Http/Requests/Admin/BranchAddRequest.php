<?php

namespace App\Http\Requests\Admin;

use App\Traits\ResponseHandler;
use App\Traits\ValidatesEnglishMultilingualInput;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BranchAddRequest extends FormRequest
{
    use ResponseHandler, ValidatesEnglishMultilingualInput;

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
        $englishBranchNameKey = $this->getEnglishFieldKey('branch_name');
        $englishBranchAddressKey = $this->getEnglishFieldKey('branch_address');

        return [
            'branch_name'   => 'required|array',
            'phone'         => 'required|unique:branches|max:20|min:4',
            'email'         => 'unique:branches',
            'branch_country' => 'required',
            'branch_state' => 'required',
            'branch_address' => 'required|array',
            $englishBranchNameKey => 'required|string|max:255',
            $englishBranchAddressKey => 'required|string|max:255',
            /* 
            'image'             => 'required|mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff',
            'logo'              => 'required|mimes: jpg,jpeg,png,webp,gif,bmp,tif,tiff',
            'banner'            => 'required|mimes: jpg,jpeg,png,webp,gif,bmp,tif,tiff',
            'bottom_banner'     => 'mimes: jpg,jpeg,png,webp,gif,bmp,tif,tiff',
            */
        ];
    }

    public function messages(): array
    {
        $englishBranchNameKey = $this->getEnglishFieldKey('branch_name');
        $englishBranchAddressKey = $this->getEnglishFieldKey('branch_address');

        return [
            'branch_name.required' => translate('The_branch_name_field_is_required'),
            $englishBranchNameKey . '.required' => translate('The_name_in_english_is_required'),
            'phone.required' => translate('The_phone_field_is_required'),
            'phone.unique' => translate('The_phone_has_already_been_taken'),
            'phone.max' => translate('please_ensure_your_phone_number_is_valid_and_does_not_exceed_20_characters'),
            'phone.min' => translate('phone_number_with_a_minimum_length_requirement_of_4_characters'),
            'email.required' => translate('The_email_field_is_required'),
            'email.unique' => translate('The_email_has_already_been_taken'),
            'branch_country.required' => translate('The_branch_country_field_is_required'),
            'branch_state.required' => translate('The_branch_state_field_is_required'),
            'branch_address.required' => translate('The_branch_address_field_is_required'),
            $englishBranchAddressKey . '.required' => translate('The_branch_address_in_english_is_required'),
            
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}
