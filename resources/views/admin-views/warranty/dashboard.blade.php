@extends('layouts.back-end.app')
@section('title', translate('warranty_dashboard'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/business_analytics.png') }}">
            {{translate('warranty_dashboard')}}
        </h2>
        <a href="{{route('admin.warranty.import')}}" class="btn btn--primary">{{translate('import_serials')}}</a>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5>{{translate('active_warranties')}}</h5>
                    <h2 class="text-primary">{{$stats['active_count']}}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5>{{translate('expired_warranties')}}</h5>
                    <h2 class="text-danger">{{$stats['expired_count']}}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5>{{translate('open_claims')}}</h5>
                    <h2 class="text-warning">{{$stats['claims_open']}}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5>{{translate('sla_compliance')}}</h5>
                    <h2 class="text-success">{{ number_format($stats['sla_compliance'], 1) }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Claims Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>{{translate('recent_claims')}}</h5>

        </div>
        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">

                <table

                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('claim_number')}}</th>
                            <th>{{translate('status')}}</th>
                            <th>{{translate('customer')}}</th>
                            <th>{{translate('serial')}}</th>
                            <th>{{translate('submitted_at')}}</th>
                            <th>{{translate('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentClaims as $claim) <!-- Assume $recentClaims from controller -->
                        <tr>
                            <td>{{$claim->claim_number}}</td>
                            <td><span class="badge badge-soft-{{ $claim->status == 'new' ? 'warning' : 'success' }}">{{translate($claim->status)}}</span></td>
                            <td>{{$claim->warranty->user->name ?? $claim->warranty->activated_by_name}}</td>
                            <td>{{$claim->serial_number}}</td>
                            <td><span class="bidi-ltr d-inline-block">{{$claim->submitted_at->format('Y-m-d')}}</span></td>
                            <td><a href="{{route('admin.warranty.claim.view', $claim->id)}}" class="btn btn-sm btn-outline-primary">{{translate('view')}}</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(count($recentClaims)==0)
            @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
            @endif
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    // Basic search/filter JS (extend your existing)
    $('.form-control').on('keyup', function() {
        var value = $(this).val();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
</script>
@endpush
