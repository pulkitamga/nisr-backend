@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('department_Users_List'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('department_Users_List')}}
                <span class="badge badge-soft-dark radius-50 fz-12">{{ $aDepartmentUsers->total() }}</span>
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
                                            placeholder="{{translate('search_by_department_name')}}" aria-label="Search orders" value="{{ request('searchValue') }}">
                                        <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <div class="dropdown">
                                    <a type="button" class="btn btn-outline--primary text-nowrap btn-block" href="{{route('admin.department.export',['searchValue' => request('searchValue')])}}">
                                        <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                        <span class="ps-2">{{ translate('export') }}</span>
                                    </a>
                                </div>
                                <a href="{{route('admin.department.add-users', $dept_id)}}" type="button" class="btn btn--primary text-nowrap">
                                    <i class="tio-add"></i>
                                    {{translate('add_New_Users')}}
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
                                <th>{{translate('User_Type')}}</th>
                                <th>{{translate('User')}}</th>
                                <th>{{translate('Email')}}</th>
                                <th>{{translate('status')}}</th>                                
                                <th class="text-center">{{translate('action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($aDepartmentUsers as $key=>$user)
                            
                                <tr>
                                    <td>{{$aDepartmentUsers->firstItem()+$key}}</td>
                                    <td class="text-capitalize">{{translate($user->user_role->name)}}</td>      
                                    <td>
                                        <div class="mb-1">
                                            <strong><a class="title-color hover-c1" href="mailto:{{$user->name}}">{{$user->name}}</a></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            <strong><a class="title-color hover-c1" href="mailto:{{$user->email}}">{{$user->email}}</a></strong>
                                        </div>
                                    </td>
                                    <td>
                                        {!! $user->status=='active'?'<label class="badge badge-success">'.translate('active').'</label>':'<label class="badge badge-danger">'.translate('inactive').'</label>' !!}
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <!-- <a title="{{translate('edit')}}"
                                                class="btn btn-outline--primary btn-sm square-btn"
                                                href="{{route('admin.department.update',$user->id)}}">
                                                <i class="tio-edit"></i>
                                            </a> -->
                                            <a title="{{translate('delete')}}"
                                                class="btn btn-outline-danger btn-sm square-btn department-delete-button"
                                                id="{{ $user['id'] }}">
                                                <i class="tio-delete"></i>
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
                            {!! $aDepartmentUsers->links() !!}
                        </div>
                    </div>
                    @if(count($aDepartmentUsers)==0)
                        @include('layouts.back-end._empty-state',['text'=>'no_department_users_found'],['image'=>'default'])
                    @endif
                </div>
            </div>
        </div>
    </div>
    <span id="route-admin-department-delete" data-url="{{ route('admin.department.delete') }}"></span>
@endsection

@push('script')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/department.js')}}"></script>
@endpush
