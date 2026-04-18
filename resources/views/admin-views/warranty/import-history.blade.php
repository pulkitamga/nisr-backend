@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')
@section('title', translate('import_history'))

@php
    $toolbarFields = [
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
            'label' => translate('Search'),
            'value' => request('searchValue'),
            'placeholder' => translate('search_by_import_date'),
            'aria_label' => translate('search_by_import_date'),
            'col_class' => 'col-xl-4 col-lg-12',
        ],
    ];

    $toolbarSummary = [];

    if (request()->filled('searchValue')) {
        $toolbarSummary[] = [
            'label' => translate('Search'),
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
            'url' => route('admin.warranty.import-history.export'),
            'form_id' => 'warranty-import-history-toolbar',
            'label' => translate('export'),
        ],
        [
            'href' => route('admin.warranty.import'),
            'class' => 'btn btn--primary text-nowrap',
            'label' => translate('new_import'),
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
            <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/import-icon.png') }}" alt="">
            {{ translate('import_history') }}
            <span class="badge badge-soft-dark radius-50 fz-14 ms-1">{{ $history->total() }}</span>
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'warranty-import-history-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.warranty.import-history'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('warranty_imports'),
            'listHeaderTotal' => $history->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('DATE') }}</th>
                            <th>{{ translate('Quantity') }}</th>
                            <th>{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $item)
                            <tr>
                                <td><span class="bidi-ltr d-inline-block">{{ $item->import_date }}</span></td>
                                <td>{{ $item->count }}</td>
                                <td>
                                    <a href="{{ route('admin.warranty.history-details', $item->import_date) }}" class="btn btn-sm btn-outline-primary">
                                        {{ translate('view_details') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    @include('layouts.back-end._empty-state', ['text' => 'no_record_found', 'image' => 'default'])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($history->isNotEmpty())
                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-lg-end">
                        {!! $history->links() !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
