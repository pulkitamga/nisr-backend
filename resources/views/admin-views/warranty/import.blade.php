@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')
@section('title', translate('warranty_import'))

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
            'placeholder' => translate('search_by_import_date'),
            'aria_label' => translate('search_by_import_date'),
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
            'url' => route('admin.warranty.import-history.export'),
            'form_id' => 'warranty-import-history-toolbar',
            'label' => translate('export'),
        ],
    ];
@endphp

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset('public/assets/back-end/img/import-icon.png') }}" alt="">
            {{ translate('warranty_import') }}
        </h2>
    </div>

    @if(session('validation_errors'))
        <div class="alert alert-warning">
            <strong>{{ translate('validation_failed') }}:</strong> {{ session('failed_rows') }} / {{ session('total_rows') }}
            <p>{{ translate('please_download_error_csv_to_see_which_columns_have_issues') }}</p>
            <a href="{{ route('admin.warranty.download_error_csv') }}" class="btn btn-danger">{{ translate('download_error_csv') }}</a>
            <div class="mt-2">
                <a href="{{ route('admin.warranty.continue-import') }}" class="btn btn-primary">{{ translate('continue_import') }}</a>
                <a href="{{ route('admin.warranty.reupload') }}" class="btn btn-secondary">{{ translate('reupload_file') }}</a>
            </div>
        </div>
    @elseif(session('import_summary'))
        <div class="alert alert-{{ session('import_summary.failed') > 0 ? 'warning' : 'success' }}">
            <strong>{{ translate('import_summary') }}:</strong>
            <ul>
                <li>{{ translate('created') }}: {{ session('import_summary.created') }}</li>
                <li>{{ translate('updated') }}: {{ session('import_summary.updated') }}</li>
                <li>{{ translate('failed') }}: {{ session('import_summary.failed') }}</li>
            </ul>
            @if (session('error_csv_path'))
                <a href="{{ route('admin.warranty.download_error_csv') }}" class="btn btn-danger mt-2">
                    {{ translate('download_error_csv') }}
                </a>
            @endif
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center gap-3">
            <h5 class="mb-0">{{ translate('upload_csv') }}</h5>
            <a href="{{ asset('sample_import.csv') }}" class="btn btn-primary" download>
                {{ translate('Download_Sample_Csv') }}
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.warranty.import.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>{{ translate('csv_file') }}</label>
                    <input type="file" name="csv_file" accept=".csv" class="form-control" required>
                    <small>{{ translate('columns: serial_number (required), product_sku (optional), warranty_months (required)') }}</small>
                </div>
                <button type="submit" class="btn btn--primary">{{ translate('import') }}</button>
            </form>
        </div>
    </div>

    <div class="mt-4">
        @include('admin-views.crm.partials._list-toolbar', [
            'toolbarId' => 'warranty-import-history-toolbar',
            'toolbarAction' => route('admin.warranty.import'),
            'toolbarResetUrl' => route('admin.warranty.import'),
            'toolbarFields' => $toolbarFields,
            'toolbarSummary' => $toolbarSummary,
        ])
    </div>

    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('import_history'),
            'listHeaderTotal' => $history->total(),
            'listHeaderActions' => $headerActions,
        ])
        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('date') }}</th>
                            <th>{{ translate('quantity') }}</th>
                            <th>{{ translate('action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $row)
                            <tr>
                                <td><span class="bidi-ltr d-inline-block">{{ $row->import_date }}</span></td>
                                <td>{{ $row->count }}</td>
                                <td>
                                    <a href="{{ route('admin.warranty.history-details', $row->import_date) }}" class="btn btn-sm btn-outline-primary">
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
