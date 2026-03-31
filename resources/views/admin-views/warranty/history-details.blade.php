@extends('layouts.back-end.app')
@section('title', translate('import_details'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0">{{translate('details_for')}} {{$date}}</h2>
        <a href="{{route('admin.warranty.import')}}" class="btn btn--primary">{{translate('back_to_imports')}}</a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>{{translate('details')}}</h5>
            <div class="d-flex gap-2">

                <form action="{{ url()->current() }}" method="GET">
                    <div class="input-group input-group-merge input-group-custom">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="tio-search"></i></div>
                        </div>
                        <input type="search" name="searchValue" class="form-control"
                            placeholder="{{ translate('search_by_serial') }}"
                            value="{{ request('searchValue') }}">
                        <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                    </div>
                </form>
                <a href="{{ route('admin.warranty.history-details.export', ['date' => $date, 'searchValue' => request('searchValue')]) }}"
                    class="btn btn--primary text-nowrap">{{translate('export_csv')}}</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">

                <table

                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('serial_number')}}</th>
                            <th>{{translate('product')}}</th>
                            <th>{{translate('status')}}</th>
                            <th>{{translate('created_at')}}</th>
                            <th>{{translate('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($details as $warranty)
                        <tr>
                            <td>{{$warranty->serial_number}}</td>
                            <td>{{$warranty->product->name ?? '-'}}</td>
                            <td><span class="badge badge-soft-primary">{{$warranty->status}}</span></td>
                            <td><span class="bidi-ltr d-inline-block">{{$warranty->created_at->format('Y-m-d H:i')}}</span></td>
                            <td>
                                <a href="{{ route('admin.warranty.activation.manual.view', ['serial_number' => $warranty->serial_number]) }}" class="btn btn-sm btn-outline-primary">{{translate('activate')}}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end">
                    {!! $details->links() !!}
                </div>
            </div>
            @if(count($details)==0)
            @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
            @endif
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $('input[name="searchValue"]').on('input', function() {
        const value = ($(this).val() || '').toLowerCase();
        $('table tbody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.indexOf(value) > -1);
        });
    });
</script>
@endpush
