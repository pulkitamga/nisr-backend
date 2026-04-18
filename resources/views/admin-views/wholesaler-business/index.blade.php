@extends('layouts.back-end.app')

@section('title', translate('WholeSalers'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/wholesale-list.css') }}">
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
    @php
        $toolbarFields = [
            [
                'type' => 'search',
                'name' => 'searchValue',
                'label' => translate('Search'),
                'value' => request('searchValue'),
                'placeholder' => translate('Search_by_Name_or_Email_or_Phone'),
                'aria_label' => translate('Search_by_Name_or_Email_or_Phone'),
                'col_class' => 'col-xl-8 col-lg-8',
            ],
            [
                'type' => 'number',
                'name' => 'choose_first',
                'label' => translate('Rows_to_show'),
                'value' => request('choose_first'),
                'placeholder' => translate('Ex') . ' : 50',
                'col_class' => 'col-xl-4 col-lg-4',
                'attributes' => ['min' => '1'],
            ],
        ];
        $toolbarSummary = [
            [
                'label' => translate('Status'),
                'value' => translate('WholeSalers'),
            ],
        ];
        if (request()->filled('searchValue')) {
            $toolbarSummary[] = [
                'label' => translate('Search'),
                'value' => \Illuminate\Support\Str::limit(request('searchValue'), 28),
                'muted' => true,
            ];
        }
        if (request()->filled('choose_first')) {
            $toolbarSummary[] = [
                'label' => translate('Rows_to_show'),
                'value' => request('choose_first'),
                'muted' => true,
            ];
        }
        $headerActions = [
            [
                'type' => 'export',
                'url' => route('admin.wholesale.business.wholesaler.export'),
                'form_id' => 'wholesale-wholesalers-toolbar',
                'label' => translate('export'),
            ],
        ];
    @endphp

    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" alt="">
            {{ translate('WholeSalers') }}
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'wholesale-wholesalers-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.wholesale.business.list'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('WholeSalers'),
            'listHeaderTotal' => $wholesaler_business->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="table-responsive">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('Wholesaler') }}</th>
                        <th>{{ translate('Phone') }}</th>
                        <th>{{ translate('Tier') }}</th>
                        <th class="text-center">{{ translate('Status') }}</th>
                        <th class="text-center">{{ translate('MOQ Override') }}</th>
                        <th class="text-center">{{ translate('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wholesaler_business as $key => $business)
                        <tr>
                            <td>{{ $wholesaler_business->firstItem() + $key }}</td>
                            <td>
                                <div class="media align-items-center gap-10">
                                    <img
                                        class="rounded-circle avatar avatar-lg"
                                        alt=""
                                        src="{{ getStorageImages(path: $business->wholesaler->image_full_url, type: 'backend-profile') }}"
                                    >
                                    <div class="media-body">
                                        <a href="{{ route('admin.wholesale.business.wholesaler.profile', $business->id) }}" class="crm-primary-link">
                                            {{ $business->company_name ?? translate('N/A') }}
                                        </a>
                                        <span class="crm-primary-link__meta bidi-auto">{{ $business->wholesaler->name ?? translate('N/A') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="bidi-ltr">{{ $business->wholesaler->phone ?? translate('N/A') }}</span></td>
                            <td>{{ $business->wholesaler->tier ?? translate('N/A') }}</td>
                            <td>
                                <form action="{{ route('admin.customer.status-update') }}" method="POST" id="customer-status{{ $business->wholesaler['id'] }}-form" class="customer-status-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $business->wholesaler['id'] }}">
                                    <label class="switcher mx-auto">
                                        <input type="checkbox" class="switcher_input auto-submit-toggle" id="customer-status{{ $business->wholesaler['id'] }}" name="is_active" value="1" {{ $business->wholesaler['is_active'] == 1 ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <form id="moq-status-{{ $business->wholesaler->id }}-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $business->wholesaler->id }}">
                                    <label class="switcher mx-auto">
                                        <input type="checkbox" class="switcher_input moq-toggle" data-id="{{ $business->wholesaler->id }}" {{ $business->wholesaler->moq_override_enabled ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <div class="crm-row-actions">
                                    <div class="crm-row-actions__primary">
                                        <a class="btn btn-sm btn-info" href="{{ route('admin.wholesale.business.wholesaler.profile', $business->id) }}">
                                            {{ translate('View') }}
                                        </a>
                                        <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.wholesale.business.wholesaler.profile.edit', $business->id) }}">
                                            {{ translate('Edit') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                {{ translate('No Wholesaler Available') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-center justify-content-md-end">
                {{ $wholesaler_business->links() }}
            </div>
        </div>
    </div>
</div>

@push('script')
@include('admin-views.wholesaler-business.partials._list-js-config')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/wholesale-list.js') }}"></script>
@endpush

@endsection
