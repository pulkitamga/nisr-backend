<div class="modal fade" id="showOwnerModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2 d-flex">
                <h3>Assign Owner</h3>
                <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="Close">
                    <i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
                <form id="updateTicketOwnerForm"  method="POST">
                    @csrf
                    <input type="hidden" name="ticket_id" id="owner_ticket_id">
                    <div class="form-group mt-2">
                        <label>{{ translate('Select Employee') }}</label>
                        <select class="form-control" name="employee_id" id="owner-employee-id">
                            <option value="" disabled>{{ translate('Select Employee') }}</option>
                            @foreach ($employees as $employee)
                            <option value="{{ $employee['id'] }}">
                                {{ $employee['name'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-xs btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>