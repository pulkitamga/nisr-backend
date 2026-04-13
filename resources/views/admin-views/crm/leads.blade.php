@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Leads'))
@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/crm.css')}}">
@endpush
@section('content')

<div class="content container-fluid">
    @php
        $selectedStatus = request('status', 'new');
        $statusOptions = [
            'all' => translate('All'),
            'new' => translate('New'),
            'working' => translate('Working'),
            'qualified' => translate('Qualified'),
            'disqualified' => translate('Disqualified'),
            'converted' => translate('Converted'),
        ];
        $activeFilterDate = request('filter_date', request('fhilter_date'));
        $toolbarFields = [
            [
                'type' => 'daterange',
                'name' => 'filter_date',
                'label' => translate('Select_Date'),
                'value' => $activeFilterDate,
                'placeholder' => translate('Select_Date'),
                'autocomplete' => 'off',
                'input_class' => 'js-daterangepicker-with-range form-control cursor-pointer',
                'attributes' => ['readonly' => 'readonly'],
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => translate('Status'),
                'value' => $selectedStatus,
                'options' => $statusOptions,
                'input_class' => 'form-control js-select2-custom set-filter',
            ],
            [
                'type' => 'number',
                'name' => 'choose_first',
                'label' => translate('Rows_to_show'),
                'value' => request('choose_first'),
                'placeholder' => translate('Ex') . ' : 200',
                'col_class' => 'col-xl-2 col-lg-6',
                'attributes' => ['min' => '1'],
            ],
            [
                'type' => 'search',
                'name' => 'searchValue',
                'label' => translate('search'),
                'value' => request('searchValue'),
                'placeholder' => translate('search_by_Name_or_Email_or_Phone'),
                'aria_label' => translate('search_by_Name_or_Email_or_Phone'),
                'col_class' => 'col-xl-4 col-lg-12',
            ],
        ];
        $toolbarSummary = [
            [
                'label' => translate('Status'),
                'value' => $statusOptions[$selectedStatus] ?? translate('All'),
            ],
        ];
        if (!request()->has('status')) {
            $toolbarSummary[] = [
                'value' => translate('default_status'),
                'muted' => true,
            ];
        }
        if (!empty($activeFilterDate)) {
            $toolbarSummary[] = [
                'label' => translate('Select_Date'),
                'value' => Str::limit($activeFilterDate, 28),
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
        if (request()->filled('choose_first')) {
            $toolbarSummary[] = [
                'label' => translate('Rows_to_show'),
                'value' => request('choose_first'),
                'muted' => true,
            ];
        }
        $headerActions = [
            [
                'type' => 'export',
                'url' => route('admin.crm.lead.export'),
                'form_id' => 'crm-lead-toolbar',
                'label' => translate('export'),
            ],
        ];
    @endphp
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('Leads')}}
            <span class="badge badge-soft-dark radius-50"></span>
        </h2>
    </div>
    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'crm-lead-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.crm.lead.index'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])
    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('Leads'),
            'listHeaderTotal' => $lead->total(),
            'listHeaderActions' => $headerActions,
        ])
        <div class="table-responsive datatable-custom">

            <table

                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th> <input type="checkbox" id="select-all">
                        </th>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('Subject')}}</th>
                        <th class="text-center">{{translate('Party_Type')}}</th>
                        <th>{{translate('Party_Name')}}</th>
                        <th>{{translate('Contact')}}</th>
                        <th>{{translate('Owner')}}</th>
                        <th>{{translate('Department')}}</th>
                        <th>{{translate('Employee')}}</th>
                        <th>{{translate('Priority')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th>{{translate('Updated At')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lead as $key=> $msg)
                    @php
                    $inbox = $msg->inboxMessages->first();
                    $purchaseOrder = $msg->purchaseOrder;
                    @endphp
                    <tr id="row-{{ $msg->id }}">
                        <td><input type="checkbox" class="message-checkbox" value="{{ $msg->id }}"></td>
                        <td>{{ $lead->firstItem() + $key }}</td>

                        <td>
                            <a href="{{ route('admin.crm.lead.show', $msg->id) }}" class="crm-primary-link">
                                {{ $inbox?->subject ?? ($purchaseOrder?->purchase_order_no ? $purchaseOrder->purchase_order_no . ', ' . translate('Purchase_Order_Created') : translate('Purchase_Order_Created')) }}
                            </a>
                        </td>
                        <td>{{ ucfirst($msg->party_type ?? translate('Unknown')) }}</td>

                        <td>
                            {{ $inbox?->sender_name ?? $purchaseOrder?->wholeseller?->name ?? translate('Unknown') }}
                        </td>
                        <td>
                            <a href="mailto:{{ $inbox?->sender_email ?? $purchaseOrder?->wholeseller?->email }}">
                                {{ $inbox?->sender_email ?? $purchaseOrder?->wholeseller?->email ?? translate('Not Available') }}
                            </a>
                            <br>
                            <a href="tel:{{ $inbox?->sender_phone ?? $purchaseOrder?->wholeseller?->phone }}">
                                {{ $inbox?->sender_phone ?? $purchaseOrder?->wholeseller?->phone ?? translate('Not Available') }}
                            </a>
                        </td>
                        <td>{{ $msg->owner?->name ?? translate('Not Assigned') }}</td>

                        <td>{{ $msg->department?->getTranslatedField('name') ?? translate('No Department') }}</td>

                        <td>{{ $msg->employee?->name ?? translate('Not Assigned') }}</td>
                        <td> {{ $msg?->priority ?? translate('Not Available') }}


                        <td>
                            @php
                            $status = strtolower($msg->status);
                            $statusClass = match ($status) {
                            'new' => 'text-primary bg-soft-primary',
                            'working' => 'text-secondary bg-soft-secondary',
                            'qualified' => 'bg-soft-info text-warning',
                            'converted' => 'text-success bg-soft-success',
                            'disqualified' => 'text-danger bg-soft-danger',
                            default => 'text-dark bg-soft-light',
                            };
                            @endphp

                            <span class="btn {{ $statusClass }} font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ \App\Utils\crm_status_label($msg->status) }}
                            </span>
                        </td>
                        <td><span class="bidi-ltr d-inline-block">{{ ($msg->updated_at ?? $msg->created_at)?->format('d M, Y H:i A') }}</span></td>

                        <td>
                            @php
                            $isLeadActionable = !in_array($msg->status, ['converted', 'disqualified']);
                            $canConvertLead = $isLeadActionable && !$msg->po_id && !empty($msg->department_id) && !empty($msg->owner_id) && !empty($msg->employee_id);
                            @endphp
                            <div class="crm-row-actions">
                                <div class="crm-row-actions__primary">
                                    <a href="{{ route('admin.crm.lead.show', $msg->id) }}" class="btn btn-sm btn-outline-info">
                                        {{ translate('View') }}
                                    </a>
                                    @if($canConvertLead)
                                    <a href="javascript:void(0)"
                                        class="btn btn-sm btn-outline-primary convert-btn"
                                        data-lead-id="{{ $msg->id }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#convertLeadModal">
                                        🔀 {{ translate('Convert to Deal') }}
                                    </a>
                                    @endif
                                </div>
                                @if($isLeadActionable && !$msg->po_id && (!$msg->owner_id || !$msg->department_id || !$msg->employee_id))
                                <div class="crm-row-actions__chips">
                                    @if(!$msg->owner_id)
                                    <span class="crm-row-actions__chip">{{ translate('No Owner') }}</span>
                                    @endif
                                    @if(!$msg->department_id)
                                    <span class="crm-row-actions__chip">{{ translate('No Department') }}</span>
                                    @endif
                                    @if(!$msg->employee_id)
                                    <span class="crm-row-actions__chip">{{ translate('No Employee') }}</span>
                                    @endif
                                </div>
                                @endif
                                <div class="dropdown crm-row-actions__menu">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                        <i class="tio-more-horizontal"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'lead_assign_owner'))
                                        <a href="javascript:void(0)"
                                            class="dropdown-item assign-owner-btn"
                                            data-id="{{ $msg->id }}"
                                            data-owner-id="{{ $msg->owner_id ?? '' }}"
                                            data-bs-toggle="false"
                                            data-bs-target="none">
                                            {{ $msg->owner_id ? translate('Re-Assign Owner') : translate('Assign Owner') }}
                                        </a>
                                        @endif
                                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'lead_assign_employee'))
                                        <a href="javascript:void(0)"
                                            class="dropdown-item assign-employee-btn"
                                            data-id="{{ $msg->id }}"
                                            data-department-id="{{ $msg->department->id ?? '' }}"
                                            data-head-id="{{ $msg->department->head_id ?? '' }}">
                                            {{ $msg->employee_id ? translate('Re-Assign Employee') : translate('Assign Employee') }}
                                        </a>
                                        @if(!auth('admin')->user()?->isSuperAdmin())
                                        <input type="hidden" id="fixed-department-id" value="{{ auth('admin')->user()->department_id }}">
                                        @endif
                                        @endif
                                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'lead_assign_department'))
                                        <a href="javascript:void(0)" class="dropdown-item assign-dept-btn" data-id="{{ $msg->id }}" data-department-id="{{ $msg->department->id ?? 0 }}" data-department-employee-id="0">
                                            {{ $msg->department_id ? translate('Re-Assign Department') : translate('Assign Department') }}
                                        </a>
                                        @endif
                                        @if($isLeadActionable)
                                        <div class="dropdown-divider"></div>
                                        <a href="javascript:void(0)"
                                            class="dropdown-item text-danger disqualify-btn"
                                            data-id="{{ $msg->id }}">
                                            {{ translate('Disqualify') }}
                                        </a>
                                        <a href="javascript:void(0)" class="dropdown-item text-warning escalate-btn" data-lead-id="{{ $msg->id }}">
                                            {{ translate('Escalate') }}
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $lead->links() !!}
            </div>
        </div>

        @if(count($lead)==0)
        @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
        @endif
    </div>
</div>

<div class="modal fade" id="escalateLeadModal" tabindex="-1" role="dialog" aria-labelledby="escalateLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="escalateLeadModalLabel">{{ translate('Escalate Lead') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="escalateLeadForm" method="POST" action="{{ route('admin.crm.lead.escalate') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="lead_id" id="escalateLeadId">
                    <div class="form-group">
                        <label for="escalation_reason">{{ translate('Escalation Reason') }}</label>
                        <textarea name="reason" id="escalation_reason" class="form-control" rows="4" placeholder="{{ translate('Explain why this lead needs escalation (e.g., limited access, department intervention required)...') }}" required></textarea>
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


@include('admin-views.crm.partials.convert-deal')
@include('admin-views.crm.partials.departments')
@include('admin-views.crm.partials.employee')
@include('admin-views.crm.partials.owner')


<span id="partySearchRoute" data-url="{{ route('admin.crm.lead.searchParty') }}"></span>
<span id="leadToDeal" data-url="{{ route('admin.crm.lead.convert-to-deal') }}"></span>
<span id="getUserOrdersRoute" data-url="{{ route('admin.crm.lead.user-orders') }}"></span>
<span id="getEmployeeRoute" data-url="{{ route('admin.crm.lead.getemployee') }}"></span>
<span id="assignOwnerRoute" data-url="{{ route('admin.crm.lead.assignment-update') }}"></span>
<span id="assignEmployeeRoute" data-url="{{ route('admin.crm.lead.assignment-update') }}"></span>
<span id="assignDepartmentRoute" data-url="{{ route('admin.crm.lead.assignment-update') }}"></span>
<span id="leadDisqualifyRoute" data-url="{{ route('admin.crm.lead.disqualify') }}"></span>

@endsection

@push('script')
@include('admin-views.crm.partials._crm-js-text')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/crm.js') }}" defer></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/lead.js') }}" defer></script>
@endpush
