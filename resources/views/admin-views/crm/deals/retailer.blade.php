@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('retail_Deals'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">

@endpush

@section('content')



<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('retail_Deals')}}
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
                            <a href="{{ route('admin.crm.deals.retail.list') }}"
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
                {{translate('retail_Deals')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $deals->total() }}</span>
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
                        placeholder="{{ translate('search_by_Name_or_Email_or_Phone')}}" aria-label="Search orders" value="{{ request('searchValue') }}">
                    <button type="submit" class="btn btn--primary">{{ translate('search')}}</button>
                </div>
            </form>
            <div class="dropdown">
                <a type="button" class="btn btn-outline--primary text-nowrap" href="{{route('admin.crm.deals.retail.export', [
                    'filter_date' => request('filter_date', request('fhilter_date')),
                    'status' => request('status'),
                    'choose_first' => request('choose_first'),
                    'searchValue' => request('searchValue')
                ])}}">
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
                        <td>{{ $deal->created_at->format('d M, Y H:i A') }}</td>
                        <td>{{ $deal->relatedParty->name ?? translate('N/A') }}</td>
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
                        <td>{{ $deal->department?->name ?? translate('No Department') }}</td>
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
                                No Order
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
                            default => 'text-dark bg-soft-light',
                            };
                            @endphp

                            <span class="btn {{ $statusClass }} font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ ucfirst($deal->status) }}
                            </span>
                        </td>

                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a href="{{ route('admin.crm.deals.retail.view', $deal->id) }}" class="btn btn-sm btn-info">{{ translate('View') }}</a>
                                @if(\App\Utils\Helpers::module_permission_check('crm_section', 'deal_retail_assign_owner'))
                                <a href="javascript:void(0)"
                                    class="btn btn-sm btn-outline-secondary assign-owner-btn"
                                    data-id="{{ $deal->id }}"
                                    data-owner-id="{{ $deal->owner_id ?? '' }}"
                                    data-bs-toggle="false"
                                    data-bs-target="none">
                                    {{ translate('Assign Owner') }}
                                </a>
                                @endif
                                @if(\App\Utils\Helpers::module_permission_check('crm_section', 'deal_retail_assign_employee'))
                                <a href="javascript:void(0)"
                                    class="btn btn-sm btn-outline-secondary assign-employee-btn"
                                    data-id="{{ $deal->id }}"
                                    data-department-id="{{ $deal->department->id ?? '' }}"
                                    data-head-id="{{ $deal->department->head_id ?? '' }}">
                                    {{ translate('Assign Employee') }}
                                </a>
                                @if((int)auth('admin')->user()?->admin_role_id !== 1)
                                <input type="hidden" id="fixed-department-id" value="{{ auth('admin')->user()->department_id }}">
                                @endif
                                @endif
                                @if(\App\Utils\Helpers::module_permission_check('crm_section', 'deal_retail_assign_department'))
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary assign-dept-btn" data-id="{{ $deal->id }}" data-department-id="{{ $deal->department->id ?? 0 }}" data-department-employee-id="0">
                                    {{ translate('Assign Department') }}
                                </a>
                                @endif

                                @if(!$deal->order)
                                <button type="button" class="btn btn-sm btn-success link-order-btn"
                                    data-deal-id="{{ $deal->id }}"
                                    data-user-id="{{ $deal->related_party_id }}"
                                    data-user-name="{{ $deal->relatedParty->name ?? translate('User') }}">
                                    {{ translate('Link Order') }}
                                </button>
                                @endif

                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-warning escalate-btn" data-deal-id="{{ $deal->id }}">
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

<!-- Escalation Modal for Retail Deals -->
<div class="modal fade" id="escalateRetailDealModal" tabindex="-1" role="dialog" aria-labelledby="escalateRetailDealModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="escalateRetailDealModalLabel">{{ translate('Escalate Deal') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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

@endsection

@push('script')
<script type="text/javascript">
    changeInputTypeForDateRangePicker($('input[name="order_date"]'));
    changeInputTypeForDateRangePicker($('input[name="customer_joining_date"]'));
</script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/crm.js') }}"></script>


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
            url: '/admin/crm/deals/retail/request-quotation/' + dealId,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res.status) {
                    toastr.success(res.message);
                } else {
                    toastr.error(@json(translate('Something went wrong')));
                }
            }
        });
    });
</script>

<script>
    $(document).on('click', '.escalate-btn', function() {
        let dealId = $(this).data('deal-id');
        $('#escalateRetailDealId').val(dealId);
        $('#escalateRetailDealModal').modal('show');
    });

    // Form submission with confirmation
    $('#escalateRetailDealForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        Swal.fire({
            title: '{{ translate("Are you sure?") }}',
            text: '{{ translate("This will notify the department and owner.") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '{{ translate("Yes, Escalate") }}',
            cancelButtonText: '{{ translate("Cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                form.off('submit').submit();
            }
        });
    });


    $(document).ready(function() {
        // Routes from span tags
        const getUserOrdersRoute = $('#getUserOrdersRoute').data('url');
        const linkOrderRoute = $('#linkOrderRoute').data('url');
        const crmCurrencySymbol = @json(getCurrencySymbol(currencyCode: getCurrencyCode(), type: 'default'));
        const crmCurrencyPosition = @json(getWebConfig('currency_symbol_position') ?? 'left');
        const crmCurrencySpaceEnabled = @json((string)(getWebConfig('currency_symbol_space') ?? '0') === '1');
        const crmCurrencyDecimals = Number(@json((int)(getWebConfig('decimal_point_settings') ?? 2)));
        const crmDealText = {
            noOrdersFound: @json(translate('No orders found for this customer.')),
            action: @json(translate('Action')),
            orderId: @json(translate('Order ID')),
            date: @json(translate('Date')),
            amount: @json(translate('Amount')),
            status: @json(translate('Status')),
            link: @json(translate('Link')),
            failedOrders: @json(translate('Failed to load orders. Please try again.')),
            linkOrderTitle: @json(translate('Link Order?')),
            linkOrderBody: @json(translate('Order will be linked to the selected deal.')),
            yesLinkIt: @json(translate('Yes, Link it!')),
            linked: @json(translate('Linked!')),
            orderLinked: @json(translate('Order linked successfully!')),
            failed: @json(translate('Failed')),
            error: @json(translate('Error')),
            serverError: @json(translate('Server error. Please try again.')),
            cancel: @json(translate('Cancel')),
        };

        function formatPanelCurrency(value) {
            const amount = Number.parseFloat(value);
            const safeAmount = Number.isFinite(amount) ? amount : 0;
            const decimals = Number.isFinite(crmCurrencyDecimals) ? crmCurrencyDecimals : 2;
            const formattedNumber = safeAmount.toLocaleString(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
            const spacing = crmCurrencySpaceEnabled ? ' ' : '';

            if (crmCurrencyPosition === 'right') {
                return `${formattedNumber}${spacing}${crmCurrencySymbol}`;
            }

            return `${crmCurrencySymbol}${spacing}${formattedNumber}`;
        }

        // Open Modal + Load Orders
        $(document).on('click', '.link-order-btn', function() {
            const dealId = $(this).data('deal-id');
            const userId = $(this).data('user-id');
            const userName = $(this).data('user-name');

            $('#modal-deal-id').text(dealId);
            $('#modal-user-name').text(userName);

            $('#orders-list').html('<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>');

            $.get(getUserOrdersRoute, {
                user_id: userId,
                deal_id: dealId
            }, function(response) {
                if (!response.orders || response.orders.length === 0) {
                    $('#orders-list').html(`<p class="text-muted text-center">${crmDealText.noOrdersFound}</p>`);
                    return;
                }

                let html = `
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="100">${crmDealText.action}</th>
                                <th>${crmDealText.orderId}</th>
                                <th>${crmDealText.date}</th>
                                <th>${crmDealText.amount}</th>
                                <th>${crmDealText.status}</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

                response.orders.forEach(order => {
                    const date = new Date(order.created_at).toLocaleString('en-IN', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const statusBadge = {
                        'delivered': 'success',
                        'confirmed': 'primary',
                        'processing': 'info',
                        'pending': 'warning',
                        'canceled': 'danger',
                        'failed': 'dark',
                        'returned': 'secondary'
                    } [order.order_status] || 'dark';

                    html += `
                    <tr>
                        <td>
                            <button class="btn btn-sm btn-primary link-this-order"
                                    data-deal-id="${dealId}"
                                    data-order-id="${order.id}">
                                <i class="tio-link"></i> ${crmDealText.link}
                            </button>
                        </td>
                        <td><strong>#${order.id}</strong></td>
                        <td>${date}</td>
                        <td>${formatPanelCurrency(order.order_amount)}</td>
                        <td>
                            <span class="badge badge-soft-${statusBadge}">
                                ${order.order_status.replace(/_/g, ' ')}
                            </span>
                        </td>
                    </tr>
                `;
                });

                html += '</tbody></table></div>';
                $('#orders-list').html(html);

            }).fail(function() {
                $('#orders-list').html(`<p class="text-danger text-center">${crmDealText.failedOrders}</p>`);
            });

            $('#linkOrderModal').modal('show');
        });

        // Link Selected Order
        $(document).on('click', '.link-this-order', function() {
            const dealId = $(this).data('deal-id');
            const orderId = $(this).data('order-id');

            Swal.fire({
                title: crmDealText.linkOrderTitle,
                text: crmDealText.linkOrderBody,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: crmDealText.yesLinkIt,
                cancelButtonText: crmDealText.cancel,
                confirmButtonColor: '#1e88e5'
            }).then(result => {
                if (result.isConfirmed) {
                    $.post(linkOrderRoute, {
                        _token: '{{ csrf_token() }}',
                        deal_id: dealId,
                        order_id: orderId
                    }, function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: crmDealText.linked,
                                text: res.message || crmDealText.orderLinked,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire(crmDealText.failed, res.message || @json(translate('Something went wrong')), 'error');
                        }
                    }).fail(function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire(crmDealText.error, crmDealText.serverError, 'error');
                    });
                }
            });
        });
    });
</script>
@endpush

