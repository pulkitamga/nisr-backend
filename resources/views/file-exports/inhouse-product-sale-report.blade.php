<table>
    <tr>
        <td colspan="7"><strong>{{ __('Inhouse Product Sale Report') }}</strong></td>
    </tr>
    <tr>
        <td colspan="7">{{ __('From') }}: {{ $filters['from'] }} | {{ __('To') }}: {{ $filters['to'] }}</td>
    </tr>
    <tr>
        <td colspan="7">{{ __('Exported at') }}: {{ optional($exportedAt ?? now())->format('Y-m-d H:i:s') }}</td>
    </tr>
</table>

<table>
    <thead>
    <tr>
        <th>{{ __('Channel') }}</th>
        <th>{{ __('Total Qty') }}</th>
        <th>{{ __('Total Sales') }}</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>{{ __('POS') }}</td>
        <td>{{ $summary['pos_qty'] }}</td>
        <td>{{ number_format($summary['pos_amount'], 2) }}</td>
    </tr>
    <tr>
        <td>{{ __('Online') }}</td>
        <td>{{ $summary['online_qty'] }}</td>
        <td>{{ number_format($summary['online_amount'], 2) }}</td>
    </tr>
    <tr>
        <td>{{ __('Wholesale') }}</td>
        <td>{{ $summary['wholesale_qty'] }}</td>
        <td>{{ number_format($summary['wholesale_amount'], 2) }}</td>
    </tr>
    <tr>
        <td><strong>{{ __('Total') }}</strong></td>
        <td><strong>{{ $summary['total_qty'] }}</strong></td>
        <td><strong>{{ number_format($summary['total_amount'], 2) }}</strong></td>
    </tr>
    </tbody>
</table>

<table>
    <tr><td colspan="7"><strong>{{ __('POS Table') }}</strong></td></tr>
    <thead>
    <tr>
        <th>#</th>
        <th>{{ __('Product') }}</th>
        <th>{{ __('Branch') }}</th>
        <th>{{ __('Qty') }}</th>
        <th>{{ __('Orders') }}</th>
        <th>{{ __('Sales') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($posRows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->product_name }}</td>
            <td>{{ $row->branch_name }}</td>
            <td>{{ $row->total_qty }}</td>
            <td>{{ $row->total_orders }}</td>
            <td>{{ number_format($row->total_amount, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table>
    <tr><td colspan="7"><strong>{{ __('Online Table') }}</strong></td></tr>
    <thead>
    <tr>
        <th>#</th>
        <th>{{ __('Product') }}</th>
        <th>{{ __('Branch') }}</th>
        <th>{{ __('Qty') }}</th>
        <th>{{ __('Orders') }}</th>
        <th>{{ __('Sales') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($onlineRows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->product_name }}</td>
            <td>{{ $row->branch_name }}</td>
            <td>{{ $row->total_qty }}</td>
            <td>{{ $row->total_orders }}</td>
            <td>{{ number_format($row->total_amount, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table>
    <tr><td colspan="7"><strong>{{ __('Wholesale Table') }}</strong></td></tr>
    <thead>
    <tr>
        <th>#</th>
        <th>{{ __('Product') }}</th>
        <th>{{ __('Branch') }}</th>
        <th>{{ __('Qty') }}</th>
        <th>{{ __('Orders') }}</th>
        <th>{{ __('Sales') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($wholesaleRows as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->product_name }}</td>
            <td>{{ $row->branch_name }}</td>
            <td>{{ $row->total_qty }}</td>
            <td>{{ $row->total_orders }}</td>
            <td>{{ number_format($row->total_amount, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
