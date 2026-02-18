<!DOCTYPE html>
<html>
<head>
    <title>Branch Stock Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        .filter-info {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filter-info table {
            width: 100%;
        }
        .filter-info td {
            padding: 5px;
        }
        .filter-info .label {
            font-weight: bold;
            width: 150px;
        }
        .chart-container {
            text-align: center;
            margin: 20px 0;
            page-break-inside: avoid;
        }
        .chart-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .chart-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #4e73df;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-row {
            font-weight: bold;
            background-color: #e9ecef !important;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        /* New styles for better layout */
        .content-wrapper {
            margin-bottom: 20px;
        }
        .data-section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Branch Stock Report</h1>
        <div class="subtitle">Generated on: {{ $exportDate }}</div>
    </div>

    <div class="filter-info">
        <table>
            <tr>
                <td class="label">Date Range:</td>
                <td>{{ $dateRange }}</td>
            </tr>
            @if(isset($product))
            <tr>
                <td class="label">Product:</td>
                <td>{{ $product->name }}</td>
            </tr>
            @endif
            @if(!empty($filters['variation_type']))
            <tr>
                <td class="label">Variation:</td>
                <td>{{ $filters['variation_type'] }}</td>
            </tr>
            @endif
            @if(!empty($filters['branch_id']))
            <tr>
                @php
                    $branch = \App\Models\Branch::find($filters['branch_id']);
                @endphp
                <td class="label">Branch:</td>
                <td>{{ $branch ? $branch->branch_name : 'Selected Branch' }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if(!empty($branches) && count($branches) > 0)
        <div class="content-wrapper">
            <!-- Stock Details Section -->
            <div class="data-section">
                <div class="section-title">Stock Details</div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Branch Name</th>
                            <th>Stock Quantity</th>
                            @if(isset($product))
                            <th>Product</th>
                            @endif
                            @if(!empty($filters['variation_type']))
                            <th>Variation</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalStock = 0; @endphp
                        @foreach($branches as $index => $branch)
                            @php $totalStock += $branch['current_stock']; @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $branch['branch_name'] }}</td>
                                <td>{{ number_format($branch['current_stock']) ?? 0 }}</td>
                                @if(isset($product))
                                <td>{{ $product->name }}</td>
                                @endif
                                @if(!empty($filters['variation_type']))
                                <td>{{ $filters['variation_type'] }}</td>
                                @endif
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2"><strong>Total</strong></td>
                            <td><strong>{{ number_format($totalStock) }}</strong></td>
                            @if(isset($product))
                            <td></td>
                            @endif
                            @if(!empty($filters['variation_type']))
                            <td></td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Chart Section - AFTER THE TABLE -->
            @if($hasChart)
                <div class="section-title">Stock Visualization</div>
                <div class="chart-container">
                    <div class="chart-title">Branch Stock Distribution Chart</div>
                    <img src="{{ $chartImage }}" alt="Stock Chart" />
                    <p style="font-size: 11px; color: #666; margin-top: 5px;">
                        Chart shows stock distribution across branches. Higher bars indicate more stock.
                    </p>
                </div>
            @endif
        </div>
    @else
        <div class="no-data">
            <h3>No Stock Data Found</h3>
            <p>No stock records match the selected filters.</p>
        </div>
    @endif

    <div class="footer">
        <p>Generated by Sales Central System | Page 1 of 1</p>
    </div>
</body>
</html>