@extends('layouts.back-end.app')

@section('title', translate('service_Ticket'))

@section('content')

@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0]['code'] ?? 'en';
$serviceWorkflow = \App\Support\ServiceTicketWorkflow::class;
$pageDirection = Session::get('direction') === 'rtl' ? 'rtl' : 'ltr';
@endphp
<div dir="{{ $pageDirection }}">
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/support_ticket.png')}}" alt="">
            {{translate('service_ticket')}}
            <span class="badge badge-soft-dark radius-50 fz-14">{{ $tickets->total() }}</span>
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
                                        aria-label="{{ translate('search') }}" value="{{ request('searchValue') }}">
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </div>
                            </form>
                        </div>
                        <div>
                            <div class="d-flex flex-wrap flex-sm-nowrap gap-3 justify-content-end">
                                @php
                                $priority = request()->has('priority') ? request()->input('priority') : '';
                                $statusId = request()->has('status') ? request()->input('status') : 'all';
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
                                    <option value="{{ $status['id'] }}" {{ $statusId == $status['id'] ? 'selected' : '' }}>
                                        {{ translate($status['name']) }}
                                    </option>
                                    @endforeach
                                </select>
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
                {{translate('Service_Ticket_List')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $tickets->total() }}</span>
            </h5>

            <div class="dropdown">
                <a type="button" class="btn btn-outline--primary text-nowrap"
                    href="{{ route('admin.support-ticket.export', 'service') }}?{{ http_build_query([
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
                        <th>{{translate('Service')}}</th>
                        <th>{{translate('Created_At')}}</th>
                        <th class="text-center">{{translate('Action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $latestJobIds = collect($tickets->items())->pluck('latestServiceJob.id')->filter()->values();
                    $qaConfirmedJobIds = $latestJobIds->isNotEmpty()
                        ? App\Models\ServiceJobActivity::whereIn('job_id', $latestJobIds)->where('activity_type', 'qa_confirmation')->pluck('job_id')->all()
                        : [];
                    @endphp
                    @foreach($tickets as $key => $ticket)
                    @php
                    $priorityClass = match(strtolower($ticket->priority)) {
                    'low' => 'badge-soft-primary',
                    'medium' => 'badge-soft-info',
                    'high' => 'badge-soft-warning',
                    'urgent' => 'badge-soft-danger',
                    default => 'badge-soft-dark',
                    };
                    $statusClass = match(strtolower($ticket->status_details->name ?? '')) {
                    'new' => 'badge-soft-primary',
                    'assigned' => 'badge-soft-info',
                    'scheduled' => 'badge-soft-warning',
                    'in_progress' => 'badge-soft-primary',
                    'completed' => 'badge-soft-success',
                    'closed' => 'badge-soft-dark',
                    default => 'badge-soft-primary',
                    };
                    $job = $ticket->latestServiceJob;
                    $service = $job ? $services->firstWhere('id', $job->service_sku) : null;
                    $qaConfirmed = $job ? in_array($job->id, $qaConfirmedJobIds) : false;
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
                        <td><span class="badge {{ $priorityClass }}">{{ translate($ticket->priority) }}</span></td>
                        <td><span class="badge {{ $statusClass }}">{{ translate($ticket->status_details->name ?? $ticket->status) }}</span></td>
                        <td>{{ $service ? $service->title : translate('No Service Picked') }}</td>
                        <td>{{ $ticket->created_at->format('d M, Y H:i') }}</td>
                        <td class="text-center">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('admin.support-ticket.service.singleTicket', $ticket->id) }}" class="btn btn-sm btn-outline-info">{{translate('View')}}</a>
                                <a href="{{ route('admin.support-ticket.singleTicket', $ticket->id) }}" class="btn btn-sm btn-outline-info">{{translate('Conversation')}}</a>


                                @if((int)$ticket->status === $serviceWorkflow::STATUS_NEW)
                                <span id="estimate-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                    data-route="{{ route('admin.support-ticket.service.estimate') }}"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-action="estimate">{{translate('Create Estimate')}}</span>
                                @elseif((int)$ticket->status === $serviceWorkflow::STATUS_ASSIGNED)
                                <span id="assign-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                    data-route="{{ route('admin.support-ticket.service.assign') }}"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-action="assign">{{translate('Assign')}}</span>
                                @elseif((int)$ticket->status === $serviceWorkflow::STATUS_SCHEDULED && $job)
                                <span id="schedule-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                    data-route="{{ route('admin.support-ticket.service.schedule') }}"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-job-id="{{ $job->id ?? '' }}"
                                    data-action="schedule">{{translate('Schedule')}}</span>
                                <span id="estimate-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-warning action-btn"
                                    data-route="{{ route('admin.support-ticket.service.estimate') }}"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-action="estimate">{{translate('Revise Estimate')}}</span>
                                @elseif((int)$ticket->status === $serviceWorkflow::STATUS_READY_TO_START && $job)
                                <span id="start-job-{{ $job->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                    data-route="{{ route('admin.support-ticket.service.start-job') }}"
                                    data-job-id="{{ $job->id ?? '' }}"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-action="start-job">{{translate('Start Job')}}</span>
                                @elseif((int)$ticket->status === $serviceWorkflow::STATUS_IN_PROGRESS && $job)
                                <span id="complete-job-{{ $job->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                    data-route="{{ route('admin.support-ticket.service.complete-job') }}"
                                    data-job-id="{{ $job->id ?? '' }}"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-action="complete-job">{{translate('Complete Job')}}</span>
                                <span id="change-order-{{ $job->id }}" class="btn btn-sm btn-outline-warning action-btn"
                                    data-route="{{ route('admin.support-ticket.service.change-order') }}"
                                    data-job-id="{{ $job->id ?? '' }}"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-action="change-order">{{translate('Change Order')}}</span>
                                @elseif((int)$ticket->status === $serviceWorkflow::STATUS_QA_PENDING && $job && !$qaConfirmed)
                                <span id="qa-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                    data-route="{{ route('admin.support-ticket.service.qa') }}"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-job-id="{{ $job->id ?? '' }}"
                                    data-action="qa">{{translate('QA Confirmation')}}</span>
                                @elseif((int)$ticket->status === $serviceWorkflow::STATUS_QA_PENDING && $job && $qaConfirmed)
                                <span id="close-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-success action-btn"
                                    data-route="{{ route('admin.support-ticket.service.close') }}"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-action="close-ticket">{{translate('Close Ticket')}}</span>
                                @endif
                                @if($job && $serviceWorkflow::canCancelFromStatus((int)$ticket->status))
                                <span id="cancel-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-danger action-btn"
                                    data-route="{{ route('admin.support-ticket.service.cancel') }}"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-job-id="{{ $job->id ?? '' }}"
                                    data-action="cancel-ticket">{{translate('Cancel Job')}}</span>
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
        @include('layouts.back-end._empty-state', ['text' => 'no_service_ticket_found'], ['image' => 'default'])
        @endif
    </div>
</div>

<!-- Modal for Assigning Ticket -->
<div class="modal fade" id="assignTicketModal" tabindex="-1" role="dialog" aria-labelledby="assignTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignTicketModalLabel">{{translate('Assign Ticket')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignTicketForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="assignTicketId">
                    <div class="form-group">
                        <label for="employee_id">{{translate('Technician')}}</label>
                        <select name="employee_id" id="employee_id" class="form-control" required>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="service_id">{{translate('Service')}}</label>
                        <select name="service_id" id="service_id" class="form-control" required>
                            @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="priority">{{translate('Priority')}}</label>
                        <select name="priority" id="priority" class="form-control" required>
                            <option value="low">{{translate('Low')}}</option>
                            <option value="medium">{{translate('Medium')}}</option>
                            <option value="high">{{translate('High')}}</option>
                            <option value="urgent">{{translate('Urgent')}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sla_hours">{{translate('SLA (Hours)')}}</label>
                        <input type="number" name="sla_hours" id="sla_hours" class="form-control" placeholder="24" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Assign')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="estimateTicketModal" tabindex="-1" role="dialog" aria-labelledby="estimateTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="estimateTicketModalLabel">{{translate('Create Estimate')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="estimateTicketForm" method="POST" action="{{ route('admin.support-ticket.service.estimate') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="estimateTicketId">

                    <input type="hidden" name="subtotal" id="subtotal">
                    <input type="hidden" name="tax" id="tax">

                    <!-- Select Service -->
                    <div class="form-group">
                        <label for="service_id">{{ translate('Service') }}</label>
                        <select name="service_id" id="estimate_service_id" class="form-control" required>
                            @foreach($services as $service)
                            <option value="{{ $service->id }}"
                                data-price-inshop="{{ $service->base_price_inshop }}"
                                data-price-mobile="{{ $service->base_price_mobile }}"
                                data-parts-cost="{{ $service->parts_cost }}"
                                data-travel-fee="{{ $service->travel_fee_per_km }}"
                                data-included-km="{{ $service->included_km_mobile }}"
                                data-labour-charge="{{ $service->labor_hours }}">
                                {{ $service->title }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Service Mode -->
                    <div class="form-group">
                        <label for="estimate_is_mobile">{{ translate('Service Mode') }}</label>
                        <select name="is_mobile" id="estimate_is_mobile" class="form-control">
                            <option value="0">{{ translate('In-shop') }}</option>
                            <option value="1">{{ translate('Mobile') }}</option>
                        </select>
                    </div>

                    <!-- In-shop fields -->
                    <div class="inshop-fields" style="display:none;">
                        <div class="form-group">
                            <label>{{ translate('Base Price (In-shop)') }}</label>
                            <input type="number" id="base_price_inshop" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Parts Cost') }}</label>
                            <input type="number" id="parts_cost" name="parts_cost" class="form-control" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Labor Charge') }}</label>
                            <input type="number" id="labor_charge" name="labor_charge" class="form-control" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Subtotal') }}</label>
                            <input type="number" id="subtotal_inshop" class="form-control" readonly>
                        </div>
                    </div>

                    <!-- Mobile fields -->
                    <div class="mobile-fields" style="display:none;">
                        <div class="form-group">
                            <label>{{ translate('Base Price (Mobile)') }}</label>
                            <input type="number" id="base_price_mobile" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Travel Fee per KM') }}</label>
                            <input type="number" id="travel_fee_per_km" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Travel Free up to (KM)') }}</label>
                            <input type="number" id="included_km" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Enter KM') }}</label>
                            <input type="number" id="entered_km" class="form-control" step="0.1" min="0">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Labor Charge') }}</label>
                            <input type="number" id="labor_charge_mobile" class="form-control" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Parts Cost') }}</label>
                            <input type="number" id="parts_cost_mobile" class="form-control" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Subtotal') }}</label>
                            <input type="number" id="subtotal_mobile" class="form-control" readonly>
                        </div>
                    </div>


                    <!-- Extra charge & total -->
                    <div class="form-group">
                        <label>{{ translate('Extra Charge') }}</label>
                        <input type="number" id="extra_charge" name="extra_charge" class="form-control" step="0.01">
                    </div>

                    <ul class="nav nav-tabs mb-4">
                        @foreach($languages as $lang)
                        <li class="nav-item">
                            <a class="nav-link estimate-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                                href="javascript:" id="esti-{{ $lang }}-link">
                                {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                            </a>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach($languages as $lang)
                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                        <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} estimate-language-form"
                            id="esti-{{ $lang }}-form">
                            <div class="form-group"> <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                <textarea name="description[]" class="form-control" rows="3" {{ $lang == $defaultLanguage ? 'required' : '' }}></textarea>
                            </div>

                        </div>
                        @endforeach
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Total') }}</label>
                        <input type="number" id="total" name="total" class="form-control" readonly>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Create Estimate')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="scheduleTicketModal" tabindex="-1" role="dialog" aria-labelledby="scheduleTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleTicketModalLabel">{{translate('Schedule Ticket')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="scheduleTicketForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="scheduleTicketId">
                    <input type="hidden" name="job_id" id="scheduleJobId">
                    <div class="form-group">
                        <label for="technician_id">{{translate('Technician')}}</label>
                        <select name="technician_id" id="technician_id" class="form-control" required>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="scheduled_at">{{translate('Scheduled Date/Time')}}</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="schedule_is_mobile">{{translate('Service Mode')}}</label>
                        <select name="is_mobile" id="schedule_is_mobile" class="form-control">
                            <option value="0">{{translate('In-shop')}}</option>
                            <option value="1">{{translate('Mobile')}}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Schedule')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="startJobModal" tabindex="-1" role="dialog" aria-labelledby="startJobModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="startJobModalLabel">{{translate('Start Job')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="startJobForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="startTicketId">
                    <input type="hidden" name="job_id" id="startJobId">
                    <div class="form-group">
                        <label for="gps_coordinates">{{translate('GPS Coordinates')}}</label>
                        <input type="text" name="gps_coordinates" id="gps_coordinates" class="form-control" placeholder="40.7128,-74.0060">
                    </div>
                    <div class="form-group">
                        <label for="odometer_reading">{{translate('Odometer Reading')}}</label>
                        <input type="number" name="odometer_reading" id="odometer_reading" class="form-control" placeholder="150000">
                    </div>

                    <div class="form-group">
                        <label>{{ translate('Upload Images') }}</label>
                        <input type="file" name="images[]" class="form-control" multiple>
                    </div>

                    <ul class="nav nav-tabs mb-4">
                        @foreach($languages as $lang)
                        <li class="nav-item">
                            <a class="nav-link job-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                                href="javascript:" id="job-{{ $lang }}-link">
                                {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                            </a>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach($languages as $lang)
                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                        <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} job-language-form"
                            id="job-{{ $lang }}-form">
                            <div class="form-group"> <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                <textarea name="description[]" class="form-control" rows="3" {{ $lang == $defaultLanguage ? 'required' : '' }}></textarea>
                            </div>

                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Start Job')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="completeJobModal" tabindex="-1" role="dialog" aria-labelledby="completeJobModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeJobModalLabel">{{translate('Complete Job')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="completeJobForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="completeTicketId">
                    <input type="hidden" name="job_id" id="completeJobId">

                    <div class="form-group">
                        <label for="odometer_end">{{translate('Odometer End')}}</label>
                        <input type="number" name="odometer_end" id="odometer_end" class="form-control" placeholder="150100">
                    </div>

                    <div class="form-group">
                        <label for="remarks">{{translate('Remarks')}}</label>
                        <textarea name="remarks" id="remarks" class="form-control" placeholder="{{translate('e.g., Job completed successfully')}}"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="attachments">{{translate('Attachments')}}</label>
                        <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
                    </div>

                    <!-- Signature Pad -->
                    <div class="form-group">
                        <label>{{translate('Customer Signature')}}</label>
                        <input type="hidden" name="customer_signature" id="customer_signature">
                        <canvas id="signatureCanvas" class="form-control" style="border: 1px solid #ccc; height:200px;"></canvas>
                        <button type="button" class="btn btn-sm btn-danger mt-2" id="clearSignature">{{ translate('Clear Signature') }}</button>
                    </div>

                    <!-- Parts and Labor -->
                    <div class="form-group">
                        <label>{{translate('Parts and Labor')}}</label>
                        <div id="parts-labor-container">
                            <div class="parts-labor-row">
                                <select name="items[0][item_type]" class="form-control" required>
                                    <option value="part">{{translate('Part')}}</option>
                                    <option value="labor">{{translate('Labor')}}</option>
                                </select>
                                <input type="text" name="items[0][item_name]" class="form-control my-1" placeholder="{{translate('Name')}}" required>
                                <input type="number" step="0.01" name="items[0][quantity]" class="form-control my-1" placeholder="{{translate('Quantity')}}" required>
                                <input type="number" step="0.01" name="items[0][rate]" class="form-control my-1" placeholder="{{translate('Rate')}}" required>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary mt-2" id="add-part-labor">{{translate('Add Item')}}</button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Complete Job')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="changeOrderModal" tabindex="-1" role="dialog" aria-labelledby="changeOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeOrderModalLabel">{{translate('Create Change Order')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="changeOrderForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="changeOrderTicketId">
                    <input type="hidden" name="job_id" id="changeOrderJobId">
                    <div class="form-group">
                        <label for="additional_charges">{{translate('Additional Charges')}}</label>
                        <input type="number" step="0.01" name="additional_charges" id="additional_charges" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>{{ translate('Upload Image') }}</label>
                        <input type="file" name="image" class="form-control" multiple>
                    </div>
                    <ul class="nav nav-tabs mb-4">
                        @foreach($languages as $lang)
                        <li class="nav-item">
                            <a class="nav-link order-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                                href="javascript:" id="order-{{ $lang }}-link">
                                {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                            </a>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach($languages as $lang)
                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                        <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} order-language-form"
                            id="order-{{ $lang }}-form">
                            <div class="form-group"> <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                                <textarea name="description[]" class="form-control" rows="3" {{ $lang == $defaultLanguage ? 'required' : '' }}></textarea>
                            </div>

                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Create Change Order')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="qaTicketModal" tabindex="-1" role="dialog" aria-labelledby="qaTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qaTicketModalLabel">{{translate('QA Confirmation')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="qaTicketForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="qaTicketId">
                    <input type="hidden" name="job_id" id="qaJobId">
                    <div class="form-group">
                        <label for="qa_passed">{{translate('QA Result')}}</label>
                        <select name="qa_passed" id="qa_passed" class="form-control" required>
                            <option value="1">{{translate('Passed')}}</option>
                            <option value="0">{{translate('Failed')}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="qa_notes_field">{{translate('QA Notes')}}</label>
                        <textarea name="qa_notes" id="qa_notes_field" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Submit QA')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="closeTicketModal" tabindex="-1" role="dialog" aria-labelledby="closeTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="closeTicketModalLabel">{{translate('Close Ticket')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="closeTicketForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="closeTicketId">
                    <div class="form-group">
                        <label for="close_qa_notes">{{translate('QA Notes')}}</label>
                        <textarea name="qa_notes" id="close_qa_notes" class="form-control" required></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Close Ticket')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelTicketModal" tabindex="-1" role="dialog" aria-labelledby="cancelTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelTicketModalLabel">{{translate('Cancel Ticket')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="cancelTicketForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="cancelTicketId">
                    <input type="hidden" name="job_id" id="cancelJobId">
                    <div class="form-group">
                        <label for="reason">{{translate('Cancellation Reason')}}</label>
                        <textarea name="reason" id="reason" class="form-control" placeholder="{{translate('e.g., Customer no-show')}}" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="fee_amount">{{translate('Fee Amount')}}</label>
                        <input type="number" step="0.01" name="fee_amount" id="fee_amount" class="form-control" placeholder="50.00" required>
                    </div>
                    <div class="form-group">
                        <label for="refund_amount">{{translate('Refund Amount')}}</label>
                        <input type="number" step="0.01" name="refund_amount" id="refund_amount" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Cancel Ticket')}}</button>
                </div>
            </form>
        </div>
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
            <form id="escalateTicketForm" method="POST" action="{{ route('admin.support-ticket.service.escalate') }}">
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
</div>
@endsection

@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/support-tickets.js')}}"></script>
<script>
    $(document).ready(function() {
        $('.action-btn').click(function(e) {
            e.preventDefault();
            let action = $(this).data('action');
            let route = $(this).data('route');
            let ticketId = $(this).data('ticket-id');
            let jobId = $(this).data('job-id');

            const actionsWithConfirmation = ['start-job', 'complete-job', 'close-ticket', 'cancel-ticket'];
            if (actionsWithConfirmation.includes(action)) {
                Swal.fire({
                    title: '{{ translate("Are you sure?") }}',
                    text: '{{ translate("This action cannot be undone.") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ translate("Yes") }}',
                    cancelButtonText: '{{ translate("No") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        handleAction(action, route, ticketId, jobId);
                    }
                });
            } else {
                handleAction(action, route, ticketId, jobId);
            }
        });

        function handleAction(action, route, ticketId, jobId) {
            switch (action) {
                case 'assign':
                    $('#assignTicketId').val(ticketId);
                    $('#assignTicketForm').attr('action', route);
                    $('#assignTicketModal').modal('show');
                    break;
                case 'estimate':
                    $('#estimateTicketId').val(ticketId);
                    $('#estimateTicketForm').attr('action', route);
                    applyEstimateDefaults();
                    recalculateEstimateTotals();
                    $('#estimateTicketModal').modal('show');
                    break;
                case 'schedule':
                    if (!jobId) {
                        toastr.error('{{ translate("no_job_associated") }}');
                        return;
                    }
                    $('#scheduleTicketId').val(ticketId);
                    $('#scheduleJobId').val(jobId);
                    $('#scheduleTicketForm').attr('action', route);
                    $('#scheduleTicketModal').modal('show');
                    break;
                case 'start-job':
                    if (!jobId) {
                        toastr.error('{{ translate("no_job_associated") }}');
                        return;
                    }
                    $('#startTicketId').val(ticketId);
                    $('#startJobId').val(jobId);
                    $('#startJobForm').attr('action', route);
                    $('#startJobModal').modal('show');
                    break;
                case 'complete-job':
                    if (!jobId) {
                        toastr.error('{{ translate("no_job_associated") }}');
                        return;
                    }
                    $('#completeTicketId').val(ticketId);
                    $('#completeJobId').val(jobId);
                    $('#completeJobForm').attr('action', route);
                    $('#completeJobModal').modal('show');
                    break;
                case 'change-order':
                    if (!jobId) {
                        toastr.error('{{ translate("no_job_associated") }}');
                        return;
                    }
                    $('#changeOrderTicketId').val(ticketId);
                    $('#changeOrderJobId').val(jobId);
                    $('#changeOrderForm').attr('action', route);
                    $('#changeOrderModal').modal('show');
                    break;
                case 'qa':
                    if (!jobId) {
                        toastr.error('{{ translate("no_job_associated") }}');
                        return;
                    }
                    $('#qaTicketId').val(ticketId);
                    $('#qaJobId').val(jobId);
                    $('#qaTicketForm').attr('action', route);
                    $('#qaTicketModal').modal('show');
                    break;
                case 'close-ticket':
                    $('#closeTicketId').val(ticketId);
                    $('#closeTicketForm').attr('action', route);
                    $('#closeTicketModal').modal('show');
                    break;
                case 'cancel-ticket':
                    $('#cancelTicketId').val(ticketId);
                    $('#cancelJobId').val(jobId);
                    $('#cancelTicketForm').attr('action', route);
                    $('#cancelTicketModal').modal('show');
                    break;
                default:
                    toastr.error('{{ translate("invalid_action") }}');
                    break;
            }
        }

        function applyEstimateDefaults() {
            let option = $('#estimate_service_id option:selected');

            let baseInshop = parseFloat(option.data('price-inshop')) || 0;
            let baseMobile = parseFloat(option.data('price-mobile')) || 0;
            let travelFee = parseFloat(option.data('travel-fee')) || 0;
            let includedKm = parseFloat(option.data('included-km')) || 0;
            let laborCharge = parseFloat(option.data('labour-charge')) || 0;
            let partsCost = parseFloat(option.data('parts-cost')) || 0;

            $('#base_price_inshop').val(baseInshop.toFixed(2));
            $('#base_price_mobile').val(baseMobile.toFixed(2));
            $('#travel_fee_per_km').val(travelFee.toFixed(2));
            $('#included_km').val(includedKm.toFixed(2));
            $('#labor_charge').val(laborCharge.toFixed(2));
            $('#parts_cost').val(partsCost.toFixed(2));
            $('#labor_charge_mobile').val(laborCharge.toFixed(2));
            $('#parts_cost_mobile').val(partsCost.toFixed(2));

            if ($('#estimate_is_mobile').val() === '1') {
                $('.mobile-fields').show();
                $('.inshop-fields').hide();
            } else {
                $('.inshop-fields').show();
                $('.mobile-fields').hide();
            }
        }

        function recalculateEstimateTotals() {
            let mode = $('#estimate_is_mobile').val();
            let baseInshop = parseFloat($('#base_price_inshop').val()) || 0;
            let baseMobile = parseFloat($('#base_price_mobile').val()) || 0;
            let travelFee = parseFloat($('#travel_fee_per_km').val()) || 0;
            let includedKm = parseFloat($('#included_km').val()) || 0;
            let extraCharge = parseFloat($('#extra_charge').val()) || 0;
            let subtotal = 0;

            if (mode === '1') {
                let enteredKm = parseFloat($('#entered_km').val()) || 0;
                let laborMobile = parseFloat($('#labor_charge_mobile').val()) || 0;
                let partsMobile = parseFloat($('#parts_cost_mobile').val()) || 0;
                let extraKm = Math.max(0, enteredKm - includedKm);
                let travelCharge = extraKm * travelFee;

                subtotal = baseMobile + laborMobile + partsMobile + travelCharge;
                $('#subtotal_mobile').val(subtotal.toFixed(2));

                // Keep submitted fields aligned with selected mode.
                $('#labor_charge').val(laborMobile.toFixed(2));
                $('#parts_cost').val(partsMobile.toFixed(2));
            } else {
                let laborInshop = parseFloat($('#labor_charge').val()) || 0;
                let partsInshop = parseFloat($('#parts_cost').val()) || 0;
                subtotal = baseInshop + laborInshop + partsInshop;
                $('#subtotal_inshop').val(subtotal.toFixed(2));
            }

            let tax = 0;
            let total = subtotal + extraCharge + tax;
            $('#subtotal').val(subtotal.toFixed(2));
            $('#tax').val(tax.toFixed(2));
            $('#total').val(total.toFixed(2));
        }

        $('#estimate_service_id, #estimate_is_mobile').on('change', function() {
            applyEstimateDefaults();
            recalculateEstimateTotals();
        });
        $('#entered_km, #parts_cost, #labor_charge, #parts_cost_mobile, #labor_charge_mobile, #extra_charge').on('input', function() {
            recalculateEstimateTotals();
        });
        applyEstimateDefaults();
        recalculateEstimateTotals();



        let itemIndex = 1;
        $('#add-part-labor').click(function() {
            let row = `
    <div class="parts-labor-row mt-2">
        <select name="items[${itemIndex}][item_type]" class="form-control" required>
            <option value="part">{{translate('Part')}}</option>
            <option value="labor">{{translate('Labor')}}</option>
        </select>
        <input type="text" name="items[${itemIndex}][item_name]" class="form-control my-1" placeholder="{{translate('Item Name')}}" required>
        <input type="number" step="0.01" name="items[${itemIndex}][quantity]" class="form-control my-1" placeholder="{{translate('Quantity')}}" required>
        <input type="number" step="0.01" name="items[${itemIndex}][rate]" class="form-control my-1" placeholder="{{translate('Rate')}}" required>
        <button type="button" class="btn btn-sm btn-danger remove-part-labor my-1">{{translate('Remove')}}</button>
    </div>`;
            $('#parts-labor-container').append(row);
            itemIndex++;
        });

        $(document).on('click', '.remove-part-labor', function() {
            $(this).closest('.parts-labor-row').remove();
        });

        $(document).ready(function() {
            const canvas = document.getElementById('signatureCanvas');
            const signatureInput = document.getElementById('customer_signature');
            const ctx = canvas.getContext('2d');
            let drawing = false;

            // Resize canvas
            function resizeCanvas() {
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
                ctx.lineWidth = 2;
                ctx.lineCap = "round";
                ctx.strokeStyle = "#000";
            }
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            // Mouse events
            canvas.addEventListener('mousedown', (e) => {
                drawing = true;
                ctx.beginPath();
                ctx.moveTo(e.offsetX, e.offsetY);
            });
            canvas.addEventListener('mousemove', (e) => {
                if (!drawing) return;
                ctx.lineTo(e.offsetX, e.offsetY);
                ctx.stroke();
            });
            canvas.addEventListener('mouseup', () => {
                drawing = false;
                signatureInput.value = canvas.toDataURL();
            });
            canvas.addEventListener('mouseleave', () => {
                drawing = false;
                signatureInput.value = canvas.toDataURL();
            });

            // Touch events
            canvas.addEventListener('touchstart', (e) => {
                e.preventDefault();
                drawing = true;
                const rect = canvas.getBoundingClientRect();
                const touch = e.touches[0];
                ctx.beginPath();
                ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
            });
            canvas.addEventListener('touchmove', (e) => {
                e.preventDefault();
                if (!drawing) return;
                const rect = canvas.getBoundingClientRect();
                const touch = e.touches[0];
                ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
                ctx.stroke();
            });
            canvas.addEventListener('touchend', (e) => {
                e.preventDefault();
                drawing = false;
                signatureInput.value = canvas.toDataURL();
            });

            // Clear signature
            $('#clearSignature').click(function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                signatureInput.value = '';
            });
        });


    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wireLanguageTabs = (tabSelector, formSelector, prefix) => {
            const tabs = document.querySelectorAll(tabSelector);
            if (!tabs.length) return;

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const lang = this.id.replace(prefix + '-', '').replace('-link', '');

                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    const forms = document.querySelectorAll(formSelector);
                    forms.forEach(form => form.classList.add('d-none'));

                    const selectedForm = document.getElementById(prefix + '-' + lang + '-form');
                    if (selectedForm) {
                        selectedForm.classList.remove('d-none');
                    }
                });
            });
        };

        wireLanguageTabs('.estimate-language-tab', '.estimate-language-form', 'esti');
        wireLanguageTabs('.job-language-tab', '.job-language-form', 'job');
        wireLanguageTabs('.order-language-tab', '.order-language-form', 'order');
    });
</script>
@if(session('force_close_prompt'))
<script>
    Swal.fire({
        title: '{{ translate('Payment is not paid!') }}',
        text: '{{ translate('If you agree, you can force close this ticket.') }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '{{ translate('Force Close') }}',
        cancelButtonText: '{{ translate('Cancel') }}'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit force close form automatically
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('admin.support-ticket.service.close') }}";

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            const ticket = document.createElement('input');
            ticket.type = 'hidden';
            ticket.name = 'ticket_id';
            ticket.value = '{{ session('force_close_prompt') }}';
            form.appendChild(ticket);

            const force = document.createElement('input');
            force.type = 'hidden';
            force.name = 'force_close';
            force.value = 1;
            form.appendChild(force);

            const notes = document.createElement('input');
            notes.type = 'hidden';
            notes.name = 'qa_notes';
            notes.value = '{{ translate('Force closed manually without payment') }}';
            form.appendChild(notes);

            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
@endif

<script>
    $(document).on('click', '.escalate-btn', function() {
        let ticketId = $(this).data('ticket-id');
        $('#escalateTicketId').val(ticketId);
        $('#escalateTicketModal').modal('show');
    });

    $('#escalateTicketForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        Swal.fire({
            title: '{{ translate('Are you sure?') }}',
            text: '{{ translate('This will notify the department and owner.') }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '{{ translate('Yes, Escalate') }}',
            cancelButtonText: '{{ translate('Cancel') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                form.off('submit').submit();
            }
        });
    });
</script>

@endpush
