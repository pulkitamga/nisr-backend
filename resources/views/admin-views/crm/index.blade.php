@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Inbox_List'))
@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/crm.css')}}">
@endpush
@section('content')

<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('Inbox_list')}}
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
                            <option {{ !request()->has('status') ?'selected':''}} disabled>{{ translate('select_status') }}</option>
                            <option {{ request()->has('status') && request('status') == 'all' ?'selected':''}} value="all">{{ translate('All') }}</option>
                            <option {{ request('status')  == 'new'?'selected':''}} value="new">{{ translate('New') }}</option>
                            <option {{ request('status')  == 'processing'?'selected':''}} value="processing">{{ translate('processing') }}</option>
                            <option {{ request('status')  == 'converted'?'selected':''}} value="converted">{{ translate('converted') }}</option>
                            <option {{ request('status')  == 'ignored'?'selected':''}} value="ignored">{{ translate('ignored') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{translate('Channel')}}</label>
                        <select class="form-control js-select2-custom set-filter" name="Channel">
                            <option {{ !request()->has('Channel') ?'selected':''}} disabled>{{ translate('select_Channel') }}</option>
                            <option {{ request()->has('Channel') && request('status') == '' ?'selected':''}} value="">{{ translate('All') }}</option>
                            <option {{ request('Channel')  == 'email'?'selected':''}} value="email">{{ translate('email') }}</option>
                            <option {{ request('Channel')  == 'form'?'selected':''}} value="form">{{ translate('form') }}</option>
                            <option {{ request('Channel')  == 'chat'?'selected':''}} value="chat">{{ translate('chat') }}</option>
                            <option {{ request('Channel')  == 'social'?'selected':''}} value="social">{{ translate('social') }}</option>
                            <option {{ request('Channel')  == 'phone'?'selected':''}} value="phone">{{ translate('phone') }}</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{translate('Choose_First')}}</label>
                        <input type="number" class="form-control" min="1" value="{{ request('choose_first') }}" placeholder="{{ translate('Ex') }} : 200" name="choose_first">
                    </div>
                    <div class="col-md-8">
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
                {{translate('Inbox_list')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $messages->total() }}</span>
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
                <a type="button" class="btn btn-outline--primary text-nowrap" href="{{route('admin.crm.messages.export', [ 
                'filter_date' => request('filter_date', request('fhilter_date')),
                 'Channel' => request('Channel'),
                        'status'       => request('status'),
                        'choose_first' => request('choose_first'),
                        'searchValue'  => request('searchValue'),])}}">
                    <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" alt="" class="excel">
                    <span class="ps-2">{{ translate('export') }}</span>
                </a>
            </div>
            <div class="dropdown">
                <a type="button" class="btn btn-outline--primary text-nowrap bulk-convert-btn">
                    🔀 <span class="ps-2">{{ translate('Bulk_convert') }}</span>
                </a>
            </div>
            <div class="dropdown">
                <a type="button" class="btn btn-outline--primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addMassageModal">
                    <i class="tio-add"></i>
                    <span>{{ translate('add_Massage') }}</span>
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
                        <th class="text-center">{{translate('Subject')}}</th>
                        <th>{{translate('Channel')}}</th>
                        <th>{{translate('Source')}}</th>
                        <th>{{translate('Name')}}</th>
                        <th>{{translate('Contact')}}</th>
                        <th>{{translate('Owner')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th>{{translate('Received_At')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $key=> $msg)
                    <tr id="row-{{ $msg->id }}" data-owner-id="{{ $msg->owner_id ?? '' }}">
                        <td> <input type="checkbox" class="message-checkbox" value="{{ $msg->id }}">
                        </td>
                        <td>
                            {{$messages->firstItem()+$key}}
                        </td>
                        <td><a href="">{{ $msg->subject ?? translate('No Subject') }}</a></td>
                        <td>{{ ucfirst($msg->pipeline) }} - {{ $msg->message_type }}</td>
                        <!-- <td>


                            <div class=" text-success d-flex align-items-center gap-3 font-weight-bolder mb-2">
                                {{ $msg->message_type }}
                                <div class="ripple-animation edit-message-type"
                                    data-bs-toggle="modal"
                                    data-bs-target="#showTypeModal"
                                    data-id="{{ $msg->id }}"
                                    data-current-type="{{ $msg->message_type }}">
                                    <i class="tio-edit"></i>
                                </div>
                            </div>

                        </td> -->
                        <td>{{ $msg->source_id ??  translate('Not Available') }}</td>
                        <td>{{ $msg->sender_name ??  translate('Not Available') }}</td>

                        <td>
                            <div class="mb-1">
                                <strong><a class="title-color hover-c1"
                                        href="mailto:{{$msg->sender_email}}">{{$msg->sender_email}}</a></strong>
                            </div>
                            <a class="title-color hover-c1" href="tel:{{$msg->sender_phone}}">{{$msg->sender_phone}}</a>

                        </td>
                        <td>{{ $msg->owner?->name ?? translate('Not Assigned') }}</td>
                        <td>
                            @php
                            $status = strtolower($msg->status);
                            $statusClass = match ($status) {
                            'new' => 'text-primary bg-soft-primary',
                            'processing' => 'text-warning bg-soft-warning',
                            'converted' => 'text-success bg-soft-success',
                            'ignored' => 'text-secondary bg-soft-secondary',
                            'spam' => 'text-danger bg-soft-danger',
                            default => 'text-dark bg-soft-light',
                            };
                            @endphp

                            <span class="btn {{ $statusClass }} font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ ucfirst($msg->status) }}
                            </span>
                        </td>
                        <td>{{ $msg->created_at->format('d M, Y H:i') }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a href="{{ route('admin.crm.massage.show', $msg->id) }}" class="btn btn-sm btn-outline-info">
                                    {{ translate('View') }}
                                </a>
                                <!-- <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary reply-btn" data-id="{{ $msg['id'] }}">
                                    {{ translate('Reply') }}
                                </a> -->
                                @if(\App\Utils\Helpers::module_permission_check('crm_section', 'inbox_assign_owner'))
                                <a href="javascript:void(0)"
                                    class="btn btn-sm btn-outline-secondary assign-owner-btn"
                                    data-id="{{ $msg->id }}"
                                    data-owner-id="{{ $msg->owner_id ?? '' }}"
                                    data-department-id="{{ $msg->department_id ?? '' }}"
                                    data-bs-toggle="false"
                                    data-bs-target="none">
                                    {{ $msg->owner_id ? translate('Re-Assign Owner') : translate('Assign Owner') }}
                                </a>
                                @endif

                                @if($msg->status != 'ignored' && $msg->status != 'spam' && $msg->status != 'converted')
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-id="{{ $msg['id'] }}" data-bs-target="#convertModal">
                                    🔀 {{ translate('Convert') }}

                                </a>
                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger mark-spam-btn" data-id="{{ $msg['id'] }}">
                                    {{ translate('Mark Spam') }}
                                </a>
                                <a href="javascript:void(0)"
                                    class="btn btn-sm btn-outline-dark ignore-btn"
                                    data-id="{{ $msg['id'] }}">
                                    {{ translate('Ignore') }}
                                </a>
                                @endif

                                @if($msg->contact_id == null)
                                @php
                                $suggestion = \App\Models\InboxSuggestion::where('inbox_message_id', $msg->id)
                                ->where('status', 'pending')->first();
                                @endphp
                                @if($suggestion)
                                <button type="button" class="btn btn-sm btn-outline-info suggestion-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#suggestionModal"
                                    data-message-id="{{ $msg->id }}"
                                    data-user-id="{{ $suggestion->user_id }}">
                                    {{ translate('Suggestion') }}
                                    <span class="badge bg-danger"> {{ translate('Reg') }}
                                    </span>
                                </button>
                                @endif
                                @endif
                                <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="{{ $msg->id }}">
                                    {{ translate('Delete') }}
                                </button>
                                <span class="d-none delete-route" data-id="{{ $msg->id }}"
                                    data-url="{{ route('admin.crm.messages.destroy', $msg->id) }}"></span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $messages->links() !!}
            </div>
        </div>
        @if(count($messages)==0)
        @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
        @endif
    </div>
</div>

<div class="modal fade" id="suggestionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">                                   {{ translate('User_Suggestion') }}
</h5>
                <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="suggestion-user-info"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="connect-user-btn">                                      {{ translate('Yes_Connect') }}
</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">                                     {{ translate('Cancel') }}
</button>
            </div>
        </div>
    </div>
</div>

@include('admin-views.crm.partials.departments')
@include('admin-views.crm.partials.employee')
@include('admin-views.crm.partials.owner')
@include('admin-views.crm.partials.type')
@include('admin-views.crm.partials.convert')
@include('admin-views.crm.partials.convert-bulk')
@include('admin-views.crm.partials.add-massage')

<span id="ignoreRoute" data-url="{{ route('admin.crm.ignore') }}"></span>
<span id="spamRoute" data-url="{{ route('admin.crm.mark-spam') }}"></span>
<span id="getEmployeeRoute" data-url="{{ route('admin.crm.getemployee') }}"></span>
<span id="assignOwnerRoute" data-url="{{ route('admin.crm.assignment-update') }}"></span>
<span id="assignEmployeeRoute" data-url="{{ route('admin.crm.assignment-update') }}"></span>
<span id="assignDepartmentRoute" data-url="{{ route('admin.crm.assignment-update') }}"></span>
<span id="getUserRoute" data-route="{{ url('admin/crm/inbox/user-info') }}"></span>
<span id="connectUserRoute" data-route="{{ route('admin.crm.inbox.connect-user') }}"></span>


@endsection

@push('script')
<script type="text/javascript">
    changeInputTypeForDateRangePicker($('input[name="order_date"]'));
    changeInputTypeForDateRangePicker($('input[name="customer_joining_date"]'));
</script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/crm.js') }}"></script>
@endpush

