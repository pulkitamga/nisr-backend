@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')
@section('title', translate('blacklist'))

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
            'label' => translate('search'),
            'value' => request('searchValue'),
            'placeholder' => translate('search_by_serial'),
            'aria_label' => translate('search_by_serial'),
            'col_class' => 'col-xl-4 col-lg-12',
        ],
    ];

    $toolbarSummary = [];

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
            'url' => route('admin.warranty.blacklist.export'),
            'form_id' => 'warranty-blacklist-toolbar',
            'label' => translate('export'),
        ],
        [
            'href' => route('admin.warranty.blacklist.add'),
            'class' => 'btn btn--primary text-nowrap',
            'label' => translate('add_blacklist'),
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
            {{ translate('blacklist') }}
            <span class="badge badge-soft-dark radius-50 fz-14 ms-1">{{ $blacklists->total() }}</span>
        </h2>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'warranty-blacklist-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.warranty.blacklist'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('blacklist'),
            'listHeaderTotal' => $blacklists->total(),
            'listHeaderActions' => $headerActions,
        ])

        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('serial_number') }}</th>
                            <th>{{ translate('reason') }}</th>
                            <th>{{ translate('blacklisted_at') }}</th>
                            <th>{{ translate('action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blacklists as $item)
                            <tr>
                                <td>{{ $item->serial_number }}</td>
                                <td>{{ $item->reason }}</td>
                                <td><span class="bidi-ltr d-inline-block">{{ $item->blacklisted_at->format('Y-m-d') }}</span></td>
                                <td>
                                    <form action="{{ route('admin.warranty.blacklist.remove', $item->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to remove this item from blacklist?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ translate('remove') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    @include('layouts.back-end._empty-state', ['text' => 'no_record_found', 'image' => 'default'])
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($blacklists->isNotEmpty())
                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-lg-end">
                        {!! $blacklists->links() !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
