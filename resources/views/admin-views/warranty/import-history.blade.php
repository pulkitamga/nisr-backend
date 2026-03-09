@extends('layouts.back-end.app')
@section('title', translate('import_history'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize">
            {{translate('import_history')}}
        </h2>
        <a href="{{route('admin.warranty.import')}}" class="btn btn--primary">
            {{translate('new_import')}}
        </a>
    </div>

    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">
                {{translate('warranty_imports')}}
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
                        @foreach($history as $item)
                        <tr>
                            <td>{{$item->import_date}}</td>
                            <td>{{$item->count}}</td>
                            <td>
                                <a href="{{route('admin.warranty.history-details', $item->import_date)}}" class="btn btn-sm btn-outline-primary">
                                    {{translate('view_details')}}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end">
                    {!! $history->links() !!}
                </div>
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
    // Simple search for date
    $('.form-control').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
</script>
@endpush

