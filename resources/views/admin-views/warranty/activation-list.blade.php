@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')
@section('title', translate('activations'))

@php
    $methodOptions = [
        '' => translate('all_methods'),
        'order_activation' => translate('user_profile'),
        'user_public_form' => translate('user_public_form'),
        'admin_manual' => translate('admin_manual'),
    ];

    $toolbarFields = [
        [
            'type' => 'select',
            'name' => 'method',
            'label' => translate('activation_method'),
            'value' => request('method', ''),
            'options' => $methodOptions,
            'input_class' => 'form-control js-select2-custom',
        ],
        [
            'type' => 'number',
            'name' => 'choose_first',
            'label' => translate('Rows_to_show'),
            'value' => request('choose_first'),
            'placeholder' => translate('Ex') . ' : 200',
            'attributes' => ['min' => '1'],
        ],
        [
            'type' => 'search',
            'name' => 'searchValue',
            'label' => translate('search'),
            'value' => request('searchValue'),
            'placeholder' => translate('search_by_serial'),
            'aria_label' => translate('search_by_serial'),
            'col_class' => 'col-xl-4 col-lg-12',
        ],
    ];

    $toolbarSummary = [
        [
            'label' => translate('activation_method'),
            'value' => $methodOptions[(string) request('method', '')] ?? translate('all_methods'),
        ],
    ];

    if (request()->filled('searchValue')) {
        $toolbarSummary[] = [
            'label' => translate('search'),
            'value' => Str::limit(request('searchValue'), 28),
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
            'url' => route('admin.warranty.activation.export'),
            'form_id' => 'warranty-activation-toolbar',
            'label' => translate('export'),
        ],
        [
            'href' => route('admin.warranty.activation.manual.view'),
            'class' => 'btn btn--primary text-nowrap',
            'label' => translate('manual_activate'),
        ],
    ];
@endphp

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/warranty.png') }}" alt="">
            {{ translate('activations_list') }}
            <span class="badge badge-soft-dark radius-50 fz-14 ms-1">{{ $activations->total() }}</span>
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'warranty-activation-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.warranty.activation.list'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('activations_list'),
            'listHeaderTotal' => $activations->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('serial') }}</th>
                            <th>{{ translate('customer') }}</th>
                            <th>{{ translate('activation_method') }}</th>
                            <th>{{ translate('start_end_date') }}</th>
                            <th>{{ translate('status') }}</th>
                            <th>{{ translate('action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activations as $warranty)
                            @php
                                $status = $warranty->statusLabel();
                                $badge = match($status) {
                                    'preactivated' => 'secondary',
                                    'active' => 'success',
                                    'expired' => 'danger',
                                    default => 'primary'
                                };
                                $activationMethod = trim((string) $warranty->activation_method) !== '' ? (string) $warranty->activation_method : 'unknown';
                                $activationMethodLabel = translate($activationMethod);
                                if ($activationMethodLabel === $activationMethod) {
                                    $activationMethodLabel = ucwords(str_replace('_', ' ', $activationMethod));
                                }
                            @endphp
                            <tr>
                                <td>{{ $warranty->serial_number }}</td>
                                <td>{{ $warranty->user->name ?? $warranty->activated_by_name }}</td>
                                <td>{{ $activationMethodLabel }}</td>
                                <td>
                                    <span class="bidi-ltr d-inline-block">{{ $warranty->start_date }}</span>
                                    {{ translate('to') }}
                                    <span class="bidi-ltr d-inline-block">{{ $warranty->end_date }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-soft-{{ $badge }}">{{ translate($status) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.warranty.activation.view', $warranty->id) }}"
                                        class="btn btn-sm btn-outline-info"
                                        title="{{ translate('View Details') }}">
                                        <i class="tio-visible"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    @include('layouts.back-end._empty-state', ['text' => 'no_record_found', 'image' => 'default'])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($activations->isNotEmpty())
                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-lg-end">
                        {!! $activations->links() !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
