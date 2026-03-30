@extends('layouts.back-end.app')
@section('title', translate('activations'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>{{translate('activations_list')}}</h5>
            <div class="d-flex gap-2">
                <form action="{{ url()->current() }}" method="GET" id="methodFilterForm">
                    <select class="form-control w-10rem" name="method" onchange="document.getElementById('methodFilterForm').submit()">
                        <option value="" {{ request('method')=='' ? 'selected':'' }}>{{translate('all_methods')}}</option>
                        <option value="order_activation" {{ request('method')=='order_activation' ? 'selected':'' }}>{{translate('user_profile')}}</option>
                        <option value="user_public_form" {{ request('method')=='user_public_form' ? 'selected':'' }}>{{translate('user_public_form')}}</option>
                        <option value="admin_manual" {{ request('method')=='admin_manual' ? 'selected':'' }}>{{translate('admin_manual')}}</option>
                    </select>
                </form>

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

                <a href="{{route('admin.warranty.activation.manual')}}" class="btn btn--primary text-nowrap">{{translate('manual_activate')}}</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">

                <table

                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('serial')}}</th>
                            <th>{{translate('customer')}}</th>
                            <th>{{translate('method')}}</th>
                            <th>{{translate('start_end_date')}}</th>
                            <th>{{translate('status')}}</th>
                            <th>{{translate('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activations as $warranty)
                        <tr>
                            <td>{{$warranty->serial_number}}</td>
                            <td>{{$warranty->user->name ?? $warranty->activated_by_name}}</td>
                            <td>{{$warranty->activation_method}}</td>
                            <td>{{$warranty->start_date}} to {{$warranty->end_date}}</td>
                            <td>
                                @php
                                $status = $warranty->statusLabel();
                                $badge = match($status) {
                                'preactivated' => 'secondary',
                                'active' => 'success',
                                'expired' => 'danger',
                                default => 'primary'
                                };
                                @endphp
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
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end">
                    {!! $activations->links() !!}
                </div>
            </div>
            @if(count($activations)==0)
            @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
            @endif
        </div>
    </div>
</div>
@endsection