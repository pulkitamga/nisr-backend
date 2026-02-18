<!-- Link Order Modal -->
<div class="modal fade" id="linkOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    Link Order to Deal  <span id="modal-deal-id" class="text-primary"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    Select an order for: <strong id="modal-user-name" class="text-info"></strong>
                </p>
                <div id="orders-list" class="border rounded p-3 bg-light">
                    <!-- Orders load here -->
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>