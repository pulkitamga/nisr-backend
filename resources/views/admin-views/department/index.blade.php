@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('departments'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('departments')}}
                <span class="badge badge-soft-dark radius-50 fz-12">{{ $departments->total() }}</span>
            </h2>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="px-3 py-4">
                        <div class="d-flex justify-content-between gap-10 flex-wrap align-items-center">
                            <div class="">
                                <form action="{{ url()->current() }}" method="GET">
                                    <div class="input-group input-group-merge input-group-custom width-500px">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                            placeholder="{{translate('search_by_department_name')}}" aria-label="{{ translate('Search orders') }}" value="{{ request('searchValue') }}">
                                        <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
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
                                <a href="{{route('admin.department.add')}}" type="button" class="btn btn--primary text-nowrap">
                                    <i class="tio-add"></i>
                                    {{translate('add_Department')}}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table
                            style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                            <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('name')}}</th>                                  
                                <th>{{translate('User')}}</th>
                                <th>{{translate('Email')}}</th>
                                <th>{{translate('status')}}</th>
                                
                                <th class="text-center">{{translate('action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($departments as $key=>$dept)                            
                                <tr>
                                    <td>{{$departments->firstItem()+$key}}</td>
                                    <td>{{$dept->getTranslatedField('name')}}</td>                                    
                                    <td>
                                        @if(!empty($dept->employee))
                                        <div class="mb-1">
                                            <strong><a class="title-color hover-c1">{{$dept->employee->name}}</a></strong>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($dept->employee))
                                        <div class="mb-1">
                                            <strong><a class="title-color hover-c1" href="mailto:{{$dept->employee->email}}">{{$dept->employee->email}}</a></strong>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{route('admin.department.updateStatus')}}" method="post" id="department-id-{{$dept['id']}}-form" class="department_id_form">
                                            @csrf
                                            <input type="hidden" name="id" value="{{$dept['id']}}">
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher_input toggle-switch-message" value="1" id="department-id-{{$dept['id']}}" name="status"
                                                       {{$dept->status?'checked':''}}
                                                       data-modal-id = "toggle-status-modal"
                                                       data-toggle-id = "department-id-{{$dept['id']}}"
                                                       data-on-image = "employee-on.png"
                                                       data-off-image = "employee-off.png"
                                                       data-on-title = "{{translate('want_to_Turn_ON_Employee_Status').'?'}}"
                                                       data-off-title = "{{translate('want_to_Turn_OFF_Department_Status').'?'}}"
                                                       data-on-message = "<p>{{translate('If_enabled,_this_department_will_be_accessible_in_the_system.')}}</p>"
                                                       data-off-message = "<p>{{translate('if_disabled,_this_department_will_not_be_accessible_in_the_system.')}}</p>">`)">
                                                <span class="switcher_control"></span>
                                            </label>
                                        </form>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{route('admin.department.update',[$dept['id']])}}"
                                               class="btn btn-outline--primary btn-sm square-btn"
                                               title="{{translate('edit')}}">
                                                <i class="tio-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-center justify-content-md-end">
                            {!! $departments->links() !!}
                        </div>
                    </div>
                    @if(count($departments)==0)
                        @include('layouts.back-end._empty-state',['text'=>'no_vendor_found'],['image'=>'default'])
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

