<div class="modal right fade"  id="convertModal" tabindex="-1" role="dialog" aria-labelledby="convertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-slideout modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="convertModalLabel">{{ translate('Convert') }}</h5>
                <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="convertForm" action="{{ route('admin.crm.convert-inquiry') }}" method="POST">
                    @csrf
                    <input type="hidden" name="message_id" id="convertMessageId">
                    <div class="mb-3">
                        <label for="typeSelect" class="form-label">{{ translate('select_type') }}</label>
                        <select class="form-control" id="typeSelect" name="type">
                            <option value="">-- {{ translate('select_type') }} --</option>
                            <option value="lead">{{ translate('Lead') }}</option>
                            <option value="ticket">{{ translate('Ticket') }}</option>
                        </select>
                    </div>

                    <div class="mb-3" id="subTypeWrapper" style="display: none;">
                        <label for="subTypeSelect" class="form-label">{{ translate('select_sub_type') }}</label>
                        <select class="form-control" id="subTypeSelect" name="sub_type">
                            <option value="">-- {{ translate('select_sub_type') }} --</option>
                        </select>
                    </div>

                    <div class="mb-3" id="reasonWrapper" style="display: none;">
                        <label for="reasonSelect" class="form-label">{{ translate('Select Ticket Reason') }}</label>
                        <select class="form-control" id="reasonSelect" name="reason">
                            <option value="">-- {{ translate('Select Reason') }} --</option>
                        </select>
                    </div>

                    <div class="mb-3" id="priorityWrapper">
                        <label for="prioritySelect" class="form-label">{{ translate('Select Priority') }}</label>
                        <select class="form-control" id="prioritySelect" name="priority">
                            <option value="medium" selected>{{ translate('Medium') }}</option>
                            <option value="urgent">{{ translate('Urgent') }}</option>
                            <option value="high">{{ translate('High') }}</option>
                            <option value="low">{{ translate('Low') }}</option>
                        </select>
                    </div>

                    <div class="mb-3" id="departmentWrapper">
                        <label for="convertDepartmentId" class="form-label">{{ translate('Select_Department') }}</label>
                        <select class="form-control" name="department_id" id="convertDepartmentId">
                            <option value="">-- {{ translate('Select_Department') }} --</option>
                            @foreach ($getDepartment as $dept)
                            <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="submit" form="convertForm" class="btn btn-primary">{{ translate('Save') }}</button>
            </div>
        </div>
    </div>
</div>

