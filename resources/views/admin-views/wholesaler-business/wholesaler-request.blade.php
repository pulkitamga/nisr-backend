@extends('layouts.back-end.app')

@section('title', translate('WholeSaler_Business_Requests'))

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
            {{translate('WholeSaler_Business_Requests')}}
        </h2>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="px-3 py-4 light-bg">
                    <div class="row g-2 align-items-center flex-grow-1">
                        <div class="col-md-7 col-lg-8" >
                            <h5 class="text-capitalize d-flex gap-1">
                                {{translate('Wholesaler_request')}}
                                <span class="badge badge-soft-dark radius-50 fz-12"></span>
                            </h5>
                        </div>
                        <div class="col-md-5 col-lg-4 d-flex gap-3  flex-sm-nowrap justify-content-end">
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
                                <a type="button" class="align-items-center btn btn-block btn-outline--primary d-flex pe-4" href="{{route('admin.wholesale.business.wholesale-req.export')}}">
                                    <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                    <span class="ps-2">{{ translate('export') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-3 py-4">
                    <div class="table-responsive">
                        <table
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                            <thead class="thead-light text-capitalize">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('wholesaler')}}</th>
                                    <th>{{translate('company')}}</th>
                                    <th>{{translate('trade')}}</th>
                                    <th>{{translate('reg._no.')}}</th>
                                    <th>{{translate('tax._no.')}}</th>
                                    <th>{{translate('VAT._no.')}}</th>
                                    <th>{{translate('tier')}}</th> <!-- Tier Column -->
                                    <th>{{translate('discount %')}}</th> <!-- Discount Column -->
                                    <th class="text-center">{{translate('action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $hasRequest = false; @endphp

                                @foreach($wholesaler_business as $key => $business)
                                @if($business->wholesaler && $business->wholesaler->wholesaler_status != 1)
                                @php $hasRequest = true; @endphp

                                <tr>
                                    <td>{{ $wholesaler_business->firstItem() + $key }}</td>
                                    <td>{{ $business->wholesaler->name ?? __('N/A') }}</td>
                                    <td>{{ $business->company_name ?? __('N/A') }}</td>
                                    <td>{{ $business->trade_name ?? __('N/A') }}</td>
                                    <td>
                                        {{ $business->registration_number ?? __('N/A') }}
                                        @if($business->register_copy)
                                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                            data-target="#registrationModal{{$business->id}}">{{ __('View') }}</button>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $business->tax_id ?? __('N/A') }}
                                        @if($business->tax_card_copy)
                                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                            data-target="#taxModal{{$business->id}}">{{ __('View') }}</button>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $business->vat_number ?? __('N/A') }}
                                        @if($business->vat_register_copy)
                                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                            data-target="#vatModal{{$business->id}}">{{ __('View') }}</button>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="tier" class="form-control w-fit-content min-w-[150px]" required>
                                            <option value="" disabled {{ $business->wholesaler->tier == null ?
                                                'selected' : '' }}>
                                                {{ translate('Select Tier') }}
                                            </option>
                                            @foreach($tiers as $tier)
                                            <option value="{{ $tier->name }}" {{ $business->wholesaler->tier ==
                                                $tier->name ? 'selected' : '' }}>
                                                {{ $tier->getTranslatedField('name') }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="number" name="wholesaler_discount" class="form-control"
                                            value="{{ old('wholesaler_discount', $business->wholesaler->wholesaler_discount) }}"
                                            required>
                                    </td>
                                    <td class="d-flex gap-3">
                                        {{-- Approve Button --}}
                                        <form action="{{ route('admin.wholesale.business.approve-reject') }}" method="POST" class="approval-form">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $business->wholesaler['id'] }}">
                                            <input type="hidden" name="tier" class="tier-value" value="{{ $business->wholesaler->tier }}">
                                            <input type="hidden" name="wholesaler_discount" class="discount-value" value="{{ $business->wholesaler->wholesaler_discount }}">
                                            <button type="button" class="btn btn-outline-success btn-sm swal-approve-btn" title="{{ translate('Approve') }}">
                                                <i class="tio-checkmark-circle" style="color: green;     font-size: x-large;"></i>
                                            </button>
                                            <input type="hidden" name="action" value="approve">
                                        </form>

                                        {{-- Reject Button --}}
                                        <form action="{{ route('admin.wholesale.business.approve-reject') }}" method="POST" class="approval-form">
                                            @csrf
                                            <input type="hidden" name="tier" class="tier-value" value="silver">
                                            <input type="hidden" name="wholesaler_discount" class="discount-value" value="0">
                                            <input type="hidden" name="id" value="{{ $business->wholesaler['id'] }}">
                                            <button type="button" class="btn btn-outline-danger btn-sm swal-reject-btn" title="{{ translate('Reject') }}">
                                                <i class="tio-clear" style="color: red; font-size: x-large;"></i>
                                            </button>
                                            <input type="hidden" name="action" value="reject">
                                        </form>
                                    </td>

                                </tr>

                                <!-- Modal for Registration Copy -->
                                <div class="modal fade" id="registrationModal{{$business->id}}" tabindex="-1"
                                    role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content text-start">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Registration Copy') }}
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="{{ translate('Close') }}">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <img src="{{ asset('storage/register_copies/' . $business->register_copy) }}"
                                                    class="img-fluid" alt="{{ __('Registration Copy') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal for Tax Card Copy -->
                                <div class="modal fade" id="taxModal{{$business->id}}" tabindex="-1" role="dialog"
                                    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content text-start">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Tax Card Copy') }}</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="{{ translate('Close') }}">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <img src="{{ asset('storage/tax_cards/' . $business->tax_card_copy) }}"
                                                    class="img-fluid" alt="{{ __('Tax Card Copy') }}">

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal for VAT Register Copy -->
                                <div class="modal fade" id="vatModal{{$business->id}}" tabindex="-1" role="dialog"
                                    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content text-start">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('VAT Register Copy') }}
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="{{ translate('Close') }}">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <img src="{{ asset('storage/vat_copies/' . $business->vat_register_copy) }}"
                                                    class="img-fluid" alt="{{ __('VAT Register Copy') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endforeach

                                @if(!$hasRequest)
                                <tr>
                                    <td colspan="11" class="text-center text-danger py-4">
                                        {{ translate('No wholesale requests found') }}
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script_2')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.querySelectorAll('select[name="tier"]').forEach((select, index) => {
        select.addEventListener('change', function() {
            const hiddenInput = select.closest('tr').querySelector('.tier-value');
            if (hiddenInput) hiddenInput.value = this.value;
        });
    });

    document.querySelectorAll('input[name="wholesaler_discount"]').forEach((input, index) => {
        input.addEventListener('input', function() {
            const hiddenInput = input.closest('tr').querySelector('.discount-value');
            if (hiddenInput) hiddenInput.value = this.value;
        });
    });

   // Approve
document.querySelectorAll('.swal-approve-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        Swal.fire({
            title: @json(__('Are you sure to approve?')),
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: @json(__('Yes, approve')),
            cancelButtonText: @json(__('Cancel')),
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('form').submit();
            }
        });
    });
});

// Reject
document.querySelectorAll('.swal-reject-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        Swal.fire({
            title: @json(__('Are you sure to reject?')),
            text: "",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: @json(__('Yes, reject')),
            cancelButtonText: @json(__('Cancel')),
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('form').submit();
            }
        });
    });
});

</script>
@endpush

@endsection


