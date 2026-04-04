@php($serviceRequestFieldNames = ['service_option', 'country', 'state', 'city', 'area', 'address', 'latitude', 'longitude', 'vehicle_type', 'vehicle_make', 'vehicle_model', 'vehicle_year', 'vehicle_mileage', 'vin', 'problem_description', 'notes', 'agree_terms'])
@php($serviceRequestHasErrors = collect($serviceRequestFieldNames)->contains(fn($field) => $errors->has($field)))
@php($oldServiceOption = old('service_option', 'in_shop'))

<div class="__btn-grp mt-2 my-3">
    @if(auth()->guard('customer')->check())
        <button type="button"
            class="btn btn--primary element-center"
            data-bs-toggle="modal"
            data-bs-target="#serviceRequestModal">
            <span class="string-limit">{{ translate('request_service') }}</span>
        </button>
    @else
        <div class="service-login-card">
            <p>{{ translate('Sign in first to submit your service request and track updates.') }}</p>
            <a href="{{ route('customer.auth.login') }}" class="btn btn--primary element-center">
                <span class="string-limit">{{ translate('Login First') }}</span>
            </a>
        </div>
    @endif

    @if(($product->added_by == 'seller' && ($sellerTemporaryClose || (isset($product->seller->shop) && $product->seller->shop->vacation_status && $currentDate >= $sellerVacationStartDate && $currentDate <= $sellerVacationEndDate))) ||
        ($product->added_by == 'admin' && ($inHouseTemporaryClose || ($inHouseVacationStatus && $currentDate >= $inHouseVacationStartDate && $currentDate <= $inHouseVacationEndDate))))
        <div class="alert alert-danger mt-2" role="alert">
            {{ translate('this_shop_is_temporary_closed_or_on_vacation._You_cannot_add_product_to_cart_from_this_shop_for_now') }}
        </div>
    @endif
</div>

<div class="modal fade service-request-modal" id="serviceRequestModal" tabindex="-1" aria-labelledby="serviceRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title service-request-title">{{ translate('Request Service') }}</h5>
                    <p class="service-request-subtitle">{{ translate('Choose your service type, confirm your details, and submit in one step.') }}</p>
                </div>
                <button type="button" class="close custom-close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    &times;
                </button>
            </div>

            <form id="serviceRequestForm" action="{{ route('service.request.store') }}" method="POST">
                @csrf
                <div class="modal-body request-model-body pt-1 px-3 px-md-4">
                    <div class="service-request-summary">
                        <div class="service-request-chip">
                            <span class="service-request-chip-label">{{ translate('Service') }}</span>
                            <span class="service-request-chip-value">{{ $serviceTitle }}</span>
                        </div>
                        <div class="service-request-chip">
                            <span class="service-request-chip-label">{{ translate('In-shop') }}</span>
                            <span class="service-request-chip-value">{{ webCurrencyConverter(amount: $product->service->base_price_inshop) }}</span>
                        </div>
                        <div class="service-request-chip">
                            <span class="service-request-chip-label">{{ translate('Mobile') }}</span>
                            <span class="service-request-chip-value">{{ webCurrencyConverter(amount: $product->service->base_price_mobile) }}</span>
                        </div>
                    </div>

                    <input type="hidden" name="service_id" value="{{ $product->service->id }}">
                    @if(auth()->guard('customer')->check())
                        <input type="hidden" name="customer_id" value="{{ auth()->guard('customer')->user()->id }}">
                    @endif

                    <datalist id="vehicle-make-options">
                        @foreach($makes as $make)
                            <option value="{{ $make->name }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="vehicle-model-options"></datalist>

                    <div class="service-request-section">
                        <h6 class="service-request-section-title"><span>1</span>{{ translate('Price options') }}</h6>

                        <div class="custom-control custom-radio mb-2 padd-input-div">
                            <input type="radio" id="inShop" name="service_option" class="custom-control-input" value="in_shop" {{ $oldServiceOption === 'in_shop' ? 'checked' : '' }}>
                            <label class="custom-control-label radio-input-label service-option-card" for="inShop">
                                <span class="service-option-price">
                                    <span>{{ translate('In-shop') }}</span>
                                    <strong>{{ webCurrencyConverter(amount: $product->service->base_price_inshop) }}</strong>
                                </span>
                                <small class="service-option-note">{{ translate('Visit the service center and complete the request there.') }}</small>
                            </label>
                        </div>

                        <div class="custom-control custom-radio padd-input-div">
                            <input type="radio" id="mobile" name="service_option" class="custom-control-input" value="mobile" {{ $oldServiceOption === 'mobile' ? 'checked' : '' }}>
                            <label class="custom-control-label radio-input-label service-option-card" for="mobile">
                                <span class="service-option-price">
                                    <span>{{ translate('Mobile_within') }} {{ $product->service->included_km_mobile }}{{ translate('km') }}</span>
                                    <strong>{{ webCurrencyConverter(amount: $product->service->base_price_mobile) }}</strong>
                                </span>
                                <small class="service-option-note">+{{ webCurrencyConverter(amount: $product->service->travel_fee_per_km) }}{{ translate('/km_beyond') }} {{ $product->service->included_km_mobile }}{{ translate('km') }}</small>
                            </label>
                        </div>
                    </div>

                    <div class="service-request-section">
                        <h6 class="service-request-section-title"><span>2</span>{{ translate('Service expectations') }}</h6>
                        <div id="inShopGuidance" class="service-mode-guidance">
                            <span class="service-mode-icon"><i class="tio-shop"></i></span>
                            <div>
                                <h6>{{ translate('In-shop service') }}</h6>
                                <p>{{ translate('Visit the service center and complete the request there.') }}</p>
                                <div class="service-mode-guidance-badges">
                                    <span class="service-mode-guidance-badge">{{ translate('Base Price (In-shop)') }}: {{ webCurrencyConverter(amount: $product->service->base_price_inshop) }}</span>
                                    <span class="service-mode-guidance-badge">{{ translate('Bring your vehicle details and issue summary.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div id="mobileGuidance" class="service-mode-guidance" style="display: none;">
                            <span class="service-mode-icon"><i class="tio-map"></i></span>
                            <div>
                                <h6>{{ translate('Mobile service') }}</h6>
                                <p>{{ translate('Share your exact service address and pin your location so the team can reach you faster.') }}</p>
                                <div class="service-mode-guidance-badges">
                                    <span class="service-mode-guidance-badge">{{ translate('Map status') }}: {{ getWebConfig('map_api_status') == 1 ? translate('Map available') : translate('Map unavailable') }}</span>
                                    <span class="service-mode-guidance-badge">{{ translate('Free up to') }} {{ $product->service->included_km_mobile }} {{ translate('km') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="service-request-section" id="mobileAddress" style="display: none;">
                        <h6 class="service-request-section-title"><span>3</span>{{ translate('Service address') }}</h6>
                        <div class="row">
                            <div class="form-group col-lg-12">
                                <label>{{ translate('country') }} <span class="text-danger">*</span></label>
                                <select id="address-country" class="form-control selectpicker @error('country') is-invalid @enderror" data-live-search="true" required>
                                    @foreach($countries as $d)
                                        <option value="{{ $d['code'] }}" data-name="{{ $d['name'] }}">{{ $d['name'] }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="country" id="country_name" value="{{ old('country') }}">
                                @error('country')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-lg-6">
                                <label>{{ translate('state') }} <span class="text-danger">*</span></label>
                                <select id="address-state" class="form-control @error('state') is-invalid @enderror" required></select>
                                <input type="hidden" name="state" id="state_name" value="{{ old('state') }}">
                                @error('state')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-lg-6">
                                <label>{{ translate('city') }} <span class="text-danger">*</span></label>
                                <select id="address-city" class="form-control @error('city') is-invalid @enderror" required></select>
                                <input type="hidden" name="city" id="city_name" value="{{ old('city') }}">
                                @error('city')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-lg-6">
                                <label>{{ translate('area') }} <span class="text-danger">*</span></label>
                                <select id="address-area" class="form-control @error('area') is-invalid @enderror" required></select>
                                <input type="hidden" name="area" id="area_name" value="{{ old('area') }}">
                                @error('area')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group col-lg-6">
                                <label>{{ translate('address') }} <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address_details" rows="1" placeholder="{{ translate('Building, Landmark, etc.') }}">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if(getWebConfig('map_api_status') == 1)
                            <div class="row">
                                <div class="form-group col-lg-6 col-md-12 col-sm-12">
                                    <label>{{ translate('Latitude') }}:</label>
                                    <input type="text" id="latitude" name="latitude" class="form-control mb-2 @error('latitude') is-invalid @enderror" value="{{ old('latitude') }}" readonly />
                                    @error('latitude')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-lg-6 col-md-12 col-sm-12">
                                    <label>{{ translate('Longitude') }}:</label>
                                    <input type="text" id="longitude" name="longitude" class="form-control mb-2 @error('longitude') is-invalid @enderror" value="{{ old('longitude') }}" readonly />
                                    @error('longitude')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group location-map-canvas-area map-area-alert-border">
                                <input type="text" id="pac-input" placeholder="{{ translate('Search location') }}" class="form-control mb-2" />
                                <div id="location_map_canvas" style="height: 300px; width: 100%;"></div>
                            </div>
                        @endif
                    </div>

                    <div class="service-request-section">
                        <h6 class="service-request-section-title"><span>4</span>{{ translate('Vehicle details') }}</h6>
                        <div class="row g-2">
                            <div class="col-6 col-md-4">
                                <label class="form-label">{{ translate('Vehicle Type') }}</label>
                                <select name="vehicle_type" class="form-control @error('vehicle_type') is-invalid @enderror">
                                    <option value="">{{ translate('Select vehicle type') }}</option>
                                    <option value="Sedan" {{ old('vehicle_type') === 'Sedan' ? 'selected' : '' }}>{{ translate('Sedan') }}</option>
                                    <option value="SUV" {{ old('vehicle_type') === 'SUV' ? 'selected' : '' }}>{{ translate('SUV') }}</option>
                                    <option value="Truck" {{ old('vehicle_type') === 'Truck' ? 'selected' : '' }}>{{ translate('Truck') }}</option>
                                    <option value="Other" {{ old('vehicle_type') === 'Other' ? 'selected' : '' }}>{{ translate('Other') }}</option>
                                </select>
                                @error('vehicle_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-4">
                                <label for="makeSelect">{{ translate('Make') }}</label>
                                <input type="text" id="makeSelect" name="vehicle_make" class="form-control @error('vehicle_make') is-invalid @enderror" list="vehicle-make-options" value="{{ old('vehicle_make') }}" placeholder="{{ translate('Select_Make') }}" autocomplete="off">
                                <small class="form-text">{{ translate('Select from suggestions or type another make.') }}</small>
                                @error('vehicle_make')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-4">
                                <label for="modelSelect">{{ translate('Model') }}</label>
                                <input type="text" id="modelSelect" name="vehicle_model" class="form-control @error('vehicle_model') is-invalid @enderror" list="vehicle-model-options" value="{{ old('vehicle_model') }}" placeholder="{{ translate('Select Model') }}" autocomplete="off">
                                <small class="form-text">{{ translate('Select from suggestions or type another model.') }}</small>
                                @error('vehicle_model')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-4">
                                <label for="yearSelect">{{ translate('Year') }}</label>
                                <select name="vehicle_year" class="form-control @error('vehicle_year') is-invalid @enderror">
                                    <option value="">{{ translate('Select Year') }}</option>
                                    @foreach($years as $year)
                                        <option value="{{ $year->year }}" {{ (string) old('vehicle_year') === (string) $year->year ? 'selected' : '' }}>{{ $year->year }}</option>
                                    @endforeach
                                </select>
                                @error('vehicle_year')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label">{{ translate('Mileage (km)') }}</label>
                                <input type="number" name="vehicle_mileage" class="form-control @error('vehicle_mileage') is-invalid @enderror" value="{{ old('vehicle_mileage') }}">
                                @error('vehicle_mileage')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label">{{ translate('VIN (optional)') }}</label>
                                <input type="text" name="vin" class="form-control @error('vin') is-invalid @enderror" value="{{ old('vin') }}">
                                @error('vin')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="service-request-section">
                        <h6 class="service-request-section-title"><span>5</span>{{ translate('Issue details') }}</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">{{ translate('Problem description') }} <span class="input-required-icon">*</span></label>
                                <textarea name="problem_description" class="form-control @error('problem_description') is-invalid @enderror" rows="4" placeholder="{{ translate('Describe the issue, symptoms, or service needed.') }}">{{ old('problem_description') }}</textarea>
                                @error('problem_description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ translate('Notes (Optional)') }}</label>
                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="{{ translate('Optional notes') }}">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    @if(!empty($partsIncluded))
                        <div class="service-request-section">
                            <h6 class="service-request-section-title"><span>6</span>{{ translate('What’s included') }}</h6>
                            <p class="mb-2 small text-muted">
                                {{ $partsIncluded }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="modal-footer px-3 px-md-4 py-3">
                    <div class="service-footer-stack">
                        <div class="service-consent @error('agree_terms') border-danger @enderror">
                            <input type="checkbox" class="service-consent-checkbox" id="agreeTerms" name="agree_terms" value="1" {{ old('agree_terms') ? 'checked' : '' }} required>
                            <label class="service-consent-label" for="agreeTerms">
                                {{ translate('I agree to the') }}
                                <a href="{{ route('service-policy') }}" target="_blank" rel="noopener noreferrer">{{ translate('Service Policies') }}</a>
                                {{ translate('and') }}
                                <a href="{{ route('terms') }}" target="_blank" rel="noopener noreferrer">{{ translate('terms_and_condition') }}</a>.
                            </label>
                        </div>
                        @error('agree_terms')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                        <button type="button" id="confirmServiceRequest" class="btn btn--primary w-100 service-request-submit">{{ translate('Confirm Request') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
