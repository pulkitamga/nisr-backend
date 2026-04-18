@extends('layouts.back-end.app')
@section('title', translate('Purchase_order_quotation'))
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
            {{ translate('Order Request') }}
        </h2>
    </div>
    <form action="{{ route('admin.wholesale.business.orders.approve', $order->id) }}" method="POST" id="quotation-form"
        class="wholesale-builder-shell">
        @csrf

        <div class="card wholesale-builder-section">
            <div class="wholesale-builder-section__header">
                <div>
                    <h3 class="wholesale-builder-section__title">{{ translate('Quotation setup') }}</h3>
                    <p class="wholesale-builder-section__subtitle">{{ translate('Order Request') }} {{ translate('_and') }} {{ translate('Quotation_No') }}</p>
                </div>
                <div class="crm-list-toolbar__summary">
                    <span class="crm-list-toolbar__chip">
                        <span class="crm-list-toolbar__chip-label">{{ translate('Wholesaler') }}</span>
                        <span id="summary-selected-wholesaler" class="bidi-auto">{{ $order->wholeseller->name ?? translate('N/A') }}</span>
                    </span>
                    <span class="crm-list-toolbar__chip crm-list-toolbar__chip--muted">
                        <span class="crm-list-toolbar__chip-label">{{ translate('Wholesaler Tier') }}</span>
                        <span id="summary-selected-tier" class="bidi-auto">{{ $order->wholeseller->tier ?? translate('N/A') }}</span>
                    </span>
                </div>
            </div>
            <div class="wholesale-builder-section__body">
            <div class=" mb-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="order_no" class="block text-md font-bold text-gray-700 mb-1">{{ translate('Purchase_Order_No') }}:</label>

                    <input type="text" name="order_no" value="{{ $order->purchase_order_no }}" readonly
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="quotation_no" class="block text-md font-bold text-gray-700 mb-1">{{ translate('Quotation_No') }}:</label>

                    <input type="text" name="quotation_no" id="quotation_no_input"
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm" placeholder="{{ translate('Enter Quotation No') }}" required>

                    <span id="order_no_status" class="text-sm"></span>
                </div>
            </div>

            <!-- Wholesaler Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-md font-bold text-gray-700 mb-1">{{ translate('Wholesaler') }}</label>
                    <input type="text" value="{{ $order->wholeseller->name ?? translate('N/A') }}"
                        name="wholesaler_"
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm" readonly id="ws-name">
                </div>
                <div>
                    <label class="block text-md font-bold text-gray-700 mb-1">{{ translate('Wholesaler Tier') }}</label>
                    <input type="text" value="{{ $order->wholeseller->tier ?? translate('N/A') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm" readonly id="ws-tier">
                </div>
            </div>
            <div class="wholesale-builder-summary-grid mt-4">
                <div class="wholesale-builder-summary-card">
                    <span class="wholesale-builder-summary-card__label">{{ translate('Wholesaler') }}</span>
                    <span class="wholesale-builder-summary-card__value bidi-auto" id="builder-wholesaler-name">{{ $order->wholeseller->name ?? translate('N/A') }}</span>
                </div>
                <div class="wholesale-builder-summary-card">
                    <span class="wholesale-builder-summary-card__label">{{ translate('Wholesaler Tier') }}</span>
                    <span class="wholesale-builder-summary-card__value bidi-auto" id="builder-wholesaler-tier">{{ $order->wholeseller->tier ?? translate('N/A') }}</span>
                </div>
                <div class="wholesale-builder-summary-card">
                    <span class="wholesale-builder-summary-card__label">{{ translate('Purchase_Order_No') }}</span>
                    <span class="wholesale-builder-summary-card__value bidi-auto" id="builder-order-number">{{ $order->purchase_order_no ?? '--' }}</span>
                </div>
            </div>
            </div>
        </div>
            <div class="card wholesale-builder-section">
                <div class="wholesale-builder-section__header">
                    <div>
                        <h3 class="wholesale-builder-section__title">{{ translate('Products') }}</h3>
                        <p class="wholesale-builder-section__subtitle">{{ translate('Add_Product') }} {{ translate('_and') }} {{ translate('Review') }} {{ translate('final_price') }}</p>
                    </div>
                    <div class="crm-list-toolbar__summary">
                        <span class="crm-list-toolbar__chip">
                            <span class="crm-list-toolbar__chip-label">{{ translate('products_count') }}</span>
                            <span id="summary-product-count">{{ $order->items->count() }}</span>
                        </span>
                    </div>
                </div>
                <div class="wholesale-builder-section__body">
                    <div class="mb-4 wholesale-builder-tools">
                        <div>
                            <button id="toggle_product_dropdown" type="button"
                                class="toggle-add-product text-indigo-600 hover:underline text-sm soft-hidden ">
                                + {{ translate('Add_Product') }}
                            </button>
                            <div id="product_dropdown_wrapper" class="mt-2 hidden">
                                <select id="product_select" class="js-example-matcher w-64"
                                    data-placeholder="{{ translate('Search and select a product') }}">
                                    <option value="" disabled selected>{{ translate('select_Product') }}</option>
                                    @foreach ($wholesaleProducts as $wholesale)

                                    <option value="{{ $wholesale->id }}"
                                        data-product-id="{{ $wholesale->product_id }}"
                                        data-variation-type="{{ $wholesale->variation_type }}" data-name="{{ $wholesale->product->getTranslatedField('name') }}"
                                        data-price="{{ optional($wholesale->price_list->first())->price_per_piece ?? 0 }}"
                                        data-tax="{{ $wholesale->tax ?? 0 }}">
                                        {{ $wholesale->product->getTranslatedField('name') }} ({{ $wholesale->variation_type ?? translate('no_variation') }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <input id="datatableSearch_" type="search" class="border border-gray-300 px-3 py-2 rounded-md shadow-sm text-sm w-64"
                            placeholder="{{ translate('Search...') }}" aria-label="{{ translate('Search') }}">
                    </div>

                    <div class="overflow-x-auto rounded-lg border">
                        <table class="table table-hover table-borderless table-thead-bordered min-w-full divide-y divide-gray-200 border rounded-lg overflow-hidden wholesale-builder-product-table">
                            <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th class="px-4 py-2 text-start">{{ translate('Product_name') }}</th>
                                    <th class="px-2 py-2 text-start">{{ translate('requested_qty') }}</th>
                                    <th class="px-4 py-2 text-start">{{ translate('Base Price') }}</th>
                                    <th class="px-4 py-2 text-start">{{ translate('Tax') }}</th>
                                    <th class="px-4 py-2 text-start">{{ translate('Final_price') }}</th>
                                    <th class="px-4 py-2 text-start">{{ translate('Action') }}</th>

                                </tr>
                            </thead>
                            <tbody id="product_table_body" class="bg-white divide-y divide-gray-200">
                                @foreach ($order->items as $item)

                                @php
                                    $linePricing = \App\Support\WholesaleLinePrice::fromValues(
                                        basePrice: $item->base_price,
                                        quantity: $item->product_quantity,
                                        tax: $item->tax,
                                        storedFinalPrice: $item->final_price
                                    );
                                @endphp
                                <tr data-product-id="{{ $item->product_id }}" data-variation-type="{{ $item->product_variation_type }}">
                                    <td class="px-4 py-2">
                                        {{ $item->product->getTranslatedField('name') }} ({{ $item->product_variation_type ?? translate('no_variation') }})
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="number"
                                            name="products[{{ $item->product_id }}][{{ $item->product_variation_type }}][approved_quantity]"
                                            value="{{ $item->product_quantity }}"
                                            min="1"
                                            step="1"
                                            class="admin-qty border px-2 py-1 rounded w-24">
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="number"
                                            name="products[{{ $item->product_id }}][{{ $item->product_variation_type }}][price]"
                                            value="{{ $item->base_price }}"
                                            min="0"
                                            step="0.01"
                                            class="admin-price border px-2 py-1 rounded w-24">
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="text"
                                            name="products[{{ $item->product_id }}][{{ $item->product_variation_type }}][tax]"
                                            value="{{ $linePricing['display_tax'] }}"
                                            data-tax-mode="{{ $linePricing['tax_mode'] }}"
                                            class="admin-tax border px-2 py-1 rounded w-24">
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="number"
                                            name="products[{{ $item->product_id }}][{{ $item->product_variation_type }}][final_price]"
                                            value="{{ number_format($linePricing['final_price'], 2, '.', '') }}"
                                            min="0"
                                            step="0.01"
                                            class="admin-final border px-2 py-1 rounded w-24">
                                    </td>

                                    <td class="px-4 py-2">
                                        <button type="button"
                                            class="remove-btn js-remove-product text-red-600 hover:underline">{{ translate('Remove') }}</button>
                                    </td>
                                </tr>

                                @endforeach

                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

            <section id="toggleSection">
                <div class="card wholesale-builder-section">
                    <div class="wholesale-builder-section__header">
                        <div>
                            <h3 class="wholesale-builder-section__title">{{ translate('Charges & Discounts') }}</h3>
                            <p class="wholesale-builder-section__subtitle">{{ translate('Charges') }} {{ translate('_and') }} {{ translate('Discounts') }}</p>
                        </div>
                    </div>
                    <div class="wholesale-builder-section__body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Charges and Discounts Section -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ translate('Charges') }}</h3>
                            <div id="charges" class="space-y-4"></div>
                            <button type="button" class="text-indigo-600 hover:underline text-sm js-add-charge">+ {{ translate('Add Charge') }}</button>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-800  mb-4">{{ translate('Discounts') }}</h3>
                            <div id="discounts" class="space-y-4"></div>
                            <button type="button" class="text-indigo-600 hover:underline text-sm js-add-discount">+ {{ translate('Add Discount') }}</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="mt-6">
                            <label class="block text-lg font-semibold text-gray-800">{{ translate('wholesaler_discount') }}</label>
                            <input type="text" id="wholesaler_discount" name="wholesaler_discount"
                                value="{{ $order->wholeseller->wholesaler_discount ?? 0 }}%" readonly
                                class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm bg-gray-100">
                        </div>

                        <input type="hidden" name="wholesaler_discount_amount" id="wholesaler_discount_amount">

                        <div class="mt-6">
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
                            <p class="wholesale-builder-section__subtitle">{{ translate('Terms_and_Conditions') }} {{ translate('_and') }} {{ translate('Note') }}</p>
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

                            <label for="terms_and_conditions" class="block text-sm font-medium text-gray-700">{{ translate('Terms_and_Conditions') }}({{ strtoupper($lang) }})</label>
                            <textarea name="terms_and_conditions[]" id="terms_and_conditions"
                                class="form-control summernote w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm"></textarea>

                            <label for="note" class="block text-sm font-medium text-gray-700 mt-3">{{ translate('Note') }}({{ strtoupper($lang) }})</label>
                            <textarea name="note[]" id="note"
                                class="summernote w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm"></textarea>
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
                                <span class="wholesale-builder-summary-card__value" id="builder-product-count">{{ $order->items->count() }}</span>
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
                            <span class="bidi-auto" id="sticky-wholesaler-name">{{ $order->wholeseller->name ?? translate('N/A') }}</span>
                        </span>
                        <span class="crm-list-toolbar__chip crm-list-toolbar__chip--muted">
                            <span class="crm-list-toolbar__chip-label">{{ translate('products_count') }}</span>
                            <span id="sticky-product-count">{{ $order->items->count() }}</span>
                        </span>
                    </div>
                    <div class="wholesale-builder-sticky__actions">
                        <div class="wholesale-builder-sticky__total">
                            <span class="wholesale-builder-sticky__total-label">{{ translate('Total Final Price') }}</span>
                            <span class="wholesale-builder-sticky__total-value" id="sticky-final-total">0.00</span>
                        </div>
                        <button type="submit" id="submit_btn"
                            class="btn btn--primary px-4 py-2">
                            {{ translate('Submit') }}</button>
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
    'mode' => 'order',
    'requireWholesalerSelection' => false,
    'loadDealIdFromQuery' => false,
    'summaryNumberTargetId' => null,
])
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/wholesale-builder.js') }}"></script>
@endpush
