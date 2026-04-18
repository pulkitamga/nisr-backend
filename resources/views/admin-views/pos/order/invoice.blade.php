<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/pos-invoice.css') }}">
<?php
$orderTotalPriceSummary = \App\Utils\OrderManager::getOrderTotalPriceSummary(order: $order);
$receiptDirection = session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr');
?>
<div class="width-363px pos-receipt" dir="{{ $receiptDirection }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <div class="text-center pt-4 mb-3">
        <h2 class="line-height-1">{{ getWebConfig('company_name') }}</h2>
        <h5 class="line-height-1 font-size-16px">
            {{ translate('Phone') }} :
            <bdi dir="ltr">{{ formatPhoneForDisplay(getWebConfig('company_phone')) }}</bdi>
        </h5>
    </div>

    <span class="dashed-hr"></span>
    <div class="row mt-3 receipt-meta">
        <div class="col-6">
            <h5>{{ translate('Order_ID') }} : <bdi dir="ltr">{{ $order['id'] }}</bdi></h5>
        </div>
        <div class="col-6">
            <h5 class="receipt-meta-date">
                <bdi dir="ltr">{{ \Carbon\Carbon::parse($order['created_at'])->locale(app()->getLocale())->translatedFormat('d/M/Y h:i a') }}</bdi>
            </h5>
        </div>
        @if($order->customer)
        <div class="col-12">
            <h5>{{ translate('Customer_Name') }} : {{$order->customer['f_name'].' '.$order->customer['l_name']}}</h5>
            @if ($order->customer->id !=0)
            <h5>{{ translate('Phone') }} : <bdi dir="ltr">{{ formatPhoneForDisplay($order->customer['phone']) }}</bdi></h5>
            @endif

        </div>
        @endif
        @if($branch)
        <div class="col-12">
            <h5 class="text-capitalize">
                {{ translate('Branch') }}: {{ $branch->branch_name }}
            </h5>
        </div>
        @endif
    </div>
    <h5 class="text-uppercase"></h5>
    <span class="dashed-hr"></span>
    <table class="table table-bordered mt-3 width-99 receipt-table">
        <thead>
            <tr>
                <th class="text-center text-uppercase">{{ translate('QTY') }}</th>
                <th class="receipt-cell-start text-uppercase">{{ translate('Description') }}</th>
                <th class="text-center">{{ translate('Price') }}</th>
            </tr>
        </thead>



        <tbody>
            @php($sub_total=0)
            @php($total_tax=0)
            @php($total_dis_on_pro=0)
            @php($product_price=0)
            @php($total_product_price=0)
            @php($ext_discount=0)
            @php($coupon_discount=0)
            @foreach($order->details as $detail)
            @if($detail->product)
            <tr>
                <td class="receipt-cell-start">
                    <bdi dir="ltr">{{$detail['qty']}}</bdi>
                </td>
                <td class="receipt-cell-start">
                    <span> {{ Str::limit($detail->product['name'], 200) }}</span><br>
                    @php($variationData = collect(json_decode($detail['variation'], true) ?? [])->filter(fn($variationValue) => !blank($variationValue)))
                    @if($detail->product->product_type == 'physical' && $variationData->count() > 0)
                    <strong><u>{{ translate('Variation') }} : </u></strong>
                    @foreach($variationData as $key1 =>$variation)
                    <div class="font-size-sm text-body color-black">
                        <span>{{ translate(\Illuminate\Support\Str::snake((string) $key1)) }} : </span>
                        <span class="font-weight-bold">
                            @php($normalizedVariation = \Illuminate\Support\Str::lower(trim((string) $variation)))
                            @if(in_array($normalizedVariation, ['left', 'right'], true))
                                {{ translate($normalizedVariation) }}
                            @else
                                <bdi dir="ltr">{{$variation}}</bdi>
                            @endif
                        </span>
                    </div>
                    @endforeach
                    @endif

                    {{ translate('Discount') }}
                    : <bdi dir="ltr">{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $detail['discount']), currencyCode: getCurrencyCode()) }}</bdi>
                </td>
                <td class="receipt-cell-end">
                    @php($amount=($detail['price']*$detail['qty'])-$detail['discount'])
                    @php($product_price = $detail['price']*$detail['qty'])
                    <bdi dir="ltr">{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $amount), currencyCode: getCurrencyCode()) }}</bdi>
                </td>
            </tr>
            @php($sub_total+=$amount)
            @php($total_product_price+=$product_price)
            @php($total_tax+=$detail['tax'])

            @endif
            @endforeach
        </tbody>
    </table>
    <span class="dashed-hr"></span>

    <table class="w-100 color-black">
        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end">{{ translate('items_Price') }}:</td>
            <td class="receipt-cell-end"><bdi dir="ltr">{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $orderTotalPriceSummary['itemPrice']), currencyCode: getCurrencyCode()) }}</bdi></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end">{{ translate('Item_Discount') }}:</td>
            <td class="receipt-cell-end"><bdi dir="ltr">-{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: abs((float)$orderTotalPriceSummary['itemDiscount'])), currencyCode: getCurrencyCode()) }}</bdi></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end">{{ translate('extra_Discount') }}:</td>
            <td class="receipt-cell-end"><bdi dir="ltr">-{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: abs((float)$orderTotalPriceSummary['extraDiscount'])), currencyCode: getCurrencyCode()) }}</bdi></td>
        </tr>
        @if(!empty($orderTotalPriceSummary['totalExchangePrice']) && $orderTotalPriceSummary['totalExchangePrice'] > 0)
        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end">{{ translate('exchange_charge') }}:</td>
            <td class="receipt-cell-end">
                <bdi dir="ltr">-{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: abs((float)$orderTotalPriceSummary['totalExchangePrice'])), currencyCode: getCurrencyCode()) }}</bdi>
            </td>
        </tr>
        @endif

        @if(!empty($orderTotalPriceSummary['totalInstallationPrice']) && $orderTotalPriceSummary['totalInstallationPrice'] > 0)
        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end">{{ translate('installation_charge') }}:</td>
            <td class="receipt-cell-end">
                <bdi dir="ltr">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $orderTotalPriceSummary['totalInstallationPrice']), currencyCode: getCurrencyCode()) }}</bdi>
            </td>
        </tr>
        @endif

        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end">{{ translate('shipping_Charge') }}:</td>
            <td class="receipt-cell-end"><bdi dir="ltr">{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $orderTotalPriceSummary['shippingTotal']), currencyCode: getCurrencyCode()) }}</bdi></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end">{{ translate('Subtotal') }}:</td>
            <td class="receipt-cell-end"><bdi dir="ltr">{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $orderTotalPriceSummary['subTotal']), currencyCode: getCurrencyCode()) }}</bdi></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end">{{ translate('Tax') }} / {{ translate('VAT') }}:</td>
            <td class="receipt-cell-end"><bdi dir="ltr">{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $orderTotalPriceSummary['taxTotal']), currencyCode: getCurrencyCode()) }}</bdi></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end">{{ translate('Coupon_Discount') }}:</td>
            <td class="receipt-cell-end"><bdi dir="ltr">-{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: abs((float)$orderTotalPriceSummary['couponDiscount'])), currencyCode: getCurrencyCode()) }}</bdi></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end font-size-20px">
                {{ translate('Total') }}:
            </td>
            <td class="receipt-cell-end font-size-20px">
                <bdi dir="ltr">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $orderTotalPriceSummary['totalAmount']), currencyCode: getCurrencyCode()) }}</bdi>
            </td>
        </tr>

        @if ($order->order_type == 'pos' || $order->order_type == 'POS')
        <tr>
            <td colspan="4">
                <span class="dashed-hr"></span>
            </td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end">
                {{ translate('Paid_Amount') }}:
            </td>
            <td class="receipt-cell-end">
                <bdi dir="ltr">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $orderTotalPriceSummary['paidAmount']), currencyCode: getCurrencyCode()) }}</bdi>
            </td>
        </tr>

        <tr>
            <td colspan="2"></td>
            <td class="receipt-cell-end">
                {{ translate('Change_Amount') }}:
            </td>
            <td class="receipt-cell-end">
                <bdi dir="ltr">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $orderTotalPriceSummary['changeAmount']), currencyCode: getCurrencyCode()) }}</bdi>
            </td>
        </tr>
        @endif

    </table>


    <span class="dashed-hr"></span>
    <div class="d-flex flex-row justify-content-between">
        <span>{{ translate('paid_By') }}: {{ translate($order->payment_method) }}</span>
    </div>
    <span class="dashed-hr"></span>
    <h5 class="text-center py-2 text-uppercase">
        {{ translate('thank_you') }}
    </h5>
    <span class="dashed-hr"></span>
</div>
