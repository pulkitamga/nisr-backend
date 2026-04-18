<div class="modal fade" id="showBranchModal" data-backdrop="static" tabindex="-1" aria-labelledby="showBranchModal"
     aria-hidden="true">
    <div class="model-sm modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2 d-flex">
            	<h3>{{ __('Select_Branch') }}</h3>
                <button type="button" class="radius-50 btn-close border-0" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                	<i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
        		<form id="updateEmployeeBranchForm" action="{{ route('admin.employee.update-employee-branch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="employee_id" id="branch-employee-id">
                    <div class="row mt-3">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="form-group">
                                <select class="js-select2-custom form-control" name="branch_id[]" id="branch-id" multiple required>
                                    @foreach ($branches as $branch)
                                        @if($branch['id'] != 1)
                                        <option value="{{ $branch['id'] }}">
                                            {{ $branch['branch_name'] }}
                                        </option>
                                        @endif
                                    @endforeach
                                </select>
                                
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-2 justify-centent-end">
                        <button type="submit" class="btn btn-xs btn-primary">{{ __('Update Branch') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

