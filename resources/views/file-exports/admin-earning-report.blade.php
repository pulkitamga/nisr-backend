<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<table>
    <thead>
    <tr>
        <th>{{translate('admin_Earning_Report')}}</th>
    </tr>
    <tr>

        <th>{{ translate('Filter_Criteria') .' '.'-'}}</th>
        <th></th>
        <th>
            {{translate('date_type').' '.'-'.' '.translate($data['dateType'])}}
            <br>
            @if($data['from'] && $data['to'])
                {{translate('From').' '.'-'.' '.date('d M, Y',strtotime($data['from']))}}
                <br>
                {{translate('To').' '.'-'.' '.date('d M, Y',strtotime($data['to']))}}
                <br>
            @endif
        </th>
    </tr>
    <tr>
        <td> {{translate('SL')}}</td>
        <td> {{translate('Duration')}}    </td>
        <td> {{translate('in-House_Earning')}}    </td>
        <td> {{translate('commission_Earning')}}</td>
        <td> {{translate('earn_From_Shipping')}}</td>
        <td> {{translate('delivery_man_incentive')}}</td>
        <td> {{translate('discount_Given')}}</td>
        <td> {{translate('VAT/TAX')}}</td>
        <td> {{translate('refund_Given')}}</td>
        <td> {{translate('total_Earning')}}</td>
    </tr>

        @foreach ($data['inhouseEarn'] as $key=>$item)
            @php($inhouseEarning = $item-$data['totalTax'][$key])))
            <tr>
                <td>{{$loop->iteration++}} </td>
                <td> {{$key}}</td>
                <td>{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $inhouseEarning), currencyCode: getCurrencyCode()) }}</td>
                <td>{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['adminCommissionEarn'][$key]), currencyCode: getCurrencyCode()) }}</td>
                <td>{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['shippingEarn'][$key]), currencyCode: getCurrencyCode()) }}</td>
                <td>{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['deliverymanIncentive'][$key]), currencyCode: getCurrencyCode()) }}</td>
                <td>{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['discountGiven'][$key]), currencyCode: getCurrencyCode()) }}</td>
                <td>{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['totalTax'][$key]), currencyCode: getCurrencyCode()) }}</td>
                <td>{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['refundGiven'][$key]), currencyCode: getCurrencyCode()) }}</td>
                <td>{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $inhouseEarning+$data['adminCommissionEarn'][$key]+$data['totalTax'][$key]+$data['shippingEarn'][$key]-$data['discountGiven'][$key]-$data['refundGiven'][$key] - $data['deliverymanIncentive'][$key]), currencyCode: getCurrencyCode()) }}</td>
            </tr>
        @endforeach
    </thead>
</table>
</html>
