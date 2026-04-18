@extends('layouts.back-end.app')
@section('title', translate('manual_activate'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>{{translate('manual_warranty_activation')}}</h5>
        </div>
        <div class="card-body">
            <form action="{{route('admin.warranty.activation.manual')}}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="final_user_id" id="manualActivationCustomerId" value="{{ old('final_user_id') }}">
                <div class="form-group">
                    <label>{{translate('serial_number')}}</label>
                    <div class="input-group">
                        <input type="text" id="manualActivationSerialNumber" name="serial_number" value="{{ old('serial_number', $prefillSerial ?? '') }}" class="form-control @error('serial_number') is-invalid @enderror" required>
                        <div class="input-group-append">
                            @include('partials.serial-scan-button', ['targetInput' => '#manualActivationSerialNumber'])
                        </div>
                    </div>
                    @error('serial_number')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>{{translate('purchase_date')}}</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="form-control @error('purchase_date') is-invalid @enderror" required>
                    @error('purchase_date')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>{{ translate('Phone') }}</label>
                    <input type="text" name="customer_phone" id="manualActivationCustomerPhone" value="{{ old('customer_phone') }}" class="form-control @error('customer_phone') is-invalid @enderror" placeholder="{{ translate('enter_phone_number') }}">
                    @error('customer_phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>{{ translate('Email') }}</label>
                    <input type="email" name="customer_email" id="manualActivationCustomerEmail" value="{{ old('customer_email') }}" class="form-control @error('customer_email') is-invalid @enderror" placeholder="{{ translate('enter_email') }}">
                    @error('customer_email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>{{ translate('Suggested customer profile') }}</label>
                    <div id="manualActivationCustomerSuggestions" class="border rounded p-2 bg-white">
                        <small class="text-muted" id="manualActivationCustomerSuggestionsHint">{{ translate('Enter phone or email to find an existing customer profile.') }}</small>
                    </div>
                    <small class="text-muted d-block mt-2" id="manualActivationSelectedCustomerText"></small>
                    @error('final_user_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>{{translate('Reason')}}</label>
                    <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>{{translate('docs')}}</label>
                    <input type="file" name="docs" class="form-control @error('docs') is-invalid @enderror" accept="pdf,jpg">
                    @error('docs')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn--primary">{{translate('activate')}}</button>
            </form>
        </div>
    </div>
</div>

@include('partials.serial-scanner-assets')

@push('script')
<script>
    (function () {
        const phoneInput = document.getElementById('manualActivationCustomerPhone');
        const emailInput = document.getElementById('manualActivationCustomerEmail');
        const customerIdInput = document.getElementById('manualActivationCustomerId');
        const suggestionsBox = document.getElementById('manualActivationCustomerSuggestions');
        const selectedText = document.getElementById('manualActivationSelectedCustomerText');
        const emptyHint = @json(translate('Enter phone or email to find an existing customer profile.'));
        const noResultsText = @json(translate('No customer profile matched the entered phone or email.'));
        const useCustomerText = @json(translate('Link this warranty to customer profile'));
        const selectedCustomerText = @json(translate('Selected customer'));
        let lookupTimer = null;

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderEmptyHint() {
            suggestionsBox.innerHTML = '<small class="text-muted">' + emptyHint + '</small>';
        }

        function renderNoResults() {
            customerIdInput.value = '';
            selectedText.textContent = '';
            suggestionsBox.innerHTML = '<small class="text-muted">' + noResultsText + '</small>';
        }

        function renderSuggestions(customers) {
            if (!customers.length) {
                renderNoResults();
                return;
            }

            suggestionsBox.innerHTML = customers.map((customer) => `
                <button
                    type="button"
                    class="list-group-item list-group-item-action manual-customer-suggestion w-100 text-start mb-2 border rounded"
                    data-customer-id="${customer.id}"
                    data-customer-name="${escapeHtml(customer.name)}"
                    data-customer-phone="${escapeHtml(customer.phone || '')}"
                    data-customer-email="${escapeHtml(customer.email || '')}"
                >
                    <div><strong>${escapeHtml(customer.name)}</strong></div>
                    <div class="small text-muted">${escapeHtml(customer.phone || '')}</div>
                    <div class="small text-muted">${escapeHtml(customer.email || '')}</div>
                    <div class="small text-primary mt-1">${useCustomerText}</div>
                </button>
            `).join('');
        }

        function fetchSuggestions() {
            const phone = phoneInput.value.trim();
            const email = emailInput.value.trim();

            if (!phone && !email) {
                customerIdInput.value = '';
                selectedText.textContent = '';
                renderEmptyHint();
                return;
            }

            const url = new URL(@json(route('admin.warranty.activation.manual.customers')), window.location.origin);
            url.searchParams.set('phone', phone);
            url.searchParams.set('email', email);

            fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then((response) => response.json())
                .then((data) => renderSuggestions(data.customers || []))
                .catch(() => renderNoResults());
        }

        function queueSuggestionsFetch() {
            customerIdInput.value = '';
            selectedText.textContent = '';
            window.clearTimeout(lookupTimer);
            lookupTimer = window.setTimeout(fetchSuggestions, 250);
        }

        phoneInput.addEventListener('input', queueSuggestionsFetch);
        emailInput.addEventListener('input', queueSuggestionsFetch);

        document.addEventListener('click', function (event) {
            const button = event.target.closest('.manual-customer-suggestion');
            if (!button) {
                return;
            }

            customerIdInput.value = button.dataset.customerId || '';
            phoneInput.value = button.dataset.customerPhone || phoneInput.value;
            emailInput.value = button.dataset.customerEmail || emailInput.value;
            selectedText.textContent = selectedCustomerText + ': ' + (button.dataset.customerName || '');
            fetchSuggestions();
        });

        renderEmptyHint();
        if (phoneInput.value.trim() || emailInput.value.trim()) {
            fetchSuggestions();
        }
    })();
</script>
@endpush
@endsection
