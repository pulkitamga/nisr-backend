<table>
    <tr>
        <td colspan="7"><strong>Inhouse Product Sale Report</strong></td>
    </tr>
    <tr>
        <td colspan="7">From: {{ $filters['from'] }} | To: {{ $filters['to'] }}</td>
    </tr>
    <tr>
        <td colspan="7">Exported at: {{ optional($exportedAt ?? now())->format('Y-m-d H:i:s') }}</td>
    </tr>
</table>

<table>
    <thead>
    <tr>
        <th>Channel</th>
        <th>Total Qty</th>
        <th>Total Sales</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>POS</td>
        <td>{{ $summary['pos_qty'] }}</td>
        <td>{{ number_format($summary['pos_amount'], 2) }}</td>
    </tr>
    <tr>
        <td>Online</td>
        <td>{{ $summary['online_qty'] }}</td>
        <td>{{ number_format($summary['online_amount'], 2) }}</td>
    </tr>
    <tr>
        <td>Wholesale</td>
        <td>{{ $summary['wholesale_qty'] }}</td>
        <td>{{ number_format($summary['wholesale_amount'], 2) }}</td>
    </tr>
    <tr>
        <td><strong>Total</strong></td>
        <td><strong>{{ $summary['total_qty'] }}</strong></td>
        <td><strong>{{ number_format($summary['total_amount'], 2) }}</strong></td>
    </tr>
    </tbody>
</table>

<table>
    <tr><td colspan="7"><strong>POS Table</strong></td></tr>
    <thead>
    <tr>
        <th>#</th>
        <th>Product</th>
        <th>Branch</th>
        <th>Qty</th>
        <th>Orders</th>
        <th>Sales</th>
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
    <tr><td colspan="7"><strong>Online Table</strong></td></tr>
    <thead>
    <tr>
        <th>#</th>
        <th>Product</th>
        <th>Branch</th>
        <th>Qty</th>
        <th>Orders</th>
        <th>Sales</th>
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
    <tr><td colspan="7"><strong>Wholesale Table</strong></td></tr>
    <thead>
    <tr>
        <th>#</th>
        <th>Product</th>
        <th>Branch</th>
        <th>Qty</th>
        <th>Orders</th>
        <th>Sales</th>
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

