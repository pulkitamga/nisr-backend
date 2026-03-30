@php
use Illuminate\Support\Facades\Session;
@endphp
@extends('layouts.back-end.app')
@section('title', translate('create_Role'))
@section('content')
<div class="content container-fluid">
    @php
        $adminUser = auth('admin')->user();
        $canManageRoles = $adminUser && ($adminUser->isSuperAdmin() || $adminUser->can('rbac.roles.manage'));
    @endphp

    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
            {{ translate('employee_role') }}
        </h2>
    </div>
    <div class="card mt-3">
        <div class="px-3 py-4">
            <div class="row justify-content-between align-items-center flex-grow-1">
                <div class="col-md-4 col-lg-6 mb-2 mb-sm-0">
                    <h5 class="d-flex align-items-center gap-2">
                        {{translate('employee_Roles')}}
                        <span class="badge badge-soft-dark radius-50 fz-12 ms-1">{{ count($roles) }}</span>
                    </h5>
                </div>
                <div class="col-md-8 col-lg-6 d-flex flex-wrap flex-sm-nowrap justify-content-sm-end gap-3">
                    <form action="{{url()->current()}}?search={{ request('searchValue') }}" method="GET">
                        <div class="input-group input-group-merge input-group-custom">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="tio-search"></i>
                                </div>
                            </div>
                            <input id="datatableSearch_" type="search" name="searchValue" class="form-control" placeholder="{{translate('search_role')}}"
                                value="{{ request('searchValue') }}">
                            <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                        </div>
                    </form>
                    <div class="dropdown">
                        <a type="button" class="btn btn-outline--primary text-nowrap btn-block" href="{{route('admin.custom-role.export',['searchValue'=>request('searchValue')])}}">
                            <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                            <span class="ps-2">{{ translate('export') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="pb-3">
            <div class="table-responsive">
                <table class="table table-hover table-borderless table-thead-bordered table-align-middle card-table text-start">
                    <thead class="thead-light thead-50 text-capitalize table-nowrap">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('role_name')}}</th>
                            <th>{{translate('modules')}}</th>
                            <th>{{translate('permissions')}}</th>
                            <th>{{translate('created_at')}}</th>
                            <th>{{translate('status')}}</th>
                            <th class="text-center">{{translate('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $key => $role)
                        @php
                            $isProtectedRole = strtolower((string)$role->name) === strtolower((string)config('permissions_admin.super_admin_role', 'Super Admin'));
                        @endphp
                        <tr>
                            <td>{{$key+1}}</td>
                            <td>{{$role['name']}}</td>
                            <td class="text-capitalize">
                                @php
                                    $rolePermissionGroups = $role->permissions
                                        ->pluck('name')
                                        ->filter(fn($permission) => str_contains((string)$permission, '.'))
                                        ->mapToGroups(function ($permission) {
                                            [$module, $action] = explode('.', $permission, 2);
                                            return [$module => $action];
                                        });
                                @endphp
                                @forelse(($rolePermissionGroups ?? collect()) as $moduleName => $actions)
                                    {{ \App\Support\AdminPermissionRegistry::moduleDisplayName((string)$moduleName) }}<br>
                                @empty
                                    -
                                @endforelse
                            </td>
                            <td class="text-capitalize">
                                @forelse(($rolePermissionGroups ?? collect()) as $moduleName => $actions)
                                    @php
                                        $permissionLabels = collect($actions)
                                            ->unique()
                                            ->values()
                                            ->map(fn($action) => \App\Support\AdminPermissionRegistry::permissionDisplayName($moduleName . '.' . $action))
                                            ->implode(', ');
                                    @endphp
                                    <small class="text-muted">{{ $permissionLabels }}</small><br>
                                @empty
                                    -
                                @endforelse
                            </td>

                            <td>{{date('d-M-y',strtotime($role['created_at']))}}</td>
                            <td>
                                <form action="{{route('admin.custom-role.employee-role-status')}}" method="post" id="employee-role-status{{$role['id']}}-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$role['id']}}">
                                    <label class="switcher" for="employee-role-status{{$role['id']}}">
                                        <input type="checkbox" class="switcher_input toggle-switch-message" id="employee-role-status{{$role['id']}}" name="status" value="1" {{ (isset($role['status']) ? (int)$role['status'] : 1) === 1 ? 'checked' : '' }} {{ ($isProtectedRole ?? false) ? 'disabled' : '' }}
                                            data-modal-id="toggle-status-modal"
                                            data-toggle-id="employee-role-status{{$role['id']}}"
                                            data-on-image="employee-on.png"
                                            data-off-image="employee-off.png"
                                            data-on-title="{{translate('want_to_Turn_ON_Employee_Status').'?'}}"
                                            data-off-title="{{translate('want_to_Turn_OFF_Employee_Status').'?'}}"
                                            data-on-message="<p>{{translate('when_the_status_is_enabled_employees_can_access_the_system_to_perform_their_responsibilities')}}</p>"
                                            data-off-message="<p>{{translate('when_the_status_is_disabled_employees_cannot_access_the_system_to_perform_their_responsibilities')}}</p>">
                                        <span class="switcher_control"></span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    @if($canManageRoles)
                                        <a href="{{route('admin.custom-role.update',[$role['id']])}}"
                                           class="btn btn-outline--primary btn-sm square-btn"
                                           title="{{translate('edit') }}">
                                            <i class="tio-edit"></i>
                                        </a>
                                        @unless($isProtectedRole ?? false)
                                            <a href="javascript:"
                                               class="btn btn-outline-danger btn-sm delete-data-without-form"
                                               data-action="{{route('admin.custom-role.delete')}}"
                                               title="{{translate('delete') }}" data-id="{{$role['id']}}">
                                                <i class="tio-delete"></i>
                                            </a>
                                        @endunless
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(count($roles)==0)
        @include('layouts.back-end._empty-state',['text'=>'no_data_found'],['image'=>'default'])
        @endif
    </div>
</div>



@endsection

@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/custom-role.js')}}"></script>
@endpush
