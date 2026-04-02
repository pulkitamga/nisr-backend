@php
    $wholesaleListConfig = [
        'csrfToken' => csrf_token(),
        'reopenApprovalModalId' => $reopenApprovalModalId ?? old('id'),
        'routes' => [
            'toggleMoq' => route('admin.wholesale.business.toggle.moq'),
            'orderNumberCheck' => route('admin.wholesale.business.order.check-number'),
            'confirmInvoiceCheck' => route('admin.wholesale.business.order.check-confirm-invoice-no'),
            'historyTemplate' => route('admin.wholesale.business.ajax-activity-history', ['order' => ':id']),
            'productToggleTemplate' => route('admin.wholesale.product.toggle-status', ['id' => '__id__']),
        ],
        'text' => [
            'somethingWentWrong' => translate('Something went wrong!'),
            'confirmDeletionTitle' => $confirmDeletionTitle ?? translate('Confirm Deletion'),
            'confirmDeletionText' => $confirmDeletionText ?? translate('Are you sure you want to delete this order?'),
            'yesDeleteIt' => translate('Yes, delete it!'),
            'cancel' => translate('Cancel'),
            'orderNumberExists' => translate('Order number already exists'),
            'orderNumberAvailable' => translate('Order number available'),
            'invoiceNumberExists' => translate('Invoice number already exists'),
            'invoiceNumberAvailable' => translate('Invoice number available'),
            'confirmOrderNumberExists' => translate('Confirm order number already exists'),
            'confirmOrderNumberAvailable' => translate('Confirm order number available'),
        ],
    ];
@endphp
<script>
    window.wholesaleListConfig = @json($wholesaleListConfig);
</script>
