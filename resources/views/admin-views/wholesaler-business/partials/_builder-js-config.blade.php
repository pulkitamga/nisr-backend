@php
    $builderConfig = [
        'mode' => $mode ?? 'create',
        'formId' => 'quotation-form',
        'checkOrderUrl' => route('admin.wholesale.business.check-order-no'),
        'requireWholesalerSelection' => $requireWholesalerSelection ?? false,
        'loadDealIdFromQuery' => $loadDealIdFromQuery ?? false,
        'summaryNumberTargetId' => $summaryNumberTargetId ?? null,
        'texts' => [
            'notAvailable' => translate('N/A'),
            'searchProductPlaceholder' => translate('Search for a product...'),
            'selectWholesalerPlaceholder' => translate('Select Wholesaler'),
            'remove' => translate('Remove'),
            'chargeName' => translate('Charge Name'),
            'discountName' => translate('Discount Name'),
            'value' => translate('Value'),
            'chargeValue' => translate('charge_value'),
            'discountValue' => translate('Discount Value'),
            'quotationExists' => translate('Quotation No already exists'),
            'quotationAvailable' => translate('Quotation No is available'),
            'chooseWholesalerFirst' => translate('Please select a wholesaler first.'),
            'duplicateVariation' => translate('This variation is already added.'),
            'duplicateProductVariation' => translate('This product variation is already added.'),
            'emptyState' => translate('No product selected'),
            'validationTitle' => translate('Please fill all required fields'),
            'field' => translate('Field'),
            'required' => translate('is required.'),
            'confirmTitle' => translate('Are you sure?'),
            'confirmSubmit' => translate('Do you want to submit?'),
            'submit' => translate('Submit'),
            'cancel' => translate('Cancel'),
            'ok' => translate('OK'),
            'oops' => translate('Oops...'),
        ],
    ];
@endphp

<script>
    window.wholesaleBuilderConfig = @json($builderConfig);
</script>
