<div class="modal fade" id="decideModal" tabindex="-1">
    <div class="modal-dialog">
        <form  method="POST" class="claim-modal-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Decide Claim') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>

                <div class="modal-body">

                    {{-- Decision --}}
                    <div class="form-group">
                        <label>{{ translate('Decision') }}</label>
                        <select name="decision" class="form-control" required>
                            <option value="approve">{{ translate('Approve') }}</option>
                            <option value="reject">{{ translate('Reject') }}</option>
                            <!-- <option value="waiting_customer">{{ translate('Waiting Customer') }}</option> -->
                        </select>
                    </div>

                    {{-- Reason Code --}}
                    <div class="form-group">
                        <label>{{ translate('reason_code') }}</label>
                        <input type="text" name="reason_code"
                               class="form-control"
                               placeholder="e.g. DEFECT-001"
                               maxlength="50"
                               required>
                    </div>

                    {{-- Reason Message --}}
                    <div class="form-group">
                        <label>{{ translate('Reason Message') }}</label>
                        <textarea name="reason_message"
                                  class="form-control"
                                  rows="3"
                                  required></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">
                        {{ translate('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn--primary">
                        {{ translate('Submit') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('decideModal');
        if (!modal) return;

        // Reset form when modal is hidden
        modal.addEventListener('hidden.bs.modal', function () {
            modal.querySelector('form').reset();
        });
    });
</script>
@endpush
