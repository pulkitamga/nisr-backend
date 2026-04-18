@php
use Illuminate\Support\Facades\Session;

$permissionGroups = $permissionGroups ?? \App\Support\AdminPermissionRegistry::groupedPermissionsBySection();
$grantedPermissions = $role->permissions->pluck('name')->toArray();
$isRtl = Session::get('direction') === 'rtl';
$moduleIcons = [
    'dashboard' => 'bi-speedometer2',
    'pos_management' => 'bi-printer',
    'order_management' => 'bi-bag',
    'product_management' => 'bi-box',
    'promotion_management' => 'bi-megaphone',
    'report' => 'bi-bar-chart',
    'user_section' => 'bi-person',
    'employee_management' => 'bi-people',
    'crm_section' => 'bi-people',
    'wholesaler_section' => 'bi-shop',
    'system_settings' => 'bi-gear',
    'branch_management' => 'bi-diagram-3',
    'cms_section' => 'bi-file-earmark-text',
    'task_section' => 'bi-list-task',
    'warranty_section' => 'bi-shield-check',
    'rbac' => 'bi-shield-lock',
];
@endphp

@extends('layouts.back-end.app')
@section('title', translate('edit_Role'))
@section('content')
<div class="content container-fluid">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .nav-link.active {
            background: var(--c1) !important;
        }

        .permission-label-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .permission-tip {
            color: #6c757d;
            font-size: 14px;
            line-height: 1;
            cursor: pointer;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .permission-tip:hover {
            color: #1d4ed8;
        }

        .permission-tip i {
            font-size: 14px;
        }

        .tooltip.permission-tip-tooltip .tooltip-inner {
            max-width: 320px;
            text-align: left;
        }

        html[dir="rtl"] .tooltip.permission-tip-tooltip .tooltip-inner {
            direction: rtl;
            text-align: right;
        }
    </style>

    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
            {{ translate('role_update') }}
        </h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="submit-create-role" method="post" action="{{ route('admin.custom-role.save-update', $role->id) }}" class="text-start">
                @csrf

                <div class="row mt-3">
                    <div class="col-lg-12">
                        <div class="form-group mb-4">
                            <label for="name" class="title-color">
                                {{translate('role_Name')}}
                                <span class="input-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="top" title="{{ translate('role_name_english_only_hint') }}">
                                    <img src="{{ dynamicAsset('public/assets/back-end/img/info-circle.svg') }}" alt="" width="14">
                                </span>
                            </label>
                            <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $role->name) }}" required
                                pattern="[a-zA-Z0-9_\- ]+"
                                oninput="this.value.match(/[^a-zA-Z0-9_\-\s]/) ? this.classList.add('is-invalid') : this.classList.remove('is-invalid')">
                            <small class="text-muted d-block mt-1">{{ translate('role_name_english_only_hint') }}</small>
                            @error('name')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-3 mb-3">
                        <div class="card border card-shadow">
                            <h3 class="card-header card-header-tab shadow-none border-bottom">{{ translate('Role_Category') }}</h3>
                            <div class="card-body px-3 py-2">
                                <div class="nav flex-column nav-pills" id="role-tabs" role="tablist">
                                    @foreach($permissionGroups as $module => $moduleSections)
                                        <a class="nav-link mb-2 border-0 align-items-center d-flex gap-2 justify-content-between left-tab {{ $loop->first ? 'active' : '' }}"
                                           data-toggle="pill"
                                           href="#{{ $module }}">
                                            <div class="align-items-center d-flex gap-2 role-left-side">
                                                <i class="bi {{ $moduleIcons[$module] ?? 'bi-shield-lock' }} font18"></i>
                                                {{ \App\Support\AdminPermissionRegistry::moduleDisplayName($module) }}
                                            </div>
                                            <i class="bi bi-arrow-right font18"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="tab-content">
                            @foreach($permissionGroups as $module => $moduleSections)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $module }}">
                                    <div class="card border card-shadow">
                                        <div class="card-header card-header-tab shadow-none border-bottom">
                                            <div class="d-flex justify-content-between align-items-center gap-4">
                                                <h3 class="mb-0 permission-label-wrap">
                                                    <span>{{ \App\Support\AdminPermissionRegistry::moduleDisplayName($module) }}</span>
                                                    <span class="input-label-secondary permission-tip"
                                                          tabindex="0"
                                                          role="button"
                                                          data-toggle="tooltip"
                                                          data-bs-toggle="tooltip"
                                                          data-placement="{{ $isRtl ? 'left' : 'right' }}"
                                                          data-tip="{{ \App\Support\AdminPermissionRegistry::moduleHint($module) }}"
                                                          aria-label="{{ \App\Support\AdminPermissionRegistry::moduleHint($module) }}"
                                                          title="{{ \App\Support\AdminPermissionRegistry::moduleHint($module) }}">
                                                        <i class="bi bi-info-circle"></i>
                                                    </span>
                                                </h3>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input module-master-checkbox"
                                                           id="master_{{ $module }}" data-module="{{ $module }}">
                                                    <label class="custom-control-label" for="master_{{ $module }}"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @foreach($moduleSections as $groupKey => $groupPermissions)
                                                @php
                                                    $groupId = str_replace(['.', ' '], '_', $module . '_' . $groupKey);
                                                @endphp
                                                <div class="card border mb-3">
                                                    <div class="card-header bg-transparent py-2">
                                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                                            <h5 class="mb-0 permission-label-wrap">
                                                                <span>{{ \App\Support\AdminPermissionRegistry::groupDisplayName($groupKey) }}</span>
                                                                <span class="input-label-secondary permission-tip"
                                                                      tabindex="0"
                                                                      role="button"
                                                                      data-toggle="tooltip"
                                                                      data-bs-toggle="tooltip"
                                                                      data-placement="{{ $isRtl ? 'left' : 'right' }}"
                                                                      data-tip="{{ \App\Support\AdminPermissionRegistry::groupHint($module, $groupKey) }}"
                                                                      aria-label="{{ \App\Support\AdminPermissionRegistry::groupHint($module, $groupKey) }}"
                                                                      title="{{ \App\Support\AdminPermissionRegistry::groupHint($module, $groupKey) }}">
                                                                    <i class="bi bi-info-circle"></i>
                                                                </span>
                                                            </h5>
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox"
                                                                       class="custom-control-input group-master-checkbox"
                                                                       id="group_master_{{ $groupId }}"
                                                                       data-module="{{ $module }}"
                                                                       data-group="{{ $groupKey }}">
                                                                <label class="custom-control-label" for="group_master_{{ $groupId }}"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body pt-3 pb-2">
                                                        <div class="row">
                                                            @foreach($groupPermissions as $permission)
                                                                @php
                                                                    $label = \App\Support\AdminPermissionRegistry::permissionDisplayName($permission);
                                                                    $hint = \App\Support\AdminPermissionRegistry::permissionHint($permission);
                                                                    $selected = in_array($permission, old('permissions', $grantedPermissions), true);
                                                                @endphp
                                                                <div class="col-md-6 col-lg-4 mb-3">
                                                                    <div class="switch-card">
                                                                        <div class="d-flex justify-content-between switch-flex align-items-center">
                                                                            <label class="mb-0 font18 permission-label-wrap">
                                                                                <span>{{ $label }}</span>
                                                                                <span class="input-label-secondary permission-tip"
                                                                                      tabindex="0"
                                                                                      role="button"
                                                                                      data-toggle="tooltip"
                                                                                      data-bs-toggle="tooltip"
                                                                                      data-placement="{{ $isRtl ? 'left' : 'right' }}"
                                                                                      data-tip="{{ $hint }}"
                                                                                      aria-label="{{ $hint }}"
                                                                                      title="{{ $hint }}">
                                                                                    <i class="bi bi-info-circle"></i>
                                                                                </span>
                                                                            </label>
                                                                            <div class="custom-control custom-switch">
                                                                                <input type="checkbox"
                                                                                       class="custom-control-input permission-checkbox"
                                                                                       name="permissions[]"
                                                                                       value="{{ $permission }}"
                                                                                       data-module="{{ $module }}"
                                                                                       data-group="{{ $groupKey }}"
                                                                                       id="{{ str_replace('.', '_', $permission) }}"
                                                                                       {{ $selected ? 'checked' : '' }}>
                                                                                <label class="custom-control-label" for="{{ str_replace('.', '_', $permission) }}"></label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('permissions')
                        <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror

                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-primary">{{ translate('Update') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<span id="select-minimum-one-box-message" data-warning="{{ translate('select_minimum_one_permission') }}"></span>
@endsection

@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/custom-role.js')}}?v={{ filemtime(base_path('public/assets/back-end/js/admin/custom-role.js')) }}"></script>
@endpush
