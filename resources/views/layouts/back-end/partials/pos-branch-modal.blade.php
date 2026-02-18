
<!-- Bootstrap JS (must be placed after jQuery if you use it) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Branch Select Modal -->
<div class="modal fade" id="branchSelectModal" tabindex="-1" aria-labelledby="branchSelectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="branchSelectModalLabel">Select Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="posBranchForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="posBranchId" class="form-label">Branch</label>
                        <select id="posBranchId" class="form-select" required>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Go to POS</button>
                </div>
            </form>
        </div>
    </div>
</div>
