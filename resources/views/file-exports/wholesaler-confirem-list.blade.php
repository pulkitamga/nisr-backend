<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}">
<table>
    <thead>
        <tr>
            <th style="font-size: 18px">{{translate('wholesaler_confirem_order_List')}}</th>
        </tr>
        <tr>

            <th>{{ translate('request_Analytics') .' '.'-'}}</th>
            <th></th>
            <th>
                {{translate('filter_By').' '.'-'.' '.ucwords($data['filter'])}}
                <br>
                {{translate('total__Request').' '.'-'.' '.count($data['confirem'])}}

            </th>
        </tr>

        <tr>
            <td>{{translate('SL')}}</td>
            <td>{{translate('DATE')}}</td>
            <td>{{translate('Purchase_Order_No')}}</td>
            <td>{{translate('Quotation_No')}}</td>
            <td>{{translate('Confirmed_order_no')}}</td>
            <td>{{translate('Inovice_no')}}</td>
            <td>{{translate('Wholesaler')}}</td>
            <td>{{translate('Delivery_Status')}}</td>
            <td>{{translate('Payment_Status')}}</td>
            <td>{{translate('Final_price')}}</td>

        </tr>
        @foreach ($data['confirem'] as $key=>$order)
        <tr>
            <td> {{++$key}} </td>
            <td>{{ \Carbon\Carbon::parse($order->confirmed_at)->format('d/m/Y') }}</td>
            <td>{{ $order->purchase_order_no }}</td>
            <td>{{ $order->quotation_no }}</td>
            <td>{{ $order->confirm_order_no ?? ''}}</td>
            <td>{{ $order->invoice_no ?? ''}}</td>
            <td>{{ $order->wholeseller->wholesalerBusiness->company_name ?? '' }}</td>
            <td>
                @php
                $deliveryStatus = strtolower($order->delivery_status ?? 'pending');
                $deliveryColors = [
                'delivered' => 'bg-green-100 text-green-800',
                'partials' => 'bg-yellow-100 text-yellow-800',
                'pending' => 'bg-red-100 text-red-800',
                ];
                $deliveryClass = $deliveryColors[$deliveryStatus] ?? 'bg-gray-100
                text-gray-800';
                @endphp

                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $deliveryClass }}">
                    {{ ucfirst($deliveryStatus) }}
                </span>
            </td>
            <td>
                @php
                $status = strtolower($order->payment_status ?? 'unpaid');
                $statusColors = [
                'paid' => 'bg-green-100 text-green-800',
                'partials' => 'bg-yellow-100 text-yellow-800',
                'unpaid' => 'bg-red-100 text-red-800',
                ];
                $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                @endphp

                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                    {{ ucfirst($status) }}
                </span>
            </td>
            <td>{{ $order->final_price ?? 'N/A' }}</td>
        </tr>
        @endforeach
    </thead>
</table>

</html>