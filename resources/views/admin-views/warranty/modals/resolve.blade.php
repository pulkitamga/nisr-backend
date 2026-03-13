
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <form  method="POST" class="claim-modal-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Mark Claim Resolved') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <p>{{ translate('Confirm the item has been delivered/collected and the claim is resolved.') }}</p>
                    <div class="form-group">
                        <label>{{ translate('Resolution Notes') }}</label>
                        <textarea name="resolution_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('Resolve') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>