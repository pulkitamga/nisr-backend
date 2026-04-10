@extends('layouts.front-end.app')

@section('title', translate('Invoice') . ' - ' . translate('Order') . ' #' . $order->id)

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .collapse {
    visibility: visible !important;
}

.navbar-collapse{

    flex-grow: 0 !important;
}
</style>
<div class="max-w-4xl mx-auto bg-white p-8 shadow-md mt-8 text-sm print:text-xs print:p-4" id="invoice">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <div>
            <h2 class="text-2xl font-bold">{{ __('Quotation') }}</h2>
            <p class="text-gray-500">{{ translate('Quotation') }} #: {{ $order->id }}</p>
            <p class="text-gray-500">{{ translate('Date') }}: {!! formatDateTimeForDisplay($order->created_at, 'd M Y') !!}</p>
        </div>
        <div class="text-end">
            <h2 class="line-height-1 text-bold text-sm-left">{{ getWebConfig('company_name') }}</h2>
            <h5 class="line-height-1 font-size-16px">
                {{ translate('phone') }} : {{ getWebConfig('company_phone') }}
            </h5>
        </div>
    </div>

    <!-- Order & Customer Info -->
    <div class="mb-6 grid grid-cols-2 gap-4">
        <div>
            <h4 class="font-semibold mb-2">{{ translate('Billed To') }}:</h4>
            <p>{{ $order->wholeseller->name }}</p>
            <p>{{ $order->wholeseller->address }}</p>
            <p>{{ $order->wholeseller->email }}</p>
            <p>{{ translate('Phone') }}: {{ $order->wholeseller->phone }}</p>

        </div>
        <div>
            <h4 class="font-semibold mb-2">{{ translate('Payment Info') }}:</h4>
            <p>{{ translate('Method') }}: {{ translate(str_replace('_', ' ', (string)$order->payment_method)) }}</p>
            <p>{{ translate('Status') }}: {{ translate($order->payment_status) }}</p>
        </div>
    </div>

    <!-- Products Table -->
    <div class="overflow-x-auto">
        <table class="w-full table-auto border border-gray-200">
            <thead class="bg-gray-100 text-start">
                <tr>
                    <th class="p-2 border">#</th>
                    <th class="p-2 border">{{ __('Product') }}</th>
                    <th class="p-2 border">{{ __('Qty') }}</th>
                    <th class="p-2 border">{{ __('Price') }}</th>
                    <th class="p-2 border">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t">
                    <td class="p-2 border">1</td>
                    <td class="p-2 border">{{ $order->product->name }}</td>
                    <td class="p-2 border">{{ $order->product_quantity }}</td>
                    <td class="p-2 border">{{ setCurrencySymbol(usdToDefaultCurrency($order->base_price), getCurrencyCode()) }}</td>
                    <td class="p-2 border">{{ setCurrencySymbol(usdToDefaultCurrency($order->base_price * $order->product_quantity), getCurrencyCode()) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Charges and Discounts -->
    <div class="mt-6 w-full flex justify-end">
        <div class="w-full md:w-1/2">
            <div class="flex justify-between py-1">
                <span>{{ __('Subtotal') }}</span>
                <span>{{ setCurrencySymbol(usdToDefaultCurrency($order->base_price * $order->product_quantity), getCurrencyCode()) }}</span>
            </div>

            @foreach ($order->metas->where('type', 'charge') as $charge)
            <div class="flex justify-between py-1">
                <span>{{ $charge->key }}</span>
                <span>{{ setCurrencySymbol(usdToDefaultCurrency($charge->value), getCurrencyCode()) }}</span>
            </div>
            @endforeach

            @foreach ($order->metas->where('type', 'discount') as $discount)
            <div class="flex justify-between py-1 text-red-600">
                <span>{{ $discount->key }}</span>
                <span>-{{ setCurrencySymbol(usdToDefaultCurrency($discount->value), getCurrencyCode()) }}</span>
            </div>
            @endforeach

            <hr class="my-2">
            <div class="flex justify-between py-2 font-semibold text-lg">
                <span>{{ __('Total') }}</span>
                <span>{{ setCurrencySymbol(usdToDefaultCurrency($order->final_price), getCurrencyCode()) }}</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center text-gray-500 text-sm">
        <p>{{ translate('Thank you for your business!') }}</p>
        <p class="text-xs mt-1">{{ translate('This is an admin-generated quotation and does not require a signature.') }}</p>
    </div>
    <div class="text-center mt-4">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
            {{ translate('Make Payment') }}
        </button>
    </div>
    <div class="text-center mt-6 hidden print:hidden">
        <button onclick="window.print()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            {{ translate('Print Invoice') }}
        </button>
    </div>
</div>
@endsection
