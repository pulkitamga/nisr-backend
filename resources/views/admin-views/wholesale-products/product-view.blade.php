@extends('layouts.back-end.app')

@section('title', translate('View'))

@section('content')

<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" class="mb-1" alt="">
            {{translate('View_Product')}}
        </h2>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card mt-3 rest-part">
                <div class="card-body">
                    <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 ps-4">
                        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/business-setup.png')}}" class="mb-1" alt="">
                        {{ translate('Product_Information') }}
                    </h5>
                    <div class="col-md-12">
                        <div class="col-sm-12 col-xxl-8">
                            <div class="pair-list">
                                <div>
                                    <span class="key text-nowrap">{{ translate('product_Name') }}</span>
                                    <span>:</span>
                                    <span class="value">{{ $ProductData->product->getTranslatedField('name') ?? __('N/A') }}</span>
                                </div>
                                <div>
                                    <span class="key">{{ translate('Category') }}</span>
                                    <span>:</span>
                                    <span class="value">{{ $ProductData->category->getTranslatedField('name') ?? __('N/A') }}</span>
                                </div>
                                <div>
                                    <span class="key">{{ translate('Sub_Category') }}</span>
                                    <span>:</span>
                                    <span class="value">{{ $ProductData->subcategory->getTranslatedField('name') ?? __('N/A') }}</span>
                                </div>

                                <!-- YEH PART REPLACE KAR DO – AB VARIATION KEY BHI DIKHEGA -->
                                <div>
                                    <span class="key">{{ translate('variation') }}</span>
                                    <span>:</span>
                                    <span class="value">
                                        @if($ProductData->variation_type && $ProductData->variation_key)
                                        <strong>{{ str_replace('|', ' • ', $ProductData->variation_key) }}</strong>
                                        <small class="text-muted d-block">({{ translate('Type') }}: {{ $ProductData->variation_type }})</small>
                                        @elseif($ProductData->variation_type)
                                        <span class="text-warning">{{ $ProductData->variation_type }}</span>
                                        <small class="text-muted d-block">({{ translate('Key not available') }})</small>
                                        @else
                                        <span class="badge badge-soft-success">{{ translate('Default') }}</span>
                                        @endif
                                    </span>
                                </div>
                                <!-- END -->

                                <div>
                                    <span class="key">{{ translate('Unit') }}</span>
                                    <span>:</span>
                                    <span class="value">{{ $ProductData->product->unit ? getUnitLabel($ProductData->product->unit) : __('N/A') }}</span>
                                </div>
                                <div>
                                    <span class="key">{{ translate('Unit_price') }}</span>
                                    <span>:</span>
                                    <span class="value">
                                        {{ setCurrencySymbol(
                        amount: usdToDefaultCurrency(amount: $ProductData->product->getVariationPrice($ProductData->variation_type, $ProductData->variation_key) ?? $ProductData->product->unit_price),
                        currencyCode: getCurrencyCode()
                    ) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-3 rest-part">
                <div class="card-body">
                    <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 ps-4">
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
                                        <th>{{ translate('Wholesale_Price') }}</th>
                                        <th>{{ translate('Discount') }} (%)</th>

                                    </tr>
                                </thead>
                                <tbody id="range-rows">
                                    @php $i = 1; @endphp
                                    @foreach($ProductData->price_list as $price)
                                    <tr>
                                        <td class="text-center">{{ $i++ }}.</td>
                                        <td>{{ ucfirst($price->tier) ?? '-' }}</td>
                                        <td>{{ $price->min_qty ?? '-' }}</td>
                                        <td>{{ $price->max_qty ?? '-' }}</td>
                                        <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $price->price_per_piece), currencyCode: getCurrencyCode()) }}</td>
                                        <td>{{ $price->discount ?? 0 }}%</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
