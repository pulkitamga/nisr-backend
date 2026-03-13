@extends('layouts.back-end.app')

@section('title', translate('State & City Management'))

@push('css_or_js')
    <link href="{{ dynamicAsset('public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset('public/assets/back-end/img/location.png')}}" alt="">
            {{translate('State & City')}}
        </h2>
    </div>
        @include('admin-views.business-settings.business-setup-inline-menu')

    <div class="row gy-3">
        <!-- STATE CARD -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 text-capitalize d-flex gap-2 align-items-center">
                        <img width="18" src="{{dynamicAsset('public/assets/back-end/img/state.png')}}" alt="">
                        {{translate('Add State')}}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.business-settings.state-city.state.store') }}" method="post">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-6">
                                <label class="title-color">{{translate('Country')}}</label>
                                <select name="country" class="form-control" required>
                                    <option value="" disabled selected>{{translate('--Select--')}}</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country['code'] }}">{{ $country['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="title-color">{{translate('State Name')}}</label>
                                <input type="text" name="name" class="form-control" placeholder="{{translate('Enter state')}}" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn--primary px-4">{{translate('Add')}}</button>
                        </div>
                    </form>

                    <!-- State List -->
                    <div class="mt-4">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Country')}}</th>
                                        <th>{{translate('State')}}</th>
                                        <th class="text-center">{{translate('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($states as $key => $state)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ Str::limit($state->country, 20) }}</td>
                                            <td>{{ $state->name }}</td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <form action="{{ route('admin.business-settings.state-city.state.delete', $state->id) }}"
                                                          method="post" id="state-delete-{{ $state->id }}">
                                                        @csrf @method('delete')
                                                        <button type="button"
                                                                class="btn btn-outline-danger btn-sm square-btn delete-btn"
                                                                data-id="state-delete-{{ $state->id }}"
                                                                title="{{translate('Delete')}}">
                                                            <i class="tio-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">{{translate('No states')}}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CITY CARD -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 text-capitalize d-flex gap-2 align-items-center">
                        <img width="18" src="{{dynamicAsset('public/assets/back-end/img/city.png')}}" alt="">
                        {{translate('Add City')}}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.business-settings.state-city.city.store') }}" method="post">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-6">
                                <label class="title-color">{{translate('State')}}</label>
                                <select name="state_id" class="form-control js-select2" required>
                                    <option value="" disabled selected>{{translate('--Select State--')}}</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="title-color">{{translate('City Name')}}</label>
                                <input type="text" name="name" class="form-control" placeholder="{{translate('Enter city')}}" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn--primary px-4">{{translate('Add')}}</button>
                        </div>
                    </form>

                    <!-- City List -->
                    <div class="mt-4">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('State')}}</th>
                                        <th>{{translate('City')}}</th>
                                        <th class="text-center">{{translate('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cities as $key => $city)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $city->state->name }}</td>
                                            <td>{{ $city->name }}</td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <form action="{{ route('admin.business-settings.state-city.city.delete', $city->id) }}"
                                                          method="post" id="city-delete-{{ $city->id }}">
                                                        @csrf @method('delete')
                                                        <button type="button"
                                                                class="btn btn-outline-danger btn-sm square-btn delete-btn"
                                                                data-id="city-delete-{{ $city->id }}"
                                                                title="{{translate('Delete')}}">
                                                            <i class="tio-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">{{translate('No cities')}}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0 text-capitalize d-flex gap-2 align-items-center">
                        <img width="18" src="{{dynamicAsset('public/assets/back-end/img/city.png')}}" alt="">
                        {{translate('Add Area')}}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.business-settings.state-city.area.store') }}" method="post">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-6">
                                <label class="title-color">{{translate('City')}}</label>
                                <select name="city_id" class="form-control js-select2" required>
                                    <option value="" disabled selected>{{translate('--Select City--')}}</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="title-color">{{translate('Area Name')}}</label>
                                <input type="text" name="name" class="form-control" placeholder="{{translate('Enter Area')}}" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn--primary px-4">{{translate('Add')}}</button>
                        </div>
                    </form>

                    <!-- City List -->
                    <div class="mt-4">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('City')}}</th>
                                        <th>{{translate('Area')}}</th>
                                        <th class="text-center">{{translate('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($areas as $key => $area)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $area->city->name ?? '-' }}</td>
                                            <td>{{ $area->name }}</td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <form action="{{ route('admin.business-settings.state-city.area.delete', $area->id) }}"
                                                          method="post" id="area-delete-{{ $area->id }}">
                                                        @csrf @method('delete')
                                                        <button type="button"
                                                                class="btn btn-outline-danger btn-sm square-btn delete-btn"
                                                                data-id="area-delete-{{ $area->id }}"
                                                                title="{{translate('Delete')}}">
                                                            <i class="tio-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">{{translate('No areas')}}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script src="{{ dynamicAsset('public/assets/select2/js/select2.min.js') }}"></script>
<script>
    $(document).on('ready', function () {
        $('.js-select2').select2();

        $('.delete-btn').click(function () {
            const formId = $(this).data('id');
            Swal.fire({
                title: @json(__('Sure?')),
                text: @json(__('Can\'t revert!')),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: @json(__('Yes, delete!'))
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`#${formId}`).submit();
                }
            });
        });
    });
</script>
@endpush
