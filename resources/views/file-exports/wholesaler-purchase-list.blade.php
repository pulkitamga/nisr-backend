<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<table>

    <thead>
        <tr>
            <th style="font-size: 18px">{{translate('wholesaler_purchase_order_List')}}</th>
        </tr>
        <tr>

            <th>{{ translate('request_Analytics') .' '.'-'}}</th>
            <th></th>
            <th>
                {{translate('filter_By').' '.'-'.' '.ucwords($data['filter'])}}
                <br>
                {{translate('total_request').' '.'-'.' '.count($data['purchase'])}}

            </th>
        </tr>

        <tr>
            <td>{{translate('SL')}}</td>
            <td>{{translate('DATE')}}</td>
            <td>{{translate('Purchase_order_no')}}</td>
            <td>{{translate('Wholesaler')}}</td>
            <td>{{translate('Tier')}}</td>
            <td>{{translate('Status')}}</td>
        </tr>
        @foreach ($data['purchase'] as $key=>$order)
        <tr>
            <td> {{++$key}} </td>
            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</td>
            <td>{{ $order->purchase_order_no}}</td>
            <td>
                {{$order->wholeseller->wholesalerBusiness->company_name ?? ''}}
            </td>
            <td>{{ $order->wholeseller_tier ?? 'N/A' }}</td>
            <td>
                {{$order->status}}

            </td>
        </tr>
        @endforeach
    </thead>
</table>

</html>