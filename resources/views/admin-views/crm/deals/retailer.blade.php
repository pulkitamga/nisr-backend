@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('retail_Deals'))

@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/crm.css')}}">
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">

@endpush

@section('content')



<div class="content container-fluid">
    @php
        $selectedStatus = request('status', 'open');
        $statusOptions = [
            'all' => translate('All'),
            'open' => translate('open'),
            'won' => translate('won'),
            'lost' => translate('lost'),
            'closed' => translate('closed'),
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
                'url' => route('admin.crm.deals.retail.export'),
                'form_id' => 'crm-retail-deals-toolbar',
                'label' => translate('export'),
            ],
        ];
    @endphp
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('retail_Deals')}}
            <span class="badge badge-soft-dark radius-50"></span>
        </h2>
    </div>
    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'crm-retail-deals-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.crm.deals.retail.list'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])
    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('retail_Deals'),
            'listHeaderTotal' => $deals->total(),
            'listHeaderActions' => $headerActions,
        ])
        <div class="table-responsive datatable-custom">

            <table

                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('Converted_At')}}</th>
                        <th>{{translate('Customer')}}</th>
                        <th>{{translate('Contact')}}</th>
                        <th>{{translate('Owner')}}</th>
                        <th>{{translate('Department')}}</th>
                        <th>{{translate('Employee')}}</th>
                        <th>{{translate('Order Status')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deals as $deal)

                    <tr>
                        <td>{{ $deal->id }}</td>
                        <td><span class="bidi-ltr d-inline-block">{{ $deal->created_at->format('d M, Y H:i A') }}</span></td>
                        <td>
                            @if($deal->relatedParty)
                                <a href="{{ route('admin.crm.deals.retail.view', $deal->id) }}" class="crm-primary-link">{{ $deal->relatedParty->name }}</a>
                            @else
                                {{ translate('N/A') }}
                            @endif
                        </td>
                        <td>
                            <a href="mailto:{{ $deal->relatedParty->email ?? '' }}">
                                {{ $deal->relatedParty->email ?? translate('Not Available') }}
                            </a>
                            <br>
                            <a href="tel:{{ $deal->relatedParty->phone ?? '' }}">
                                {{ $deal->relatedParty->phone ?? translate('Not Available') }}
                            </a>
                        </td>
                        <td>{{ $deal->owner?->name ?? translate('No Owner') }}</td>
                        <td>{{ $deal->department?->getTranslatedField('name') ?? translate('No Department') }}</td>
                        <td>{{ $deal->employee?->name ?? translate('No Employee') }}</td>

                        <td>
                            @if($deal->order)
                            @php
                            $status = strtolower($deal->order->order_status);
                            $statusClass = match ($status) {
                            'pending', 'processing' => 'text-dark bg-soft-dark',
                            'confirmed' => 'text-primary bg-soft-primary',
                            'delivered' => 'text-success bg-soft-success',
                            'canceled', 'failed', 'returned' => 'text-danger bg-soft-danger',
                            default => 'text-dark bg-soft-light',
                            };
                            @endphp
                            <span class="btn {{ $statusClass }} font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ ucfirst(str_replace('_', ' ', $deal->order->order_status)) }}
                            </span>
                            @else
                            <span class="btn text-warning bg-soft-warning font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ translate('No_Order') }}
                            </span>
                            @endif
                        </td>
                        <td>
                            @php
                            $status = strtolower($deal->status);
                            $statusClass = match ($status) {
                            'open' => 'text-primary bg-soft-primary',
                            'won' => 'text-success bg-soft-success',
                            'lost' => 'text-danger bg-soft-danger',
                            'closed' => 'text-dark bg-soft-dark',
                            default => 'text-dark bg-soft-light',
                            };
                            @endphp

                            <span class="btn {{ $statusClass }} font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ ucfirst($deal->status) }}
                            </span>
                        </td>

                        <td>
                            <div class="crm-row-actions">
                                <div class="crm-row-actions__primary">
                                    <a href="{{ route('admin.crm.deals.retail.view', $deal->id) }}" class="btn btn-sm btn-info">{{ translate('View') }}</a>
                                    @if(!$deal->order)
                                    <button type="button" class="btn btn-sm btn-success link-order-btn"
                                        data-deal-id="{{ $deal->id }}"
                                        data-user-id="{{ $deal->related_party_id }}"
                                        data-user-name="{{ $deal->relatedParty->name ?? translate('User') }}">
                                        {{ translate('Link Order') }}
                                    </button>
                                    @endif
                                </div>
                                @if(!$deal->owner_id || !$deal->department_id || !$deal->employee_id)
                                <div class="crm-row-actions__chips">
                                    @if(!$deal->owner_id)
                                    <span class="crm-row-actions__chip">{{ translate('No Owner') }}</span>
                                    @endif
                                    @if(!$deal->department_id)
                                    <span class="crm-row-actions__chip">{{ translate('No Department') }}</span>
                                    @endif
                                    @if(!$deal->employee_id)
                                    <span class="crm-row-actions__chip">{{ translate('No Employee') }}</span>
                                    @endif
                                </div>
                                @endif
                                <div class="dropdown crm-row-actions__menu">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                        <i class="tio-more-horizontal"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'deal_retail_assign_owner'))
                                        <a href="javascript:void(0)"
                                            class="dropdown-item assign-owner-btn"
                                            data-id="{{ $deal->id }}"
                                            data-owner-id="{{ $deal->owner_id ?? '' }}"
                                            data-bs-toggle="false"
                                            data-bs-target="none">
                                            {{ translate('Assign Owner') }}
                                        </a>
                                        @endif
                                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'deal_retail_assign_employee'))
                                        <a href="javascript:void(0)"
                                            class="dropdown-item assign-employee-btn"
                                            data-id="{{ $deal->id }}"
                                            data-department-id="{{ $deal->department->id ?? '' }}"
                                            data-head-id="{{ $deal->department->head_id ?? '' }}">
                                            {{ translate('Assign Employee') }}
                                        </a>
                                        @if(!auth('admin')->user()?->isSuperAdmin())
                                        <input type="hidden" id="fixed-department-id" value="{{ auth('admin')->user()->department_id }}">
                                        @endif
                                        @endif
                                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'deal_retail_assign_department'))
                                        <a href="javascript:void(0)" class="dropdown-item assign-dept-btn" data-id="{{ $deal->id }}" data-department-id="{{ $deal->department->id ?? 0 }}" data-department-employee-id="0">
                                            {{ translate('Assign Department') }}
                                        </a>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'deal_retail_disqualify') && strtolower((string)$deal->status) === 'open')
                                        @if(!in_array(strtolower((string)($deal->quotation_status ?? 'draft')), ['draft', ''], true))
                                        <a href="javascript:void(0)" class="dropdown-item text-danger deal-mark-lost-btn" data-deal-id="{{ $deal->id }}">
                                            {{ translate('Mark Lost') }}
                                        </a>
                                        @else
                                        <a href="javascript:void(0)" class="dropdown-item text-danger deal-disqualify-btn" data-deal-id="{{ $deal->id }}">
                                            {{ translate('Disqualify') }}
                                        </a>
                                        @endif
                                        <a href="javascript:void(0)" class="dropdown-item deal-close-btn" data-deal-id="{{ $deal->id }}">
                                            {{ translate('Close') }}
                                        </a>
                                        @endif
                                        <a href="javascript:void(0)" class="dropdown-item text-warning escalate-btn" data-deal-id="{{ $deal->id }}">
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
        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $deals->links() !!}
            </div>
        </div>

        @if(count($deals)==0)
        @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
        @endif
    </div>
</div>

<!-- Escalation Modal for Retail Deals -->
<div class="modal fade" id="escalateRetailDealModal" tabindex="-1" role="dialog" aria-labelledby="escalateRetailDealModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title" id="escalateRetailDealModalLabel">{{ translate('Escalate Deal') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="escalateRetailDealForm" method="POST" action="{{ route('admin.crm.deals.retail.escalate') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="deal_id" id="escalateRetailDealId">
                    <div class="form-group">
                        <label for="escalation_reason">{{ translate('Escalation Reason') }}</label>
                        <textarea name="reason" id="escalation_reason" class="form-control" rows="4" placeholder="{{ translate('Explain why this deal needs escalation...') }}" required></textarea>
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
@include('admin-views.crm.partials.link-order')
@include('admin-views.crm.partials.departments')
@include('admin-views.crm.partials.employee')
@include('admin-views.crm.partials.owner')


<span id="getEmployeeRoute" data-url="{{ route('admin.crm.deals.retail.getemployee') }}"></span>
<span id="assignOwnerRoute" data-url="{{ route('admin.crm.deals.retail.owner-assign') }}"></span>
<span id="assignEmployeeRoute" data-url="{{ route('admin.crm.deals.retail.employee-assign') }}"></span>
<span id="assignDepartmentRoute" data-url="{{ route('admin.crm.deals.retail.update-ticket-department') }}"></span>
<span id="getUserOrdersRoute" data-url="{{ route('admin.crm.deals.retail.get-user-orders') }}"></span>
<span id="linkOrderRoute" data-url="{{ route('admin.crm.deals.retail.link-order') }}"></span>
<span id="dealDisqualifyRoute" data-url="{{ route('admin.crm.deals.retail.disqualify') }}"></span>
<span id="dealMarkLostRoute" data-url="{{ route('admin.crm.deals.retail.mark-lost') }}"></span>
<span id="dealCloseRoute" data-url="{{ route('admin.crm.deals.retail.close') }}"></span>
<span id="crm-deal-currency"
    data-symbol="{{ getCurrencySymbol(currencyCode: getCurrencyCode(), type: 'default') }}"
    data-position="{{ getWebConfig('currency_symbol_position') ?? 'left' }}"
    data-space="{{ (string)(getWebConfig('currency_symbol_space') ?? '0') === '1' ? '1' : '0' }}"
    data-decimals="{{ (int)(getWebConfig('decimal_point_settings') ?? 2) }}">
</span>
<span id="crm-deal-are-you-sure" data-text="{{ translate('Are you sure?') }}"></span>
<span id="crm-deal-yes" data-text="{{ translate('Yes') }}"></span>
<span id="crm-deal-cancel" data-text="{{ translate('Cancel') }}"></span>
<span id="crm-deal-success" data-text="{{ translate('Success') }}"></span>
<span id="crm-deal-error" data-text="{{ translate('Error') }}"></span>
<span id="crm-deal-updated-successfully" data-text="{{ translate('Updated successfully') }}"></span>
<span id="crm-deal-something-went-wrong" data-text="{{ translate('Something went wrong') }}"></span>
<span id="crm-deal-disqualify-title" data-text="{{ translate('Disqualify Deal?') }}"></span>
<span id="crm-deal-disqualify-body" data-text="{{ translate('This should be used before sending quotation.') }}"></span>
<span id="crm-deal-mark-lost-title" data-text="{{ translate('Mark Deal Lost?') }}"></span>
<span id="crm-deal-mark-lost-body" data-text="{{ translate('Use this after quotation is sent.') }}"></span>
<span id="crm-deal-close-title" data-text="{{ translate('Close Deal?') }}"></span>
<span id="crm-deal-close-body" data-text="{{ translate('Review logic must be completed before close.') }}"></span>
<span id="crm-deal-escalate-warning" data-text="{{ translate('This will notify the department and owner.') }}"></span>
<span id="crm-deal-yes-escalate" data-text="{{ translate('Yes, Escalate') }}"></span>
<span id="crm-deal-no-orders-found" data-text="{{ translate('No orders found for this customer.') }}"></span>
<span id="crm-deal-action-label" data-text="{{ translate('Action') }}"></span>
<span id="crm-deal-order-id-label" data-text="{{ translate('Order ID') }}"></span>
<span id="crm-deal-date-label" data-text="{{ translate('Date') }}"></span>
<span id="crm-deal-amount-label" data-text="{{ translate('Amount') }}"></span>
<span id="crm-deal-status-label" data-text="{{ translate('Status') }}"></span>
<span id="crm-deal-link-label" data-text="{{ translate('Link') }}"></span>
<span id="crm-deal-failed-orders" data-text="{{ translate('Failed to load orders. Please try again.') }}"></span>
<span id="crm-deal-link-order-title" data-text="{{ translate('Link Order?') }}"></span>
<span id="crm-deal-link-order-body" data-text="{{ translate('Order will be linked to the selected deal.') }}"></span>
<span id="crm-deal-yes-link-it" data-text="{{ translate('Yes, Link it!') }}"></span>
<span id="crm-deal-linked-title" data-text="{{ translate('Linked!') }}"></span>
<span id="crm-deal-order-linked" data-text="{{ translate('Order linked successfully!') }}"></span>
<span id="crm-deal-failed-title" data-text="{{ translate('Failed') }}"></span>
<span id="crm-deal-server-error" data-text="{{ translate('Server error. Please try again.') }}"></span>

@endsection

@push('script')
@include('admin-views.crm.partials._crm-js-text')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/crm.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/crm-deals.js') }}"></script>
@endpush
