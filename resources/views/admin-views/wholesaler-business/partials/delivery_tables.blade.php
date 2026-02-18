<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 bg-white shadow-sm rounded">
        <thead class="bg-light text-muted border-bottom">
            <tr class="text-nowrap">
                <th class="fw-semibold">{{ translate('sl') }}</th>
                <th class="fw-semibold">{{ translate('date') }}</th>
                <th class="fw-semibold">{{ translate('product_name') }}</th>
                <th class="fw-semibold">{{ translate('variation') }}</th>
                <th class="fw-semibold">{{ translate('requested_qty') }}</th>
                <th class="fw-semibold">{{ translate('qty_sent') }}</th>
                <th class="fw-semibold">{{ translate('remaining') }}</th>

            </tr>
        </thead>
        <tbody>
            @forelse($deliveries as $index => $delivery)
                                <tr class="align-middle">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($delivery->created_at)->format('d/m/Y') }}</td>
                                    <td>{{ $delivery->product->name ?? 'N/A' }}</td>
                                    <td>{{ $delivery->product_variation_type ?? 'No Variation' }}</td>
                                    <td>{{ $delivery->product_quantity }}</td>
                                    <td>{{ $delivery->quantity_sent }}</td>
                                    <td>{{ $delivery->remaining }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">{{ translate('No delivery records found') }}</td>
                                </tr>
                                @endforelse
        </tbody>
    </table>
</div>
<div class="table-responsive mt-4 shadow-sm rounded bg-white">
    <h5 class="px-3 pt-4 pb-2 text-muted fw-bold border-bottom">Delivery Logs</h5>
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light text-muted">
            <tr class="text-nowrap">
               <th class="fw-semibold">{{ translate('sl') }}</th>
                        <th class="fw-semibold">{{ translate('date') }}</th>
                        <th class="fw-semibold">{{ translate('product') }}</th>
                        <th class="fw-semibold">{{ translate('variation') }}</th>
                        <th class="fw-semibold">{{ translate('qty_sent') }}</th>
                        <th class="fw-semibold">{{ translate('branch') }}</th>
                        <th class="fw-semibold">{{ translate('note') }}</th>
                        <th class="fw-semibold text-center">{{ translate('Csv') }}</th>
            </tr>
        </thead>
        <tbody>
             @forelse($deliveryLogs as $index => $log)
                    <tr>
                        <td>{{ $deliveryLogs->firstItem() + $index }}</td>
                        <td>{{ \Carbon\Carbon::parse($log->delivery_date)->format('d/m/Y') }}</td>
                        <td>{{ $log->product->name ?? 'N/A' }}</td>
                        <td>{{ $log->product_variation_type ?? 'No Variation' }}</td>
                        <td>{{ $log->quantity_sent }}</td>
                        <td>{{ $log->branch->branch_name ?? 'N/A' }}</td>
                        <td>{{ $log->note ?? '-' }}</td>
                        <td class="text-center align-middle">
                            @if($log->serial_csv_path)
                            <a href="{{ route('admin.wholesale.business.delivery.download-csv', $log->id) }}"
                                class="btn btn-sm btn-outline-info" title="Download CSV">
                                <i class="tio-download"></i>
                            </a>
                            @else
                            {{ translate('no_csv_file_found') }}
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">{{ translate('no_delivery_logs_found') }}</td>
                    </tr>
                    @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="p-3">
    {{ $deliveryLogs->links() }}
</div>