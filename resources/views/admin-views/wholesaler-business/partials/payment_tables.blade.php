<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">{{ translate('Payment_History') }}</h4>
        <h5 class="text-danger mb-0">{{ translate('Remaining') }}: {{ webCurrencyConverter(amount:
            $remaining) }}</h5>
        <div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered m-0">
                <thead class="bg-light">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('Date') }}</th>
                        <th>{{ translate('Amount') }}</th>
                        <th>{{ translate('Method') }}</th>
                        <th>{{ translate('Reference') }}</th>
                        <th>{{ translate('Note') }}</th>
                        <th>{{ translate('Created_at') }}</th>

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
                        <td>{{ $payment->created_at->format('d M Y') }}</td>

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
            <div class="modal-dialog">
                <form action="{{ route('admin.wholesale.business.orders.payment.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPaymentModalLabel">{{ translate('Add_Payment') }}
                            </h5>
                            <button type="button"
                                class="radius-50 border-0 font-weight-bold text-black-50 position-absolute right-3 top-3 z-index-99"
                                data-bs-dismiss="modal" aria-label="Close"> <span
                                    aria-hidden="true">x</span></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">{{ translate('Remaining_Amount') }}</label>
                                <input type="text" id="remaining_before" class="form-control" value="{{ $remaining }}"
                                    readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ translate('Payment_Amount') }}</label>
                                <input type="number" step="0.01" min="0" max="{{ $remaining }}" id="payment_amount"
                                    name="amount" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ translate('Remaining_After_Payment') }}</label>
                                <input type="text" id="remaining_after" name="remaining" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ translate('Payment_Method') }}</label>
                                <select name="method" class="form-control" required>
                                    <option value="cash">{{ translate('Cash') }}</option>
                                    <option value="bank">{{ translate('Bank Transfer') }}</option>
                                    <option value="cheque">{{ translate('Cheque') }}</option>
                                    <option value="upi">{{ translate('UPI') }}</option>
                                    <option value="other">{{ translate('Other') }}</option>

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