<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<table>
    <thead>
        <tr>
            <th style="font-size: 18px">{{translate('wholesaler_quotation_List')}}</th>
        </tr>
        <tr>

            <th>{{ translate('request_Analytics') .' '.'-'}}</th>
            <th></th>
            <th>
                {{translate('filter_By').' '.'-'.' '.ucwords($data['filter'])}}
                <br>
                {{translate('total__Request').' '.'-'.' '.count($data['quotation'])}}

            </th>
        </tr>

        <tr>
            <td>{{translate('SL')}}</td>
            <td>{{translate('DATE')}}</td>
            <td>{{translate('Order_No')}}</td>
            <td>{{translate('Quotation_No')}}</td>
            <td>{{translate('Wholesaler')}}</td>
            <td>{{translate('Tier')}}</td>
            <td>{{translate('Status')}}</td>
            <td>{{translate('Final_price')}}</td>
        </tr>
        @foreach ($data['quotation'] as $key=>$order)
        <tr>
            <td> {{++$key}} </td>
            <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</td>
            <td>{{ $order->purchase_order_no }}</td>
            <td>{{ $order->quotation_no }}</td>
            <td>{{ $order->wholeseller->wholesalerBusiness->company_name ?? '' }}</td>
            <td>{{ $order->wholeseller_tier ?? 'N/A' }}</td>
            <td>
                @php
                $status = $order->status;
                $color = match($status) {
                'sent' => 'bg-blue-100 text-blue-800',
                'accepted' => 'bg-green-100 text-green-800',
                'rejected' => 'bg-red-100 text-red-800',
                default => 'bg-gray-100 text-gray-800',
                };
                @endphp

                <span class="p-2 rounded-full text-sm font-semibold {{ $color }}">
                    {{ ucfirst($status) }}
                </span>
            </td>
            <td>{{ $order->final_price ?? 'N/A' }}</td>
        </tr>
        @endforeach
    </thead>
</table>

</html>