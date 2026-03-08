@php use Carbon\Carbon; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Wholesale Ticket'))

@section('content')
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/support_ticket.png')}}" alt="">
            {{translate('Wholesale Ticket')}}
            <span class="badge badge-soft-dark radius-50 fz-14">{{ $tickets->total() }}</span>
        </h2>
    </div>
    <div class="row mt-20">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="px-3 py-4 mb-3">
                    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                        <div class="">
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
                                        aria-label="{{ translate('Search orders') }}" value="{{ request('searchValue') }}">
                                    <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                </div>
                            </form>
                        </div>
                        <div class="">
                            <div class="d-flex flex-wrap flex-sm-nowrap gap-3 justify-content-end ticket-filter-controls">
                                @php
                                $priority = request()->has('priority') ? request()->input('priority') : '';
                                $statusId = request()->has('status') ? request()->input('status') : '56';
                                @endphp

                                <select class="form-control border-color-c1 w-160 filter-tickets" data-value="priority">
                                    <option value="all">{{ translate('all_Priority') }}</option>
                                    @foreach(['low','medium','high','urgent'] as $p)
                                    <option value="{{ $p }}" {{ $priority === $p ? 'selected' : '' }}>{{ translate($p) }}</option>
                                    @endforeach
                                </select>

                                <select class="form-control border-color-c1 w-160 filter-tickets" data-value="status">
                                    <option value="all">{{ translate('all_Status') }}</option>
                                    @foreach($aAllStatus as $status)
                                    @php
                                    // PHP 8 match expression for status badge class
                                    $statusClass = match(strtolower($status['name'] ?? '')) {
                                    'new' => 'badge-soft-primary',
                                    'processing' => 'badge-soft-warning',
                                    'resolved' => 'badge-soft-success',
                                    'closed' => 'badge-soft-dark',
                                    default => 'badge-soft-light',
                                    };
                                    @endphp
                                    <option value="{{ $status['id'] }}" {{ $statusId == $status['id'] ? 'selected' : '' }}>
                                        {{ translate($status['name']) }}
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn--primary text-nowrap apply-ticket-filters">
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
                {{translate('Inbox_list')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $tickets->total() }}</span>
            </h5>

            <div class="dropdown">
                <a type="button" class="btn btn-outline--primary text-nowrap"
                    href="{{ route('admin.support-ticket.export', 'wholesale') }}?{{ http_build_query([
                            'priority' => request('priority'),
                            'status' => request('status'),
                            'searchValue' => request('searchValue')
                        ]) }}">
                    <img width="14" src="{{ dynamicAsset('public/assets/back-end/img/excel.png') }}" alt="" class="excel">
                    <span class="ps-2">{{ translate('Export') }}</span>
                </a>
            </div>

        </div>
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
                    case 56: // new
                    $statusClass = 'badge-soft-primary';
                    break;

                    case 57: // open
                    $statusClass = 'badge-soft-info';
                    break;

                    case 58: // assigned
                    $statusClass = 'badge-soft-success';
                    break;

                    case 59: // triage
                    $statusClass = 'badge-soft-secondary';
                    break;

                    case 60: // inprogress
                    $statusClass = 'badge-soft-warning';
                    break;

                    case 61: // resolved
                    $statusClass = 'badge-soft-success';
                    break;

                    case 62: // closed
                    $statusClass = 'badge-soft-dark';
                    break;

                    default:
                    $statusClass = 'badge-soft-dark';
                    break;
                    }

                    @endphp
                    <tr>
                        <td>{{ $tickets->firstItem() + $key }}</td>
                        <td>{{ $ticket->subject ?? translate('No Subject') }}</td>
                        <td>
                            @if($ticket->customer)
                            {{ $ticket->customer->f_name ?? '' }} {{ $ticket->customer->l_name ?? '' }}
                            <div class="fz-12 text-muted">{{ $ticket->customer->email ?? '' }}</div>
                            @else
                            {{ translate('Customer Not Found') }}
                            @endif
                        </td>
                        <td><span class="badge {{ $priorityClass }}">{{ ucfirst($ticket->priority) }}</span></td>
                        <td><span class="badge {{ $statusClass }}">{{ $ticket->status_details->name ?? $ticket->status }}</span></td>
                        <td>{{ $ticket->created_at->format('d M, Y H:i') }}</td>
                        <td class="text-center">

                            <a href="{{ route('admin.support-ticket.details', $ticket->id) }}"
                                class="btn btn-sm btn-outline-success">{{ translate('View') }}</a>
                            <a href="{{ route('admin.support-ticket.singleTicket', $ticket->id) }}" class="btn btn-sm btn-outline-info">{{translate('Chat')}}</a>
                            @php
                            $statusName = strtolower($ticket->status_details?->name ?? '');
                            @endphp
                            @if(in_array($statusName, ['new', 'closed']))
                            <form action="{{ route('admin.support-ticket.status') }}" method="POST" class="d-inline statusForm">
                                @csrf
                                <input type="hidden" name="id" value="{{ $ticket->id }}">

                                @php
                                switch ($statusName) {
                                case 'new':
                                $statusBtnText = 'Open';
                                break;
                                case 'closed':
                                $statusBtnText = 'Reopen';
                                break;
                                default:
                                $statusBtnText = '';
                                break;
                                }
                                @endphp

                                @if($statusBtnText)
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    {{ translate($statusBtnText) }}
                                </button>
                                @endif
                            </form>
                            @endif

                            @if(\App\Utils\Helpers::module_permission_check('crm_section', 'ticket_employee_update') || auth('admin')->user()->id == ($ticket->department?->head_id))
                            <a href="javascript:void(0)"
                                class="btn btn-sm btn-outline-secondary assign-employee-btn"
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
                            <a class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#showWholesaleFollowUpModal" data-ticket-id="{{ $ticket->id }}" data-department-id="{{ $ticket->department_id }}" data-employee-id="{{ $ticket->employee_id }}" data-status-id="{{ $ticket->status }}" data-status-name="{{ $ticket->status_details?->name ?? '' }}" title="{{ translate('Follow-up details') }}">
                                {{ translate('follow_Up') }}
                            </a>
                            @endif
                            <a href="javascript:void(0)" class="btn btn-sm btn-outline-warning escalate-btn" data-ticket-id="{{ $ticket->id }}">
                                {{ translate('Escalate') }}
                            </a>
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

<div class="modal fade" id="showWholesaleFollowUpModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="showWholesaleFollowUpModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-2 d-flex">
                <h3>{{ translate('Wholesale Follow Up') }}</h3>
                <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body pt-0">
                <form id="updateWholesaleFollowUpForm" action="{{ route('admin.complaints.update-wholesale-follow-up') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ticket_id" id="wholesale-follow-up-ticket-id">
                    <input type="hidden" name="id" id="wholesale-follow-up-id">
                    <input type="hidden" name="support_ticket_id" id="wholesale-follow-up-support-ticket-id">
                    <input type="hidden" name="department_id" id="wholesale-follow-up-department-id">
                    <input type="hidden" name="employee_id" id="wholesale-follow-up-employee-id">

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label" for="wholesale-follow-up-status">{{ translate('Select Status') }}</label>
                                <select class="js-select2-custom form-control" name="ticket-follow-up-status" id="wholesale-follow-up-status">
                                    <option value="0" selected disabled>{{ translate('Select Status') }}</option>
                                    @foreach ([
                                    ['id'=>56,'name'=>'New'],
                                    ['id'=>57,'name'=>'Open'],
                                    ['id'=>58,'name'=>'Assigned'],
                                    ['id'=>59,'name'=>'In Progress'],
                                    ['id'=>60,'name'=>'Resolved'],
                                    ['id'=>61,'name'=>'Closed'],
                                    ['id'=>62,'name'=>'Cancelled']
                                    ] as $status)
                                    <option value="{{ $status['id'] }}" data-status-name="{{ strtolower($status['name'] ?? '') }}">{{ translate($status['name']) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row d-none" id="wholesale-ticket-next-follow-up-date-row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label" for="wholesale-ticket-next-follow-up-date">{{ translate('Next Follow-Up Date') }}</label>
                                <input type="date" name="ticket-next-follow-up-date" id="wholesale-ticket-next-follow-up-date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label" for="wholesale-follow-up-note">{{ translate('Note') }}</label>
                                <textarea rows="3" class="form-control" name="ticket-follow-up-note" id="wholesale-follow-up-note" placeholder="{{ translate('Enter follow-up note') }}"></textarea>
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
            <form id="escalateTicketForm" method="POST" action="{{ route('admin.support-ticket.esclate.wholesale') }}">
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
<span id="support-ticket-ticket-id-required" data-text="{{ translate('Ticket ID is required.') }}"></span>
@endsection

@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/support-tickets.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/complaint.js')}}"></script>

@endpush

