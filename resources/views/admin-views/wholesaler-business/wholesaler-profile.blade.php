@extends('layouts.back-end.app')
@section('title', translate('wholesaler_details'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path:'public/assets/back-end/css/owl.min.css') }}">

<style>
    .nav-link.active {
        color: #377dff;
        border-bottom: 2px solid;
    }
    .field-colon {
        margin-inline: .75rem;
    }
    .bidi-auto {
        unicode-bidi: plaintext;
    }
    .bidi-ltr {
        direction: ltr;
        unicode-bidi: isolate;
        display: inline-block;
        text-align: left;
    }
</style>
@endpush

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
    <div class="d-print-none pb-2">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                    <img width="20" src="{{ dynamicAsset(path:'public/assets/back-end/img/add-new-seller.png') }}"
                        alt="">
                    {{ translate('wholesaler_details') }}
                </h2>
            </div>
        </div>
    </div>

    <div class="row g-2 h-100">
        {{-- ✅ Card 1: Wholesaler Info --}}
        <div class="col-xl-6 col-xxl-6 col--xxl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="mb-4 d-flex align-items-center gap-2">
                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/vendor-information.png') }}" alt="">
                        {{ translate('business_information')}}
                    </h4>

                    <div class="customer-details-new-card">
                        <img src="{{ getStorageImages(path: $wholesaler->image_full_url , type: 'backend-profile') }}"
                            alt="{{translate('Image')}}" class="aspect-1">
                        <div class="customer-details-new-card-content">
                            <h6 class="name line--limit-2">{{ $business->company_name }}</h6>
                            <ul class="customer-details-new-card-content-list">
                                <li><span class="key">{{translate('Contact')}}</span><span class="field-colon">:</span><strong
                                        class="value bidi-ltr">{{ $wholesaler->phone ?? translate('no_Data_found') }}</strong>
                                </li>
                                <li><span class="key">{{translate('Email')}}</span><span class="field-colon">:</span><strong
                                        class="value bidi-ltr">{{ $wholesaler->email ?? translate('no_Data_found') }}</strong>
                                </li>
                                <li><span class="key text-capitalize">{{translate('joined_date')}}</span><span
                                        class="field-colon">:</span><strong class="value bidi-ltr">{{ date('d M Y',
                                        strtotime($wholesaler->created_at)) }}</strong></li>
                                <li><span class="key">{{translate('reffer by')}}</span><span
                                        class="field-colon">:</span><strong class="value bidi-auto">{{ $wholesaler->refferd_by ??
                                        translate('no_Data_found') }}</strong></li>
                                <li><span class="key">{{translate('Tier')}}</span><span class="field-colon">:</span><strong
                                        class="value bidi-auto">{{ $wholesaler->tier ?? translate('no_Data_found') }}</strong>
                                </li>
                                <li><span class="key">{{translate('Discount')}}</span><span class="field-colon">:</span><strong
                                        class="value bidi-ltr">{{ $wholesaler->wholesaler_discount ?? translate('no_Data_found')
                                        }}%</strong></li>
                                <li><span class="key">{{translate('MOQ Override')}}</span><span
                                        class="field-colon">:</span><strong class="value bidi-auto">{{ $wholesaler->moq_override_enabled
                                        ? 'Yes' : 'No'}}</strong></li>


                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-xxl-6 col--xxl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="mb-4 d-flex align-items-center gap-2 text-capitalize">{{
                        translate('Company_information') }}</h4>
                    <div>
                        <ul class="customer-details-new-card-content-list">
                            <li><strong class="key">{{ translate('Company') }}</strong><span class="field-colon">:</span><span class="value bidi-auto">{{
                                    $business->company_name }}</span></li>
                            <li><strong class="key">{{ translate('Trade Name') }}</strong><span class="field-colon">:</span><span class="value bidi-auto">{{
                                    $business->trade_name }}</span></li>
                            <li><strong class="key">{{ translate('reg._no.') }}</strong><span class="field-colon">:</span><span class="value bidi-ltr">{{
                                    $business->registration_number }}</span></li>
                            <li><strong class="key">{{ translate('ID') }}</strong><span class="field-colon">:</span><span class="value bidi-ltr">{{
                                    $business->tax_id }}</span></li>
                            <li><strong class="key">{{ translate('VAT No.') }}</strong><span class="field-colon">:</span><span class="value bidi-ltr">{{
                                    $business->vat_number }}</span></li>
                        </ul>
                        <div class="mt-3">
                            @if($business->register_copy)
                            <a href="{{ asset('storage/register_copies/'.$business->register_copy) }}"
                                target="_blank">{{ translate('View Registration Copy') }}</a><br>
                            @endif
                            @if($business->tax_card_copy)
                            <a href="{{ asset('storage/tax_cards/'.$business->tax_card_copy) }}" target="_blank">{{
                                translate('View Tax Card') }}</a><br>
                            @endif
                            @if($business->vat_register_copy)
                            <a href="{{ asset('storage/vat_copies/'.$business->vat_register_copy) }}" target="_blank">{{
                                translate('View VAT Register') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(count($wholesaler->addresses)>0)
        <div class="col-xl-6 col-xxl-6 col--xxl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-4 d-flex align-items-center gap-2 text-capitalize">{{ translate('saved_address') }}
                    </h4>

                    {{-- Shipping Addresses --}}
                    <h5 class="mb-3">{{ translate('Shipping Addresses') }}</h5>
                    <div class="address-slider owl-theme owl-carousel mb-4">
                        @foreach($wholesaler->addresses->where('is_billing', 0) as $address)
                        <div class="customer-address-card customer-details-new-card p-3">
                            <h6 class="name text-14 mb-3">
                                {{ $address['address_type'] }}
                            </h6>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <p class="mb-1"><strong>{{ translate('Name') }}:</strong> <span class="bidi-auto">{{
                                        $address['contact_person_name'] }}</span></p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-1"><strong>{{ translate('Phone') }}:</strong> <span class="bidi-ltr">{{ $address['phone'] }}
                                    </span>
                                    </p>
                                </div>
                                <div class="col-12">
                                    <p class="mb-1"><strong>{{ translate('Address') }}:</strong> <span class="bidi-auto">{{ $address['address']
                                        }}</span></p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Billing Addresses --}}
                    <h5 class="mb-3">{{ translate('Billing Addresses') }}</h5>
                    <div class="address-slider owl-theme owl-carousel">
                        @foreach($wholesaler->addresses->where('is_billing', 1) as $address)
                        <div class="customer-address-card customer-details-new-card p-3">
                            <h6 class="name text-14 mb-3">
                                {{ $address['address_type'] }}
                            </h6>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <p class="mb-1"><strong>{{ translate('Name') }}:</strong> <span class="bidi-auto">{{
                                        $address['contact_person_name'] }}</span></p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-1"><strong>{{ translate('Phone') }}:</strong> <span class="bidi-ltr">{{ $address['phone'] }}
                                    </span>
                                    </p>
                                </div>
                                <div class="col-12">
                                    <p class="mb-1"><strong>{{ translate('Address') }}:</strong> <span class="bidi-auto">{{ $address['address']
                                        }}</span></p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
        @endif



        <div class="col-xl-12 col-xxl-12 col--xxl-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="text-capitalize">{{ translate('contacts') }}</h5>
                    <div class="dropdown">
                        <a type="button" class="btn btn-outline--primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addContactModal">
                            {{ translate('Add Contact') }}

                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($business->contacts && $business->contacts->count())
                    <div class="table-responsive datatable-custom">

                        <table

                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                            <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th>{{ translate('Name') }}</th>
                                    <th>{{ translate('Email') }}</th>
                                    <th>{{ translate('Phone') }}</th>
                                    <th>{{ translate('Active') }}</th>
                                    <th class="text-center">{{ translate('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($business->contacts as $contact)
                                <tr>
                                    <td>{{ $contact->first_name }} {{ $contact->last_name }}</td>
                                    <td><span class="bidi-ltr">{{ $contact->email }}</span></td>
                                    <td><span class="bidi-ltr">{{ $contact->phone_number }}</span></td>
                                    <td>
                                        <span class="btn btn-sm btn-outline-{{ $contact->is_active ? 'success' : 'secondary' }}">
                                            {{ $contact->is_active ? translate('Yes') : translate('No') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewContactModal-{{ $contact->id }}">
                                            <i class="tio-visible"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editContactModal-{{ $contact->id }}">
                                            <i class="tio-edit"></i>
                                        </button>

                                        <form method="POST" action="{{ route('admin.wholesale.business.wholsaler-contect.softDelete', $contact->id) }}" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('{{ translate('Are_you_sure_to_delete_this') }}')">
                                                <i class="tio-delete"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @include('admin-views.wholesaler-business.partials.contact-view', ['contact' => $contact])
                                @include('admin-views.wholesaler-business.partials.edit-contact-modal', ['contact' => $contact])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        {{ translate('No contact details found.') }}
                    </div>
                    @endif
                </div>
            </div>
            @include('admin-views.wholesaler-business.partials.create-contact')
        </div>
    </div>


    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.wholesale.business.order.assign-invoice-no') }}">
                @csrf
                <input type="hidden" name="order_id" id="invoice_order_id">
                <div class="modal-content text-start">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ translate('Assign Invoice No') }}</h5>
                        <button type="button"
                            class="radius-50 border-0 font-weight-bold text-black-50 position-absolute right-3 top-3 z-index-99"
                            data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"> <span
                                aria-hidden="true">x</span></i></button>
                    </div>
                    <div class="modal-body">
                        <label>{{ translate('Invoice NO') }}</label>
                        <input type="text" name="invoice_no" id="invoice_no" class="form-control" required>
                        <small id="invoiceAvailability" class="text-sm mt-1"></small>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="submitInvoice" class="btn btn--primary"
                            disabled>{{ translate('Submit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="modal fade" id="confirmOrderModal" tabindex="-1" aria-labelledby="confirmOrderLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.wholesale.business.order.assign-confirm-no') }}">
                @csrf
                <input type="hidden" name="order_id" id="confirm_order_id">
                <div class="modal-content text-start">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ translate('Assign Confirm Order No') }}</h5>
                        <button type="button"
                            class="radius-50 border-0 font-weight-bold text-black-50 position-absolute right-3 top-3 z-index-99"
                            data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"> <span
                                aria-hidden="true">x</span></i></button>
                    </div>
                    <div class="modal-body">
                        <label>{{ translate('Confirm Order No') }}</label>
                        <input type="text" name="confirm_order_no" id="confirm_order_no"
                            class="form-control" required>
                        <small id="confirmOrderAvailability" class="text-sm mt-1"></small>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="submitConfirmOrder" class="btn btn--primary"
                            disabled>{{ translate('Submit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    {{-- ✅ Card 3: Orders --}}
    <div class="col-lg-12 pt-lg-6">
        <div class="inline-page-menu my-4">
            <ul class="list-unstyled" id="orderTabs">
                <li class="">
                    <a class="nav-link active" href="#" data-type="purchase">{{ translate('Purchase_order') }}</a>
                </li>
                <li class="">
                    <a class="nav-link" href="#" data-type="quotation">{{ translate('Quotation') }}</a>
                </li>
                <li class="">
                    <a class="nav-link" href="#" data-type="confirmed">{{ translate('Confirmed Order') }}</a>
                </li>
            </ul>
        </div>

        <div class="card mt-3">
            <div id="orderTableArea">
                {{-- Table will be loaded here dynamically --}}
                <div class="text-center p-4 text-muted">{{ translate('Loading') }}...</div>
            </div>
        </div>

    </div>


    <div class="modal fade" id="purchaseOrderModal" tabindex="-1"
        aria-labelledby="purchaseOrderLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="purchaseOrderForm" method="POST"
                action="{{ route('admin.wholesale.business.order.assign-number') }}">
                @csrf
                <input type="hidden" name="order_id" id="modal_order_id">
                <div class="modal-content text-start">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ translate('Assign Purchase Order No') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="{{ translate('Close') }}">&times;</button>


                    </div>
                    <div class="modal-body">
                        <label>{{ translate('Purchase_Order_No') }}</label>
                        <input type="text" name="purchase_order_no" id="purchase_order_no"
                            class="form-control" required>
                        <small id="availabilityMessage" class="text-sm mt-1"></small>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="submitOrderNo" class="btn btn--primary"
                            disabled>{{ translate('Submit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @endsection

    @push('script')



    <script src="{{ dynamicAsset(path:'public/assets/back-end/js/owl.min.js') }}"></script>
    <script>
        'use strict';
        $('.order-statistics-slider, .address-slider').owlCarousel({
            margin: 16,
            loop: false,
            autoWidth: true,
        });

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.order-tab').forEach(tab => tab.classList.add('d-none'));
                document.querySelector(this.dataset.target).classList.remove('d-none');
            });
        });




        $(document).ready(function() {
            function loadTable(type) {
                $('#orderTableArea').html('<div class="text-center p-4 text-muted">{{ __('Loading...') }}</div>');

                $.get("{{ route('admin.wholesale.business.orders.by-type') }}", {
                    type: type,
                        business_id: "{{ $business->wholesaler_id }}"

                }, function(data) {
                    $('#orderTableArea').html(data.html);
                });
            }

            loadTable('purchase');

            $('#orderTabs .nav-link').on('click', function(e) {
                e.preventDefault();
                $('#orderTabs .nav-link').removeClass('active');
                $(this).addClass('active');
                let type = $(this).data('type');
                loadTable(type);
            });
        });


        const csrfToken = @json(csrf_token());

        function submitDeleteForm(deleteUrl) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = deleteUrl;

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrfToken;
            form.appendChild(tokenInput);

            document.body.appendChild(form);
            form.submit();
        }


        function openOrderNoPopup(orderId) {
            $('#modal_order_id').val(orderId);
            $('#purchase_order_no').val('');
            $('#availabilityMessage').text('');
            $('#submitOrderNo').prop('disabled', true);
            $('#purchaseOrderModal').modal('show');
        }

        $('#purchase_order_no').on('keyup', function() {
            let poNo = $(this).val();

            if (poNo.length < 1) {
                $('#availabilityMessage').text('');
                $('#submitOrderNo').prop('disabled', true);
                return;
            }

            $.get("{{ route('admin.wholesale.business.order.check-number') }}", {
                number: poNo
            }, function(res) {
                if (res.exists) {
                    $('#availabilityMessage').text(@json(__('Order number already exists'))).addClass('text-danger').removeClass('text-success');
                    $('#submitOrderNo').prop('disabled', true);
                } else {
                    $('#availabilityMessage').text(@json(__('Order number available'))).addClass('text-success').removeClass('text-danger');
                    $('#submitOrderNo').prop('disabled', false);
                }
            });
        });


        function confirmAndDelete(deleteUrl) {
            if (confirm(@json(__('Are you sure you want to delete this order?')))) {
                submitDeleteForm(deleteUrl);
            }
        }



        let currentPopup = null;

        function toggleActionPopup(button) {
            // Hide existing popup
            if (currentPopup) {
                currentPopup.remove();
                currentPopup = null;
            }

            // Clone popup div and move to body
            const originalPopup = button.nextElementSibling;
            const popup = originalPopup.cloneNode(true);
            popup.classList.remove('d-none');
            popup.classList.add('show');

            document.body.appendChild(popup);
            currentPopup = popup;

            // Get button position
            const rect = button.getBoundingClientRect();
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

            // Position popup just below the button
            popup.style.position = 'absolute';
            popup.style.top = (rect.bottom + scrollTop) + 'px';
            popup.style.left = 1500 + 'px';
            popup.style.zIndex = 1050;
        }

        // Click outside to close popup
        document.addEventListener('click', function(e) {
            if (
                !e.target.closest('.action-popup') &&
                !e.target.closest('.action-btn')
            ) {
                if (currentPopup) {
                    currentPopup.remove();
                    currentPopup = null;
                }
            }
        });

        function openInvoicePopup(orderId) {
            $('#invoice_order_id').val(orderId);
            $('#invoice_no').val('');
            $('#invoiceAvailability').text('');
            $('#submitInvoice').prop('disabled', true);
            $('#invoiceModal').modal('show');
        }

        function openConfirmOrderPopup(orderId) {
            $('#confirm_order_id').val(orderId);
            $('#confirm_order_no').val('');
            $('#confirmOrderAvailability').text('');
            $('#submitConfirmOrder').prop('disabled', true);
            $('#confirmOrderModal').modal('show');
        }

        $('#invoice_no').on('keyup', function() {
            let value = $(this).val();
            if (value.length < 1) {
                $('#invoiceAvailability').text('');
                $('#submitInvoice').prop('disabled', true);
                return;
            }
            $.get("{{ route('admin.wholesale.business.order.check-confirm-invoice-no') }}", {
                type: 'invoice_no',
                number: value
            }, function(res) {
                if (res.exists) {
                    $('#invoiceAvailability').text('Invoice number already exists').addClass('text-danger').removeClass('text-success');
                    $('#submitInvoice').prop('disabled', true);
                } else {
                    $('#invoiceAvailability').text('Invoice number available').addClass('text-success').removeClass('text-danger');
                    $('#submitInvoice').prop('disabled', false);
                }
            });
        });

        $('#confirm_order_no').on('keyup', function() {
            let value = $(this).val();
            if (value.length < 1) {
                $('#confirmOrderAvailability').text('');
                $('#submitConfirmOrder').prop('disabled', true);
                return;
            }
            $.get("{{ route('admin.wholesale.business.order.check-confirm-invoice-no') }}", {
                type: 'confirm_order_no',
                number: value
            }, function(res) {
                if (res.exists) {
                    $('#confirmOrderAvailability').text('Confirm order number already exists').addClass('text-danger').removeClass('text-success');
                    $('#submitConfirmOrder').prop('disabled', true);
                } else {
                    $('#confirmOrderAvailability').text('Confirm order number available').addClass('text-success').removeClass('text-danger');
                    $('#submitConfirmOrder').prop('disabled', false);
                }
            });
        });
    </script>
    @endpush

