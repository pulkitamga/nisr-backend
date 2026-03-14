@extends('layouts.front-end.app')

@section('title', translate('order_Details'))

@section('content')
<div class="container pb-5 mb-2 mb-md-4 mt-3 rtl __inline-47 text-align-direction">
    <div class="row g-3">
        @include('web-views.partials._profile-aside')

        <section class="col-lg-9">
            @include('web-views.users-profile.account-details.partial', ['order' => $order])

            <div class="card mt-3 border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div>
                            <h5 class="mb-0 text-capitalize">{{ translate('warranty_and_support') }}</h5>
                            <small class="text-muted d-block mt-1">
                                {{ translate('all_customers_can_use_public_warranty_form') }}
                            </small>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <a href="{{ route('warranty.activate') }}" class="btn btn-sm btn-outline-primary">
                                {{ translate('public_warranty_form') }}
                            </a>
                            <span class="fs-12 text-muted">
                                {{ translate('order') }} #{{ $order->id }}
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="thead-light text-capitalize">
                                <tr>
                                    <th>{{ translate('product_name') }}</th>
                                    <th>{{ translate('quantity') }}</th>
                                    <th>{{ translate('action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->details as $detail)
                                    @php
                                        $product = json_decode($detail->product_details, true);
                                        $productName = $product['name'] ?? ($detail?->productAllStatus?->name ?? translate('product'));
                                        $warrantyData = $orderDetailWarrantyMap[$detail->id] ?? [];
                                        $warranty = $warrantyData['first'] ?? null;
                                        $activatedCount = (int)($warrantyData['activated_count'] ?? 0);
                                        $remainingCount = (int)($warrantyData['remaining_count'] ?? (int)$detail->qty);
                                        $isDeliveredItem = \App\Support\WarrantyOrderSupport::isDeliveredItem($order, $detail);
                                        $isWarrantyEnabled = (bool)($detail?->product?->is_warranty);
                                        $canActivateWarranty = \App\Support\WarrantyOrderSupport::canActivate($isWarrantyEnabled, $isDeliveredItem, $remainingCount);
                                        $defaultTicketType = ($isWarrantyEnabled && $warranty) ? 'service' : 'retail';
                                        $supportMessage = \App\Support\WarrantyOrderSupport::supportMessage($isWarrantyEnabled, $isDeliveredItem, $remainingCount);
                                    @endphp
                                    <tr class="border-bottom">
                                        <td>
                                            <div class="font-semi-bold">{{ $productName }}</div>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">{{ $detail->qty }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                @if($isWarrantyEnabled)
                                                    @if($canActivateWarranty)
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-success activate-warranty-btn"
                                                            data-toggle="modal"
                                                            data-target="#activateWarrantyModal"
                                                            data-detail-id="{{ $detail->id }}"
                                                            data-remaining-count="{{ $remainingCount }}">
                                                            {{ translate('activate_warranty') }}
                                                        </button>
                                                    @elseif($warranty && $warranty->warranty_public_id)
                                                        <a
                                                            href="{{ route('warranty.view', ['warranty_public_id' => $warranty->warranty_public_id]) }}"
                                                            class="btn btn-sm btn-outline-primary">
                                                            {{ translate('view_warranty') }}
                                                        </a>
                                                    @else
                                                        <span class="badge badge-soft-secondary">
                                                            {{ $supportMessage }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-soft-secondary">
                                                        {{ translate('no_warranty') }}
                                                    </span>
                                                    @endif

                                                @if($isWarrantyEnabled)
                                                    <span class="badge badge-soft-info">
                                                        {{ translate('activated') }}: {{ $activatedCount }} / {{ (int)$detail->qty }}
                                                    </span>
                                                @endif

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn--primary create-support-ticket-btn"
                                                    data-toggle="modal"
                                                    data-target="#createSupportTicketModal"
                                                    data-order-id="{{ $order->id }}"
                                                    data-detail-id="{{ $detail->id }}"
                                                    data-product-name="{{ $productName }}"
                                                    data-product-qty="{{ $detail->qty }}"
                                                    data-ticket-type="{{ $defaultTicketType }}">
                                                    {{ translate('create_support_ticket') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            {{ translate('no_data_found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="activateWarrantyModal" tabindex="-1" aria-labelledby="activateWarrantyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rtl">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-capitalize flex-grow-1">{{ translate('activate_warranty') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('warranty.activate.order.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="order_detail_id" id="orderDetailId" value="">

                    <div class="form-group">
                        <div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
                            <label class="title-color mb-0">{{ translate('serial_no') }}</label>
                            <small class="text-muted" id="remainingSerialLabel"></small>
                        </div>
                        <small class="text-muted d-block mb-2">{{ translate('you_can_fill_one_or_more_serial_numbers') }}</small>
                        <div id="serialInputContainer"></div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="agreeTerms" name="agree_terms" required>
                            <label class="custom-control-label" for="agreeTerms">
                                {{ translate('I_have_read_and_agree_to_the') }}
                                <a href="{{ route('warranty-policy') }}" target="_blank" class="text--primary">
                                    {{ translate('warranty_policy') }}
                                </a>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-3">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('cancel') }}</button>
                        <button type="submit" class="btn btn--primary">{{ translate('activate_warranty') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="serialScannerModal" tabindex="-1" aria-labelledby="serialScannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rtl">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-capitalize flex-grow-1">{{ translate('scan_serial_number') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="serial-reader" style="width:100%;"></div>
                <div class="text-muted fs-12 mt-2" id="scannerFeedback"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createSupportTicketModal" tabindex="-1" aria-labelledby="createSupportTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rtl">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-capitalize flex-grow-1">{{ translate('create_support_ticket') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('account-order-details-warranty-support.support-ticket.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="order_id" id="supportTicketOrderId">
                    <input type="hidden" name="order_detail_id" id="supportTicketOrderDetailId">

                    <div class="form-group">
                        <label class="title-color">{{ translate('product_name') }}</label>
                        <input type="text" id="supportTicketProductName" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label class="title-color">{{ translate('quantity') }}</label>
                        <input type="text" id="supportTicketProductQty" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label class="title-color">{{ translate('type') }} <span class="text-danger">*</span></label>
                        <select name="ticket_type" id="supportTicketType" class="form-control text-capitalize" required>
                            <option value="support">{{ translate('support') }}</option>
                            <option value="complaint">{{ translate('complaint') }}</option>
                            <option value="service">{{ translate('service') }}</option>
                            <option value="retail">{{ translate('retail') }}</option>
                            <option value="wholesale">{{ translate('wholesale') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="title-color">{{ translate('describe_your_issue') }}</label>
                        <textarea name="ticket_description" rows="4" class="form-control" placeholder="{{ translate('write_your_message') }}"></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-3">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('cancel') }}</button>
                        <button type="submit" class="btn btn--primary">{{ translate('submit_a_ticket') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const serialI18n = {
        serialLabel: @json(translate('serial_no')),
        placeholder: @json(translate('enter_serial_number')),
        remainingLabel: @json(translate('remaining_serial_numbers_to_activate')),
        scanTitle: @json(translate('scan_barcode_or_qr')),
        cameraUnavailable: @json(translate('camera_is_not_available_on_this_device')),
        scannerLoadFailed: @json(translate('scanner_failed_to_load')),
        noCameraFound: @json(translate('no_camera_found')),
        scannerStartFailed: @json(translate('unable_to_start_camera_scanner')),
    };

    let activeSerialInputId = null;
    let html5QrCode = null;
    let scannerRunning = false;

    function renderSerialInputs(count) {
        const container = $('#serialInputContainer');
        container.empty();

        if (count <= 0) {
            container.append(
                '<div class="alert alert-warning mb-0">' + @json(translate('all_warranty_units_for_this_item_are_already_activated')) + '</div>'
            );
            return;
        }

        for (let index = 0; index < count; index++) {
            const inputId = 'serialNoInput' + index;
            const itemHtml = `
                <div class="form-group serial-input-group">
                    <div class="input-group">
                        <input
                            type="text"
                            id="${inputId}"
                            name="serial_no[]"
                            class="form-control"
                            placeholder="${serialI18n.placeholder}">
                        <div class="input-group-append">
                            <button
                                type="button"
                                class="btn btn-outline-secondary scan-serial-btn text-dark"
                                data-target-input="${inputId}"
                                title="${serialI18n.scanTitle}"
                                style="color:#000 !important;">
                                <i class="tio-camera text-dark" style="color:#000 !important;"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1">${serialI18n.serialLabel} #${index + 1}</small>
                </div>
            `;
            container.append(itemHtml);
        }
    }

    async function stopSerialScanner() {
        if (html5QrCode && scannerRunning) {
            try {
                await html5QrCode.stop();
            } catch (error) {
            }
            try {
                await html5QrCode.clear();
            } catch (error) {
            }
            scannerRunning = false;
        }
    }

    async function startSerialScanner() {
        if (!window.Html5Qrcode) {
            $('#scannerFeedback').text(serialI18n.scannerLoadFailed);
            return;
        }

        if (!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia)) {
            $('#scannerFeedback').text(serialI18n.cameraUnavailable);
            return;
        }

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode('serial-reader');
        }

        if (scannerRunning) {
            return;
        }

        try {
            const cameras = await Html5Qrcode.getCameras();
            if (!cameras || cameras.length === 0) {
                $('#scannerFeedback').text(serialI18n.noCameraFound);
                return;
            }

            const preferredCamera = cameras.find((camera) =>
                /back|rear|environment/i.test(camera.label || '')
            );
            const cameraId = preferredCamera ? preferredCamera.id : cameras[0].id;

            await html5QrCode.start(
                cameraId,
                {
                    fps: 10,
                    qrbox: { width: 230, height: 230 }
                },
                function (decodedText) {
                    if (activeSerialInputId) {
                        $('#' + activeSerialInputId).val(decodedText);
                    }
                    $('#serialScannerModal').modal('hide');
                },
                function () {
                }
            );

            scannerRunning = true;
            $('#scannerFeedback').text('');
        } catch (error) {
            $('#scannerFeedback').text(serialI18n.scannerStartFailed);
        }
    }

    $(document).on('click', '.activate-warranty-btn', function () {
        $('#orderDetailId').val($(this).data('detail-id'));
        const remainingCount = parseInt($(this).data('remaining-count'), 10) || 0;
        $('#remainingSerialLabel').text(serialI18n.remainingLabel + ': ' + remainingCount);
        renderSerialInputs(remainingCount);
    });

    $(document).on('click', '.create-support-ticket-btn', function () {
        $('#supportTicketOrderId').val($(this).data('order-id'));
        $('#supportTicketOrderDetailId').val($(this).data('detail-id'));
        $('#supportTicketProductName').val($(this).data('product-name'));
        $('#supportTicketProductQty').val($(this).data('product-qty'));
        $('#supportTicketType').val($(this).data('ticket-type'));
    });

    $(document).on('click', '.scan-serial-btn', function () {
        activeSerialInputId = $(this).data('target-input');
        $('#scannerFeedback').text('');
        $('#serialScannerModal').modal('show');
    });

    $('#serialScannerModal').on('shown.bs.modal', function () {
        startSerialScanner();
    });

    $('#serialScannerModal').on('hidden.bs.modal', function () {
        stopSerialScanner();
        activeSerialInputId = null;
    });
</script>
@endpush

