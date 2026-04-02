@php
    $branchStockTransferReportConfig = [
        'csrfToken' => csrf_token(),
        'routes' => [
            'data' => route('admin.stock.transfer-report-data'),
            'excel' => route('admin.stock.transfer-report-export-excel'),
            'pdf' => route('admin.stock.transfer-report-export-pdf'),
        ],
        'text' => [
            'loading' => translate('Loading...'),
            'filter' => translate('filter'),
            'failedToLoad' => translate('failed_to_load_report_data'),
            'noData' => translate('no_data_found'),
            'pending' => translate('pending'),
            'approved' => translate('approved'),
            'rejected' => translate('rejected'),
            'from' => translate('from'),
            'to' => translate('to'),
        ],
    ];
@endphp
<script>
    window.branchStockTransferReportConfig = @json($branchStockTransferReportConfig);
</script>
