@php
    $resolvedExtraCharges = $productData->extraCharges ?? ['installation' => 0, 'exchange' => 0];
    $installationCharge = max(0, (float)($resolvedExtraCharges['installation'] ?? 0));
    $exchangeCharge = max(0, (float)($resolvedExtraCharges['exchange'] ?? 0));
@endphp

@if(($productData['product_type'] ?? null) === 'physical' && ($installationCharge > 0 || $exchangeCharge > 0))
    <div class="product-extra-charge-options bg-light rounded p-3 mt-3 d-flex flex-column gap-3"
         data-exchange-qty-min-message="{{ translate('Exchange qty must be at least 1 when Replacement Discount is enabled.') }}"
         data-exchange-qty-limit-message="{{ translate('Exchange qty cannot exceed product quantity.') }}">
        <input type="hidden" name="installation_charge" class="js-installation-charge-value" value="0">
        <input type="hidden" name="replacement_discount_enabled" class="js-replacement-discount-enabled" value="0">
        <input type="hidden" name="exchange_quantity" class="js-exchange-quantity-value" value="0">

        @if($installationCharge > 0)
            <label class="form-check d-flex align-items-center gap-2 mb-0">
                <input type="checkbox"
                       class="form-check-input js-installation-charge-checkbox"
                       data-price="{{ $installationCharge }}">
                <span class="form-check-label d-flex gap-1 flex-wrap">
                    <strong>{{ translate('installation_charge') }}:</strong>
                    <span>{{ webCurrencyConverter(amount: $installationCharge) }}</span>
                </span>
            </label>
        @endif

        @if($exchangeCharge > 0)
            <div class="d-flex flex-column gap-2">
                <label class="form-check d-flex align-items-center gap-2 mb-0">
                    <input type="checkbox"
                           class="form-check-input js-exchange-charge-checkbox"
                           data-price="{{ $exchangeCharge }}">
                    <span class="form-check-label d-flex gap-1 flex-wrap">
                        <strong>{{ translate('exchange_charge') }}:</strong>
                        <span>{{ webCurrencyConverter(amount: $exchangeCharge) }}</span>
                    </span>
                </label>

                <div class="js-exchange-qty-wrapper d-none ps-sm-4">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <strong>{{ translate('Exchange_qty') }}:</strong>
                        <div class="d-inline-flex align-items-center border rounded overflow-hidden bg-white">
                            <button type="button" class="btn btn-link text-dark text-decoration-none px-3 py-2 js-exchange-qty-minus">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="text"
                                   class="form-control border-0 text-center rounded-0 shadow-none js-exchange-qty-input"
                                   value="0"
                                   inputmode="numeric"
                                   autocomplete="off"
                                   style="max-width: 4.5rem;">
                            <button type="button" class="btn btn-link text-dark text-decoration-none px-3 py-2 js-exchange-qty-plus">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
