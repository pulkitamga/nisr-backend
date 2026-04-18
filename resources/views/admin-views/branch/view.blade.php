@extends('layouts.back-end.app')

@section('title', $seller?->shop->name ?? translate("manage_branch"))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('manage_branch')}}
            </h2>
        </div>
        <div class="page-header border-0 mb-4">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <ul class="nav nav-tabs flex-wrap page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active"
                           href="{{ route('admin.branch.view', $seller['id']) }}">{{translate('branch_overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('admin.branch.assign-manager', $seller['id']) }}">{{translate('assign_manager')}}</a>
                    </li>
                    
                </ul>
            </div>
        </div>
        <div class="card card-top-bg-element mb-5">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3 justify-content-between">
                    <div class="media flex-column flex-sm-row gap-3">
                        
                        <div class="media-body">
                            
                            <div class="d-block">
                                <h2 class="mb-2 pb-1">{{ $seller->getTranslatedField('branch_name')? $seller->getTranslatedField('branch_name') : translate("Shop_Name")." : ".translate("update_Please") }}</h2>
                                 
                                 
                            </div>
                        </div>
                    </div>
                    @if ($seller['status']=="inactive")
                        <div class="d-flex justify-content-sm-end flex-wrap gap-2 mb-3">
                            <form class="d-inline-block" action="{{route('admin.branch.updateStatus')}}" id="reject-form" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller['id']}}">
                                <input type="hidden" name="status" value="inactive">
                                <button type="button" class="btn btn-danger px-5 form-alert" data-message="{{translate('want_to_reject_this_branch').'?'}}" data-id="reject-form">{{translate('Reject')}}</button>
                            </form>
                            <form class="d-inline-block" action="{{route('admin.branch.updateStatus')}}" id="approve-form" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller['id']}}">
                                <input type="hidden" name="status" value="active">
                                <button type="button" class="btn btn-success px-5 form-alert" data-message="{{translate('want_to_approve_this_branch').'?'}}" data-id="approve-form">{{translate('Approve')}}</button>
                            </form>
                        </div>
                    @endif
                    @if ($seller['status']=="active")
                        <div class="d-flex justify-content-sm-end flex-wrap gap-2 mb-3">
                            <form class="d-inline-block" action="{{route('admin.branch.updateStatus')}}" id="suspend-form" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller['id']}}">
                                <input type="hidden" name="status" value="suspended">
                                <button type="button" class="btn btn-danger px-5 form-alert" data-message="{{translate('want_to_suspend_this_branch').'?'}}" data-id="suspend-form">{{translate('suspend_this_branch')}}</button>
                            </form>
                        </div>
                    @endif
                    @if ($seller['status']=="suspended" || $seller['status']=="rejected")
                        <div class="d-flex justify-content-sm-end flex-wrap gap-2 mb-3">
                            <form class="d-inline-block" action="{{route('admin.branch.updateStatus')}}" id="active-form" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller['id']}}">
                                <input type="hidden" name="status" value="active">
                                <button type="button" class="btn btn-success px-5 form-alert" data-message="{{translate('want_to_active_this_branch').'?'}}" data-id="active-form">{{translate('Active')}}</button>
                            </form>
                        </div>
                    @endif
                </div>
                <hr>
                <div class="d-flex gap-3 flex-wrap flex-lg-nowrap">
                    
                    <div class="row gy-3 flex-grow-1 w-100">
                        <div class="col-sm-6 col-xxl-3">
                            <h4 class="mb-3 text-capitalize">{{translate('Branch_information')}}</h4>

                            <div class="pair-list">
                                <div>
                                    <span class="key text-nowrap">{{translate('Branch_Name')}}</span>
                                    <span>:</span>
                                    <span class="value ">{{$seller->getTranslatedField('branch_name')}}</span>
                                </div>

                                <div>
                                    <span class="key">{{translate('Phone')}}</span>
                                    <span>:</span>
                                    <span class="value">{{$seller->phone}}</span>
                                </div>

                                <div>
                                    <span class="key">{{translate('Address')}}</span>
                                    <span>:</span>
                                    <span class="value">{{$seller->getTranslatedField('branch_address')}}</span>
                                </div>

                                <div>
                                    <span class="key">{{translate('Status')}}</span>
                                    <span>:</span>
                                    <span class="value">
                                        <span class="badge badge-{{$seller['status']=='active'? 'info' :'danger'}}">
                                            {{ $seller['status']=='active'? translate('Active') : translate('Inactive') }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xxl-3">
                            <h4 class="mb-3 text-capitalize">{{translate('contact_information')}}</h4>

                            <div class="pair-list">
                                <div>
                                    <span class="key">{{translate('Name')}}</span>
                                    <span>:</span>
                                    <span class="value text-capitalize">{{$seller['branch_name']}}</span>
                                </div>

                                <div>
                                    <span class="key">{{translate('Email')}}</span>
                                    <span>:</span>
                                    <span class="value">{{$seller['email']}}</span>
                                </div>

                                <div>
                                    <span class="key">{{translate('Phone')}}</span>
                                    <span>:</span>
                                    <span class="value">{{$seller['phone']}}</span>
                                </div>
                                <div>
                                    <span class="key">{{translate('State')}}</span>
                                    <span>:</span>
                                    <span class="value">{{$seller['branch_state'] ?? ''}}</span>
                                </div>
                            </div>
                        </div>
                        @if ($seller['status']!="pending")
                            <div class="col-xxl-6">
                                <div class="bg-light p-3 border border-primary-light rounded">
                                    <h4 class="mb-3 text-capitalize">{{translate('bank_Information')}}</h4>

                                    <div class="d-flex gap-5">
                                        <div class="pair-list">
                                            <div>
                                                <span class="key text-nowrap">{{translate('bank_Name')}}</span>
                                                <span class="px-2">:</span>
                                                <span class="value ">{{ $seller['bank_name'] ?? translate('no_Data_found') }}</span>
                                            </div>

                                            <div>
                                                <span class="key text-nowrap">{{translate('Branch')}}</span>
                                                <span class="px-2">:</span>
                                                <span class="value">{{ $seller['branch'] ?? translate('no_Data_found') }}</span>
                                            </div>
                                        </div>
                                        <div class="pair-list">
                                            <div>
                                                <span class="key text-nowrap">{{translate('holder_Name')}}</span>
                                                <span class="px-2">:</span>
                                                <span class="value">{{ $seller['holder_name'] ?? translate('no_Data_found') }}</span>
                                            </div>

                                            <div>
                                                <span class="key text-nowrap">{{translate('A/C_No')}}</span>
                                                <span class="px-2">:</span>
                                                <span class="value">{{ $seller['account_no'] ?? translate('no_Data_found') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
    </div>
@endsection
