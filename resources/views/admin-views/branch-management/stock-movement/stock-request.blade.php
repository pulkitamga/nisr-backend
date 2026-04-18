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
            {{ translate('Add_New_Stock_Request') }}
        </h2>
    </div>

    <form class="stock_request_form text-start" action="{{ route('admin.branch.stock.request.store') }}" method="POST" enctype="multipart/form-data" id="stock_request_form">
        @csrf
        <div class="card mt-3 rest-part">
            <div class="card-header">
                <div class="d-flex gap-2">
                    <i class="tio-user-big"></i>
                    <h4 class="mb-0">{{ translate('Stock_Request') }}</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!--
                            Reason : for product branch -->
                    @php
                    $adminBranchId = auth('admin')->user()->branch_id;
                    @endphp

                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="form-group">
                            <label for="from_branch_id" class="title-color">
                                {{ translate('Requested_branch') }}
                                <span class="input-required-icon">*</span>
                            </label>
                            <select class="js-select2-custom form-control" name="from_branch_id" required readonly disabled>
                                @foreach ($fromBranches as $branch)
                                @if($branch['id'] == $adminBranchId)
                                <option value="{{ $branch['id'] }}" selected>
                                    {{ $branch['branch_name'] }}
                                </option>
                                @endif
                                @endforeach
                            </select>

                            {{-- Hidden input to actually submit value since disabled select doesn't submit --}}
                            <input type="hidden" name="from_branch_id" value="{{ $adminBranchId }}">
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="form-group">
                            <label for="name" class="title-color">
                                {{ translate('to_branch') }}
                                <span class="input-required-icon">*</span>
                            </label>
                            <select class="js-select2-custom form-control" name="to_branch_id" required>
                                <option value="{{ old('branch_id') }}" selected disabled>{{ translate('Select_Branch') }}</option>
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
                                {{ translate('DATE') }}
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
                                <th class="text-center">{{ translate('SL') }}</th>
                                <th>{{ translate('Category') }}</th>
                                <th>{{ translate('Products') }}</th>
                                <th>{{ translate('attributes') }}</th>
                                <th class="text-capitalize">{{ translate('QTY') }}</th>
                                <th class="text-center">{{ translate('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="product-rows">
                            <tr class="product-row" data-row-id="row-0">
                                <td class="text-center">1.</td>
                                <td>
                                    <select class="js-select2-custom form-control category-select category-select-0" name="products[0][category_id]" data-row-id="0" required>
                                        <option value="" selected disabled>{{ translate('select_Category') }}
                                        </option>
                                        @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}">
                                            {{ $category['name'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select autocomplete="off" name="products[0][product_id]" class="js-select2-custom form-control product-select product-select-0" data-row-id="0" placeholder="{{ translate('select_Product') }}" aria-label="{{ translate('Search') }}">
                                        <option value="">{{ translate('select_Product') }}</option>
                                        @foreach ($products as $product)
                                        <option value="{{ $product['id'] }}" {{ old('name') == $product['id'] ? 'selected' : '' }}>
                                            {{ $product['name'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="js-select2-custom form-control attribute-select-0" name="products[0][attribute_id]" required>
                                        <option value="" selected disabled>{{ translate('select_attributes') }}</option>
                                        @foreach ($attributes as $attribute)
                                        <option value="{{ $attribute['id'] }}">
                                            {{ $attribute['name'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="width:10%;">
                                    <input autocomplete="off" type="number" name="products[0][product_qty]" class="form-control" placeholder="{{ translate('QTY') }}" aria-label="{{ translate('Quantity') }}">
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="row justify-content-end">
                        <div class="col-sm-12">
                            <div class="mt-3">
                                <button type="button" class="btn btn-md btn-primary" id="add-product-row">{{ translate('Add_Product') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-end gap-3 mt-3 mx-1">
            <button type="reset" class="btn btn-secondary px-5">{{ translate('Reset') }}</button>
            <button type="button" class="btn btn--primary px-5 product-add-stock-request-check">
                {{ translate('Submit') }}
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
<script>
    $(document).on('click', '.product-add-stock-request-check', function() {
        $('#stock_request_form').submit(); // ✅ ye line honi chahiye
    });

</script>
@push('script_2')
<script type="text/javascript">
    let productRowCount = 1;

    // Function to add a new product row
    function addProductRow() {
        productRowCount++;
        const newRow = `
            <tr class="product-row" data-row-id="row-${productRowCount}">
                <td class="text-center">${productRowCount}.</td>
                <td>
                    <select class="js-select2-custom form-control category-select category-select-${productRowCount}" name="products[${productRowCount}][category_id]" data-row-id="${productRowCount}"
                    required>
                        <option value="" selected
                                disabled>{{ translate('select_Category') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['id'] }}">
                                {{ $category['name'] }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select autocomplete="off" name="products[${productRowCount}][product_id]" class="js-select2-custom form-control product-select product-select-${productRowCount}" data-row-id="${productRowCount}">
                        <option value="">{{ translate('select_Product') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select class="js-select2-custom form-control attribute-select-${productRowCount}" name="products[${productRowCount}][attribute_id]"
                    required>
                        <option value="" selected
                                disabled>{{ translate('select_attributes') }}</option>
                        @foreach ($attributes as $attribute)
                            <option value="{{ $attribute['id'] }}">
                                {{ $attribute['name'] }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td  style="width:10%;">
                    <input autocomplete="off" type="number" name="products[${productRowCount}][product_qty]" class="form-control" placeholder="{{ translate('QTY') }}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger remove-product-btn" data-row-id="row-${productRowCount}">
                        {{ translate('Remove') }}
                    </button>
                </td>
            </tr>
        `;
        $("#product-rows").append(newRow);
        $('.js-select2-custom').select2();
    }


    // Function to remove a product row
    $(document).on("click", ".remove-product-btn", function() {
        const rowId = $(this).data("row-id");
        $(`tr[data-row-id="${rowId}"]`).remove(); // Remove the corresponding row
    });

    // Event listener for adding rows
    $(document).on("click", "#add-product-row", function() {
        addProductRow();
    });

</script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/stock-request-add-update.js') }}"></script>
@endpush
