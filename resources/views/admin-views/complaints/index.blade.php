@php use Illuminate\Support\Str; @endphp
@php use Carbon\Carbon; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Registered_Complaints'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/support_ticket.png')}}" alt="">
                {{translate('Registered_Complaints')}}
                <span class="badge badge-soft-dark radius-50 fz-12">{{ $tickets->total() }}</span>
            </h2>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="px-3 py-4">
                        <div class="d-flex justify-content-between gap-10 flex-wrap align-items-center">
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
                                               placeholder="{{translate('search_ticket_by_subject_or_status').'...'}}"
                                               aria-label="{{ translate('search_orders') }}" value="{{ request('searchValue') }}">
                                        <button type="submit"
                                                class="btn btn--primary">{{translate('Search')}}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <div class="dropdown d-none">
                                    <a type="button" class="btn btn-outline--primary text-nowrap btn-block" href="{{route('admin.department.export',['searchValue' => request('searchValue')])}}">
                                        <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                        <span class="ps-2">{{ translate('export') }}</span>
                                    </a>
                                </div>
                                <div class="">
	                                <div class="d-flex flex-wrap flex-sm-nowrap gap-3 justify-content-end">
	                                    @php($priority=request()->has('priority')?request()->input('priority'):'')
	                                    <select class="form-control border-color-c1 w-160 filter-tickets"
	                                            data-value="priority">
	                                        <option value="all">{{translate('all_Priority')}}</option>
	                                        <option
	                                            value="low" {{$priority=='low'?'selected':''}}>{{translate('Low')}}</option>
	                                        <option
	                                            value="medium" {{$priority=='medium'?'selected':''}}>{{translate('Medium')}}</option>
	                                        <option
	                                            value="high" {{$priority=='high'?'selected':''}}>{{translate('High')}}</option>
	                                        <option
	                                            value="urgent" {{$priority=='urgent'?'selected':''}}>{{translate('Urgent')}}</option>
	                                    </select>

	                                    @php($statusId=request()->has('status') ? request()->input('status'):'')
	                                    <select class="form-control border-color-c1 w-160 filter-tickets"
	                                            data-value="status">
	                                        <option value="all">{{translate('all_Status')}}</option>
                                            @foreach($aAllStatus as $status)
	                                           <option value="{{ $status['id'] }}" {{ $statusId == $status['id'] ? 'selected' : ''}}>{{translate($status['name'])}}</option>
                                            @endforeach
	                                    </select>
	                                </div>
	                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table

                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                            <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{translate('SL')}}</th>     
                                <th>{{translate('Type')}}</th> 
                                <th>
                                    @if(\App\Utils\Helpers::module_permission_check('crm_section', 'ticket_department_update'))
                                        {{translate('Department')}}
                                    @endif    
                                    @if(\App\Utils\Helpers::module_permission_check('crm_section', 'ticket_employee_update'))
                                        / {{translate('Employee')}}
                                    @endif 
                                </th> 
                                <th>{{translate('Customer')}}</th>
                                <th>{{translate('DATE')}}</th>
                                <th>{{translate('Description')}}</th>                                
                                <th class="text-center">{{translate('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($tickets as $key=>$ticket)                            
                                <tr>
                                    <td>{{$tickets->firstItem()+$key}}</td>
                                    <td>
                                    	<div>
                                    		{{translate(str_replace('_',' ',$ticket->type))}}
                                    	</div>
                                    	<span class="badge badge-soft-danger">{{translate(str_replace('_',' ',$ticket->priority))}}</span>
                                    </td>     
                                    <td>
                                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'ticket_department_update'))
                                            <div class="{{ $ticket->department_id != 0 ? ' text-success' : 'text-danger' }} d-flex align-items-center gap-3 font-weight-bolder mb-2">
                                                @if (!empty($ticket->department) && !is_null($ticket->department->name))
                                                    {{ translate($ticket->department->name) }}
                                                    <div class="ripple-animation" data-toggle="modal" data-target="#showDepartmentsModal" data-ticket-id="{{ $ticket->id }}" data-department-id="{{ $ticket->department->id ?? 0 }}" data-department-employee-id="0">
                                                        <i class="tio-edit"></i>
                                                    </div>
                                                @else
                                                   {{translate('Select_Department')}}
                                                    <div class="ripple-animation" data-toggle="modal" data-target="#showDepartmentsModal" data-ticket-id="{{ $ticket->id }}" data-department-id="{{ $ticket->department->id ?? 0 }}" data-department-employee-id="0">
                                                        <i class="tio-edit"></i>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'ticket_employee_update'))
                                            @if($ticket->department_id != 0)
                                            <div class="{{ $ticket->employee_id != 0 ? ' text-success' : 'text-danger' }} d-flex align-items-center gap-3 font-weight-bolder mb-2">
                                                @if (!empty($ticket->employee) && !is_null($ticket->employee->name))
                                                    {{ translate($ticket->employee->name) }}
                                                    <div class="ripple-animation" data-toggle="modal" data-target="#showEmployeeModal" data-ticket-id="{{ $ticket->id }}" data-department-id="{{ $ticket->department->id ?? 0 }}" data-department-employee-id="0">
                                                        <i class="tio-edit"></i>
                                                    </div>
                                                @else
                                                   {{translate('Select_Employee')}}
                                                    <div class="ripple-animation" data-toggle="modal" data-target="#showEmployeeModal" data-ticket-id="{{ $ticket->id }}" data-department-id="{{ $ticket->department->id ?? 0 }}" data-department-employee-id="0">
                                                        <i class="tio-edit"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            @endif
                                        @endif                            
                                    </td>
                                    <td>
                                        {{$ticket->customer->f_name??""}} {{$ticket->customer->l_name??""}} <br>
                                        {{$ticket->customer->email??""}}
                                    </td>
                                    <td>
                                        @if ($ticket->created_at->diffInDays(Carbon::now()) < 7)
                                            {{ date('D h:i:A',strtotime($ticket->created_at)) }}
                                        @else
                                            {{ date('d M Y h:i:A',strtotime($ticket->created_at)) }}
                                        @endif
                                    </td>
                                    <td>
                                    	{{$ticket->description}}
                                    </td>                                    
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a title="{{translate('view_complaint')}}"
                                                class="btn btn-outline-info btn-sm square-btn"
                                                href="{{route('admin.complaints.singleTicket',$ticket['id'])}}">
                                                <i class="tio-open-in-new"></i>
                                            </a>
                                            <div class="btn btn-outline-warning btn-sm square-btn" data-toggle="modal" data-target="#showFollowUpModal" data-ticket-id="{{ $ticket->id }}" data-department-id="{{ $ticket->department_id }}" data-employee-id="{{ $ticket->employee_id }}"  title="{{ translate('Follow-up details') }}">
                                                <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/support_ticket.png')}}" alt=""> 
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-center justify-content-md-end">
                            {!! $tickets->links() !!}
                        </div>
                    </div>
                    @if(count($tickets)==0)
                        @include('layouts.back-end._empty-state',['text'=>'no_complaints_found'],['image'=>'default'])
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('admin-views.complaints.partials.departments')
    @include('admin-views.complaints.partials.employee')
    @include('admin-views.complaints.partials.follow-up')

    <span id="route-get-department-employee" data-url="{{ route('admin.complaints.get-department-employee') }}"></span>
@endsection

@push('script')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/support-tickets.js')}}"></script>
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/complaint.js')}}"></script>
@endpush

