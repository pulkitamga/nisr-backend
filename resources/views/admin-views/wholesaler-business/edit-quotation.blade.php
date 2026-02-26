@extends('layouts.back-end.app')
@section('title', translate('Edit_Quotation'))
@push('css_or_js')
<!-- Summernote CSS -->
<link href="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>

@endpush
@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0] ?? 'en';
@endphp

<div class="content container-fluid">
          <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <h2 class="h1 mb-0 d-flex gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
            {{ translate('Edit Quotation') }}
        </h2>
    </div>
    <form action="{{ route('admin.wholesale.business.invoice.update', $order->id) }}" method="POST" id="quotation-form"
        class="space-y-6">
        @csrf
   <div class="card p-5">    
    <div class="flex space-x-6 mb-4">
    <!-- Purchase Order No -->
    <div class="flex-1">
        <label for="order_no" class="block text-md font-bold text-gray-700 mb-1">{{ translate('Purchase Order No') }}:</label>
        <input type="text" name="order_no" id="order_no_input" oninput="checkOrderNo(this.value)"
            class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm" placeholder="Enter Order No"
            value="{{ old('order_no', $order->purchase_order_no) }}" readonly>
    </div>

    <!-- Quotation No -->
    <div class="flex-1">
        <label for="order_no" class="block text-md font-bold text-gray-700 mb-1">{{ translate('Quotation No') }}:</label>
        <input type="text" name="order_no" id="order_no_input" oninput="checkOrderNo(this.value)"
            class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm" placeholder="Enter Order No"
            value="{{ old('order_no', $order->quotation_no) }}" readonly>
    </div>
    </div>

      <div class="grid grid-cols-2 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-md font-bold text-gray-700">{{ translate('Wholesaler') }}</label>
                    <input type="text" value="{{ $order->wholeseller->name ?? 'N/A' }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm" readonly>
                </div>

                <div>
                    <label class="block text-md font-bold text-gray-700">{{ translate('Wholesaler Tier') }}</label>
                    <input type="text" value="{{ $order->wholeseller->tier ?? 'N/A' }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm" readonly>
                </div>

            </div>

    </div>


        <form action="{{ route('admin.wholesale.business.orders.approve', $order->id) }}" method="POST"
            id="quotation-form" class="space-y-6">
            @csrf
     <div class="card py-3">
            <div class="card-body">
            <div class="mb-4 flex items-center justify-between">
                    <div>
                        <button id="toggle_product_dropdown" type="button"
                            class="text-indigo-600 hover:underline text-sm">
                            + {{ translate('Add_Product') }}
                        </button>
                        <div id="product_dropdown_wrapper" class="mt-2 hidden">
                            <select id="product_select" class="js-example-matcher w-64"
                                data-placeholder="Search and select a product">
                                <option value="" disabled selected>Select Product{{ translate('Requested Qty') }}</option>
                                @foreach ($wholesaleProducts as $wholesale)

                               <option value="{{ $wholesale->product_id }}|{{ $wholesale->variation_type ?? '' }}" 
                                        data-product-id="{{ $wholesale->product_id }}"
                                        data-variation="{{ $wholesale->variation_type ?? '' }}"
                                        data-name="{{ $wholesale->product->name }}"
                                        data-price="{{ optional($wholesale->price_list->first())->price_per_piece ?? 0 }}"
                                        data-tax="{{ $wholesale->product->tax_model == 'exclude' ? $wholesale->product->tax : 0 }}">
                                    {{ $wholesale->product->name }} ({{ $wholesale->variation_type ?? 'No Variation' }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input type="text" id="product_search_input" placeholder="Search product in table..."
                        class="border border-gray-300 px-3 py-2 rounded-md shadow-sm text-sm w-64" />
                </div>
                
                    <div class="overflow-x-auto rounded-lg border">
                <table class="table table-hover table-borderless table-thead-bordered min-w-full divide-y divide-gray-200 border rounded-lg overflow-hidden">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th class= "text-left">{{ translate('Product Name') }}</th>
                                <th class="">{{ translate('Requested Qty') }}</th>
                                <th class="">{{ translate('Base Price') }}</th>
                                <th class="">{{ translate('Tax') }}</th>
                                <th class="">{{ translate('Final Price') }}</th>
                                <th class="">{{ translate('Action') }}</th>
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
                {{ $item->product->name ?? 'N/A' }} ({{ $item->product_variation_type ?? 'No Variation' }})
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
                    value="{{ $item->tax }}%"
                    class="admin-tax border px-2 py-1 rounded w-24">
            </td>

            <td class="px-4 py-2">
                <input type="number" step="0.01"
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


            <!-- Charges and Discounts Section -->
        <div class="card p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-md font-bold text-gray-800 mb-2">{{ translate('Charges') }}</h3>
                <div id="charges" class="space-y-4">
                    @foreach ($existingCharges as $chargeIndex => $charge)
                    <div class="flex space-x-2 items-center mb-2">
                        <input type="text" name="charges[{{ $chargeIndex }}][name]" value="{{ $charge['label'] }}"
                            placeholder="Charge Name" class="flex-1 px-3 py-2 border rounded" />
                        <input type="number" name="charges[{{ $chargeIndex }}][value]" value="{{ $charge['amount'] }}"
                            placeholder="Value" class="px-3 py-2 border rounded" data-charge
                            oninput="updateFinalPrice()" />
                        <button type="button" onclick="this.parentElement.remove(); updateFinalPrice();"
                            class="btn btn-danger btn-sm square-btn">
                            <i class="tio-delete"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
                <button type="button" onclick="addCharge()" class="text-indigo-600 hover:underline text-sm">{{ translate('Add Charge') }}</button>
            </div>

            <div>
                <h3 class="text-md font-bold text-gray-800 mb-2">{{ translate('Discounts') }}</h3>
                <div id="discounts" class="space-y-4">
                    @foreach ($existingDiscounts as $discountIndex => $discount)
                    <div class="flex space-x-2 items-center mb-2">
                        <input type="text" name="discounts[{{ $discountIndex }}][name]" value="{{ $discount['label'] }}"
                            placeholder="Discount Name" class="flex-1 px-3 py-2 border rounded" />
                        <input type="number" name="discounts[{{ $discountIndex }}][value]"
                            value="{{ $discount['amount'] }}" placeholder="Value" class="px-3 py-2 border rounded"
                            data-discount oninput="updateFinalPrice()" />
                        <button type="button" onclick="this.parentElement.remove(); updateFinalPrice();"
                            class="btn btn-danger btn-sm square-btn">
                            <i class="tio-delete"></i>
                        </button>
                    </div>
                    @endforeach

                </div>
                <button type="button" onclick="addDiscount()" class="text-indigo-600 hover:underline text-sm">{{ translate('Add Discount') }}</button>
            </div>
            <div class="mt-2">
                <label class="text-md font-bold text-gray-800 mb-2">{{ translate('Wholesaler Discount') }}</label>
                <input type="text" id="wholesaler_discount" name="wholesaler_discount"
                    value="{{ $order->wholeseller->wholesaler_discount ?? 0 }}%" readonly
                    class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm bg-gray-100">
            </div>

            <input type="hidden" name="wholesaler_discount_amount" id="wholesaler_discount_amount">

            <!-- Final Price Box -->
            <div class="mt-2">
                <label for="final_price" class="text-md font-bold text-gray-800 mb-2">{{ translate('Total Final Price') }}</label>
                <input type="text" id="final_price" name="final_price"
                    class="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm bg-gray-100">
            </div>
            
         </div>   
        </div>
        <div class="card mt-lg-5">
                    <div class="card-header">
                        <ul class="nav nav-tabs mb-4">
                            @foreach($language as $lang)
                            <li class="nav-item">
                                <a class="nav-link form-system-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                                    href="javascript:" id="{{ $lang }}-link">
                                    {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <div class="card-body">
                         @foreach($language as $lang)
                <div class="form-system-language-form {{ $lang != $defaultLanguage ? 'd-none' : '' }}"
                    id="{{ $lang }}-form">
                    <input type="hidden" name="lang[]" value="{{ $lang }}"> <label for="terms_and_conditions" class="block text-sm font-medium text-gray-700 ">
                        {{ translate('Terms and Conditions') }}({{ strtoupper($lang) }})
                    </label>
                    <textarea name="terms_and_conditions[]" id="terms_and_conditions"
                        class="form-control summernote w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm">
              {{ getTranslation($order->translations, $lang, 'terms_and_conditions', $order->terms_and_conditions) }}      </textarea>
                     <label for="note" class="block text-sm font-medium text-gray-700 mt-4">
                  {{ translate('Note') }}({{ strtoupper($lang) }})
                </label>
                <textarea name="note[]" id="note"
                    class="summernote w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm">
              {{ getTranslation($order->translations, $lang, 'note', $order->note) }}
                </textarea>
                </div>
                @endforeach
            
            <!-- Actions -->
            <div class="flex justify-end mt-6">
                <button type="submit" id="submit_btn"
                    class="bg-green-600 text-white px-6 py-3 rounded-md shadow-md hover:bg-green-700">{{ translate('Update Quotation') }}</button>
            </div>
                    </div>
                    
                </div>
               
        </form>
        
</div>

@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
<script>
    let chargeIndex   = {{ count($existingCharges) }};
    let discountIndex = {{ count($existingDiscounts) }};

        const toggleBtn = document.getElementById('toggle_product_dropdown');
        const wrapper = document.getElementById('product_dropdown_wrapper');
        const productSelect = document.getElementById('product_select');
        const tableBody = document.getElementById('product_table_body');

        // Show or hide product dropdown on button click
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

        $(document).ready(function () {
            $('#product_select').select2({
                placeholder: 'Search for a product...',
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

        $(productSelect).on('select2:select', function (e) {
    const selectedOption = e.params.data;

    const [productId, variationTypeFromValue] = selectedOption.id.split('|');

    const $option = $(e.target).find('option[value="' + selectedOption.id + '"]');
    const productName = String($option.data('name')).trim();
    const variationType = String($option.data('variation') || variationTypeFromValue || '').trim().toLowerCase();

    const basePrice = parseFloat($option.data('price')) || 0;
    const productTax = parseFloat($option.data('tax')) || 0;

    const duplicate = Array.from(tableBody.querySelectorAll('tr')).some(row => {
        const rowProductId = String(row.getAttribute('data-product-id')).trim();
        const rowVariation = String(row.getAttribute('data-variation-type') || '').trim().toLowerCase();
        return rowProductId === productId && rowVariation === variationType;
    });

    console.log("Is Duplicate?", duplicate);
    if (duplicate) {
        alert("This product with this variation is already in the order.");
        return;
    }

    const row = document.createElement('tr');
    const displayVariation = variationType || 'No Variation';

    row.setAttribute('data-product-id', productId);
    row.setAttribute('data-variation-type', variationType);
    row.innerHTML = `
        <td class="px-4 py-2">${productName} (${displayVariation})</td>
        <td class="px-4 py-2"><input type="number" name="products[${productId}][${variationType}][approved_quantity]" value="1" class="admin-qty border px-2 py-1 rounded w-24"></td>
        <td class="px-4 py-2"><input type="number" name="products[${productId}][${variationType}][price]" value="${basePrice}" class="admin-price border px-2 py-1 rounded w-24"></td>
        <td class="px-4 py-2"><input type="text" name="products[${productId}][${variationType}][tax]" value="${productTax}%" class="admin-tax border px-2 py-1 rounded w-24"></td>
        <td class="px-4 py-2"><input type="number" step="0.01" name="products[${productId}][${variationType}][final_price]" value="${basePrice.toFixed(2)}" class="admin-final border px-2 py-1 rounded w-24"></td>
        <td class="px-4 py-2"><button type="button" class="remove-btn text-red-600 hover:underline">Remove</button></td>
    `;
    tableBody.appendChild(row);

    updateListeners();
    updateFinalPrice();
});





       

        $(document).on('ready', function () {
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

function checkOrderNo(value) {
    if (value.trim() === '') {
        setStatus('', '', false);
        return;
    }

    fetch(checkOrderNoUrl + '?order_no=' + encodeURIComponent(value))
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                setStatus('Order No already exists', 'red', true);
            } else {
                setStatus('Order No is available', 'green', false);
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

    



         function addCharge() {
        const container = document.getElementById('charges');
        const html = `
            <div class="flex gap-2 items-center mt-2">
                <input type="text" name="charges[${chargeIndex}][name]" placeholder="Charge Name" class="flex-1 px-3 py-2 border rounded" />
                <input type="number" name="charges[${chargeIndex}][value]" placeholder="Value" class="px-3 py-2 border rounded" data-charge oninput="updateFinalPrice()" />
                <button type="button" onclick="this.parentElement.remove(); updateFinalPrice();" class="btn btn-danger btn-sm square-btn">
                  <i class="tio-delete"></i>
                </button>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        chargeIndex++; // increment for next time
    }

    function addDiscount() {
        const container = document.getElementById('discounts');
        const html = `
            <div class="flex gap-2 items-center mt-2">
                <input type="text" name="discounts[${discountIndex}][name]" placeholder="Discount Name" class="flex-1 px-3 py-2 border rounded" />
                <input type="number" name="discounts[${discountIndex}][value]" placeholder="Value" class="px-3 py-2 border rounded" data-discount oninput="updateFinalPrice()" />
                <button type="button" onclick="this.parentElement.remove(); updateFinalPrice();" class="btn btn-danger btn-sm square-btn">
                  <i class="tio-delete"></i>
                </button>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        discountIndex++; // increment for next time
    }

</script>



@endpush
