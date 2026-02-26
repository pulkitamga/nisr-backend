@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('wholesale_Deals'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">

@endpush

@section('content')


<style>
    .modal.right .modal-dialog {
        position: fixed;
        right: 0;
        top: 0;
        margin: 0;
        width: 600px;
        height: 100%;
        transform: translateX(100%);
        transition: transform 0.3s ease-out;
    }

    .modal.right.show .modal-dialog {
        transform: translateX(0);
    }

    .modal-dialog-slideout .modal-content {
        height: 100%;
        overflow-y: auto;
    }

    /* Dropdown container */
    .custom-select2-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        max-height: 250px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        margin-top: 2px;
        padding: 0;
        list-style: none;
        display: none;
        z-index: 1055;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Dropdown items */
    .custom-select2-dropdown li {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 14px;
        color: #374151;
        transition: background 0.2s, color 0.2s;
    }

    /* Hover effect */
    .custom-select2-dropdown li:hover {
        background: #f3f4f6;
        color: #111827;
    }

    /* Active (selected) item */
    .custom-select2-dropdown li.active {
        background: #2563eb;
        color: #fff;
    }
</style>


<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('wholesale_Deals')}}
            <span class="badge badge-soft-dark radius-50"></span>
        </h2>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ url()->current() }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Select_Date') }}</label>
                        <div class="position-relative">
                            <span class="tio-calendar icon-absolute-on-right"></span>
                            <input type="text" name="fhilter_date" class="js-daterangepicker-with-range form-control cursor-pointer" value="{{request('fhilter_date')}}" placeholder="{{ translate('Select_Date') }}" autocomplete="off" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{translate('Status')}}</label>
                        <select class="form-control js-select2-custom set-filter" name="status">
                            <option {{ !request()->has('status') ?'selected':''}} disabled>
                                {{ translate('select_status') }}
                            </option>
                            <option {{ request()->has('status') && request('status') == 'all' ?'selected':''}} value="all">
                                {{ translate('All') }}
                            </option>
                            <option {{ request('status')  == 'open'?'selected':''}} value="open">
                                {{ translate('open') }}
                            </option>
                            <option {{ request('status')  == 'won'?'selected':''}} value="won">
                                {{ translate('won') }}
                            </option>
                            <option {{ request('status')  == 'lost'?'selected':''}} value="lost">
                                {{ translate('lost') }}
                            </option>

                        </select>

                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{translate('Choose_First')}}</label>
                        <input type="number" class="form-control" min="1" value="{{ request('choose_first') }}" placeholder="{{ translate('Ex') }} : 200" name="choose_first">
                    </div>
                    <div class="col-md-12">
                        <label class="d-md-block">&nbsp;</label>
                        <div class="btn--container justify-content-end">
                            <a href="{{ route('admin.crm.index') }}"
                                class="btn btn-secondary px-5">
                                {{ translate('reset') }}
                            </a>
                            <button type="submit" class="btn btn--primary">{{translate('Filter')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">
                {{translate('wholesale_Deals')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $deals->total() }}</span>
            </h5>

            <form action="{{ url()->current() }}" method="GET">
                <input type="hidden" name="fhilter_date" value="{{request('fhilter_date')}}">
                <input type="hidden" name="Channel" value="{{request('Channel')}}">
                <input type="hidden" name="status" value="{{request('status')}}">
                <input type="hidden" name="choose_first" value="{{request('choose_first')}}">
                <div class="input-group input-group-merge input-group-custom">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <i class="tio-search"></i>
                        </div>
                    </div>
                    <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                        placeholder="{{ translate('search_by_Name_or_Email_or_Phone')}}" aria-label="Search orders" value="{{ request('searchValue') }}">
                    <button type="submit" class="btn btn--primary">{{ translate('search')}}</button>
                </div>
            </form>
            <div class="dropdown">
                <a type="button" class="btn btn-outline--primary text-nowrap" href="{{route('admin.customer.export', ['sort_by' => request('sort_by'), 'choose_first' => request('choose_first'),'is_active' => request('is_active'), 'order_date' => request('order_date'),'customer_joining_date' => request('customer_joining_date'),  'searchValue' => request('searchValue')])}}">
                    <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" alt="" class="excel">
                    <span class="ps-2">{{ translate('export') }}</span>
                </a>
            </div>

        </div>
        <div class="table-responsive datatable-custom">

            <table
                style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('Converted_At')}}</th>
                        <th>{{translate('Company')}}</th>
                        <th>{{translate('Contact')}}</th>
                        <th>{{translate('Owner')}}</th>
                        <th>{{translate('Department')}}</th>
                        <th>{{translate('Employee')}}</th>
                        <th>{{translate('Quotation Status')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deals as $deal)

                    <tr>
                        <td>{{ $deal->id }}</td>
                        <td>{{ $deal->created_at->format('d M, Y H:i A') }}</td>
                        <td>{{ $deal->relatedParty->company_name ?? translate('N/A') }}</td>
                        <td>
                            <a href="mailto:{{ $deal->relatedUser->email }}">
                                {{ $deal->relatedUser->email ?? translate('Not Available') }}
                            </a>
                            <br>
                            <a href="tel:{{ $deal->relatedUser->phone }}">
                                {{ $deal->relatedUser->phone ?? translate('Not Available') }}
                            </a>
                        </td>
                        <td>{{ $deal->owner?->name ?? translate('No Owner') }}</td>
                        <td>{{ $deal->department?->name ?? translate('No Department') }}</td>
                        <td>{{ $deal->employee?->name ?? translate('No Employee') }}</td>

                        <td>
                            @php
                            $status = strtolower($deal->quotation_status);
                            $statusClass = match ($status) {
                            'draft' => 'text-dark bg-soft-dark',
                            'sent' => 'text-primary bg-soft-primary',
                            'accepted' => 'text-success bg-soft-success',
                            'rejected' => 'text-danger bg-soft-danger',
                            default => 'text-dark bg-soft-light',
                            };
                            @endphp

                            <span class="btn {{ $statusClass }} font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ ucfirst($deal->quotation_status) }}
                            </span>
                        </td>
                        <td>
                            @php
                            $status = strtolower($deal->status);
                            $statusClass = match ($status) {
                            'open' => 'text-primary bg-soft-primary',
                            'won' => 'text-success bg-soft-success',
                            'lost' => 'text-danger bg-soft-danger',
                            default => 'text-dark bg-soft-light',
                            };
                            @endphp

                            <span class="btn {{ $statusClass }} font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ ucfirst($deal->status) }}
                            </span>
                        </td>

                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a href="{{ route('admin.crm.deals.wholesale.view', $deal->id) }}" class="btn btn-sm btn-info">View</a>
                                <!-- @if(auth('admin')->user()->admin_role_id == 1)
                                <a href="javascript:void(0)"
                                    class="btn btn-sm btn-outline-secondary assign-owner-btn"
                                    data-id="{{ $deal->id }}"
                                    data-bs-toggle="false"
                                    data-bs-target="none">
                                    {{ translate('Assign Owner') }}
                                </a>
                                @endif -->


                                @if(auth('admin')->user()->admin_role_id == 1 || auth('admin')->user()->id == ($deal->department?->head_id) || auth('admin')->user()->id == ($deal->owner_id))
                                <a href="javascript:void(0)"
                                    class="btn btn-sm btn-outline-secondary assign-employee-btn"
                                    data-id="{{ $deal->id }}"
                                    data-department-id="{{ $deal->department->id ?? '' }}"
                                    data-head-id="{{ $deal->department->head_id ?? '' }}">
                                    {{ translate('Assign Employee') }}
                                </a>
                                @if(auth('admin')->id() != 1)
                                <input type="hidden" id="fixed-department-id" value="{{ auth('admin')->user()->department_id }}">
                                @endif
                                @endif

<!-- 
                                @if(auth('admin')->user()->admin_role_id == 1 || auth('admin')->user()->id == ($deal->owner_id))
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary assign-dept-btn" data-id="{{ $deal->id }}" data-department-id="{{ $deal->department->id ?? 0 }}" data-department-employee-id="0">
                                    {{ translate('Assign Department') }}
                                </a>
                                @endif -->


                                @if(\App\Utils\Helpers::module_permission_check('wholesaler_section', 'create_quotation') && is_null($deal->po_id))
                                <a href="{{ route('admin.wholesale.business.create-quotation') }}"
                                    class="btn btn-sm btn-primary create-quotation-btn"
                                    data-id="{{ $deal->id }}">
                                    Create Quotation
                                </a>
                                @elseif(is_null($deal->po_id))
                                <a href="#" class="btn btn-sm btn-primary request-quotation-btn" data-id="{{ $deal->id }}">
                                    Request Quotation
                                </a>
                                @endif

                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-warning escalate-wholesale-btn" data-deal-id="{{ $deal->id }}">
                                    {{ translate('Escalate') }}
                                </a>
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


<div class="modal fade" id="escalateWholesaleDealModal" tabindex="-1" role="dialog" aria-labelledby="escalateWholesaleDealModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="escalateWholesaleDealModalLabel">{{ translate('Escalate Deal') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="escalateWholesaleDealForm" method="POST" action="{{ route('admin.crm.deals.wholesale.escalate') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="deal_id" id="escalateWholesaleDealId">
                    <div class="form-group">
                        <label for="escalation_reason">{{ translate('Escalation Reason') }}</label>
                        <textarea name="reason" id="escalation_reason" class="form-control" rows="4" placeholder="{{ translate('Explain why this deal needs escalation...') }}" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
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


<span id="getEmployeeRoute" data-url="{{ route('admin.crm.deals.wholesale.getemployee') }}"></span>
<span id="assignOwnerRoute" data-url="{{ route('admin.crm.deals.wholesale.owner-assign') }}"></span>
<span id="assignEmployeeRoute" data-url="{{ route('admin.crm.deals.wholesale.employee-assign') }}"></span>
<span id="assignDepartmentRoute" data-url="{{ route('admin.crm.deals.wholesale.update-ticket-department') }}"></span>
@endsection

@push('script')
<script type="text/javascript">
    changeInputTypeForDateRangePicker($('input[name="order_date"]'));
    changeInputTypeForDateRangePicker($('input[name="customer_joining_date"]'));
</script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/lead.js') }}"></script>

<script>
    $(function () {

    $(document).on('click', '.escalate-wholesale-btn', function() {
        let dealId = $(this).data('deal-id');
        $('#escalateWholesaleDealId').val(dealId);
        $('#escalateWholesaleDealModal').modal('show');
    });

    if (!window.escalateHandlerAttached) {
        window.escalateHandlerAttached = true;

        $('#escalateWholesaleDealForm').on('submit', function(e) {
            e.preventDefault();
            const form = this;

            Swal.fire({
                title: '{{ translate("Are you sure?") }}',
                text: '{{ translate("This will notify the department and owner.") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ translate("Yes, Escalate") }}',
                cancelButtonText: '{{ translate("Cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }

});
</script>
<script>
    $(document).ready(function() {
        $(document).on('click', '.create-quotation-btn', function(e) {
            e.preventDefault();
            let dealId = $(this).data('id');
            let url = $(this).attr('href');

            // Check if URL already has query parameters
            if (url.indexOf('?') > -1) {
                url += '&deal_id=' + dealId;
            } else {
                url += '?deal_id=' + dealId;
            }

            window.location.href = url;
        });
    });



    $(document).on('click', '.request-quotation-btn', function(e) {
        e.preventDefault();
        let dealId = $(this).data('id');

        $.ajax({
            url: '/admin/crm/deals/wholesale/request-quotation/' + dealId,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res.status) {
                    toastr.success(res.message);
                } else {
                    toastr.error('Something went wrong');
                }
            }
        });
    });
</script>


@endpush


