@extends('layouts.back-end.app')
@section('title', translate('warranty_dashboard'))

@php
    use Illuminate\Support\Str;

    $statusOptions = [
        'all' => translate('all'),
        'new' => translate('new'),
        'triage_pending' => translate('triage_pending'),
        'approved' => translate('approved'),
        'rma_issued' => translate('rma_issued'),
        'received' => translate('received'),
        'repair_pending' => translate('repair_pending'),
        'replacement_pending' => translate('replacement_pending'),
        'diagnosis_pending' => translate('diagnosis_pending'),
        'qc_pending' => translate('qc_pending'),
        'shipped_ready' => translate('shipped_ready'),
        'dispatched' => translate('dispatched'),
        'resolved' => translate('resolved'),
        'closed' => translate('closed'),
        'rejected' => translate('rejected'),
        'waiting_customer' => translate('waiting_customer'),
        'waiting_parts' => translate('waiting_parts'),
        'waiting_payment' => translate('waiting_payment'),
    ];

    $toolbarFields = [
        [
            'type' => 'select',
            'name' => 'status',
            'label' => translate('Status'),
            'value' => request('status', 'all'),
            'options' => $statusOptions,
            'input_class' => 'form-control js-select2-custom',
        ],
        [
            'type' => 'number',
            'name' => 'choose_first',
            'label' => translate('Rows_to_show'),
            'value' => request('choose_first', $recentClaimsLimit ?? 10),
            'placeholder' => translate('Ex') . ' : 10',
            'attributes' => ['min' => '1'],
        ],
        [
            'type' => 'search',
            'name' => 'searchValue',
            'label' => translate('search'),
            'value' => request('searchValue'),
            'placeholder' => translate('search_by_claim_or_serial'),
            'aria_label' => translate('search_by_claim_or_serial'),
            'col_class' => 'col-xl-4 col-lg-12',
        ],
    ];

    $toolbarSummary = [
        [
            'label' => translate('Status'),
            'value' => $statusOptions[(string) request('status', 'all')] ?? translate('all'),
        ],
        [
            'label' => translate('Rows_to_show'),
            'value' => request('choose_first', $recentClaimsLimit ?? 10),
            'muted' => true,
        ],
    ];

    if (request()->filled('searchValue')) {
        $toolbarSummary[] = [
            'label' => translate('search'),
            'value' => Str::limit(request('searchValue'), 28),
            'muted' => true,
        ];
    }

    $headerActions = [
        [
            'href' => route('admin.warranty.claim.all'),
            'class' => 'btn btn-outline--primary text-nowrap',
            'label' => translate('view_all'),
        ],
    ];
@endphp

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/business_analytics.png') }}">
            {{translate('warranty_dashboard')}}
        </h2>
        <a href="{{route('admin.warranty.import')}}" class="btn btn--primary">{{translate('import_serials')}}</a>
    </div>

    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'warranty-dashboard-toolbar',
        'toolbarAction' => route('admin.warranty.dashboard'),
        'toolbarResetUrl' => route('admin.warranty.dashboard'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])

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
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('recent_claims'),
            'listHeaderTotal' => $recentClaims->count(),
            'listHeaderActions' => $headerActions,
        ])
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
