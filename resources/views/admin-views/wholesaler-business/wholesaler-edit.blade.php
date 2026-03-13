@extends('layouts.back-end.app')

@section('title', translate('wholesaler_edit'))
@push('css_or_js')
<link rel="stylesheet"
    href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/css/intlTelInput.css') }}">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/vendor-information.png')}}" alt="">
            {{ translate('wholesaler') . ' # ' . $wholesaler->id }}
        </h2>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('admin.wholesale.business.wholesaler.update', [$wholesaler->id]) }}" method="post"
                enctype="multipart/form-data" class="text-start">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <h5
                            class="mb-0 page-header-title d-flex text-capitalize align-items-center gap-2 border-bottom pb-3 mb-3">
                            <i class="tio-user"></i>
                            {{ translate('general_information') }}
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="hidden" name="id" value="{{ $wholesaler->id }}">
                                <div class="form-group">
                                    <label for="name" class="title-color">{{ translate('full_Name') }}</label>
                                    <input type="text" name="name" class="form-control" id="name"
                                        placeholder="{{ translate('ex') }} : John Doe"
                                        value="{{ $wholesaler->f_name . ' ' . $wholesaler->l_name }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="title-color">{{ translate('contact') }}</label>
                                    <input type="tel" name="phone" class="form-control" value="{{ $wholesaler->phone }}"
                                        readonly>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="title-color">{{ translate('email') }}</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ $wholesaler->email }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="joined_date" class="title-color">{{ translate('joined_date') }}</label>
                                    <input type="text" name="joined_date" class="form-control"
                                        value="{{ date('d M Y', strtotime($wholesaler->created_at)) }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="referred_by" class="title-color">{{ translate('referred_by') }}</label>
                                    <input type="text" name="referred_by" class="form-control"
                                        value="{{ $wholesaler->refferd_by ?? translate('no_data_found') }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="moq_override" class="title-color">{{ translate('MOQ Override')
                                        }}</label>
                                    <input type="text" name="moq_override" class="form-control"
                                        value="{{ $wholesaler->moq_override_enabled ? 'Yes' : 'No' }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="moq_override" class="title-color">{{ translate('Tier') }}</label>
                                    <input type="text" name="moq_override" class="form-control"
                                        value="{{$wholesaler->tier }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="moq_override" class="title-color">{{ translate('Discount') }}</label>
                                    <input type="text" name="moq_override" class="form-control"
                                        value="{{$wholesaler->wholesaler_discount }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="moq_override" class="title-color">{{ translate('Status') }}</label>
                                    <input type="text" name="moq_override" class="form-control"
                                        value="{{ $wholesaler->wholesaler_status == 1 ? 'Active' : 'Inactive' }}"
                                        readonly>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="text-center mb-3">
                                        <img class="upload-img-view" id="viewer"
                                            src="{{ getStorageImages(path: $wholesaler->image_full_url , type: 'backend-profile') }}"
                                            alt="" />
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="mb-0 page-header-title d-flex align-items-center gap-2 border-bottom pb-3 mb-3">
                            <i class="tio-user"></i>
                            {{ translate('wholesaler_information') }}
                        </h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tier" class="title-color">{{ translate('tier') }}</label>
                                    <select name="tier" class="form-control" required>
                                        @foreach($tiers as $tier)
                                        <option value="{{ $tier->name }}" {{ $wholesaler->tier == $tier->name ?
                                            'selected' : '' }}>
                                            {{ ucfirst($tier->name) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="wholesaler_discount" class="title-color">{{ translate('Discount')
                                        }}</label>
                                    <input type="number" name="wholesaler_discount" class="form-control"
                                        value="{{ $wholesaler->wholesaler_discount }}"
                                        placeholder="{{ translate('enter_discount') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status" class="title-color">{{ translate('Status') }}</label>
                                    <select name="wholesaler_status" class="form-control" required>
                                        <option value="1" {{ $wholesaler->wholesaler_status == 1 ? 'selected' : '' }}>{{
                                            translate('active') }}</option>
                                        <option value="0" {{ $wholesaler->wholesaler_status == 0 ? 'selected' : '' }}>{{
                                            translate('inactive') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <button type="submit" class="btn btn--primary px-4">{{ translate('update') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/spartan-multi-image-picker.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/select-multiple-image.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/country-picker-init.js') }}"></script>
@endpush