@php
$branches = \App\Models\Branch::where('id','!=',1)->get();
@endphp


<div class="modal fade" id="receiveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="" class="claim-modal-form" id="receiveForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header  text-white">
                    <h5 class="modal-title">{{ translate('Receive RMA Item') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-danger font-weight-bold">
                                    {{ translate('Enter Serial Number') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="serial_number" class="form-control" 
                                       placeholder="e.g. ABC123XYZ" required autocomplete="off">
                                <small class="text-muted">{{ translate('Must match RMA issued serial') }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-danger font-weight-bold">
                                    {{ translate('Select Receiving Branch') }} <span class="text-danger">*</span>
                                </label>
                                <select name="branch_id" class="form-control" required>
                                    <option value="">{{ translate('-- Select Branch --') }}</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->branch_name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">{{ translate('Must be the same as RMA branch') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>{{ translate('Received Notes (Condition, Accessories, etc.)') }}</label>
                        <textarea name="received_notes" class="form-control" rows="3" 
                                  placeholder="{{ translate('Item received in good condition with box and charger...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn--primary">
                        <i class="tio-check"></i> {{ translate('Confirm Receive') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>