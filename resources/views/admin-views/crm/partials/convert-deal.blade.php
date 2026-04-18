<div class="modal right fade" id="convertLeadModal" tabindex="-1" role="dialog" aria-labelledby="convertLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-slideout modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header bg-section">
                <h3 class="mb-0">{{ translate('Convert Lead to Deal') }}</h3>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="convertForm" action="{{ route('admin.crm.lead.convert-to-deal') }}" method="post">
                    @csrf
                    <input type="hidden" name="lead_id" id="lead_id">

                    <div class="mb-3">
                        <label>{{ translate('Party_Type') }}</label>
                        <select class="form-control" name="party_type" id="party_type">
                            <option value="company">{{ translate('Wholesale (Company)') }}</option>
                            <option value="contact">{{ translate('Retail (Contact)') }}</option>
                        </select>
                    </div>

                    <div class="mb-3 position-relative">
                        <label>{{ translate('Search Party') }}</label>
                        <input type="text" class="form-control" name="party_search_input" id="party_search_input" placeholder="{{ translate('Type to search...') }}">
                        <input type="hidden" name="party_id" id="party_id">
                        <ul class="custom-select2-dropdown" id="party_search_results"></ul>
                    </div>

                    <!-- 👇 This appears only for retail users -->
                    <div class="mb-3" id="order-section" style="display: none;">
                        <label>{{ translate('Select Order') }}</label>
                        <select class="form-control" name="order_id" id="order_id">
                            <option value="">{{ translate('Select Order') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>{{ translate('Deal Value') }}</label>
                        <input type="number" class="form-control" name="value">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">{{ translate('Convert') }}</button>
                </form>
            </div>


        </div>
    </div>
</div>

