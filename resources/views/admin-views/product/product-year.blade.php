@extends('layouts.back-end.app')
@section('title', translate('product_year'))

@section('content')
<div class="content container-fluid">
    <div>
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/all-orders.png')}}" class="mb-1 me-1" alt="">
                {{translate('product_makes')}}
            </h2>
        </div>

        <div class="card shadow rounded">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Add Years') }}</h5>
            </div>
            <div class="card-body">
                <form action="#" method="POST">
                    <div class="row">
                        <div class="mb-3 col-lg-6 col-md-12 col-sm-12">
                            <label for="make" class="form-label">{{ __('Make') }}</label>
                            <input type="text" class="form-control" id="make" name="make" placeholder="e.g. Toyota" required>
                        </div>
                        <div class="mb-3 col-lg-6 col-md-12 col-sm-12">
                            <label for="model" class="form-label">{{ __('Model') }}</label>
                            <input type="text" class="form-control" id="model" name="model" placeholder="e.g. x50 x70 x90" required>
                            <small class="text-muted">{{ __('You can enter multiple models separated by space.') }}</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="row mt-20">
        <div class="col-md-12">
            <div class="card">
                <div class="px-3 py-4">
                    <div class="row align-items-center">
                        <div class="col-lg-4">

                            <form action="{{ url()->current() }}" method="GET">
                                <div class="input-group input-group-custom input-group-merge">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="tio-search"></i>
                                        </div>
                                    </div>
                                    <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                        placeholder="{{ translate('search_by_Product_Name') }}"
                                        aria-label="{{ translate('Search orders') }}" value="{{ request('searchValue') }}">
                                    <input type="hidden" value="{{ request('status') }}" name="status">
                                    <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable"
                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('Make Name') }}</th>
                                <th class="text-center">{{ translate('Make models') }}</th>
                                <th class="text-center">{{ translate('status') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

