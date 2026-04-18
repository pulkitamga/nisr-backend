@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('service_Ticket'))
@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/crm.css')}}">
@endpush

@section('content')

@php
$languages = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $languages ?? [], true)) {
    $defaultLanguage = $languages[0] ?? 'en';
}
$serviceWorkflow = \App\Support\ServiceTicketWorkflow::class;
$pageDirection = Session::get('direction') === 'rtl' ? 'rtl' : 'ltr';
$priority = request()->has('priority') ? request()->input('priority') : 'all';
$statusId = request()->has('status') ? request()->input('status') : 'all';
$selectedStatus = $aAllStatus->firstWhere('id', (int) $statusId);
$selectedStatusLabel = $statusId === 'all'
    ? translate('all_Status')
    : ($selectedStatus ? \App\Utils\crm_status_label($selectedStatus->getTranslatedField('name') ?? $selectedStatus->name) : translate('all_Status'));
$toolbarFields = [
    [
        'type' => 'search',
        'name' => 'searchValue',
        'label' => translate('Search'),
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
            (string) $statusOption['id'] => \App\Utils\crm_status_label($statusOption->getTranslatedField('name') ?? $statusOption->name),
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
if ($priority !== 'all') {
    $toolbarSummary[] = [
        'label' => translate('Priority'),
        'value' => translate($priority),
        'muted' => true,
    ];
}
if (request()->filled('searchValue')) {
    $toolbarSummary[] = [
        'label' => translate('Search'),
        'value' => Str::limit(request('searchValue'), 28),
        'muted' => true,
    ];
}
$headerActions = [
    [
        'type' => 'export',
        'url' => route('admin.support-ticket.export', 'service'),
        'form_id' => 'crm-service-ticket-toolbar',
        'label' => translate('export'),
    ],
];
@endphp
<div dir="{{ $pageDirection }}">
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/support_ticket.png')}}" alt="">
            {{translate('service_Ticket')}}
            <span class="badge badge-soft-dark radius-50 fz-14">{{ $tickets->total() }}</span>
        </h2>
    </div>
    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'crm-service-ticket-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.support-ticket.view', 'service'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])
    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('Service_Ticket_List'),
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
                    $normalizedStatusName = \Illuminate\Support\Str::snake((string)($ticket->status_details->name ?? ''));
                    $priorityClass = match(strtolower($ticket->priority)) {
                    'low' => 'badge-soft-primary',
                    'medium' => 'badge-soft-info',
                    'high' => 'badge-soft-warning',
                    'urgent' => 'badge-soft-danger',
                    default => 'badge-soft-dark',
                    };
                    $statusClass = match($normalizedStatusName) {
                    'new' => 'badge-soft-primary',
                    'open' => 'badge-soft-primary',
                    'assigned' => 'badge-soft-info',
                    'scheduled' => 'badge-soft-warning',
                    'in_progress' => 'badge-soft-primary',
                    'completed' => 'badge-soft-success',
                    'closed' => 'badge-soft-dark',
                    default => 'badge-soft-primary',
                    };
                    $job = $ticket->latestServiceJob;
                    $service = $ticket->service
                        ?? $job?->service
                        ?? $services->firstWhere('id', (int) data_get($ticket->relatedInboxMessage?->details, 'service_id'));
                    $qaConfirmed = $job ? in_array($job->id, $qaConfirmedJobIds) : false;
                    @endphp
                    <tr>
                        <td>{{ $tickets->firstItem() + $key }}</td>
                        <td>
                            <a href="{{ route('admin.support-ticket.service.singleTicket', $ticket->id) }}" class="crm-primary-link">
                                {{ $ticket->subject ?? translate('No Subject') }}
                            </a>
                        </td>
                        <td>
                            @if($ticket->customer)
                            {{ $ticket->customer->f_name ?? '' }} {{ $ticket->customer->l_name ?? '' }}
                            <div class="fz-12 text-muted">{{ $ticket->customer->email ?? '' }}</div>
                            @else
                            {{ translate('customer_not_found') }}
                            @endif
                        </td>
                        <td><span class="badge {{ $priorityClass }}">{{ translate($ticket->priority) }}</span></td>
                        <td><span class="badge {{ $statusClass }}">{{ \App\Utils\crm_status_label($ticket->status_details?->getTranslatedField('name') ?? $ticket->status_details?->name ?? $ticket->status, 'N/A') }}</span></td>
                        <td>{{ $service ? $service->title : translate('No Service Picked') }}</td>
                        <td><span class="bidi-ltr d-inline-block">{{ $ticket->created_at->format('d M, Y H:i') }}</span></td>
                        <td class="text-center">
                            <div class="crm-row-actions">
                                <div class="crm-row-actions__primary">
                                    <a href="{{ route('admin.support-ticket.service.singleTicket', $ticket->id) }}" class="btn btn-sm btn-outline-info">{{translate('View')}}</a>

                                    @if((int)$ticket->status === $serviceWorkflow::STATUS_NEW)
                                    <button type="button" id="estimate-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                        data-route="{{ route('admin.support-ticket.service.estimate') }}"
                                        data-ticket-id="{{ $ticket->id }}"
                                        data-service-id="{{ $ticket->service_id }}"
                                        data-action="estimate">{{translate('Create Estimate')}}</button>
                                    @elseif((int)$ticket->status === $serviceWorkflow::STATUS_OPEN)
                                    <button type="button" id="assign-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                        data-route="{{ route('admin.support-ticket.service.assign') }}"
                                        data-ticket-id="{{ $ticket->id }}"
                                        data-service-id="{{ $ticket->service_id }}"
                                        data-action="assign">{{translate('Assign')}}</button>
                                    @elseif((int)$ticket->status === $serviceWorkflow::STATUS_ASSIGNED && $job)
                                    <button type="button" id="schedule-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                        data-route="{{ route('admin.support-ticket.service.schedule') }}"
                                        data-ticket-id="{{ $ticket->id }}"
                                        data-job-id="{{ $job->id ?? '' }}"
                                        data-action="schedule">{{translate('Schedule')}}</button>
                                    @elseif((int)$ticket->status === $serviceWorkflow::STATUS_SCHEDULED && $job)
                                    <button type="button" id="start-job-{{ $job->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                        data-route="{{ route('admin.support-ticket.service.start-job') }}"
                                        data-job-id="{{ $job->id ?? '' }}"
                                        data-ticket-id="{{ $ticket->id }}"
                                        data-action="start-job">{{translate('Start Job')}}</button>
                                    @elseif((int)$ticket->status === $serviceWorkflow::STATUS_IN_PROGRESS && $job)
                                    <button type="button" id="complete-job-{{ $job->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                        data-route="{{ route('admin.support-ticket.service.complete-job') }}"
                                        data-job-id="{{ $job->id ?? '' }}"
                                        data-ticket-id="{{ $ticket->id }}"
                                        data-action="complete-job">{{translate('Complete Job')}}</button>
                                    @elseif((int)$ticket->status === $serviceWorkflow::STATUS_COMPLETED && $job && !$qaConfirmed)
                                    <button type="button" id="qa-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-primary action-btn"
                                        data-route="{{ route('admin.support-ticket.service.qa') }}"
                                        data-ticket-id="{{ $ticket->id }}"
                                        data-job-id="{{ $job->id ?? '' }}"
                                        data-action="qa">{{translate('QA Confirmation')}}</button>
                                    @elseif((int)$ticket->status === $serviceWorkflow::STATUS_COMPLETED && $job && $qaConfirmed)
                                    <button type="button" id="close-ticket-{{ $ticket->id }}" class="btn btn-sm btn-outline-success action-btn"
                                        data-route="{{ route('admin.support-ticket.service.close') }}"
                                        data-ticket-id="{{ $ticket->id }}"
                                        data-action="close-ticket">{{translate('Close Ticket')}}</button>
                                    @endif
                                </div>
                                <div class="dropdown crm-row-actions__menu">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                        <i class="tio-more-horizontal"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="{{ route('admin.support-ticket.singleTicket', $ticket->id) }}" class="dropdown-item">{{translate('Conversation')}}</a>
                                        @if((int)$ticket->status === $serviceWorkflow::STATUS_ASSIGNED && $job)
                                        <button type="button" id="estimate-ticket-{{ $ticket->id }}" class="dropdown-item action-btn"
                                            data-route="{{ route('admin.support-ticket.service.estimate') }}"
                                            data-ticket-id="{{ $ticket->id }}"
                                            data-service-id="{{ $ticket->service_id }}"
                                            data-action="estimate">{{translate('Revise Estimate')}}</button>
                                        @endif
                                        @if((int)$ticket->status === $serviceWorkflow::STATUS_IN_PROGRESS && $job)
                                        <button type="button" id="change-order-{{ $job->id }}" class="dropdown-item action-btn"
                                            data-route="{{ route('admin.support-ticket.service.change-order') }}"
                                            data-job-id="{{ $job->id ?? '' }}"
                                            data-ticket-id="{{ $ticket->id }}"
                                            data-action="change-order">{{translate('Change Order')}}</button>
                                        @endif
                                        @if($job && $serviceWorkflow::canCancelFromStatus((int)$ticket->status))
                                        <div class="dropdown-divider"></div>
                                        <button type="button" id="cancel-ticket-{{ $ticket->id }}" class="dropdown-item text-danger action-btn"
                                            data-route="{{ route('admin.support-ticket.service.cancel') }}"
                                            data-ticket-id="{{ $ticket->id }}"
                                            data-job-id="{{ $job->id ?? '' }}"
                                            data-action="cancel-ticket">{{translate('Cancel Job')}}</button>
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
        @include('layouts.back-end._empty-state', ['text' => 'no_service_ticket_found'], ['image' => 'default'])
        @endif
    </div>
</div>

<!-- Modal for Assigning Ticket -->
<div class="modal fade" id="assignTicketModal" tabindex="-1" role="dialog" aria-labelledby="assignTicketModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="assignTicketModalLabel">{{translate('Assign Ticket')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="estimateTicketModalLabel">{{translate('Create Estimate')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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
                            <label>{{ translate('parts_cost') }}</label>
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
                            <label>{{ translate('travel_fee_per_km') }}</label>
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
                            <label>{{ translate('parts_cost') }}</label>
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

                    <div class="form-group">
                        <label>{{ translate('Description') }}</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
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
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleTicketModalLabel">{{translate('Schedule Ticket')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="startJobModalLabel">{{translate('Start Job')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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

                    <div class="form-group">
                        <label>{{ translate('Description') }}</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
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
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="completeJobModalLabel">{{translate('Complete Job')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="changeOrderModalLabel">{{translate('Create Change Order')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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
                        <label>{{ translate('Upload_Image') }}</label>
                        <input type="file" name="image" class="form-control" multiple>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Description') }}</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
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
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="qaTicketModalLabel">{{translate('QA Confirmation')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="closeTicketModalLabel">{{translate('Close Ticket')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelTicketModalLabel">{{translate('Cancel Ticket')}}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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
                        <label for="refund_amount">{{translate('Refund_Amount')}}</label>
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
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="escalateTicketModalLabel">{{ translate('Escalate Ticket') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
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
<span id="support-ticket-escalate-warning" data-text="{{ translate('This will notify the department and owner.') }}"></span>
<span id="support-ticket-yes-escalate" data-text="{{ translate('Yes, Escalate') }}"></span>
<span id="support-ticket-something-went-wrong" data-text="{{ translate('Something_went_wrong') }}"></span>
<span id="service-ticket-are-you-sure" data-text="{{ translate('Are you sure?') }}"></span>
<span id="service-ticket-action-cannot-be-undone" data-text="{{ translate('This action cannot be undone.') }}"></span>
<span id="service-ticket-yes" data-text="{{ translate('Yes') }}"></span>
<span id="service-ticket-no" data-text="{{ translate('No') }}"></span>
<span id="service-ticket-invalid-action" data-text="{{ translate('invalid_action') }}"></span>
<span id="service-ticket-no-job-associated" data-text="{{ translate('no_job_associated') }}"></span>
<span id="service-ticket-part-label" data-text="{{ translate('Part') }}"></span>
<span id="service-ticket-labor-label" data-text="{{ translate('Labor') }}"></span>
<span id="service-ticket-item-name" data-text="{{ translate('Item Name') }}"></span>
<span id="service-ticket-quantity" data-text="{{ translate('Quantity') }}"></span>
<span id="service-ticket-rate" data-text="{{ translate('Rate') }}"></span>
<span id="service-ticket-remove" data-text="{{ translate('Remove') }}"></span>
<span id="service-ticket-force-close-title" data-text="{{ translate('Payment is not paid!') }}"></span>
<span id="service-ticket-force-close-text" data-text="{{ translate('If you agree, you can force close this ticket.') }}"></span>
<span id="service-ticket-force-close-confirm" data-text="{{ translate('Force Close') }}"></span>
<span id="service-ticket-force-close-cancel" data-text="{{ translate('Cancel') }}"></span>
<span id="service-ticket-force-close-note" data-text="{{ translate('Force closed manually without payment') }}"></span>
<span id="service-ticket-force-close"
    data-ticket-id="{{ session('force_close_prompt', '') }}"
    data-route="{{ route('admin.support-ticket.service.close') }}"
    data-csrf="{{ csrf_token() }}"></span>
@endsection

@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/support-tickets.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/service-ticket.js')}}"></script>
@endpush
