

<div class="modal fade" id="resumeClaimModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="claim-modal-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Resume Claim') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <p>{{ translate('Move the claim back to the previous logical step.') }}</p>
                    <div class="form-group">
                        <label>{{ translate('Target Status') }}</label>
                        <select name="target_status" class="form-control" required>
                            @if($claim && $claim->status === 'waiting_customer')
                                <option value="received">{{ translate('Received') }}</option>
                            @endif
                            @if($claim && $claim->status === 'waiting_parts')
                                <option value="repair_pending">{{ translate('repair_pending') }}</option>
                            @endif
                            @if($claim && $claim->status === 'waiting_payment')
                                <option value="diagnosis_pending">{{ translate('diagnosis_pending') }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('resume') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
