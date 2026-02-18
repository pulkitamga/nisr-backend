<!-- resources/views/admin-views/partials/_call-popup.blade.php -->

<div id="call-popup-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
    <!-- Calls yahan Ajax se append honge -->
</div>

@push('scripts')
<script>
    function fetchCalls() {
        $.ajax({
            url: '{{ route("admin.ucm.calls") }}',
            method: 'GET',
            success: function(calls) {
                let container = $('#call-popup-container');
                container.empty();

                calls.forEach(call => {
                    if (!call.is_mine) {
                        let html = `
                            <div class="card mb-2 shadow-sm call-popup" style="width: 300px;" data-call-id="${call.call_id}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>${call.caller}</strong>
                                            ${call.contact ? `<br><small class="text-muted">${call.contact.name}</small>` : ''}
                                        </div>
                                        <button class="btn btn-success btn-sm accept-call" data-id="${call.call_id}">
                                            Accept
                                        </button>
                                    </div>
                                </div>
                            </div>`;
                        container.append(html);
                    } else {
                        // Ongoing call for this user
                        let html = `
                            <div class="card mb-2 shadow-sm bg-info text-white" style="width: 300px;">
                                <div class="card-body p-3">
                                    <strong>Ongoing Call</strong><br>
                                    ${call.caller} → ${call.callee}
                                    ${call.contact ? `<hr class="my-2"><strong>${call.contact.name}</strong>` : ''}
                                    <button class="btn btn-danger btn-sm mt-2 end-call" data-id="${call.call_id}">
                                        End Call
                                    </button>
                                </div>
                            </div>`;
                        container.append(html);
                    }
                });
            }
        });
    }

    // Poll every 3 seconds
    setInterval(fetchCalls, 3000);
    fetchCalls(); // Initial load

    // Accept Call
    $(document).on('click', '.accept-call', function() {
        let callId = $(this).data('id');
        $.post('{{ route("admin.ucm.accept") }}', {
            _token: '{{ csrf_token() }}',
            call_id: callId
        }, function() {
            fetchCalls();
        });
    });

    // End Call
    $(document).on('click', '.end-call', function() {
        let callId = $(this).data('id');
        $.post('{{ route("admin.ucm.end") }}', {
            _token: '{{ csrf_token() }}',
            call_id: callId
        }, function() {
            $(`[data-call-id="${callId}"]`).remove();
        });
    });
</script>
@endpush