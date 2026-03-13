<table>
    <thead>
        <tr>
            <th>{{ translate('Date') }}</th>
            <th>{{ translate('Type') }}</th>
            <th>{{ translate('Quantity') }}</th>
            <th>{{ translate('Reference') }}</th>
            <th>{{ translate('Description') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($history as $log)
        @php
        // Ensure data is treated as an array
        $logData = is_array($log) ? $log : $log->toArray();
        $isStockIn = ($logData['type'] ?? '') === 'IN';
        $reference = $logData['reference'] ?? 'N/A';
        @endphp
        <tr>
            {{-- Date Formatting --}}
            <td>{{ \Carbon\Carbon::parse($logData['created_at'])->format('Y-m-d H:i A') }}</td>

            {{-- Transaction Type --}}
            <td>{{ $isStockIn ? translate('Stock In') : translate('Stock Out') }}</td>

            {{-- Signed Quantity --}}
            <td>{{ ($isStockIn ? '+' : '-') . ($logData['quantity'] ?? 0) }}</td>

            {{-- Reference Code --}}
            <td>{{ $reference }}</td>

            {{-- Contextual Description --}}
            <td>
                @if($reference === 'BRANCH TRANSFER')
                {{ $isStockIn ? translate('Received from') : translate('Sent to') }}
                {{ $logData['from_branch'] ?? $logData['to_branch'] ?? translate('Branch') }}
                @else
                {{ $logData['remarks'] ?? '' }}
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
