<?php

namespace App\Http\Requests;

use App\Traits\CalculatorTrait;
use App\Traits\ResponseHandler;
use App\Traits\ValidatesEnglishMultilingualInput;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class ProductAddRequest extends Request
{
    use CalculatorTrait, ResponseHandler, ValidatesEnglishMultilingualInput;

    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizedPayload = $this->normalizedServiceScalarPayload();

        if ($normalizedPayload !== []) {
            $this->merge($normalizedPayload);
        }
    }

    public function validationData(): array
    {
        return array_merge(parent::validationData(), $this->normalizedServiceScalarPayload());
    }

    private function normalizedServiceScalarPayload(): array
    {
        $normalizedPayload = [];

        foreach ($this->serviceScalarFieldNames() as $field) {
            if ($this->exists($field)) {
                $normalizedPayload[$field] = $this->extractServiceScalarValue($this->input($field));
            }
        }

        return $normalizedPayload;
    }

    private function serviceScalarFieldNames(): array
    {
        return [
            'service_id',
            'base_price_inshop',
            'base_price_mobile',
            'parts_cost',
            'included_km_mobile',
            'travel_fee_per_km',
            'labor_hours',
        ];
    }

    private function extractServiceScalarValue(mixed $value): ?string
    {
        if (is_array($value)) {
            foreach ($value as $candidate) {
                $resolvedCandidate = $this->extractServiceScalarValue($candidate);

                if ($resolvedCandidate !== null && $resolvedCandidate !== '') {
                    return $resolvedCandidate;
                }
            }

            return null;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $normalizedValue = trim((string) $value);

        return $normalizedValue !== '' ? $normalizedValue : null;
    }

    private function serviceIdRules(): array
    {
        return [
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (($this->input('product_type') ?? null) !== 'services') {
                    return;
                }

                $normalizedValue = $this->extractServiceScalarValue($value);

                if ($normalizedValue === null) {
                    $fail(trans('validation.required', ['attribute' => translate('service_id')]));
                    return;
                }

                if (mb_strlen($normalizedValue) > 255) {
                    $fail(trans('validation.max.string', ['attribute' => translate('service_id'), 'max' => 255]));
                }
            },
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */

    /*
       
        Reason : for branch_id validation
    */
    public function rules(): array
    {
        $allowedUnits = implode(',', units());

        $rules = [

            'name' => 'required_if:product_type,physical',
            'branch_id' => 'required',
            'category_id' => 'required',
            'product_type' => 'required',
            'digital_product_type' => 'required_if' . ':' . 'product_type' . ',' . 'digital',
            // 'digital_file_ready' => 'required_if' . ':' . 'digital_product_type' . ',==,' . 'ready_product' . '|' . 'mimes' . ':jpg,jpeg,png,gif,zip,pdf',
            'unit' => 'required_if' . ':' . 'product_type' . ',' . 'physical' . '|' . 'in:' . $allowedUnits,
            'tax' => 'required' . '|' . 'min' . ':0',
            'tax_model' => 'required',
            'unit_price' =>  'required_if' . ':' . 'product_type' . ',' . 'physical',
            'video_url' => 'nullable|url',
            'discount' => 'required' . '|' . 'gt' . ':-1',
            'shipping_cost' => 'required_if' . ':' . 'product_type' . ',' . 'physical' . '|' . 'gt' . ':-1',
            'code' => 'nullable|required_if:product_type,physical|regex:/^[a-zA-Z0-9]+$/|min:6|max:20|unique:products,code',
            'minimum_order_qty' =>  'required_if' . ':' . 'product_type' . ',' . 'physical' . '|' . 'numeric' . '|' . 'min' . ':1',
            'current_stock' => 'integer',
            'service_tittle' => 'required_if:product_type,services|array',
            'service_tittle.*' => 'nullable|string',
            'parts_included' => 'required_if:product_type,services|array',
            'parts_included.*' => 'nullable|string',
            'service_description' => 'required_if:product_type,services|array',
            'service_description.*' => 'nullable|string',
            'service_id' => $this->serviceIdRules(),
            'base_price_inshop' => 'required_if:product_type,services|numeric|min:0',
            'base_price_mobile' => 'required_if:product_type,services|numeric|min:0',
            'parts_cost' => 'required_if:product_type,services|numeric|min:0',
            'included_km_mobile' => 'required_if:product_type,services|numeric|min:0',
            'travel_fee_per_km' => 'required_if:product_type,services|numeric|min:0',
            'labor_hours' => 'required_if:product_type,services|numeric|min:0',
            'image' => ['file', 'mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff', 'max:2048'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff', 'max:2048'],
            'meta_image' => ['file', 'mimes:jpg,jpeg,png,webp,gif,bmp,tif,tiff', 'max:2048'],
        ];

        if (!isset($this['existing_thumbnail'])) {
            array_unshift($rules['image'], 'required');
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'image' . '.' . 'required' => translate('product_thumbnail_is_required!'),
            'image.mimes' => translate('The_image_type_must_be') . '.jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff, .webp',
            'image.max' => translate('file_size_too_big') . '. ' . translate('Max') . ' 2 MB.',
            'images.*.mimes' => translate('The_image_type_must_be') . '.jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff, .webp',
            'images.*.max' => translate('file_size_too_big') . '. ' . translate('Max') . ' 2 MB.',
            'meta_image.mimes' => translate('The_image_type_must_be') . '.jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff, .webp',
            'meta_image.max' => translate('file_size_too_big') . '. ' . translate('Max') . ' 2 MB.',
            'branch_id' . '.' . 'required' => translate('branch_is_required!'),
            'category_id' . '.' . 'required' => translate('category_is_required!'),
            'unit' . '.' . 'required_if' => translate('unit_is_required!'),
            'unit' . '.' . 'in' => translate('The_selected_unit_is_invalid') . '!',
            'code.required_if' => translate('product_code_is_required_for_physical_products'),
            'code.max' => translate('please_ensure_your_code_does_not_exceed_20_characters'),
            'code.min' => translate('code_with_a_minimum_length_requirement_of_6_characters'),
            'minimum_order_qty' . '.' . 'required' => translate('minimum_order_quantity_is_required!'),
            'minimum_order_qty' . '.' . 'min' => translate('minimum_order_quantity_must_be_positive!'),
            'video_url.url' => translate('please_enter_a_valid_video_url') . '!',
            // 'digital_file_ready' . '.' . 'required_if' => translate('ready_product_upload_is_required!'),
            // 'digital_file_ready' . '.' . 'mimes' => translate('ready_product_upload_must_be_a_file_of_type') . ':' . 'pdf, zip, jpg, jpeg, png, gif.',
            'digital_product_type' . '.' . 'required_if' => translate('digital_product_type_is_required!'),
            'shipping_cost' . '.' . 'required_if' => translate('shipping_cost_is_required!')
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (!$this->has('colors_active') && !$this->file('images') && !$this->has('existing_images')) {
                    $validator->errors()->add(
                        'images',
                        translate('product_images_is_required') . '!'
                    );
                }

                if (getWebConfig(name: 'product_brand') && empty($this->brand_id) && $this['product_type'] == 'physical') {
                    $validator->errors()->add(
                        'brand_id',
                        translate('brand_is_required') . '!'
                    );
                }

                if ($this['product_type'] == 'physical' && $this['unit_price'] <= $this->getDiscountAmount(price: $this['unit_price'] ?? 0, discount: $this['discount'], discountType: $this['discount_type'])) {
                    $validator->errors()->add(
                        'unit_price',
                        translate('discount_can_not_be_more_or_equal_to_the_price') . '!'
                    );
                }
                if ($this['product_type'] === 'services') {
                    $this->validateEnglishMultilingualFields($validator, [
                        'service_tittle' => ['message' => translate('The_title_in_english_is_required') . '!'],
                        'parts_included' => ['message' => translate('The_description_in_english_is_required') . '!'],
                        'service_description' => [
                            'message' => translate('The_description_in_english_is_required') . '!',
                            'rich_text' => true,
                        ],
                    ]);
                } else {
                    $this->validateEnglishMultilingualFields($validator, [
                        'name' => ['message' => translate('The_name_in_english_is_required') . '!'],
                        'description' => [
                            'message' => translate('The_description_in_english_is_required') . '!',
                            'rich_text' => true,
                        ],
                    ]);
                }


                $productImagesCount = 0;
                if ($this->has('colors_active') && $this->has('colors') && count($this['colors']) > 0) {
                    foreach ($this['colors'] as $color) {
                        $color_ = str_replace('#', '', $color);
                        $image = 'color_image_' . $color_;
                        if ($this->file($image)) {
                            $productImagesCount++;
                        } else if ($this->has($image)) {
                            $productImagesCount++;
                        }
                    }
                    if ($productImagesCount != count($this['colors'])) {
                        $validator->errors()->add(
                            'images',
                            translate('color_images_is_required') . '!'
                        );
                    }
                }

                if ($this['product_type'] == 'physical' && ($this->has('colors') || ($this->has('choice_attributes') && count($this['choice_attributes']) > 0))) {
                    foreach ($this->all() as $requestKey => $requestValue) {
                        if (str_contains($requestKey, 'sku_')) {
                            if (empty($this[$requestKey])) {
                                $validator->errors()->add(
                                    'sku_error',
                                    translate('Variation_SKU_are_required') . '!'
                                );
                            }
                        }
                    }
                }

                if ($this['product_type'] == 'digital') {
                    $digitalProductVariationCount = 0;
                    if ($this['extensions_type'] && count($this['extensions_type']) > 0) {
                        $options = [];
                        foreach ($this['extensions_type'] as $type) {
                            $name = 'extensions_options_' . $type;
                            $my_str = implode('|', $this[$name]);
                            $options[$type] = explode(',', $my_str);
                        }

                        foreach ($options as $arrayKey => $array) {
                            foreach ($array as $key => $value) {
                                if ($value) {
                                    $digitalProductVariationCount++;
                                }
                            }
                        }

                        if ($digitalProductVariationCount == 0) {
                            $validator->errors()->add(
                                'variation_error',
                                translate('Digital_Product_variations_are_required') . '!'
                            );
                        }

                        if ($this['digital_product_type'] == 'ready_product' && empty($this['digital_files'])) {
                            $validator->errors()->add(
                                'files',
                                translate('Digital_files_are_required') . '!'
                            );
                        }

                        if ($this['digital_files'] && $digitalProductVariationCount != count($this['digital_files'])) {
                            $validator->errors()->add(
                                'files',
                                translate('Digital_files_are_required') . '!'
                            );
                        }

                        if ($this->has('digital_product_sku') && empty($this['digital_product_sku'])) {
                            $validator->errors()->add(
                                'sku_error',
                                translate('Digital_SKU_are_required') . '!'
                            );
                        } elseif ($this->has('digital_product_sku') && !empty($this['digital_product_sku'])) {
                            foreach ($this['digital_product_sku'] as $digitalSKU) {
                                if (empty($digitalSKU)) {
                                    $validator->errors()->add(
                                        'sku_error',
                                        translate('Digital_SKU_are_required') . '!'
                                    );
                                }
                            }
                        }
                    } else {
                        if ($this['digital_product_type'] == 'ready_product' && empty($this['digital_file_ready'])) {
                            $validator->errors()->add(
                                'files',
                                translate('Digital_files_are_required') . '!'
                            );
                        }
                    }
                }

                if ($this['preview_file']) {
                    $disallowedExtensions = ['php', 'java', 'js', 'html', 'exe', 'sh'];
                    $maxFileSize = 10 * 1024 * 1024; // 10 MB in bytes
                    $extension = $this['preview_file']->getClientOriginalExtension();
                    $fileSize = $this['preview_file']->getSize();

                    if ($fileSize > $maxFileSize) {
                        $validator->errors()->add(
                            'files',
                            translate('File_size_exceeds_the_maximum_limit_of_10MB') . '!'
                        );
                    } elseif (in_array($extension, $disallowedExtensions)) {
                        $validator->errors()->add(
                            'files',
                            translate('Files_with_extensions_like') . (' .php,.java,.js,.html,.exe,.sh ') . translate('are_not_supported') . '!'
                        );
                    }
                }
            }
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}
