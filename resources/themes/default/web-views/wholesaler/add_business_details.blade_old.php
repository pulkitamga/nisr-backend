@extends('layouts.front-end.app')

@section('title', translate('add_business_details'))

@section('content')
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
                    <form action="{{route('wholesaler.save-business')}}" method="post">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('business_Name') }}</label>
                                <input type="text" class="form-control" id="business_name" name="business_name" value="" required="" placeholder="">
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('Address') }}</label>
                                <input type="text" class="form-control" id="address" name="address" value="" required="">
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('City') }}</label>
                                <input type="text" class="form-control" id="city" name="city" value="" required="">
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('State') }}</label>
                                <input type="text" class="form-control" id="state" name="state" value="" required="">
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="firstName" class="mb-2 text-capitalize">{{ translate('Country') }}</label>
                                <input type="text" class="form-control" id="country" name="country" value="" required="">
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