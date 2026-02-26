<div class="modal right fade"  id="convertModal" tabindex="-1" role="dialog" aria-labelledby="convertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-slideout modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="convertModalLabel">{{ translate('Convert') }}</h5>
                <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="Close">
                    <i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="convertForm" action="{{ route('admin.crm.convert-inquiry') }}" method="POST">
                    @csrf
                    <input type="hidden" name="message_id" id="convertMessageId">
                    <div class="mb-3">
                        <label for="typeSelect" class="form-label">{{ translate('Select Type') }}</label>
                        <select class="form-control" id="typeSelect" name="type">
                            <option value="">-- {{ translate('Select Type') }} --</option>
                            <option value="lead">{{ translate('Lead') }}</option>
                            <option value="ticket">{{ translate('Ticket') }}</option>
                            <option value="warranty">{{ translate('Warranty') }}</option>
                        </select>
                    </div>

                    <div class="mb-3" id="subTypeWrapper" style="display: none;">
                        <label for="subTypeSelect" class="form-label">{{ translate('Select Sub-Type') }}</label>
                        <select class="form-control" id="subTypeSelect" name="sub_type">
                            <option value="">-- {{ translate('Select Sub-Type') }} --</option>
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
                            <option value="medium" selected>{{ translate('medium') }}</option>
                            <option value="urgent">{{ translate('urgent') }}</option>
                            <option value="high">{{ translate('high') }}</option>
                            <option value="low">{{ translate('low') }}</option>
                        </select>
                    </div>

                    <div class="mb-3" id="departmentWrapper">
                        <label for="department-id" class="form-label">{{ translate('Select Department') }}</label>
                        <select class="form-control" name="department_id" id="department-id">
                            <option value="">-- {{ translate('Select Department') }} --</option>
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
