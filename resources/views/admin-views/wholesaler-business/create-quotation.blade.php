@extends('layouts.back-end.app')
@section('title', translate('Create Quotation'))
@push('css_or_js')
<script src="https://cdn.tailwindcss.com"></script>

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
        class="space-y-6">
        @csrf
        <input type="hidden" name="deal_id" id="deal_id_hidden" value="">
        <div class="card p-5">
            <div class="flex flex-wrap items-center gap-6 mb-4">
                <!-- Quotation No -->
                <div class="flex-1">
                    <label for="quotation_no" class="block text-md font-semibold text-gray-700 whitespace-nowrap">
                        {{ translate('Quotation No') }}:
                    </label>
                    <input type="text" name="quotation_no" id="quotation_no_input"
                        oninput="checkQuotationNo(this.value)"
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
                    <input type="text" id="ws-name" value="{{ $order->wholeseller->name ?? __('N/A') }}" name="wholesaler_name"
                        class="w-full form-control " readonly>
                </div>
                <input type="hidden" id="ws-id" value="{{ $order->wholeseller->id ?? '' }}" name="wholesaler_id"
                    class="w-full form-control shadow-sm" readonly>
                <div>
                    <label class="blocktext-md font-semibold text-gray-700 whitespace-nowrap">{{ translate('Wholesaler Tier') }}</label>
                    <input type="text" id="ws-tier" value="{{ $order->wholeseller->tier ?? __('N/A') }}" name="wholesale_tier"
                        class="w-full form-control" readonly>
                </div>
            </div>
        </div>


        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-4 mt-4 flex items-center justify-between">
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
                                    data-name="{{ $wholesale->product->name }}"
                                    data-tax="{{ $wholesale->product->tax_model == 'exclude' ? $wholesale->product->tax : 0 }}"
                                    data-prices='@json($wholesale->price_list->map(fn($p) => [
                                 "tier" => $p->tier,
                                      "price" => $p->price_per_piece
                                             ]))'>
                                    {{ $wholesale->product->name }} ({{ $wholesale->variation_type ?? translate('No Variation') }})
                                </option>

                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input id="datatableSearch_" type="search" class="border border-gray-300 px-3 py-2 rounded-md shadow-sm text-sm w-64"
                        placeholder="{{ translate('Search...') }}" aria-label="{{ translate('Search') }}">
                </div>
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th class="px-4 py-2 text-left">{{ translate('Product Name') }}</th>
                            <th class="px-4 py-2 text-left">{{ translate('Requested Qty') }}</th>
                            <th class="px-4 py-2 text-left">{{ translate('Base Price') }}</th>
                            <th class="px-4 py-2 text-left">{{ translate('Tax') }}</th>
                            <th class="px-4 py-2 text-left">{{ translate('Final Price') }}</th>
                            <th class="px-4 py-2 text-left">{{ translate('Action') }}</th>

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
        <section id="toggleSection" class="fully-hidden">
            <div class="card p-5">
                <div class="flex gap-6">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ translate('Charges') }}</h3>
                        <div id="charges" class="space-y-4"></div>
                        <button type="button" onclick="addCharge()" class=" btn text-indigo-600 hover:underline text-sm">{{ translate('Add Charge') }}</button>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ translate('Discounts') }}</h3>
                        <div id="discounts" class="space-y-4"></div>
                        <button type="button" onclick="addDiscount()" class="btn text-indigo-600 hover:underline text-sm">{{ translate('Add Discount') }}</button>
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

            <div class="card mt-lg-5">
                <div class="card-header">
                    @php($activeLanguage = in_array(getDefaultLanguage(), $language ?? $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage)
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
                <div class="card-body">
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

                    <div class="flex justify-end mt-6">

                        <button type="submit" id="submit_btn"
                            class="btn--primary text-white px-6 py-3 rounded-md shadow-md hover:bg-green-700">
                            {{ translate('Submit') }}
                        </button>
                    </div>
                </div>
            </div>



        </section>
    </form>
</div>
</div>
</div>

@endsection

@push('script')

<script src="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>
    let chargeIndex = 0;
    let discountIndex = 0;
    const searchProductPlaceholder = @json(__('Search for a product...'));
    const chargeNameLabel = @json(__('Charge Name'));
    const discountNameLabel = @json(__('Discount Name'));
    const valueLabel = @json(__('Value'));
    const quotationExistsMessage = @json(__('Quotation No already exists'));
    const quotationAvailableMessage = @json(__('Quotation No is available'));
    const selectWholesalerPlaceholder = @json(__('-- Select Wholesaler --'));
    const fallbackNotAvailable = @json(__('N/A'));

    const toggleBtn = document.getElementById('toggle_product_dropdown');
    const wrapper = document.getElementById('product_dropdown_wrapper');
    const productSelect = document.getElementById('product_select');
    const tableBody = document.getElementById('product_table_body');

    function recalculatePrice(e) {
        const row = e.target.closest('tr');
        const qty = parseFloat(row.querySelector('.admin-qty').value) || 0;
        const price = parseFloat(row.querySelector('.admin-price').value) || 0;

        let taxPercent = 0;
        const taxInput = row.querySelector('.admin-tax');
        if (taxInput) {
            const taxVal = taxInput.value.trim();
            if (taxVal.endsWith('%')) {
                taxPercent = parseFloat(taxVal.replace('%', '')) || 0;
            } else {
                taxPercent = parseFloat(taxVal) || 0;
            }
        }

        const total = qty * price;
        const taxAmount = total * (taxPercent / 100);
        const finalAmount = total + taxAmount;

        row.querySelector('.admin-final').value = finalAmount.toFixed(2);

        updateFinalPrice();
    }


    function updateListeners() {
        document.querySelectorAll('.admin-qty, .admin-price, .admin-tax').forEach(input => {
            input.removeEventListener('input', recalculatePrice);
            input.addEventListener('input', recalculatePrice);
        });

        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.removeEventListener('click', handleRemoveClick);
            btn.addEventListener('click', handleRemoveClick);
        });
    }

    function handleRemoveClick(e) {
        e.preventDefault();
        removeProductRow(e.target);
    }

    function updateFinalPrice() {
        const rows = document.querySelectorAll('#product_table_body tr');
        if (rows.length === 0) {
            document.getElementById('final_price').value = '0.00';
            return;
        }

        let baseTotal = 0;
        document.querySelectorAll('.admin-final').forEach(input => {
            baseTotal += parseFloat(input.value) || 0;
        });
        if (baseTotal <= 0) {
            document.getElementById('final_price').value = '0.00';
            document.getElementById('wholesaler_discount_amount').value = '0.00';
            return;
        }

        let discountInput = document.getElementById('wholesaler_discount').value || '0';
        let discountPercent = parseFloat(discountInput.replace('%', '').trim()) || 0;

        const discountAmount = baseTotal * (discountPercent / 100);
        document.getElementById('wholesaler_discount_amount').value = discountAmount.toFixed(2);

        let finalTotal = baseTotal - discountAmount;
        document.querySelectorAll('[data-charge]').forEach(input => {
            finalTotal += parseFloat(input.value) || 0;
        });

        document.querySelectorAll('[data-discount]').forEach(input => {
            finalTotal -= parseFloat(input.value) || 0;
        });

        document.getElementById('final_price').value = finalTotal.toFixed(2);

        console.log(finalTotal);

    }


    document.getElementById('wholesaler_discount').addEventListener('input', function() {
        updateFinalPrice();
    });


    function removeProductRow(button) {
        const row = button.closest('tr');
        const productId = row.getAttribute('data-product-id');
        row.remove();

        const hiddenInput = document.querySelector(`input[name="product_${productId}"]`);
        if (hiddenInput) hiddenInput.remove();

        updateFinalPrice();
    }

    $(document).ready(function() {
        $('#product_select').select2({
            placeholder: searchProductPlaceholder,
            matcher: matchCustom,
        });

        updateListeners();
        updateFinalPrice();
    });

    function matchCustom(params, data) {
        if ($.trim(params.term) === "") return data;
        if (typeof data.text === "undefined") return null;

        if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
            let modifiedData = $.extend({}, data, true);
            return modifiedData;
        }
        return null;
    }

    function updatePricesBasedOnWholesaler() {
        const tier = getSelectedWholesalerTier();

        document.querySelectorAll('tr[data-product-id]').forEach(row => {
            const productId = row.getAttribute('data-product-id');

            const option = document.querySelector(`#product_select option[value="${productId}"]`);
            const prices = option ? option.dataset.prices : null;
            let basePrice = 0;

            if (prices) {
                try {
                    const parsed = JSON.parse(prices);
                    const match = parsed.find(p => p.tier.toLowerCase() === tier.toLowerCase());
                    basePrice = match ? parseFloat(match.price) : 0;
                } catch (err) {
                    console.error("Invalid price JSON", prices);
                }
            }
            row.querySelector('input[name$="[price]"]').value = basePrice.toFixed(2);
            row.querySelector('input[name$="[final_price]"]').value = basePrice.toFixed(2);
        });

        updateFinalPrice();
    }


    function getSelectedWholesalerTier() {
        const select = document.getElementById('wholesaler-select');
        const selectedOption = select.options[select.selectedIndex];
        return selectedOption.getAttribute('data-tier')?.toLowerCase();
    }




    $('#toggle_product_dropdown').on('click', function() {
        const selectedWholesalerId = $('#wholesaler-select').val();
        if (!selectedWholesalerId) {
            Swal.fire({
                icon: 'warning',
                title: @json(__('Oops...')),
                text: @json(__('Please select a wholesaler first.')),
                confirmButtonColor: '#3085d6',
                confirmButtonText: @json(__('OK'))
            });
            return;
        }


        $('#product_dropdown_wrapper').toggleClass('hidden');
    });


    function checkEmptyTable() {
        const rows = tableBody.querySelectorAll('tr[data-product-id]');
        if (rows.length === 0) {
            tableBody.innerHTML = `
            <tr id="no_product_row">
                <td colspan="6" class="text-center text-muted p-10">{{ translate('No product selected') }}</td>
            </tr>
        `;
        }
    }



   $(productSelect).on('select2:select', function(e) {

    const selectedOption = $(e.target).find('option[value="' + e.params.data.id + '"]');
    const pricesRaw = selectedOption.attr('data-prices');
    const pricesJson = pricesRaw ? JSON.parse(pricesRaw) : [];
    const tier = getSelectedWholesalerTier(); 
    let basePrice = 0;

    if (Array.isArray(pricesJson)) {
        const matchedPrice = pricesJson.find(p => p.tier.toLowerCase() === tier.toLowerCase());
        basePrice = matchedPrice ? parseFloat(matchedPrice.price) : 0;
    }

    if (document.getElementById('no_product_row')) {
        document.getElementById('no_product_row').remove();
    }

    const productTax = parseFloat(selectedOption.data('tax')) || 0;
    const productId = e.params.data.id;
    const productName = e.params.data.text;
    const variationType = selectedOption.data('variation-type') || "";

    // 🔥 Duplicate check: product_id + variation_type
    if ([...tableBody.querySelectorAll('tr')].some(row =>
        row.getAttribute('data-product-id') === productId &&
        row.getAttribute('data-variation-type') === variationType
    )) {
        alert(@json(__('This variation is already added.')));
        return;
    }

    const row = document.createElement('tr');
    row.setAttribute('data-product-id', productId);
    row.setAttribute('data-variation-type', variationType);

    row.innerHTML = `
        <td class="px-4 py-2">
            ${productName}
            <input type="hidden" name="products[${productId}][product_id]" value="${productId}">
            <input type="hidden" name="products[${productId}][variation_type]" value="${variationType}">
        </td>
        <td class="px-4 py-2"><input type="number" name="products[${productId}][approved_quantity]" value="1" class="admin-qty border px-2 py-1 rounded w-24"></td>
        <td class="px-4 py-2"><input type="number" name="products[${productId}][price]" value="${basePrice}" class="admin-price border px-2 py-1 rounded w-24"></td>
        <td class="px-4 py-2"><input type="text" name="products[${productId}][tax]" value="${productTax}%" class="admin-tax border px-2 py-1 rounded w-24"></td>
        <td class="px-4 py-2"><input type="number" step="0.01" name="products[${productId}][final_price]" value="${basePrice.toFixed(2)}" class="admin-final border px-2 py-1 rounded w-24"></td>
        <td class="px-4 py-2"><button type="button" class="remove-btn btn btn-danger btn-sm square-btn"><i class="tio-delete"></i></button></td>
    `;

    tableBody.appendChild(row);
    updateListeners();
    updateFinalPrice();
});



    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-btn')) {
            e.target.closest('tr').remove();
            checkEmptyTable();
        }
    });

    window.addCharge = function() {
        const container = document.createElement('div');
        container.classList.add('flex', 'gap-2', 'items-center', 'mt-2');
        container.innerHTML = `
            <input type="text" name="charges[${chargeIndex}][name]" placeholder="${chargeNameLabel}" class="flex-1 px-3 py-2 border rounded" />
            <input type="number" name="charges[${chargeIndex}][value]" placeholder="${valueLabel}" class=" px-3 py-2 border rounded" data-charge oninput="updateFinalPrice()" />
            <button type="button" onclick="this.parentElement.remove(); updateFinalPrice();" class="btn btn-danger btn-sm square-btn"> <i class="tio-delete"></i></button>
        `;
        document.getElementById('charges').appendChild(container);
        chargeIndex++;
    };

    window.addDiscount = function() {
        const container = document.createElement('div');
        container.classList.add('flex', 'gap-2', 'items-center', 'mt-2');
        container.innerHTML = `
            <input type="text" name="discounts[${discountIndex}][name]" placeholder="${discountNameLabel}" class="flex-1 px-3 py-2 border rounded" />
            <input type="number" name="discounts[${discountIndex}][value]" placeholder="${valueLabel}" class=" px-3 py-2 border rounded" data-discount oninput="updateFinalPrice()" />
            <button type="button" onclick="this.parentElement.remove(); updateFinalPrice();" class="btn btn-danger btn-sm square-btn"> <i class="tio-delete"></i></button>
        `;
        document.getElementById('discounts').appendChild(container);
        discountIndex++;
    };

    $(document).on('ready', function() {
        $('.summernote').summernote({
            height: 150,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']]
            ]
        });
    });

    const checkOrderNoUrl = "{{ route('admin.wholesale.business.check-order-no') }}";

    function checkQuotationNo(value) {
        if (value.trim() === '') {
            setStatus('', '', false);
            return;
        }

        fetch(checkOrderNoUrl + '?order_no=' + encodeURIComponent(value))
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    setStatus(quotationExistsMessage, 'red', true);
                } else {
                    setStatus(quotationAvailableMessage, 'green', false);
                }
            })
            .catch(err => {
                console.error("Fetch error:", err);
            });
    }

    function setStatus(message, color, disable) {
        const status = document.getElementById('order_no_status');
        const input = document.getElementById('order_no_input');
        const button = document.getElementById('submit_btn');

        status.innerText = message;
        status.style.color = color;
        input.style.borderColor = color;

        if (button) {
            button.disabled = disable;
        }
    }


    $(document).ready(function() {
        $('#wholesaler-select').select2({
            placeholder: selectWholesalerPlaceholder,
            allowClear: true,
            width: 'resolve'
        });

        $('#wholesaler-select').on('change', function() {
            const selected = $(this).find(':selected');

            const name = selected.data('name') || fallbackNotAvailable;
            const phone = selected.data('phone') || fallbackNotAvailable;
            const email = selected.data('email') || fallbackNotAvailable;
            const tier = selected.data('tier') || fallbackNotAvailable;
            const id = selected.data('id') || fallbackNotAvailable;
            const discount = selected.data('wholesalediscount') || '0';

            $('#ws-name').val(name);
            $('#ws-tier').val(tier);
            $('#ws-id').val(id);
            $('#wholesaler_discount').val(discount + '%');
            if (typeof updatePricesBasedOnWholesaler === 'function') {
                updatePricesBasedOnWholesaler();
            }
        });
    });



    document.getElementById('datatableSearch_').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');

        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            if (rowText.indexOf(query) > -1) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const dealId = urlParams.get('deal_id');
        if (dealId) {
            $('#deal_id_hidden').val(dealId);
        }
    });
</script>



@endpush


