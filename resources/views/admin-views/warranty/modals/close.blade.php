<div class="modal fade" id="closeModal" tabindex="-1">
    <div class="modal-dialog">
        <form  method="POST" class="claim-modal-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Close Claim') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ translate('Resolution Notes') }}</label>
                        <textarea name="resolution_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('Close Claim') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>