@extends('layouts.back-end.app')

@section('title', translate('Wholesaler Order Requests'))

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" alt="">
                {{ translate('Wholesaler_Purchase_Requests') }}
            </h2>
        </div>
        <!-- 🔍 Order Request Filter Row -->
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
                    <!-- Status -->
                    <div class="col-md-2">
                        <label class="form-label">{{ translate('Status') }}</label>
                        <select name="status" class="form-control">
                            <option value="">{{ translate('All') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                            <option value="Processed" {{ request('status') == 'Processed' ? 'selected' : '' }}>{{ translate('Processed') }}
                            </option>
                            <option value="	Quotationsend" {{ request('status') == '	Quotationsend' ? 'selected' : '' }}>
                                {{ translate('Quotationsend') }}</option>
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
                                {{ translate('Wholesaler_Purchase_Requests') }}
                                <span class="badge badge-soft-dark radius-50 fz-12">{{ $orders->total() }}</span>
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
                                    href="{{ route('admin.wholesale.business.wholesale-purchase.export') }}">
                                    <img width="14"
                                        src="{{ dynamicAsset(path: 'public/assets/back-end/img/excel.png') }}"
                                        class="excel" alt="">
                                    <span class="ps-2">{{ translate('export') }}</span>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="table-responsive">
                    <table
                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('DATE') }}</th>
                                <th>{{ translate('Purchase_order_no') }}</th>
                                <th>{{ translate('Wholesaler') }}</th>
                                <th>{{ translate('Tier') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowColor = ['bg-light', 'bg-white']; @endphp

                            @forelse($orders as $key => $order)
                                @php $bgClass = $rowColor[$key % 2]; @endphp
                                {{-- @foreach ($order->items as $index => $item)
                        <tr class="{{ $bgClass }}">
                            @if ($index == 0)
                            <td>{{ $orders->firstItem() + $key }}</td>
                            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</td>
                            <td>{{ $order->purchase_order_no}}</td>
                            <td>
                                {{$order->wholeseller->wholesalerBusiness->company_name ?? ''}}
                            </td>
                            <td>{{ $order->wholeseller_tier ?? __('N/A') }}</td>
                            <td>
                                @php
                                $status = $order->status;
                                $badgeClass = match ($status) {
                                'pending' => 'danger', // red
                                'processed' => 'info', // green
                                default => 'success', // blue
                                };
                                @endphp

                                <span class="btn bg-soft-{{ $badgeClass }} text-{{ $badgeClass }} p-2">
                                    {{ ucfirst($status) }}
                                </span>
                                </span>
                            </td>
                            <td>
                                <div class="row gap-3">
                                    @if ($order->status == 'pending')
                                    <button class="btn btn-warning btn-sm square-btn"
                                        onclick="openOrderNoPopup({{ $order->id }})">
                                        <i class="tio-edit"></i>
                                    </button>
                                    @elseif ($order->status == 'processed')
                                    <a title="{{translate('Edit')}}" class="btn btn-outline-dark btn-sm square-btn"
                                        href="{{ route('admin.wholesale.business.order.view', $order->id) }}">
                                        <i class="tio-edit"></i>
                                    </a>
                                    @else
                                    <a title="{{translate('View')}}" class="btn btn-outline-info btn-sm square-btn"
                                        href="{{ route('admin.wholesale.business.purchase.order.view', $order->id) }}">
                                        <i class="tio-visible"></i>
                                    </a>
                                    @endif
                                    <a href="javascript:void(0);" title="{{ translate('Delete') }}"
                                        class="btn btn-outline-danger btn-sm square-btn"
                                        onclick="confirmAndDelete('{{ route('admin.wholesale.business.order.delete', $order->id) }}')">
                                        <i class="tio-delete"></i>
                                    </a>
                                    <a data-id="{{ $order->order_id }}" class="btn btn-info btn-sm wholesale-order-status-history" data-toggle="modal" data-target="#exampleModalLong"><i class="tio-history"></i></a>

                                </div>


                                <!-- Modal -->
                                <div class="modal fade" id="purchaseOrderModal" tabindex="-1"
                                    aria-labelledby="purchaseOrderLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form id="purchaseOrderForm" method="POST"
                                            action="{{ route('admin.wholesale.business.order.assign-number') }}">
                                            @csrf
                                            <input type="hidden" name="order_id" id="modal_order_id">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ translate('Assign Purchase Order No') }}</h5>
                                                    <button type="button"
                                                        class="radius-50 border-0 font-weight-bold text-black-50 position-absolute right-3 top-3 z-index-99"
                                                        data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"> <span
                                                            aria-hidden="true">x</span></i></button>


                                                </div>
                                                <div class="modal-body">
                                                    <label>{{ translate('Purchase Order No') }}</label>
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

                            </td>
                            @endif
                        </tr>
                        @endforeach --}}
                        
                                <tr class="{{ $bgClass }}">
                                    <td>{{ $orders->firstItem() + $key }}</td>
                                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</td>
                                    <td>{{ $order->purchase_order_no }}</td>
                                    <td>{{ $order->wholeseller->wholesalerBusiness->company_name ?? '' }}</td>
                                    <td>{{ $order->wholeseller_tier ?? __('N/A') }}</td>
                                    <td>
                                        @php
                                            $status = strtolower($order->status);
                                            $badgeClass = match ($status) {
                                                'pending' => 'danger',
                                                'processed' => 'info',
                                                default => 'success',
                                            };
                                        @endphp
                                        <span class="btn bg-soft-{{ $badgeClass }} text-{{ $badgeClass }} p-2">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="row gap-3">
                                            @if ($status === 'pending')
                                                <button class="btn btn-warning btn-sm square-btn"
                                                    onclick="openOrderNoPopup({{ $order->id }})">
                                                    <i class="tio-edit"></i>
                                                </button>
                                            @elseif ($status === 'processed')
                                                <a title="{{ translate('Edit') }}"
                                                    class="btn btn-outline-dark btn-sm square-btn"
                                                    href="{{ route('admin.wholesale.business.order.view', $order->id) }}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                            @else
                                                <a title="{{ translate('View') }}"
                                                    class="btn btn-outline-info btn-sm square-btn"
                                                    href="{{ route('admin.wholesale.business.purchase.order.view', $order->id) }}">
                                                    <i class="tio-visible"></i>
                                                </a>
                                            @endif

                                            <a href="javascript:void(0);" title="{{ translate('Delete') }}"
                                                class="btn btn-outline-danger btn-sm square-btn"
                                                onclick="confirmAndDelete('{{ route('admin.wholesale.business.order.delete', $order->id) }}')">
                                                <i class="tio-delete"></i>
                                            </a>

                                            <a data-id="{{ $order->order_id }}"
                                                class="btn btn-info btn-sm wholesale-order-status-history"
                                                data-toggle="modal" data-target="#exampleModalLong">
                                                <i class="tio-history"></i>
                                            </a>
                                        </div>
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        {{ translate('No order requests found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>


                    </table>
                </div>
                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-center justify-content-md-end">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    @include('admin-views.wholesaler-business.partials.activity-modal')
    <!-- Modal -->
    <div class="modal fade" id="purchaseOrderModal" tabindex="-1" aria-labelledby="purchaseOrderLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="purchaseOrderForm" method="POST"
                action="{{ route('admin.wholesale.business.order.assign-number') }}">
                @csrf
                <input type="hidden" name="order_id" id="modal_order_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ translate('Assign Purchase Order No') }}</h5>
                        <button type="button"
                            class="radius-50 border-0 font-weight-bold text-black-50 position-absolute right-3 top-3 z-index-99"
                            data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"> <span aria-hidden="true">x</span></i></button>


                    </div>
                    <div class="modal-body">
                        <label>{{ translate('Purchase Order No') }}</label>
                        <input type="text" name="purchase_order_no" id="purchase_order_no" class="form-control"
                            required>
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
    <span class="status-history-url"
        data-url="{{ route('admin.wholesale.business.ajax-activity-history', ['order' => ':id']) }}"></span>

@endsection
@push('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/libs/bootstrap-5/bootstrap.bundle.min.js') }}"></script>
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
                    $('#availabilityMessage').text(@json(__('Order number already exists'))).addClass('text-danger')
                        .removeClass('text-success');
                    $('#submitOrderNo').prop('disabled', true);
                } else {
                    $('#availabilityMessage').text(@json(__('Order number available'))).addClass('text-success')
                        .removeClass('text-danger');
                    $('#submitOrderNo').prop('disabled', false);
                }
            });
        });


        document.getElementById('datatableSearch_').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');

            rows.forEach(row => {
                // Convert all text inside the row to lowercase
                const rowText = row.textContent.toLowerCase();
                if (rowText.indexOf(query) > -1) {
                    row.style.display = ''; // Show row
                } else {
                    row.style.display = 'none'; // Hide row
                }
            });
        });
    </script>
@endpush


