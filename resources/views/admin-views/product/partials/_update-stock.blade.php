<div class="pb-4">
    <a class="d-flex align-items-center"
        href="{{ route('admin.products.view', ['addedBy' => $product['added_by'] == 'seller' ? 'vendor' : 'in-house', 'id' => $product['id']]) }}">
        <div class="avatar rounded avatar-70 border">
            <img class="avatar-img"
                src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'backend-product') }}" alt="">
        </div>
        <div class="ms-3">
            <div class="d-block">
                <span class="line--limit-2 h5 text-hover-primary mb-2">
                    {{ $product['name'] }}
                </span>
            </div>
            <span class="d-block font-size-sm text-body">
                {{ translate('Price') }} :
                {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $product['unit_price']), currencyCode: getCurrencyCode()) }}
            </span>
        </div>
    </a>
</div>
<div class="card-body bg-soft-secondary rounded mb-4">
    <input name="product_id" value="{{ $product['id'] }}" class="d-none">
    <div id="quantity" class="mb-3">
        <label class="form-label text-dark">{{ translate('main_stock') }}</label>
        <input type="number" min="0" value={{ $product->current_stock }} step="1"
            placeholder="{{ translate('main_stock') }}" name="current_stock_products_show" class="form-control bg-white"
            readonly>
    </div>
    @if (!$product['variation'] || count(json_decode($product['variation'], true)) == 0)
        <div id="quantity" class="mb-3">
            <label class="form-label text-dark">{{ translate('adjust_stock') }}</label>
            <input type="number" step="1" value="0" name="stock_adjustment" class="form-control bg-white stock-adjustment-input"
                data-current-stock="{{ (int) $product->current_stock }}"
                placeholder="{{ translate('Enter_adjustment') }} (+/-)"
                required>
        </div>
        <div id="quantity" class="mb-3">
            <label class="form-label text-dark">{{ translate('new_stock') }}</label>
            <input type="number" min="0" step="1" value="{{ (int) $product->current_stock }}" name="current_stock"
                class="form-control bg-white" readonly required>
        </div>
    @endif
    @if (!$product['variation'] || count(json_decode($product['variation'], true)) == 0)
        <div class="mt-3">
            <label class="form-label text-dark">{{ translate('Reason') }}</label>
            <input type="text" name="stock_reason" class="form-control bg-white"
                placeholder="{{ translate('Reason_for_stock_adjustment') }}" required>
        </div>
    @endif
    @if ($product['variation'] && count(json_decode($product['variation'], true)) > 0)
        <div>
            <label class="form-label text-dark">{{ translate('Variations_Stock') }}</label>
            <div class="bg-white p-2 rounded">
                <div class="sku_combination" id="sku_combination">
                    @if ($restockId)
                        @include('admin-views.product.partials._edit-restock-combinations', [
                            'combinations' => json_decode($product['variation'], true),
                        ])
                        <input type="hidden" name="restock_id" id="" value="{{ $restockId }}">
                    @else
                        @include('admin-views.product.partials._edit-sku-combinations', [
                            'combinations' => json_decode($product['variation'], true),
                        ])
                    @endif
                </div>
            </div>
        </div>
    @endif
    @if ($product['variation'] && count(json_decode($product['variation'], true)) > 0)
        <div class="mt-3">
            <label class="form-label text-dark">
                {{ translate('Reason') }}
            </label>

            @foreach (json_decode($product['variation'], true) as $key => $variation)
                <div class="row align-items-center mb-2">

                    {{-- Attribute / Type --}}
                    <div class="col-md-2">
                        <small class="text-muted">
                            {{ $variation['type'] ?? 'Variation ' . ($key + 1) }}
                        </small>
                    </div>

                    {{-- Reason (same size as SKU / Price / Stock) --}}
                    <div class="col-md-3 col-lg-3">
                        <input type="text" name="variation_reason[{{ $variation['type'] ?? $key }}]"
                            class="form-control bg-white" placeholder="Reason"
                            value="{{ $variation['reason'] ?? '' }}">
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    @php($systemBranchId = 1)
    @php($allBranches = $branches ?? collect())
    <div class="mt-3">
        <label class="form-label text-dark">{{ translate('deduction_branch') }}</label>
        <select name="deduction_branch_id" class="form-control bg-white">
            <option value="">{{ translate('select_branch_for_stock_reduction_only') }}</option>
            @foreach ($allBranches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
            @endforeach
        </select>
        <small class="text-muted d-block mt-1">
            {{ translate('stock_additions_are_always_posted_to_system_branch') }}
            @php($systemBranchName = optional($allBranches->firstWhere('id', $systemBranchId))->branch_name ?? 'Branch #1')
            ({{ $systemBranchName }})
        </small>
    </div>
</div>

<script>
    $(document).off('input.stock-adjustment-simple').on('input.stock-adjustment-simple', '.stock-adjustment-input', function() {
        const current = parseInt($(this).data('current-stock')) || 0;
        const adjustment = parseInt($(this).val()) || 0;
        const newStock = Math.max(0, current + adjustment);

        if ((current + adjustment) < 0) {
            $(this).val(-current);
        }

        $('input[name="current_stock"]').val(newStock);
    });
</script>
