@extends('layouts.front-end.app')

@section('title', $baseProduct->name)

@push('css_or_js')
@include(VIEW_FILE_NAMES['product_seo_meta_content_partials'], ['metaContentData' => $baseProduct?->seoInfo, 'productDetails' => $baseProduct])
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/product-details.css') }}" />
<style>
    /* Variation buttons styling */
    .variation-buttons-container {
        margin: 20px 0;
    }
    
    .variation-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }
    
    .variation-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    
    .variation-btn {
        padding: 2px 2px;
        border: 2px solid #e7fcff;
        background: #f8f9fa;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
        min-width: 100px;
        font-weight: 400;
    }
    
    .variation-btn:hover {
        border-color: #239e92;
        background: #e7f1ff;
    }
    
    .variation-btn.active {
        border-color: #239e92;
        background-color: rgba(40, 167, 69, 0.1);
        color: #239e92;
        font-weight: bold;
    }
    
    /* Product details section */
    .product-details-section {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        background: #fff;
    }
    
    .product-price {
        font-size: 24px;
        font-weight: bold;
        color: #239e92;
        margin: 10px 0;
    }
    
    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }
    
    .quantity-input {
        width: 80px;
        height: 40px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }
    
    .add-to-cart-btn {
        background: #239e92;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 5px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .add-to-cart-btn:disabled {
        background: #cccccc;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
@include('layouts.front-end.partials._store-header')

<section>
    <div class="container py-4">
        <div class="card">
            <div class="card-body">
                <div class="row {{Session::get('direction') === "rtl" ? '__dir-rtl' : ''}}">
                    
                    <!-- Product Images -->
                    <div class="col-md-4">
                        <div class="cz-product-gallery">
                            <div class="cz-preview">
                                <div id="sync1" class="owl-carousel owl-theme product-thumbnail-slider">
                                    @for ($i = 0; $i < 3; $i++)
                                        <div class="product-preview-item d-flex align-items-center justify-content-center {{ $i == 0 ? 'active' : '' }}" id="image{{ $i }}">
                                            <img class="cz-image-zoom img-responsive w-100"
                                                src="{{ getStorageImages(path: $baseProduct->thumbnail_full_url, type: 'product') }}"
                                                data-zoom="{{ getStorageImages(path: $baseProduct->thumbnail_full_url, type: 'product') }}"
                                                alt="{{ $baseProduct->name }}">
                                            <div class="cz-image-zoom-pane"></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Details and Variations -->
                    <div class="col-lg-8 col-md-8 mt-md-0 mt-sm-3 web-direction">
                        <div class="details product-cart-option-container">
                            <h2 class="mb-2 __inline-24">{{ $baseProduct->name }}</h2>
                            <p class="text-muted mb-4">{{ $baseProduct->description }}</p>
                            
                            @if(auth()->guard('customer')->check() && auth()->guard('customer')->user()->user_type == 1)
                            
                            <!-- Variation Buttons -->
                            <div class="variation-buttons-container">
                                <div class="variation-title">{{ translate('variation') }} :</div>
                                <div class="variation-buttons" id="variation-buttons">
                                    @foreach($variationsWithRanges as $index => $variationData)
                                        @php
                                            $wp = $variationData['wholesaleProduct'];
                                            $range = $variationData['filteredRange'];
                                            $resolvedVariationKey = $wp->resolved_variation_key ?? $wp->variation_key ?? '__default__';
                                            $displayText = $wp->resolved_variation_display ?? $wp->variation_type ?? 'Default';
                                            $cleanVariant = $wp->resolved_variation_type ?? $displayText;
                                        @endphp
                                        <button type="button" 
                                                class="variation-btn {{ $index == 0 ? 'active' : '' }}" 
                                                data-index="{{ $index }}"
                                                data-variation-key="{{ $resolvedVariationKey }}"
                                                data-display-text="{{ $displayText }}"
                                                data-clean-variant="{{ $cleanVariant }}"
                                                data-price="{{ $range->price_per_piece }}"
                                                data-display-price="{{ webCurrencyConverterOnlyDigit($range->price_per_piece) }}"
                                                data-min-qty="{{ $moqOverride ? 1 : $range->min_qty }}"
                                                data-max-qty="{{ $range->max_qty }}"
                                                data-range-id="{{ $range->id }}"
                                                data-discount="{{ $range->discount ?? 0 }}"
                                                onclick="selectVariation(this)">
                                            {{ $displayText }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Product Details -->
                            <div class="product-details-section">
                                <!-- Price Display -->
                                <div class="product-price">
                                    @php
                                        $firstVariation = $variationsWithRanges[0] ?? null;
                                        $firstRange = $firstVariation['filteredRange'] ?? null;
                                        $firstWp = $firstVariation['wholesaleProduct'] ?? null;
                                        $firstCleanVariant = $firstWp ? ($firstWp->resolved_variation_display ?? $firstWp->variation_type ?? '') : '';
                                        $firstResolvedVariationKey = $firstWp ? ($firstWp->resolved_variation_key ?? $firstWp->variation_key ?? '__default__') : '__default__';
                                        $initialQuantity = $moqOverride ? 1 : ($firstRange ? $firstRange->min_qty : 1);
                                    @endphp
                                    
                                    @if($firstRange)
                                        <span id="current-price">{{ webCurrencyConverter($firstRange->price_per_piece) }}</span>
                                        <span class="text-muted">/ piece</span>
                                    @endif
                                </div>
                                
                                <!-- Variation Info -->
                                <div class="mt-2 mb-3">
                                    <strong>Selected Variation:</strong>
                                    <span id="selected-variation-text">
                                        @if($firstCleanVariant)
                                            {{ $firstCleanVariant }}
                                        @endif
                                    </span>
                                </div>
                                
                                <!-- MOQ Information -->
                                <div class="mb-3">
                                    @if($firstRange ?? null)
                                        <strong>MOQ:</strong>
                                        <span id="moq-info">
                                            Min: {{ $moqOverride ? 1 : $firstRange->min_qty }} pcs
                                            @if($firstRange->max_qty)
                                                | Max: {{ $firstRange->max_qty }} pcs
                                            @endif
                                        </span>
                                    @endif
                                </div>
                                
                                <!-- Quantity Selection -->
                                <div class="mb-4">
                                    <div class="mb-2"><strong>Quantity:</strong></div>
                                    <div class="quantity-controls">
                                        <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity(-1)">-</button>
                                        <input type="number" 
                                               id="quantity-input"
                                               class="quantity-input"
                                               value="{{ $moqOverride ? 1 : ($firstRange->min_qty ?? 1) }}"
                                               min="{{ $moqOverride ? 1 : ($firstRange->min_qty ?? 1) }}"
                                               max="{{ $firstRange->max_qty ?? '' }}">
                                        <button type="button" class="btn btn-outline-secondary" onclick="changeQuantity(1)">+</button>
                                    </div>
                                </div>
                                
                                <!-- Total Price -->
                                <div class="mb-4">
                                    <div><strong>Total Price:</strong></div>
                                    <div class="product-price" id="total-price">
                                        @if($firstRange ?? null)
                                            {{ setCurrencySymbol(amount: webCurrencyConverterOnlyDigit($firstRange->price_per_piece * $initialQuantity), currencyCode: getCurrencyCode(type: 'web'), type: 'web') }}
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- <!-- Add to Cart Form -->
                                <form action="{{ route('web.addwholesale') }}" method="POST" id="add-to-cart-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $baseProduct->id }}" id="product-id">
                                    <input type="hidden" name="seller_id" value="{{ $baseProduct->added_by == 'admin' ? 1 : $baseProduct->user_id }}">
                                    <input type="hidden" name="name" value="{{ $baseProduct->name }}">
                                    
                                    <!-- VARIANT: Full variation key for processing -->
                                    <input type="hidden" name="variant" value="{{ $firstWp->variation_key ?? '' }}" id="variant-input">
                                    
                                    <!-- Clean variant for display -->
                                    <input type="hidden" name="clean_variant" value="{{ $firstCleanVariant }}" id="clean-variant-input">
                                    
                                    <input type="hidden" name="tax" value="{{ $baseProduct->tax }}">
                                    <input type="hidden" name="tax_model" value="{{ $baseProduct->tax_model }}">
                                    <input type="hidden" name="thumbnail" value="{{ getStorageImages(path: $baseProduct->thumbnail_full_url, type: 'product') }}">
                                    <input type="hidden" name="price" value="{{ $firstRange->price_per_piece ?? 0 }}" id="price-input">
                                    <input type="hidden" value="{{ webCurrencyConverterOnlyDigit($firstRange ? $firstRange->price_per_piece : 0) }}" id="display-price-input">
                                    <input type="hidden" name="discount" value="{{ getProductPriceByType(product: $baseProduct, type: 'discount', result: 'value') }}">
                                    <input type="hidden" name="shipping_cost" value="0">
                                    <input type="hidden" name="price_range_id" value="{{ $firstRange->id ?? 0 }}" id="price-range-id">
                                    <input type="hidden" name="quantity" value="{{ $moqOverride ? 1 : ($firstRange->min_qty ?? 1) }}" id="quantity-hidden">
                                    
                                    <button type="submit" class="add-to-cart-btn" id="add-to-cart-btn">
                                        <i class="fas fa-cart-plus"></i> Add to Purchase Order
                                    </button>
                                </form> --}}
                                <!-- Add to Cart Form -->
                                <form action="{{ route('web.addwholesale') }}" method="POST" id="add-to-cart-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $baseProduct->id }}" id="product-id">
                                    <input type="hidden" name="seller_id" value="{{ $baseProduct->added_by == 'admin' ? 1 : $baseProduct->user_id }}">
                                    <input type="hidden" name="name" value="{{ $baseProduct->name }}">
                                    
                                    <!-- VARIANT: Full variation key for processing -->
                                    <input type="hidden" name="variant" value="{{ $firstResolvedVariationKey }}" id="variant-input">
                                    
                                    <input type="hidden" name="tax" value="{{ $baseProduct->tax }}">
                                    <input type="hidden" name="tax_model" value="{{ $baseProduct->tax_model }}">
                                    <input type="hidden" name="thumbnail" value="{{ getStorageImages(path: $baseProduct->thumbnail_full_url, type: 'product') }}">
                                    <input type="hidden" name="price" value="{{ $firstRange->price_per_piece ?? 0 }}" id="price-input">
                                    <input type="hidden" name="discount" value="{{ getProductPriceByType(product: $baseProduct, type: 'discount', result: 'value') }}">
                                    <input type="hidden" name="shipping_cost" value="0">
                                    <input type="hidden" name="price_range_id" value="{{ $firstRange->id ?? 0 }}" id="price-range-id">
                                    <input type="hidden" name="quantity" value="{{ $moqOverride ? 1 : ($firstRange->min_qty ?? 1) }}" id="quantity-hidden">
                                    
                                    <button type="submit" class="add-to-cart-btn" id="add-to-cart-btn">
                                        <i class="fas fa-cart-plus"></i> Add to Purchase Order
                                    </button>
                                </form>
                            </div>
                            
                            @else
                            <div class="alert alert-warning">
                                Wholesaler access required.
                            </div>
                            @endif
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('script')
<script>
    // Current selected variation index
    let currentVariationIndex = 0;
    const webCurrencySymbol = @json(getCurrencySymbol(type: 'web'));
    const webCurrencyPosition = @json(getWebConfig('currency_symbol_position') ?? 'left');
    const webCurrencySpaceEnabled = @json((string)(getWebConfig('currency_symbol_space') ?? '0') === '1');
    const webCurrencyDecimals = Number(@json((int)(getWebConfig('decimal_point_settings') ?? 2)));
    
// Select variation function
function selectVariation(button) {
    // Remove active class from all buttons
    document.querySelectorAll('.variation-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked button
    button.classList.add('active');
    
    // Get variation data from button
    currentVariationIndex = button.getAttribute('data-index');
    const variationKey = button.getAttribute('data-variation-key');
    const displayText = button.getAttribute('data-display-text');
    const price = button.getAttribute('data-price');
    const displayPrice = button.getAttribute('data-display-price');
    const minQty = button.getAttribute('data-min-qty');
    const maxQty = button.getAttribute('data-max-qty');
    const rangeId = button.getAttribute('data-range-id');
    
    // Update display
    document.getElementById('selected-variation-text').textContent = displayText;
    
    // Update price display
    const priceElement = document.getElementById('current-price');
    priceElement.textContent = formatPrice(displayPrice);
    
    // Update MOQ info
    const moqInfo = document.getElementById('moq-info');
    moqInfo.textContent = `Min: ${minQty} pcs${maxQty ? ` | Max: ${maxQty} pcs` : ''}`;
    
    // Update quantity input
    const quantityInput = document.getElementById('quantity-input');
    const quantityHidden = document.getElementById('quantity-hidden');
    quantityInput.min = minQty;
    quantityInput.value = minQty;
    quantityHidden.value = minQty;
    
    if (maxQty) {
        quantityInput.max = maxQty;
    } else {
        quantityInput.removeAttribute('max');
    }
    
    // Update hidden form fields
    document.getElementById('variant-input').value = variationKey; // Full variation key
    document.getElementById('price-input').value = price;
    document.getElementById('display-price-input').value = displayPrice;
    document.getElementById('price-range-id').value = rangeId;
    
    // Calculate and update total price
    calculateTotalPrice();
}
    
    // Change quantity function
    function changeQuantity(change) {
        const quantityInput = document.getElementById('quantity-input');
        const quantityHidden = document.getElementById('quantity-hidden');
        let currentValue = parseInt(quantityInput.value) || parseInt(quantityInput.min);
        const min = parseInt(quantityInput.min) || 1;
        const max = quantityInput.max ? parseInt(quantityInput.max) : null;
        
        let newValue = currentValue + change;
        
        if (newValue < min) {
            newValue = min;
        }
        
        if (max && newValue > max) {
            newValue = max;
        }
        
        quantityInput.value = newValue;
        quantityHidden.value = newValue;
        
        calculateTotalPrice();
    }
    
    // Calculate total price function
    function calculateTotalPrice() {
        const quantityInput = document.getElementById('quantity-input');
        const displayPriceInput = document.getElementById('display-price-input');
        const totalPriceElement = document.getElementById('total-price');
        
        const quantity = parseInt(quantityInput.value) || parseInt(quantityInput.min);
        const price = parseFloat(displayPriceInput.value) || 0;
        const total = quantity * price;
        
        totalPriceElement.textContent = formatPrice(total);
    }
    
    // Format price function
    function formatPrice(price) {
        const amount = Number.parseFloat(price) || 0;
        const decimalPlaces = Number.isFinite(webCurrencyDecimals) ? webCurrencyDecimals : 2;
        const formattedAmount = amount
            .toFixed(decimalPlaces)
            .replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        const space = webCurrencySpaceEnabled ? ' ' : '';

        if (webCurrencyPosition === 'right') {
            return `${formattedAmount}${space}${webCurrencySymbol}`;
        }

        return `${webCurrencySymbol}${space}${formattedAmount}`;
    }
    
    // Quantity input change event
    document.getElementById('quantity-input').addEventListener('input', function() {
        const quantityHidden = document.getElementById('quantity-hidden');
        const min = parseInt(this.min) || 1;
        const max = this.max ? parseInt(this.max) : null;
        let value = parseInt(this.value) || min;
        
        if (value < min) {
            value = min;
            this.value = min;
        }
        
        if (max && value > max) {
            value = max;
            this.value = max;
        }
        
        quantityHidden.value = value;
        calculateTotalPrice();
    });
    
    // Form submission validation
    document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
        const variantInput = document.getElementById('variant-input');
        const quantityInput = document.getElementById('quantity-input');
        
        if (!variantInput.value || variantInput.value.trim() === '') {
            e.preventDefault();
            Toastr.error('Please select a variation');
            return false;
        }
        
        if (!quantityInput.value || quantityInput.value < 1) {
            e.preventDefault();
            Toastr.error('Please enter a valid quantity');
            return false;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('add-to-cart-btn');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        submitBtn.disabled = true;
    });
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        calculateTotalPrice();
        
        // Set initial values if not set
        const variantInput = document.getElementById('variant-input');
        if (!variantInput.value) {
            const firstBtn = document.querySelector('.variation-btn.active');
            if (firstBtn) {
                const variationKey = firstBtn.getAttribute('data-variation-key');
                const cleanVariant = firstBtn.getAttribute('data-clean-variant');
                variantInput.value = variationKey;
                const cleanVariantInput = document.getElementById('clean-variant-input');
                if (cleanVariantInput) {
                    cleanVariantInput.value = cleanVariant;
                }
            }
        }
    });
</script>
@endpush
