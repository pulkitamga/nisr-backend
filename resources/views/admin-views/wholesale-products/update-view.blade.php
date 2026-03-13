@extends('layouts.back-end.app')

@section('title', translate('Update'))

@section('content')
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" class="mb-1" alt="">
            {{translate('Update_Product')}}
        </h2>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <form class="user" action="{{route('admin.wholesale.product.update',$ProductData->id)}}" method="post" id="update-product-form">
                @csrf
                <input type="hidden" name="primary_id" value="{{ $ProductData->id }}">

                <!-- Product Information -->
                <div class="card mt-3 rest-part">
                    <div class="card-body">
                        <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" class="mb-1" alt="">
                            {{ translate('Product_Information') }}
                        </h5>

                        <div class="row">
                            <!-- Category -->
                            <div class="col-sm-6 col-lg-4 col-xl-2">
                                <div class="form-group">
                                    <label class="title-color">{{ translate('category') }}</label>
                                    <input type="text" class="form-control" value="{{ $ProductData->category->name }}" readonly>
                                    <input type="hidden" name="category_id" value="{{ $ProductData->category_id }}">
                                </div>
                            </div>

                            <!-- Sub Category -->
                            <div class="col-sm-6 col-lg-4 col-xl-2">
                                <div class="form-group">
                                    <label class="title-color">{{ translate('sub_Category') }}</label>
                                    <input type="text" class="form-control" value="{{ $get_sub_category->name ?? '-' }}" readonly>
                                    <input type="hidden" name="sub_category_id" value="{{ $ProductData->sub_category_id }}">
                                </div>
                            </div>

                            <!-- Product -->
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="form-group">
                                    <label class="title-color">{{ translate('Products') }}</label>
                                    <input type="text" class="form-control" value="{{ $get_product->name }}" readonly>
                                    <input type="hidden" name="product_id" value="{{ $ProductData->product_id }}">
                                </div>
                            </div>

                            <!-- Variation (New - Replaced Attribute) -->
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="form-group">
                                    <label class="title-color">{{ translate('Variation') }}</label>
                                    <input type="text" class="form-control"
                                        value="{{ $ProductData->variation_type 
                                               ? $ProductData->variation_type . 
                                                 ($ProductData->variation_key 
                                                   ? ' → (' . str_replace([':', '|'], [' : ', ' • '], $ProductData->variation_key) . ')'
                                                   : '')
                                               : 'Default' }}"
                                        readonly>
                                    <input type="hidden" name="variation_type" value="{{ $ProductData->variation_type }}">
                                    <input type="hidden" name="variation_key" value="{{ $ProductData->variation_key }}">
                                </div>
                            </div>

                            <!-- Unit Price (Dynamic - Variation ya Default) -->
                            <div class="col-sm-6 col-lg-4 col-xl-2">
                                <div class="form-group">
                                    <label class="title-color">{{ translate('Unit_Price') }}</label>
                                    <input type="number" step="0.01" class="form-control font-weight-bold text-primary"
                                        value="{{ $ProductData->product->getVariationPrice($ProductData->variation_type, $ProductData->variation_key) }}"
                                        readonly id="base-unit-price">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wholesale Prices -->
                <div class="card mt-3 rest-part">
                    <div class="card-body">
                        <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" class="mb-1" alt="">
                            {{ translate('wholesale_Prices') }}
                        </h5>

                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-hover table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                                    <thead class="thead-light thead-50 text-capitalize">
                                        <tr>
                                            <th class="text-center">{{ translate('SL') }}</th>
                                            <th>{{ translate('Tier') }}</th>
                                            <th>{{ translate('Min_Quantity') }}</th>
                                            <th>{{ translate('Max_Quantity') }}</th>
                                            <th>{{ translate('Unit_Price') }}</th>
                                            <th>{{ translate('Discount') }} (%)</th>
                                            <th>{{ translate('Price_per_piece') }}</th>
                                            <th class="text-center">{{ translate('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="range-rows">
                                        @php $i = 1; $j = 0; @endphp
                                        @forelse(($sortedPriceList ?? $ProductData->price_list) as $price)
                                        @php $row_id = $j++; @endphp
                                        <tr class="range-row" data-row-id="{{ $row_id }}">
                                            <td class="text-center row-number">{{ $i++ }}.</td>
                                            <td>
                                                <input type="text" class="form-control" name="tier[]"
                                                    value="{{ $price->tier }}" readonly>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="min_qty[]"
                                                    value="{{ $price->min_qty }}" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="max_qty[]"
                                                    value="{{ $price->max_qty ?? '' }}">
                                            </td>
                                            <td>
                                              <input type="number" step="0.01" class="form-control unit-price-input"
       value="{{ number_format($ProductData->product->getVariationPrice($ProductData->variation_type, $ProductData->variation_key), 2, '.', '') }}"
       readonly>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control discount-input"
                                                    name="discount[]" value="{{ $price->discount ?? 0 }}" min="0" max="100">
                                            </td>
                                            <td>
                                              @php
    $unitPrice = $ProductData->product->getVariationPrice($ProductData->variation_type, $ProductData->variation_key);
    $finalPrice = $unitPrice - ($unitPrice * ($price->discount ?? 0) / 100);
@endphp
<input type="number" step="0.01" class="form-control final-price-input"
       name="final_price[]"
       value="{{ number_format($finalPrice, 2, '.', '') }}"
       readonly>
                                            </td>
                                            <td class="text-center">
                                                @if($i > 2)
                                                <button type="button" class="btn btn-danger remove-product-btn">
                                                    {{ translate('Remove') }}
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">{{ translate('No price tiers found') }}</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <div class="d-flex justify-content-start">
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-md btn-primary" id="add-price-range">
                                            {{ translate('Add_Price_Range') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="row justify-content-end gap-3 mt-3 mx-1">
                                    <input type="hidden" name="from_submit" value="admin">
                                    <button type="reset" class="btn btn-secondary reset-button">{{ translate('reset') }}</button>
                                    <button type="submit" class="btn btn--primary btn-user">
                                        {{ translate('submit') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    window.remainingTiers = @json($remainingTiers);

    const BASE_UNIT_PRICE = parseFloat({{ $ProductData->product->getVariationPrice($ProductData->variation_type, $ProductData->variation_key) }});

        document.addEventListener('DOMContentLoaded', function () {
            const okButtonText = @json(__('OK'));
        const minQtyPlaceholder = @json(__('Min qty'));
        const maxQtyPlaceholder = @json(__('Max qty'));
        const removeButtonText = @json(translate('Remove'));
        const allKnownTierNames = new Set([
            ...Array.from(document.querySelectorAll('#range-rows input[name="tier[]"]')).map(input => input.value),
            ...(window.remainingTiers || []).map(tier => tier.name)
        ]);

        function calculateFinalPrice(row) {
            const discount = parseFloat(row.querySelector('.discount-input')?.value || 0);
            const finalPrice = BASE_UNIT_PRICE - (BASE_UNIT_PRICE * discount / 100);
            const finalInput = row.querySelector('.final-price-input');
            if (finalInput) finalInput.value = finalPrice.toFixed(2);
        }

        function updateRowNumbers() {
            document.querySelectorAll('#range-rows .row-number').forEach((el, idx) => {
                el.textContent = (idx + 1) + '.';
            });
        }

        // Run on all existing rows
        document.querySelectorAll('#range-rows .range-row').forEach(row => {
            calculateFinalPrice(row);
        });

        // Real-time discount update
        document.addEventListener('input', function (e) {
            if (e.target && e.target.classList.contains('discount-input')) {
                calculateFinalPrice(e.target.closest('tr'));
            }
        });

        document.getElementById('add-price-range')?.addEventListener('click', function () {
            if (!window.remainingTiers || window.remainingTiers.length === 0) {
                Swal.fire({
                    title: '{{ translate('No tier available!') }}',
                    text: '{{ translate('There are no more tiers to add.') }}',
                    icon: 'info',
                    confirmButtonText: okButtonText
                });
                return;
            }

            const rows = document.querySelectorAll('#range-rows tr.range-row');
            const newIndex = rows.length + 1;
            const nextTier = window.remainingTiers.shift();

            const newRow = document.createElement('tr');
            newRow.className = 'range-row';
            newRow.dataset.isNew = '1';
            newRow.innerHTML = `
                <td class="text-center row-number">${newIndex}.</td>
                <td><input type="text" class="form-control" name="tier[]" value="${nextTier.name}" readonly></td>
                <td><input type="number" class="form-control" name="min_qty[]" placeholder="${minQtyPlaceholder}" required min="1"></td>
                <td><input type="number" class="form-control" name="max_qty[]" placeholder="${maxQtyPlaceholder}"></td>
                <td><input type="number" step="0.01" class="form-control unit-price-input" value="${BASE_UNIT_PRICE.toFixed(2)}" readonly></td>
                <td><input type="number" step="0.01" class="form-control discount-input" name="discount[]" value="0" min="0" max="100"></td>
                <td><input type="number" step="0.01" class="form-control final-price-input" name="final_price[]" value="${BASE_UNIT_PRICE.toFixed(2)}" readonly></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger remove-product-btn">${removeButtonText}</button>
                </td>
            `;
            document.getElementById('range-rows').appendChild(newRow);
        });

        // Remove row + update serial numbers
        document.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-product-btn')) {
                const row = e.target.closest('tr');
                const tierInput = row?.querySelector('input[name="tier[]"]');
                const tierName = tierInput?.value?.trim();

                if (tierName && allKnownTierNames.has(tierName) && !(window.remainingTiers || []).some(tier => tier.name === tierName)) {
                    window.remainingTiers = [...(window.remainingTiers || []), { name: tierName }];
                }

                row?.remove();
                updateRowNumbers();
            }
        });
    });
</script>
@endsection
