<div class="modal fade" id="showTypeModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="showTypeModal"
    aria-hidden="true">
    <div class="model-sm modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2 d-flex">
                <h3>{{ translate('Select Type') }}</h3>
                <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
                <form id="updateTypeForm" action="{{ route('admin.crm.update-massage-type') }}" method="POST">
                    @csrf
                    <input type="hidden" name="message_id" id="message_id">

                    <div class="row mt-3">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="form-group">
                                <select class="js-select2-custom form-control" name="message_type" id="type-id">
                                    <option value="" selected disabled>{{ translate('select_type') }}</option>
                                    <option value="support">{{ translate('Support') }}</option>
                                    <option value="service">{{ translate('Service') }}</option>
                                    <option value="career">{{ translate('Career') }}</option>
                                    <option value="warranty">{{ translate('Warranty') }}</option>
                                    <option value="contact">{{ translate('Contact') }}</option>

                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-2 justify-centent-end">
                        <button type="submit" class="btn btn-xs btn-primary">{{ translate('Update Type') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

