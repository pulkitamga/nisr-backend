<table>
    <thead>
        <tr>
            <th>{{ __('DATE') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Quantity') }}</th>
            <th>{{ __('Reference') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($history as $log)
        <tr>
            <td>{{ $log->created_at->format('Y-m-d') }}</td>
            <td>
                {{-- Matching the logic from your table --}}
                {{ $log->received_from_branch == request('branch_id') ? __('Transfer In') : __('Transfer Out') }}
            </td>
            <td>{{ $log->quantity }}</td>
            <td>
                {{ __('Request') }} #{{ $log->stock_requests_id }}
                {{ $log->stockRequest->reference ?? '' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
