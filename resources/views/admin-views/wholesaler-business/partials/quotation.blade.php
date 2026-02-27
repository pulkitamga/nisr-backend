<div class="card-body">
    <div class="px-3 py-4 light-bg">
        <div class="row g-2 align-items-center flex-grow-1">
            <div class="col-md-8">
                <h5 class="text-capitalize d-flex gap-1">
                    {{translate('Wholesaler_quotations')}}
                    <span class="badge badge-soft-dark radius-50 fz-12">{{$quotations->total()}}</span>
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
                        placeholder="{{ translate('Search...') }}" aria-label="Search">
                </div>
            </div>

        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="bg-light">
                <tr>
                    <th>{{ translate('SL') }}</th>
                    <th>{{ translate('Date') }}</th>
                    <th class="text-nowrap">{{ translate('Quotation No') }}</th>
                    <th class="text-nowrap">{{ translate('Order No') }}</th>
                    <th>{{ translate('Total') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{ translate('Action') }}</th>

                </tr>
            </thead>
            <tbody>
                @foreach($quotations as $i => $quote)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($quote->created_at)->format('d/m/Y') }}</td>
                    <td>{{ $quote->quotation_no }}</td>
                    <td>{{ $quote->purchase_order_no }}</td>
                    <td>{{ $quote->final_price }}</td>
                    <td>
                        @php
                        $statusClasses = [
                        'sent' => 'badge badge-soft-primary',
                        'accepted' => 'badge badge-soft-success',
                        'rejected' => 'badge badge-soft-danger',
                        ];
                        @endphp

                        <span class="{{ $statusClasses[$quote->status] ?? 'badge bg-secondary' }}">
                            {{ ucfirst($quote->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <a title="{{translate('View Details')}}"
                                class="btn btn-outline-info btn-sm square-btn"
                                href="{{ route('admin.wholesale.business.orders.invoice', $quote->id) }}">
                                <i class="tio-invisible"></i>
                            </a>
                            <a title="{{translate('View Details')}}"
                                class="btn btn-outline-info btn-sm square-btn"
                                href="{{ route('admin.wholesale.business.orders.invoice.edit', $quote->id) }}">
                                <i class="tio-edit"></i>
                            </a>


                            <a href="javascript:void(0);" title="{{ translate('Delete') }}"
                                class="btn btn-danger btn-sm square-btn"
                                onclick="confirmAndDelete('{{ route('admin.wholesale.business.quotation.delete', $quote->id) }}')">
                                <i class="tio-delete"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $quotations->links() }}
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
