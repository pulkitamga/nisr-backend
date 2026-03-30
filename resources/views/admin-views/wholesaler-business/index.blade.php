@extends('layouts.back-end.app')

@section('title', translate('WholeSalers'))

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif
<style>
    .form-check-input.moq-toggle {
        width: 100%;
        height: auto;
        transform: scale(1.5);
        cursor: pointer;
    }
</style>

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
            {{translate('WholeSalers')}}
        </h2>
    </div>
    <div class="card mt-3">
        <div class="card-body">
            <div class="px-3 py-4 light-bg">
                <div class="row g-2 align-items-center flex-grow-1">
                    <div class="col-md-7 col-lg-8">
                        <h5 class="text-capitalize d-flex gap-1">
                            {{translate('WholeSalers')}}
                            <span class="badge badge-soft-dark radius-50 fz-12">{{$wholesaler_business->total()}}</span>
                        </h5>
                    </div>
                    <div class="col-md-5 col-lg-4 d-flex gap-3 flex-sm-nowrap justify-content-end">
                        <div class="input-group input-group-custom input-group-merge">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="tio-search"></i>
                                </div>
                            </div>
                            <input id="datatableSearch_" type="search" class="form-control"
                                placeholder="{{ translate('Search...') }}" aria-label="{{ translate('Search') }}">
                        </div>
                        <div class="dropdown">
                            <a type="button" class="align-items-center btn btn-block btn-outline--primary d-flex pe-4"
                                href="{{route('admin.wholesale.business.wholesaler.export')}}">
                                <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}"
                                    class="excel" alt="">
                                <span class="ps-2">{{ translate('export') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left' }};"
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('wholesaler')}}</th>
                            <th>{{translate('phone')}}</th>
                            <th>{{translate('tier')}}</th>
                            <th class="text-center">{{translate('Status')}}</th>
                            <th class="text-center">{{ translate('MOQ Override') }}</th>

                            <th class="text-center">{{translate('action')}}</th>
                        </tr>
                    </thead>
                   <tbody>
    @forelse($wholesaler_business as $key=>$business)
        @if($business->wholesaler && $business->wholesaler->wholesaler_status != 0)
            <tr>
                <td>{{ $wholesaler_business->firstItem() + $key }}</td>
                <td>
                    <div class="media align-items-center gap-10">
                        <img class="rounded-circle avatar avatar-lg" alt=""
                             src="{{ getStorageImages(path: $business->wholesaler->image_full_url, type:'backend-profile') }}">
                        <div class="media-body">
                            {{ $business->company_name ?? __('N/A') }}
                        </div>
                    </div>
                </td>
                <td>{{ $business->wholesaler->phone ?? __('N/A') }}</td>
                <td>{{ $business->wholesaler->tier }}</td>

                <td>
                    <form action="{{ route('admin.customer.status-update') }}" method="POST"
                          id="customer-status{{$business->wholesaler['id']}}-form"
                          class="customer-status-form">
                        @csrf
                        <input type="hidden" name="id" value="{{ $business->wholesaler['id'] }}">
                        <label class="switcher mx-auto">
                            <input type="checkbox" class="switcher_input auto-submit-toggle"
                                   id="customer-status{{$business->wholesaler['id']}}" name="is_active"
                                   value="1" {{ $business->wholesaler['is_active'] == 1 ? 'checked' : '' }}>
                            <span class="switcher_control"></span>
                        </label>
                    </form>
                </td>

                <td>
                    <form id="moq-status-{{ $business->wholesaler->id }}-form">
                        @csrf
                        <input type="hidden" name="id" value="{{ $business->wholesaler->id }}">
                        <label class="switcher mx-auto">
                            <input type="checkbox"
                                   class="switcher_input moq-toggle"
                                   data-id="{{ $business->wholesaler->id }}"
                                   {{ $business->wholesaler->moq_override_enabled ? 'checked' : '' }}>
                            <span class="switcher_control"></span>
                        </label>
                    </form>
                </td>

                <td>
                    <div class="d-flex justify-content-center gap-2">
                        <a title="{{translate('view')}}" class="btn btn-outline-info btn-sm square-btn"
                           href="{{ route('admin.wholesale.business.wholesaler.profile',$business->id) }}">
                            <i class="tio-invisible"></i>
                        </a>
                        <a title="{{translate('edit')}}" class="btn btn-outline-info btn-sm square-btn"
                           href="{{ route('admin.wholesale.business.wholesaler.profile.edit',$business->id) }}">
                            <i class="tio-edit"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @endif
    @empty
        <tr>
            <td colspan="7" class="text-center text-muted">
                {{ translate('No Wholesaler Available') }}
            </td>
        </tr>
    @endforelse
</tbody>

                </table>
            </div>
            <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-center justify-content-md-end">

                </div>
            </div>
        </div>
    </div>
</div>
</div>

@push('script')
<script>
    $(document).on('change', '.moq-toggle', function() {
        let wholesalerId = $(this).data('id');
        let status = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: "{{ route('admin.wholesale.business.toggle.moq') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: wholesalerId,
                status: status
            },
            success: function(res) {
                toastr.success(res.message);
            },
            error: function() {
                toastr.error(@json(__('Something went wrong!')));
            }
        });
    });




    $(document).on('change', '.auto-submit-toggle', function() {
        var form = $(this).closest('form');
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                toastr.success(response.message); 
            },
            error: function() {
                toastr.error(@json(__('Something went wrong!')));
            }
        });
    });


    document.getElementById('datatableSearch_').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');

        rows.forEach(row => {
            // Convert all text inside the row to lowercase
            const rowText = row.textContent.toLowerCase();
            if (rowText.indexOf(query) > -1) {
                row.style.display = ''; // Show row
            } else {
                row.style.display = 'none'; // Hide row
            }
        });
    });
</script>
@endpush


@endsection

