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
                            <option value="paid">{{ translate('Mark as Paid') }}</option>
                            <option value="waive">{{ translate('Waive All Fees') }}</option>
                            <option value="reject">{{ translate('Reject Claim') }}</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label>{{ translate('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid via cash on 2025-04-05"></textarea>
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
        const actionSelect = modal.querySelector('#paymentAction');
        const chargeCheckboxes = modal.querySelectorAll('input[name="charge_ids[]"]');
        const pendingWrapper = modal.querySelector('#pendingChargesWrapper');

        function toggleChargeView() {
            const action = actionSelect.value;
            const required = action === 'paid';

            pendingWrapper.style.display = required ? 'block' : 'none';

            chargeCheckboxes.forEach(cb => {
                cb.required = required;
                if (!required) cb.checked = false;
            });
        }

        actionSelect.addEventListener('change', toggleChargeView);
        modal.addEventListener('shown.bs.modal', toggleChargeView);
    });
</script>
@endpush