@extends('layouts.back-end.app')
@section('title', translate('payment_view'))

@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset(path:'public/assets/back-end/css/owl.min.css') }}">
<style>
    .bidi-auto {
        unicode-bidi: plaintext;
    }
    .bidi-ltr {
        direction: ltr;
        unicode-bidi: isolate;
        display: inline-block;
        text-align: left;
    }
</style>

@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="content container-fluid">
    <div class="d-print-none pb-2">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                    <img width="20" src="{{ dynamicAsset(path:'public/assets/back-end/img/add-new-seller.png') }}"
                        alt="">
                    {{ translate('wholesaler_details') }}
                </h2>
            </div>
        </div>
    </div>

    <div class="row g-2 h-100">


        {{-- ✅ Business Info --}}

        <div class="col-xl-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-4 px-4">
                    <h4 class="mb-4 text-capitalize border-bottom pb-2">{{ translate('Company_information') }}</h4>

                    @php $business = $order->wholeseller->wholesalerBusiness; @endphp

                    <div class="row g-4">
                        {{-- Left Column --}}
                        <div class="col-md-6">
                            <div class="info-block">
                                <h6 class="text-muted mb-3">{{ translate('Business Details') }}</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><strong>{{ __('Company') }}:</strong> <span class="bidi-auto">{{ $business->company_name }}</span></li>
                                    <li class="mb-2"><strong>{{ __('Trade Name') }}:</strong> <span class="bidi-auto">{{ $business->trade_name }}</span></li>
                                    <li class="mb-2"><strong>{{ __('Registration No.') }}:</strong> <span class="bidi-ltr">{{ $business->registration_number }}</span></li>
                                    <li class="mb-2"><strong>{{ __('Tax ID') }}:</strong> <span class="bidi-ltr">{{ $business->tax_id }}</span></li>
                                    <li class="mb-2"><strong>{{ __('VAT No') }}:</strong> <span class="bidi-ltr">{{ $business->vat_number }}</span></li>
                                </ul>
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="col-md-6">
                            <div class="info-block">
                                <h6 class="text-muted mb-3">{{ translate('Wholeseller Details') }}</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><strong>{{ translate('Tier') }}:</strong> <span class="bidi-auto">{{ $order->wholeseller->tier ?? translate('no_data_found') }}</span></li>
                                    <li class="mb-2"><strong>{{ translate('Discount') }}:</strong> <span class="bidi-ltr">{{ $order->wholeseller->wholesaler_discount ?? translate('no_data_found') }}%</span></li>
                                    <li class="mb-2"><strong>{{ translate('MOQ Override') }}:</strong> <span class="bidi-auto">{{ $order->wholeseller->moq_override_enabled ? __('Yes') : __('No') }}</span></li>
                                </ul>

                                <div class="mt-3">
                                    @if($business->register_copy)
                                    <a href="{{ asset('storage/register_copies/'.$business->register_copy) }}" target="_blank" class="d-block text-decoration-none text-primary fw-semibold mb-1">
                                        📄 {{ translate('View Registration Copy') }}
                                    </a>
                                    @endif
                                    @if($business->tax_card_copy)
                                    <a href="{{ asset('storage/tax_cards/'.$business->tax_card_copy) }}" target="_blank" class="d-block text-decoration-none text-primary fw-semibold mb-1">
                                        📄 {{ translate('View Tax Card') }}
                                    </a>
                                    @endif
                                    @if($business->vat_register_copy)
                                    <a href="{{ asset('storage/vat_copies/'.$business->vat_register_copy) }}" target="_blank" class="d-block text-decoration-none text-primary fw-semibold">
                                        📄 {{ translate('View VAT Register') }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        {{-- ✅ Payment Info --}}
        <div class="col-12">
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ translate('Payment_History') }}</h4>
                    <h5 class="text-danger mb-0">{{ translate('Remaining') }}: {{ webCurrencyConverter(amount:
                        $remaining) }}</h5>
                    <div>

                        <button class="btn btn--primary btn-sm mt-2" data-bs-toggle="modal"
                            data-bs-target="#addPaymentModal">
                            {{ translate('Add_Payment') }}
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered m-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Method') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Note') }}</th>
                                    <th>{{ __('Created at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->payments as $index => $payment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $payment->date}}</td>
                                    <td>{{ webCurrencyConverter(amount: $payment->amount) }}</td>
                                    <td>{{ $payment->payment_through }}</td>
                                    <td>{{ $payment->reference }}</td>
                                    <td>{{ $payment->notes }}</td>
                                    <td><span class="bidi-ltr d-inline-block">{{ $payment->created_at->format('d M Y') }}</span></td>

                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ translate('no_payments_found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Add Payment Modal -->
                    <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog" style="max-width: 720px;">
                            <form action="{{ route('admin.wholesale.business.orders.payment.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order->id }}">
                                <div class="modal-content" style="max-height: 90vh; overflow: hidden; display: flex; flex-direction: column;">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addPaymentModalLabel">{{ translate('Add_Payment') }}
                                        </h5>
                                        <button type="button" class="close custom-close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                                                    &times;
                                        </button>
                                    </div>
                                    <div class="modal-body flex-grow-1 overflow-auto" style="padding-inline-end: 1rem;">
                                        <div class="mb-3">
                                            <label class="form-label">{{ translate('Remaining_Amount') }}</label>
                                            <input type="text" id="remaining_before" class="form-control"
                                                value="{{ $remaining }}" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ translate('Payment_Amount') }}</label>
                                            <input type="number" step="0.01" min="0" max="{{ $remaining }}"
                                                id="payment_amount" name="amount" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ translate('Remaining_After_Payment') }}</label>
                                            <input type="text" id="remaining_after" name="remaining"
                                                class="form-control" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ translate('Payment_Method') }}</label>
                                            <select name="method" class="form-control" required>
                                                <option value="cash">{{ __('Cash') }}</option>
                                                <option value="bank">{{ __('Bank Transfer') }}</option>
                                                <option value="cheque">{{ __('Cheque') }}</option>
                                                <option value="upi">{{ __('UPI') }}</option>
                                                <option value="other">{{ __('Other') }}</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ translate('Date') }}</label>
                                            <input type="date" id="date" class="form-control" name="date">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ translate('Reference') }}</label>
                                            <textarea name="reference" class="form-control" rows="2"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ translate('Note') }}</label>
                                            <textarea name="note" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn--primary">{{ translate('Save_Payment')
                                            }}</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{
                                            translate('Cancel') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/libs/bootstrap-5/bootstrap.bundle.min.js') }}"></script>
<script>
    document.getElementById('payment_amount').addEventListener('input', function() {
        let remaining = parseFloat(document.getElementById('remaining_before').value) || 0;
        let entered = parseFloat(this.value) || 0;
        let after = remaining - entered;

        document.getElementById('remaining_after').value = after.toFixed(2);
    });
</script>
@endpush
