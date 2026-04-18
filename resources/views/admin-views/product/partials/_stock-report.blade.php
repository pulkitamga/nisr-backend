<div class="modal-header">
    <h5 class="modal-title">{{ translate('stock_report') }}</h5>
    <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body">
    @include('admin-views.product.partials._stock-report-content')
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        {{ translate('Close') }}
    </button>
</div>

