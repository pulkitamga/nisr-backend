@extends('layouts.front-end.app')

@section('title', translate('add_business_details'))

@section('content')
    @include('layouts.front-end.partials._store-header')

<style>
    .hide-this {
        display: none;
    }
</style>
<div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
    <div class="row">
        <div class="col-md-8 offset-2">
            <div class="card">
                <div class="card-header {{ count($SpecificBusiness) > 0 ? 'd-none' : '' }}">
                    <div class="card-title mb-0">{{ translate("add_Your_Business_Details") }}</div>
                </div>
                <div class="card-body">
                    @if(count($SpecificBusiness) == 0)
                    <form action="{{route('wholesaler.save-business')}}" method="post" enctype="multipart/form-data" >
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('company_Name') }}</label>
                                <input type="text" class="form-control"  name="company_name" value="" required="" placeholder="">
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('Trade_Name') }}</label>
                                <input type="text" class="form-control"  name="trade_name" value="" required="" placeholder="">
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('registration_Number') }}</label>
                                <input type="text" class="form-control"  name="registration_number" value="" required="" placeholder="">
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('Tax_ID') }}</label>
                                <input type="text" class="form-control"  name="tax_id" value="" required="" placeholder="">
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('commercial_Register_Document') }}</label>
                                <input type="file" class="form-control border-0"  name="register_copy" placeholder="">
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('Tax_Card_Copy') }}</label>
                                <input type="file" class="form-control border-0"  name="tax_card_copy" placeholder="">
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('VAT_Registration_Number') }}</label>
                                <input type="text" class="form-control"  name="vat_number" value="" required="" placeholder="">
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('VAT_Registration_Copy') }}</label>
                                <input type="file" class="form-control border-0"  name="vat_register_copy"  placeholder="">
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn--primary px-4 fs-14 font-semi-bold py-2 float-end">{{ translate('submit') }}</button>
                            </div>
                        </div>
                    </form>
                    @else
                    <div class="alert alert-success text-center p-4 rounded shadow-sm">
                        <h3 class="text-success fw-bold">
                            {{ translate('Your_Wholesaler_Profile_is_Under_Review!') }}
                        </h3>
                        <p class="mt-2 mb-0">
                            {{ translate('Our_team_is_diligently_verifying_your_details_to_ensure_a_seamless_onboarding_experience._You _will_receive_an_email_notification_once_your_verification_is_successfully_completed.') }}
                            <br>
                            <strong>{{ translate('Thank_you_for_your_patience_and_trust!') }}</strong>
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection