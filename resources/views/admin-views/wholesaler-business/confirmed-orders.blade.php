@extends('layouts.back-end.app')

@section('title', translate('Confirmed_Orders'))

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
<script src="https://cdn.tailwindcss.com"></script>

<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
            {{translate('Confirmed_Orders')}}
        </h2>
    </div>
    <!-- 🔍 Confirmed Orders Filter Row -->
    <div class="card p-4 mb-4 shadow-sm">
        <form method="GET" action="{{ url()->current() }}">
            <div class="row g-3 align-items-end">
                <!-- Date From -->
                <div class="col-md-3">
                    <label class="form-label">{{ translate('Date From') }}</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <!-- Date To -->
                <div class="col-md-3">
                    <label class="form-label">{{ translate('Date To') }}</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <!-- Delivery Status -->
                <div class="col-md-2">
                    <label class="form-label">{{ translate('Delivery Status') }}</label>
                    <select name="delivery_status" class="form-control">
                        <option value="">{{ translate('All') }}</option>
                        <option value="complete" {{ request('delivery_status')=='completed' ? 'selected' : '' }}>
                            {{ translate('Complete') }}
                        </option>
                        <option value="partials" {{ request('delivery_status')=='partials' ? 'selected' : '' }}>
                            {{ translate('partials') }}
                        </option>
                        <option value="pending" {{ request('delivery_status')=='pending' ? 'selected' : '' }}>
                            {{ translate('Pending') }}
                        </option>
                    </select>
                </div>

                <!-- Payment Status -->
                <div class="col-md-2">
                    <label class="form-label">{{ translate('Payment Status') }}</label>
                    <select name="payment_status" class="form-control">
                        <option value="">{{ translate('All') }}</option>
                        <option value="paid" {{ request('payment_status')=='paid' ? 'selected' : '' }}>
                            {{ translate('Paid') }}
                        </option>
                        <option value="unpaid" {{ request('payment_status')=='unpaid' ? 'selected' : '' }}>
                            {{ translate('Unpaid') }}
                        </option>
                        <option value="partials" {{ request('payment_status')=='partials' ? 'selected' : '' }}>
                            {{ translate('partials') }}
                        </option>
                    </select>
                </div>

                <!-- Price Sort -->
                <div class="col-md-2">
                    <label class="form-label">{{ translate('Price') }}</label>
                    <select name="price_sort" class="form-control">
                        <option value="">{{ translate('Default') }}</option>
                        <option value="low_high" {{ request('price_sort')=='low_high' ? 'selected' : '' }}>
                            {{ translate('Low to High') }}
                        </option>
                        <option value="high_low" {{ request('price_sort')=='high_low' ? 'selected' : '' }}>
                            {{ translate('High to Low') }}
                        </option>
                    </select>
                </div>

                <!-- Submit -->
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn--primary">
                        <i class="tio-filter-list"></i> {{ translate('Apply Filters') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div class="px-3 py-4 light-bg">
                <div class="row g-2 align-items-center flex-grow-1">
                    <div class="col-md-7 col-lg-8">
                        <h5 class="text-capitalize d-flex gap-1">
                            {{translate('Confirmed_Orders')}}
                            <span class="badge badge-soft-dark radius-50 fz-12">{{$orders->total()}}</span>
                        </h5>
                    </div>
                    <div class="col-md-5 col-lg-4 d-flex gap-3 flex-sm-nowrap justify-content-end">
                        <div class="input-group input-group-custom input-group-merge">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="tio-search"></i>
                                </div>
                            </div>
                            <input id="datatableSearch_" type="search" class="form-control"
                                placeholder="{{ translate('Search...') }}" aria-label="{{ translate('Search') }}">
                        </div>
                        <div class="dropdown">
                            <a type="button" class="align-items-center btn btn-block btn-outline--primary d-flex pe-4"
                                href="{{route('admin.wholesale.business.wholesale-confirm.export')}}">
                                <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}"
                                    class="excel" alt="">
                                <span class="ps-2">{{ translate('export') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('Date')}}</th>
                            <th>{{translate('Purchase_Order_No')}}</th>
                            <th>{{translate('external_Po_Number')}}</th>
                            <th>{{translate('Quotation_No')}}</th>
                            <th>{{translate('Confirmed_order_no')}}</th>
                            <th>{{translate('Inovice_no')}}</th>
                            <th>{{translate('Wholesaler')}}</th>
                            <th>{{translate('Delivery_Status')}}</th>
                            <th>{{translate('Payment_status')}}</th>
                            <th>{{translate('Final_price')}}</th>
                            <th class="text-center">{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rowColor = ['bg-light', 'bg-white']; @endphp

                        @forelse($orders as $key => $order)
                        @php $bgClass = $rowColor[$key % 2]; @endphp
                        <tr class="{{ $bgClass }}">
                            <td>{{ $orders->firstItem() + $key }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->confirmed_at)->format('d/m/Y') }}</td>
                            <td>{{ $order->purchase_order_no }}</td>
                            <td>{{ $order->external_po_number ?? '' }}</td>
                            <td>{{ $order->quotation_no }}</td>
                            <td>{{ $order->confirm_order_no ?? ''}}</td>
                            <td>{{ $order->invoice_no ?? ''}}</td>
                            <td>{{ $order->wholeseller->wholesalerBusiness->company_name ?? '' }}</td>
                            <td>
                                @php
                                $deliveryStatus = strtolower($order->delivery_status ?? 'pending');
                                $deliveryColors = [
                                'delivered' => 'bg-green-100 text-green-800',
                                'partials' => 'bg-yellow-100 text-yellow-800',
                                'pending' => 'bg-red-100 text-red-800',
                                ];
                                $deliveryClass = $deliveryColors[$deliveryStatus] ?? 'bg-gray-100
                                text-gray-800';
                                @endphp

                                <span
                                    class="px-5 py-1 text-cap text-xs leading-5 rounded-full {{ $deliveryClass }}">
                                    {{ ucfirst($deliveryStatus) }}
                                </span>
                            </td>
                            <td>
                                @php
                                $status = strtolower($order->payment_status ?? 'unpaid');
                                $statusColors = [
                                'paid' => 'bg-green-100 text-green-800',
                                'partials' => 'bg-yellow-100 text-yellow-800',
                                'unpaid' => 'bg-red-100 text-red-800',
                                ];
                                $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                                @endphp

                                <span
                                    class="px-5 py-1 text-cap text-xs leading-5b rounded-full {{ $colorClass }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td> {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount:   $order->final_price), currencyCode: getCurrencyCode()) }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2 position-relative">
                                    {{-- View Invoice --}}
                                    <a title="{{ translate('View Details') }}"
                                        class="btn btn-outline-info btn-sm square-btn"
                                        href="{{ route('admin.wholesale.business.confirm-order.tracking-page', $order->id) }}">
                                        <i class="tio-invisible"></i>
                                    </a>
                                    <a data-id="{{ $order->order_id }}" class="btn btn-info btn-sm wholesale-order-status-history" data-toggle="modal" data-target="#exampleModalLong"><i class="tio-history"></i></a>


                                    {{-- Action Button --}}
                                    <button type="button" class="btn btn-outline-secondary btn-sm square-btn action-btn"
                                        onclick="toggleActionPopup(this)">
                                        <i class="tio-more-horizontal"></i>
                                    </button>

                                    <div class="action-popup shadow-sm p-2 bg-white border rounded d-none position-absolute z-3"
                                        style="top: 100%; inset-inline-start: 0; min-width: 150px;">
                                        @if (!$order->invoice_no)
                                        <a href="javascript:void(0)" class="dropdown-item text-dark py-1 px-2"
                                            onclick="openInvoicePopup({{ $order->id }})">
                                            <i class="tio-edit"></i> {{ translate('Invoice No') }}
                                        </a>
                                        @endif
                                        @if (!$order->confirm_order_no)
                                        <a href="javascript:void(0)" class="dropdown-item text-dark py-1 px-2"
                                            onclick="openConfirmOrderPopup({{ $order->id }})">
                                            <i class="tio-edit"></i> {{ translate('Confirm Order No') }}
                                        </a>
                                        @endif
                                        @if ($order->invoice_no)
                                        <a class="dropdown-item text-dark py-1 px-2"
                                            href="{{ route('admin.wholesale.business.confirm-order.complete.invoice', $order->id) }}">
                                            <i class="tio-wallet"></i> {{ translate('Invoice') }}
                                        </a>
                                        @endif
                                        <a class="dropdown-item text-dark py-1 px-2"
                                            href="{{ route('admin.wholesale.business.orders.payment', $order->id) }}">
                                            <i class="tio-wallet"></i> {{ translate('Payment') }}
                                        </a>
                                        <a class="dropdown-item text-dark py-1 px-2"
                                            href="{{ route('admin.wholesale.business.orders.delivery', $order->id) }}">
                                            <i class="tio-truck"></i> {{ translate('Delivery') }}
                                        </a>
                                        @if ($order->attachments)
                                        <a class="dropdown-item text-info py-1 px-2" href="{{ asset('storage/wholesale_attachment/'.$order->attachments) }}" target="_blank">
                                            <i class="tio-attachment"></i> {{ translate('Attachment') }}
                                        </a>
                                        @endif
                                        <a class="dropdown-item text-danger py-1 px-2" href="javascript:void(0);"
                                            onclick="confirmAndDelete('{{ route('admin.wholesale.business.confirem.order.delete', $order->id) }}')">
                                            <i class="tio-delete"></i>{{ translate('Delete') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                {{ translate('No order found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
        <div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.wholesale.business.order.assign-invoice-no') }}">
                    @csrf
                    <input type="hidden" name="order_id" id="invoice_order_id">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ translate('Assign Invoice No') }}</h5>
                            <button type="button"
                                class="radius-50 border-0 font-weight-bold text-black-50 position-absolute right-3 top-3 z-index-99"
                                data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"> <span
                                    aria-hidden="true">x</span></i></button>
                        </div>
                        <div class="modal-body">
                            <label>{{ translate('Invoice No') }}</label>
                            <input type="text" name="invoice_no" id="invoice_no" class="form-control" required>
                            <small id="invoiceAvailability" class="text-sm mt-1"></small>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" id="submitInvoice" class="btn btn--primary" disabled> {{ translate('Submit') }}
                            </button>
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
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ translate('Assign Confirm Order No') }}</h5>
                            <button type="button"
                                class="radius-50 border-0 font-weight-bold text-black-50 position-absolute right-3 top-3 z-index-99"
                                data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"> <span
                                    aria-hidden="true">x</span></i></button>
                        </div>
                        <div class="modal-body">
                            <label>{{ translate('Confirm Order No') }}</label>
                            <input type="text" name="confirm_order_no" id="confirm_order_no" class="form-control"
                                required>
                            <small id="confirmOrderAvailability" class="text-sm mt-1"></small>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" id="submitConfirmOrder" class="btn btn--primary"
                                disabled> {{ translate('Submit') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="table-responsive mt-4">
        <div class="px-4 d-flex justify-content-center justify-content-md-end">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@include('admin-views.wholesaler-business.partials.activity-modal')

<span class="status-history-url" data-url="{{ route('admin.wholesale.business.ajax-activity-history', ['order' => ':id'] ) }}"></span>
@endsection
@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
</script>


<script>
    function confirmAndDelete(deleteUrl) {
        Swal.fire({
            title: @json(__('Confirm Deletion')),
            text: @json(__('Are you sure you want to delete this order?')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: @json(__('Yes, delete it!')),
            cancelButtonText: @json(__('Cancel'))
        }).then((result) => {
            if (result.isConfirmed) {
                submitDeleteForm(deleteUrl);
            }
        });
    }


    let currentPopup = null;

    function toggleActionPopup(button) {
        if (currentPopup) {
            currentPopup.remove();
            currentPopup = null;
        }

        const originalPopup = button.nextElementSibling;
        const popup = originalPopup.cloneNode(true);
        popup.classList.remove('d-none');
        popup.classList.add('show');
        popup.style.visibility = 'hidden';
        document.body.appendChild(popup);
        currentPopup = popup;

        const rect = button.getBoundingClientRect();

        popup.style.position = 'fixed';
        popup.style.top = rect.bottom + 'px';
        popup.style.left = rect.left + 'px';
        popup.style.zIndex = 1050;
        popup.style.visibility = 'visible';

        const popupRight = rect.left + popup.offsetWidth;
        const screenWidth = window.innerWidth;

        if (popupRight > screenWidth) {
            popup.style.left = (screenWidth - popup.offsetWidth - 10) + 'px'; // shift left
        }
    }


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


    document.getElementById('datatableSearch_').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');

        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.indexOf(query) > -1) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>


<script>
    $('.wholesale-order-status-history').on('click', function() {
        let url = $('.status-history-url').data('url');
        let id = $(this).data('id');
        url = url.replace(":id", id)
        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                $(".load-with-ajax").empty().append(data);
            }
        });
    });

    function viewAttachment(url) {
        const ext = url.split('.').pop().toLowerCase();
        let htmlContent = '';

        if (ext === 'pdf') {
            htmlContent = `<iframe src="${url}" width="100%" height="400px"></iframe>`;
        } else {
            htmlContent = `<p>{{ __('Cannot preview this file.') }} <a href="${url}" target="_blank">{{ __('Click here to download') }}</a></p>`;
        }

        Swal.fire({
            title: @json(__('Attachment')),
            html: htmlContent,
            showCloseButton: true,
            showCancelButton: false,
            confirmButtonText: @json(__('Close'))
        });
    }
</script>

@endpush


