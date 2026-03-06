@extends('layouts.back-end.app')

@section('title', translate('Career Ticket'))

@section('content')

@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $languages[0]['code'] ?? 'en';
@endphp
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/support_ticket.png')}}" alt="">
            {{translate('career_tickets')}}
        </h2>
    </div>
    <div class="row mt-20">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="px-3 py-4 mb-3">
                    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                        <div>
                            <form action="{{ url()->current() }}" method="GET">
                                <div class="input-group input-group-merge input-group-custom">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="tio-search"></i>
                                        </div>
                                    </div>
                                    <input id="datatableSearch_" type="search" name="searchValue"
                                        class="form-control"
                                        placeholder="{{ translate('search_ticket_by_subject_or_status').'...' }}"
                                        aria-label="Search tickets" value="{{ request('searchValue') }}">
                                    <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                </div>
                            </form>
                        </div>
                        <div>
                            <div class="d-flex flex-wrap flex-sm-nowrap gap-3 justify-content-end ticket-filter-controls">
                                @php
                                $priority = request()->has('priority') ? request()->input('priority') : '';
                                $statusId = request()->has('status') ? request()->input('status') : '27';
                                $talentPoolFilter = request()->has('talent_pool') ? request()->input('talent_pool') : 'all';
                                @endphp

                                <!-- Priority -->
                                <select class="form-control border-color-c1 w-160 filter-tickets" name="priority">
                                    <option value="all">{{ translate('all_priority') }}</option>
                                    @foreach(['low','medium','high','urgent'] as $p)
                                    <option value="{{ $p }}" {{ $priority === $p ? 'selected' : '' }}>{{ translate($p) }}</option>
                                    @endforeach
                                </select>

                                <!-- Status -->
                                <select class="form-control border-color-c1 w-160 filter-tickets" name="status">
                                    <option value="all">{{ translate('all_status') }}</option>
                                    @foreach($statuses as $status)
                                    <option value="{{ $status['id'] }}" {{ $statusId == $status['id'] ? 'selected' : '' }}>
                                        {{ translate($status['name']) }}
                                    </option>
                                    @endforeach
                                </select>

                                <!-- Talent Pool Filter -->
                                <select class="form-control border-color-c1 w-160 filter-tickets" name="talent_pool">
                                    <option value="all" {{ $talentPoolFilter === 'all' ? 'selected' : '' }}>{{ translate('all_talent_pool') }}</option>
                                    <option value="yes" {{ $talentPoolFilter === 'yes' ? 'selected' : '' }}>{{ translate('talent_pool_yes') }}</option>
                                    <option value="no" {{ $talentPoolFilter === 'no' ? 'selected' : '' }}>{{ translate('talent_pool_no') }}</option>
                                </select>
                                <button type="button" class="btn btn--primary text-nowrap apply-career-filters">
                                    {{ translate('apply') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">
                {{translate('Career Ticket List')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $tickets->total() }}</span>
            </h5>

            <div class="dropdown">
                <a type="button" class="btn btn-outline--primary text-nowrap"
                    href="{{ route('admin.support-ticket.career.pool.export', 'career') }}?{{ http_build_query([
                        'priority' => request('priority'),
                        'status' => request('status'),
                        'searchValue' => request('searchValue'),
                        'talent_pool' => request('talent_pool')
                    ]) }}">
                    <img width="14" src="{{ dynamicAsset('public/assets/back-end/img/excel.png') }}" alt="" class="excel">
                    <span class="ps-2">{{ translate('export') }}</span>
                </a>
            </div>


        </div>
        <div class="table-responsive datatable-custom">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle w-100">
                <thead class="thead-light text-capitalize">
                    <tr>
                        <th>{{ translate('sl') }}</th>
                        <th>{{ translate('subject') }}</th>
                        <th>{{ translate('candidate') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th>{{ translate('recruiter') }}</th>
                        <th>{{ translate('created_at') }}</th>
                        <th>{{ translate('action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $index => $ticket)


                    <tr>
                        <td>{{ $tickets->firstItem() + $index }}</td>
                        <td>{{ $ticket->subject }}</td>
                        <td>{{ optional($ticket->relatedInboxMessage)->sender_name ?? optional($ticket->relatedInboxMessage)->sender_email ?? translate('N/A') }}</td>
                        <td>{{ $ticket->status_details->name ?? translate('N/A') }}</td>
                        <td>{{ $ticket->employee->name ?? translate('Unassigned') }}</td>
                        <td>{{ $ticket->created_at->format('d-m-Y H:i') }}</td>
                        @php
                        $pendingInterview = $ticket->careerInterviews->whereNull('conducted_at')->first();
                        @endphp
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.support-ticket.career.single', $ticket->id) }}" class="btn btn-sm btn-info">{{ translate('view') }}</a>
                                @if($ticket->status == 27) <!-- New -->
                                <button class="btn btn-sm btn-outline-primary action-btn" data-action="assign-recruiter" data-route="{{ route('admin.support-ticket.career.assign') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('assign_recruiter') }}</button>
                                @elseif($ticket->status == 29) <!-- Assigned -->
                                <button class="btn btn-sm btn-outline-primary action-btn" data-action="screen" data-route="{{ route('admin.support-ticket.career.screen') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('screen') }}</button>
                                @elseif($ticket->status == 31 )
                                @if($pendingInterview)
                                <button class="btn btn-sm btn-outline-primary action-btn"
                                    data-action="conduct-interview"
                                    data-route="{{ route('admin.support-ticket.career.conduct-interview') }}"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-interview-id="{{ $pendingInterview->id }}">
                                    {{ translate('conduct_interview') }}
                                </button>
                                @endif
                                @endif

                                @if(!in_array($ticket->status, [32, 33, 34, 35]))
                                <button class="btn btn-sm btn-outline-primary action-btn" data-action="attach-offer" data-route="{{ route('admin.support-ticket.career.attach-offer') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('attach_offer') }}</button>
                                <button class="btn btn-sm btn-outline-danger action-btn" data-action="decline-offer" data-route="{{ route('admin.support-ticket.career.decline-offer') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('decline_offer') }}</button>
                                <button class="btn btn-sm btn-outline-primary action-btn" data-action="schedule-interview" data-route="{{ route('admin.support-ticket.career.schedule-interview') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('schedule_interview') }}</button>
                                <button class="btn btn-sm btn-outline-danger action-btn" data-action="reject" data-route="{{ route('admin.support-ticket.career.reject') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('reject') }}</button>
                                <button class="btn btn-sm btn-outline-info action-btn" data-action="talent-pool" data-route="{{ route('admin.support-ticket.career.talent-pool') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('talent_pool') }}</button>
                                @endif
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-warning escalate-btn" data-ticket-id="{{ $ticket->id }}">
                                    {{ translate('Escalate') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-end">
            {{ $tickets->links() }}
        </div>
        @if(count($tickets) == 0)
        @include('layouts.back-end._empty-state', ['text' => 'no_career_ticket_found'], ['image' => 'default'])
        @endif
    </div>
</div>

<div class="modal fade" id="escalateTicketModal" tabindex="-1" role="dialog" aria-labelledby="escalateTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="escalateTicketModalLabel">{{ translate('Escalate Ticket') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="escalateTicketForm" method="POST" action="{{ route('admin.support-ticket.career.escalate') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="escalateTicketId">
                    <div class="form-group">
                        <label for="escalation_reason">{{ translate('Escalation Reason') }}</label>
                        <textarea name="reason" id="escalation_reason" class="form-control" rows="4" placeholder="{{ translate('Explain why this ticket needs escalation (e.g., limited access, department intervention required)...') }}" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-warning">{{ translate('Escalate') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modals -->
<div class="modal fade" id="assignRecruiterModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignRecruiterForm" method="POST" class="confirm-submit-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('assign_recruiter') }}</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="assignTicketId">
                    <div class="form-group">
                        <label>{{ translate('recruiter') }}</label>
                        <select name="recruiter_id" class="form-control select2" required>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('priority') }}</label>
                        <select name="priority" class="form-control" required>
                            <option value="low">{{ translate('low') }}</option>
                            <option value="medium">{{ translate('medium') }}</option>
                            <option value="high">{{ translate('high') }}</option>
                            <option value="urgent">{{ translate('urgent') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('assign') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="screenModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="screenForm" method="POST" class="confirm-submit-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('screen_candidate') }}</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="screenTicketId">
                    <div class="form-group">
                        <label>{{ translate('Notes') }}</label>
                        <textarea name="notes" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Qualified') }}</label>
                        <div>
                            <input type="radio" name="qualified" value="1" id="qualifiedYes" required>
                            <label for="qualifiedYes">{{ translate('yes') }}</label>
                            <input type="radio" name="qualified" value="0" id="qualifiedNo">
                            <label for="qualifiedNo">{{ translate('no') }}</label>
                        </div>
                    </div>
                    <div class="form-group" id="reasonCodeDiv" style="display: none;">
                        <label>{{ translate('reason_code') }}</label>
                        <input type="text" name="reason_code" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="scheduleInterviewModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="scheduleInterviewForm" method="POST" class="confirm-submit-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('schedule_interview') }}</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="scheduleTicketId">
                    <div class="form-group">
                        <label>{{ translate('Scheduled At') }}</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('panel') }}</label>
                        <select name="panel[]" class="form-control select2" multiple required>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Schedule') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="conductInterviewModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="conductInterviewForm" method="POST" class="confirm-submit-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('conduct_interview') }}</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="conductTicketId">
                    <input type="hidden" name="interview_id" id="conductInterviewId">
                    <div class="form-group">
                        <label>{{ translate('outcome') }}</label>
                        <select name="outcome" class="form-control" required>
                            <option value="pass">{{ translate('pass') }}</option>
                            <option value="fail">{{ translate('fail') }}</option>
                            <option value="no_show">{{ translate('no_show') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Notes') }}</label>
                        <textarea name="notes" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="attachOfferModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="attachOfferForm" method="POST" enctype="multipart/form-data" class="confirm-submit-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('attach_signed_offer') }}</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="attachTicketId">
                    <div class="form-group">
                        <label>{{ translate('offer_file') }}</label>
                        <input type="file" name="offer_file" class="form-control" accept=".pdf" required>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('start_date') }}</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('attach') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="declineOfferModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="declineOfferForm" method="POST" class="confirm-submit-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('decline_offer') }}</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="declineTicketId">
                    <div class="form-group">
                        <label>{{ translate('reason') }}</label>
                        <textarea name="reason" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST" class="confirm-submit-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('reject_candidate') }}</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="rejectTicketId">
                    <div class="form-group">
                        <label>{{ translate('reason_code') }}</label>
                        <input type="text" name="reason_code" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('closure_message') }}</label>
                        <textarea name="closure_message" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('reject') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="talentPoolModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="talentPoolForm" method="POST" class="confirm-submit-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('add_to_talent_pool') }}</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="talentPoolTicketId">
                    <div class="form-group">
                        <label>{{ translate('consent') }}</label>
                        <div>
                            <input type="radio" name="consent" value="1" id="consentYes" required>
                            <label for="consentYes">{{ translate('yes') }}</label>
                            <input type="radio" name="consent" value="0" id="consentNo">
                            <label for="consentNo">{{ translate('no') }}</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('recontact_date') }}</label>
                        <input type="date" name="recontact_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('add') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@push('script')

<script>
    $('.apply-career-filters').on('click', function() {
        let url = new URL(window.location.href);
        $('.ticket-filter-controls .filter-tickets').each(function() {
            let param = $(this).attr('name');
            if (!param) {
                return;
            }

            let value = $(this).val();
            if (value === undefined || value === null || value === '') {
                url.searchParams.delete(param);
                return;
            }

            url.searchParams.set(param, value);
        });
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });



    $(document).on('click', '.escalate-btn', function() {
        let ticketId = $(this).data('ticket-id');
        $('#escalateTicketId').val(ticketId);
        $('#escalateTicketModal').modal('show');
    });

    // Form submission with confirmation
    $('#escalateTicketForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will notify the department and owner.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Escalate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.off('submit').submit(); // Submit without further prevention
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('.select2').select2();

        $('.action-btn').click(function() {
            var action = $(this).data('action');
            var route = $(this).data('route');
            var ticketId = $(this).data('ticket-id');
            var interviewId = $(this).data('interview-id');

            switch (action) {
                case 'assign-recruiter':
                    $('#assignTicketId').val(ticketId);
                    $('#assignRecruiterForm').attr('action', route);
                    $('#assignRecruiterModal').modal('show');
                    break;

                case 'screen':
                    $('#screenTicketId').val(ticketId);
                    $('#screenForm').attr('action', route);
                    $('#screenModal').modal('show');

                    // Show/hide reason code based on selected radio
                    $('input[name="qualified"]').change(function() {
                        $('#reasonCodeDiv').toggle($('#qualifiedNo').is(':checked'));
                    });
                    break;

                case 'schedule-interview':
                    $('#scheduleTicketId').val(ticketId);
                    $('#scheduleInterviewForm').attr('action', route);
                    $('#scheduleInterviewModal').modal('show');
                    break;

                case 'conduct-interview':
                    $('#conductTicketId').val(ticketId);
                    $('#conductInterviewId').val(interviewId);
                    $('#conductInterviewForm').attr('action', route);
                    $('#conductInterviewModal').modal('show');
                    break;

                case 'attach-offer':
                    $('#attachTicketId').val(ticketId);
                    $('#attachOfferForm').attr('action', route);
                    $('#attachOfferModal').modal('show');
                    break;

                case 'decline-offer':
                    $('#declineTicketId').val(ticketId);
                    $('#declineOfferForm').attr('action', route);
                    $('#declineOfferModal').modal('show');
                    break;

                case 'reject':
                    $('#rejectTicketId').val(ticketId);
                    $('#rejectForm').attr('action', route);
                    $('#rejectModal').modal('show');
                    break;

                case 'talent-pool':
                    $('#talentPoolTicketId').val(ticketId);
                    $('#talentPoolForm').attr('action', route);
                    $('#talentPoolModal').modal('show');
                    break;
            }
        });

        $('.confirm-submit-form').submit(function(e) {
            e.preventDefault();
            var form = $(this);
            Swal.fire({
                title: '{{ translate("are_you_sure") }}',
                showCancelButton: true,
                confirmButtonText: '{{ translate("yes") }}',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.get(0).submit();
                }
            });
        });
    });
</script>
@endpush
