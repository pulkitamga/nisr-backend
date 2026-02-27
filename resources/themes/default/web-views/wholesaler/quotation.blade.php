@extends('layouts.front-end.app')

@section('title', translate('my_Wholesale_Order_List'))

@section('content')
    @include('layouts.front-end.partials._store-header')
<style>
    .collapse {
    visibility: visible !important;
}

.navbar-collapse{

    flex-grow: 0 !important;
}
</style>
<div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
    <div class="row">
        @include('web-views.partials._profile-aside')



        <section class="col-12 col-lg-9 __customer-profile customer-profile-wishlist px-0 mb-4">
            <div class="card __card web-direction customer-profile-orders h-100">
                <div id="print-area" class="border">


                    <div class="card-body m-4 position-relative" style="overflow: hidden;">


                        <div style="position: relative; z-index: 1;" class=" p-4">


                            @php($totelTax=0)
                            <div class="d-flex justify-content-between align-items-baseline card-header">
                                <div>

                                    <h6 class="mb-3">{{ translate('Quotation_Details') }}</h6>
                                </div>
                                @if ($order->status === 'accepted')
                                <div>
                                    <button class="btn btn-success mx-1">{{ translate('accepted') }}</button>
                                </div>

                                @elseif($order->status === 'rejected')
                                <div>
                                    <button class="btn btn-danger mx-1">{{ translate('rejected') }}</button>
                                </div>
                                @else
                                <div>
                                    <button class="btn btn-success mx-1" onclick="confirmAction('approve')">
                                        {{ translate('Approve') }}
                                    </button>

                                    <button class="btn btn-danger mx-1" onclick="confirmAction('reject')">
                                        {{ translate('Reject') }}
                                    </button>

                                </div>

                                @endif

                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ translate('Product') }}</th>
                                            <th>{{ translate('Variation') }}</th>
                                            <th>{{ translate('Quantity') }}</th>
                                            <th>{{ translate('Price') }}</th>
                                            <th>{{ translate('Tax') }}</th>
                                            <th>{{ translate('Final Price') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $index => $item)
                                        @php($tax=0)
                                        @php($tax=$item->final_price -($item->product_quantity*$item->base_price))
                                        @php($totelTax+=$tax)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->product->name ?? 'N/A' }}</td>
                                            <td>{{ $item->product_variation_type ?? 'No Variation' }}</td>
                                            <td>{{ $item->product_quantity }}</td>
                                            <td>{{ webCurrencyConverter(amount:$item->base_price) }}</td>
                                            <td>{{ webCurrencyConverter(amount:$tax) }}</td>
                                            <td>{{ webCurrencyConverter(amount:$item->final_price) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>



                            <div class="row">
                                <div class="col-md-7">
                                    <div class="p-3">
                                        <div class="mt-4">
                                            <h4 class="font-weight-bold mb-1">{{ translate('Terms and Conditions') }}
                                            </h4>
                                            @if (!empty(strip_tags($order->terms_and_conditions)))

                                            <p>{!! $order->terms_and_conditions !!}</p>
                                            @endif

                                        </div>

                                        @if (!empty(strip_tags($order->note)))
                                        <div class="mt-4">
                                            <h4 class="font-weight-bold mb-1">{{ translate('Note') }}</h4>
                                            <p>{{ strip_tags($order->note) }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-5 ml-auto">
                                    <div class="p-3">
                                        <h6 class="mb-3 font-weight-bold text-center border py-lg-2 bg-dark text-white">
                                            {{ translate('Sub Total') }}
                                        </h6>
                                        <ul class="list-unstyled mb-3">
                                            <li class="d-flex justify-content-between mb-1">
                                                <span>Tax</span>
                                                <span>{{ webCurrencyConverter(amount:$totelTax) }}</span>
                                            </li>
                                        </ul>
                                        @if($order->metas && $order->metas->count())

                                        <ul class="list-unstyled mb-3">
                                            @foreach($order->metas as $meta)
                                            <li class="d-flex justify-content-between mb-1">
                                                <span>{{ ucwords(str_replace('_', ' ', $meta->key)) }}</span>
                                                <span>{{ webCurrencyConverter(amount:$meta->value) }}</span>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif

                                        <ul class="list-unstyled mb-3">
                                            <li class="d-flex justify-content-between mb-1">
                                                <span>Wholesaler Discount</span>
                                                <span>{{ webCurrencyConverter(amount:$order->wholesaler_discount_amount)
                                                    }}</span>
                                            </li>
                                        </ul>
                                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                            <strong>{{ translate('Total') }}</strong>
                                            <strong>{{ webCurrencyConverter(amount: $order->final_price) }}</strong>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>


                </div>
            </div>
        </section>
    </div>
</div>

<!-- External PO & Quotation Upload Modal -->
<div class="modal fade" id="externalPoModal" tabindex="-1" aria-labelledby="externalPoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="externalPoForm" method="POST" action="{{ route('wholesale.order.approve', ['id' => $order->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Provide External PO & Quotation') }}</h5>
                    <button type="button" class="radius-50 btn-close border-0" data-dismiss="modal" aria-label="Close">
                        <i class="tio-clear"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="external_po_number" class="form-label">{{ translate('External PO Number') }}</label>
                        <input type="text" class="form-control" id="external_po_number" placeholder="Ex : QUT-12345678" name="external_po_number"  required>
                    </div>
                    <div class="mb-3">
                        <label for="quotation_file" class="form-label">{{ translate('Quotation File') }}</label>
                        <input type="file" class="form-control" id="quotation_file" name="quotation_file" accept=".pdf,.doc,.docx">
                        <small class="text-muted">{{ translate('Optional: You can attach a quotation file') }}</small>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ translate('Submit & Approve') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const approveUrl = @json(route('wholesale.order.approve', ['id' => $order -> id]));
    const rejectUrl = @json(route('wholesale.order.reject', ['id' => $order -> id]));
</script>

<form id="rejectQuotationForm" action="{{ route('wholesale.order.reject', ['id' => $order->id]) }}" method="POST" class="d-none">
    @csrf
</form>


@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmAction(action) {
        if (action === 'approve') {
            var externalPoModal = new bootstrap.Modal(document.getElementById('externalPoModal'));
            externalPoModal.show();
        } else {
            // Reject button → normal confirm Swal
            const url = rejectUrl;
            Swal.fire({
                title: `Confirm Reject`,
                text: `Are you sure you want to reject this quotation?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, Reject it!`
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('rejectQuotationForm').submit();
                }
            });
        }
    }
</script>
@endpush
