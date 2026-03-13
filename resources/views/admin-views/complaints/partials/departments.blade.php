<div class="modal fade" id="showDepartmentsModal" data-backdrop="static" tabindex="-1" aria-labelledby="showDepartmentsModal"
     aria-hidden="true">
    <div class="model-sm modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2 d-flex">
            	<h3>{{ __('Select Department') }}</h3>
                <button type="button" class="radius-50 btn-close border-0" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                	<i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
        		<form id="updateTicketDepartmentForm" action="{{ route('admin.complaints.update-ticket-department') }}" method="POST">
        			@csrf
        			<input type="hidden" name="ticket_id" id="ticket_id">
        			<input type="hidden" name="employee_id" id="employee_id" value="0">
	                <div class="row mt-3">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="form-group">
                                <select class="js-select2-custom form-control" name="department_id" id="department-id">
                                    <option value="0" selected
                                            disabled>{{ translate('select_department') }}</option>
                                    @foreach ($getDepartment as $dept)
                                        <option value="{{ $dept['id'] }}">
                                            {{ $dept['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
	                <div class="text-end mt-2 justify-centent-end">
                        <button type="submit" class="btn btn-xs btn-primary">{{ __('Update Department') }}</button>
                    </div>
	            </form>
            </div>
        </div>
    </div>
</div>

