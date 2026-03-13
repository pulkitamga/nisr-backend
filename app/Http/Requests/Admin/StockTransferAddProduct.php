<?php

namespace App\Http\Requests\Admin;

use App\Traits\ResponseHandler;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class StockTransferAddProduct extends FormRequest
{
    use ResponseHandler;

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
        return [
            'from_branch_id' => 'required',
            'to_branch_id' => 'required|exists:branches,id',
            'transfer_date' => 'required|date|before_or_equal:today',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.category_id' => 'required|exists:categories,id',
            // 'products.*.attribute_id' => 'required|exists:attributes,id',
            'products.*.product_qty' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'from_branch_id.required' => translate('The "From Branch" is required.'),
            'to_branch_id.required' => translate('The "To Branch" is required.'),
            'transfer_date.required' => translate('The "Transfer Date" is required.'),
            'transfer_date.date' => translate('The "Transfer Date" must be a valid date.'),
            'transfer_date.before_or_equal' => translate('The "Transfer Date" cannot be a future date.'),
            'products.required' =>  translate('At least one product is required.'),
            'products.array' =>  translate('Products should be an array.'),
            'products.*.product_id.required' =>  translate('Each product must have a valid product.'),
            'products.*.category_id.required' =>  translate('Each product must have a valid category.'),
            // 'products.*.attribute_id.required' =>  translate('Each product must have a valid attributes.'),
            'products.*.product_id.exists' =>  translate('The selected product does not exist.'),
            'products.*.category_id.exists' =>  translate('The selected category does not exist.'),
            // 'products.*.attribute_id.exists' =>  translate('The selected attribute does not exist.'),
            'products.*.product_qty.required' =>  translate('Each product must have a quantity.'),
            'products.*.product_qty.integer' =>  translate('The quantity must be an integer.'),
            'products.*.product_qty.min' =>  translate('The quantity must be at least 1.'),
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json(['errors' => $this->errorProcessor($validator)], 422)
            );
        }

        // Create redirect response
        $response = redirect()
            ->back()
            ->withInput($this->input())
            ->withErrors($validator);

        // PRESERVE error_csv flash data
        if (session()->has('error_csv')) {
            $response->with('error_csv', session('error_csv'));
        }

        throw new HttpResponseException($response);
    }
}
