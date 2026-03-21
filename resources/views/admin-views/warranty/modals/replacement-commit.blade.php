<div class="modal fade" id="replacementCommitModal" tabindex="-1">
    <div class="modal-dialog">
        <form  method="POST" class="claim-modal-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Commit Replacement') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ translate('New Serial Number') }}</label>
                        <div class="input-group">
                            <input type="text" id="replacementCommitSerialNumber" name="new_serial_number" class="form-control" required>
                            <div class="input-group-append">
                                @include('partials.serial-scan-button', ['targetInput' => '#replacementCommitSerialNumber'])
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('Commit') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
