<div class="modal fade" id="showDepartmentsModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="showDepartmentsModal"
    aria-hidden="true">
    <div class="model-sm modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2 d-flex">
                <h3>{{ translate('Select Department') }}</h3>
                <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
                <form id="updateTicketDepartmentForm" method="POST">
                    @csrf
                    <input type="hidden" name="ticket_id" id="depart_ticket_id">
                    <input type="hidden" name="employee_id" id="employee_id" value="0">

                    {{-- Department Selection --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="department-id">{{ translate('Select Department') }}</label>
                                <select class="js-select2-custom form-control" name="department_id" id="department-id" required>
                                    <option value="" selected disabled>{{ translate('select_department') }}</option>
                                    @foreach ($getDepartment as $dept)
                                    <option value="{{ $dept['id'] }}">
                                        {{ $dept['name'] }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Priority Selection --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="priority">{{ translate('Priority') }}</label>
                                <select class="form-control" name="priority" id="priority" required>
                                    <option value="" disabled selected>{{ translate('Select Priority') }}</option>
                                    <option value="low">{{ translate('Low') }}</option>
                                    <option value="medium">{{ translate('Medium') }}</option>
                                    <option value="high">{{ translate('High') }}</option>
                                    <option value="urgent">{{ translate('Urgent') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Extra Message Box --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="admin-message">{{ translate('Message (Optional)') }}</label>
                                <textarea class="form-control" name="reply" id="admin-message" rows="3" placeholder="{{ translate('Enter message if any...') }}"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-xs btn-primary">{{ translate('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

