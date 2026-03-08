<div class="modal fade" id="diagnoseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form  method="POST" class="claim-modal-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Diagnose Claim') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>

                <div class="modal-body">

                    <!-- Diagnosis Notes -->
                    <div class="form-group">
                        <label>{{ translate('Diagnosis Notes') }}</label>
                        <textarea name="diagnosis_notes" class="form-control" rows="3" required></textarea>
                    </div>

                    <!-- Action -->
                    <div class="form-group">
                        <label>{{ translate('Action') }}</label>
                        <select name="repair_or_replace" id="actionSelect" class="form-control" required>
                            <option value="repair">{{ translate('Repair') }}</option>
                            <option value="replace">{{ translate('Replace') }}</option>
                            <option value="reject">{{ translate('Reject') }}</option>
                        </select>
                    </div>

                    <!-- Tamper Detected -->
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="tamper_detected" value="1" id="tamperCheckbox">
                            {{ translate('Tamper Detected') }}
                        </label>
                    </div>

                    <!-- Inspection Fee -->
                    <div class="form-group" id="inspectionFeeGroup" style="display:none;">
                        <label>{{ translate('Inspection Fee') }}</label>
                        <input type="number" name="inspection_fee" id="inspectionFeeInput" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>

                    <!-- Repair Fee -->
                    <div class="form-group" id="repairFeeGroup" style="display:block;">
                        <label>{{ translate('Repair Fee') }}</label>
                        <input type="number" name="repair_fee" id="repairFeeInput" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>

                    <div id="replaceOptions" style="display:none;">

                        <div class="form-group">
                            <label>{{ translate('Replacement Pricing Type') }}</label>
                            <select name="replacement_fee_option" id="replacementFeeOption" class="form-control" required>
                                <option value="free">{{ translate('Free Replacement') }}</option>
                                <option value="fee_required">{{ translate('Fee Required') }}</option>
                            </select>
                        </div>

                        <!-- Replacement Fee (only if fee_required) -->
                        <div class="form-group" id="replacementFeeGroup" style="display:none;">
                            <label>{{ translate('Replacement Fee') }}</label>
                            <input type="number" name="replacement_fee" id="replacementFeeInput" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>

                        <!-- Coverage Mode -->
                        <div class="form-group">
                            <label>{{ translate('Coverage Mode for New Warranty') }}</label>
                            <select name="replacement_mode" id="replacementModeSelect" class="form-control" required>
                                <option value="remaining">{{ translate('Remaining Term') }}</option>
                                <option value="full">{{ translate('Full Term') }}</option>
                            </select>
                        </div>
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
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('diagnoseModal');
    if (!modal) return;

    const actionSelect = modal.querySelector('#actionSelect');
    const tamperCheckbox = modal.querySelector('#tamperCheckbox');
    const inspectionGroup = modal.querySelector('#inspectionFeeGroup');
    const repairFeeGroup = modal.querySelector('#repairFeeGroup');
    const replaceOptions = modal.querySelector('#replaceOptions');
    const feeOptionSelect = modal.querySelector('#replacementFeeOption');
    const replacementFeeGroup = modal.querySelector('#replacementFeeGroup');
    const inspectionFeeInput = modal.querySelector('#inspectionFeeInput');
    const repairFeeInput = modal.querySelector('#repairFeeInput');
    const replacementFeeInput = modal.querySelector('#replacementFeeInput');
    const replacementModeSelect = modal.querySelector('#replacementModeSelect');

    function toggleFields() {
        const action = actionSelect.value;
        const isTamper = tamperCheckbox.checked;
        const isRepair = action === 'repair';
        const isReplace = action === 'replace';
        const isReject = action === 'reject';
        const feeOption = feeOptionSelect.value;
        const isReplacementFeeRequired = isReplace && feeOption === 'fee_required';
        const showInspectionFee = isTamper && !isReject;

        // Tamper fee is only relevant when action is repair/replace.
        inspectionGroup.style.display = showInspectionFee ? 'block' : 'none';
        inspectionFeeInput.disabled = !showInspectionFee;
        if (!showInspectionFee) {
            inspectionFeeInput.value = '';
        }

        // Repair → repair fee
        repairFeeGroup.style.display = isRepair ? 'block' : 'none';
        repairFeeInput.disabled = !isRepair;
        if (!isRepair) {
            repairFeeInput.value = '';
        }

        // Replace → show pricing + mode
        replaceOptions.style.display = isReplace ? 'block' : 'none';
        feeOptionSelect.disabled = !isReplace;
        feeOptionSelect.required = isReplace;
        replacementModeSelect.disabled = !isReplace;
        replacementModeSelect.required = isReplace;

        replacementFeeGroup.style.display = isReplacementFeeRequired ? 'block' : 'none';
        replacementFeeInput.disabled = !isReplacementFeeRequired;
        if (!isReplacementFeeRequired) {
            replacementFeeInput.value = '0';
        }
    }

    actionSelect.addEventListener('change', toggleFields);
    tamperCheckbox.addEventListener('change', toggleFields);
    feeOptionSelect.addEventListener('change', toggleFields);

    modal.addEventListener('shown.bs.modal', toggleFields);

    modal.addEventListener('hidden.bs.modal', function () {
        modal.querySelector('form').reset();
        toggleFields();
    });
});
</script>
@endpush
