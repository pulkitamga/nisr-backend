<div class="modal-header">
    <h5 class="modal-title">{{ translate('stock_report') }}</h5>
    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <div><strong>{{ translate('product') }}:</strong> {{ $product->name }}</div>
            <div><strong>{{ translate('variation') }}:</strong> {{ $variation ?? 'Default' }}</div>
            <div><strong>{{ translate('current_stock') }}:</strong> {{ $currentStock }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <input type="checkbox" class="action-toggle-internal-transfer" id="include-internal-transfer"
                data-base-url="{{ $reportBaseUrl }}"
                {{ $includeInternalTransfer ? 'checked' : '' }}>
            <label class="mb-0" for="include-internal-transfer">
                {{ translate('view_internal_branch_transfers') }}
            </label>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-2 text-success">{{ translate('stock_in') }}</h6>
                <div class="d-flex justify-content-between">
                    <span>{{ translate('initial_stock') }}</span>
                    <strong>+ {{ $summary['stock_in']['initial_stock'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>{{ translate('manual_adjust_add') }}</span>
                    <strong>+ {{ $summary['stock_in']['manual_adjust_add'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>{{ translate('returns') }}</span>
                    <strong>+ {{ $summary['stock_in']['returns'] }}</strong>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-2 text-danger">{{ translate('stock_out') }}</h6>
                <div class="d-flex justify-content-between">
                    <span>{{ translate('sales_pos') }}</span>
                    <strong>- {{ $summary['stock_out']['sales_pos'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>{{ translate('sales_online') }}</span>
                    <strong>- {{ $summary['stock_out']['sales_online'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>{{ translate('sales_wholesale_transfer') }}</span>
                    <strong>- {{ $summary['stock_out']['sales_wholesale_transfer'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>{{ translate('manual_adjust_negative') }}</span>
                    <strong>- {{ $summary['stock_out']['manual_adjust_negative'] }}</strong>
                </div>
            </div>
        </div>
    </div>

    @if ($includeInternalTransfer)
        <div class="border rounded p-3 mb-3">
            <h6 class="mb-2">{{ translate('internal_branch_transfer') }}</h6>
            <div class="d-flex justify-content-between">
                <span>{{ translate('stock_in') }}</span>
                <strong>+ {{ $summary['internal_transfer']['in'] }}</strong>
            </div>
            <div class="d-flex justify-content-between">
                <span>{{ translate('stock_out') }}</span>
                <strong>- {{ $summary['internal_transfer']['out'] }}</strong>
            </div>
            <small class="text-muted">
                {{ translate('internal_transfers_do_not_change_net_general_stock') }}
            </small>
        </div>
    @endif

    <div class="table-responsive" style="max-height: 350px;">
        <table class="table table-sm table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>{{ translate('date') }}</th>
                    <th>{{ translate('type') }}</th>
                    <th>{{ translate('quantity') }}</th>
                    <th>{{ translate('category') }}</th>
                    <th>{{ translate('reference') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historyRows as $history)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($history['date'])->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="{{ $history['type'] === 'IN' ? 'text-success' : 'text-danger' }} fw-semibold">
                                {{ $history['type'] === 'IN' ? 'Stock In' : 'Stock Out' }}
                            </span>
                        </td>
                        <td class="{{ $history['type'] === 'IN' ? 'text-success' : 'text-danger' }} fw-semibold">
                            {{ $history['type'] === 'IN' ? '+' : '-' }} {{ $history['quantity'] }}
                        </td>
                        <td>{{ $history['category'] }}</td>
                        <td>
                            <div>{{ str_replace('_', ' ', $history['reason']) }}</div>
                            <small class="text-muted">{{ $history['remarks'] }}</small>
                            @if ($history['from_branch'] || $history['to_branch'])
                                <small class="d-block text-muted">
                                    {{ $history['from_branch'] ? ('From: ' . $history['from_branch']) : '' }}
                                    {{ $history['to_branch'] ? (' | To: ' . $history['to_branch']) : '' }}
                                </small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            {{ translate('no_stock_history_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        {{ translate('close') }}
    </button>
</div>
