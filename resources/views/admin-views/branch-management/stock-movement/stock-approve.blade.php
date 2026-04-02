@extends('layouts.back-end.app')

@section('title', translate('Pending Stock Approvals'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/branch-management.css') }}">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="22" src="{{ dynamicAsset(path: 'public/assets/back-end/img/stock.png') }}" alt="">
            {{ translate('Pending Stock Approvals') }}
            <span class="badge badge-soft-dark radius-50 fz-12">{{ $transfers->sum(fn($transfer) => $transfer->products->count()) }}</span>
        </h2>
        <p class="text-muted mb-0 mt-2">{{ translate('Approve or reject incoming stock transfers to your branch.') }}</p>
    </div>

    @foreach (['success' => 'success', 'error' => 'danger'] as $flashKey => $alertType)
        @if (session($flashKey))
            <div class="alert alert-{{ $alertType }}">{{ session($flashKey) }}</div>
        @endif
    @endforeach

    <div class="card">
        <div class="card-body p-0">
            @if($transfers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('Product') }}</th>
                                <th>{{ translate('Category') }}</th>
                                <th class="text-center">{{ translate('Quantity') }}</th>
                                <th>{{ translate('Sent From') }}</th>
                                <th>{{ translate('Transfer Date') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfers as $transfer)
                                @foreach($transfer->products as $product)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.stock-transfer.view', $transfer->id) }}" class="crm-primary-link">
                                                {{ $product->product?->getTranslatedField('name') ?? translate('not_available') }}
                                            </a>
                                        </td>
                                        <td>{{ $product->category?->getTranslatedField('name') ?? translate('not_available') }}</td>
                                        <td class="text-center">{{ $product->quantity }}</td>
                                        <td>{{ $transfer->fromBranch?->getTranslatedField('branch_name') ?? translate('not_available') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($transfer->transfer_date)->translatedFormat('d M Y, h:i A') }}</td>
                                        <td class="text-center">
                                            <div class="crm-row-actions">
                                                <div class="crm-row-actions__primary">
                                                    <form action="{{ route('admin.branch.stock.approve', ['id' => $product->id]) }}" method="POST" class="branch-stock-decision-form" data-confirm-type="approve">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success btn-sm">
                                                            {{ translate('approve') }}
                                                        </button>
                                                    </form>
                                                </div>
                                                <div class="dropdown crm-row-actions__menu">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                                        <i class="tio-more-horizontal"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a href="{{ route('admin.stock-transfer.view', $transfer->id) }}" class="dropdown-item">
                                                            <i class="tio-invisible mr-2"></i>{{ translate('view') }}
                                                        </a>
                                                        <form action="{{ route('admin.branch.stock.reject', ['id' => $product->id]) }}" method="POST" class="crm-row-actions__form branch-stock-decision-form" data-confirm-type="reject">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="tio-clear mr-2"></i>{{ translate('reject') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="branch-empty-state text-center text-muted py-5 px-4">
                    <i class="tio-inbox branch-empty-state__icon"></i>
                    <p class="mb-0 mt-3">{{ translate('No stock pending for approval.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('script')
    @include('admin-views.branch-management.partials._stock-approval-js-config')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/branch-stock-approval.js') }}"></script>
@endpush
