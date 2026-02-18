<div class="modal fade" id="showBranchesStockModal" data-backdrop="static" tabindex="-1" aria-labelledby="showBranchesStockModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px;">
		<div class="modal-content">
			<div class="modal-header border-0 pb-2 d-flex justify-content-between">
				<h3>Select Branch & Upload Serials (Warranty)</h3>  <div>
                        <a href="{{ asset('sample.csv') }}" class="btn btn-primary" download>
                            {{ translate('Download_Sample_Csv') }}
                        </a>
                    </div>

				<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
					<i class="tio-clear"></i>
				</button>
			</div>
			<div class="modal-body pt-0">

				<div id="csv-error-container"></div>
				<form id="branchesStockForm" action="{{ route('admin.stock-request.stock-request-update') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<input type="hidden" name="product_id" id="product_id">
					<input type="hidden" name="request_id" id="request_id">

					<!-- Warranty Info -->
					<div class="alert alert-info d-none" id="warranty-alert">
						<strong>Warranty Product:</strong> Upload CSV with <span id="required-qty"></span> serials.
					</div>

					<div class="table-responsive">
						<table class="table table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
							<thead class="thead-light thead-50 text-capitalize">
								<tr>
									<th class="text-center">SL</th>
									<th class="text-center">Select</th>
									<th>Branch Name</th>
									<th class="text-center">Available QTY</th>
									<th>Branch Address</th>
								</tr>
							</thead>
							<tbody id="branches-tbody"></tbody>
						</table>
					</div>

					<!-- CSV Upload (Only for Warranty) -->
					<div class="mt-3 csv-upload-section" id="csv-upload-section" style="display:none;">
						<label class="form-label">
							<strong>Upload Serials CSV</strong>
							<small class="text-muted d-block">One serial per line. Must match requested quantity.</small>
						</label>
						<input type="file" name="serial_csv" class="form-control" accept=".csv">
					</div>

					<div class="text-end mt-3">
						<button type="submit" class="btn btn-primary">Transfer Stock</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>