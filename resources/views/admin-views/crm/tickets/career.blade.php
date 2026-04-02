@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Career Ticket'))
@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/crm.css')}}">
@endpush

@section('content')

@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = $languages[0]['code'] ?? 'en';
}
$priority = request()->has('priority') ? request()->input('priority') : 'all';
$statusId = request()->has('status') ? request()->input('status') : (string) \App\Support\CareerTicketWorkflow::STATUS_NEW;
$talentPoolFilter = request()->has('talent_pool') ? request()->input('talent_pool') : 'all';
$selectedStatusLabel = $statusId === 'all'
    ? translate('all_status')
    : ($statuses->firstWhere('id', (int) $statusId)?->getTranslatedField('name') ?? translate('all_status'));
$toolbarFields = [
    [
        'type' => 'search',
        'name' => 'searchValue',
        'label' => translate('search'),
        'value' => request('searchValue'),
        'placeholder' => translate('search_ticket_by_subject_or_status'),
        'aria_label' => translate('search_ticket_by_subject_or_status'),
        'col_class' => 'col-xl-4 col-lg-12',
    ],
    [
        'type' => 'select',
        'name' => 'priority',
        'label' => translate('priority'),
        'value' => $priority,
        'options' => collect(['all', 'low', 'medium', 'high', 'urgent'])
            ->mapWithKeys(fn ($option) => [$option => $option === 'all' ? translate('all_priority') : translate($option)])
            ->all(),
        'input_class' => 'form-control border-color-c1',
        'col_class' => 'col-xl-3 col-lg-4',
    ],
    [
        'type' => 'select',
        'name' => 'status',
        'label' => translate('status'),
        'value' => $statusId,
        'options' => ['all' => translate('all_status')] + $statuses->mapWithKeys(fn ($statusOption) => [
            (string) $statusOption['id'] => $statusOption->getTranslatedField('name'),
        ])->all(),
        'input_class' => 'form-control border-color-c1',
        'col_class' => 'col-xl-3 col-lg-4',
    ],
    [
        'type' => 'select',
        'name' => 'talent_pool',
        'label' => translate('talent_pool'),
        'value' => $talentPoolFilter,
        'options' => [
            'all' => translate('all_talent_pool'),
            'yes' => translate('talent_pool_yes'),
            'no' => translate('talent_pool_no'),
        ],
        'input_class' => 'form-control border-color-c1',
        'col_class' => 'col-xl-2 col-lg-4',
    ],
];
$toolbarSummary = [
    [
        'label' => translate('status'),
        'value' => $selectedStatusLabel,
    ],
];
if ($statusId !== 'all' && !request()->has('status')) {
    $toolbarSummary[] = [
        'value' => translate('default_status'),
        'muted' => true,
    ];
}
if ($priority !== 'all') {
    $toolbarSummary[] = [
        'label' => translate('priority'),
        'value' => translate($priority),
        'muted' => true,
    ];
}
if ($talentPoolFilter !== 'all') {
    $toolbarSummary[] = [
        'label' => translate('talent_pool'),
        'value' => translate($talentPoolFilter === 'yes' ? 'talent_pool_yes' : 'talent_pool_no'),
        'muted' => true,
    ];
}
if (request()->filled('searchValue')) {
    $toolbarSummary[] = [
        'label' => translate('search'),
        'value' => Str::limit(request('searchValue'), 28),
        'muted' => true,
    ];
}
$headerActions = [
    [
        'type' => 'export',
        'url' => route('admin.support-ticket.career.pool.export'),
        'form_id' => 'crm-career-ticket-toolbar',
        'label' => translate('export'),
    ],
];
@endphp
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/support_ticket.png')}}" alt="">
            {{translate('career_tickets')}}
            <span class="badge badge-soft-dark radius-50 fz-14">{{ $tickets->total() }}</span>
        </h2>
    </div>
    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'crm-career-ticket-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.support-ticket.career.index'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])
    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('Career Ticket List'),
            'listHeaderTotal' => $tickets->total(),
            'listHeaderActions' => $headerActions,
        ])
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
                        <td>
                            <a href="{{ route('admin.support-ticket.career.single', $ticket->id) }}" class="crm-primary-link">
                                {{ $ticket->subject }}
                            </a>
                        </td>
                        <td>{{ optional($ticket->relatedInboxMessage)->sender_name ?? optional($ticket->relatedInboxMessage)->sender_email ?? translate('N/A') }}</td>
                        <td>{{ $ticket->status_details?->getTranslatedField('name') ?? translate('N/A') }}</td>
                        <td>{{ $ticket->employee->name ?? translate('Unassigned') }}</td>
                        <td><span class="bidi-ltr d-inline-block">{{ $ticket->created_at->format('d-m-Y H:i') }}</span></td>
                        @php
                        $pendingInterview = $ticket->careerInterviews->whereNull('conducted_at')->first();
                        @endphp
                        <td>
                            @php
                            $hasCareerOverflow = ($ticket->status == 31 && $pendingInterview) || $ticket->status == 32 || in_array($ticket->status, [29, 30, 31, 32]) || !in_array($ticket->status, [33, 35]);
                            @endphp
                            <div class="crm-row-actions">
                                <div class="crm-row-actions__primary">
                                    <a href="{{ route('admin.support-ticket.career.single', $ticket->id) }}" class="btn btn-sm btn-info">{{ translate('view') }}</a>
                                    @if($ticket->status == 27)
                                    <button type="button" class="btn btn-sm btn-outline-primary action-btn" data-action="assign-recruiter" data-route="{{ route('admin.support-ticket.career.assign') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('assign_recruiter') }}</button>
                                    @elseif($ticket->status == 29)
                                    <button type="button" class="btn btn-sm btn-outline-primary action-btn" data-action="screen" data-route="{{ route('admin.support-ticket.career.screen') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('screen') }}</button>
                                    @elseif($ticket->status == 31 && $pendingInterview)
                                    <button type="button" class="btn btn-sm btn-outline-primary action-btn"
                                        data-action="conduct-interview"
                                        data-route="{{ route('admin.support-ticket.career.conduct-interview') }}"
                                        data-ticket-id="{{ $ticket->id }}"
                                        data-interview-id="{{ $pendingInterview->id }}">
                                        {{ translate('conduct_interview') }}
                                    </button>
                                    @elseif($ticket->status == 31)
                                    <button type="button" class="btn btn-sm btn-outline-primary action-btn" data-action="schedule-interview" data-route="{{ route('admin.support-ticket.career.schedule-interview') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('schedule_interview') }}</button>
                                    @elseif($ticket->status == 32)
                                    <button type="button" class="btn btn-sm btn-outline-primary action-btn" data-action="attach-offer" data-route="{{ route('admin.support-ticket.career.attach-offer') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('attach_offer') }}</button>
                                    @elseif(in_array($ticket->status, [34, 35]))
                                    <button type="button" class="btn btn-sm btn-outline-info action-btn" data-action="talent-pool" data-route="{{ route('admin.support-ticket.career.talent-pool') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('talent_pool') }}</button>
                                    @endif
                                </div>
                                @if($hasCareerOverflow)
                                <div class="dropdown crm-row-actions__menu">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                        <i class="tio-more-horizontal"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        @if($ticket->status == 31 && $pendingInterview)
                                        <button type="button" class="dropdown-item action-btn" data-action="schedule-interview" data-route="{{ route('admin.support-ticket.career.schedule-interview') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('schedule_interview') }}</button>
                                        @endif
                                        @if($ticket->status == 32)
                                        <button type="button" class="dropdown-item text-danger action-btn" data-action="decline-offer" data-route="{{ route('admin.support-ticket.career.decline-offer') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('decline_offer') }}</button>
                                        @endif
                                        @if(in_array($ticket->status, [29, 30, 31, 32]))
                                        <button type="button" class="dropdown-item text-danger action-btn" data-action="reject" data-route="{{ route('admin.support-ticket.career.reject') }}" data-ticket-id="{{ $ticket->id }}">{{ translate('reject') }}</button>
                                        @endif
                                        @if(!in_array($ticket->status, [33, 35]))
                                        <div class="dropdown-divider"></div>
                                        <a href="javascript:void(0)" class="dropdown-item text-warning escalate-btn" data-ticket-id="{{ $ticket->id }}">
                                            {{ translate('Escalate') }}
                                        </a>
                                        @endif
                                    </div>
                                </div>
                                @endif
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
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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

<span id="career-ticket-are-you-sure" data-text="{{ translate('are_you_sure') }}"></span>
<span id="career-ticket-yes" data-text="{{ translate('yes') }}"></span>
<span id="career-ticket-cancel" data-text="{{ translate('cancel') }}"></span>
<span id="career-ticket-escalate-warning" data-text="{{ translate('This will notify the department and owner.') }}"></span>
<span id="career-ticket-yes-escalate" data-text="{{ translate('Yes, Escalate') }}"></span>
@endsection
@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/career-ticket.js')}}"></script>
@endpush
