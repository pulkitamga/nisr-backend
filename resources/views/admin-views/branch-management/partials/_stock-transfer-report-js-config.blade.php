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
            'filter' => translate('Filter'),
            'failedToLoad' => translate('failed_to_load_report_data'),
            'noData' => translate('no_Data_found'),
            'pending' => translate('Pending'),
            'transferred' => translate('transferred'),
            'approved' => translate('Approved'),
            'rejected' => translate('rejected'),
            'from' => translate('From'),
            'to' => translate('To'),
        ],
    ];
@endphp
<script>
    window.branchStockTransferReportConfig = @json($branchStockTransferReportConfig);
</script>
