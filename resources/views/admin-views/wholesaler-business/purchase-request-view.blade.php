@extends('layouts.back-end.app')
@section('title', translate('purchase_request_view'))
@push('css_or_js')
<link href="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush

@section('content')

<div class="content container-fluid">

    <div class="card p-4">
        <form action="{{ route('admin.wholesale.business.orders.approve', $order->id) }}" method="POST" id="quotation-form">
            @csrf

            <div class="mb-4">

                <div class="mb-3">
                    <label for="order_no" class="form-label fw-semibold">{{ translate('Purchase Order No') }}:</label>
                    <input type="text" name="order_no" value="{{ $order->purchase_order_no }}" readonly
                        class="form-control w-auto d-inline-block" required>
                </div>

                <div class="mb-3">
                    <label for="quotation_no" class="form-label fw-semibold">{{ translate('Quotation No') }}:</label>
                    <input type="text" name="quotation_no" id="quotation_no_input" oninput="checkQuotationNo(this.value)"
                        value="{{ $quotation->quotation_no }}" readonly
                        class="form-control w-auto d-inline-block">
                    <span id="order_no_status" class="small ms-2"></span>
                </div>

            </div>

            <!-- Wholesaler Info -->
            <div class="row my-4">

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ translate('Wholesaler') }}</label>
                    <input type="text"
                        value="{{ $order->wholeseller->wholesalerBusiness->company_name ?? 'N/A' }}"
                        name="wholesaler_" class="form-control" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ translate('Wholesaler Tier') }}</label>
                    <input type="text" value="{{ $order->wholeseller->tier ?? 'N/A' }}" class="form-control"
                        readonly>
                </div>

            </div>

            <!-- Table -->
            <div class="table-responsive border rounded my-4">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>{{ translate('Product Name') }}</th>
                            <th>{{ translate('Requested Qty') }}</th>
                            <th>{{ translate('Base Price') }}</th>
                            <th>{{ translate('Tax') }}</th>
                            <th>{{ translate('Final Price') }}</th>
                        </tr>
                    </thead>

                    <tbody id="product_table_body">
                        @foreach ($order->items as $item)
                        @php
                        $baseTotal = $item->base_price * $item->product_quantity;
                        $taxPercent = floatval(str_replace('%', '', $item->tax));
                        $taxAmount = ($baseTotal * $taxPercent) / 100;
                        $finalPrice = $baseTotal + $taxAmount;
                        @endphp

                        <tr data-product-id="{{ $item->product_id }}">

                            <td>{{ $item->product->name }}  ({{ $item->product_variation_type ?? 'No Variation' }})</td>

                            <td>
                                <input type="number" name="products[{{ $item->product_id }}][approved_quantity]"
                                    value="{{ $item->product_quantity }}"
                                    class="form-control form-control-sm w-auto admin-qty">
                            </td>

                            <td>
                                <input type="number" name="products[{{ $item->product_id }}][price]"
                                    value="{{ $item->base_price }}"
                                    class="form-control form-control-sm w-auto admin-price">
                            </td>

                            <td>
                                <input type="text" name="products[{{ $item->product_id }}][tax]"
                                    value="{{ $item->tax }}%"
                                    class="form-control form-control-sm w-auto admin-tax">
                            </td>

                            <td>
                                <input type="number" name="products[{{ $item->product_id }}][final_price]"
                                    value="{{ number_format($finalPrice, 2, '.', '') }}" step="0.01"
                                    class="form-control form-control-sm w-auto admin-final">
                            </td>

                          
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </form>

    </div>
</div>

@endsection
