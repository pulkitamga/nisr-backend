<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<table>
    <thead>
    <tr>
        <th>{{translate('vendor_Report')}}</th>
    </tr>
    <tr>

        <th>{{ translate('Filter_Criteria') .' '.'-'}}</th>
        <th></th>
        <th>
            {{translate('search_Bar_Content').' '.'-'.' '. $data['search'] ?? 'N/A'}}
            <br>
            {{translate('store').' '.'-'.' '.ucwords($data['vendor'] != 'all' ? $data['vendor']?->shop->name : translate('All') )}}
            <br>
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
        @if($data['vendor'] == 'all')
        <td> {{translate('vendor_Info')}}    </td>
        @endif
        <td> {{translate('total_Order')}}    </td>
        <td> {{translate('commission')}}</td>
        <td> {{translate('refund_Rate')}}</td>
    </tr>
    @foreach ($data['orders'] as $key=>$order)
        <tr>
            <td> {{++$key}}</td>
            @if($data['vendor'] == 'all')
                <td>
                    {{$order?->seller?->shop?->name ?? translate('data_not_found')}}
                    <br>
                    {{$order->seller?->f_name.' '.$order->seller?->l_name }}
                </td>
            @endif
            <td>{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order->total_order_amount), currencyCode: getCurrencyCode()) }}</td>
            <td>{{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order->total_admin_commission), currencyCode: getCurrencyCode()) }}</td>
            <td>
                    <?php
                    $array = array();
                    if ($data['refunds']) {
                        foreach ($data['refunds'] as $refund) {
                            $array += array(
                                $refund['payer_id'] => $refund['total_refund_amount']
                            );
                        }
                    }
                    if (array_key_exists($order->seller_id, $array)) {
                        echo number_format(($array[$order->seller_id] / $order->total_order_amount) * 100, 2) . '%';
                    } else {
                        echo '0%';
                    }
                    ?>
            </td>
        </tr>
    @endforeach
    </thead>
</table>
</html>
