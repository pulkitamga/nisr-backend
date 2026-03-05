@extends('layouts.back-end.app')

@section('title', translate('Transfer_Product_Stock'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <h2 class="h1 mb-0 d-flex gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
            {{ translate('transfer_Product_Stock') }}
        </h2>
    </div>

    @if(session('error_csv'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Transfer failed!</strong> {{ session('error_count') }} serials invalid.
        <a href="{{ route('admin.stock-transfer.download-error-csv', session('error_csv')) }}" class="btn btn-sm btn-warning ms-2">
            Download Error Report
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="alert">&times;</button>
    </div>
    @endif

    <form action="{{ route('admin.stock-transfer.store') }}" method="POST" enctype="multipart/form-data" id="stock_transfer_form">
        @csrf
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="mb-0">{{ translate('stock_Transfer') }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="title-color">{{ translate('from_branch') }} <span class="text-danger">*</span></label>
                        <select name="from_branch_id" id="from_branch_id" class="form-control js-select2-custom" required>
                            <option value="">{{ translate('select_branch') }}</option>
                            @foreach($fromBranches as $branch)
                            <option value="{{ $branch['id'] }}">{{ $branch['branch_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="title-color">{{ translate('to_branch') }} <span class="text-danger">*</span></label>
                        <select name="to_branch_id" class="form-control js-select2-custom" required>
                            <option value="">{{ translate('select_branch') }}</option>
                            @foreach($toBranches as $branch)
                            @if($branch['id'] != 1)
                            <option value="{{ $branch['id'] }}">{{ $branch['branch_name'] }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="title-color">{{ translate('Date') }} <span class="text-danger">*</span></label>
                        <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-0">{{ translate('Add_Products') }}</h4>
                <a href="{{ asset('sample.csv') }}" class="btn btn-primary" download>{{ translate('Download_Sample_Csv') }}</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>SL</th>
                            <th>Category</th>
                            <th>Product</th>
                            <th>Variation</th>
                            <th>Available Stock</th>
                            <th>QTY</th>
                            <th>Serial CSV</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="product-rows">
                        <tr class="product-row" data-row-id="0">
                            <td class="text-center">1</td>
                            <td>
                                <select name="products[0][category_id]" class="form-control category-select" required>
                                    <option value="">{{ translate('select_category') }}</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="products[0][product_id]" class="form-control product-select js-select2-custom" data-row-id="0" required>
                                    <option value="">{{ translate('select_product') }}</option>
                                    @foreach($products as $p)
                                    <option value="{{ $p['id'] }}"
                                        data-variation='@json($p->variation ? json_decode($p->variation, true) : [])'
                                        data-is-traceable="{{ $p->is_traceable }}">
                                        {{ $p['name'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="products[0][variation_type]" class="form-control variation-select" data-row-id="0" required>
                                    <option value="">{{ translate('select_variation') }}</option>
                                </select>
                            </td>
                            <td>
                                <span class="available-stock text-success font-weight-bold" data-row-id="0">0</span>
                            </td>
                            <td>
                                <input type="number" name="products[0][product_qty]" class="form-control qty-input" min="1" required>
                            </td>
                            <td>
                                <div class="serial-csv-wrapper" data-row-id="0" style="display: none;">
                                    <input type="file" name="products[0][serial_csv]" class="form-control serial-csv" accept=".csv">
                                    <small class="text-muted">CSV: serial_number (one per line)</small>
                                </div>
                            </td>
                            <td class="text-center"></td>
                        </tr>
                    </tbody>
                </table>

                <button type="button" class="btn btn-primary mt-3" id="add-product-row">
                    {{ translate('Add Product') }}
                </button>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="reset" class="btn btn-secondary px-5">{{ translate('reset') }}</button>
            <button type="submit" class="btn btn--primary px-5">{{ translate('Transfer Stock') }}</button>
        </div>
    </form>
</div>
@endsection
@push('script_2')
<script>
    let rowCount = 0;
    let fromBranchId = '';

  function toggleSerialCsv(rowId) {
    const $productSelect = $(`.product-select[data-row-id="${rowId}"]`);
    const $wrapper = $(`.serial-csv-wrapper[data-row-id="${rowId}"]`);
    const $input = $wrapper.find('.serial-csv');

    // Yeh line debug ke liye — console mein dekh sakte ho
    console.log(`Row ${rowId} | is_traceable =`, $productSelect.find('option:selected').data('is-traceable'));

    const isTraceable = $productSelect.find('option:selected').data('is-traceable') == 1;

    if (isTraceable) {
        $wrapper.slideDown(200);
        $input.prop('required', true);
    } else {
        $wrapper.slideUp(200);
        $input.prop('required', false).val('');
    }
}

   $(document).on('change', '.product-select', function() {
    const rowId = $(this).data('row-id');
    let variations = $(this).find('option:selected').data('variation') || [];

    const $variationSelect = $(`select.variation-select[data-row-id="${rowId}"]`);
    const $stockSpan = $(`.available-stock[data-row-id="${rowId}"]`);

    $variationSelect.empty().append('<option value="">{{ translate("select_variation") }}</option>');
    $stockSpan.text('0');

    if (Array.isArray(variations) && variations.length > 0) {
        variations.forEach(v => {
            const type = v.type || '';
            const baseQty = v.qty || 0;
            $variationSelect.append(
                `<option value="${type}" data-base-qty="${baseQty}">
                    ${type}
                </option>`
            );
        });
        $variationSelect.prop('disabled', false);
    } else {
        $variationSelect.append('<option disabled>No variation</option>').prop('disabled', true);
    }

    toggleSerialCsv(rowId);
    updateAvailableStock(rowId);
});


    $(document).on('change', '.variation-select, #from_branch_id', function() {
        if ($(this).attr('id') === 'from_branch_id') {
            fromBranchId = $(this).val();
        }
        $('.product-row').each(function() {
            updateAvailableStock($(this).data('row-id'));
        });
    });
    // Yeh pura function replace kar do
   function updateAvailableStock(rowId) {
    const $stockSpan = $(`.available-stock[data-row-id="${rowId}"]`);
    const $productSelect = $(`.product-select[data-row-id="${rowId}"]`);
    const $variationSelect = $(`.variation-select[data-row-id="${rowId}"]`);

    // Agar branch select nahi kiya → base qty dikhao
    if (!fromBranchId) {
        const baseQty = $variationSelect.find('option:selected').data('base-qty') || 0;
        $stockSpan.text(baseQty).removeClass('text-danger').addClass('text-success');
        return;
    }

    const productId = $productSelect.val();
    const variationType = $variationSelect.val();
    const variations = $productSelect.find('option:selected').data('variation') || [];

    // Agar product mein variation hi nahi hai (ya array empty hai)
    if (!Array.isArray(variations) || variations.length === 0) {
        // AJAX CALL MAT KARO — direct non-variation stock fetch karo
        $.ajax({
            url: '{{ route("admin.stock-transfer.get-stock") }}',
            type: 'GET',
            data: {
                branch_id: fromBranchId,
                product_id: productId
                // variation_type intentionally nahi bheja
            },
            success: function(res) {
                const stock = parseInt(res.stock) || 0;
                $stockSpan.text(stock);
                $stockSpan.toggleClass('text-success text-danger', stock === 0);
            },
            error: function() {
                $stockSpan.text('Error');
            }
        });
        return;
    }

    if (!variationType) {
        const baseQty = $variationSelect.find('option:selected').data('base-qty') || 0;
        $stockSpan.text(baseQty).removeClass('text-danger').addClass('text-success');
        return;
    }
    $.ajax({
        url: '{{ route("admin.stock-transfer.get-stock") }}',
        type: 'GET',
        data: {
            branch_id: fromBranchId,
            product_id: productId,
            variation_type: variationType
        },
        success: function(res) {
            const realStock = parseInt(res.stock) || 0;
            $stockSpan.text(realStock);
            $stockSpan.toggleClass('text-success text-danger', realStock === 0);
        },
        error: function() {
            $stockSpan.text('Error');
        }
    });
}
    $('#add-product-row').on('click', function() {
        rowCount++;
        const rowHtml = `
    <tr class="product-row" data-row-id="${rowCount}">
        <td class="text-center">${rowCount + 1}</td>
        <td>
            <select name="products[${rowCount}][category_id]" class="form-control category-select" required>
                <option value="">{{ translate('select_category') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="products[${rowCount}][product_id]" class="form-control product-select js-select2-custom" data-row-id="${rowCount}" required>
                <option value="">{{ translate('select_product') }}</option>
                @foreach($products as $p)
                    <option value="{{ $p['id'] }}"
                        data-variation='@json($p->variation ? json_decode($p->variation, true) : [])'
                        data-is-traceable="{{ $p->is_traceable }}">
                        {{ $p['name'] }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="products[${rowCount}][variation_type]" class="form-control variation-select" data-row-id="${rowCount}" required>
                <option value="">{{ translate('select_variation') }}</option>
            </select>
        </td>
        <td><span class="available-stock text-success font-weight-bold" data-row-id="${rowCount}">0</span></td>
        <td><input type="number" name="products[${rowCount}][product_qty]" class="form-control qty-input" min="1" required></td>
      <td>
    <div class="serial-csv-wrapper" data-row-id="${rowCount}" style="display: none;">
        <input type="file" name="products[${rowCount}][serial_csv]" class="form-control serial-csv" accept=".csv">
        <small class="text-muted">CSV: serial_number (one per line)</small>
    </div>
</td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
        </td>
    </tr>`;
        $('#product-rows').append(rowHtml);
        $('.js-select2-custom').select2();
    });
    // Remove Row
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });

    // Branch change ke baad sab rows update karo
    $(document).on('change', '#from_branch_id', function() {
        fromBranchId = $(this).val();
        if (fromBranchId) {
            $('.product-row').each(function() {
                const rowId = $(this).data('row-id');
                updateAvailableStock(rowId);
            });
        }
    });
</script>
@endpush
