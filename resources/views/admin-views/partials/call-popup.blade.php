<!-- resources/views/admin-views/partials/_call-popup.blade.php -->

<div id="call-popup-container" style="position: fixed; bottom: 20px; inset-inline-end: 20px; z-index: 9999;">
    <!-- Calls yahan Ajax se append honge -->
</div>

@push('script')
<script>
    let ucmPollInFlight = false;

    function fetchCalls() {
        if (ucmPollInFlight || document.hidden) {
            return;
        }

        ucmPollInFlight = true;
        $.ajax({
            url: '{{ route("admin.ucm.calls") }}',
            method: 'GET',
            success: function(calls) {
                let container = $('#call-popup-container');
                container.empty();

                calls.forEach(call => {
                    if (!call.is_mine || !call.channel) {
                        return;
                    }

                    if (call.status === 'ringing') {
                        let html = `
                            <div class="card mb-2 shadow-sm call-popup" style="width: 300px;" data-call-id="${call.call_id}" data-channel="${call.channel}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>${call.caller}</strong>
                                            ${call.contact ? `<br><small class="text-muted">${call.contact.name}</small>` : ''}
                                        </div>
                                        <button class="btn btn-success btn-sm accept-call" data-id="${call.call_id}" data-channel="${call.channel}">
                                            {{ __('Accept') }}
                                        </button>
                                    </div>
                                    <button class="btn btn-outline-danger btn-sm mt-2 reject-call" data-id="${call.call_id}" data-channel="${call.channel}">
                                        {{ __('Reject') }}
                                    </button>
                                </div>
                            </div>`;
                        container.append(html);
                    } else {
                        // Ongoing call for this user
                        let html = `
                            <div class="card mb-2 shadow-sm bg-info text-white" style="width: 300px;" data-call-id="${call.call_id}" data-channel="${call.channel}">
                                <div class="card-body p-3">
                                    <strong>{{ __('Ongoing Call') }}</strong><br>
                                    ${call.caller} → ${call.callee}
                                    ${call.contact ? `<hr class="my-2"><strong>${call.contact.name}</strong>` : ''}
                                    <button class="btn btn-danger btn-sm mt-2 end-call" data-id="${call.call_id}" data-channel="${call.channel}">
                                        {{ __('End Call') }}
                                    </button>
                                </div>
                            </div>`;
                        container.append(html);
                    }
                });
            },
            complete: function() {
                ucmPollInFlight = false;
            }
        });
    }

    // Poll every 5 seconds, skip hidden tab and overlapping requests.
    setInterval(fetchCalls, 5000);
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            fetchCalls();
        }
    });
    fetchCalls(); // Initial load

    function postCallAction(url, payload, onSuccess) {
        $.post(url, payload, function(response) {
            if (response && response.ok) {
                onSuccess();
                return;
            }
            fetchCalls();
        }).fail(function() {
            fetchCalls();
        });
    }

    // Accept Call
    $(document).on('click', '.accept-call', function() {
        let callId = $(this).data('id');
        let channel = $(this).data('channel');
        postCallAction('{{ route("admin.ucm.accept") }}', {
            _token: '{{ csrf_token() }}',
            call_id: callId,
            channel: channel
        }, function() {
            fetchCalls();
        });
    });

    // Reject Call
    $(document).on('click', '.reject-call', function() {
        let callId = $(this).data('id');
        let channel = $(this).data('channel');
        postCallAction('{{ route("admin.ucm.reject") }}', {
            _token: '{{ csrf_token() }}',
            call_id: callId,
            channel: channel
        }, function() {
            $(`[data-call-id="${callId}"]`).remove();
        });
    });

    // End Call
    $(document).on('click', '.end-call', function() {
        let callId = $(this).data('id');
        let channel = $(this).data('channel');
        postCallAction('{{ route("admin.ucm.end") }}', {
            _token: '{{ csrf_token() }}',
            call_id: callId,
            channel: channel
        }, function() {
            $(`[data-call-id="${callId}"]`).remove();
        });
    });
</script>
@endpush
