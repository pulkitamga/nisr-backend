@extends('layouts.back-end.app')

@section('title', translate('Wholesale Order Invoice'))


@push('css_or_js')
<style>
    .bidi-auto {
        unicode-bidi: plaintext;
    }
    .bidi-ltr {
        direction: ltr;
        unicode-bidi: isolate;
        display: inline-block;
        text-align: left;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        inset-inline-start: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    /* Modal content */
    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 40%;
        text-align: center;
    }

    /* Close button */
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    /* Hover effect for close button */
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        #print-area,
        #print-area * {
            visibility: visible;
        }

        #print-area {
            position: absolute;
            inset-inline-start: 0;
            top: 0;
            width: 100%;
            z-index: 9999;

        }

        .card-header,
        .btn {
            display: none !important;
        }

        body.no-image-print #print-area img {
            display: none !important;
        }

        body.no-image-print #print-area div[style*="background"] {
            background: none !important;
        }

        /* ✅ Preserve background colors */
        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* ✅ Fix for Bootstrap/Tailwind grid */
        .row {
            display: flex !important;
            flex-wrap: wrap !important;
            margin-inline-end: -15px !important;
            margin-inline-start: -15px !important;
        }

        .col-md-5,
        .col-md-6,
        .col-md-7 {
            position: relative !important;
            width: 100% !important;
            padding-inline-end: 15px !important;
            padding-inline-start: 15px !important;
        }

        .col-md-5 {
            flex: 0 0 41.666667% !important;
            max-width: 41.666667% !important;
        }

        .col-md-6 {
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }

        .col-md-7 {
            flex: 0 0 58.333333% !important;
            max-width: 58.333333% !important;
        }

        .text-right {
            text-align: right !important;
        }

        /* ✅ Prevent page break inside tables and sections */
        table,
        tr,
        td,
        th,
        .card-body,
        .content,
        .row,
        .table-responsive {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        thead {
            display: table-header-group;
            background-color: #f3f3f3 !important;
        }

        tfoot {
            display: table-footer-group;
        }

        th {
            background-color: #f3f3f3 !important;
            color: #000 !important;
        }

        /* ✅ Fit content to A4 */
        @page {
            size: A4 portrait;
            margin: 0;
        }

    }
</style>


@endpush

@section('content')
@php($isRtl = Session::get('direction') === 'rtl')


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
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>{{ translate('Quotation_No') }} #<span class="bidi-ltr">{{ $order->quotation_no }}</span></h5>

            <button onclick="showPrintModal()" class="btn btn--primary">
                <i class="tio-email"></i> {{ translate('Send_to_company') }}
            </button>
            <button onclick="showPrintModal()" class="btn btn--primary">
                <i class="tio-print"></i> {{ translate('Print Invoice') }}
            </button>
        </div>
        <div id="printModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close align-self-sm-end" onclick="closeModal()">&times;</span>
                <h3 class="mb-4">{{ translate('Choose Print Option') }}</h3>
                <button onclick="printWithImages()" class="btn btn--primary">{{ translate('Print with Images') }}</button>
                <button onclick="printWithoutImages()" class="btn btn-secondary mt-4">{{ translate('Print without Images') }}</button>
            </div>
        </div>
        <div id="print-area" class="border">
            @if(isset($quotationImages['image_header']))
            <img src="{{ asset('storage/company/' . $quotationImages['image_header']['image_name']) }}"
                alt="Invoice Header"
                style="width: 100%; height: auto;">

            @endif
            @if(isset($quotationImages['image_bg']))
            <div class="invoice-bg-wholesale m-5" style="background-image: url('{{ asset('storage/company/' . $quotationImages['image_bg']['image_name']) }}');"></div>
            @endif
            <div style="position: relative; z-index: 1; padding: 3rem;">

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>{{ translate('Business Info') }}</h6>
                        <p>
                            <strong class="bidi-auto">{{ $order->wholeseller->wholesalerBusiness->company_name }}</strong><br>
                            <span class="bidi-ltr">{{ $order->wholeseller->email ?? '' }}</span><br>
                            <span class="bidi-ltr">{{ $order->wholeseller->phone ?? '' }}</span><br>
                            <span class="bidi-auto">{{ $order->wholeseller_tier ?? '' }}</span>

                        </p>
                    </div>
                    <div class="col-md-6 {{ $isRtl ? 'text-right' : 'text-left' }}">
                        <h6>{{ translate('Order Info') }}</h6>
                        <p>
                            <strong>{{ translate('Invoice NO') }}:</strong> #<span class="bidi-ltr">{{ $order->invoice_no }}</span><br>
                            <strong>{{ translate('Purchase Order NO') }}:</strong> #<span class="bidi-ltr">{{ $order->purchase_order_no }}</span><br>
                            <strong>{{ translate('Quotation NO') }}:</strong> #<span class="bidi-ltr">{{ $order->quotation_no }}</span><br>
                            @if($order->confirm_order_no)
                            <strong>{{ translate('Confirem Order NO') }}:</strong> #<span class="bidi-ltr">{{ $order->confirm_order_no }}</span><br>
                            @endif
                            <strong>{{ translate('Date') }}:</strong> <span class="bidi-ltr">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</span><br>

                        </p>
                    </div>
                </div>
                @php($totelTax=0)
                <h6 class="mb-3">{{ translate('Product Details') }}</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{ translate('Product') }}</th>
                                <th>{{ translate('Quantity') }}</th>
                                <th>{{ translate('Price') }}</th>
                                <th>{{ translate('Tax') }}</th>
                                <th>{{ translate('Final Price') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $index => $item)
                            @php($tax=0)
                            @php($tax=$item->final_price -($item->product_quantity*$item->base_price))
                            @php($totelTax+=$tax)

                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->product->getTranslatedField('name') ?? __('N/A') }}</td>
                                <td>{{ $item->product_quantity }}</td>
                                <td>{{ webCurrencyConverter(amount:$item->base_price) }}</td>
                                <td>{{ webCurrencyConverter(amount:$tax) }}</td>
                                <td>{{ webCurrencyConverter(amount:$item->final_price) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row">
                    <div class="col-md-7">
                        <div class="p-3">
                            @if (isset($quotation) && !empty(strip_tags($quotation->terms_and_conditions)))
                            <div class="mt-4">
                                <h4 class="font-weight-bold mb-1">{{ translate('Terms and Conditions') }}</h4>
                                <p>{!!getTranslatedValue($quotation, 'terms_and_conditions', $quotation->terms_and_conditions) !!}</p>

                            </div>
                            @endif

                            @if (isset($quotation) && !empty(strip_tags($quotation->note)))
                            <div class="mt-4">
                                <h4 class="font-weight-bold mb-1">{{ translate('Note') }}</h4>
                                <p>{{ strip_tags($quotation->note) }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-5 ms-auto">
                        <div class="p-3">
                            <h6 class="mb-3 font-weight-bold text-center border py-lg-2 bg-dark text-white">
                                {{ translate('Sub Total') }}
                            </h6>
                            <ul class="list-unstyled mb-3">
                                <li class="d-flex justify-content-between mb-1">
                                    <span>{{ __('Tax') }}</span>
                                    <span>{{ webCurrencyConverter(amount:$totelTax) }}</span>
                                </li>
                            </ul>
                            @if(isset($quotation) && $quotation->metas && $quotation->metas->count())
                            <ul class="list-unstyled mb-3">
                                @foreach($quotation->metas as $meta)
                                <li class="d-flex justify-content-between mb-1">
                                    <span>{{ ucwords(str_replace('_', ' ', $meta->key)) }}</span>
                                    <span>{{ webCurrencyConverter(amount: $meta->value) }}</span>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                            @if (isset($quotation) && !empty(strip_tags($quotation->wholesaler_discount_amount)))
                            <ul class="list-unstyled mb-3">
                                <li class="d-flex justify-content-between mb-1">
                                    <span>{{ __('Wholesaler Discount') }}</span>
                                    <span>{{ webCurrencyConverter(amount:$quotation->wholesaler_discount_amount) }}</span>
                                </li>
                            </ul>
                            @endif
                            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                <strong>{{ translate('Total') }}</strong>
                                <strong>{{ webCurrencyConverter(amount: $order->final_price) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>


            </div>


            @if(isset($quotationImages['image_footer']))
            <img src="{{ asset('storage/company/' . $quotationImages['image_footer']['image_name']) }}"
                alt="Invoice Footer"
                style="width: 100%; height: auto;" class="mt-4">
            @endif
        </div>


    </div>
</div>
</div>

@endsection


@push('script')
<script>
    // Show Modal
    function showPrintModal() {
        document.getElementById('printModal').style.display = "block";
    }

    // Close Modal
    function closeModal() {
        document.getElementById('printModal').style.display = "none";
    }

    // Print with Images
    function printWithImages() {
        closeModal(); // Close the modal
        window.print(); // Trigger print with images
    }

    // Print without Images
    function printWithoutImages() {
        closeModal();
        document.body.classList.add('no-image-print');
        setTimeout(() => {
            window.print();
            setTimeout(() => {
                document.body.classList.remove('no-image-print');
            }, 1000);
        }, 100);
    }
</script>

@endpush
