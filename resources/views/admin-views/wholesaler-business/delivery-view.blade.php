@extends('layouts.back-end.app')
@section('title', translate('delivery_details'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path:'public/assets/back-end/css/owl.min.css') }}">
<style>
    #addDeliveryModal .modal-content {
        border-radius: 1rem;
        border: none;
        background: #fdfdfd;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    /* Modal Header */
    #addDeliveryModal .modal-header {
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    #addDeliveryModal .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
    }

    #addDeliveryModal .modal-header button {
        background-color: rgba(255, 255, 255, 0.9);
        color: #333;
        font-size: 1.1rem;
        border-radius: 50%;
        padding: 0.2rem 0.5rem;
        transition: 0.2s ease;
    }

    #addDeliveryModal .form-control,
    #addDeliveryModal .form-select,
    #addDeliveryModal .form-control-plaintext {
        border-radius: 0.5rem;
        border-color: #e5e7eb;
        padding: 0.5rem 0.75rem;
    }

    #addDeliveryModal .form-label {
        font-weight: 600;
        color: #374151;
    }

    #addDeliveryModal textarea.form-control {
        resize: vertical;
    }


    /* Error Message */
    #addDeliveryModal #quantityError {
        font-size: 0.875rem;
        font-weight: 500;
    }

    #addDeliveryModal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
        padding-inline-end: 1rem;
    }


    #addDeliveryModal .modal-dialog {
        max-width: 700px;
        width: 90%;
        margin: auto;
    }

    @media (max-width: 576px) {
        #addDeliveryModal .modal-dialog {
            max-width: 95%;
            margin: 1rem auto;
        }

        #addDeliveryModal .modal-body {
            padding: 1rem;
        }
    }
</style>


@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="content container-fluid">
    <div class="d-print-none pb-2">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                    <img width="20" src="{{ dynamicAsset(path:'public/assets/back-end/img/add-new-seller.png') }}"
                        alt="">
                    {{ translate('wholesaler_details') }}
                </h2>
            </div>
        </div>
    </div>


    @if(session('error_csv'))
    @php
    $errorCount = session('error_count', 'several');
    @endphp
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>{{ translate('Transfer failed!') }}</strong>
        {{ $errorCount }} {{ $errorCount == 1 ? translate('serial') : translate('serials') }} {{ $errorCount == 1 ? translate('is') : translate('are') }} {{ translate('invalid.') }}
        <a href="{{ route('admin.stock-transfer.download-error-csv', session('error_csv')) }}"
            class="btn btn-sm btn-warning ms-2">
            {{ translate('Download Error Report') }}
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ translate('Close') }}">&times;</button>
    </div>
    @endif

    <div class="row g-2 h-100">
        <div class="col-12">
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ translate('Order_Delivery') }}</h4>
                    <div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 bg-white shadow-sm rounded">
                            <thead class="bg-light text-muted border-bottom">
                                <tr class="text-nowrap">
                                    <th class="fw-semibold">{{ translate('sl') }}</th>
                                    <th class="fw-semibold">{{ translate('date') }}</th>
                                    <th class="fw-semibold">{{ translate('product_name') }}</th>
                                    <th class="fw-semibold">{{ translate('variation') }}</th>
                                    <th class="fw-semibold">{{ translate('requested_qty') }}</th>
                                    <th class="fw-semibold">{{ translate('qty_sent') }}</th>
                                    <th class="fw-semibold">{{ translate('remaining') }}</th>
                                    <th class="fw-semibold text-center">{{ translate('action') }}</th>

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deliveries as $index => $delivery)
                                <tr class="align-middle">
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="bidi-ltr d-inline-block">{{ \Carbon\Carbon::parse($delivery->created_at)->format('d/m/Y') }}</span></td>
                                    <td>{{ $delivery->product->getTranslatedField('name') ?? __('N/A') }}</td>
                                    <td>{{ $delivery->product_variation_type ?? __('No Variation') }}</td>
                                    <td>{{ $delivery->product_quantity }}</td>
                                    <td>{{ $delivery->quantity_sent }}</td>
                                    <td>{{ $delivery->remaining }}</td>

                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm open-delivery-modal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addDeliveryModal"
                                            data-product-id="{{ $delivery->product_id }}"
                                            data-order-id="{{ $delivery->confirmed_order_id }}"
                                            data-variation-type="{{ $delivery->product_variation_type ?? '' }}"
                                            data-product-name="{{ $delivery->product->getTranslatedField('name') ?? __('N/A') }}"
                                            data-requested-qty="{{ $delivery->product_quantity }}"
                                            data-sent-qty="{{ $delivery->quantity_sent }}"
                                            data-remaining="{{ $delivery->remaining }}"
                                            data-is-traceable="{{ $delivery->product->is_traceable ?? 0 }}">
                                            <i class="tio-truck me-1"></i> Add Delivery
                                        </button>

                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">{{ translate('No delivery records found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>




                    <div class="modal fade" id="addDeliveryModal" tabindex="-1" aria-labelledby="addDeliveryModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <form id="deliveryForm" method="POST" action="{{ route('admin.wholesale.business.order.delivery.store') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="modalVariationType" name="variation_type">

                                <div class="modal-content border-0 shadow-lg">
                                    <div class="modal-header d-flex justify-content-between align-items-center">
                                        <h5 class="modal-title d-flex align-items-center gap-2" id="addDeliveryModalLabel">
                                            <i class="bi bi-truck"></i> {{ translate('add_delivery') }}
                                        </h5>

                                        <a href="{{ asset('sample.csv') }}" class="btn btn-primary ms-auto" id="download_sample" download>
                                            {{ translate('Download_Sample_Csv') }}
                                        </a>

                                        <button type="button" class="close custom-close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                                            &times;
                                        </button>
                                    </div>


                                    <div class="modal-body">
                                        <input type="hidden" name="product_id" id="modalProductId">
                                        <input type="hidden" name="confirmed_order_id" id="modalConfirmedOrderId">

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">{{ translate('product') }}</label>
                                            <div class="form-control-plaintext" id="modalProductName"></div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col">
                                                <label class="form-label fw-semibold">{{ translate('requested_quantity') }}</label>
                                                <div class="form-control-plaintext" id="modalRequestedQty"></div>
                                            </div>
                                            <div class="col">
                                                <label class="form-label fw-semibold">{{ translate('already_sent') }}</label>
                                                <div class="form-control-plaintext" id="modalSentQty"></div>
                                            </div>
                                            <div class="col">
                                                <label class="form-label fw-semibold">{{ translate('remaining') }}</label>
                                                <div class="form-control-plaintext" id="modalRemainingQty"></div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="branchSelect" class="form-label fw-semibold">{{ translate('select_branch') }}</label>
                                            <select class="form-control select2" id="branchSelect" name="branch_id" required>
                                                <option value="">{{ translate('choose_branch') }}</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="branchStock" class="form-label">{{ translate('available_stock_in_selected_branch') }}</label>
                                            <input type="text" id="branchStock" class="form-control" readonly value="-" />
                                        </div>

                                        <div class="mb-3">
                                            <label for="sentQuantity" class="form-label">{{ translate('quantity_to_send') }}</label>
                                            <input type="number" class="form-control" id="sentQuantity" name="quantity_sent" min="1" required>
                                            <div id="quantityError" class="text-danger mt-1" style="display:none;">
                                                {{ translate('quantity_exceeds_branch_stock') }}
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="serialCsv" class="form-label fw-semibold">{{ translate('upload_serials_csv') }}</label>
                                            <input type="file" class="form-control" id="serialCsv" name="serial_csv" accept=".csv" required>
                                            <small class="text-muted">
                                                {{ translate('csv_format_one_serial_per_line') }}
                                            </small>
                                            <div id="csvError" class="text-danger mt-1" style="display:none;"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="remainingQtyAfterSend" class="form-label">{{ translate('remaining_quantity_after_this_delivery') }}</label>
                                            <input type="text" name="remaining_quantity" id="remainingQtyAfterSend" class="form-control" readonly value="-" />
                                        </div>

                                        <div class="mb-3">
                                            <label for="deliveryStatusSelect" class="form-label fw-semibold">{{ translate('delivery_status') }}</label>
                                            <select class="form-control" id="deliveryStatusSelect" name="delivery_status" required>
                                                <option value="partials">{{ translate('partial') }}</option>
                                                <option value="delivered">{{ translate('delivered') }}</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="note" class="form-label fw-semibold">{{ translate('note') }}</label>
                                            <textarea class="form-control" id="deliveryNote" name="note" rows="3"
                                                placeholder="{{ translate('enter_any_note_about_this_delivery') }}"></textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" id="submitDeliveryBtn" class="btn btn--primary">
                                            <i class="bi bi-box-arrow-up"></i> {{ translate('submit_delivery') }}
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="bi bi-x-circle"></i> {{ translate('cancel') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>



                </div>
            </div>
        </div>
        {{-- ✅ Delivery Log Table --}}
        <div class="table-responsive mt-4 shadow-sm rounded bg-white">
            <h5 class="px-3 pt-4 pb-2 text-muted fw-bold border-bottom">{{ translate('delivery_logs') }}</h5>
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted">
                    <tr class="text-nowrap">
                        <th class="fw-semibold">{{ translate('sl') }}</th>
                        <th class="fw-semibold">{{ translate('date') }}</th>
                        <th class="fw-semibold">{{ translate('product') }}</th>
                        <th class="fw-semibold">{{ translate('variation') }}</th>
                        <th class="fw-semibold">{{ translate('qty_sent') }}</th>
                        <th class="fw-semibold">{{ translate('branch') }}</th>
                        <th class="fw-semibold">{{ translate('note') }}</th>
                        <th class="fw-semibold text-center">{{ translate('Csv') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveryLogs as $index => $log)
                    <tr>
                        <td>{{ $deliveryLogs->firstItem() + $index }}</td>
                        <td><span class="bidi-ltr d-inline-block">{{ \Carbon\Carbon::parse($log->delivery_date)->format('d/m/Y') }}</span></td>
                        <td>{{ $log->product->getTranslatedField('name') ?? __('N/A') }}</td>
                        <td>{{ $log->product_variation_type ?? __('No Variation') }}</td>
                        <td>{{ $log->quantity_sent }}</td>
                        <td>{{ $log->branch->branch_name ?? __('N/A') }}</td>
                        <td>{{ $log->note ?? '-' }}</td>
                        <td class="text-center align-middle">
                            @if($log->serial_csv_path)
                            <a href="{{ route('admin.wholesale.business.delivery.download-csv', $log->id) }}"
                                class="btn btn-sm btn-outline-info" title="{{ translate('Download CSV') }}">
                                <i class="tio-download"></i>
                            </a>
                            @else
                            {{ translate('no_csv_file_found') }}
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">{{ translate('no_delivery_logs_found') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-3">
            {{ $deliveryLogs->links() }}
        </div>

    </div>
</div>

<script>
    const branchListRoute = @json(route('admin.wholesale.business.branch-list'));
    const branchProductStoreRoute = @json(route('admin.wholesale.business.branch-product-store'));
</script>

@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/libs/bootstrap-5/bootstrap.bundle.min.js') }}"></script>
<script>
    $(document).ready(function() {
        let branchStock = 0;
        let remainingQty = 0;
        let expectedSerialCount = 0;

        $('#branchSelect').select2({
            dropdownParent: $('#addDeliveryModal'),
            placeholder: @json(__('Select Branch')),
            width: '100%'
        });

        $('.open-delivery-modal').on('click', function() {
            const productId = $(this).data('product-id');
            const productName = $(this).data('product-name');
            const requestedQty = parseInt($(this).data('requested-qty'));
            const sentQty = parseInt($(this).data('sent-qty'));
            const confirmId = $(this).data('order-id');
            remainingQty = parseInt($(this).data('remaining'));
            const variationType = $(this).data('variation-type') || '';
            const isTraceable = Number($(this).data('is-traceable')) === 1 || $(this).data('is-traceable') === true;

            $('#modalProductId').val(productId);
            $('#modalProductName').text(productName);
            $('#modalRequestedQty').text(requestedQty);
            $('#modalSentQty').text(sentQty);
            $('#modalRemainingQty').text(remainingQty);
            $('#modalConfirmedOrderId').val(confirmId);
            $('#modalVariationType').val(variationType);

            $('#branchSelect').val('').trigger('change');
            $('#branchStock').val('-');
            $('#sentQuantity').val('');
            $('#serialCsv').val('');
            $('#remainingQtyAfterSend').val('-');
            $('#quantityError').hide();
            $('#csvError').hide();
            $('#submitDeliveryBtn').prop('disabled', false);

            $('#deliveryForm').data('is-traceable', isTraceable);

            if (isTraceable) {
                $('#serialCsv').closest('.mb-3').show();
                $('#download_sample').show();
                $('#serialCsv').prop('required', true);
            } else {
                $('#serialCsv').closest('.mb-3').hide();
                $('#download_sample').hide();
                $('#serialCsv').prop('required', false);
            }

            $.ajax({
                url: "{{ route('admin.wholesale.business.branch-list') }}",
                method: 'GET',
                success: function(branches) {
                    $('#branchSelect').empty().append('<option value="">{{ __('Select Branch') }}</option>');
                    branches.forEach(branch => {
                        $('#branchSelect').append(`<option value="${branch.id}">${branch.branch_name}</option>`);
                    });
                }
            });
        });

        $('#branchSelect').on('change', function() {
            const branchId = $(this).val();
            const productId = $('#modalProductId').val();

            if (!branchId) {
                $('#branchStock').val('-');
                return;
            }

            $.ajax({
                url: "{{ route('admin.wholesale.business.branch-product-store') }}",
                method: 'GET',
                data: {
                    branch_id: branchId,
                    product_id: productId,
                    variation_type: $('#modalVariationType').val()
                },
                success: function(res) {
                    branchStock = parseInt(res.stock) || 0;
                    $('#branchStock').val(branchStock);
                }
            });
        });

        $('#sentQuantity').on('input', function() {
            const qty = parseInt($(this).val()) || 0;
            expectedSerialCount = qty;

            if (qty > branchStock) {
                $('#quantityError').text('{{ translate("quantity_exceeds_branch_stock") }}').show();
                $('#submitDeliveryBtn').prop('disabled', true);
            } else if (qty > remainingQty) {
                $('#quantityError').text('{{ translate("quantity_exceeds_remaining") }}').show();
                $('#submitDeliveryBtn').prop('disabled', true);
            } else {
                $('#quantityError').hide();
                validateCsvMatch();
            }

            const newRemaining = remainingQty - qty;
            $('#remainingQtyAfterSend').val(newRemaining < 0 ? 0 : newRemaining);
        });

        $('#serialCsv').on('change', function() {
            validateCsvMatch();
        });

        $('#deliveryForm').on('submit', function(e) {
            const traceable = $(this).data('is-traceable') === true;
            const serialCsvInput = $('#serialCsv')[0];
            const hasCsvFile = !!(serialCsvInput && serialCsvInput.files && serialCsvInput.files.length > 0);

            if (traceable && !hasCsvFile) {
                e.preventDefault();
                toastr.error('CSV file is required for traceable product.');
                return;
            }
        });

        function validateCsvMatch() {
            const file = $('#serialCsv')[0].files[0];
            if (!file || expectedSerialCount === 0) {
                $('#csvError').hide();
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const text = e.target.result;
                const lines = text.split(/\r\n|\n/).map(l => l.trim()).filter(l => l);
                if (lines.length !== expectedSerialCount) {
                    $('#csvError').text(`Expected ${expectedSerialCount} serials, got ${lines.length}`).show();
                    $('#submitDeliveryBtn').prop('disabled', true);
                } else {
                    $('#csvError').hide();
                    $('#submitDeliveryBtn').prop('disabled', false);
                }
            };
            reader.readAsText(file);
        }
    });
</script>
@endpush
