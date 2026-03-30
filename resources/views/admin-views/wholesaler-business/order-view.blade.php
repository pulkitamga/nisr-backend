@extends('layouts.back-end.app')
@section('title', translate('Purchase_order_quotation'))
@push('css_or_js')

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
<script src="https://cdn.tailwindcss.com"></script>

<div class="content container-fluid">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <h2 class="h1 mb-0 d-flex gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
            {{ translate('Order Request') }}
        </h2>
    </div>
    <form action="{{ route('admin.wholesale.business.orders.approve', $order->id) }}" method="POST" id="quotation-form"
        class="space-y-6">
        @csrf

        <div class="card p-4">
            <div class=" mb-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="order_no" class="block text-md font-bold text-gray-700 mb-1">{{ translate('Purchase Order No') }}:</label>

                    <input type="text" name="order_no" value="{{ $order->purchase_order_no }}" readonly
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="quotation_no" class="block text-md font-bold text-gray-700 mb-1">{{ translate('Quotation No') }}:</label>

                    <input type="text" name="quotation_no" id="quotation_no_input" oninput="checkQuotationNo(this.value)"
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm" placeholder="{{ translate('Enter Quotation No') }}" required>

                    <span id="order_no_status" class="text-sm"></span>
                </div>
            </div>

            <!-- Wholesaler Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-md font-bold text-gray-700 mb-1">{{ translate('Wholesaler') }}</label>
                    <input type="text" value="{{ $order->wholeseller->name ?? __('N/A') }}"
                        name="wholesaler_"
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm" readonly>
                </div>
                <div>
                    <label class="block text-md font-bold text-gray-700 mb-1">{{ translate('Wholesaler Tier') }}</label>
                    <input type="text" value="{{ $order->wholeseller->tier ?? __('N/A') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm" readonly>
                </div>
            </div>
        </div>
        <form action="{{ route('admin.wholesale.business.orders.approve', $order->id) }}" method="POST"
            id="quotation-form" class="space-y-6">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="mb-4 flex items-center justify-between px-1">
                        <div>
                            <button id="toggle_product_dropdown" type="button"
                                class="toggle-add-product text-indigo-600 hover:underline text-sm soft-hidden ">
                                + {{ translate('Add_Product') }}
                            </button>
                            <div id="product_dropdown_wrapper" class="mt-2 hidden">
                                <select id="product_select" class="js-example-matcher w-64"
                                    data-placeholder="{{ translate('Search and select a product') }}">
                                    <option value="" disabled selected>{{ translate('Select Product') }}</option>
                                    @foreach ($wholesaleProducts as $wholesale)

                                    <option value="{{ $wholesale->id }}"
                                        data-product-id="{{ $wholesale->product_id }}"
                                        data-variation-type="{{ $wholesale->variation_type }}" data-name="{{ $wholesale->product->getTranslatedField('name') }}"
                                        data-price="{{ optional($wholesale->price_list->first())->price_per_piece ?? 0 }}"
                                        data-tax="{{ $wholesale->product->tax_model == 'exclude' ? $wholesale->product->tax : 0 }}">
                                        {{ $wholesale->product->getTranslatedField('name') }} ({{ $wholesale->variation_type ?? translate('No Variation') }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <input id="datatableSearch_" type="search" class="border border-gray-300 px-3 py-2 rounded-md shadow-sm text-sm w-64"
                            placeholder="{{ translate('Search...') }}" aria-label="{{ translate('Search') }}">
                    </div>

                    <div class="overflow-x-auto rounded-lg border">
                        <table class="table table-hover table-borderless table-thead-bordered min-w-full divide-y divide-gray-200 border rounded-lg overflow-hidden">
                            <thead class="thead-light thead-50 text-capitalize">
                                <tr>
                                    <th class="px-4 py-2 text-start">{{ translate('Product Name') }}</th>
                                    <th class="px-2 py-2 text-start">{{ translate('Requested Qty') }}</th>
                                    <th class="px-4 py-2 text-start">{{ translate('Base Price') }}</th>
                                    <th class="px-4 py-2 text-start">{{ translate('Tax') }}</th>
                                    <th class="px-4 py-2 text-start">{{ translate('Final Price') }}</th>
                                    <th class="px-4 py-2 text-start">{{ translate('Action') }}</th>

                                </tr>
                            </thead>
                            <tbody id="product_table_body" class="bg-white divide-y divide-gray-200">
                                @foreach ($order->items as $item)

                                @php
                                $baseTotal = $item->base_price * $item->product_quantity;
                                $taxPercent = floatval(str_replace('%', '', $item->tax));
                                $taxAmount = ($baseTotal * $taxPercent) / 100;
                                $finalPrice = $baseTotal + $taxAmount;
                                @endphp
                                <tr data-product-id="{{ $item->product_id }}" data-variation-type="{{ $item->product_variation_type }}">
                                    <td class="px-4 py-2">
                                        {{ $item->product->getTranslatedField('name') }} ({{ $item->product_variation_type ?? translate('No Variation') }})
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="number"
                                            name="products[{{ $item->product_id }}][{{ $item->product_variation_type }}][approved_quantity]"
                                            value="{{ $item->product_quantity }}"
                                            class="admin-qty border px-2 py-1 rounded w-24">
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="number"
                                            name="products[{{ $item->product_id }}][{{ $item->product_variation_type }}][price]"
                                            value="{{ $item->base_price }}"
                                            class="admin-price border px-2 py-1 rounded w-24">
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="text"
                                            name="products[{{ $item->product_id }}][{{ $item->product_variation_type }}][tax]"
                                            value="{{ $item->tax }}"
                                            class="admin-tax border px-2 py-1 rounded w-24">
                                    </td>

                                    <td class="px-4 py-2">
                                        <input type="number"
                                            name="products[{{ $item->product_id }}][{{ $item->product_variation_type }}][final_price]"
                                            value="{{ number_format($finalPrice, 2, '.', '') }}"
                                            class="admin-final border px-2 py-1 rounded w-24">
                                    </td>

                                    <td class="px-4 py-2">
                                        <button type="button" onclick="removeProductRow(this)"
                                            class="text-red-600 hover:underline">{{ translate('Remove') }}</button>
                                    </td>
                                </tr>

                                @endforeach

                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

            <section id="toggleSection" class="fully-hidden">
                <div class="card p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Charges and Discounts Section -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ translate('Charges') }}</h3>
                            <div id="charges" class="space-y-4"></div>
                            <button type="button" onclick="addCharge()" class="text-indigo-600 hover:underline text-sm">+ {{ translate('Add Charge') }}</button>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-800  mb-4">{{ translate('Discounts') }}</h3>
                            <div id="discounts" class="space-y-4"></div>
                            <button type="button" onclick="addDiscount()" class="text-indigo-600 hover:underline text-sm">+ {{ translate('Add Discount') }}</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="mt-6">
                            <label class="block text-lg font-semibold text-gray-800">{{ translate('Wholesaler Discount') }}</label>
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

                <div class="card mt-lg-5">
                    <div class="card-header">
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

                    <div class="card-body">
                        @foreach($language as $lang)
                        <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}"
                            id="{{ $lang }}-form">
                            <input type="hidden" name="lang[]" value="{{ $lang }}">

                            <label for="terms_and_conditions" class="block text-sm font-medium text-gray-700">{{ translate('Terms and Conditions') }}({{ strtoupper($lang) }})</label>
                            <textarea name="terms_and_conditions[]" id="terms_and_conditions"
                                class="form-control summernote w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm"></textarea>

                            <label for="note" class="block text-sm font-medium text-gray-700 mt-3">{{ translate('Note') }}({{ strtoupper($lang) }})</label>
                            <textarea name="note[]" id="note"
                                class="summernote w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                        @endforeach


                        <!-- Actions -->
                        <div class="flex justify-end mt-6">
                            <button type="submit" id="submit_btn"
                                class="bg-green-600 text-white px-6 py-3 rounded-md shadow-md hover:bg-green-700">
                                {{ translate('Submit') }}</button>

                        </div>

                    </div>

                </div>


            </section>

        </form>
</div>

@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let chargeIndex = 0;
    let discountIndex = 0;
    const searchProductPlaceholder = @json(__('Search for a product...'));
    const removeLabel = @json(__('Remove'));
    const chargeNameLabel = @json(__('Charge Name'));
    const discountNameLabel = @json(__('Discount Name'));
    const valueLabel = @json(__('Value'));
    const chargeValueLabel = @json(__('Charge Value'));
    const discountValueLabel = @json(__('Discount Value'));
    const quotationExistsMessage = @json(__('Quotation No already exists'));
    const quotationAvailableMessage = @json(__('Quotation No is available'));

    const toggleBtn = document.getElementById('toggle_product_dropdown');
    const wrapper = document.getElementById('product_dropdown_wrapper');
    const productSelect = document.getElementById('product_select');
    const tableBody = document.getElementById('product_table_body');

    toggleBtn.addEventListener('click', () => {
        wrapper.classList.toggle('hidden');
    });

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
        let baseTotal = 0;

        // Step 1: Calculate total from all products
        document.querySelectorAll('.admin-final').forEach(input => {
            baseTotal += parseFloat(input.value) || 0;
        });

        // Step 2: Apply wholesaler discount (%)
        const wholesalerDiscountInput = document.getElementById('wholesaler_discount');
        let discountPercent = 0;
        if (wholesalerDiscountInput) {
            const discountText = wholesalerDiscountInput.value.trim();
            if (discountText.endsWith('%')) {
                discountPercent = parseFloat(discountText.replace('%', '')) || 0;
            } else {
                discountPercent = parseFloat(discountText) || 0;
            }
        }

        const discountAmount = baseTotal * (discountPercent / 100);
        document.getElementById('wholesaler_discount_amount').value = discountAmount.toFixed(2); // ✅ Set value here

        let finalTotal = baseTotal - discountAmount;

        // Step 3: Add charges
        document.querySelectorAll('[data-charge]').forEach(input => {
            finalTotal += parseFloat(input.value) || 0;
        });

        // Step 4: Subtract other discounts
        document.querySelectorAll('[data-discount]').forEach(input => {
            finalTotal -= parseFloat(input.value) || 0;
        });

        document.getElementById('final_price').value = finalTotal.toFixed(2);
    }



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

        // Init on page load
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

   $(productSelect).on('select2:select', function(e) {

    const selectedOption = e.params.data;
    const uniqueId = selectedOption.id;

    const $option = $(e.target).find('option[value="' + uniqueId + '"]');

    const productId = $option.data('product-id');
    const variationType = $option.data('variation-type');

    const productName = selectedOption.text;
    const basePrice = parseFloat($option.data('price')) || 0;
    const productTax = parseFloat($option.data('tax')) || 0;

    // Duplicate check
    const isDuplicate = [...tableBody.querySelectorAll('tr')].some(row =>
        row.getAttribute('data-product-id') == productId &&
        row.getAttribute('data-variation-type') == variationType
    );

    if (isDuplicate) {
        Swal.fire({
            icon: 'warning',
            title: @json(__('Oops...')),
            text: @json(__('This product variation is already added.')),
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    // CREATE ROW
    const row = document.createElement('tr');
    row.setAttribute('data-unique-id', uniqueId);
    row.setAttribute('data-product-id', productId);
    row.setAttribute('data-variation-type', variationType);

    row.innerHTML = `
        <td class="px-4 py-2">${productName}</td>

        <td class="px-4 py-2">
            <input type="number"
                   name="products[${productId}][${variationType}][approved_quantity]"
                   value="1"
                   class="admin-qty border px-2 py-1 rounded w-24">
        </td>

        <td class="px-4 py-2">
            <input type="number"
                   name="products[${productId}][${variationType}][price]"
                   value="${basePrice}"
                   class="admin-price border px-2 py-1 rounded w-24">
        </td>

        <td class="px-4 py-2">
            <input type="text"
                   name="products[${productId}][${variationType}][tax]"
                   value="${productTax}%"
                   class="admin-tax border px-2 py-1 rounded w-24">
        </td>

        <td class="px-4 py-2">
            <input type="number"
                   name="products[${productId}][${variationType}][final_price]"
                   value="${basePrice.toFixed(2)}"
                   class="admin-final border px-2 py-1 rounded w-24">
        </td>

        <td class="px-4 py-2">
            <button type="button" class="remove-btn text-red-600 hover:underline">
                ${removeLabel}
            </button>
        </td>
    `;

    tableBody.appendChild(row);
    updateListeners();
    updateFinalPrice();
});




    window.addCharge = function() {
        const container = document.createElement('div');
        container.classList.add('flex', 'gap-2', 'items-center', 'mt-2');
        container.innerHTML = `
    <input type="text" name="charges[${chargeIndex}][name]" placeholder="${chargeNameLabel}"
        class="flex-1 px-3 py-2 border rounded" />

    <input type="number" name="charges[${chargeIndex}][value]" placeholder="${valueLabel}"
        class="px-3 py-2 border rounded" data-charge data-fieldname="${chargeValueLabel}"
        oninput="updateFinalPrice()" required />

    <button type="button" onclick="this.parentElement.remove(); updateFinalPrice();"
        class="btn btn-danger btn-sm square-btn"><i class="tio-delete"></i></button>
`;

        document.getElementById('charges').appendChild(container);
        chargeIndex++;
    };

    window.addDiscount = function() {
        const container = document.createElement('div');
        container.classList.add('flex', 'gap-2', 'items-center', 'mt-2');
        container.innerHTML = `
    <input type="text" name="discounts[${discountIndex}][name]" placeholder="${discountNameLabel}"
        class="flex-1 px-3 py-2 border rounded" />

    <input type="number" name="discounts[${discountIndex}][value]" placeholder="${valueLabel}"
        class="px-3 py-2 border rounded" data-discount data-fieldname="${discountValueLabel}"
        oninput="updateFinalPrice()" required />

    <button type="button" onclick="this.parentElement.remove(); updateFinalPrice();"
        class="btn btn-danger btn-sm square-btn"><i class="tio-delete"></i></button>
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


    document.addEventListener("DOMContentLoaded", function() {
        const toggleBtn = document.getElementById("toggleSectionBtn");
        const section = document.getElementById("toggleSection");
        const addProductBtn = document.querySelector('.toggle-add-product');

        toggleBtn.addEventListener("click", function() {
            // Toggle section (completely show/hide)
            section.classList.toggle("fully-hidden");
            section.classList.toggle("fully-visible");
            addProductBtn.classList.toggle("soft-hidden");
            addProductBtn.classList.toggle("soft-visible");
        });
    });



    document.getElementById('datatableSearch_').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');

        rows.forEach(row => {
            // Convert all text inside the row to lowercase
            const rowText = row.textContent.toLowerCase();
            if (rowText.indexOf(query) > -1) {
                row.style.display = ''; // Show row
            } else {
                row.style.display = 'none'; // Hide row
            }
        });
    });


    document.getElementById('submit_btn').addEventListener('click', function(event) {
        event.preventDefault();

        const form = this.closest('form');
        // Required fields nikal lo
        const requiredFields = form.querySelectorAll('[required]');
        let emptyField = null;

        for (const field of requiredFields) {
            if (!field.value.trim()) {
                emptyField = field;
                break;
            }
        }

        if (emptyField) {
            Swal.fire({
                icon: 'warning',
                title: @json(__('Please fill all required fields')),
                text: `${@json(__('Field'))} "${emptyField.dataset.fieldname || emptyField.previousElementSibling?.innerText || emptyField.name}" ${@json(__('is required.'))}`,
            }).then(() => {
                emptyField.focus();
            });
            return; // stop here, no submit confirmation yet
        }

        Swal.fire({
            title: @json(__('Are you sure?')),
            text: @json(__('Do you want to submit?')),
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: @json(__('Submit')),
            cancelButtonText: @json(__('Cancel'))
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>



@endpush

