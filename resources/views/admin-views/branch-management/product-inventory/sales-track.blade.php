@extends('layouts.back-end.app')
@section('title', translate('Branch Sales Tracking'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h1 mb-0 text-capitalize">{{ translate('Branch Sales Tracking') }}</h2>
        <span class="badge badge-soft-dark radius-50 fz-14">
            {{ $orders->total() }} {{ translate('results_found') }}
        </span>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ url()->current() }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="searchValue" class="form-control" placeholder="{{ translate('Search_by_Name_or_Email_or_Phone') }}" value="{{ request('searchValue') }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">{{ translate('Search') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table">
                    <thead class="thead-light text-capitalize">
                        <tr>
                            <th class="text-center">#</th>
                            <th>{{ translate('Order_ID') }}</th>
                            <th>{{ translate('Customer') }}</th>
                            <th>{{ translate('Products') }}</th>
                            <th>{{ translate('total_qty') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Order_Status') }}</th>
                            <th>{{ translate('Delivery_Status') }}</th>
                            <th>{{ translate('Return') }}</th>
                            <th>{{ translate('DATE') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $key => $order)
                            <tr>
                                <td class="text-center">{{ $orders->firstItem() + $key }}</td>
                                <td class="text-primary font-weight-bold">#{{ $order->id }}</td>
                                <td>
                                    {{ $order->customer?->f_name }} {{ $order->customer?->l_name }}<br>
                                    <small class="text-muted">{{ $order->customer?->phone }}</small>
                                </td>
                                <td>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($order->details as $detail)
                                            <li>{{ $detail->product?->name }} <strong>(x{{ $detail->qty }})</strong></li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td><span class="badge badge-soft-info">{{ $order->details->sum('qty') }}</span></td>
                                <td>{{ \App\CentralLogics\Helpers::currency_converter($order->order_amount) }}</td>
                                <td>
                                    <span class="badge badge-{{ $order->order_status == 'delivered' ? 'success' : ($order->order_status == 'returned' ? 'danger' : 'warning') }}">
                                        {{ translate($order->order_status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $order->order_status == 'delivered' ? translate('Delivered') : translate('Pending') }}
                                </td>
                                <td>
                                    {{ $order->order_status == 'returned' ? translate('Returned') : '-' }}
                                </td>
                                <td>{{ $order->created_at->format('d M, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center p-4">
                                    {{ translate('No sales data found for this branch.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end">
            {!! $orders->links() !!}
        </div>
    </div>
</div>
@endsection
