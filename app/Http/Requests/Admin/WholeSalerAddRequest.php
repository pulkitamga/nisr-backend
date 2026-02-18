<?php

namespace App\Http\Requests\Admin;

use App\Traits\ResponseHandler;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Contracts\Repositories\WholeSalerRepositoryInterface;
use Illuminate\Validation\Rule;
class WholeSalerAddRequest extends FormRequest
{
    use ResponseHandler;
    public function __construct(
        private readonly WholesaleproductsRepositoryInterface $Wholesaleproductrepo
    )
    {
    }
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
        $pr_id = $this->Wholesaleproductrepo->getFirstWhere(['id' => $this->route('id')]);
        dd($pr_id);
        return [
            'category_id' => 'required',
            'sub_category_id' => 'required',
            'product_id' => $this->route('id') ? 'required|unique:wholesale_products,product_id,' . $pr_id->product_id . ',product_id' : 'required|unique:wholesale_products,product_id',
            'attribute_id' => 'required',
            // Ensuring min_qty, max_qty, and price_per_piece are arrays and contain numeric values
            'min_qty'         => 'required|array',
            'min_qty.*'       => 'required|integer|min:1',

            'max_qty'         => 'required|array',
            'max_qty.*'       => 'required|integer|min:1|gte:min_qty.*',

            'price_per_piece' => 'required|array',
            'price_per_piece.*' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => translate('please_select_a_category'),
            'sub_category_id.required' => translate('please_select_sub_category'),
            'product_id.required' => translate('please_select_product'),
            'attribute_id.required' => translate('plesae_select_product_attribute'),

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
            'price_per_piece.required'         => translate('please_enter_price_per_piece'),
            'price_per_piece.array'            => translate('price_per_piece_must_be_an_array'),
            'price_per_piece.*.required'       => translate('each_price_per_piece_is_required'),
            'price_per_piece.*.numeric'        => translate('price_per_piece_must_be_a_valid_number'),
            'price_per_piece.*.min'            => translate('price_per_piece_must_be_at_least_0.01'),
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}
