<div class="modal fade" id="dispatchModal" tabindex="-1">
    <div class="modal-dialog">
        <form  method="POST" class="claim-modal-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Dispatch Item') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ translate('Dispatch Mode') }}</label>
                        <select name="dispatch_mode" class="form-control" required>
                            <option value="pickup">{{ translate('pickup') }}</option>
                            <option value="ship">{{ translate('Ship') }}</option>
                        </select>
                    </div>
                    <div class="form-group" style="display: none;">
                        <label>{{ translate('Tracking Number') }}</label>
                        <input type="text" name="tracking_number" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('Dispatch') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
