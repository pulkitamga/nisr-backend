@extends('layouts.back-end.app')

@section('title', translate('clear_cache'))

@section('content')



<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" alt="">
            {{translate('Clear_cache')}}
        </h2>
    </div>
    @include('admin-views.business-settings.system-settings-inline-menu')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 text-capitalize d-flex gap-2 text-capitalize">
                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/product_setup.png')}}" alt="">
                {{translate('Clear_cache')}}
            </h5>
        </div>
        <div class="card-body">
            <form id="clear-cache-form" action="{{ route('admin.business-settings.web-config.cache-clear') }}" method="POST">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center gap-10 form-control p-4">
                                <span class="title-color">
                                    {{ translate('Clear_cache') }}
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                        data-placement="right"
                                        title="{{ translate('if_you_click_clear_button_all_cache_was_clear') }}">
                                        <img width="16" src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                                    </span>
                                </span>

                                <!-- ✅ Clear Button -->
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmClearCache()">
                                    {{ translate('Clear') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('script_2')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
    <script>
        Swal.fire({
            title: 'Success!',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
@endif
<script>
    function confirmClearCache() {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to clear the entire website cache!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, clear it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('clear-cache-form').submit();
            }
        });
    }
</script>

@endpush