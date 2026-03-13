@extends('layouts.back-end.app')

@section('title', translate('Stock Inventory'))

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">


@section('content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">{{ __('Pending Stock Approvals') }}</h1>
        <p class="text-gray-500 text-sm">{{ __('Approve or reject incoming stock transfers to your branch.') }}</p>
    </div>

    @if($transfers->count() > 0)
    <div class="overflow-x-auto bg-white shadow rounded-2xl">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100 text-xs text-gray-600 uppercase tracking-wide">
                <tr>
                    <th class="px-6 py-3 text-left">{{ __('Product') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('Category') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('Quantity') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('Sent From') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('Transfer Date') }}</th>
                    <th class="px-6 py-3 text-center">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach($transfers as $transfer)
                @foreach($transfer->products as $product)
                <tr class="hover:bg-gray-50 transition-all">
                    <td class="px-6 py-4 text-gray-900">{{ $product->product->name ?? __('N/A') }}</td>
                    <td class="px-6 py-4 text-gray-700">{{ $product->category->name ?? __('N/A') }}</td>
                    <td class="px-6 py-4 text-gray-800 font-semibold">{{ $product->quantity }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $transfer->fromBranch->name ?? __('Main Branch') }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($transfer->transfer_date)->format('d M Y, h:i A') }}</td>
                    <td class="px-6 py-4">
                        <div class="d-flex gap-4">
                            <form action="{{ route('admin.branch.stock.approve', ['id' => $product->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center justify-center p-2 rounded-full transition duration-150" title="{{ __('Approve') }}">
                                    <i class="bi bi-check-circle me-1"><span class="visually-hidden">{{ __('Approve') }}</span>
                                    </i>

                                </button>
                            </form>
                            <form action="{{ route('admin.branch.stock.reject', ['id' => $product->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center justify-center p-2 rounded-full transition duration-150" title="{{ __('Reject') }}">
                                    <i class="bi bi-x-circle me-1"></i> 

                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-white rounded-xl p-6 shadow-sm text-center text-gray-600 mt-6">
        <p class="text-lg">{{ __('No stock pending for approval.') }}</p>
    </div>
    @endif
</div>
@endsection
