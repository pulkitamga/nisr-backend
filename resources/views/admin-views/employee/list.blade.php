@extends('layouts.back-end.app')

@section('title', translate('employee_list'))

@section('content')
    @php($superAdminRole = config('permissions_admin.super_admin_role', 'Super Admin'))
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/employee.png')}}" width="20" alt="">
                {{translate('employee_list')}}
            </h2>
        </div>
        <div class="card">
            <div class="card-header flex-wrap gap-10">
                <div class="px-sm-3 py-4 flex-grow-1">
                    <div class="d-flex justify-content-between gap-3 flex-wrap align-items-center">
                        <div class="">
                            <h5 class="mb-0 text-capitalize gap-2">
                                {{translate('employee_table')}}
                                <span class="badge badge-soft-dark radius-50 fz-12">{{$employees->total()}}</span>
                            </h5>
                        </div>
                        <div class="align-items-center d-flex gap-3 justify-content-lg-end flex-wrap flex-lg-nowrap flex-grow-1">
                            <div class="">
                                <form action="{{ url()->current() }}" method="GET">
                                    <div class="input-group input-group-merge input-group-custom">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input type="search" name="searchValue" class="form-control"
                                                placeholder="{{translate('Search_by_Name_or_Email_or_Phone')}}"
                                                value="{{ request('searchValue') }}" required>
                                        <button type="submit" class="btn btn--primary">{{translate('Search')}}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="">
                                <form action="{{ url()->current() }}" method="GET">
                                    <div class="d-flex gap-2 align-items-center text-start">
                                        <div class="">
                                            <select class="form-control text-ellipsis min-w-200" name="role_id">
                                                <option value="all" {{ request('role_id', 'all') == 'all' ? 'selected' : '' }}>{{translate('All')}}</option>
                                                @foreach($employee_roles as $employee_role)
                                                    <option value="{{ $employee_role['id'] }}" {{ request('role_id') == $employee_role['id'] ? 'selected' : '' }}>
                                                            {{ ucfirst($employee_role['name']) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="">
                                            <button type="submit" class="btn btn--primary px-4 w-100 text-nowrap">
                                                {{ translate('Filter')}}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="dropdown">
                                <a type="button" class="btn btn-outline--primary text-nowrap btn-block" href="{{route('admin.employee.export',['role'=>request('role_id'),'searchValue'=>request('searchValue')])}}">
                                    <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                    <span class="ps-2">{{ translate('export') }}</span>
                                </a>
                            </div>
                            @can('employee_management.create')
                                <div class="">
                                    <a href="{{route('admin.employee.add-new')}}" class="btn btn--primary text-nowrap">
                                        <i class="tio-add"></i>
                                        <span class="text ">{{translate('add_New')}}</span>
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="datatable"

                            class="table table-hover table-borderless table-thead-bordered table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize table-nowrap">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('Name')}}</th>
                            <th>{{translate('Email')}}</th>
                            <th>{{translate('Phone')}}</th>
                            <th>{{translate('role')}}</th>
                            <th>{{translate('Status')}}</th>
                            <th class="text-center">{{translate('Action')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($employees as $key => $employee)
                            @php($isSuperAdminEmployee = $employee->roles->contains('name', $superAdminRole))
                            <tr>
                                <td>{{$key+1}}</td>
                                <td class="text-capitalize">
                                    <div class="media align-items-center gap-10">
                                        <img class="rounded-circle avatar avatar-lg" alt=""
                                             src="{{getStorageImages(path: $employee->image_full_url,type:'backend-profile')}}">
                                        <div class="media-body">
                                            {{$employee['name']}}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{$employee['email']}}
                                </td>
                                <td>{{$employee['phone']}}</td>
                                <td>{{ $employee->roles->first()?->name ?? translate('role_not_found') }}</td>
                              
                                <td>
                                    @if($isSuperAdminEmployee)
                                        <label class="badge badge-primary-light">{{ translate('super_admin') }}</label>
                                    @else
                                        @can('employee_management.update')
                                            <form action="{{route('admin.employee.status')}}" method="post" id="employee-id-{{$employee['id']}}-form" class="employee_id_form">
                                                @csrf
                                                <input type="hidden" name="id" value="{{$employee['id']}}">
                                                <label class="switcher">
                                                    <input type="checkbox" class="switcher_input toggle-switch-message" value="1" id="employee-id-{{$employee['id']}}" name="status"
                                                           {{$employee->status?'checked':''}}
                                                           data-modal-id = "toggle-status-modal"
                                                           data-toggle-id = "employee-id-{{$employee['id']}}"
                                                           data-on-image = "employee-on.png"
                                                           data-off-image = "employee-off.png"
                                                           data-on-title = "{{translate('want_to_Turn_ON_Employee_Status').'?'}}"
                                                           data-off-title = "{{translate('want_to_Turn_OFF_Employee_Status').'?'}}"
                                                           data-on-message = "<p>{{translate('if_enabled_this_employee_can_log_in_to_the_system_and_perform_his_role')}}</p>"
                                                           data-off-message = "<p>{{translate('if_disabled_this_employee_can_not_log_in_to_the_system_and_perform_his_role')}}</p>">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </form>
                                        @else
                                            <label class="badge {{ $employee->status ? 'badge-soft-success' : 'badge-soft-danger' }}">
                                                {{ $employee->status ? translate('Active') : translate('Inactive') }}
                                            </label>
                                        @endcan
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-10 justify-content-center">
                                        @can('employee_management.update')
                                            @if(!$isSuperAdminEmployee || auth('admin')->user()?->isSuperAdmin())
                                            <a href="{{route('admin.employee.update',[$employee['id']])}}"
                                               class="btn btn-outline--primary btn-sm square-btn"
                                               title="{{translate('Edit')}}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            @endif
                                        @endcan
                                        @can('employee_management.read')
                                            <a class="btn btn-outline-info btn-sm square-btn" title="{{ translate('View') }}" href="{{route('admin.employee.view',['id'=>$employee['id']])}}">
                                                <i class="tio-invisible"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-lg-end">
                        {{$employees->links()}}
                    </div>
                </div>
                @if(count($employees)==0)
                    <div class="w-100">
                        @include('layouts.back-end._empty-state',['text'=>'no_employee_found'],['image'=>'default'])
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('admin-views.employee.partials.department')
    @include('admin-views.employee.partials.branch')
@endsection

@push('script')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/employee.js')}}"></script>
@endpush

