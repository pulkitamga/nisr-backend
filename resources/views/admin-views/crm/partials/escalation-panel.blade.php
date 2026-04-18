@php
    $escalationItems = ($escalations ?? collect())->values();
    $latestEscalation = $escalationItems->first();
    $currentStatus = strtolower(trim((string)($latestEscalation->status ?? 'open')));
    $statusFlow = [
        'open' => ['acknowledged', 'in_progress', 'resolved', 'rejected', 'cancelled'],
        'acknowledged' => ['in_progress', 'resolved', 'rejected', 'cancelled'],
        'in_progress' => ['resolved', 'rejected', 'cancelled'],
    ];
    $nextStatuses = $statusFlow[$currentStatus] ?? [];
    $statusClass = [
        'open' => 'badge-soft-warning',
        'acknowledged' => 'badge-soft-info',
        'in_progress' => 'badge-soft-primary',
        'resolved' => 'badge-soft-success',
        'rejected' => 'badge-soft-danger',
        'cancelled' => 'badge-soft-dark',
    ];
    $resolveStatusLabel = static function (?string $status): string {
        $normalized = strtolower(trim((string)$status));
        if ($normalized === '') {
            $normalized = 'open';
        }

        return \App\Utils\crm_status_label($normalized);
    };
@endphp

<div class="card mt-3" dir="{{ session('direction') === 'rtl' ? 'rtl' : 'ltr' }}">
    <div class="card-header">
        <h5 class="mb-0">{{ translate('Escalation Management') }}</h5>
    </div>
    <div class="card-body">
        @if($latestEscalation)
            <div class="row g-3 mb-3">
                <div class="col-lg-7">
                    <div class="mb-2">
                        <strong>{{ translate('Current Escalation') }}:</strong>
                        <span class="badge {{ $statusClass[$currentStatus] ?? 'badge-soft-secondary' }}">
                            {{ $resolveStatusLabel($latestEscalation->status) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>{{ translate('Escalation Reason') }}:</strong>
                        <span dir="auto">{{ $latestEscalation->reason ?: translate('N/A') }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>{{ translate('Escalated By') }}:</strong>
                        <span>{{ $latestEscalation->escalatedBy?->name ?? translate('System') }}</span>
                    </div>
                    <div>
                        <strong>{{ translate('Escalated At') }}:</strong>
                        <span class="bidi-ltr d-inline-block">{{ $latestEscalation->created_at?->translatedFormat('d M, Y H:i') }}</span>
                    </div>
                </div>
                <div class="col-lg-5">
                    @if(!empty($nextStatuses))
                        <form method="POST" action="{{ route('admin.crm.escalation.update-status', $latestEscalation->id) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">{{ translate('Change Escalation Status') }}</label>
                                <select class="form-control" name="status" required>
                                    <option value="">{{ translate('select_status') }}</option>
                                    @foreach($nextStatuses as $nextStatus)
                                        <option value="{{ $nextStatus }}">{{ $resolveStatusLabel($nextStatus) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn--primary btn-sm">{{ translate('Update') }}</button>
                        </form>
                    @else
                        <p class="text-muted mb-0">{{ translate('escalation_already_closed') }}</p>
                    @endif
                </div>
            </div>
        @endif

        <h6 class="mb-2">{{ translate('Escalation History') }}</h6>
        @if($escalationItems->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('Escalation Status') }}</th>
                            <th>{{ translate('Escalation Reason') }}</th>
                            <th>{{ translate('Escalated By') }}</th>
                            <th>{{ translate('Escalated At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($escalationItems as $index => $escalation)
                            @php
                                $itemStatus = strtolower(trim((string)($escalation->status ?? 'open')));
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="badge {{ $statusClass[$itemStatus] ?? 'badge-soft-secondary' }}">
                                        {{ $resolveStatusLabel($escalation->status) }}
                                    </span>
                                </td>
                                <td dir="auto">{{ $escalation->reason ?: translate('N/A') }}</td>
                                <td>{{ $escalation->escalatedBy?->name ?? translate('System') }}</td>
                                <td><span class="bidi-ltr d-inline-block">{{ $escalation->created_at?->translatedFormat('d M, Y H:i') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">{{ translate('no_escalation_history') }}</p>
        @endif
    </div>
</div>
