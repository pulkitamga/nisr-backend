<div class="modal fade" id="showFollowUpModal" data-backdrop="static" tabindex="-1" aria-labelledby="showFollowUpModal"
     aria-hidden="true">
    <div class="model-sm modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2 d-flex">
            	<h3>Follow Up Details</h3>
                <button type="button" class="radius-50 btn-close border-0" data-dismiss="modal" aria-label="Close">
                	<i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
        		<form id="updateTicketFollowUpForm" action="{{ route('admin.complaints.update-ticket-follow-up') }}" method="POST">
        			@csrf
        			<input type="hidden" name="ticket_id" id="follow-up-ticket-id">
                    <input type="hidden" name="department_id" id="follow-up-department-id">
                    <input type="hidden" name="employee_id" id="follow-up-employee-id">
	                <div class="row mt-3">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="form-group">
                            	<label class="control-label" for="follow-up-status">{{ translate('select_ticket_status') }} </label>
                                <select class="js-select2-custom form-control" name="ticket-follow-up-status" id="ticket-follow-up-status">
                                    <option value="0" selected disabled>{{ translate('select_ticket_status') }}</option>
                                    @foreach ($aInProgressStatus as $status)
                                    	<option value="{{ $status['id'] }}" data-status-name="{{ strtolower($status['name'] ?? '') }}">{{ translate($status['name'])}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                
                    <div class="row d-none" id="ticket-next-follow-up-date-row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                        	<div class="form-group">
	                        	<label class="control-label" for="follow-up-note">{{ translate('Next_Follow_Up_Date') }} </label>
	                            <input type="date" name="ticket-next-follow-up-date" value="" id="ticket-next-follow-up-date" class="form-control">
	                        </div>
                        </div>
                    </div>
                    <div class="row d-none" id="ticket-remainder-days-after-row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="form-group">
                                <label class="control-label" for="ticket-remainder-days-after">{{ translate('When_remainder_day') }}  ( {{ translate('in_day') }} ) </label>
                                <input type="number" name="ticket-remainder-days-after" value="" id="ticket-remainder-days-after" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row d-none" id="ticket-remainder-interval-row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="form-group">
                                <label class="control-label" for="ticket-remainder-interval">{{ translate('remainder_interval') }} ( {{ translate('in_hrs') }} ) </label>
                                <input type="number" name="ticket-remainder-interval" value="" id="ticket-remainder-days-after" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row d-none" id="ticket-remainder-cycle-row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="form-group">
                                <label class="control-label" for="ticket-remainder-cycle">{{ translate('remainder_cycle') }} </label>
                                <input type="number" name="ticket-remainder-cycle" value="" id="ticket-remainder-cycle" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                        	<div class="form-group">
	                        	<label class="control-label" for="follow-up-note">{{ translate('Note') }} </label>
	                            <div class="form-group">
	                                <textarea rows="3" class="form-control" name="ticket-follow-up-note" id="ticket-follow-up-note" placeholder="{{translate('enter_follow_up_note')}}"></textarea>
	                            </div>
	                        </div>
                        </div>
                    </div>
	                <div class="text-end mt-2 justify-centent-end">
                        <button type="submit" class="btn btn-xs btn-primary">Update Follow Up</button>
                    </div>
	            </form>
            </div>
        </div>
    </div>
</div>
