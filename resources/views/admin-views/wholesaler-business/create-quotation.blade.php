@extends('layouts.back-end.app')
@section('title', translate('Create Quotation'))
@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/wholesale-builder.css') }}">
<link href="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush
@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}
@endphp
<div class="content container-fluid">

    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <h2 class="h1 mb-0 d-flex gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
            {{ translate('Create_quotation') }}
        </h2>
    </div>
    <form action="{{ route('admin.wholesale.business.store-quotation') }}" method="POST" id="quotation-form"
        class="wholesale-builder-shell">
        @csrf
        <input type="hidden" name="deal_id" id="deal_id_hidden" value="">
        <div class="card wholesale-builder-section">
            <div class="wholesale-builder-section__header">
                <div>
                    <h3 class="wholesale-builder-section__title">{{ translate('Quotation setup') }}</h3>
                    <p class="wholesale-builder-section__subtitle">{{ translate('Select Wholesaler') }} {{ translate('and') }} {{ translate('Quotation No') }}</p>
                </div>
                <div class="crm-list-toolbar__summary">
                    <span class="crm-list-toolbar__chip">
                        <span class="crm-list-toolbar__chip-label">{{ translate('Wholesaler') }}</span>
                        <span id="summary-selected-wholesaler" class="bidi-auto">{{ translate('N/A') }}</span>
                    </span>
                    <span class="crm-list-toolbar__chip crm-list-toolbar__chip--muted">
                        <span class="crm-list-toolbar__chip-label">{{ translate('Wholesaler Tier') }}</span>
                        <span id="summary-selected-tier" class="bidi-auto">{{ translate('N/A') }}</span>
                    </span>
                </div>
            </div>
            <div class="wholesale-builder-section__body">
            <div class="flex flex-wrap items-center gap-6 mb-4">
                <!-- Quotation No -->
                <div class="flex-1">
                    <label for="quotation_no" class="block text-md font-semibold text-gray-700 whitespace-nowrap">
                        {{ translate('Quotation No') }}:
                    </label>
                    <input type="text" name="quotation_no" id="quotation_no_input"
                        class="form-control w-full"
                        placeholder="{{ translate('Enter Quotation No') }}" required>
                    <span id="order_no_status" class="text-sm"></span>
                </div>


                <!-- Wholesaler Select Dropdown -->
                <div class="flex-1">
                    <label for="wholesaler-select" class="text-md font-semibold text-gray-700 whitespace-nowrap">
                        {{ translate('Select Wholesaler') }}:
                    </label>
                    <select id="wholesaler-select" class="form-control w-auto select2">
                        <option value="" aria-readonly>{{ translate('Select Wholesaler') }}</option>
                        @foreach($wholesalers as $wholesaler)
                        <option value="{{ $wholesaler->id }}"
                            data-name="{{ $wholesaler->wholesalerBusiness->company_name ?? '' }}"
                            data-email="{{ $wholesaler->email }}"
                            data-phone="{{ $wholesaler->phone ?? '' }}"
                            data-wholesalediscount="{{ $wholesaler->wholesaler_discount ?? 0 }}"
                            data-id="{{ $wholesaler->id ?? __('N/A') }}"
                            data-tier="{{ $wholesaler->tier ?? __('N/A') }}">
                            {{ $wholesaler->wholesalerBusiness->company_name ?? '' }} ({{ $wholesaler->email }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block  text-md font-semibold text-gray-700 whitespace-nowrap">{{ translate('Wholesaler') }}</label>
                    <input type="text" id="ws-name" value="{{ old('wholesaler_name') }}" name="wholesaler_name"
                        class="w-full form-control " readonly>
                </div>
                <input type="hidden" id="ws-id" value="{{ old('wholesaler_id') }}" name="wholesaler_id"
                    class="w-full form-control shadow-sm" readonly>
                <div>
                    <label class="block text-md font-semibold text-gray-700 whitespace-nowrap">{{ translate('Wholesaler Tier') }}</label>
                    <input type="text" id="ws-tier" value="{{ old('wholesale_tier') }}" name="wholesale_tier"
                        class="w-full form-control" readonly>
                </div>
            </div>
            <div class="wholesale-builder-summary-grid">
                <div class="wholesale-builder-summary-card">
                    <span class="wholesale-builder-summary-card__label">{{ translate('Wholesaler') }}</span>
                    <span class="wholesale-builder-summary-card__value bidi-auto" id="builder-wholesaler-name">{{ translate('N/A') }}</span>
                </div>
                <div class="wholesale-builder-summary-card">
                    <span class="wholesale-builder-summary-card__label">{{ translate('Wholesaler Tier') }}</span>
                    <span class="wholesale-builder-summary-card__value bidi-auto" id="builder-wholesaler-tier">{{ translate('N/A') }}</span>
                </div>
                <div class="wholesale-builder-summary-card">
                    <span class="wholesale-builder-summary-card__label">{{ translate('Quotation No') }}</span>
                    <span class="wholesale-builder-summary-card__value bidi-auto" id="builder-quotation-number">--</span>
                </div>
            </div>
            </div>
        </div>


        <div class="card wholesale-builder-section mb-4">
            <div class="wholesale-builder-section__header">
                <div>
                    <h3 class="wholesale-builder-section__title">{{ translate('Products') }}</h3>
                    <p class="wholesale-builder-section__subtitle">{{ translate('Add_Product') }} {{ translate('and') }} {{ translate('review') }} {{ translate('final_price') }}</p>
                </div>
                <div class="crm-list-toolbar__summary">
                    <span class="crm-list-toolbar__chip">
                        <span class="crm-list-toolbar__chip-label">{{ translate('products_count') }}</span>
                        <span id="summary-product-count">0</span>
                    </span>
                </div>
            </div>
            <div class="wholesale-builder-section__body">
                <div class="mb-4 wholesale-builder-tools">
                    <div>
                        <button id="toggle_product_dropdown" type="button"
                            class="btn toggle-add-product text-indigo-600 hover:underline text-sm soft-hidden ">
                            {{ translate('Add_Product') }}
                        </button>
                        <div id="product_dropdown_wrapper" class="mt-2 hidden">
                            <select id="product_select" class="js-example-matcher w-64"
                                data-placeholder="{{ translate('Search and select a product') }}">
                                <option value="" disabled selected>{{ translate('Select Product') }}</option>
                                @foreach ($wholesaleProducts as $wholesale)

                                <option value="{{ $wholesale->id }}"
                                    data-product-id="{{ $wholesale->product_id }}"
                                    data-variation-type="{{ $wholesale->variation_type }}"
                                    data-name="{{ $wholesale->product->getTranslatedField('name') }}"
                                    data-tax="{{ $wholesale->tax ?? 0 }}"
                                    data-prices='@json($wholesale->price_list->map(fn($p) => [
                                 "tier" => $p->tier,
                                      "price" => $p->price_per_piece
                                             ]))'>
                                    {{ $wholesale->product->getTranslatedField('name') }} ({{ $wholesale->variation_type ?? translate('No Variation') }})
                                </option>

                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input id="datatableSearch_" type="search" class="border border-gray-300 px-3 py-2 rounded-md shadow-sm text-sm w-64"
                        placeholder="{{ translate('Search...') }}" aria-label="{{ translate('Search') }}">
                </div>
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start wholesale-builder-product-table">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th class="px-4 py-2 text-start">{{ translate('Product Name') }}</th>
                            <th class="px-4 py-2 text-start">{{ translate('Requested Qty') }}</th>
                            <th class="px-4 py-2 text-start">{{ translate('Base Price') }}</th>
                            <th class="px-4 py-2 text-start">{{ translate('Tax') }}</th>
                            <th class="px-4 py-2 text-start">{{ translate('Final Price') }}</th>
                            <th class="px-4 py-2 text-start">{{ translate('Action') }}</th>

                        </tr>
                    </thead>

                    <tbody id="product_table_body" class="bg-white divide-y divide-gray-200">
                        <tr id="no_product_row">
                            <td colspan="6" class="text-center text-muted p-10">{{ translate('No product selected') }}</td>
                        </tr>
                    </tbody>

                </table>
            </div>
        </div>
        <section id="toggleSection">
            <div class="card wholesale-builder-section">
                <div class="wholesale-builder-section__header">
                    <div>
                        <h3 class="wholesale-builder-section__title">{{ translate('Charges & Discounts') }}</h3>
                        <p class="wholesale-builder-section__subtitle">{{ translate('Charges') }} {{ translate('and') }} {{ translate('Discounts') }}</p>
                    </div>
                </div>
                <div class="wholesale-builder-section__body">
                <div class="flex gap-6">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ translate('Charges') }}</h3>
                        <div id="charges" class="space-y-4"></div>
                        <button type="button" class="btn js-add-charge text-indigo-600 hover:underline text-sm">{{ translate('Add Charge') }}</button>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ translate('Discounts') }}</h3>
                        <div id="discounts" class="space-y-4"></div>
                        <button type="button" class="btn js-add-discount text-indigo-600 hover:underline text-sm">{{ translate('Add Discount') }}</button>
                    </div>
                </div>

                <div class="flex gap-6 mt-6">
                    <div class="flex-1">
                        <label class="block text-lg font-semibold text-gray-800">{{ translate('Wholesaler Discount') }}</label>
                        <input type="text" id="wholesaler_discount" name="wholesaler_discount"
                            class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                        <input type="hidden" name="wholesaler_discount_amount" id="wholesaler_discount_amount">
                    </div>

                    <div class="flex-1">
                        <label for="final_price" class="block text-lg font-semibold text-gray-800">{{ translate('Total Final Price') }}</label>
                        <input type="text" id="final_price" name="final_price"
                            class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                    </div>
                </div>
                </div>
            </div>

            <div class="card wholesale-builder-section wholesale-builder-language-block mt-lg-5">
                <div class="wholesale-builder-section__header">
                    <div>
                        <h3 class="wholesale-builder-section__title">{{ translate('Localized Terms & Notes') }}</h3>
                        <p class="wholesale-builder-section__subtitle">{{ translate('Terms and Conditions') }} {{ translate('and') }} {{ translate('Note') }}</p>
                    </div>
                    @php
                    $activeLanguage = $defaultLanguage;
                    $_la = is_array($language ?? null) ? $language : (is_array($languages ?? null) ? $languages : []);
                    if (in_array(getDefaultLanguage(), $_la, true)) $activeLanguage = getDefaultLanguage();
                @endphp
<ul class="nav nav-tabs mb-4">
                        @foreach($language as $lang)
                        <li class="nav-item">
                            <a class="nav-link form-system-language-tab {{ $lang == $activeLanguage ? 'active' : '' }}"
                                href="javascript:" id="{{ $lang }}-link">
                                {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                            </a>
                         </li>
                         @endforeach
                     </ul>
                </div>
                <div class="wholesale-builder-section__body">
                    @foreach($language as $lang)
                    <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}"
                        id="{{ $lang }}-form">
                        <input type="hidden" name="lang[]" value="{{ $lang }}">

                        <label for="terms_and_conditions" class="block text-sm font-medium text-gray-700">{{ translate('Terms and Conditions') }}({{ strtoupper($lang) }})</label>
                        <textarea name="terms_and_conditions[]" id="terms_and_conditions"
                            class="form-control summernote w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm"></textarea>


                        <div class="mt-3">
                            <label for="note" class="block text-sm font-medium text-gray-700">{{ translate('Note') }}({{ strtoupper($lang) }})</label>
                            <textarea name="note[]" id="note"
                                class="summernote w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm"></textarea>

                        </div>

                    </div>
                    @endforeach

                </div>
            </div>

            <div class="card wholesale-builder-section">
                <div class="wholesale-builder-section__header">
                    <div>
                        <h3 class="wholesale-builder-section__title">{{ translate('Final Summary') }}</h3>
                        <p class="wholesale-builder-section__subtitle">{{ translate('Total Final Price') }}</p>
                    </div>
                </div>
                <div class="wholesale-builder-section__body">
                    <div class="wholesale-builder-summary-grid">
                        <div class="wholesale-builder-summary-card">
                            <span class="wholesale-builder-summary-card__label">{{ translate('products_count') }}</span>
                            <span class="wholesale-builder-summary-card__value" id="builder-product-count">0</span>
                        </div>
                        <div class="wholesale-builder-summary-card">
                            <span class="wholesale-builder-summary-card__label">{{ translate('Wholesaler discount amount') }}</span>
                            <span class="wholesale-builder-summary-card__value" id="builder-discount-amount">0.00</span>
                        </div>
                        <div class="wholesale-builder-summary-card">
                            <span class="wholesale-builder-summary-card__label">{{ translate('Total Final Price') }}</span>
                            <span class="wholesale-builder-summary-card__value" id="builder-final-total">0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wholesale-builder-sticky" id="sticky-submit-bar">
                <div class="wholesale-builder-sticky__meta">
                    <span class="crm-list-toolbar__chip">
                        <span class="crm-list-toolbar__chip-label">{{ translate('Wholesaler') }}</span>
                        <span class="bidi-auto" id="sticky-wholesaler-name">{{ translate('N/A') }}</span>
                    </span>
                    <span class="crm-list-toolbar__chip crm-list-toolbar__chip--muted">
                        <span class="crm-list-toolbar__chip-label">{{ translate('products_count') }}</span>
                        <span id="sticky-product-count">0</span>
                    </span>
                </div>
                <div class="wholesale-builder-sticky__actions">
                    <div class="wholesale-builder-sticky__total">
                        <span class="wholesale-builder-sticky__total-label">{{ translate('Total Final Price') }}</span>
                        <span class="wholesale-builder-sticky__total-value" id="sticky-final-total">0.00</span>
                    </div>
                    <button type="submit" id="submit_btn"
                        class="btn btn--primary px-4 py-2">
                        {{ translate('Submit') }}
                    </button>
                </div>
            </div>



        </section>
    </form>
</div>

@endsection

@push('script')

<script src="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('admin-views.wholesaler-business.partials._builder-js-config', [
    'mode' => 'create',
    'requireWholesalerSelection' => true,
    'loadDealIdFromQuery' => true,
    'summaryNumberTargetId' => 'builder-quotation-number',
])
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/wholesale-builder.js') }}"></script>
@endpush
