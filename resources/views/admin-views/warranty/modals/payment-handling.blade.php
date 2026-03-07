<div class="modal fade" id="paymentHandlingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form  method="POST" class="claim-modal-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Handle Payment') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>

                <div class="modal-body">

                    @if($claim->charges->where('is_paid', false)->count())
                    <div id="pendingChargesWrapper" class="alert mb-3" style="display: none;">
                        <strong>{{ translate('Pending Charges') }}:</strong>
                        <div class="mt-2">
                            @foreach($claim->charges->where('is_paid', false) as $charge)
                            <div>
                                <label class="d-flex align-items-center">
                                    <input type="checkbox" name="charge_ids[]" value="{{ $charge->id }}" class="mr-2">
                                    <span>
                                        <strong>{{ translate(ucfirst(str_replace('_', ' ', $charge->charge_type))) }}:</strong>
                                        {{setCurrencySymbol(amount: usdToDefaultCurrency(amount:$charge->amount))}}
                                    </span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif


                    <!-- Action -->
                    <div class="form-group">
                        <label>{{ translate('Action') }}</label>
                        <select name="action" id="paymentAction" class="form-control" required>
                            <option value="remind">{{ translate('Send Reminder') }}</option>
                            <option value="pos">{{ translate('Record POS Payment') }}</option>
                            <option value="cod">{{ translate('Approve Cash on Delivery') }}</option>
                            <option value="cod_collect">{{ translate('Confirm COD Collection') }}</option>
                            <option value="online_link">{{ translate('Generate Online Payment Link') }}</option>
                            <option value="waive">{{ translate('Waive All Fees') }}</option>
                            <option value="reject">{{ translate('Reject Claim') }}</option>
                        </select>
                    </div>

                    <div class="form-group" id="paymentReferenceWrapper" style="display: none;">
                        <label>{{ translate('Payment Reference') }}</label>
                        <input
                            type="text"
                            name="payment_reference"
                            id="paymentReference"
                            class="form-control"
                            placeholder="{{ translate('POS slip number or COD receipt number') }}"
                        >
                    </div>

                    <div class="form-group" id="linkExpiryWrapper" style="display: none;">
                        <label>{{ translate('Payment Link Expiry (Hours)') }}</label>
                        <input type="number" min="1" max="168" name="link_expire_hours" id="linkExpireHours" class="form-control" value="24">
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label>{{ translate('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="{{ translate('e.g. Paid via cash on 2025-04-05') }}"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('paymentHandlingModal');
        if (!modal) return;

        const actionSelect = modal.querySelector('#paymentAction');
        const chargeCheckboxes = modal.querySelectorAll('input[name="charge_ids[]"]');
        const pendingWrapper = modal.querySelector('#pendingChargesWrapper');
        const paymentReferenceWrapper = modal.querySelector('#paymentReferenceWrapper');
        const paymentReferenceInput = modal.querySelector('#paymentReference');
        const linkExpiryWrapper = modal.querySelector('#linkExpiryWrapper');
        const linkExpireHoursInput = modal.querySelector('#linkExpireHours');

        function toggleChargeView() {
            const action = actionSelect.value;
            const chargeActions = ['pos', 'cod', 'cod_collect', 'online_link'];
            const referenceActions = ['pos', 'cod_collect'];
            const linkActions = ['online_link'];

            const chargeRequired = chargeActions.includes(action);
            const referenceRequired = referenceActions.includes(action);
            const linkRequired = linkActions.includes(action);

            if (pendingWrapper) {
                pendingWrapper.style.display = chargeRequired ? 'block' : 'none';
            }

            chargeCheckboxes.forEach(cb => {
                if (!chargeRequired) cb.checked = false;
            });

            if (paymentReferenceWrapper && paymentReferenceInput) {
                paymentReferenceWrapper.style.display = referenceRequired ? 'block' : 'none';
                paymentReferenceInput.required = referenceRequired;
                if (!referenceRequired) {
                    paymentReferenceInput.value = '';
                }
            }

            if (linkExpiryWrapper && linkExpireHoursInput) {
                linkExpiryWrapper.style.display = linkRequired ? 'block' : 'none';
                linkExpireHoursInput.required = linkRequired;
                if (!linkRequired) {
                    linkExpireHoursInput.value = '24';
                }
            }
        }

        actionSelect.addEventListener('change', toggleChargeView);
        modal.addEventListener('shown.bs.modal', toggleChargeView);
    });
</script>
@endpush
