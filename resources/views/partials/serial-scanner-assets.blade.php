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
                <div class="text-muted fs-12 mt-2" id="serialScannerFeedback"></div>
            </div>
        </div>
    </div>
</div>

@push('css_or_js')
<style>
    .serial-scan-btn {
        min-width: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #1f2937;
    }

    .serial-scan-btn svg {
        width: 18px;
        height: 18px;
        display: block;
    }
</style>
@endpush

@push('script')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    (function () {
        if (window.__serialScannerInitialized) {
            return;
        }

        window.__serialScannerInitialized = true;

        const serialScannerI18n = {
            cameraUnavailable: @json(translate('camera_is_not_available_on_this_device')),
            scannerLoadFailed: @json(translate('scanner_failed_to_load')),
            noCameraFound: @json(translate('no_camera_found')),
            scannerStartFailed: @json(translate('unable_to_start_camera_scanner')),
        };

        let activeTargetSelector = null;
        let html5QrCode = null;
        let scannerRunning = false;

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
                $('#serialScannerFeedback').text(serialScannerI18n.scannerLoadFailed);
                return;
            }

            if (!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia)) {
                $('#serialScannerFeedback').text(serialScannerI18n.cameraUnavailable);
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
                    $('#serialScannerFeedback').text(serialScannerI18n.noCameraFound);
                    return;
                }

                const preferredCamera = cameras.find((camera) => /back|rear|environment/i.test(camera.label || ''));
                const cameraId = preferredCamera ? preferredCamera.id : cameras[0].id;

                await html5QrCode.start(
                    cameraId,
                    {
                        fps: 10,
                        qrbox: { width: 230, height: 230 }
                    },
                    function (decodedText) {
                        if (activeTargetSelector) {
                            $(activeTargetSelector).val(decodedText).trigger('change');
                        }

                        $('#serialScannerModal').modal('hide');
                    },
                    function () {
                    }
                );

                scannerRunning = true;
                $('#serialScannerFeedback').text('');
            } catch (error) {
                $('#serialScannerFeedback').text(serialScannerI18n.scannerStartFailed);
            }
        }

        $(document).on('click', '.scan-serial-btn', function () {
            activeTargetSelector = $(this).data('target-input');
            $('#serialScannerFeedback').text('');
            $('#serialScannerModal').modal('show');
        });

        $('#serialScannerModal').on('shown.bs.modal', function () {
            startSerialScanner();
        });

        $('#serialScannerModal').on('hidden.bs.modal', function () {
            stopSerialScanner();
            activeTargetSelector = null;
            $('#serialScannerFeedback').text('');
        });
    })();
</script>
@endpush
