@php
use Illuminate\Support\Facades\Session;
@endphp
@extends('layouts.back-end.app')
@section('title', translate('create_Role'))
@section('content')
<div class="content container-fluid">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    .nav-link.active {
      background: var(--c1) !important;
    }
  </style>
  <div class="mb-3">
    <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2 text-capitalize">
      <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
      {{translate('employee_role_setup')}}
    </h2>
  </div>
  @php
  $modules = [
  ['key' => 'dashboard', 'title_key' => 'dashboard', 'icon' => 'bi-speedometer2'],
  ['key' => 'pos_management', 'title_key' => 'pos_management', 'icon' => 'bi-printer'],
  ['key' => 'order_management', 'title_key' => 'order_management', 'icon' => 'bi-bag'],
  ['key' => 'product_management', 'title_key' => 'product_management', 'icon' => 'bi-box'],
  ['key' => 'promotion_management', 'title_key' => 'promotion_management', 'icon' => 'bi-megaphone'],
  ['key' => 'support_section', 'title_key' => 'support_section', 'icon' => 'bi-headset'],
  ['key' => 'report', 'title_key' => 'report', 'icon' => 'bi-bar-chart'],
  ['key' => 'user_section', 'title_key' => 'user_section', 'icon' => 'bi-person'],
  ['key' => 'crm_section', 'title_key' => 'crm_section', 'icon' => 'bi-people'],
  ['key' => 'wholesaler_section', 'title_key' => 'wholesaler_section', 'icon' => 'bi-shop'],
  ['key' => 'system_settings', 'title_key' => 'system_settings', 'icon' => 'bi-gear'],
  ['key' => 'branch_management', 'title_key' => 'branch_management', 'icon' => 'bi-diagram-3'],
  ['key' => 'cms_management', 'title_key' => 'cms_management', 'icon' => 'bi-file-earmark-text'],
  ['key' => 'warranty_section', 'title_key' => 'warranty_section', 'icon' => 'bi-file-earmark-text'],
  ];

  $permissions = config('role_permissions');
  @endphp
  <div class="card">
    <div class="card-body">

      <form id="submit-create-role" method="post" action="{{route('admin.custom-role.store')}}" class="text-start">
        @csrf

        <input type="hidden" name="modules[]" id="selected-modules">
        <input type="hidden" name="module_permissions_json" id="module-permissions-json">


        <div class="row mt-3">
          <div class="col-lg-12">
            <div class="form-group mb-4">
              <label for="name" class="title-color">{{translate('role_name')}}</label>
              <input type="text" name="name" class="form-control" id="name"
                aria-describedby="emailHelp"
                placeholder="{{translate('ex').':'.translate('store')}}" required>
            </div>
          </div>
          <!-- LEFT SIDE MODULE LIST -->
          <div class="col-lg-3 mb-3">
            <div class="card border card-shadow">
              <h3 class="card-header card-header-tab shadow-none border-bottom">{{translate('Role_Category')}}</h3>
              <div class="card-body px-3 py-2">
                <div class="nav flex-column nav-pills" id="role-tabs" role="tablist">
                  @foreach($modules as $index => $module)
                  <a class="nav-link mb-2 border-0 align-items-center d-flex gap-2 justify-content-between left-tab {{ $index === 0 ? 'active' : '' }}"
                    data-toggle="pill"
                    href="#{{ $module['key'] }}">
                    <div class="align-items-center d-flex gap-2 role-left-side">
                      <i class="bi {{ $module['icon'] }} font18"></i> {{ translate($module['title_key']) }}
                    </div>
                    <i class="bi bi-arrow-right font18"></i>
                  </a>
                  @endforeach
                </div>
              </div>
            </div>
          </div>

          <!-- RIGHT SIDE PERMISSIONS TAB -->
          <div class="col-lg-9">
            <div class="tab-content">
              @foreach($modules as $index => $module)
              <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $module['key'] }}">
                <div class="card border card-shadow">
                  <div class="card-header card-header-tab shadow-none border-bottom">
                    <div class="d-flex justify-content-between align-items-center gap-4">
                      <h3 class="mb-0">{{ translate($module['title_key']) }}</h3>
                      <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input module-master-checkbox"
                          id="master_{{ $module['key'] }}" data-module="{{ $module['key'] }}">
                        <label class="custom-control-label" for="master_{{ $module['key'] }}"></label>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      @if(isset($permissions[$module['key']]))
                      @foreach($permissions[$module['key']] as $permission)
                      <div class="col-md-6 col-lg-4 mb-3">
                        <div class="switch-card">
                          <div class="d-flex justify-content-between switch-flex align-items-center">
                            <label class="mb-0 font18">
                              @php
                              $label = str_replace($module['key'] . '.', '', $permission);
                              $label = str_replace('_', ' ', $label);
                              $label = ucwords($label);
                              @endphp
                              {{ translate($label) }}
                            </label>
                            <div class="custom-control custom-switch">
                              <input type="checkbox"
                                class="custom-control-input permission-checkbox"
                                name="permissions[]"
                                value="{{ $permission }}"
                                data-module="{{ $module['key'] }}"
                                id="{{ $permission }}">
                              <label class="custom-control-label" for="{{ $permission }}"></label>
                            </div>
                          </div>
                        </div>
                      </div>
                      @endforeach
                      @else
                      <div class="col-12">
                        <p class="text-muted"> {{translate('no_role_permissions_define_for_this_module')}}</p>
                      </div>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>

            <div class="d-flex justify-content-end mt-3">
              <button class="btn btn-primary">{{translate('Add_Role')}}</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

</div>



@endsection

@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/custom-role.js')}}"></script>
<script>
  $(document).ready(function() {
    // When the master switch for a module is toggled
    $('.module-master-checkbox').on('change', function() {
      var moduleKey = $(this).data('module');
      var isChecked = $(this).is(':checked');

      // Find all permission checkboxes for this module
      $('.permission-checkbox[data-module="' + moduleKey + '"]').prop('checked', isChecked);
    });

    // Optional: Auto-check master switch if all individual toggles are checked manually
    $('.permission-checkbox').on('change', function() {
      var moduleKey = $(this).data('module');
      var allModuleCheckboxes = $('.permission-checkbox[data-module="' + moduleKey + '"]');
      var masterCheckbox = $('#master_' + moduleKey);

      if (allModuleCheckboxes.length === allModuleCheckboxes.filter(':checked').length) {
        masterCheckbox.prop('checked', true);
      } else {
        masterCheckbox.prop('checked', false);
      }
    });
  });
</script>
@endpush