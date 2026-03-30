@extends('layouts.back-end.app')
@section('title', translate('blacklist'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>{{translate('blacklist')}}</h5>
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
                <a href="{{ route('admin.warranty.blacklist.add') }}" class="btn btn--primary text-nowrap">{{ translate('add_blacklist') }}</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">

                <table

                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('serial_number')}}</th>
                            <th>{{translate('reason')}}</th>
                            <th>{{translate('blacklisted_at')}}</th>
                            <th>{{translate('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($blacklists as $item)
                        <tr>
                            <td>{{$item->serial_number}}</td>
                            <td>{{$item->reason}}</td>
                            <td>{{$item->blacklisted_at->format('Y-m-d')}}</td>
                            <td>
                                <form action="{{ route('admin.warranty.blacklist.remove', $item->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to remove this item from blacklist?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ translate('remove') }}</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end">
                    {!! $blacklists->links() !!}
                </div>
            </div>
            @if(count($blacklists)==0)
            @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
            @endif
        </div>
    </div>
</div>
@endsection