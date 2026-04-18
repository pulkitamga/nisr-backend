@extends('layouts.back-end.app')

@section('title', translate('warranty_claims_chart'))

@push('css_or_js')
    <style>
         .claim-chart-page {
        --wr-primary: #0f4c81;
        --wr-secondary: #1d4ed8;
        --wr-soft: rgba(29, 78, 216, 0.14);
    }

    .claim-chart-page .report-hero {
        background: linear-gradient(135deg, var(--wr-primary) 0%, var(--wr-secondary) 100%);
        color: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 12px 30px rgba(30, 64, 175, 0.24);
    }
        .chart-card>div:last-child {
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
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
@php
$isRtl = session('direction') === 'rtl' || (function_exists('getWebConfig') && getWebConfig(name: 'site_direction') === 'rtl');
@endphp
@section('content')
    <div class="content container-fluid claim-chart-page {{ $isRtl ? 'text-end' : '' }}" <!-- Header -->
        <div class="report-hero mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h2 class="h1 mb-1 text-white">{{ translate('Claim Report') }}</h2>
                    <p class="mb-0 opacity-75">
                        {{ translate('claims_analytics') }}:
                      {{ $startDate->translatedFormat('M d, Y') }} - {{ $endDate->translatedFormat('M d, Y') }}
                    </p>
                </div>
                <span class="badge badge-light text-dark">
                    {{ translate('updated') }} {{ now()->translatedFormat('M d, Y h:i A') }}
                </span>
            </div>
        </div>

        <div class="filter-card">
            <form id="filterForm" method="GET" action="{{ url()->current() }}">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">{{ translate('date_range') }}</label>
                        <select class="form-control" name="date_type" id="dateTypeFilter">
                            <option value="this_year"
                                {{ ($selectedDateType ?? 'this_year') === 'this_year' ? 'selected' : '' }}>
                                {{ translate('this_Year') }}
                            </option>
                            <option value="this_month" {{ ($selectedDateType ?? '') === 'this_month' ? 'selected' : '' }}>
                                {{ translate('this_Month') }}
                            </option>
                            <option value="this_week" {{ ($selectedDateType ?? '') === 'this_week' ? 'selected' : '' }}>
                                {{ translate('this_Week') }}
                            </option>
                            <option value="today" {{ ($selectedDateType ?? '') === 'today' ? 'selected' : '' }}>
                                {{ translate('today') }}
                            </option>
                            <option value="custom_date"
                                {{ ($selectedDateType ?? '') === 'custom_date' ? 'selected' : '' }}>
                                {{ translate('custom_range') }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 custom-date-range"
                        style="{{ ($selectedDateType ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                        <label class="form-label">{{ translate('From') }}</label>
                        <input type="date" class="form-control" name="from" id="fromDateFilter"
                            value="{{ $selectedFrom ?? $startDate->toDateString() }}">
                    </div>

                    <div class="col-md-2 custom-date-range"
                        style="{{ ($selectedDateType ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                        <label class="form-label">{{ translate('To') }}</label>
                        <input type="date" class="form-control" name="to" id="toDateFilter"
                            value="{{ $selectedTo ?? $endDate->toDateString() }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">{{ translate('Branch') }}</label>
                        <select class="form-control" name="branch_id" id="branchFilter">
                            <option value="">{{ translate('all_branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">{{ translate('Status') }}</label>
                        <select class="form-control" name="status" id="statusFilter">
                            <option value="all">{{ translate('all_statuses') }}</option>
                            <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>
                                {{ translate('New') }}</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                {{ translate('Approved') }}</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                {{ translate('rejected') }}</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>
                                {{ translate('resolved') }}</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>
                                {{ translate('Closed') }}</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Search') }}</label>
                        <input type="text" name="search" id="searchInput" class="form-control"
                            placeholder="{{ translate('claim_or_serial') }}" value="{{ request('search') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Product') }}</label>
                        <select class="form-control" name="product_id" id="productFilter">
                            <option value="">{{ translate('all_Products') }}</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}"
                                    {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-12 d-flex flex-wrap gap-2 pt-2">

                        <button type="submit" class="btn btn--primary">
                            <i class="tio-filter"></i> {{ translate('Filter') }}
                        </button>

                        <a href="{{ route('admin.warranty.claim.chart') }}" class="btn btn-outline-secondary">
                            <i class="tio-refresh"></i> {{ translate('Reset') }}
                        </a>

                        <a id="exportExcelBtn"
                            href="{{ route('admin.warranty.claim.export.excel') }}?{{ http_build_query(request()->all()) }}"
                            class="btn btn-outline-success">
                            <i class="tio-download-to"></i> {{ translate('excel') }}
                        </a>

                        <a id="exportPdfBtn"
                            href="{{ route('admin.warranty.claim.export.pdf') }}?{{ http_build_query(request()->all()) }}"
                            class="btn btn-outline-danger">
                            <i class="tio-download-to"></i> PDF
                        </a>

                    </div>
                </div>
            </form>
        </div>

        <div class="row mb-4" id="summaryCards">
            <div class="col-md-2 col-sm-4">
                <div class="stat-card">
                    <div class="stat-number" id="card-total">{{ $cards['total'] }}</div>
                    <div class="stat-label">{{ translate('total_claims') }}</div>
                </div>
            </div>

            <div class="col-md-2 col-sm-4">
                <div class="stat-card">
                    <div class="stat-number" id="card-new">{{ $cards['new'] }}</div>
                    <div class="stat-label">{{ translate('New') }}</div>
                </div>
            </div>

            <div class="col-md-2 col-sm-4">
                <div class="stat-card">
                    <div class="stat-number" id="card-approved">{{ $cards['approved'] }}</div>
                    <div class="stat-label">{{ translate('Approved') }}</div>
                </div>
            </div>

            <div class="col-md-2 col-sm-4">
                <div class="stat-card">
                    <div class="stat-number" id="card-rejected">{{ $cards['rejected'] }}</div>
                    <div class="stat-label">{{ translate('rejected') }}</div>
                </div>
            </div>

            <div class="col-md-2 col-sm-4">
                <div class="stat-card">
                    <div class="stat-number" id="card-pending">{{ $cards['pending'] }}</div>
                    <div class="stat-label">{{ translate('Pending') }}</div>
                </div>
            </div>

            <div class="col-md-2 col-sm-4">
                <div class="stat-card">
                    <div class="stat-number" id="card-resolved">{{ $cards['resolved'] }}</div>
                    <div class="stat-label">{{ translate('resolved') }}</div>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">
                    {{ translate('claims') }}
                    (<span id="dateRangeText">
                        {{ $startDate->translatedFormat('d M Y') }} - {{ $endDate->translatedFormat('d M Y') }}
                    </span>)
                </h4>
                <span class="badge badge-soft-primary" id="dateRangeLabel">
                    {{ $startDate->translatedFormat('d M Y') }} - {{ $endDate->translatedFormat('d M Y') }}
                </span>
            </div>
            <div style="position: relative; height: 350px; width: 100%;">
                <canvas id="claimsChart" style="height: 100%; width: 100%;"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ translate('claims_list') }} <span class="badge badge-soft-dark"
                        id="claimsTotal">{{ $claims->total() }}</span></h5>
            </div>
            <div class="table-responsive datatable-custom">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('claim_number') }}</th>
                            <th>{{ translate('serial') }}</th>
                            <th>{{ translate('Product') }}</th>
                            <th>{{ translate('warranty_months') }}</th>
                            <th>{{ translate('warranty_end_date') }}</th>
                            <th>{{ translate('Remaining') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Customer') }}</th>
                            <th>{{ translate('Branch') }}</th>
                            <th>{{ translate('submitted_at') }}</th>
                            <th>{{ translate('sla_due') }}</th>
                            <th class="text-center">{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody id="claimTableBody">
                        @foreach ($claims as $key => $claim)
                            @php
                                $badge = match ($claim->status) {
                                    'new', 'waiting_customer', 'waiting_parts', 'waiting_payment' => 'warning',
                                    'rejected', 'closed' => 'danger',
                                    default => 'success',
                                };

                                $warranty = $claim->warranty;
                                $productName = $warranty?->product?->name ?? '-';
                                $warrantyMonths = $warranty?->warranty_months ?? '-';
                                $endDate = $warranty?->end_date ? $warranty->end_date->translatedFormat('Y-m-d') : '-';

                                // Corrected remaining calculation
                                if ($warranty && $warranty->end_date) {
                                    $now = now()->startOfDay();
                                    $end = $warranty->end_date->startOfDay();
                                    if ($now > $end) {
                                        $remaining =
                                            '<span class="badge badge-soft-danger">' . translate('expired') . '</span>';
                                    } else {
                                        $months = $now->diffInMonths($end);
                                        $days = $now->copy()->addMonths($months)->diffInDays($end);
                                        if ($months > 0 && $days > 0) {
                                            $remaining =
                                                $months .
                                                ' ' .
                                                translate('months') .
                                                ' ' .
                                                $days .
                                                ' ' .
                                                translate('Days');
                                        } elseif ($months > 0) {
                                            $remaining = $months . ' ' . translate('months');
                                        } else {
                                            $remaining = $days . ' ' . translate('Days');
                                        }
                                    }
                                } else {
                                    $remaining = '-';
                                }
                            @endphp
                            <tr>
                                <td>{{ $claims->firstItem() + $key }}</td>
                                <td>{{ $claim->claim_number }}</td>
                                <td>{{ $claim->serial_number }}</td>
                                <td>{{ $productName }}</td>
                                <td>{{ $warrantyMonths }}</td>
                                <td>{{ $endDate }}</td>
                                <td>{!! $remaining !!}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $badge }} fz-12">
                                        {{ translate($claim->status) }}
                                    </span>
                                </td>
                                <td>{{ $claim->warranty?->user?->name ?? ($claim->warranty?->activated_by_name ?? '') }}
                                </td>
                                <td>{{ $claim->branch?->branch_name ?? '-' }}</td>
                                <td><span
                                        class="bidi-ltr d-inline-block">{{ $claim->submitted_at?->translatedFormat('Y-m-d H:i A') }}</span>
                                </td>
                                <td><span
                                        class="bidi-ltr d-inline-block">{{ $claim->resolution_due?->translatedFormat('Y-m-d H:i A') ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.warranty.claim.view', $claim->id) }}"
                                        class="btn btn-sm btn-outline-info">{{ translate('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($claims->isEmpty())
                    @include('layouts.back-end._empty-state', [
                        'text' => 'no_record_found',
                        'image' => 'default',
                    ])
                @endif
            </div>
            <div class="px-4 py-3 d-flex justify-content-end" id="paginationLinks">
                {{ $claims->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let chartInstance = null;

        const initialChartData = @json($chartData);
        renderChart(initialChartData);

        $(document).ready(function() {

            // ⭐ page load par export links sync karo
            updateExportLinks();
            toggleCustomDateInputs();

            // ================================
            // FILTER SUBMIT
            // ================================
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();

                const formData = $(this).serialize();

                // ⭐ export links update karo
                updateExportLinks();

                // ===== CHART + CARDS =====
                $.get('{{ route('admin.warranty.claim.chart.data') }}', formData, function(res) {
                    $('#dateRangeText').text(res.date_range_label);
                    $('#card-total').text(res.cards.total);
                    $('#card-new').text(res.cards.new);
                    $('#card-approved').text(res.cards.approved);
                    $('#card-rejected').text(res.cards.rejected);
                    $('#card-pending').text(res.cards.pending);
                    $('#card-resolved').text(res.cards.resolved);

                    renderChart(res.chart);
                    if (res.date_range_label) {
                        $('#dateRangeLabel').text(res.date_range_label);
                    }
                });

                // ===== TABLE =====
                $.get('{{ route('admin.warranty.claim.table.data') }}', formData, function(res) {
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

            $('#dateTypeFilter').on('change', function() {
                toggleCustomDateInputs();
                if ($(this).val() !== 'custom_date') {
                    $('#filterForm').submit();
                }
            });
        });

        function toggleCustomDateInputs() {
            const isCustomRange = $('#dateTypeFilter').val() === 'custom_date';
            $('.custom-date-range').toggle(isCustomRange);
        }

        // ======================================
        // ⭐ EXPORT LINKS UPDATE FUNCTION
        // ======================================
        function updateExportLinks() {

            let params = $('#filterForm').serialize();

            let excelUrl = '{{ route('admin.warranty.claim.export.excel') }}?' + params;
            let pdfUrl = '{{ route('admin.warranty.claim.export.pdf') }}?' + params;

            $('#exportExcelBtn').attr('href', excelUrl);
            $('#exportPdfBtn').attr('href', pdfUrl);
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
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 6
                            }
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
            <td colspan="13" class="text-center py-5">
                <img src="{{ dynamicAsset('public/assets/back-end/img/empty.png') }}" width="100">
                <p class="mt-3">{{ translate('no_record_found') }}</p>
            </td>
        </tr>`);
                $('#paginationLinks').empty();
                return;
            }

            let sl = data.from || 1;

            $.each(data.data, function(index, claim) {

                let badgeClass = 'badge-soft-success';

                if (['new', 'waiting_customer', 'waiting_parts', 'waiting_payment'].includes(claim.status)) {
                    badgeClass = 'badge-soft-warning';
                } else if (['rejected', 'closed'].includes(claim.status)) {
                    badgeClass = 'badge-soft-danger';
                }

                tbody.append(`
        <tr>
            <td>${sl + index}</td>
            <td>${claim.claim_number}</td>
            <td>${claim.serial_number}</td>
            <td>${claim.product_name}</td>
            <td>${claim.warranty_months}</td>
            <td>${claim.warranty_end_date}</td>
            <td>${claim.remaining}</td>
            <td><span class="badge ${badgeClass}">${claim.status.replace(/_/g,' ')}</span></td>
            <td>${claim.customer}</td>
            <td>${claim.branch_name}</td>
            <td>${claim.submitted_at}</td>
            <td>${claim.resolution_due}</td>
            <td class="text-center">
                <a href="${claim.view_url}" class="btn btn-sm btn-outline-info">
                    {{ translate('View') }}
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
                {{ translate('Previous') }}</a>
            </li>`;

            for (let i = 1; i <= data.last_page; i++) {
                html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                 </li>`;
            }

            html += `<li class="page-item ${data.next_page_url ? '' : 'disabled'}">
                <a class="page-link" href="#" data-page="${data.current_page + 1}">
                {{ translate('Next') }}</a>
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

            $.get('{{ route('admin.warranty.claim.table.data') }}', formData, function(res) {
                refreshTable(res);
                $('#claimsTotal').text(res.total);
            });
        });
        $('#exportPdfBtn').on('click', function(e) {
            e.preventDefault();

            const chartCanvas = document.getElementById('claimsChart');
            const href = $(this).attr('href');

            if (!chartCanvas) {
                window.open(href, '_blank');
                return;
            }

            // Add small delay to ensure chart is rendered
            setTimeout(function() {
                try {
                    const chartImage = chartCanvas.toDataURL('image/png');

                    console.log('Chart image length:', chartImage.length);
                    console.log('Valid format:', chartImage.startsWith('data:image/png;base64,'));

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = href;

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    const chartInput = document.createElement('input');
                    chartInput.type = 'hidden';
                    chartInput.name = 'chart_image';
                    chartInput.value = chartImage;
                    form.appendChild(chartInput);

                    // Add all current query parameters
                    const urlParams = new URLSearchParams(window.location.search);
                    urlParams.forEach((value, key) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = value;
                        form.appendChild(input);
                    });

                    document.body.appendChild(form);
                    form.submit();

                    setTimeout(() => document.body.removeChild(form), 100);

                } catch (error) {
                    console.error('Error capturing chart:', error);
                    window.open(href, '_blank');
                }
            }, 500);
        });
    </script>
@endpush
