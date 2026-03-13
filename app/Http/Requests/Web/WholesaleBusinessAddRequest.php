<?php

namespace App\Http\Requests\Web;

use App\Traits\CalculatorTrait;
use App\Traits\RecaptchaTrait;
use App\Traits\ResponseHandler;
use Illuminate\Foundation\Http\FormRequest;

class WholesaleBusinessAddRequest extends FormRequest
{
    use RecaptchaTrait;
    use CalculatorTrait, ResponseHandler;

    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'company_name'        => 'required|string|max:255',
            'trade_name'          => 'required|string|max:255',
            'registration_number' => 'required|string|max:50|unique:wholesaler_businesses,registration_number',
            'tax_id'              => 'required|string|max:50|unique:wholesaler_businesses,tax_id',
            'register_copy'       => 'required|file|mimes:pdf,jpg,jpeg,png',
            'tax_card_copy'       => 'required|file|mimes:pdf,jpg,jpeg,png',
            'vat_number'          => 'required|string|max:50|unique:wholesaler_businesses,vat_number',
            'vat_register_copy'   => 'required|file|mimes:pdf,jpg,jpeg,png',
        ];
    }

    /**
     * Define custom error messages.
     */
    public function messages(): array
    {
        return [

            'company_name.required'        => 'Company Name is required.',
            'trade_name.required'          => 'Trade Name is required.',
            'registration_number.required' => 'Registration Number is required.',
            'registration_number.unique'   => 'This Registration Number is already taken.',
            'tax_id.required'              => 'Tax ID is required.',
            'tax_id.unique'                => 'This Tax ID is already taken.',
            'register_copy.required'       => 'Commercial Register Document is required.',
            'tax_card_copy.mimes'          => 'Tax Card Copy must be a PDF, JPG, JPEG, or PNG file.',
            // 'tax_card_copy.max'            => 'Tax Card Copy must not exceed 2MB.',
            'vat_number.required'          => 'VAT Registration Number is required.',
            'vat_number.unique'            => 'This VAT Registration Number is already taken.',
            'vat_register_copy.mimes'      => 'VAT Registration Copy must be a PDF, JPG, JPEG, or PNG file.',
            // 'vat_register_copy.max'        => 'VAT Registration Copy must not exceed 2MB.',
        ];
    }
}
