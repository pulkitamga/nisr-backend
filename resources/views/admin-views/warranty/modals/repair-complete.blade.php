<div class="modal fade" id="repairCompleteModal" tabindex="-1">
    <div class="modal-dialog">
        <form  method="POST" class="claim-modal-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Complete Repair') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ translate('Labor Notes') }}</label>
                        <textarea name="labor_notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Parts Used (comma separated)') }}</label>
                        <input type="text" name="parts_used" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('Complete') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>