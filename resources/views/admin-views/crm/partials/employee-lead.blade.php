<div class="modal fade" id="showEmployeeModal" data-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2 d-flex">
                <h3>{{ translate('Assign Department & Employee') }}</h3>
                <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="Close">
                    <i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
                <form id="updateTicketEmployeeForm" action="{{ route('admin.crm.lead.employee-assign') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ticket_id" id="modal_ticket_id">
                    @if(auth('admin')->id() == 1)
                    <div class="form-group">
                        <label>{{ translate('Select Department') }}</label>
                        <select class="form-control" name="department_id" id="ticket-department-id">
                            <option value="">{{ translate('Select Department') }}</option>
                            @foreach($getDepartment as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="form-group mt-2">
                        <label>{{ translate('Select Employee') }}</label>
                        <select class="form-control" name="employee_id" id="ticket-employee-id">
                            <option value="">{{ translate('Select Employee') }}</option>
                        </select>
                    </div>

                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-xs btn-primary">{{ translate('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
