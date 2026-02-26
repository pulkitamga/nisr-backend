@php use App\Utils\Helpers; @endphp
@extends('layouts.back-end.app')
@section('title', translate('Dashboard'))
@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Header -->
    <div class="page-header pb-0 mb-0 border-0">
        <div class="flex-between align-items-center">
            <div>
                <h1 class="page-header-title">{{ translate('Welcome_Admin') }}</h1>
                <p>{{ translate('Monitor your business analytics and statistics.') }}</p>
            </div>
        </div>
    </div>

    <!-- Statistics selector -->
    <div class="card mb-2 remove-card-shadow">
        <div class="card-body">
            <div class="row flex-between align-items-center g-2 mb-3">
                <div class="col-sm-6">
                    <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                        <img src="{{ asset('public/assets/back-end/img/business_analytics.png') }}" alt="">
                        {{ translate('Business Analytics') }}
                    </h4>
                </div>
                <div class="col-sm-6 d-flex justify-content-sm-end">
                    <select class="custom-select w-auto" name="statistics_type" id="statistics_type_for_crm">
                        <option value="overall" {{ $statisticsType == 'overall' ? 'selected' : '' }}>{{ translate('Overall Statistics') }}</option>
                        <option value="today" {{ $statisticsType == 'today' ? 'selected' : '' }}>{{ translate("Today's Statistics") }}</option>
                        <option value="this_month" {{ $statisticsType == 'this_month' ? 'selected' : '' }}>{{ translate("This Month's Statistics") }}</option>
                    </select>
                </div>
            </div>

            <!-- ==== MESSAGES ==== -->
            <div class="row g-2" id="section_messages">
                @include('admin-views.crm.dashboard.partials.message')
            </div>

            <!-- ==== LEADS ==== -->
            <div class="row g-2" id="section_leads">
                @include('admin-views.crm.dashboard.partials.lead')
            </div>
        </div>
    </div>
    <div id="loading" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); z-index: 9999; justify-content: center; align-items: center;">
        <i class="tio-loading spin"></i> {{ translate('Loading...') }}
    </div>
    <!-- ==== DEALS ==== -->
    <div class="card mb-2 remove-card-shadow">
        <div class="card-body">
            <div class="row flex-between align-items-center g-2 mb-3" id="section_deals">
                @include('admin-views.crm.dashboard.partials.deal')
            </div>
        </div>
    </div>

    <!-- ==== TICKETS ==== -->
    <div class="card mb-2 remove-card-shadow">
        <div class="card-body">
            <div class="row flex-between align-items-center g-2 mb-3">
                <div class="col-sm-12">
                    <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                        <img src="{{ asset('public/assets/back-end/img/business_analytics.png') }}" alt="">
                        {{ translate('Tickets Section') }}
                    </h4>
                </div>
            </div>
            <div class="row g-2" id="section_tickets">
                @include('admin-views.crm.dashboard.partials.tickets', [
                'counts' => [
                'Support' => $supportTickets,
                'Complaint' => $complaintTickets,
                'Service' => $serviceTickets,
                'Career' => $careerTickets,
                'Retail' => $retailTickets,
                'Wholesale' => $wholesaleTickets
                ],
                'img' => 'tickets.png'
                ])
            </div>
        </div>
    </div>

    <!-- ==== WARRANTY ==== -->
    <div class="card mb-2 remove-card-shadow">
        <div class="card-body">
            <div class="row flex-between align-items-center g-2 mb-3">
                <div class="col-sm-12">
                    <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                        <img src="{{ asset('public/assets/back-end/img/business_analytics.png') }}" alt="">
                        {{ translate('Warranty Section') }}
                    </h4>
                </div>
            </div>
            <div class="row g-2" id="section_warranty">
                @include('admin-views.crm.dashboard.partials.warranty', [
                'counts' => [
                'Claims' => $warrantyClaims,
                'Approved' => $claimsApproved,
                'Pending' => $claimsPending,
                'Active' => $activeWarranty
                ],
                'img' => 'warranty.png'
                ])
            </div>
        </div>
    </div>

    <!-- ==== SLA ==== -->
    <div class="card mb-2 remove-card-shadow">
        <div class="card-body">
            <div class="row flex-between align-items-center g-2 mb-3">
                <div class="col-sm-12">
                    <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                        <img src="{{ asset('public/assets/back-end/img/business_analytics.png') }}" alt="">
                        {{ translate('SLA and Activity') }}
                    </h4>
                </div>
            </div>
            <div class="row g-2" id="section_sla">
                @include('admin-views.crm.dashboard.partials.sla', [
                'counts' => [
                'Overdue SLAs' => $overdueSLAs,
                'Pending Activities'=> $pendingActivities,
                'VoIP Calls Today' => $voipCallsToday
                ],
                'img' => 'sla.png'
                ])
            </div>
        </div>
    </div>

    <!-- ==== SERVICE OVERVIEW (chart) ==== -->
    <div class="row g-1" id="section_service">
        <div class="col-lg-12">
            @include('admin-views.crm.dashboard.partials.service_overview', [
            'totalServices' => $totalServices,
            'totalInvoice' => $totalInvoice
            ])
        </div>
    </div>
</div>

<!-- hidden helpers for JS -->
<span id="order-status-url-crm" data-url="{{ route('admin.crm.dashboard') }}"></span>
@endsection
@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/apexcharts.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/dashboard.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        renderServiceChart({{ $totalServices }}, {{ $totalInvoice }});
    });

    $('#statistics_type_for_crm').on('change', function () {
        const type = $(this).val();
        const url = $('#order-status-url-crm').data('url');
        console.log('Statistics type changed to:', type);

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: { statistics_type: type, _token: $('meta[name="csrf-token"]').attr('content') },
            beforeSend: function () { $('#loading').fadeIn(); },
            success: function (resp) {
                console.log('AJAX response:', resp);
                if (!resp.success) { alert(@json(translate('Error loading data'))); return; }
                updateNumbers(resp.data);
                renderServiceChart(resp.data.totalServices, resp.data.totalInvoice);
            },
            error: function (xhr) { console.error('AJAX error:', xhr.responseText); alert(@json(translate('Request failed - check console'))); },
            complete: function () { $('#loading').fadeOut(); }
        });
    });

    function updateNumbers(data) {
        // Messages
        $('#inboundMessages').text(data.inboundMessages);
        $('#newMessages').text(data.newMessages);
        $('#convertedMessages').text(data.convertedMessages);
        $('#ignoredMessages').text(data.ignoredMessages);

        // Leads
        $('#totalLeads').text(data.totalLeads);
        $('#workingLeads').text(data.workingLeads);
        $('#qualifiedLeads').text(data.qualifiedLeads);
        $('#convertedLeads').text(data.convertedLeads);

        // Deals Retail
        $('#openRetailDeals').text(data.openRetailDeals);
        $('#wonRetailDeals').text(data.wonRetailDeals);
        $('#lostRetailDeals').text(data.lostRetailDeals);

        // Deals Wholesale
        $('#openWholesaleDeals').text(data.openWholesaleDeals);
        $('#wonWholesaleDeals').text(data.wonWholesaleDeals);
        $('#lostWholesaleDeals').text(data.lostWholesaleDeals);

        // Tickets (dynamic IDs)
        $('#count-Support').text(data.supportTickets);
        $('#count-Complaint').text(data.complaintTickets);
        $('#count-Service').text(data.serviceTickets);
        $('#count-Career').text(data.careerTickets);
        $('#count-Retail').text(data.retailTickets);
        $('#count-Wholesale').text(data.wholesaleTickets);

        // Warranty (dynamic IDs)
        $('#count-Claims').text(data.warrantyClaims);
        $('#count-Approved').text(data.claimsApproved);
        $('#count-Pending').text(data.claimsPending);
        $('#count-Active').text(data.activeWarranty);

        // SLA (dynamic IDs) - note: spaces in keys replaced with nothing in ID, but data keys have spaces, so map manually
        $('#count-Overdue SLAs').text(data.overdueSLAs);
        $('#count-Pending Activities').text(data.pendingActivities);
        $('#count-VoIP Calls Today').text(data.voipCallsToday);

        // Service Overview Legend/Header (assuming your helpers for currency)
        $('#totalServicesLegend').text(data.totalServices);
        $('#totalInvoiceLegend').text(new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(data.totalInvoice)); // Adjust currency formatter as per your app
        $('#totalInvoiceHeader').text(new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(data.totalInvoice)); // Adjust as needed
    }

    let serviceChart = null;
    function renderServiceChart(services, invoice) {
        const options = {
            chart: { type: 'donut', height: 280 },
            series: [parseFloat(services), parseFloat(invoice)],
            labels: [@json(translate('Services Completed')), @json(translate('Invoice Amount'))],
            colors: ['#00E396', '#FEB019'],
            legend: { show: false },
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: val => val.toLocaleString() } }
        };

        const el = document.querySelector('#serviceChart1');
        if (!el) return console.error('#serviceChart1 not found');
        if (serviceChart) serviceChart.destroy();
        serviceChart = new ApexCharts(el, options);
        serviceChart.render();
    }
</script>
@endpush
