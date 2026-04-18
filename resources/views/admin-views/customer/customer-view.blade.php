@extends('layouts.back-end.app')

@section('title', translate('customer_details'))

@push('css_or_js')
    <style>
        .customer-view-modern {
            --surface: #fff;
            --soft-surface: #f3f6fb;
            --ink: #1e2a3b;
            --muted: #5f6f84;
            --stroke: #dbe3ef;
            --accent: #1f4f9b;
            --accent-soft: #eaf1ff;
            display: grid;
            gap: 1rem;
        }

        .customer-view-modern .modern-card {
            border: 1px solid var(--stroke);
            border-radius: 14px;
            background: var(--surface);
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
        }

        .customer-view-modern .hero-grid {
            display: grid;
            grid-template-columns: minmax(320px, 1.5fr) minmax(260px, 1fr);
            gap: 1rem;
        }

        .customer-view-modern .profile-card {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #f5f9ff 100%);
            text-align: start;
        }

        .customer-view-modern .profile-body {
            min-width: 0;
            flex: 1 1 260px;
            display: grid;
            gap: .65rem;
        }

        .customer-view-modern .avatar {
            width: 88px;
            height: 88px;
            border-radius: 16px;
            object-fit: cover;
            border: 1px solid #dce7f5;
            background: #f3f4f6;
        }

        .customer-view-modern .profile-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--ink);
        }

        .customer-view-modern .profile-subtitle {
            margin: .15rem 0 0;
            color: var(--muted);
            font-size: .82rem;
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .35rem;
        }

        .customer-view-modern .kv {
            margin: 0;
            display: grid;
            gap: .35rem;
        }

        .customer-view-modern .kv-row {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: wrap;
            gap: .55rem;
            color: var(--ink);
            font-size: .86rem;
        }

        .customer-view-modern .kv-key {
            min-width: 90px;
            color: var(--muted);
            font-weight: 500;
        }

        .customer-view-modern .kv-value {
            min-width: 0;
        }

        .customer-view-modern .meta-card {
            padding: 1rem;
            background: linear-gradient(150deg, #163d83 0%, #1f4f9b 52%, #2a68be 100%);
            color: #fff;
            border: 0;
        }

        .customer-view-modern .meta-title {
            margin: 0 0 .85rem;
            font-size: .92rem;
            letter-spacing: .02em;
            opacity: .95;
            color: #ffffff;
            font-weight: 700;
            background: transparent;
        }

        .customer-view-modern .meta-list {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: .6rem;
        }

        .customer-view-modern .meta-list li {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            font-size: .83rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .customer-view-modern .meta-list strong {
            color: #fff;
            font-size: .98rem;
        }

        .customer-view-modern .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: .75rem;
        }

        .customer-view-modern .kpi {
            border-radius: 12px;
            border: 1px solid var(--stroke);
            padding: .8rem;
            background: var(--surface);
            display: flex;
            flex-direction: column;
            gap: .25rem;
        }

        .customer-view-modern .kpi-label {
            color: var(--muted);
            font-size: .78rem;
            line-height: 1.2;
        }

        .customer-view-modern .kpi-value {
            font-size: 1.25rem;
            line-height: 1;
            font-weight: 700;
            color: var(--ink);
        }

        .customer-view-modern .module-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .customer-view-modern .module-card {
            padding: 1rem;
            display: grid;
            gap: .9rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            text-align: start;
        }

        .customer-view-modern .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .5rem;
        }

        .customer-view-modern .module-title {
            margin: 0;
            color: var(--ink);
            font-size: .95rem;
            font-weight: 700;
        }

        .customer-view-modern .module-stats {
            display: grid;
            gap: .45rem;
        }

        .customer-view-modern .module-stat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #eff4fb;
            border-radius: 8px;
            padding: .48rem .6rem;
            font-size: .82rem;
            color: var(--ink);
        }

        .customer-view-modern .module-list {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: .45rem;
        }

        .customer-view-modern .module-list li {
            padding: .5rem .6rem;
            border: 1px solid var(--stroke);
            border-radius: 9px;
            background: #fff;
            font-size: .78rem;
            color: var(--ink);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .6rem;
        }

        .customer-view-modern .module-list a {
            color: var(--ink);
            text-decoration: none;
        }

        .customer-view-modern .module-list a:hover {
            color: var(--accent);
        }

        .customer-view-modern .address-wrap {
            padding: 1rem;
            display: grid;
            gap: .75rem;
        }

        .customer-view-modern .address-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: .75rem;
        }

        .customer-view-modern .address-card {
            border: 1px solid var(--stroke);
            border-radius: 10px;
            padding: .75rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            font-size: .82rem;
            text-align: start;
        }

        .customer-view-modern .field-list {
            display: grid;
            gap: .35rem;
        }

        .customer-view-modern .field-row {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            flex-wrap: wrap;
            gap: .25rem .45rem;
            color: var(--ink);
        }

        .customer-view-modern .field-label {
            color: var(--ink);
            font-weight: 700;
            white-space: nowrap;
        }

        .customer-view-modern .field-value {
            min-width: 0;
        }

        .customer-view-modern .bidi-ltr {
            direction: ltr;
            unicode-bidi: isolate;
            display: inline-block;
        }

        .customer-view-modern .address-card h6 {
            margin: 0 0 .45rem;
            color: var(--ink);
            font-size: .85rem;
        }

        .customer-view-modern .orders-card {
            overflow: hidden;
        }

        .customer-view-modern .orders-head {
            padding: 1rem;
            border-bottom: 1px solid var(--stroke);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .customer-view-modern .orders-title {
            margin: 0;
            font-size: .98rem;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .customer-view-modern .search-wrap {
            display: flex;
            align-items: stretch;
            gap: .45rem;
            flex-wrap: nowrap;
            width: 100%;
            justify-content: flex-end;
        }

        .customer-view-modern .search-form {
            display: flex;
            align-items: stretch;
            gap: .45rem;
            flex-wrap: nowrap;
            flex: 1;
            justify-content: flex-end;
            min-width: 320px;
        }

        .customer-view-modern .search-input {
            min-width: min(420px, 70vw);
            max-width: 620px;
            flex: 1;
        }

        .customer-view-modern .search-form .btn,
        .customer-view-modern .search-wrap > .btn {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .customer-view-modern .orders-table thead th {
            border-top: 0;
            font-size: .78rem;
            color: var(--muted);
            font-weight: 600;
            white-space: nowrap;
        }

        .customer-view-modern .orders-table tbody td {
            vertical-align: middle;
        }

        .customer-view-modern .table-actions {
            display: inline-flex;
            gap: .35rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .customer-view-modern .muted {
            color: var(--muted);
        }

        .customer-view-modern[dir="rtl"] .search-wrap {
            justify-content: flex-start;
        }

        .customer-view-modern[dir="rtl"] .search-form {
            justify-content: flex-start;
        }

        .customer-view-modern[dir="rtl"] .search-form .input-group {
            direction: rtl;
        }

        .customer-view-modern[dir="rtl"] .search-form .input-group .form-control {
            text-align: right;
        }

        @media (max-width: 1199px) {
            .customer-view-modern .module-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991px) {
            .customer-view-modern .hero-grid,
            .customer-view-modern .module-grid {
                grid-template-columns: 1fr;
            }

            .customer-view-modern .search-input {
                min-width: 100%;
                max-width: 100%;
            }

            .customer-view-modern .search-wrap {
                flex-direction: column;
                align-items: stretch;
            }

            .customer-view-modern .search-form {
                min-width: 100%;
                width: 100%;
                flex-wrap: wrap;
            }

            .customer-view-modern .search-form .btn,
            .customer-view-modern .search-wrap > .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $direction = get_direction();
        $crmSearchSeed = $customer['email'] ?: ($customer['phone'] ?: (string)$customer['id']);
        $crmDealSearchSeed = $customer['phone'] ?: ($customer['email'] ?: (string)$customer['id']);
        $crmViewAllUrl = route('admin.crm.index', ['searchValue' => $crmSearchSeed, 'status' => 'all']);
        if (($integrationData['crm']['deals_count'] ?? 0) > 0) {
            $crmViewAllUrl = route('admin.crm.deals.retail.list', ['searchValue' => $crmDealSearchSeed, 'status' => 'all']);
        } elseif (($integrationData['crm']['leads_count'] ?? 0) > 0) {
            $crmViewAllUrl = route('admin.crm.lead.index', ['searchValue' => $crmSearchSeed, 'status' => 'all']);
        }
    @endphp

    <div class="content container-fluid">
        <div class="customer-view-modern" dir="{{ $direction }}">
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <h2 class="h1 mb-0 text-capitalize d-flex gap-2 align-items-center">
                    <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                    {{ translate('customer_details') }}
                </h2>
                <a href="{{ route('admin.customer.list') }}" class="btn btn-outline--primary btn-sm">
                    <i class="{{ $direction === 'rtl' ? 'tio-arrow-forward' : 'tio-arrow-backward' }}"></i> {{ translate('Back') }}
                </a>
            </div>

            <div class="hero-grid">
                <div class="modern-card profile-card">
                    <img src="{{ getStorageImages(path: $customer->image_full_url, type: 'backend-profile') }}"
                         alt="{{ translate('Image') }}"
                         class="avatar">

                    <div class="profile-body">
                        <h4 class="profile-title line--limit-1" title="{{ $customer['f_name'].' '.$customer['l_name'] }}">
                            {{ $customer['f_name'].' '.$customer['l_name'] }}
                        </h4>
                        <p class="profile-subtitle">
                            <span>{{ translate('Customer') }}</span>
                            <span class="bidi-ltr">#{{ $customer['id'] }}</span>
                        </p>

                        <div class="kv">
                            <div class="kv-row">
                                <span class="kv-key">{{ translate('Contact') }}</span>
                                <strong class="kv-value">
                                    @if($customer['phone'])
                                        <span class="bidi-ltr">{{ $customer['phone'] }}</span>
                                    @else
                                        {{ translate('no_Data_found') }}
                                    @endif
                                </strong>
                            </div>
                            <div class="kv-row">
                                <span class="kv-key">{{ translate('Email') }}</span>
                                <strong class="kv-value line--limit-1">
                                    @if($customer['email'])
                                        <span class="bidi-ltr">{{ $customer['email'] }}</span>
                                    @else
                                        {{ translate('no_Data_found') }}
                                    @endif
                                </strong>
                            </div>
                            <div class="kv-row">
                                <span class="kv-key">{{ translate('joined_date') }}</span>
                                <strong class="kv-value"><span class="bidi-ltr">{{ date('d M Y', strtotime($customer['created_at'])) }}</span></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modern-card meta-card">
                    <h5 class="meta-title">{{ translate('customer_overview') }}</h5>
                    <ul class="meta-list">
                        <li>
                            <span>{{ translate('saved_address') }}</span>
                            <strong>{{ $customer->addresses->count() }}</strong>
                        </li>
                        <li>
                            <span>{{ translate('total_Orders') }}</span>
                            <strong>{{ $orderStatusArray['total_order'] }}</strong>
                        </li>
                        <li>
                            <span>{{ translate('crm') }}</span>
                            <strong>{{ $integrationData['crm']['overview_count'] }}</strong>
                        </li>
                        <li>
                            <span>{{ translate('Warranty') }}</span>
                            <strong>{{ $integrationData['warranty']['overview_count'] }}</strong>
                        </li>
                        <li>
                            <span>{{ translate('calls') }}</span>
                            <strong>{{ $integrationData['calls']['overview_count'] }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="kpi-grid">
                <div class="kpi">
                    <span class="kpi-label">{{ translate('total_Orders') }}</span>
                    <span class="kpi-value">{{ $orderStatusArray['total_order'] }}</span>
                </div>
                <div class="kpi">
                    <span class="kpi-label">{{ translate('ongoing') }}</span>
                    <span class="kpi-value">{{ $orderStatusArray['ongoing'] }}</span>
                </div>
                <div class="kpi">
                    <span class="kpi-label">{{ translate('completed') }}</span>
                    <span class="kpi-value">{{ $orderStatusArray['completed'] }}</span>
                </div>
                <div class="kpi">
                    <span class="kpi-label">{{ translate('canceled') }}</span>
                    <span class="kpi-value">{{ $orderStatusArray['canceled'] }}</span>
                </div>
                <div class="kpi">
                    <span class="kpi-label">{{ translate('Returned') }}</span>
                    <span class="kpi-value">{{ $orderStatusArray['returned'] }}</span>
                </div>
                <div class="kpi">
                    <span class="kpi-label">{{ translate('refunded') }}</span>
                    <span class="kpi-value">{{ $orderStatusArray['refunded'] }}</span>
                </div>
                <div class="kpi">
                    <span class="kpi-label">{{ translate('crm') }}</span>
                    <span class="kpi-value">{{ $integrationData['crm']['overview_count'] }}</span>
                </div>
                <div class="kpi">
                    <span class="kpi-label">{{ translate('warranty_Claims') }}</span>
                    <span class="kpi-value">{{ $integrationData['warranty']['claims_count'] }}</span>
                </div>
            </div>

            @if($customer->addresses->count() > 0)
                <div class="modern-card address-wrap">
                    <h5 class="m-0">{{ translate('saved_address') }}</h5>
                    <div class="address-grid">
                        @foreach($customer->addresses as $address)
                            <div class="address-card">
                                <h6>{{ translate($address['address_type']).' ( '.translate($address['is_billing'] == 0 ? 'shipping_address': 'billing_address').' )' }}</h6>
                                <div class="field-list">
                                    <div class="field-row">
                                        <strong class="field-label">{{ translate('Name') }}:</strong>
                                        <span class="field-value">{{ $address['contact_person_name'] ?: translate('no_Data_found') }}</span>
                                    </div>
                                    <div class="field-row">
                                        <strong class="field-label">{{ translate('Phone') }}:</strong>
                                        <span class="field-value">
                                            @if($address['phone'])
                                                <span class="bidi-ltr">{{ $address['phone'] }}</span>
                                            @else
                                                {{ translate('no_Data_found') }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="field-row">
                                        <strong class="field-label">{{ translate('City') }}:</strong>
                                        <span class="field-value">{{ $address['city'] ?: translate('no_Data_found') }}</span>
                                    </div>
                                    <div class="field-row">
                                        <strong class="field-label">{{ translate('Area') }}:</strong>
                                        <span class="field-value">{{ $address['area'] ?: ($address['state'] ?: translate('no_Data_found')) }}</span>
                                    </div>
                                    <div class="field-row">
                                        <strong class="field-label">{{ translate('Address') }}:</strong>
                                        <span class="field-value">{{ $address['address'] ?: translate('no_Data_found') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="module-grid">
                <div class="modern-card module-card">
                    <div class="module-header">
                        <h5 class="module-title">{{ translate('crm') }}</h5>
                        <a href="{{ $crmViewAllUrl }}" class="btn btn-outline--primary btn-xs">
                            {{ translate('view_all') }}
                        </a>
                    </div>

                    <div class="module-stats">
                        <div class="module-stat-row">
                            <span>{{ translate('messages') }}</span>
                            <strong>{{ $integrationData['crm']['messages_count'] }}</strong>
                        </div>
                        <div class="module-stat-row">
                            <span>{{ translate('Leads') }}</span>
                            <strong>{{ $integrationData['crm']['leads_count'] }}</strong>
                        </div>
                        <div class="module-stat-row">
                            <span>{{ translate('deals') }}</span>
                            <strong>{{ $integrationData['crm']['deals_count'] }}</strong>
                        </div>
                    </div>

                    <ul class="module-list">
                        @if($integrationData['crm']['recent_messages']->count() > 0)
                            @foreach($integrationData['crm']['recent_messages'] as $message)
                                <li>
                                    <a class="line--limit-1" href="{{ route('admin.crm.message.show', $message->id) }}">
                                        {{ $message->subject ?: translate('no_subject') }}
                                    </a>
                                    <span class="badge badge-soft-info">{{ translate($message->status) }}</span>
                                </li>
                            @endforeach
                        @elseif($integrationData['crm']['recent_deals']->count() > 0)
                            @foreach($integrationData['crm']['recent_deals'] as $deal)
                                <li>
                                    <a class="line--limit-1" href="{{ route('admin.crm.deals.retail.view', $deal->id) }}">
                                        <span class="bidi-ltr">#{{ $deal->id }}</span>
                                        <small class="muted d-block">
                                            @if($deal->updated_at)
                                                <span class="bidi-ltr">{{ $deal->updated_at->format('d M Y, h:i A') }}</span>
                                            @endif
                                        </small>
                                    </a>
                                    <span class="badge badge-soft-success">{{ translate((string) $deal->status) }}</span>
                                </li>
                            @endforeach
                        @else
                            <li class="muted">{{ translate('no_Data_found') }} ({{ translate('crm') }} {{ translate('messages') }})</li>
                        @endif
                    </ul>
                </div>

                <div class="modern-card module-card">
                    <div class="module-header">
                        <h5 class="module-title">{{ translate('Warranty') }}</h5>
                        <a href="{{ route('admin.warranty.claim.all') }}" class="btn btn-outline--primary btn-xs">
                            {{ translate('view_all') }}
                        </a>
                    </div>

                    <div class="module-stats">
                        <div class="module-stat-row">
                            <span>{{ translate('warranties') }}</span>
                            <strong>{{ $integrationData['warranty']['warranties_count'] }}</strong>
                        </div>
                        <div class="module-stat-row">
                            <span>{{ translate('Active') }}</span>
                            <strong>{{ $integrationData['warranty']['active_warranties_count'] }}</strong>
                        </div>
                        <div class="module-stat-row">
                            <span>{{ translate('open_claims') }}</span>
                            <strong>{{ $integrationData['warranty']['open_claims_count'] }}</strong>
                        </div>
                    </div>

                    <ul class="module-list">
                        @forelse($integrationData['warranty']['recent_claims'] as $claim)
                            <li>
                                <a href="{{ route('admin.warranty.claim.view', $claim->id) }}">
                                    <span class="bidi-ltr">{{ $claim->claim_number ?: ('#'.$claim->id) }}</span>
                                </a>
                                <span class="badge badge-soft-warning">{{ translate($claim->status) }}</span>
                            </li>
                        @empty
                            <li class="muted">{{ translate('no_Data_found') }} ({{ translate('warranty_Claims') }})</li>
                        @endforelse
                    </ul>
                </div>

                <div class="modern-card module-card">
                    <div class="module-header">
                        <h5 class="module-title">{{ translate('calls') }}</h5>
                        <a href="{{ route('admin.crm.dashboard') }}" class="btn btn-outline--primary btn-xs">
                            {{ translate('view_all') }}
                        </a>
                    </div>

                    <div class="module-stats">
                        <div class="module-stat-row">
                            <span>{{ translate('Total') }}</span>
                            <strong>{{ $integrationData['calls']['calls_count'] }}</strong>
                        </div>
                        <div class="module-stat-row">
                            <span>{{ translate('ongoing') }}</span>
                            <strong>{{ $integrationData['calls']['ongoing_calls_count'] }}</strong>
                        </div>
                        <div class="module-stat-row">
                            <span>{{ translate('completed') }}</span>
                            <strong>{{ $integrationData['calls']['completed_calls_count'] }}</strong>
                        </div>
                    </div>

                    <ul class="module-list">
                        @forelse($integrationData['calls']['recent_calls'] as $call)
                            <li>
                                <span class="line--limit-1">
                                    <span class="bidi-ltr">{{ $call->call_id ?: ('#'.$call->id) }}</span>
                                    <small class="muted d-block">
                                        @if($call->call_date)
                                            <span class="bidi-ltr">{{ $call->call_date->format('d M Y, h:i A') }}</span>
                                        @else
                                            {{ translate('no_Data_found') }}
                                        @endif
                                    </small>
                                </span>
                                <span class="badge badge-soft-success">{{ translate($call->status) }}</span>
                            </li>
                        @empty
                            <li class="muted">{{ translate('no_Data_found') }} ({{ translate('calls') }})</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="modern-card orders-card">
                <div class="orders-head">
                    <h5 class="orders-title">
                        {{ translate('Orders') }}
                        <span class="badge badge-secondary">{{ $orders->total() }}</span>
                    </h5>

                    <div class="search-wrap">
                        <form action="{{ url()->current() }}" method="GET" class="search-form">
                            <div class="input-group input-group-merge input-group-custom search-input">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input type="search"
                                       name="searchValue"
                                       class="form-control {{ $direction === 'rtl' ? 'text-end' : 'text-start' }}"
                                       placeholder="{{ translate('search_by_order_id_customer_phone_or_email') }}"
                                       aria-label="{{ translate('search_orders') }}"
                                       value="{{ $searchValue }}">
                            </div>
                            <button type="submit" class="btn btn--primary">{{ translate('Search') }}</button>
                            @if(request()->filled('searchValue'))
                                <a href="{{ url()->current() }}" class="btn btn-outline-secondary">{{ translate('Reset') }}</a>
                            @endif
                        </form>

                        <a class="btn btn-outline--primary text-nowrap"
                           href="{{ route('admin.customer.order-list-export', [$customer['id'], 'searchValue' => request('searchValue')]) }}">
                            <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                            <span class="ps-2">{{ translate('export') }}</span>
                        </a>
                    </div>
                </div>

                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 orders-table">
                        <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('Order_ID') }}</th>
                            <th>{{ translate('Total') }}</th>
                            <th>{{ translate('Order_Status') }}</th>
                            <th class="text-center">{{ translate('Action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($orders as $key => $order)
                            <tr>
                                <td>{{ $orders->firstItem() + $key }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.details', ['id' => $order['id']]) }}"
                                       class="title-color hover-c1">
                                        <span class="bidi-ltr">{{ $order['id'] }}</span>
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $order['order_amount']), currencyCode: getCurrencyCode()) }}
                                    </div>
                                    @if($order->payment_status == 'paid')
                                        <span class="badge badge-soft-success">{{ translate('paid') }}</span>
                                    @else
                                        <span class="badge badge-soft-danger">{{ translate('unpaid') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($order['order_status'] == 'pending')
                                        <span class="badge badge-soft-info fz-12">{{ translate($order['order_status']) }}</span>
                                    @elseif($order['order_status'] == 'processing' || $order['order_status'] == 'out_for_delivery')
                                        <span class="badge badge-soft-warning fz-12">
                                            {{ str_replace('_',' ', $order['order_status'] == 'processing' ? translate('Packaging') : translate($order['order_status'])) }}
                                        </span>
                                    @elseif($order['order_status'] == 'confirmed' || $order['order_status'] == 'delivered')
                                        <span class="badge badge-soft-success fz-12">{{ translate($order['order_status']) }}</span>
                                    @elseif($order['order_status'] == 'failed')
                                        <span class="badge badge-soft-danger fz-12">{{ translate('Failed_to_Deliver') }}</span>
                                    @else
                                        <span class="badge badge-soft-danger fz-12">{{ translate($order['order_status']) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="table-actions">
                                        <a class="btn btn-outline--primary btn-sm square-btn"
                                           title="{{ translate('View') }}"
                                           href="{{ route('admin.orders.details', ['id' => $order['id']]) }}">
                                            <i class="tio-invisible"></i>
                                        </a>
                                        <a class="btn btn-outline-info btn-sm square-btn"
                                           title="{{ translate('Invoice') }}"
                                           target="_blank"
                                           href="{{ route('admin.orders.generate-invoice', [$order['id']]) }}">
                                            <i class="tio-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    @include('layouts.back-end._empty-state', ['text' => 'no_order_found'], ['image' => 'default'])
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-3 p-3 pt-0">
                    <div class="d-flex justify-content-lg-end">
                        {!! $orders->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
