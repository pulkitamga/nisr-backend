@php
use Illuminate\Support\Arr;
@endphp

<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle">
        {{ translate('history_of_Order') }}
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">
    <div class="timeline-wrapper">
        <div class="timeline-steps">
            @forelse($histories as $history)
            @php
            $props = $history->properties->toArray();
            $attributes = Arr::get($props, 'attributes', []);
            $actionType = Arr::get($props, 'action_type', []);
            $old = Arr::get($props, 'old', []);
            $causer = Arr::get($props, 'causer_name', 'Admin');

            if (
            empty(Arr::get($old, 'confirm_order_no')) &&
            !empty(Arr::get($attributes, 'confirm_order_no'))
            ) {
            $status = 'confirm order assigned';
            }

            
            elseif (
            empty(Arr::get($old, 'invoice_no')) &&
            !empty(Arr::get($attributes, 'invoice_no'))
            ) {
            $status = 'Invoice No Assigned';
            }


            elseif (
            empty(Arr::get($old, 'purchase_order_no')) &&
            !empty(Arr::get($attributes, 'purchase_order_no'))
            ) {
            $status = 'Purchase Order No Assigned';
            }
            
             else {
            $status = $attributes['status']
            ?? $attributes['payment_status']
            ?? $attributes['delivery_status']
            ?? $actionType
            ?? 'unknown';
            }
            @endphp



            <div class="timeline-step {{ in_array($status, ['returned', 'failed', 'canceled']) ? 'failed' : 'completed' }}">
                <div class="timeline-number">
                    <svg viewBox="0 0 512 512" width="100">
                        <path d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"></path>
                    </svg>
                </div>
                <div class="timeline-info">
                    <p class="timeline-title">
                        <strong>{{ ucfirst($history->event) }}</strong>
                        - {{ $status }}
                    </p>
                    <p class="timeline-text">
                        {{ translate('By') }}: {{ $causer }}
                    </p>
                    <p class="timeline-text">
                        {{ translate('At') }}: {{ $history->created_at->format('d/m/y h:i A') }}
                    </p>

                    @foreach($attributes as $key => $value)
                    @if($key !== 'status')
                    <p class="timeline-text">
                        {{ ucfirst(str_replace('_', ' ', $key)) }}:
                        {{ is_numeric($value) ?  $value : $value }}
                    </p>
                    @endif
                    @endforeach
                </div>
            </div>
            @empty

            <div class="timeline-step failed">
                <div class="timeline-number">
                    <svg viewBox="0 0 512 512" width="100">
                        <path d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"></path>
                    </svg>
                </div>
                <div class="timeline-info">
                    <p class="timeline-title">{{ translate('no_history_for_this_order') }}</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('close') }}</button>
</div>