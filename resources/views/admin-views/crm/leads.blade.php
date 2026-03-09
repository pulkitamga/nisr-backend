@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Leads'))
@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/crm.css')}}">
@endpush
@section('content')

<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('Leads')}}
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
                            <input type="text" name="filter_date" class="js-daterangepicker-with-range form-control cursor-pointer" value="{{ request('filter_date', request('fhilter_date')) }}" placeholder="{{ translate('Select_Date') }}" autocomplete="off" readonly>
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
                            <option {{ request('status')  == 'new'?'selected':''}} value="new">
                                {{ translate('New') }}
                            </option>
                            <option {{ request('status')  == 'working'?'selected':''}} value="working">
                                {{ translate('Working') }}
                            </option>
                            <option {{ request('status')  == 'qualified'?'selected':''}} value="qualified">
                                {{ translate('Qualified') }}
                            </option>
                            <option {{ request('status')  == 'disqualified'?'selected':''}} value="disqualified">
                                {{ translate('Disqualified') }}
                            </option>
                            <option {{ request('status')  == 'converted'?'selected':''}} value="converted">
                                {{ translate('Converted') }}
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
                            <a href="{{ route('admin.crm.lead.index') }}"
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
                {{translate('Leads')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $lead->total() }}</span>
            </h5>

            <form action="{{ url()->current() }}" method="GET">
                <input type="hidden" name="filter_date" value="{{ request('filter_date', request('fhilter_date')) }}">
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
                        placeholder="{{ translate('search_by_Name_or_Email_or_Phone')}}" aria-label="{{ translate('Search orders') }}" value="{{ request('searchValue') }}">
                    <button type="submit" class="btn btn--primary">{{ translate('search')}}</button>
                </div>
            </form>
            <div class="dropdown">
                <a type="button"
                    class="btn btn-outline--primary text-nowrap"
                    href="{{ route('admin.crm.lead.export', [
                        'filter_date' => request('filter_date', request('fhilter_date')),
                        'status'       => request('status'),
                        'choose_first' => request('choose_first'),
                        'searchValue'  => request('searchValue'),
                ]) }}">
                    <img width="14" src="{{ dynamicAsset(path: 'public/assets/back-end/img/excel.png') }}" alt="" class="excel">
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
                    $purchaseOrder = \App\Models\WholesalePurchaseOrder::find($msg->source_id);
                    @endphp
                    <tr id="row-{{ $msg->id }}">
                        <td><input type="checkbox" class="message-checkbox" value="{{ $msg->id }}"></td>
                        <td>{{ $lead->firstItem() + $key }}</td>

                        <td>
                            <a href="">
                                {{ $inbox?->subject ??  ($purchaseOrder?->purchase_order_no .', '. 'Purchase Order Created' ?? 'Purchase Order Created') }}
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

                        <td>{{ $msg->department?->name ?? translate('No Department') }}</td>

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
                                {{ ucfirst($msg->status) }}
                            </span>
                        </td>
                        <td>{{ ($msg->updated_at ?? $msg->created_at)?->format('d M, Y H:i A') }}</td>

                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a href="{{ route('admin.crm.lead.show', $msg->id) }}" class="btn btn-sm btn-outline-info">
                                    {{ translate('View') }}
                                </a>
                                <!-- <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary reply-btn" data-id="{{ $msg->id }}">
                                    {{ translate('Reply') }}
                                </a> -->
                                @if(\App\Utils\Helpers::module_permission_check('crm_section', 'lead_assign_owner'))
                                <a href="javascript:void(0)"
                                    class="btn btn-sm btn-outline-secondary assign-owner-btn"
                                    data-id="{{ $msg->id }}"
                                    data-owner-id="{{ $msg->owner_id ?? '' }}"
                                    data-bs-toggle="false"
                                    data-bs-target="none">
                                    {{ $msg->owner_id ? translate('Re-Assign Owner') : translate('Assign Owner') }}
                                </a>
                                @endif
                                @if(\App\Utils\Helpers::module_permission_check('crm_section', 'lead_assign_employee'))
                                <a href="javascript:void(0)"
                                    class="btn btn-sm btn-outline-secondary assign-employee-btn"
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
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary assign-dept-btn" data-id="{{ $msg->id }}" data-department-id="{{ $msg->department->id ?? 0 }}" data-department-employee-id="0">
                                    {{ $msg->department_id ? translate('Re-Assign Department') : translate('Assign Department') }}
                                </a>
                                @endif
                                @if(!in_array($msg->status, ['converted', 'disqualified']) && !$msg->po_id)
                                    @if(!empty($msg->department_id) && !empty($msg->owner_id) && !empty($msg->employee_id))
                                    <a href="javascript:void(0)"
                                        class="btn btn-sm btn-outline-primary convert-btn"
                                        data-lead-id="{{ $msg->id }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#convertLeadModal">
                                        🔀 {{ translate('Convert to Deal') }}
                                    </a>
                                    @else
                                    <span class="btn btn-sm btn-outline-secondary disabled" title="{{ translate('Assign department, owner and employee before convert') }}">
                                        {{ translate('Assign before Convert') }}
                                    </span>
                                    @endif
                                @endif

                                @if(!in_array($msg->status, ['converted', 'disqualified']))
                                <a href="javascript:void(0)"
                                    class="btn btn-sm btn-outline-danger disqualify-btn"
                                    data-id="{{ $msg->id }}">
                                    {{ translate('Disqualify') }}
                                </a>
                                @endif
                                <!-- <a href="javascript:void(0)" class="btn btn-sm btn-outline-dark ignore-btn" data-id="{{ $msg->id }}">
                                    {{ translate('Merge') }}
                                </a> -->

                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-warning escalate-btn" data-lead-id="{{ $msg->id }}">
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
        <div class="modal-content">
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
<span id="getEmployeeRoute" data-url="{{ route('admin.crm.lead.getemployee') }}"></span>
<span id="assignOwnerRoute" data-url="{{ route('admin.crm.lead.assignment-update') }}"></span>
<span id="assignEmployeeRoute" data-url="{{ route('admin.crm.lead.assignment-update') }}"></span>
<span id="assignDepartmentRoute" data-url="{{ route('admin.crm.lead.assignment-update') }}"></span>

@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/crm.js') }}" defer></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/lead.js') }}" defer></script>

<script>
    const convertSelectPartyMessage = @json(translate('Please select a party from search results before converting'));
    const convertLeadMissingMessage = @json(translate('Lead id is missing. Please close and reopen the convert form'));
    const convertingText = @json(translate('Converting...'));
    const convertButtonText = @json(translate('Convert'));

    $(document).on('shown.bs.modal', '#convertLeadModal', function() {
        let partyRouteUrl = $('#partySearchRoute').data('url');
        const form = $('#convertForm');

        form.find('button[type="submit"]').prop('disabled', false).text(convertButtonText);
        $('#party_search_results').hide().empty();
        $('#party_search_input').val('');
        $('#party_id').val('');
        $('#order-section').hide();
        $('#order_id').empty().append('<option value="">{{ translate("Select Order") }}</option>');

        $('#party_search_input').off('keyup').on('keyup', function() {
            let query = $(this).val().trim();
            let partyType = $('#party_type').val();
            $('#party_id').val('');

            if (query.length < 1) {
                $('#party_search_results').hide();
                return;
            }

            $.ajax({
                url: partyRouteUrl,
                type: 'GET',
                data: {
                    q: query,
                    party_type: partyType
                },
                success: function(data) {
                    let resultsContainer = $('#party_search_results');
                    resultsContainer.empty();

                    if (data.length > 0) {
                        data.forEach(item => {
                            resultsContainer.append(`<li class="list-group-item list-group-item-action" data-id="${item.id}" style="cursor:pointer;">${item.text}</li>`);
                        });
                        resultsContainer.show();
                    } else {
                        resultsContainer.hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error, xhr.responseText);
                }
            });
        });

        $(document).off('click', '#party_search_results li').on('click', '#party_search_results li', function() {
            let selectedId = $(this).data('id');
            let selectedText = $(this).text();

            $('#party_id').val(selectedId);
            $('#party_search_input').val(selectedText);
            $('#party_search_results').hide();
        });

        // Reset on party type change
        $('#party_type').off('change').on('change', function() {
            $('#party_id').val('');
            $('#party_search_input').val('');
            $('#party_search_results').hide();
        });
    });

    $(document).on('click', '.convert-btn', function() {
        let leadId = $(this).data('lead-id');
        $('#lead_id').val(leadId);
    });

    $(document).off('submit', '#convertForm').on('submit', '#convertForm', function(e) {
        const leadId = $('#lead_id').val();
        const partyId = $('#party_id').val();
        const submitBtn = $(this).find('button[type="submit"]');

        if (!leadId) {
            e.preventDefault();
            Swal.fire(@json(__('Error')), convertLeadMissingMessage, 'error');
            return;
        }

        if (!partyId) {
            e.preventDefault();
            Swal.fire(@json(__('Error')), convertSelectPartyMessage, 'error');
            return;
        }

        submitBtn.prop('disabled', true).text(convertingText);
    });

    $(document).on('hidden.bs.modal', '#convertLeadModal', function() {
        const form = $('#convertForm');
        form.find('button[type="submit"]').prop('disabled', false).text(convertButtonText);
    });
</script>


<script>
    $(document).on("click", ".disqualify-btn", function() {
        let leadId = $(this).data("id");

        Swal.fire({
            title: @json(__('Are you sure?')),
            text: @json(__('You want to disqualify this lead!')),
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: @json(__('Yes, Disqualify'))
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.crm.lead.disqualify') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        message_id: leadId
                    },
                    success: function(res) {
                        if (res.status) {
                            Swal.fire(@json(__('Done!')), res.message, "success");
                            // Optionally row remove / status update
                            $("#row-" + leadId).find("span.btn").removeClass().addClass("btn text-danger bg-soft-danger").text("Disqualified");
                        } else {
                            Swal.fire(@json(__('Error!')), res.message, "error");
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(@json(translate('Error')) + "!", xhr.responseJSON?.message || @json(translate('Something went wrong')), "error");
                    }
                });
            }
        });
    });

$(document).ready(function() {
    // Reset form when party_type changes
    $('#party_type').on('change', function() {
        const type = $(this).val();

        // Reset fields
        $('#order-section').hide();
        $('#order_id').empty().append('<option value="">{{ translate("Select Order") }}</option>');
        $('#party_search_input').val('');
        $('#party_id').val('');
    });

    // Handle party selection
    $(document).on('click', '#party_search_results li', function() {
        const selectedId = $(this).data('id');
        const selectedText = $(this).text();
        const type = $('#party_type').val();

        $('#party_id').val(selectedId);
        $('#party_search_input').val(selectedText);
        $('#party_search_results').hide();

        // Always hide order-section initially
        $('#order-section').hide();

        // If party_type is 'contact', show order-section and fetch orders
        if (type === 'contact') {
            $('#order-section').show(); // Show the order section
            $('#order_id').html('<option>{{ translate("Loading...") }}</option>');

            $.ajax({
                url: "{{ route('admin.crm.lead.user-orders') }}",
                type: "GET",
                data: { user_id: selectedId },
                success: function(data) {
                    $('#order_id').empty();

                    if (data && data.length > 0) {
                        $('#order_id').append('<option value="">{{ translate("Select Order") }}</option>');
                        data.forEach(order => {
                            $('#order_id').append(`<option value="${order.id}">${order.order_no}</option>`);
                        });
                    } else {
                        $('#order_id').append('<option value="">{{ translate("No Orders Found") }}</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', { status, error, xhr }); // Debug: Log error
                    $('#order_id').html('<option value="">{{ translate("Error loading orders") }}</option>');
                }
            });
        }
    });
});

</script>
@endpush

