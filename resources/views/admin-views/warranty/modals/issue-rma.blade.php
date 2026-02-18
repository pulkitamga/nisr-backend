@php
$branches = \App\Models\Branch::where('id','!=',1)->get();
@endphp


<div class="modal fade" id="issueRmaModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="claim-modal-form">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Issue RMA') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ translate('RMA Number (auto-generated if empty)') }}</label>
                        <input type="text" name="rma_number" class="form-control" placeholder="{{ translate('Leave empty for auto') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Return Branch') }}</label>
                        <select name="branch_id" class="form-control" required>
                            <option value="">{{ translate('Select Branch') }}</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ translate('Return Deadline (days)') }}</label>
                        <input type="number" name="return_days" class="form-control" value="7" min="1">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Instructions') }}</label>
                        <textarea name="instructions" class="form-control" rows="3">{{ translate('Return the product to the nearest branch...') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn--primary">{{ translate('Issue RMA') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>