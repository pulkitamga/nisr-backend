<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Quantity</th>
            <th>Reference</th>
        </tr>
    </thead>
    <tbody>
        @foreach($history as $log)
        <tr>
            <td>{{ $log->created_at->format('Y-m-d') }}</td>
            <td>
                {{-- Matching the logic from your table --}}
                {{ $log->received_from_branch == request('branch_id') ? 'Transfer In' : 'Transfer Out' }}
            </td>
            <td>{{ $log->quantity }}</td>
            <td>
                Request #{{ $log->stock_requests_id }}
                {{ $log->stockRequest->reference ?? '' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>