@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('branch_List'))

@section('content')


    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('branch_List')}}
                <span class="badge badge-soft-dark radius-50 fz-12">{{ $branches->total() }}</span>
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
                                            placeholder="{{translate('search_by_branch_name_or_phone_or_email')}}" aria-label="{{ translate('Search orders') }}" value="{{ request('searchValue') }}">
                                        <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                    </div>
                                </form>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <div class="dropdown">
                                    <a type="button" class="btn btn-outline--primary text-nowrap btn-block" href="{{route('admin.branch.export',['searchValue' => request('searchValue')])}}">
                                        <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                        <span class="ps-2">{{ translate('export') }}</span>
                                    </a>
                                </div>
                                <a href="{{route('admin.branch.add')}}" type="button" class="btn btn--primary text-nowrap">
                                    <i class="tio-add"></i>
                                    {{translate('add_New_Branch')}}
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
                                 
                                <th>{{translate('branch_name')}}</th>
                                <th>{{ translate('branch_manager') }}</th>
                                 <th>{{translate('branch_address')}}</th>
                                 <th>{{translate('branch_zipcode')}}</th>
                                 <!-- <th>{{translate('branch_working_hours')}}</th> -->
                                  
                                <th>{{translate('contact_info')}}</th>
                                <th>{{translate('Shipping_area')}}</th>
                                <!-- <th>{{translate('delivery_restriction')}}</th> -->
                                <th>{{translate('status')}}</th>
                                
                                <th class="text-center">{{translate('action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($branches as $key=>$seller)
                            
                                <tr>
                                    <td>{{$branches->firstItem()+$key}}</td>
                                     
                                    <td>{{$seller->getTranslatedField('branch_name')}}</td>
                                    <td>
                                        @if($seller->manager)
                                            {{ $seller->manager->name }}
                                        @else
                                            <span class="text-muted">{{ translate('Not Assigned') }}</span>
                                        @endif
                                    </td>
                                    
                                    <td>{{$seller->getTranslatedField('branch_country') }},{{ $seller->getTranslatedField('branch_address')}}</td>
                                    <td>{{$seller->branch_zipcode}}</td>
                                    <!-- <td>{{$seller->mon_branch_hours_from}} To {{$seller->mon_branch_hours_to}} </td> -->
                                    
                                    <td>
                                        <div class="mb-1">
                                            <strong><a class="title-color hover-c1" href="mailto:{{$seller->email}}">{{$seller->email}}</a></strong>
                                        </div>
                                        <a class="title-color hover-c1" href="tel:{{$seller->phone}}">{{$seller->phone}}</a>
                                    </td>
                                    <td>{{$seller->shipping_method_areas}}</td>
                                    <!-- <td>{{$seller->delivery_restrictions}}</td> -->
                                    <td>
                                        {!! $seller->status=='active'?'<label class="badge badge-success">'.translate('active').'</label>':'<label class="badge badge-danger">'.translate('inactive').'</label>' !!}
                                    </td>
                                    
                                    <td>
                                        @if($seller->id != 1)
                                        <div class="d-flex justify-content-center gap-2">
                                            <a title="{{ translate('edit') }}" class="btn btn-outline--primary btn-sm square-btn"
                                               href="{{ route('admin.branch.update', $seller->id) }}">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a title="{{ translate('view') }}" class="btn btn-outline-info btn-sm square-btn"
                                               href="{{ route('admin.branch.view', $seller->id) }}">
                                                <i class="tio-invisible"></i>
                                            </a>
                                    
                                            <!-- Delete Button -->
                                            <form action="{{ route('admin.branch.chose.delete', $seller->id) }}" method="POST" 
                                                  onsubmit="return confirm('{{ translate('Are you sure you want to delete this branch?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm square-btn" title="{{ translate('delete') }}">
                                                    <i class="tio-delete"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @endif
                                    </td>
                                    
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-center justify-content-md-end">
                            {!! $branches->links() !!}
                        </div>
                    </div>
                    @if(count($branches)==0)
                        @include('layouts.back-end._empty-state',['text'=>'no_vendor_found'],['image'=>'default'])
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

