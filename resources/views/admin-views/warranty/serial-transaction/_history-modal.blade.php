<div class="timeline-container position-relative">
    @forelse($history as $h)
        <div class="timeline-entry position-relative">
            
            <div class="timeline-card p-3 ms-5 rounded-3 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="fw-semibold mb-1 text-dark">
                            @if($h->from_branch_id)
                                {{ $h->fromBranch->branch_name ?? 'Unknown Branch' }}
                            @else
                                <em>{{ translate('initial_import') }}</em>
                            @endif
                            →
                            @if($h->to_branch_id)
                                {{ $h->toBranch->branch_name ?? 'Unknown Branch' }}
                            @elseif($h->distributor_id)
                                {{ $h->distributor->company_name ?? 'Wholesaler' }}
                            @else
                                <em>{{ translate('Unknown') }}</em>
                            @endif
                        </h6>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge bg-light text-primary border border-primary fw-normal">
                                {{ ucwords(str_replace('_', ' ', $h->transfer_type)) }}
                            </span>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($h->transferred_at)->format('d M Y, h:i A') }}
                            </small>
                        </div>
                        @if($h->stock_transfer_id)
                            <small class="d-block text-secondary mt-1">
                                {{ translate('Stock Transfer') }} 
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

<style>
/* --- Timeline Core --- */
.timeline-container {
    position: relative;
    padding-left: 2rem;
}
.timeline-container::before {
    content: "";
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #3b82f6, #93c5fd);
}

/* --- Each Entry --- */
.timeline-entry {
    position: relative;
    margin-bottom: 2rem;
}
.timeline-entry:last-child {
    margin-bottom: 0;
}

/* --- Icon Dot --- */
.timeline-dot {
    position: absolute;
    left: -2px;
    top: 0.5rem;
    background: #3b82f6;
    color: #fff;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    transition: all 0.3s ease;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
}
.timeline-dot:hover {
    transform: scale(1.08);
    box-shadow: 0 0 10px rgba(59,130,246,0.5);
}

/* --- Card Styling --- */
.timeline-card {
    border: 1px solid #f0f0f0;
    background-color: #fff;
    transition: all 0.3s ease;
}
.timeline-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

/* --- Typography --- */
.timeline-card h6 {
    font-size: 15px;
    color: #1e293b;
}
.timeline-card small {
    font-size: 13px;
}
.badge {
    font-size: 12px;
    padding: 0.35em 0.6em;
}
</style>
