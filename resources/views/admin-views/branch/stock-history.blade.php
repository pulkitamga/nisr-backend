@extends('layouts.back-end.app')

@section('title', translate('Stock History'))

@section('content')
<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
            {{translate('Stock_Transfer_History')}}
        </h2>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3 border-right">
                    <div class="d-flex flex-column">
                        <span class="text-muted fz-12">{{ translate('Branch') }}</span>
                        <span class="h4 mb-0">{{ $branch->branch_name }}</span>
                    </div>
                </div>
                <div class="col-md-3 border-right">
                    <div class="d-flex flex-column">
                        <span class="text-muted fz-12">{{ translate('Product') }}</span>
                        <span class="h4 mb-0 text-capitalize">{{ $product->name }}</span>
                    </div>
                </div>
                <div class="col-md-3 border-right">
                    <div class="d-flex flex-column">
                        <span class="text-muted fz-12">{{ translate('Variation') }}</span>
                        <div>
                            <span class="badge badge-soft-primary">{{ $stock->variation_type ?? 'N/A' }}</span>
                            @if($stock->variation_key && $stock->variation_key !== 'No Variation')
                            <small class="text-muted">({{ str_replace(['|', ':'], [' • ', ' : '], $stock->variation_key) }})</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex flex-column">
                        <span class="text-muted fz-12">{{ translate('Current_Stock') }}</span>
                        <span class="h3 mb-0 text-primary">{{ $current_stock }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                <i class="tio-back-ui"></i> {{ translate('Back') }}
            </a>
            <a href="{{ route('admin.branch.export', ['product_id' => $product->id, 'branch_id' => $branch->id, 'variation_type' => $stock->variation_type]) }}" class="btn btn-outline--primary p-2">
                <img width="14" src="{{asset('public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                {{ translate('export') }}
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Type') }}</th>
                            <th>{{ translate('Quantity') }}</th>
                            <th>{{ translate('Reference') }}</th>
                            <th>{{ translate('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        @php
                        $logArray = is_array($log) ? $log : $log->toArray();
                        $isStockIn = ($logArray['type'] ?? '') === 'IN';
                        $typeClass = $isStockIn ? 'text-success' : 'text-danger';
                        $date = $logArray['created_at'] ?? now();
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($date)->format('Y-m-d h:i A') }}</td>
                            <td>
                                <span class="{{ $typeClass }} font-weight-bold">
                                    {{ $isStockIn ? translate('Stock In') : translate('Stock Out') }}
                                </span>
                            </td>
                            <td class="{{ $typeClass }} font-weight-bold">
                                {{ $isStockIn ? '+' : '-' }} {{ $logArray['quantity'] ?? 0 }}
                            </td>
                            <td>
                                <strong>{{ $logArray['reference'] ?? '' }}</strong><br>
                                <small class="text-muted">
                                    @if(($logArray['reference'] ?? '') === 'BRANCH TRANSFER')
                                    {{ $isStockIn ? translate('Received from') : translate('Sent to') }}
                                    {{ $logArray['from_branch'] ?? $logArray['to_branch'] ?? 'Branch' }}
                                    @else
                                    {{ $logArray['remarks'] ?? '' }}
                                    @endif
                                </small>
                            </td>
                            <td>
                                <span class="badge badge-success">
                                    {{ $logArray['status'] ?? translate('completed') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <img class="mb-3" src="{{asset('public/assets/back-end/img/empty-state.png')}}" alt="" width="100">
                                <p class="text-muted">{{ translate('No transfer history found') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection