<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <title>{{ translate('crm_report_title') }}</title>
    <style>
        body {
            direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 15px;
        }

        h1 {
            font-size: 22px;
            color: #2C3E50;
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #3498db;
        }

        h3 {
            font-size: 15px;
            color: #2C3E50;
            margin-top: 20px;
            margin-bottom: 10px;
            background: #E8F4FD;
            padding: 8px;
            border-radius: 5px;
            border-left: 5px solid #3498db;
        }

        .filter-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .filter-item {
            display: flex;
            padding: 5px 0;
        }

        .filter-label {
            font-weight: bold;
            width: 110px;
            color: #495057;
        }

        .filter-value {
            color: #2C3E50;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
            font-size: 10.5px;
        }

        th {
            background: #3498db;
            color: white;
            font-weight: bold;
            padding: 8px 5px;
            text-align: center;
            border: 1px solid #2980b9;
        }

        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            text-align: center;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        .summary-table {
            width: 70%;
            margin: 15px 0;
            border: 2px solid #3498db;
        }

        .summary-table td {
            padding: 8px;
        }

        .summary-table td:first-child {
            font-weight: bold;
            background: #f8f9fa;
            width: 40%;
        }

        .grand-total {
            background: #f1c40f;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }

        .badge-success {
            background: #2ecc71;
        }

        .badge-warning {
            background: #f39c12;
        }

        .badge-danger {
            background: #e74c3c;
        }

        .badge-info {
            background: #3498db;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 9px;
            color: #7f8c8d;
            text-align: center;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-success {
            color: #27ae60;
        }

        .text-warning {
            color: #f39c12;
        }

        .text-danger {
            color: #e74c3c;
        }

        .text-info {
            color: #3498db;
        }
    </style>
</head>

<body>

    <!-- MAIN TITLE -->
    <h1>{{ translate('crm_report_title') }}</h1>

    <!-- GENERATED INFO -->
    <div
        style="text-align: {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}; margin-bottom: 15px; font-size: 10px; color: #7f8c8d;">
        <strong>{{ translate('generated') }}:</strong>
        {{ $filters['generated_at'] ?? now()->format('d M Y h:i A') }}<br>
        <strong>{{ translate('By') }}:</strong>{{ auth('admin')->user()->name ?? 'System' }}
    </div>

    <!-- ============ FILTERS SECTION - EXACTLY LIKE EXCEL ============ -->
    <h3>{{ translate('filters_applied') }}</h3>
    <div class="filter-card">
        <div class="filter-grid">
            <div class="filter-item">
                <span class="filter-label">{{ translate('Date Range') }}:</span>
                <span class="filter-value">{{ $filters['start_date'] ?? '-' }} to
                    {{ $filters['end_date'] ?? '-' }}</span>
            </div>
            <div class="filter-item">
                <span class="filter-label">{{ translate('Department') }}</span>
                <span class="filter-value">{{ $filters['department'] ?? 'All Departments' }}</span>
            </div>
            <div class="filter-item">
                <span class="filter-label">{{ translate('Pipeline') }}</span>
                <span class="filter-value">{{ $filters['pipeline'] ?? 'All Pipelines' }}</span>
            </div>
            <div class="filter-item">
                <span class="filter-label">{{ translate('status') }}</span>
                <span class="filter-value">{{ $filters['status'] ?? 'All Status' }}</span>
            </div>
            <div class="filter-item">
                <span class="filter-label">{{ translate('Message Type') }}</span>
                <span class="filter-value">{{ $filters['message_type'] ?? 'All Types' }}</span>
            </div>
            <div class="filter-item">
                <span class="filter-label">{{ translate('report_period') }}</span>
                <span class="filter-value">{{ $filters['start_date'] ?? '-' }} to
                    {{ $filters['end_date'] ?? '-' }}</span>
            </div>
        </div>
    </div>

    <!-- ============ SUMMARY STATISTICS SECTION ============ -->
    @if (isset($filters['summary']))
        <h3>{{ translate('summary_statistics') }}</h3>
        <table class="summary-table">
            @php
                $summary = $filters['summary'];
                $total = $summary['total'] ?? 0;
                $assigned = $summary['assigned'] ?? 0;
                $pending = $summary['pending'] ?? 0;
                $converted = $summary['converted'] ?? 0;
                $ignored = $summary['ignored'] ?? 0;
                $spam = $summary['spam'] ?? 0;

                $assignedPercentage = $total > 0 ? round(($assigned / $total) * 100, 1) : 0;
                $convertedPercentage = $total > 0 ? round(($converted / $total) * 100, 1) : 0;
            @endphp
            <tr>
                <td>{{ translate('Total Messages') }}</td>
                <td>{{ number_format($total) }}</td>
                <td>100%</td>
            </tr>
            <tr>
                <td>{{ translate('Assigned') }}</td>
                <td class="text-success">{{ number_format($assigned) }}</td>
                <td><span class="badge badge-success">{{ $assignedPercentage }}%</span></td>
            </tr>
            <tr>
                <td>{{ translate('Pending') }}</td>
                <td class="text-warning">{{ number_format($pending) }}</td>
                <td><span
                        class="badge badge-warning">{{ $total > 0 ? round(($pending / $total) * 100, 1) : 0 }}%</span>
                </td>
            </tr>
            <tr>
                <td>{{ translate('converted') }}</td>
                <td class="text-info">{{ number_format($converted) }}</td>
                <td><span class="badge badge-info">{{ $convertedPercentage }}%</span></td>
            </tr>
            <tr>
                <td>{{ translate('ignored') }}</td>
                <td class="text-danger">{{ number_format($ignored) }}</td>
                <td><span class="badge badge-danger">{{ $total > 0 ? round(($ignored / $total) * 100, 1) : 0 }}%</span>
                </td>
            </tr>
            <tr>
                <td>{{ translate('Spam') }}</td>
                <td>{{ number_format($spam) }}</td>
                <td><span class="badge"
                        style="background: #95a5a6;">{{ $total > 0 ? round(($spam / $total) * 100, 1) : 0 }}%</span>
                </td>
            </tr>
            <tr style="background: #e8f4fd;">
                <td><strong>{{ translate('assignment_rate') }}</strong></td>
                <td colspan="2"><strong>{{ $assignedPercentage }}%</strong></td>
            </tr>
            <tr style="background: #e8f4fd;">
                <td><strong>{{ translate('conversion_rate') }}</strong></td>
                <td colspan="2"><strong>{{ $convertedPercentage }}%</strong></td>
            </tr>
        </table>
    @endif

    <!-- ============ DAILY BREAKDOWN SECTION ============ -->
    <h3>{{ translate('daily_breakdown') }}</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">{{ translate('date') }}</th>
                <th style="width: 8%;">{{ translate('total') }}</th>
                <th style="width: 8%;">{{ translate('Assigned') }}</th>
                <th style="width: 8%;">{{ translate('Pending') }}</th>
                <th style="width: 8%;">{{ translate('converted') }}</th>
                <th style="width: 8%;">{{ translate('ignored') }}</th>
                <th style="width: 8%;">{{ translate('Spam') }}</th>
                <th style="width: 10%;">{{ translate('assignment_rate') }}</th>
                <th style="width: 10%;">{{ translate('conversion_rate') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $key => $row)
                @php
                    $dailyTotal = $row->total ?? 0;
                    $dailyAssigned = $row->assigned ?? 0;
                    $dailyPending = $row->pending ?? 0;
                    $dailyConverted = $row->converted ?? 0;
                    $dailyIgnored = $row->ignored ?? 0;
                    $dailySpam = $row->spam ?? 0;

                    $dailyAssignPercent = $dailyTotal > 0 ? round(($dailyAssigned / $dailyTotal) * 100, 1) : 0;
                    $dailyConvertPercent = $dailyTotal > 0 ? round(($dailyConverted / $dailyTotal) * 100, 1) : 0;
                @endphp
                <tr>
                    <td><strong>{{ $key + 1 }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($row->date)->format('d / m / Y') }}</td>
                    <td><strong>{{ number_format($dailyTotal) }}</strong></td>
                    <td class="text-success">{{ number_format($dailyAssigned) }}</td>
                    <td class="text-warning">{{ number_format($dailyPending) }}</td>
                    <td class="text-info">{{ number_format($dailyConverted) }}</td>
                    <td class="text-danger">{{ number_format($dailyIgnored) }}</td>
                    <td>{{ number_format($dailySpam) }}</td>
                    <td>
                        <span
                            class="badge {{ $dailyAssignPercent >= 70 ? 'badge-success' : ($dailyAssignPercent >= 40 ? 'badge-warning' : 'badge-danger') }}">
                            {{ $dailyAssignPercent }}%
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $dailyConvertPercent }}%</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 30px; color: #7f8c8d;">
                        <strong>{{ translate('no_data') }}</strong>
                    </td>
                </tr>
            @endforelse
        </tbody>

        <!-- GRAND TOTAL ROW -->
        @if ($data->count() > 0)
            <tfoot>
                @php
                    $totalSum = $data->sum('total');
                    $assignedSum = $data->sum('assigned');
                    $convertedSum = $data->sum('converted');
                    $pendingSum = $data->sum('pending');
                    $ignoredSum = $data->sum('ignored');
                    $spamSum = $data->sum('spam');
                    $totalAssignPercent = $totalSum > 0 ? round(($assignedSum / $totalSum) * 100, 1) : 0;
                    $totalConvertPercent = $totalSum > 0 ? round(($convertedSum / $totalSum) * 100, 1) : 0;
                @endphp
                <tr style="background: #f1c40f; font-weight: bold;">
                    <td colspan="2" style="text-align: right;">{{ translate('grand_total') }}</td>
                    <td>{{ number_format($totalSum) }}</td>
                    <td>{{ number_format($assignedSum) }}</td>
                    <td>{{ number_format($pendingSum) }}</td>
                    <td>{{ number_format($convertedSum) }}</td>
                    <td>{{ number_format($ignoredSum) }}</td>
                    <td>{{ number_format($spamSum) }}</td>
                    <td>
                        <span
                            class="badge {{ $totalAssignPercent >= 70 ? 'badge-success' : ($totalAssignPercent >= 40 ? 'badge-warning' : 'badge-danger') }}">
                            {{ $totalAssignPercent }}%
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $totalConvertPercent }}%</span>
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- DETAILED MESSAGES TABLE -->
    @if (isset($detailed_data) && $detailed_data->count() > 0)
        <h3>{{ translate('detailed_messages') }}</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 12%;">{{ translate('date') }}</th>
                        <th style="width: 15%;">{{ translate('Department') }}</th>
                        <th style="width: 10%;">{{ translate('Status') }}</th>
                        <th style="width: 58%;">{{ translate('Message') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detailed_data as $key => $message)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($message->created_at)->format('d M Y H:i') }}</td>
                            <td>{{ optional($message->department)->name ?? translate('Unassigned') }}</td>
                            <td>
                                @php
                                    $statusClass = match ($message->status) {
                                        'converted' => 'badge-success',
                                        'pending' => 'badge-warning',
                                        'spam' => 'badge-danger',
                                        'ignored' => 'badge-danger',
                                        default => 'badge-info',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($message->status) }}</span>
                            </td>
                            <td style="text-align: left;">{{ Str::limit($message->message ?? '-', 100) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
    @endif

    <!-- FOOTER WITH FILTER SUMMARY -->
    <div class="footer">
        <p><strong>{{ translate('Filter Summary') }}:</strong>
            {{ translate('Date') }} {{ $filters['start_date'] ?? '-' }} to {{ $filters['end_date'] ?? '-' }} |
            {{ translate('Department') }} {{ $filters['department'] ?? translate('All') }} |
            {{ translate('Pipeline') }} {{ $filters['pipeline'] ?? translate('All') }} |
            {{ translate('Status') }} {{ $filters['status'] ?? translate('All') }} |
            {{ translate('Type') }} {{ $filters['message_type'] ?? translate('All') }}
        </p>
        <p>* {{ translate('auto_generated') }}</p>
        <p>{{ translate('generated_on') }}: {{ now()->format('d M Y h:i:s A') }}</p>

    </div>

</body>

</html>




