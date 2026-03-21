@php($targetInput = $targetInput ?? '')
@php($title = $title ?? translate('scan_barcode_or_qr'))

<button
    type="button"
    class="btn btn-outline-secondary scan-serial-btn serial-scan-btn"
    data-target-input="{{ $targetInput }}"
    title="{{ $title }}"
    aria-label="{{ $title }}"
>
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path d="M4 7V5a1 1 0 0 1 1-1h2M20 7V5a1 1 0 0 0-1-1h-2M4 17v2a1 1 0 0 0 1 1h2M20 17v2a1 1 0 0 1-1 1h-2M7 12h10M8 9h1v6H8zM11 9h2v6h-2zM15 9h1v6h-1z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
    </svg>
</button>
