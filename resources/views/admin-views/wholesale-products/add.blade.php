@extends('layouts.back-end.app')

@section('title', translate('add'))

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" class="mb-1" alt="">
            {{translate('add_New_Product')}}
        </h2>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <form class="user" action="{{route('admin.wholesale.product.add')}}" method="post"
                enctype="multipart/form-data" id="add-product-form">
                @csrf
                <div class="card mt-3 rest-part">
                    <div class="card-body">
                        <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}"
                                class="mb-1" alt="">
                            {{ translate('Product_Information') }}
                        </h5>
                        <div class="row">
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="form-group">
                                    <label for="name" class="title-color">{{ translate('category') }}</label>
                                    <select
                                        class="js-select2-custom form-control action-get-request-onchange category-select-0"
                                        name="category_id"
                                        data-url-prefix="{{ url('/admin/products/get-sub-categories?parent_id=') }}"
                                        data-element-id="sub-category-select" data-element-type="select">
                                        <option value="{{ old('category_id') }}" selected disabled>{{
                                            translate('select_category') }}</option>
                                        @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}" {{ request('category_id')==$category['id']
                                            ? 'selected' : '' }}>
                                            {{ $category['defaultName'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="form-group">
                                    <label for="name" class="title-color">{{ translate('sub_Category') }}</label>
                                    <select class="js-select2-custom form-control action-get-onchange"
                                        name="sub_category_id" id="sub-category-select"
                                        data-url-prefix="{{ url('/admin/products/get-products?parent_id=') }}"
                                        data-element-id="sub-category-select" data-element-type="select">
                                        <option
                                            value="{{request('sub_category_id') != null ? request('sub_category_id') : null}}"
                                            selected {{request('sub_category_id') !=null ? '' : 'disabled' }}>
                                            {{request('sub_category_id') != null ? $subCategory['defaultName']:
                                            translate('select_Sub_Category') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="form-group">
                                    <label for="name" class="title-color">{{ translate('Products') }}</label>
                                    <select class="js-select2-custom form-control product-select" name="product_id"
                                        id="sub-sub-category-select" data-element-id="product-select"
                                        data-element-type="select" data-row-id="0">
                                        <option value="" selected disabled>--Select--</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="form-group">
                                    <label for="name" class="title-color">{{ translate('Variation') }}</label>
                                    <select class="js-select2-custom form-control variation-select" name="variation_type" required>
                                        <option value="" selected disabled>{{ translate('select_variation') }}</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Variation Select Ke Baad Yeh Line Add Kar Do -->
<div style="display: none;">
    <input type="hidden" name="variation_key" id="hidden-variation-key" value="">
</div>
                        </div>
                    </div>
                </div>
                <div class="card mt-3 rest-part">
                    <div class="card-body">
                        <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}"
                                class="mb-1" alt="">
                            {{ translate('wholesale_Prices') }}
                        </h5>
                        <div class="row">
                            <div class="col-md-12">
                                <table
                                    class="table table-hover table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                                    <thead class="thead-light thead-50 text-capitalize">
                                        <tr>
                                            <th class="text-center">{{ __('SL') }}</th>
                                            <th>{{ __('Tier') }}</th>
                                            <th>{{ __('Min. Quantity') }}</th>
                                            <th>{{ __('Max. Quantity') }}</th>
                                            <th>{{ __('Unit Price') }}</th>
                                            <th>{{ __('Discount (%)') }}</th>
                                            <th>{{ __('Final piece') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="range-rows">
                                        @foreach($defaultTiers as $index => $tier)
                                        <tr class="range-row" data-row-id="{{ $index }}">
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <input type="hidden" name="tier[]" value="{{ $tier->name }}">
                                                <input type="text" class="form-control" value="{{ $tier->getTranslatedField('name') }}"
                                                    disabled>
                                            </td>
                                            <td><input type="number" class="form-control" name="min_qty[]"
                                                    placeholder="{{ __('Min qty') }}"></td>
                                            <td><input type="number" class="form-control" name="max_qty[]"
                                                    placeholder="{{ __('Max qty') }}"></td>
                                            <td><input type="text" class="form-control unit-price" name="unit_price[]"
                                                    placeholder="{{ __('Unit Price') }}" readonly data-row="{{ $index }}"></td>
                                            <td><input type="number" step="0.01" class="form-control discount-input"
                                                    name="discount[]" placeholder="{{ __('Discount') }}" data-row="{{ $index }}">
                                            </td>
                                            <td><input type="text" class="form-control final-price" name="final_price[]"
                                                    placeholder="{{ __('Final Price') }}" data-row="{{ $index }}"></td>
                                        </tr>
                                        @endforeach
                                    </tbody>


                                </table>
                                <div class="d-flex justify-content-start">
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-md btn-primary" id="add-price-range">{{
                                            translate('Add_Price_Range') }}</button>
                                    </div>
                                </div>
                                <div class="row justify-content-end gap-3 mt-3 mx-1">
                                    <input type="hidden" name="from_submit" value="admin">
                                    <button type="reset" class="btn btn-secondary reset-button">{{translate('reset')}} </button>
                                    <button type="button" class="btn btn--primary btn-user form-submit" data-form-id="add-product-form"
                                        data-redirect-route="{{route('admin.wholesale.product.list')}}"
                                        data-message="{{translate('want_to_add_this_product').'?'}}">{{translate('submit')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
<span id="route-admin-products-search-category_wise"
    data-url="{{ route('admin.stock-request.search-product') }}"></span>

<script>
    window.remainingTiers = @json($remainingTiers);
</script>

@endsection
@push('script_2')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/whole-sale.js') }}"></script>
<script>
$(document).ready(function() {
    let currentUnitPrice = 0;

    $('.product-select').on('change', function() {
    const productId = $(this).val();
    if (!productId) {
        $('.variation-select').html('<option value="" selected disabled>{{ __('Select Variation') }}</option>').prop('disabled', true);
        $('.unit-price').val('');
        currentUnitPrice = 0;
        $('#hidden-variation-key').val('');
        return;
    }

    $.get(`/admin/wholesale/product/get-variations/${productId}`, function(res) {
        const $variationSelect = $('.variation-select');
        $variationSelect.empty().append('<option value="" selected disabled>{{ __('Select Variation') }}</option>');

        const variations = res.variations || [];

        if (variations.length > 0) {
            variations.forEach(v => {
                // const displayText = v.variation_key 
                //     ? `${v.type} → (${v.variation_key})`
                //     : v.type;

                const displayText =  v.type;
                $variationSelect.append(
                    `<option value="${v.type}" 
                             data-key="${v.variation_key || ''}" 
                             data-price="${v.price}">
                        ${displayText}
                    </option>`
                );
            });
            $variationSelect.prop('disabled', false);
        } else {
            currentUnitPrice = res.unit_price || 0;
            $('.unit-price').val(currentUnitPrice.toFixed(2));
            $('#hidden-variation-key').val(''); // No variation
            $variationSelect.append('<option value="" selected>{{ __('No variation available') }}</option>').prop('disabled', true);
        }

        updateFinalPrices();
    });
});

// Variation change
$(document).on('change', '.variation-select', function() {
    const $selected = $(this).find('option:selected');
    const price = parseFloat($selected.data('price')) || 0;
    const key = $selected.data('key') || '';

    currentUnitPrice = price;
    $('.unit-price').val(price.toFixed(2));
    $('#hidden-variation-key').val(key); // Yeh ab bilkul sahi ja raha hai!

    updateFinalPrices();
});

    // Discount change
    $(document).on('input', '.discount-input', function() {
        updateFinalPrices();
    });

    function updateFinalPrices() {
        $('.range-row').each(function() {
            const discount = parseFloat($(this).find('.discount-input').val()) || 0;
            const finalPrice = currentUnitPrice - (currentUnitPrice * discount / 100);
            $(this).find('.final-price').val(finalPrice.toFixed(2));
        });
    }
});


</script>
@endpush
