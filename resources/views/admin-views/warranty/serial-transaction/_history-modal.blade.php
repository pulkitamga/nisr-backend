<div class="warranty-transfer-timeline position-relative">
    @forelse($history as $h)
        <div class="warranty-transfer-timeline__entry position-relative">
            
            <div class="warranty-transfer-timeline__card p-3 ms-5 rounded-3 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="fw-semibold mb-1 text-dark">
                            @if($h->from_branch_id)
                                {{ $h->fromBranch->branch_name ?? ($fallbackLabels['unknown'] ?? translate('Unknown')) }}
                            @else
                                <em>{{ $fallbackLabels['initial_import'] ?? translate('initial_import') }}</em>
                            @endif
                            →
                            @if($h->to_branch_id)
                                {{ $h->toBranch->branch_name ?? ($fallbackLabels['unknown'] ?? translate('Unknown')) }}
                            @elseif($h->distributor_id)
                                {{ $h->distributor->company_name ?? ($fallbackLabels['wholesaler'] ?? translate('Wholesaler')) }}
                            @else
                                <em>{{ $fallbackLabels['unknown'] ?? translate('Unknown') }}</em>
                            @endif
                        </h6>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge bg-light text-primary border border-primary fw-normal warranty-transfer-timeline__badge">
                                {{ $typeLabels[$h->transfer_type] ?? $h->transfer_type }}
                            </span>
                            <small class="text-muted">
                                <span class="bidi-ltr d-inline-block">{{ \Carbon\Carbon::parse($h->transferred_at)->format('d M Y, h:i A') }}</span>
                            </small>
                        </div>
                        @if($h->stock_transfer_id)
                            <small class="d-block text-secondary mt-1">
                                {{ translate('Stock_Transfer') }} 
                            </small>
                        @elseif($h->wholesale_delivery_id)
                            <small class="d-block text-secondary mt-1">
                                {{ translate('Delivery') }}
                            </small>
                        @endif
                    </div>
                    <small class="text-muted">{{ \Carbon\Carbon::parse($h->transferred_at)->diffForHumans() }}</small>
                </div>
            </div>
        </div>
    @empty
        <p class="text-center text-muted py-4">{{ translate('no_history_found') }}</p>
    @endforelse
</div>
