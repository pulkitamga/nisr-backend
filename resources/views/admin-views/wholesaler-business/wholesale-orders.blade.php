@extends('layouts.back-end.app')

@section('title', translate('Quotation_Sent'))

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
            {{translate('Quotation_Sent')}}
        </h2>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card p-4 mb-4 shadow-sm">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row g-3">
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

                        <!-- Tier -->
                        <div class="col-md-2">
                            <label class="form-label">{{ translate('Tier') }}</label>
                            <select name="tier" class="form-control">
                                <option value="">{{ translate('All Tiers') }}</option>
                                <option value="gold" {{ request('tier')=='gold' ? 'selected' : '' }}>{{ translate('Gold') }}</option>
                                <option value="silver" {{ request('tier')=='silver' ? 'selected' : '' }}>{{ translate('Silver') }}</option>
                                <option value="bronze" {{ request('tier')=='bronze' ? 'selected' : '' }}>{{ translate('Bronze') }}</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-2">
                            <label class="form-label">{{ translate('Status') }}</label>
                            <select name="status" class="form-control">
                                <option value="">{{ translate('All') }}</option>
                                <option value="sent" {{ request('status')=='sent' ? 'selected' : '' }}>{{ translate('Sent') }}</option>
                                <option value="accepted" {{ request('status')=='accepted' ? 'selected' : '' }}>{{ translate('Accepted') }}
                                </option>
                                <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>{{ translate('Rejected') }}
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

                        <!-- Submit Button -->
                        <div class="col-12 text-end mt-3">
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
                                    {{translate('Quotation_list')}}
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
                                        placeholder="{{ translate('Search...') }}" aria-label="Search">
                                </div>
                                <div class="dropdown">
                                    <a type="button" class="align-items-center btn btn-block btn-outline--primary d-flex pr-4"
                                        href="{{route('admin.wholesale.business.wholesale-quotation.export')}}">
                                        <img width="14"
                                            src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}"
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
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('Date')}}</th>
                                    <th>{{translate('Order_No')}}</th>
                                    <th>{{translate('Quotation_No')}}</th>
                                    <th>{{translate('Wholesaler')}}</th>
                                    <th>{{translate('Tier')}}</th>
                                    <th class="text-center">{{translate('Status')}}</th>
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
                                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</td>
                                    <td>{{ $order->purchase_order_no }}</td>
                                    <td>{{ $order->quotation_no }}</td>
                                    <td>{{ $order->wholeseller->wholesalerBusiness->company_name ?? '' }}</td>
                                    <td>{{ $order->wholeseller_tier ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                        $status = $order->status;
                                        $color = match($status) {
                                        'sent' => 'bg-blue-100 text-blue-800',
                                        'accepted' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                        };
                                        @endphp

                                        <span class="px-5 py-1 text-cap text-xs rounded-full {{ $color }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>

                                    <td> {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount:  $order->final_price), currencyCode: getCurrencyCode()) }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a title="{{translate('View Details')}}"
                                                class="btn btn-outline-info btn-sm square-btn"
                                                href="{{ route('admin.wholesale.business.orders.invoice', $order->id) }}">
                                                <i class="tio-invisible"></i>
                                            </a>
                                            <a title="{{translate('View Details')}}"
                                                class="btn btn-outline-info btn-sm square-btn"
                                                href="{{ route('admin.wholesale.business.orders.invoice.edit', $order->id) }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a href="javascript:void(0);" title="{{ translate('Delete') }}"
                                                class="btn btn-outline-danger btn-sm square-btn"
                                                onclick="confirmAndDelete('{{ route('admin.wholesale.business.quotation.delete', $order->id) }}')">
                                                <i class="tio-delete"></i>
                                            </a>
                                            <a data-id="{{ $order->order_id }}" class="btn btn-info btn-sm wholesale-order-status-history" data-toggle="modal" data-target="#exampleModalLong"><i class="tio-history"></i></a>

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

    <span class="status-history-url" data-url="{{ route('admin.wholesale.business.ajax-activity-history', ['order' => ':id'] ) }}"></span>


    @endsection
    @push('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmAndDelete(deleteUrl) {
            Swal.fire({
                title: 'Confirm Deletion',
                text: 'Are you sure you want to delete this order?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = deleteUrl;
                }
            });
        }

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
    @endpush