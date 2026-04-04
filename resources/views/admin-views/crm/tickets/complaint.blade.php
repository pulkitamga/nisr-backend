@php use Carbon\Carbon; use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('complaint_ticket'))
@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/crm.css')}}">
@endpush

@section('content')
<div class="content container-fluid">
    @php
    $priority = request()->has('priority') ? request()->input('priority') : 'all';
    $statusId = request()->has('status') ? request()->input('status') : (string) \App\Support\ComplaintTicketWorkflow::STATUS_NEW;
    $selectedStatus = $aAllStatus->firstWhere('id', (int) $statusId);
    $selectedStatusLabel = $statusId === 'all'
        ? translate('all_Status')
        : ($selectedStatus?->getTranslatedField('name') ?? translate('all_Status'));
    $toolbarFields = [
        [
            'type' => 'search',
            'name' => 'searchValue',
            'label' => translate('search'),
            'value' => request('searchValue'),
            'placeholder' => translate('search_ticket_by_subject_or_status'),
            'aria_label' => translate('search_ticket_by_subject_or_status'),
            'col_class' => 'col-xl-6 col-lg-12',
        ],
        [
            'type' => 'select',
            'name' => 'priority',
            'label' => translate('Priority'),
            'value' => $priority,
            'options' => collect(['all', 'low', 'medium', 'high', 'urgent'])
                ->mapWithKeys(fn ($option) => [$option => $option === 'all' ? translate('all_Priority') : translate($option)])
                ->all(),
            'input_class' => 'form-control border-color-c1',
            'col_class' => 'col-xl-3 col-lg-6',
        ],
        [
            'type' => 'select',
            'name' => 'status',
            'label' => translate('Status'),
            'value' => $statusId,
            'options' => ['all' => translate('all_Status')] + $aAllStatus->mapWithKeys(fn ($statusOption) => [
                (string) $statusOption['id'] => $statusOption->getTranslatedField('name'),
            ])->all(),
            'input_class' => 'form-control border-color-c1',
            'col_class' => 'col-xl-3 col-lg-6',
        ],
    ];
    $toolbarSummary = [
        [
            'label' => translate('Status'),
            'value' => $selectedStatusLabel,
        ],
    ];
    if (!request()->has('status')) {
        $toolbarSummary[] = [
            'value' => translate('default_status'),
            'muted' => true,
        ];
    }
    if ($priority !== 'all') {
        $toolbarSummary[] = [
            'label' => translate('Priority'),
            'value' => translate($priority),
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
            'url' => route('admin.support-ticket.export', 'complaint'),
            'form_id' => 'crm-complaint-ticket-toolbar',
            'label' => translate('export'),
        ],
    ];
    @endphp
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/support_ticket.png')}}" alt="">
            {{translate('complaint_ticket')}}
            <span class="badge badge-soft-dark radius-50 fz-14">{{ $tickets->total() }}</span>
        </h2>
    </div>
    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'crm-complaint-ticket-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.support-ticket.view', 'complaint'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])
    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('complaint_ticket'),
            'listHeaderTotal' => $tickets->total(),
            'listHeaderActions' => $headerActions,
        ])
        <div class="table-responsive datatable-custom">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle w-100">
                <thead class="thead-light text-capitalize">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('Subject')}}</th>
                        <th>{{translate('Customer')}}</th>
                        <th>{{translate('Priority')}}</th>
                        <th>{{translate('Status')}}</th>
                        <!-- <th>{{translate('Source ID')}}</th> -->
                        <th>{{translate('Created At')}}</th>
                        <th class="text-center">{{translate('Action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $key => $ticket)
                    @php
                    $priorityClass = '';
                    $statusClass = '';

                    switch(strtolower($ticket->priority)) {
                    case 'low': $priorityClass='badge-soft-primary'; break;
                    case 'medium': $priorityClass='badge-soft-info'; break;
                    case 'high': $priorityClass='badge-soft-warning'; break;
                    case 'urgent': $priorityClass='badge-soft-danger'; break;
                    default: $priorityClass='badge-soft-dark'; break;
                    }

                    switch ($ticket->status ?? 0) {
                    case 36: // new
                    $statusClass = 'badge-soft-primary';
                    break;

                    case 37: // open
                    $statusClass = 'badge-soft-info';
                    break;

                    case 38: // assigned
                    $statusClass = 'badge-soft-success';
                    break;

                    case 39: // triage
                    $statusClass = 'badge-soft-secondary';
                    break;

                    case 40: // inprogress
                    $statusClass = 'badge-soft-warning';
                    break;

                    case 41: // resolved
                    $statusClass = 'badge-soft-success';
                    break;

                    case 42: // closed
                    $statusClass = 'badge-soft-dark';
                    break;

                    default:
                    $statusClass = 'badge-soft-dark';
                    break;
                    }

                    @endphp
                    <tr>
                        <td>{{ $tickets->firstItem() + $key }}</td>
                        <td>
                            <a href="{{ route('admin.support-ticket.details', $ticket->id) }}" class="crm-primary-link">
                                {{ $ticket->subject ?? translate('No Subject') }}
                            </a>
                        </td>
                        <td>
                            @if($ticket->customer)
                            {{ $ticket->customer->f_name ?? '' }} {{ $ticket->customer->l_name ?? '' }}
                            <div class="fz-12 text-muted">{{ $ticket->customer->email ?? '' }}</div>
                            @else
                            {{ translate('Customer Not Found') }}
                            @endif
                        </td>
                        <td><span class="badge {{ $priorityClass }}">{{ translate($ticket->priority) }}</span></td>
                        <td><span class="badge {{ $statusClass }}">{{ $ticket->status_details->getTranslatedField('name') ?? $ticket->status }}</span></td>
                        <td><span class="bidi-ltr d-inline-block">{{ $ticket->created_at->format('d M, Y H:i') }}</span></td>
                        @php
                        $statusName = strtolower($ticket->status_details?->name);
                        @endphp
                        <td class="text-center">
                            <div class="crm-row-actions">
                                <div class="crm-row-actions__primary">
                                    <a href="{{ route('admin.support-ticket.details', $ticket->id) }}"
                                        class="btn btn-sm btn-outline-success">{{ translate('View') }}</a>
                                    <a href="{{ route('admin.support-ticket.singleTicket', $ticket->id) }}" class="btn btn-sm btn-outline-info">{{translate('Chat')}}</a>
                                </div>
                                @if(!$ticket->employee_id)
                                <div class="crm-row-actions__chips">
                                    <span class="crm-row-actions__chip">{{ translate('No Employee') }}</span>
                                </div>
                                @endif
                                <div class="dropdown crm-row-actions__menu">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                        <i class="tio-more-horizontal"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        @if($statusName === 'new' || $statusName === 'closed')
                                        <form action="{{ route('admin.support-ticket.status') }}" method="POST" class="statusForm crm-row-actions__form">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $ticket->id }}">

                                            @php
                                            $statusBtnText = $statusName === 'new' ? 'Open' : 'Reopen';
                                            @endphp

                                            <button type="submit" class="dropdown-item">
                                                {{ translate($statusBtnText) }}
                                            </button>
                                        </form>
                                        @endif
                                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'ticket_employee_update') || auth('admin')->user()->id == ($ticket->department?->head_id))
                                        <a href="javascript:void(0)"
                                            class="dropdown-item assign-employee-btn"
                                            data-id="{{ $ticket->id }}"
                                            data-department-id="{{ $ticket->department->id ?? '' }}"
                                            data-head-id="{{ $ticket->department->head_id ?? '' }}">
                                            {{ $ticket->employee_id ? translate('Re-Assign Employee') : translate('Assign Employee') }}
                                        </a>

                                        @if(!auth('admin')->user()?->isSuperAdmin())
                                        <input type="hidden" id="fixed-department-id" value="{{ auth('admin')->user()->department_id }}">
                                        @endif
                                        @endif
                                        @if(!empty($ticket->status_details) && trim(strtolower($ticket->status_details->name)) != 'closed')
                                        <a class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#showComplainFollowUpModal" data-ticket-id="{{ $ticket->id }}" data-department-id="{{ $ticket->department_id }}" data-employee-id="{{ $ticket->employee_id }}" data-status-id="{{ $ticket->status }}" data-status-name="{{ $ticket->status_details?->name ?? '' }}" data-follow-up-date="{{ $ticket->follow_up_date ? \Carbon\Carbon::parse($ticket->follow_up_date)->format('Y-m-d') : '' }}" title="{{ translate('Follow-up details') }}">
                                            {{ translate('change_Status') }}
                                        </a>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                        <a href="javascript:void(0)" class="dropdown-item text-warning escalate-btn" data-ticket-id="{{ $ticket->id }}">
                                            {{ translate('Escalate') }}
                                        </a>
                                    </div>
                                </div>
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
        @include('layouts.back-end._empty-state',['text'=>'no_support_ticket_found'],['image'=>'default'])
        @endif
    </div>
</div>

<div class="modal fade" id="showComplainFollowUpModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="showSupportFollowUpModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2 d-flex">
                <h3>{{ translate('Support Follow Up') }}</h3>
                <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
                <form id="updateComplainTicketFollowUpForm" action="{{ route('admin.complaints.update-complain-follow-up') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ticket_id" id="complain-follow-up-ticket-id">
                    <input type="hidden" name="department_id" id="complain-follow-up-department-id">
                    <input type="hidden" name="employee_id" id="complain-follow-up-employee-id">
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label" for="support-follow-up-status">{{ translate('Select Status') }}</label>
                                <select class="js-select2-custom form-control" name="ticket-follow-up-status" id="complain-follow-up-status">
                                    <option value="0" selected disabled>{{ translate('Select Status') }}</option>
                                    @foreach ([
                                    ['id' => 37, 'name' => 'Open'],
                                    ['id' => 38, 'name' => 'Assigned'],
                                    ['id' => 39, 'name' => 'In Progress'],
                                    ['id' => 40, 'name' => 'Waiting'],
                                    ['id' => 41, 'name' => 'Resolved'],
                                    ['id' => 42, 'name' => 'Closed']
                                    ] as $status)
                                    @php
                                        $complainStatusName = strtolower(trim((string) ($status['name'] ?? '')));
                                        $complainNormalizedStatusName = str_replace([' ', '-'], '_', $complainStatusName);
                                        $complainRequiresFollowUpDate = in_array($complainNormalizedStatusName, ['in_progress', 'inprogress'], true) ? 1 : 0;
                                    @endphp
                                    <option value="{{ $status['id'] }}"
                                        data-status-name="{{ $complainStatusName }}"
                                        data-require-follow-up-date="{{ $complainRequiresFollowUpDate }}">
                                        {{ translate($status['name']) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row d-none" id="complain-ticket-next-follow-up-date-row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label" for="complain-ticket-next-follow-up-date">{{ translate('Next Follow-Up Date') }}</label>
                                <input type="date" name="ticket-next-follow-up-date" id="complain-ticket-next-follow-up-date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label" for="complain-follow-up-note">{{ translate('Note') }}</label>
                                <textarea rows="3" class="form-control" name="ticket-follow-up-note" id="complain-follow-up-note" placeholder="{{ translate('Enter follow-up note') }}"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-2">
                        <button type="submit" class="btn btn-xs btn-primary">{{ translate('Update Follow Up') }}</button>
                    </div>
                </form>
            </div>
        </div>
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
            <form id="escalateTicketForm" method="POST" action="{{ route('admin.complaints.escalate') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="id" id="escalateTicketId">
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
@include('admin-views.crm.partials.employee')
<span id="getEmployeeRoute" data-url="{{ route('admin.crm.getemployee') }}"></span>
<span id="assignEmployeeRoute" data-url="{{ route('admin.complaints.update-ticket-department') }}"></span>
<span id="route-get-department-employee" data-url="{{ route('admin.complaints.get-department-employee') }}"></span>
<span id="complaint-select-employee" data-text="{{ translate('Select Employee') }}"></span>
<span id="complaint-loading" data-text="{{ translate('Loading...') }}"></span>
<span id="complaint-department-updated" data-text="{{ translate('department_updated_successfully') }}"></span>
<span id="complaint-employee-updated" data-text="{{ translate('ticket_assigned_successfully') }}"></span>
<span id="complaint-follow-up-updated" data-text="{{ translate('updated successfully!') }}"></span>
<span id="complaint-something-went-wrong" data-text="{{ translate('something_went_wrong') }}"></span>
<span id="support-ticket-escalate-warning" data-text="{{ translate('This will notify the department and owner.') }}"></span>
<span id="support-ticket-yes-escalate" data-text="{{ translate('Yes, Escalate') }}"></span>
<span id="support-ticket-something-went-wrong" data-text="{{ translate('something_went_wrong') }}"></span>
<span id="support-ticket-ticket-id-required" data-text="{{ translate('Ticket ID is required.') }}"></span>
<span id="support-ticket-follow-up-date-required" data-text="{{ translate('follow_up_date_required_for_in_progress') }}"></span>
@endsection

@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/support-tickets.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/complaint.js')}}"></script>
@endpush
