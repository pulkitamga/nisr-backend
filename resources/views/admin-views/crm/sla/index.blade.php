@extends('layouts.back-end.app')

@section('title', translate('Inbox SLA Policies'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 d-flex align-items-center gap-2">
            <i class="tio-filter-list"></i>
            {{ translate('SLA Policies') }}
        </h2>
    </div>

    {{-- 🔍 Filters + Search --}}
    <div class="card mb-3">
        <div class="px-3 py-4">
            <form action="{{ url()->current() }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-3">
                    <select name="entity_type" class="form-control select2">
                        <option value="all">{{ translate('All Entity Types') }}</option>
                        @foreach($entityTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('entity_type') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="priority" class="form-control select2">
                        <option value="all">{{ translate('All Priorities') }}</option>
                        @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" {{ request('priority') == $p ? 'selected' : '' }}>
                            {{ ucfirst($p) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="active_status" class="form-control select2">
                        <option value="all">{{ translate('all_Status') }}</option>
                        <option value="active" {{ request('active_status') == 'active' ? 'selected' : '' }}>
                            {{ translate('Active') }}
                        </option>
                        <option value="inactive" {{ request('active_status') == 'inactive' ? 'selected' : '' }}>
                            {{ translate('Inactive') }}
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-merge input-group-custom">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="tio-search"></i>
                            </div>
                        </div>
                        <input type="text" class="form-control" name="searchValue"
                            placeholder="{{ translate('Search by Entity Type...') }}"
                            value="{{ request('searchValue') }}">
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="btn--container justify-content-end">
                        <a href="{{ route('admin.sla.index') }}" class="btn btn-secondary px-5">
                            {{ translate('Reset') }}
                        </a>
                        <button type="submit" class="btn btn--primary">{{ translate('Filter') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 📋 Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ translate('SLA Policy List') }}
                <span class="badge badge-soft-dark ms-1">{{ $policies->total() }}</span>
            </h5>
            <a href="{{ route('admin.sla.create') }}" class="btn btn-outline--primary text-nowrap">
                <i class="tio-add"></i> {{ translate('Create New Policy') }}
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="thead-light text-capitalize">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('Entity Type') }}</th>
                        <th>{{ translate('Priority') }}</th>
                        <th>{{ translate('Response Time') }}</th>
                        <th>{{ translate('Resolution Time') }}</th>
                        <th class="text-center">{{ translate('Active') }}</th>
                        <th>{{ translate('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($policies as $policy)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $policy->entity_type)) }}</td>
                        <td>{{ ucfirst($policy->priority)}}</td>
                        <td>{{ $policy->response_time_minutes }} {{ translate('min') }}</td>
                        <td>{{ $policy->resolution_time_minutes }} {{ translate('min') }}</td>
                        <td>
                            <label class="switcher mx-auto">
                                <input type="checkbox" class="switcher_input status-toggle"
                                    data-id="{{ $policy->id }}" {{ $policy->is_active ? 'checked' : '' }}>
                                <span class="switcher_control"></span>
                            </label>
                        </td>
                        <td>
                            <div class="row gap-2">
                                <a href="{{ route('admin.sla.edit', $policy->id) }}"
                                    class="btn btn-outline-success btn-sm">
                                    <i class="tio-edit"></i>
                                </a>
                                <form action="{{ route('admin.sla.destroy', $policy->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="tio-delete"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $policies->links() !!}
            </div>
        </div>

        @if(count($policies)==0)
        @include('layouts.back-end._empty-state',['text'=>translate('no_record_found')],['image'=>'default'])
        @endif
    </div>
</div>
<span id="routeSlaUpdate" data-route="{{ route('admin.sla.status') }}"></span>
@endsection

@push('script')
<script type="text/javascript">
    changeInputTypeForDateRangePicker($('input[name="order_date"]'));
    changeInputTypeForDateRangePicker($('input[name="customer_joining_date"]'));
</script>

@include('admin-views.crm.partials._crm-js-text')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/crm.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.status-toggle').on('change', function(e) {
            e.preventDefault();
            var checkbox = $(this);
            var policyId = checkbox.data('id');
            var routeUrl = document.getElementById('routeSlaUpdate')
            var isChecked = checkbox.is(':checked') ? 1 : 0;

            Swal.fire({
                title: '{{ translate("Are you sure?") }}',
                text: '{{ translate("You are about to change the status of this policy.") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ translate("Yes, change it!") }}',
                cancelButtonText: '{{ translate("Cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: routeUrl,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: policyId,
                            status: isChecked
                        },
                        success: function(response) {
                            toastr.success(response.message || '{{ translate("Status_updated_successfully") }}');
                        },
                        error: function() {
                            toastr.error('{{ translate("Something_went_wrong") }}');
                            checkbox.prop('checked', !isChecked); // revert
                        }
                    });
                } else {
                    checkbox.prop('checked', !isChecked); // revert if cancelled
                }
            });
        });
    });
</script>
@endpush
