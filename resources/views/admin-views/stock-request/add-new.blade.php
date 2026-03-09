@extends('layouts.back-end.app')

@section('title', translate('Add_Stock_Request'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="content container-fluid">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <h2 class="h1 mb-0 d-flex gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
            {{ translate('add_New_Stock_Request') }}
        </h2>
    </div>

    <form class="stock_request_form text-start" action="{{ route('admin.stock-request.store') }}" method="POST"
        enctype="multipart/form-data" id="stock_request_form">
        @csrf
        <div class="card mt-3 rest-part">
            <div class="card-header">
                <div class="d-flex gap-2">
                    <i class="tio-user-big"></i>
                    <h4 class="mb-0">{{ translate('stock_Request') }}</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!--
                            Reason : for product branch -->
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="form-group">
                            <label for="name" class="title-color">
                                {{ translate('Requested_branch') }}
                                <span class="input-required-icon">*</span>
                            </label>
                            <select class="js-select2-custom form-control" name="from_branch_id"
                                required>
                                <option value="{{ old('branch_id') }}" selected
                                    disabled>{{ translate('select_branch') }}</option>
                                @foreach ($fromBranches as $branch)
                                @if($branch['id'] != 1)
                                <option value="{{ $branch['id'] }}"
                                    {{ old('name') == $branch['id'] ? 'selected' : '' }}>
                                    {{ $branch['branch_name'] }}
                                </option>
                                @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="form-group">
                            <label for="name" class="title-color">
                                {{ translate('to_branch') }}
                                <span class="input-required-icon">*</span>
                            </label>
                            <select class="js-select2-custom form-control" name="to_branch_id"
                                required>
                                <option value="{{ old('branch_id') }}" selected
                                    disabled>{{ translate('select_branch') }}</option>
                                @foreach ($toBranches as $branch)
                                <option value="{{ $branch['id'] }}" selected>
                                    {{ $branch['branch_name'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3" id="from_div">
                        <label class="title-color d-flex justify-content-between gap-2">
                            <span class="d-flex align-items-center gap-2">
                                {{ translate('Date') }}
                                <span class="input-required-icon">*</span>
                            </span>
                        </label>
                        <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" id="transfer_date" class="form-control">

                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3 rest-part">
            <div class="card-header">
                <div class="d-flex gap-2">
                    <i class="tio-add"></i>
                    <h4 class="mb-0">{{ translate('Add_Products') }}</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <table class="table table-hover table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th class="text-center">SL</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Products') }}</th>
                                <th>{{ __('Variation') }}</th>
                                <th class="text-capitalize">{{ __('QTY') }}</th>
                                <th class="text-center">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="product-rows">
                          @php $rowId = 0 @endphp
<tr class="product-row" data-row-id="{{ $rowId }}">
    <td class="text-center">1.</td>
    <td>
        <select class="js-select2-custom form-control category-select" name="products[0][category_id]" required>
            <option value="" selected disabled>{{ translate('select_category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="products[0][product_id]" 
                class="js-select2-custom form-control product-select" 
                data-row-id="0" required>
            <option value="">{{ translate('select_product') }}</option>
            @foreach ($products as $product)
                <option value="{{ $product['id'] }}"
                    data-variation='@json($product->variation ? json_decode($product->variation, true) : [])'>
                    {{ $product['name'] }}
                </option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="products[0][variation_type]" 
                class="form-control variation-select js-select2-custom" 
                data-row-id="0" required>
            <option value="">{{ translate('select_variation') }}</option>
        </select>
    </td>
    <td style="width:10%;">
        <input type="number" name="products[0][product_qty]" class="form-control" min="1" placeholder="QTY" required>
    </td>
    <td class="text-center"></td>
</tr>
                        </tbody>
                    </table>
                    <div class="row justify-content-end">
                        <div class="col-sm-12">
                            <div class="mt-3">
                                <button type="button" class="btn btn-md btn-primary" id="add-product-row">{{ translate('Add Product') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-end gap-3 mt-3 mx-1">
            <button type="reset" class="btn btn-secondary px-5">{{ translate('reset') }}</button>
            <button type="button" class="btn btn--primary px-5 product-add-stock-request-check">
                {{ translate('submit') }}
            </button>
        </div>
    </form>
</div>
<div class="modal fade pt-5" id="quick-view" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" id="quick-view-modal"></div>
    </div>
</div>
<!-- <span id="route-admin-products-search-product" data-url="{{ route('admin.stock-request.search-product') }}"></span> -->
<span id="route-admin-pos-quick-view" data-url="{{ route('admin.stock-request.quick-view') }}"></span>
<span id="route-admin-products-search-category_wise" data-url="{{ route('admin.stock-request.search-product') }}"></span>
<span id="message-want-to-add-or-update-this-request" data-text="{{ translate('want_to_add_this_request') }}"></span>
<span id="message-stock-request-added-successfully" data-text="{{ translate('request_added_successfully') }}"></span>
@endsection

@push('script_2')

<script type="text/javascript">
    let productRowCount = 0;

    function addProductRow() {
    productRowCount++;
    const newRow = `
        <tr class="product-row" data-row-id="${productRowCount}">
            <td class="text-center">${productRowCount + 1}.</td>
            <td>
                <select class="js-select2-custom form-control category-select" name="products[${productRowCount}][category_id]" required>
                    <option value="" selected disabled>{{ translate('select_category') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="products[${productRowCount}][product_id]" 
                        class="js-select2-custom form-control product-select" 
                        data-row-id="${productRowCount}" required>
                    <option value="">{{ translate('select_product') }}</option>
                    @foreach ($products as $product)
                        <option value="{{ $product['id'] }}"
                            data-variation='@json($product->variation ? json_decode($product->variation, true) : [])'>
                            {{ $product['name'] }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="products[${productRowCount}][variation_type]" 
                        class="form-control variation-select js-select2-custom" 
                        data-row-id="${productRowCount}" required>
                    <option value="">{{ translate('select_variation') }}</option>
                </select>
            </td>
            <td style="width:10%;">
                <input type="number" name="products[${productRowCount}][product_qty]" class="form-control" min="1" placeholder="QTY" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm remove-product-btn">{{ __('Remove') }}</button>
            </td>
        </tr>
    `;
    $("#product-rows").append(newRow);
    $('.js-select2-custom').select2();
}

    // Product change → load attributes
  $(document).on('change', '.product-select', function() {
    const rowId = $(this).data('row-id');
    const $variationSelect = $(`.variation-select[data-row-id="${rowId}"]`);
    const variations = $(this).find('option:selected').data('variation') || [];

    $variationSelect.empty().append('<option value="">{{ translate("select_variation") }}</option>');

    if (Array.isArray(variations) && variations.length > 0) {
        variations.forEach(v => {
            const type = v.type || '';
            $variationSelect.append(`<option value="${type}">${type}</option>`);
        });
        $variationSelect.prop('disabled', false);
    } else {
        $variationSelect.append('<option disabled>{{ translate("no_variation") }}</option>').prop('disabled', true);
    }
});

    // Remove row
    $(document).on('click', '.remove-product-btn', function() {
        $(this).closest('tr').remove();
    });

    $('#add-product-row').on('click', addProductRow);

</script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/stock-request-add-update.js') }}"></script>
@endpush
