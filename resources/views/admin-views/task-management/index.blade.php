@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('task_Requests'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('task_Requests')}}
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
                                            placeholder="{{translate('search_by_department_name')}}" aria-label="{{ translate('search_orders') }}" value="{{ request('searchValue') }}">
                                        <button type="submit" class="btn btn--primary">{{translate('Search')}}</button>
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
                                <a href="{{route('admin.department.add')}}" type="button" class="btn btn--primary text-nowrap">
                                    <i class="tio-add"></i>
                                    {{translate('new_Task')}}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table

                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                            <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Name')}}</th>                                  
                                <th>{{translate('User')}}</th>
                                <th>{{translate('Email')}}</th>
                                <th>{{translate('Status')}}</th>
                                
                                <th class="text-center">{{translate('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($departments as $key=>$dept)
                            
                                <tr>
                                    <td>{{$departments->firstItem()+$key}}</td>
                                    <td>{{$dept->getTranslatedField('name')}}</td>                                    
                                    <td>
                                        <div class="mb-1">
                                            <strong><a class="title-color hover-c1" href="mailto:{{$dept->users[0]->name}}">{{$dept->users[0]->name}}</a></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            <strong><a class="title-color hover-c1" href="mailto:{{$dept->users[0]->email}}">{{$dept->users[0]->email}}</a></strong>
                                        </div>
                                    </td>
                                    <td>
                                        {!! $dept->status=='active'?'<label class="badge badge-success">'.translate('Active').'</label>':'<label class="badge badge-danger">'.translate('Inactive').'</label>' !!}
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <!-- <a title="{{translate('Edit')}}"
                                                class="btn btn-outline--primary btn-sm square-btn"
                                                href="{{route('admin.department.update',$dept->id)}}">
                                                <i class="tio-edit"></i>
                                            </a> -->
                                            <a title="{{translate('view_department_users')}}"
                                                class="btn btn-outline-info btn-sm square-btn"
                                                href="{{route('admin.department.users',$dept->id)}}">
                                                <i class="tio-invisible"></i>
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

