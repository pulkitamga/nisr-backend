@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Branch Vendors'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
@endpush

@section('content')
@php
    $toolbarFields = [
        [
            'type' => 'number',
            'name' => 'choose_first',
            'label' => translate('Rows_to_show'),
            'value' => request('choose_first'),
            'placeholder' => translate('Ex') . ' : 200',
            'col_class' => 'col-xl-2 col-lg-4',
            'attributes' => ['min' => '1'],
        ],
        [
            'type' => 'search',
            'name' => 'searchValue',
            'label' => translate('search'),
            'value' => request('searchValue'),
            'placeholder' => translate('search_by_vendor_info'),
            'aria_label' => translate('search_by_vendor_info'),
            'col_class' => 'col-xl-5 col-lg-8',
        ],
    ];

    $toolbarSummary = [];
    if (request()->filled('searchValue')) {
        $toolbarSummary[] = ['label' => translate('search'), 'value' => Str::limit(request('searchValue'), 28), 'muted' => true];
    }
    if (request()->filled('choose_first')) {
        $toolbarSummary[] = ['label' => translate('Rows_to_show'), 'value' => request('choose_first'), 'muted' => true];
    }

    $headerActions = [
        [
            'type' => 'export',
            'url' => route('admin.branch.vendors.export'),
            'form_id' => 'branch-vendors-toolbar',
            'label' => translate('export'),
        ],
    ];
@endphp

<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/products.png') }}" alt="">
            {{ translate('Branch Vendors') }}
            <span class="badge badge-soft-dark radius-50 fz-14 ms-1">{{ $vendors->total() }}</span>
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'branch-vendors-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.branch.vendors'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('Branch Vendors'),
            'listHeaderTotal' => $vendors->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="table-responsive">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{ translate('Vendor') }}</th>
                        <th>{{ translate('email') }}</th>
                        <th>{{ translate('Phone') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th class="text-center">{{ translate('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendors as $key => $vendor)
                        <tr>
                            <td>{{ $vendors->firstItem() + $key }}</td>
                            <td>
                                <a href="{{ route('admin.branch.vendors.view', $vendor->id) }}" class="crm-primary-link">
                                    {{ trim($vendor->f_name . ' ' . $vendor->l_name) }}
                                </a>
                            </td>
                            <td>{{ $vendor->email }}</td>
                            <td>{{ $vendor->phone }}</td>
                            <td>{{ translate($vendor->status) }}</td>
                            <td class="text-center">
                                <div class="crm-row-actions">
                                    <div class="crm-row-actions__primary">
                                        <a href="{{ route('admin.branch.vendors.view', $vendor->id) }}" class="btn btn-outline-info btn-sm">
                                            {{ translate('View') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">{{ translate('No vendors found for this branch.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-end">
                {!! $vendors->links() !!}
            </div>
        </div>
    </div>
</div>
@endsection
