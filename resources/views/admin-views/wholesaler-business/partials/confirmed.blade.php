

        <div class="card-body">
            <div class="px-3 py-4 light-bg">
                <div class="row g-2 align-items-center flex-grow-1">
                    <div class="col-md-8">
                        <h5 class="text-capitalize d-flex gap-1">
                            {{translate('Wholesaler_Confirm_Order')}}
                            <span class="badge badge-soft-dark radius-50 fz-12">{{$confirmed->total()}}</span>
                        </h5>
                    </div>
                    <div class="col-md-4 d-flex gap-3 flex-wrap flex-sm-nowrap justify-content-end">
                        <div class="input-group input-group-custom input-group-merge">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="tio-search"></i>
                                </div>
                            </div>
                            <input id="datatableSearch_" type="search" class="form-control"
                                placeholder="{{ translate('Search...') }}" aria-label="{{ translate('Search') }}">
                        </div>
                    </div>

                </div>
            </div>
            <div class="table-responsive">



<table class="table table-hover table-bordered">
    <thead class="bg-light">
         <tr>
            <th>{{ translate('SL') }}</th>
            <th>{{ __('Date') }}</th>
            <th class="text-nowrap">{{ __('Purchase Order No') }}</th>
            <th class="text-nowrap">{{ __('Quotation No') }}</th>
            <th class="text-nowrap">{{ __('Confirm Order No') }}</th>
            <th class="text-nowrap">{{ __('Invoice No') }}</th>
            <th>{{ __('Total') }}</th>
            <th>{{ __('Status') }}</th>
            <th class="text-nowrap">{{ __('Delivery Status') }}</th>
            <th class="text-nowrap">{{ __('Payment Status') }}</th>
            <th>{{ __('Action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($confirmed as $i => $c)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td><span class="bidi-ltr d-inline-block">{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y') }}</span></td>
            <td>{{ $c->purchase_order_no }}</td>
            <td>{{ $c->quotation_no }}</td>
            <td>{{ $c->confirm_order_no ?? '' }}</td>
            <td>{{ $c->invoice_no ?? '' }}</td>
            <td>{{ $c->final_price }}</td>
           @php
    $statusClasses = [
        'confirmed' => 'badge badge-soft-success',
        'rejected' => 'badge badge-soft-danger',
        'delivered' => 'badge badge-soft-primary',
    ];

    $deliveryStatusClasses = [
        'pending' => 'badge badge-soft-warning',
        'partials' => 'badge badge-soft-info',
        'delivered' => 'badge badge-soft-success',
    ];

    $paymentStatusClasses = [
        'unpaid' => 'badge badge-soft-danger',
        'partials' => 'badge badge-soft-info',
        'paid' => 'badge badge-soft-success',
    ];
@endphp


    <td>
        <span class="{{ $statusClasses[$c->status] ?? 'badge badge-soft-secondary' }}">
            {{ ucfirst($c->status) }}
        </span>
    </td>
    <td>
        <span class="{{ $deliveryStatusClasses[$c->delivery_status] ?? 'badge badge-soft-secondary' }}">
            {{ ucfirst($c->delivery_status) }}
        </span>
    </td>
    <td>
        <span class="{{ $paymentStatusClasses[$c->payment_status] ?? 'badge badge-soft-secondary' }}">
            {{ ucfirst($c->payment_status) }}
        </span>
    </td>
 <td>
                                        <div class="d-flex align-items-center gap-2 position-relative">
                                            {{-- View Invoice --}}
                                            <a title="{{ translate('View Details') }}"
                                                class="btn btn-outline-info btn-sm square-btn"
                                                href="{{ route('admin.wholesale.business.orders.invoice', $c->id) }}">
                                                <i class="tio-invisible"></i>
                                            </a>

                                            {{-- Action Button --}}
                                            <button type="button"
                                                class="btn btn-outline-secondary btn-sm square-btn action-btn"
                                                onclick="toggleActionPopup(this)">
                                                <i class="tio-more-horizontal"></i>
                                            </button>

                                            {{-- Popup --}}
                                            <div class="action-popup shadow-sm p-2 bg-white border rounded d-none position-absolute z-3"
                                                style="top: 100%; inset-inline-start: 0; min-width: 150px;">
                                                @if (!$c->invoice_no)
                                                <a href="javascript:void(0)" class="dropdown-item text-dark py-1 px-2"
                                                    onclick="openInvoicePopup({{ $c->id }})">
                                                    <i class="tio-edit"></i> {{ translate('Invoice No') }}
                                                </a>
                                                @endif

                                                <!-- Confirm Order No Button -->
                                                @if (!$c->confirm_order_no)
                                                <a href="javascript:void(0)" class="dropdown-item text-dark py-1 px-2"
                                                    onclick="openConfirmOrderPopup({{ $c->id }})">
                                                    <i class="tio-edit"></i> {{ translate('Confirm Order No') }}
                                                </a>
                                                @endif



                                                <a class="dropdown-item text-dark py-1 px-2"
                                                    href="{{ route('admin.wholesale.business.orders.payment', $c->id) }}">
                                                    <i class="tio-wallet"></i> {{ translate('Payment') }}
                                                </a>
                                                <a class="dropdown-item text-dark py-1 px-2"
                                                    href="{{ route('admin.wholesale.business.orders.delivery', $c->id) }}">
                                                    <i class="tio-truck"></i> {{ translate('Delivery') }}
                                                </a>
                                                <a class="dropdown-item text-danger py-1 px-2"
                                                    href="javascript:void(0);"
                                                    onclick="confirmAndDelete('{{ route('admin.wholesale.business.confirem.order.delete', $c->id) }}')">
                                                    <i class="tio-delete"></i> {{ translate('Delete') }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
        </tr>
        @endforeach
    </tbody>
</table>
{{ $confirmed->links() }}

  </div>

<script>
    
 document.getElementById('datatableSearch_').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('table tbody tr');

    rows.forEach(row => {
        // Convert all text inside the row to lowercase
        const rowText = row.textContent.toLowerCase();
        if (rowText.indexOf(query) > -1) {
            row.style.display = ''; // Show row
        } else {
            row.style.display = 'none'; // Hide row
        }
    });
});
</script>
