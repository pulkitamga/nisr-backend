<div class="pos-product-item card action-select-product" data-id="{{ $product['id'] }}">
    <div class="pos-product-item_thumb position-relative">
        @if($product?->clearanceSale)
        <div class="position-absolute badge badge-soft-warning user-select-none m-2">
            {{ translate('Clearance_Sale') }}
        </div>
        @endif
        <img class="img-fit" src="{{ getStorageImages(path:$product->thumbnail_full_url, type: 'backend-product') }}"
            alt="{{ $product['name'] }}">
    </div>

    <div class="pos-product-item_content clickable">
        @php
            $resolvedBranchId = (int)($branch->id ?? 1);
        @endphp
        <div class="pos-product-item_title">
            {{ $product['name'] }}
        </div>
        <div class="pos-product-item_price">
            {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string', price: $product['unit_price'], from: 'panel') }}
        </div>
        <div class="pos-product-item_hover-content">
            <div class="d-flex flex-wrap gap-2">
                <span class="fz-22 text-capitalize">
                    @if ($product['product_type'] == 'physical')
                    @if ($resolvedBranchId === 1)
                    {{ $product['current_stock'] > 0 ? $product['current_stock'].' '.getUnitLabel($product['unit']) : translate('out_of_stock').'.' }}
                    @else
                    @php
                    $branchStock = \App\Models\ManageBranchProductStock::where('branch_id', $resolvedBranchId)
                    ->where('product_id', $product->id)
                    ->sum('current_stock');
                    @endphp
                    {{ $branchStock > 0 ? $branchStock.' '.getUnitLabel($product['unit']) : translate('out_of_stock').'.' }}
                    @endif
                    @else
                    {{ translate('click_for_details').'.' }}
                    @endif
                </span>

            </div>
        </div>
    </div>
</div>
