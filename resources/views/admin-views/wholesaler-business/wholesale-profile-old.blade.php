@extends('layouts.back-end.app')
@section('title', translate('wholesaler_details'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path:'public/assets/back-end/css/owl.min.css') }}">

<style>
    .nav-link.active {
        color: #377dff;
        border-bottom: 2px solid;
    }
    .field-colon {
        margin-inline: .75rem;
    }
    .bidi-auto {
        unicode-bidi: plaintext;
    }
    .bidi-ltr {
        direction: ltr;
        unicode-bidi: isolate;
        display: inline-block;
        text-align: left;
    }
</style>
@endpush

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
    <div class="d-print-none pb-2">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                    <img width="20" src="{{ dynamicAsset(path:'public/assets/back-end/img/add-new-seller.png') }}"
                        alt="">
                    {{ translate('wholesaler_details') }}
                </h2>
            </div>
        </div>
    </div>

    <div class="row g-2 h-100">
        {{-- ✅ Card 1: Wholesaler Info --}}
        <div class="col-xl-6 col-xxl-6 col--xxl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="mb-4 d-flex align-items-center gap-2">
                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/vendor-information.png') }}" alt="">
                        {{ translate('business_information')}}
                    </h4>

                    <div class="customer-details-new-card">
                        <img src="{{ getStorageImages(path: $wholesaler->image_full_url , type: 'backend-profile') }}"
                            alt="{{translate('image')}}" class="aspect-1">
                        <div class="customer-details-new-card-content">
                            <h6 class="name line--limit-2">{{ $business->company_name }}</h6>
                            <ul class="customer-details-new-card-content-list">
                                <li><span class="key">{{translate('contact')}}</span><span class="field-colon">:</span><strong
                                        class="value bidi-ltr">{{ $wholesaler->phone ?? translate('no_data_found') }}</strong>
                                </li>
                                <li><span class="key">{{translate('email')}}</span><span class="field-colon">:</span><strong
                                        class="value bidi-ltr">{{ $wholesaler->email ?? translate('no_data_found') }}</strong>
                                </li>
                                <li><span class="key text-capitalize">{{translate('joined_date')}}</span><span
                                        class="field-colon">:</span><strong class="value bidi-ltr">{{ date('d M Y',
                                        strtotime($wholesaler->created_at)) }}</strong></li>
                                <li><span class="key">{{translate('reffer by')}}</span><span
                                        class="field-colon">:</span><strong class="value bidi-auto">{{ $wholesaler->refferd_by ??
                                        translate('no_data_found') }}</strong></li>
                                <li><span class="key">{{translate('tier')}}</span><span class="field-colon">:</span><strong
                                        class="value bidi-auto">{{ $wholesaler->tier ?? translate('no_data_found') }}</strong>
                                </li>
                                <li><span class="key">{{translate('discount')}}</span><span class="field-colon">:</span><strong
                                        class="value bidi-ltr">{{ $wholesaler->wholesaler_discount ?? translate('no_data_found')
                                        }}%</strong></li>
                                <li><span class="key">{{translate('MOQ Override')}}</span><span
                                        class="field-colon">:</span><strong class="value bidi-auto">{{ $wholesaler->moq_override_enabled
                                        ? 'Yes' : 'No'}}</strong></li>


                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ Card 2: Business Info --}}
        <div class="col-xl-6 col-xxl-6 col--xxl-6">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="mb-4 d-flex align-items-center gap-2 text-capitalize">{{
                        translate('Company_information') }}</h4>
                    <div>
                        <ul class="customer-details-new-card-content-list">
                            <li><strong class="key">Company</strong><span class="field-colon">:</span><span class="value bidi-auto">{{
                                    $business->company_name }}</span></li>
                            <li><strong class="key">Trade Name</strong><span class="field-colon">:</span><span class="value bidi-auto">{{
                                    $business->trade_name }}</span></li>
                            <li><strong class="key">Reg. No.</strong><span class="field-colon">:</span><span class="value bidi-ltr">{{
                                    $business->registration_number }}</span></li>
                            <li><strong class="key">Tax ID</strong><span class="field-colon">:</span><span class="value bidi-ltr">{{
                                    $business->tax_id }}</span></li>
                            <li><strong class="key">VAT No</strong><span class="field-colon">:</span><span class="value bidi-ltr">{{
                                    $business->vat_number }}</span></li>
                        </ul>
                        <div class="mt-3">
                            @if($business->register_copy)
                            <a href="{{ asset('storage/register_copies/'.$business->register_copy) }}"
                                target="_blank">{{ translate('View Registration Copy') }}</a><br>
                            @endif
                            @if($business->tax_card_copy)
                            <a href="{{ asset('storage/tax_cards/'.$business->tax_card_copy) }}" target="_blank">{{
                                translate('View Tax Card') }}</a><br>
                            @endif
                            @if($business->vat_register_copy)
                            <a href="{{ asset('storage/vat_copies/'.$business->vat_register_copy) }}" target="_blank">{{
                                translate('View VAT Register') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(count($wholesaler->addresses)>0)
        <div class="col-xl-6 col-xxl-6 col--xxl-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-4 d-flex align-items-center gap-2 text-capitalize">{{ translate('saved_address') }}
                    </h4>

                    {{-- Shipping Addresses --}}
                    <h5 class="mb-3">{{ translate('Shipping Addresses') }}</h5>
                    <div class="address-slider owl-theme owl-carousel mb-4">
                        @foreach($wholesaler->addresses->where('is_billing', 0) as $address)
                        <div class="customer-address-card customer-details-new-card p-3">
                            <h6 class="name text-14 mb-3">
                                {{ $address['address_type'] }}
                            </h6>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <p class="mb-1"><strong>{{ translate('name') }}:</strong> <span class="bidi-auto">{{
                                        $address['contact_person_name'] }}</span></p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-1"><strong>{{ translate('phone') }}:</strong> <span class="bidi-ltr">{{ $address['phone'] }}</span>
                                    </p>
                                </div>
                                <div class="col-12">
                                    <p class="mb-1"><strong>{{ translate('address') }}:</strong> <span class="bidi-auto">{{ $address['address']
                                        }}</span></p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Billing Addresses --}}
                    <h5 class="mb-3">{{ translate('Billing Addresses') }}</h5>
                    <div class="address-slider owl-theme owl-carousel">
                        @foreach($wholesaler->addresses->where('is_billing', 1) as $address)
                        <div class="customer-address-card customer-details-new-card p-3">
                            <h6 class="name text-14 mb-3">
                                {{ $address['address_type'] }}
                            </h6>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <p class="mb-1"><strong>{{ translate('name') }}:</strong> <span class="bidi-auto">{{
                                        $address['contact_person_name'] }}</span></p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-1"><strong>{{ translate('phone') }}:</strong> <span class="bidi-ltr">{{ $address['phone'] }}</span>
                                    </p>
                                </div>
                                <div class="col-12">
                                    <p class="mb-1"><strong>{{ translate('address') }}:</strong> <span class="bidi-auto">{{ $address['address']
                                        }}</span></p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
        @endif



        <div class="col-xl-6 col-xxl-6 col--xxl-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="text-capitalize">{{ translate('Contact') }}</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#addContactModal">
                        {{ translate('Add Contact') }}
                    </button>
                </div>
                <div>
                    <div class="card">
                        @if($business->contacts && $business->contacts->count())
                        @foreach($business->contacts as $contact)
                        <div class="card-header">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#editContactModal-{{ $contact->id }}"> <i class="tio-edit"></i>
                            </button>
                            <form method="POST"
                                action="{{ route('admin.wholesale.business.wholsaler-contect.softDelete', $contact->id) }}"
                                style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Are you sure to delete?')"> <i class="tio-delete"></i>
                                </button>
                            </form>
                        </div>
                        <div class="row card-body g-3">
                            <div class="col-md-6">

                                <p class="mb-2"><strong>Name:</strong> <span class="bidi-auto">{{ $contact->first_name }} {{ $contact->last_name }}</span></p>
                                @if($contact->job_title)
                                <p class="mb-2"><strong>Job Title:</strong> <span class="bidi-auto">{{ $contact->job_title }}</span></p>
                                @endif
                                <p class="mb-2"><strong>Email:</strong> <span class="bidi-ltr">{{ $contact->email }}</span></p>
                                <p class="mb-2"><strong>Phone:</strong> <span class="bidi-ltr">{{ $contact->phone_number }}</span></p>
                                <p class="mb-2"><strong>Mobile 1:</strong> <span class="bidi-ltr">{{ $contact->mobile_number_1 }}</span></p>
                                <p class="mb-2"><strong>Mobile 2:</strong> <span class="bidi-ltr">{{ $contact->mobile_number_2 }}</span></p>
                                <p class="mb-2"><strong>Preferred Contact Method:</strong> <span class="bidi-auto">{{
                                    $contact->preferred_contact_method }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Address:</strong><br>
                                    <span class="bidi-auto">{{ $contact->address }}</span><br>
                                    <span class="bidi-auto">{{ $contact->city }}, {{ $contact->state }}</span><br>
                                    <span class="bidi-auto">{{ $contact->country }}</span>
                                </p>
                                <p class="mb-2"><strong>Notes:</strong> <span class="bidi-auto">{{ $contact->notes }}</span></p>
                                <p class="mb-2"><strong>Tags:</strong> <span class="bidi-auto">{{ $contact->tags }}</span></p>
                                <p class="mb-2"><strong>Active:</strong> <span class="bidi-auto">{{ $contact->is_active ? 'Yes' : 'No' }}</span></p>
                                <p class="mb-2"><strong>Last Contacted:</strong> <span class="bidi-ltr">{{ $contact->last_contacted_at }}</span></p>

                                <div class="modal fade" id="editContactModal-{{ $contact->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <form
                                            action="{{ route('admin.wholesale.business.wholsaler-contect.update', $contact->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Contact</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close">X</button>

                                                </div>

                                                <div class="modal-body row g-3">
                                                    <div class="col-md-6">
                                                        <label>First Name</label>
                                                        <input type="text" name="first_name" class="form-control"
                                                            value="{{ $contact->first_name }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Last Name</label>
                                                        <input type="text" name="last_name" class="form-control"
                                                            value="{{ $contact->last_name }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Email</label>
                                                        <input type="email" name="email" class="form-control"
                                                            value="{{ $contact->email }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Phone Number</label>
                                                        <input type="text" name="phone_number" class="form-control"
                                                            value="{{ $contact->phone_number }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Mobile Number 1</label>
                                                        <input type="text" name="mobile_number_1" class="form-control"
                                                            value="{{ $contact->mobile_number_1 }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Mobile Number 2</label>
                                                        <input type="text" name="mobile_number_2" class="form-control"
                                                            value="{{ $contact->mobile_number_2 }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Job Title</label>
                                                        <input type="text" name="job_title" class="form-control"
                                                            value="{{ $contact->job_title }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Preferred Contact Method</label>
                                                        <input type="text" name="preferred_contact_method"
                                                            class="form-control"
                                                            value="{{ $contact->preferred_contact_method }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Address</label>
                                                        <input type="text" name="address" class="form-control"
                                                            value="{{ $contact->address }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>City</label>
                                                        <input type="text" name="city" class="form-control"
                                                            value="{{ $contact->city }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>State</label>
                                                        <input type="text" name="state" class="form-control"
                                                            value="{{ $contact->state }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Country</label>
                                                        <input type="text" name="country" class="form-control"
                                                            value="{{ $contact->country }}">
                                                    </div>

                                                    <div class="col-md-12">
                                                        <label>Notes</label>
                                                        <textarea name="notes"
                                                            class="form-control">{{ $contact->notes }}</textarea>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Tags</label>
                                                        <input type="text" name="tags" class="form-control"
                                                            value="{{ $contact->tags }}">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Is Active</label>
                                                        <select name="is_active" class="form-select">
                                                            <option value="1" {{ $contact->is_active ? 'selected' : ''
                                                                }}>Yes</option>
                                                            <option value="0" {{ !$contact->is_active ? 'selected' : ''
                                                                }}>No</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Last Contacted At</label>
                                                        <input type="datetime-local" name="last_contacted_at"
                                                            class="form-control"
                                                            value="{{ $contact->last_contacted_at ? date('Y-m-d\TH:i', strtotime($contact->last_contacted_at)) : '' }}">
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button class="btn btn-primary" type="submit">Update</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="alert alert-warning">
                            {{ translate('No contact details found.') }}
                        </div>
                        @endif

                    </div>
                </div>

            </div>

            <div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <!-- large modal -->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ translate('Add Contact') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close">X</button>
                        </div>

                        <form action="{{ route('admin.wholesale.business.wholsaler-contect') }}" method="POST">
                            @csrf
                            <input type="hidden" name="company_id" value="{{ $business->id }}">

                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="job_title" class="form-label">Job Title</label>
                                        <input type="text" name="job_title" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="phone_number" class="form-label">Phone Number</label>
                                        <input type="text" name="phone_number" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="mobile_number_1" class="form-label">Mobile Number 1</label>
                                        <input type="text" name="mobile_number_1" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="mobile_number_2" class="form-label">Mobile Number 2</label>
                                        <input type="text" name="mobile_number_2" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="preferred_contact_method" class="form-label">Preferred
                                            Contact Method</label>
                                        <input type="text" name="preferred_contact_method" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="address" class="form-label">Address</label>
                                        <input type="text" name="address" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" name="city" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" name="state" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="country" class="form-label">Country</label>
                                        <input type="text" name="country" class="form-control">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="tags" class="form-label">Tags</label>
                                    <input type="text" name="tags" class="form-control">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label d-block">Active?</label>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="is_active" value="0"> <!-- fallback -->
                                            <input class="form-check-input" type="checkbox" name="is_active"
                                                id="is_active" value="1" checked>
                                            <label class="form-check-label" for="is_active">Yes</label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="last_contacted_at" class="form-label">Last Contacted
                                            At</label>
                                        <input type="datetime-local" name="last_contacted_at" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Contact</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Card 3: Orders --}}
    <div class="col-lg-12 pt-lg-6">
        <div class="inline-page-menu my-4">
            <ul class="list-unstyled" id="orderTabs">
                <li class="">
                    <a class="nav-link active" href="#" data-type="purchase">Purchase Order</a>
                </li>
                <li class="">
                    <a class="nav-link" href="#" data-type="quotation">Quotation</a>
                </li>
                <li class="">
                    <a class="nav-link" href="#" data-type="confirmed">Confirmed Order</a>
                </li>
            </ul>
        </div>


        <div class="card">
            <div class="table-responsive" id="orderTableArea">
                {{-- Table will be loaded here dynamically --}}
                <div class="text-center p-4 text-muted">Loading...</div>
            </div>
        </div>
    </div>

    @endsection

    @push('script')
    <script src="{{ dynamicAsset(path:'public/assets/back-end/js/owl.min.js') }}"></script>
    <script>
        'use strict';
        $('.order-statistics-slider, .address-slider').owlCarousel({
            margin: 16,
            loop: false,
            autoWidth: true,
        });
        
         document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.order-tab').forEach(tab => tab.classList.add('d-none'));
            document.querySelector(this.dataset.target).classList.remove('d-none');
        });
    });




     $(document).ready(function () {
        function loadTable(type) {
    $('#orderTableArea').html('<div class="text-center p-4 text-muted">Loading...</div>');

    $.get("{{ route('admin.wholesale.business.orders.by-type') }}", { type: type }, function (data) {
        $('#orderTableArea').html(data.html);
    });
}


        // Load first tab by default
        loadTable('purchase');

        $('#orderTabs .nav-link').on('click', function (e) {
            e.preventDefault();
            $('#orderTabs .nav-link').removeClass('active');
            $(this).addClass('active');
            let type = $(this).data('type');
            loadTable(type);
        });
    });
    </script>
    @endpush
