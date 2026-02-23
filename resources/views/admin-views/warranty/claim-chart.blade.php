@extends('layouts.back-end.app')

@section('title', translate('warranty_claims_chart'))

@push('css_or_js')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        .chart-card > div:last-child {
            position: relative;
            height: 350px;
            width: 100%;
        }
        #claimsChart {
            height: 100% !important;
            width: 100% !important;
        }
        .chart-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }
        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #eef2f7;
            transition: 0.2s;
        }
        .summary-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .filter-section {
            background: #f9fbfd;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .filter-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
}
.stat-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.stat-number {
    font-size: 1.8rem;
    font-weight: bold;
    line-height: 1;
}

.stat-label {
    color: #7f8c8d;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
    </style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/warranty.png') }}" alt="">
            {{ translate('claims_analytics') }}
        </h2>
    </div>

   <div class="filter-card">
        <form id="filterForm" method="GET" action="{{ url()->current() }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ translate('date_range') }}</label>
                    <div class="position-relative">
                        <span class="tio-calendar icon-absolute-on-right"></span>
                        <input type="text" name="date_range" id="dateRangePicker"
                               class="form-control cursor-pointer"
                               value="{{ $startDate->format('m/d/Y') }} - {{ $endDate->format('m/d/Y') }}"
                               autocomplete="off" readonly>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ translate('branch') }}</label>
                    <select class="form-control" name="branch_id" id="branchFilter">
                        <option value="">{{ translate('all_branches') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ translate('status') }}</label>
                    <select class="form-control" name="status" id="statusFilter">
                        <option value="all">{{ translate('all_statuses') }}</option>
                        <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>{{ translate('new') }}</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ translate('approved') }}</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ translate('rejected') }}</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>{{ translate('resolved') }}</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>{{ translate('closed') }}</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <label class="form-label">{{ translate('search') }}</label>
                    <input type="text" name="search" id="searchInput" class="form-control"
                           placeholder="{{ translate('claim_or_serial') }}" value="{{ request('search') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ translate('product') }}</label>
                    <select class="form-control" name="product_id" id="productFilter">
                        <option value="">{{ translate('all_products') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button type="submit" class="btn btn--primary btn-block">
                        <i class="tio-filter"></i> {{ translate('apply') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

   <div class="row mb-4" id="summaryCards">
    <div class="col-md-2 col-sm-4">
        <div class="stat-card" style="border-left: 4px solid #3498db;">
            <div class="stat-number text-primary" id="card-total">{{ $cards['total'] }}</div>
            <div class="stat-label">{{ translate('total_claims') }}</div>
        </div>
    </div>

    <div class="col-md-2 col-sm-4">
        <div class="stat-card" style="border-left: 4px solid #f39c12;">
            <div class="stat-number text-warning" id="card-new">{{ $cards['new'] }}</div>
            <div class="stat-label">{{ translate('new') }}</div>
        </div>
    </div>

    <div class="col-md-2 col-sm-4">
        <div class="stat-card" style="border-left: 4px solid #2ecc71;">
            <div class="stat-number text-success" id="card-approved">{{ $cards['approved'] }}</div>
            <div class="stat-label">{{ translate('approved') }}</div>
        </div>
    </div>

    <div class="col-md-2 col-sm-4">
        <div class="stat-card" style="border-left: 4px solid #e74c3c;">
            <div class="stat-number text-danger" id="card-rejected">{{ $cards['rejected'] }}</div>
            <div class="stat-label">{{ translate('rejected') }}</div>
        </div>
    </div>

    <div class="col-md-2 col-sm-4">
        <div class="stat-card" style="border-left: 4px solid #9b59b6;">
            <div class="stat-number text-info" id="card-pending">{{ $cards['pending'] }}</div>
            <div class="stat-label">{{ translate('pending') }}</div>
        </div>
    </div>

    <div class="col-md-2 col-sm-4">
        <div class="stat-card" style="border-left: 4px solid #34495e;">
            <div class="stat-number text-dark" id="card-resolved">{{ $cards['resolved'] }}</div>
            <div class="stat-label">{{ translate('resolved') }}</div>
        </div>
    </div>
</div>

    <div class="chart-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ translate('claims_by_day') }} ({{ translate('stacked') }})</h4>
            <span class="badge badge-soft-primary" id="dateRangeLabel">
                {{ $startDate->format('d M') }} - {{ $endDate->format('d M') }}
            </span>
        </div>

         <div class="d-flex justify-content-end gap-2 mb-3">
            <a href="{{ route('admin.warranty.claim.export.excel') }}?{{ http_build_query(request()->all()) }}" 
            class="btn btn-success">
                <i class="tio-file-excel"></i> {{ translate('export_excel') }}
            </a>
            <a href="{{ route('admin.warranty.claim.export.pdf') }}?{{ http_build_query(request()->all()) }}" 
                class="btn btn-danger">
                <i class="tio-file-pdf"></i> {{ translate('export_pdf') }}
            </a>
        </div>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="claimsChart" style="height: 100%; width: 100%;"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ translate('claims_list') }} <span class="badge badge-soft-dark" id="claimsTotal">{{ $claims->total() }}</span></h5>
        </div>
        <div class="table-responsive datatable-custom">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('claim_number') }}</th>
                        <th>{{ translate('serial') }}</th>
                        <th>{{ translate('product') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th>{{ translate('customer') }}</th>
                        <th>{{ translate('submitted_at') }}</th>
                        <th>{{ translate('sla_due') }}</th>
                        <th class="text-center">{{ translate('action') }}</th>
                    </tr>
                </thead>
                <tbody id="claimTableBody">
                    @foreach($claims as $key => $claim)
                    @php
                        $badge = match($claim->status){
                            'new','waiting_customer','waiting_parts','waiting_payment' => 'warning',
                            'rejected','closed' => 'danger',
                            default => 'success'
                        };
                    @endphp
                    <tr>
                        <td>{{ $claims->firstItem() + $key }}</td>
                        <td>{{ $claim->claim_number }}</td>
                        <td>{{ $claim->serial_number }}</td>
                        <td>{{ $claim->warranty?->product?->name ?? '-' }}</td>
                        <td>
                            <span class="badge badge-soft-{{ $badge }} fz-12">
                                {{ translate($claim->status) }}
                            </span>
                        </td>
                        <td>{{ $claim->warranty?->user?->name ?? $claim->warranty?->activated_by_name ?? '' }}</td>
                        <td>{{ $claim->submitted_at?->format('Y-m-d H:i A') }}</td>
                        <td>{{ $claim->resolution_due?->format('Y-m-d H:i A') ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.warranty.claim.view', $claim->id) }}"
                               class="btn btn-sm btn-outline-info">{{ translate('view') }}</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($claims->isEmpty())
                @include('layouts.back-end._empty-state', ['text'=>'no_record_found','image'=>'default'])
            @endif
        </div>
        <div class="px-4 py-3 d-flex justify-content-end" id="paginationLinks">
            {{ $claims->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
let chartInstance = null;

const initialChartData = @json($chartData);
renderChart(initialChartData);

$(document).ready(function() {

    // ⭐ page load par export links sync karo
    updateExportLinks();

    $('#dateRangePicker').daterangepicker({
        startDate: moment('{{ $startDate }}'),
        endDate: moment('{{ $endDate }}'),
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: { format: 'MM/DD/YYYY' }
    });

    // ================================
    // FILTER SUBMIT
    // ================================
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();

        // ⭐ export links update karo
        updateExportLinks();

        // ===== CHART + CARDS =====
        $.get('{{ route("admin.warranty.claim.chart.data") }}', formData, function(res) {

            $('#card-total').text(res.cards.total);
            $('#card-new').text(res.cards.new);
            $('#card-approved').text(res.cards.approved);
            $('#card-rejected').text(res.cards.rejected);
            $('#card-pending').text(res.cards.pending);
            $('#card-resolved').text(res.cards.resolved);

            renderChart(res.chart);

            if (res.chart.labels && res.chart.labels.length > 0) {
                $('#dateRangeLabel').text(
                    res.chart.labels[0] + ' - ' +
                    res.chart.labels[res.chart.labels.length - 1]
                );
            }
        });

        // ===== TABLE =====
        $.get('{{ route("admin.warranty.claim.table.data") }}', formData, function(res) {
            refreshTable(res);
            $('#claimsTotal').text(res.total);
        });
    });

    // search debounce
    let timer;
    $('#searchInput').on('keyup', function() {
        clearTimeout(timer);
        timer = setTimeout(() => $('#filterForm').submit(), 500);
    });

    $('#statusFilter, #branchFilter, #productFilter').on('change', function() {
        $('#filterForm').submit();
    });
});


// ======================================
// ⭐ EXPORT LINKS UPDATE FUNCTION
// ======================================
function updateExportLinks() {

    let params = $('#filterForm').serialize();

    let excelUrl = '{{ route("admin.warranty.claim.export.excel") }}?' + params;
    let pdfUrl   = '{{ route("admin.warranty.claim.export.pdf") }}?' + params;

    $('.btn-success').attr('href', excelUrl);
    $('.btn-danger').attr('href', pdfUrl);
}


// ======================================
// CHART RENDER
// ======================================
function renderChart(data) {
    const ctx = document.getElementById('claimsChart').getContext('2d');

    if (chartInstance) chartInstance.destroy();

    chartInstance = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            },
            plugins: {
                tooltip: { mode: 'index', intersect: false },
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, boxWidth: 6 }
                }
            }
        }
    });
}


// ======================================
// TABLE REFRESH
// ======================================
function refreshTable(data) {

    const tbody = $('#claimTableBody');
    tbody.empty();

    if (data.data.length === 0) {
        tbody.html(`
        <tr>
            <td colspan="9" class="text-center py-5">
                <img src="{{ dynamicAsset("public/assets/back-end/img/empty.png") }}" width="100">
                <p class="mt-3">{{ translate("no_record_found") }}</p>
            </td>
        </tr>`);
        $('#paginationLinks').empty();
        return;
    }

    let sl = data.from || 1;

    $.each(data.data, function(index, claim) {

        let badgeClass = 'badge-soft-success';

        if (['new','waiting_customer','waiting_parts','waiting_payment'].includes(claim.status)) {
            badgeClass = 'badge-soft-warning';
        }
        else if (['rejected','closed'].includes(claim.status)) {
            badgeClass = 'badge-soft-danger';
        }

        tbody.append(`
        <tr>
            <td>${sl + index}</td>
            <td>${claim.claim_number}</td>
            <td>${claim.serial_number}</td>
            <td><span class="badge ${badgeClass}">${claim.status.replace(/_/g,' ')}</span></td>
            <td>${claim.customer}</td>
            <td>${claim.product_name}</td>
            <td>${claim.submitted_at}</td>
            <td>${claim.resolution_due}</td>
            <td class="text-center">
                <a href="${claim.view_url}" class="btn btn-sm btn-outline-info">
                    {{ translate('view') }}
                </a>
            </td>
        </tr>
        `);
    });

    $('#paginationLinks').html(generatePagination(data));
}


// ======================================
// PAGINATION
// ======================================
function generatePagination(data) {

    let html = '<ul class="pagination">';

    html += `<li class="page-item ${data.prev_page_url ? '' : 'disabled'}">
                <a class="page-link" href="#" data-page="${data.current_page - 1}">
                {{ translate('previous') }}</a>
            </li>`;

    for (let i = 1; i <= data.last_page; i++) {
        html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                 </li>`;
    }

    html += `<li class="page-item ${data.next_page_url ? '' : 'disabled'}">
                <a class="page-link" href="#" data-page="${data.current_page + 1}">
                {{ translate('next') }}</a>
            </li>`;

    html += '</ul>';
    return html;
}


// pagination click
$(document).on('click', '.pagination .page-link', function(e) {

    e.preventDefault();
    let page = $(this).data('page');
    if (!page) return;

    let formData = $('#filterForm').serialize() + '&page=' + page;

    $.get('{{ route("admin.warranty.claim.table.data") }}', formData, function(res) {
        refreshTable(res);
        $('#claimsTotal').text(res.total);
    });
});
</script>
@endpush