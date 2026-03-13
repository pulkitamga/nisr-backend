<?php

namespace App\Http\Requests\Admin;

use App\Traits\ResponseHandler;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Contracts\Repositories\WholesaleproductsRepositoryInterface;
use Illuminate\Validation\Rule;

class WholeSaleProductAddRequrest extends FormRequest
{
    use ResponseHandler;
    public function __construct(
        private readonly WholesaleproductsRepositoryInterface $Wholesaleproductrepo
    ) {}
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
        'category_id'       => 'required|exists:categories,id',
        'sub_category_id'   => 'required|exists:categories,id',
        'product_id'        => 'required|exists:products,id',
        'variation_type'    => 'nullable|string',
        'variation_key'     => 'nullable|string',

        'min_qty'           => 'required|array|min:1',
        'min_qty.*'         => 'required|integer|min:1',

        'max_qty'           => 'nullable|array',
        'max_qty.*'         => 'nullable|integer|gte:min_qty.*',

        'unit_price'        => 'required|array|min:1',
        'unit_price.*'      => 'required|numeric|min:0.01',

        'discount'          => 'nullable|array',
        'discount.*'        => 'nullable|numeric|min:0|max:100',
    ];
}

    public function messages(): array
    {
        return [
            'category_id.required' => translate('please_select_a_category'),
            'sub_category_id.required' => translate('please_select_sub_category'),
            'product_id.required' => translate('please_select_product'),

            'min_qty.required'         => translate('please_enter_minimum_quantity'),
            'min_qty.array'            => translate('minimum_quantity_must_be_an_array'),
            'min_qty.*.required'       => translate('each_minimum_quantity_is_required'),
            'min_qty.*.integer'        => translate('minimum_quantity_must_be_a_number'),
            'min_qty.*.min'            => translate('minimum_quantity_must_be_at_least_1'),

            // Max Quantity Validation Messages
            'max_qty.required'         => translate('please_enter_maximum_quantity'),
            'max_qty.array'            => translate('maximum_quantity_must_be_an_array'),
            'max_qty.*.required'       => translate('each_maximum_quantity_is_required'),
            'max_qty.*.integer'        => translate('maximum_quantity_must_be_a_number'),
            'max_qty.*.min'            => translate('maximum_quantity_must_be_at_least_1'),
            'max_qty.*.gte'            => translate('maximum_quantity_must_be_greater_than_or_equal_to_minimum_quantity'),

            // Price Per Piece Validation Messages
            'unit_price.required'         => translate('please_enter_unit_price'),
            'unit_price.array'            => translate('unit_price_must_be_an_array'),
            'unit_price.*.required'       => translate('each_unit_price_is_required'),
            'unit_price.*.numeric'        => translate('unit_price_must_be_a_valid_number'),
            'unit_price.*.min'            => translate('unit_price_must_be_at_least_0.01'),
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}
