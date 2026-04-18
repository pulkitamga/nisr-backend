<div class="modal fade" id="showBranchesStockModal" data-backdrop="static" tabindex="-1" aria-labelledby="showBranchesStockModal"
     aria-hidden="true">
    <div class="model-lg modal-dialog modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2 d-flex">
            	<h3>{{ __('Select_Branch') }}</h3>
                <button type="button" class="radius-50 btn-close border-0" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                	<i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
            	<div class="table-responsive">
            		<form id="branchesStockForm" action="{{ route('admin.stock-request.stock-request-update') }}" method="POST">
            			@csrf
            			<input type="hidden" name="product_id" id="product_id">
                        <input type="hidden" name="request_id" id="request_id">
		                <table id="datatable" class="table table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
		                    <thead class="thead-light thead-50 text-capitalize">
		                    <tr>
		                        <th class="text-center">{{ translate('SL') }}</th>
		                        <th class="text-center">{{ translate('Select') }}</th>
		                        <th class="">{{ translate('Branch_Name') }}</th>
		                        <th class="text-center">{{ translate('Available_QTY') }}</th>
		                        <th class="">{{ translate('Branch_Address') }}</th>
		                    </tr>
		                    </thead>
		                    <tbody> 
		                        
		                    </tbody>
		                </table>
		                <div class="text-end mt-3 justify-centent-end">
                            <button type="submit" class="btn btn-xs btn-primary">{{ __('Transfer Stock') }}</button>
                        </div>
		            </form>
	            </div>
            </div>
        </div>
    </div>
</div>

