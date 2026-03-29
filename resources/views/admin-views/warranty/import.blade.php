@extends('layouts.back-end.app')
@section('title', translate('warranty_import'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset('public/assets/back-end/img/import-icon.png')}}" alt="">
            {{translate('warranty_import')}}
        </h2>
    </div>

    @if(session('validation_errors'))
    <div class="alert alert-warning">
        <strong>{{translate('validation_failed')}}:</strong> {{ session('failed_rows') }} out of {{ session('total_rows') }} rows contain errors.
        <p>{{translate('please_download_error_csv_to_see_which_columns_have_issues')}}</p>
        <a href="{{ route('admin.warranty.download_error_csv') }}" class="btn btn-danger">{{translate('download_error_csv')}}</a>
        <div class="mt-2">
            <a href="{{ route('admin.warranty.continue-import') }}" class="btn btn-primary">{{translate('continue_import')}}</a>
            <a href="{{ route('admin.warranty.reupload') }}" class="btn btn-secondary">{{translate('reupload_file')}}</a>
        </div>
    </div>
    @elseif(session('import_summary'))
    <div class="alert alert-{{session('import_summary.failed') > 0 ? 'warning' : 'success'}}">
        <strong>{{translate('import_summary')}}:</strong>
        <ul>
            <li>{{translate('created')}}: {{ session('import_summary.created') }}</li>
            <li>{{translate('updated')}}: {{ session('import_summary.updated') }}</li>
            <li>{{translate('failed')}}: {{ session('import_summary.failed') }}</li>
        </ul>
        @if (session('error_csv_path'))
        <a href="{{ route('admin.warranty.download_error_csv') }}" class="btn btn-danger mt-2">
            {{translate('download_error_csv')}}
        </a>
        @endif
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5>{{translate('upload_csv')}}</h5>

            <div>
                <a href="{{ asset('sample_import.csv') }}" class="btn btn-primary" download>
                    {{ translate('Download_Sample_Csv') }}
                </a>
            </div>

        </div>
        <div class="card-body">
            <form action="{{route('admin.warranty.import.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>{{translate('csv_file')}}</label>
                    <input type="file" name="csv_file" accept=".csv" class="form-control" required>
                    <small>{{translate('columns: serial_number (required), product_id (optional), warranty_months (required)')}}</small>
                </div>
                <button type="submit" class="btn btn--primary">{{translate('import')}}</button>
            </form>
        </div>
    </div>

    <!-- History Table -->
    <div class="card mt-4">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">
                {{translate('import_history')}}
            </h5>
            <form action="{{ url()->current() }}" method="GET">
                <div class="input-group input-group-merge input-group-custom">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <i class="tio-search"></i>
                        </div>
                    </div>
                    <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                        placeholder="{{ translate('search_by_Name_or_Email_or_Phone')}}" aria-label="{{ translate('Search orders') }}" value="{{ request('searchValue') }}">
                    <button type="submit" class="btn btn--primary">{{ translate('search')}}</button>
                </div>
            </form>
            <div class="dropdown">
                <a type="button"
                    class="btn btn-outline--primary text-nowrap"
                    href="{{ route('admin.warranty.import-history.export') }}">
                    <img width="14" src="{{ dynamicAsset(path: 'public/assets/back-end/img/excel.png') }}" alt="" class="excel">
                    <span class="ps-2">{{ translate('export') }}</span>
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">

                <table
                    style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('date')}}</th>
                            <th>{{translate('quantity')}}</th>
                            <th>{{translate('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $row)
                        <tr>
                            <td>{{$row->import_date}}</td>
                            <td>{{$row->count}}</td>
                            <td><a href="{{route('admin.warranty.history-details', $row->import_date)}}" class="btn btn-sm btn-outline-primary">{{translate('view_details')}}</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($history)==0)
            @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
            @endif
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    // Search on history table
    $('.form-control').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
</script>
@endpush

