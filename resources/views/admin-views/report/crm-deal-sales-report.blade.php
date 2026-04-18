@extends('layouts.back-end.app')
@section('title', translate('crm_sales_performance_report'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .report-kpi-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 16px;
            background: #fff;
            height: 100%;
        }

        .report-kpi-title {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .report-kpi-value {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }

        .report-kpi-meta {
            font-size: 12px;
            color: #4b5563;
        }

        .report-chart-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            background: #fff;
            height: 100%;
        }
    </style>
@endpush

@section('content')
    @php($isRtl = get_direction() === 'rtl')
    @php($dateRange = $periodLabel)
    <div class="content container-fluid {{ $isRtl ? 'text-end' : 'text-start' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/customer.png') }}" alt="">
                {{ translate('crm_sales_report') }}
            </h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row g-2 align-items-start">
                        <div class="col-md-4">
                            <label class="form-label mb-1">{{ translate('Department') }}</label>
                            <select class="js-select2-custom form-control" name="department_ids[]" multiple>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ in_array($department->id, $filters['department_ids'] ?? []) ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ translate('leave_empty_for_all') }}</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1">{{ translate('Employee') }}</label>
                            <select class="js-select2-custom form-control" name="employee_ids[]" multiple>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ in_array($employee->id, $filters['employee_ids'] ?? []) ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ translate('leave_empty_for_all') }}</small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">{{ translate('date_range') }}</label>
                            <select class="form-control" name="date_type" id="crm_deal_date_type">
                                <option value="this_year"
                                    {{ ($filters['date_type'] ?? 'this_year') === 'this_year' ? 'selected' : '' }}>
                                    {{ translate('this_Year') }}</option>
                                <option value="this_month"
                                    {{ ($filters['date_type'] ?? '') === 'this_month' ? 'selected' : '' }}>
                                    {{ translate('this_Month') }}</option>
                                <option value="this_week"
                                    {{ ($filters['date_type'] ?? '') === 'this_week' ? 'selected' : '' }}>
                                    {{ translate('this_Week') }}</option>
                                <option value="today" {{ ($filters['date_type'] ?? '') === 'today' ? 'selected' : '' }}>
                                    {{ translate('today') }}</option>
                                <option value="custom_date"
                                    {{ ($filters['date_type'] ?? '') === 'custom_date' ? 'selected' : '' }}>
                                    {{ translate('custom_range') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 custom-date-range" id="crm_deal_from_div"
                            style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('From') }}</label>
                            <input type="date" class="form-control" name="from" value="{{ $filters['from'] }}">
                        </div>
                        <div class="col-md-2 custom-date-range" id="crm_deal_to_div"
                            style="{{ ($filters['date_type'] ?? 'this_year') === 'custom_date' ? '' : 'display:none;' }}">
                            <label class="form-label mb-1">{{ translate('To') }}</label>
                            <input type="date" class="form-control" name="to" value="{{ $filters['to'] }}">
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn--primary">{{ translate('Filter') }}</button>
                            <a href="{{ route('admin.report.crm-sales-performance') }}"
                                class="btn btn-outline-secondary">{{ translate('Reset') }}</a>
                            <a href="{{ route('admin.report.crm-sales-performance-export-excel', request()->query()) }}"
                                class="btn btn-outline-success">
                                <i class="tio-download-to me-1"></i> {{ translate('excel') }}
                            </a>
                            <a href="{{ route('admin.report.crm-sales-performance-export-pdf', request()->query()) }}"
                                class="btn btn-outline-danger" target="_blank" download="crm-sales-performance-report.pdf">
                                <i class="tio-download-to me-1"></i> {{ translate('PDF') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-md-4 col-lg-2">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('won_sales') }}</div>
                    <div class="report-kpi-value">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['won_sales_total']), currencyCode: getCurrencyCode()) }}
                    </div>
                    <div class="report-kpi-meta">{{ translate('won') }}: {{ $summary['won_count'] }}</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('retail_won_sales') }}</div>
                    <div class="report-kpi-value">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['retail_won_sales']), currencyCode: getCurrencyCode()) }}
                    </div>
                    <div class="report-kpi-meta">{{ translate('deals') }}: {{ $summary['retail_won_count'] }}</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('wholesale_won_sales') }}</div>
                    <div class="report-kpi-value">
                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['wholesale_won_sales']), currencyCode: getCurrencyCode()) }}
                    </div>
                    <div class="report-kpi-meta">{{ translate('deals') }}: {{ $summary['wholesale_won_count'] }}</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('won_deals') }}</div>
                    <div class="report-kpi-value">{{ $summary['won_count'] }}</div>
                    <div class="report-kpi-meta">{{ translate('Retail') }}: {{ $summary['retail_won_count'] }} |
                        {{ translate('Wholesale') }}: {{ $summary['wholesale_won_count'] }}</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('lost_deals') }}</div>
                    <div class="report-kpi-value">{{ $summary['lost_count'] }}</div>
                    <div class="report-kpi-meta">{{ translate('Retail') }}: {{ $summary['retail_lost_count'] }} |
                        {{ translate('Wholesale') }}: {{ $summary['wholesale_lost_count'] }}</div>
                </div>
            </div>
            <div class="col-md-4 col-lg-2">
                <div class="report-kpi-card">
                    <div class="report-kpi-title">{{ translate('total_deals') }}</div>
                    <div class="report-kpi-value">{{ $summary['total_deals'] }}</div>
                    <div class="report-kpi-meta">{{ translate('won') }} + {{ translate('lost') }}</div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-lg-8">
                <div class="report-chart-card">
                    <h4 class="mb-2">{{ translate('won_vs_lost_by_employee') }} ({{ $dateRange }})</h4>
                    <div id="crm-employee-status-chart"></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="report-chart-card">
                    <h4 class="mb-2">{{ translate('overall_deal_status_split') }}</h4>
                    <div id="crm-status-split-chart"></div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="report-chart-card">
                    <h4 class="mb-2">{{ translate('won_sales_retail_vs_wholesale_by_employee') }} ({{ $dateRange }})
                    </h4>
                    <div id="crm-employee-sales-type-chart"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header border-0">
                <h4 class="mb-0">{{ translate('department_and_employee_won_lost_summary') }}</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('Department') }}</th>
                            <th>{{ translate('Employee') }}</th>
                            <th class="text-end">{{ translate('retail_won_sales') }}</th>
                            <th class="text-end">{{ translate('wholesale_won_sales') }}</th>
                            <th class="text-end">{{ translate('won_sales') }}</th>
                            <th class="text-center">{{ translate('won') }}</th>
                            <th class="text-center">{{ translate('lost') }}</th>
                            <th class="text-center">{{ translate('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php($serial = 1)
                        @forelse($departmentSections as $section)
                            <tr class="table-active">
                                <td colspan="9"><strong>{{ $section->department_name }}</strong></td>
                            </tr>

                            @foreach ($section->employees as $row)
                                <tr>
                                    <td>{{ $serial++ }}</td>
                                    <td>{{ $section->department_name }}</td>
                                    <td>{{ $row->employee_name }}</td>
                                    <td class="text-end">
                                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $row->retail_won_sales), currencyCode: getCurrencyCode()) }}
                                    </td>
                                    <td class="text-end">
                                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $row->wholesale_won_sales), currencyCode: getCurrencyCode()) }}
                                    </td>
                                    <td class="text-end">
                                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $row->won_sales_total), currencyCode: getCurrencyCode()) }}
                                    </td>
                                    <td class="text-center">{{ $row->won_count }}</td>
                                    <td class="text-center">{{ $row->lost_count }}</td>
                                    <td class="text-center">{{ $row->total_deals }}</td>
                                </tr>
                            @endforeach

                            <tr class="table-light font-weight-bold">
                                <td>-</td>
                                <td colspan="2">{{ translate('department_total') }}</td>
                                <td class="text-end">
                                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $section->totals['retail_won_sales']), currencyCode: getCurrencyCode()) }}
                                </td>
                                <td class="text-end">
                                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $section->totals['wholesale_won_sales']), currencyCode: getCurrencyCode()) }}
                                </td>
                                <td class="text-end">
                                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $section->totals['won_sales_total']), currencyCode: getCurrencyCode()) }}
                                </td>
                                <td class="text-center">{{ $section->totals['won_count'] }}</td>
                                <td class="text-center">{{ $section->totals['lost_count'] }}</td>
                                <td class="text-center">{{ $section->totals['total_deals'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">{{ translate('no_Data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($departmentSections->isNotEmpty())
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td>-</td>
                                <td colspan="2">{{ translate('grand_total') }}</td>
                                <td class="text-end">
                                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['retail_won_sales']), currencyCode: getCurrencyCode()) }}
                                </td>
                                <td class="text-end">
                                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['wholesale_won_sales']), currencyCode: getCurrencyCode()) }}
                                </td>
                                <td class="text-end">
                                    {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['won_sales_total']), currencyCode: getCurrencyCode()) }}
                                </td>
                                <td class="text-center">{{ $summary['won_count'] }}</td>
                                <td class="text-center">{{ $summary['lost_count'] }}</td>
                                <td class="text-center">{{ $summary['total_deals'] }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/apexcharts.js') }}"></script>
    <script>
        (function() {
            const chartData = @json($chart);
            const wonLabel = @json(translate('won'));
            const lostLabel = @json(translate('lost'));
            const retailLabel = @json(translate('Retail'));
            const wholesaleLabel = @json(translate('Wholesale'));
            const salesLabel = @json(translate('Sales'));

            const renderChart = (selector, options) => {
                const el = document.querySelector(selector);
                if (!el) return;
                new ApexCharts(el, options).render();
            };

            renderChart("#crm-employee-status-chart", {
                chart: {
                    type: 'bar',
                    height: 340,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                        name: wonLabel,
                        data: chartData.employee_won_counts
                    },
                    {
                        name: lostLabel,
                        data: chartData.employee_lost_counts
                    }
                ],
                xaxis: {
                    categories: chartData.employee_labels,
                    labels: {
                        rotate: -30
                    }
                },
                dataLabels: {
                    enabled: false
                },
                colors: ['#22c55e', '#ef4444'],
                noData: {
                    text: @json(translate('no_Data_found'))
                }
            });

            renderChart("#crm-status-split-chart", {
                chart: {
                    type: 'donut',
                    height: 320
                },
                series: chartData.status_values,
                labels: chartData.status_labels,
                colors: ['#22c55e', '#ef4444'],
                legend: {
                    position: 'bottom'
                },
                noData: {
                    text: @json(translate('no_Data_found'))
                }
            });

            renderChart("#crm-employee-sales-type-chart", {
                chart: {
                    type: 'bar',
                    height: 360,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                        name: retailLabel + ' ' + salesLabel,
                        data: chartData.employee_retail_won_sales
                    },
                    {
                        name: wholesaleLabel + ' ' + salesLabel,
                        data: chartData.employee_wholesale_won_sales
                    }
                ],
                xaxis: {
                    categories: chartData.employee_labels,
                    labels: {
                        rotate: -30
                    }
                },
                dataLabels: {
                    enabled: false
                },
                colors: ['#0ea5e9', '#f59e0b'],
                noData: {
                    text: @json(translate('no_Data_found'))
                }
            });

            $('#crm_deal_date_type').on('change', function() {
                const isCustom = $(this).val() === 'custom_date';
                $('.custom-date-range').toggle(isCustom);
            });
        })();

        // PDF Download with chart images - FIXED version with download
        $(document).ready(function() {
            $('.btn-outline-danger[href*="export-pdf"]').on('click', function(e) {
                e.preventDefault();

                // Store reference to the clicked element
                const $button = $(this);
                let href = $button.attr('href');

                // Wait a moment for charts to render
                setTimeout(function() {
                    // Get chart elements (ApexCharts renders as SVG)
                    const employeeChartEl = document.querySelector(
                    '#crm-employee-status-chart svg');
                    const statusChartEl = document.querySelector('#crm-status-split-chart svg');
                    const salesTypeChartEl = document.querySelector(
                        '#crm-employee-sales-type-chart svg');

                    if (employeeChartEl && statusChartEl && salesTypeChartEl) {
                        try {
                            // Convert SVGs to base64 images
                            const employeeChartSVG = employeeChartEl.outerHTML;
                            const statusChartSVG = statusChartEl.outerHTML;
                            const salesTypeChartSVG = salesTypeChartEl.outerHTML;

                            const employeeImage = 'data:image/svg+xml;base64,' + btoa(unescape(
                                encodeURIComponent(employeeChartSVG)));
                            const statusImage = 'data:image/svg+xml;base64,' + btoa(unescape(
                                encodeURIComponent(statusChartSVG)));
                            const salesTypeImage = 'data:image/svg+xml;base64,' + btoa(unescape(
                                encodeURIComponent(salesTypeChartSVG)));

                            // Create a form and submit it
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = href;
                            form.target = '_blank'; // Open in new tab

                            // Add CSRF token
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = '{{ csrf_token() }}';
                            form.appendChild(csrfInput);

                            // Add chart images
                            const employeeInput = document.createElement('input');
                            employeeInput.type = 'hidden';
                            employeeInput.name = 'employee_chart';
                            employeeInput.value = employeeImage;
                            form.appendChild(employeeInput);

                            const statusInput = document.createElement('input');
                            statusInput.type = 'hidden';
                            statusInput.name = 'status_chart';
                            statusInput.value = statusImage;
                            form.appendChild(statusInput);

                            const salesTypeInput = document.createElement('input');
                            salesTypeInput.type = 'hidden';
                            salesTypeInput.name = 'retail_wholesale_chart';
                            salesTypeInput.value = salesTypeImage;
                            form.appendChild(salesTypeInput);

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
                            document.body.removeChild(form); // Clean up
                        } catch (error) {
                            console.error('Error capturing charts:', error);
                            window.open(href, '_blank');
                        }
                    } else {
                        console.log('Chart elements not found, falling back to regular link');
                        window.open(href, '_blank');
                    }
                }, 500);
            });
        });
    </script>
@endpush
