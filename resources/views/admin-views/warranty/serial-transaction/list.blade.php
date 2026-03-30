@extends('layouts.back-end.app')
@section('title', translate('serial_transaction_history'))

@push('css_or_js')
<link href="{{ dynamicAsset('public/assets/back-end/css/daterangepicker.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="content container-fluid">
    <div>
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0">
                <img src="{{ dynamicAsset('public/assets/back-end/img/serial.png') }}" class="mb-1 me-1" alt="">
                <span class="page-header-title">{{ translate('Serial_Transaction_History') }}</span>
            </h2>
            <span class="badge badge-soft-dark radius-50 fz-14">{{ $transactions->total() }}</span>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.warranty.serial-transaction.list') }}" id="form-data" method="GET">
                    <div class="row gx-2 gy-3">
                        <div class="col-12">
                            <h4 class="mb-3 text-capitalize">{{ translate('filter_transactions') }}</h4>
                        </div>

                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <label class="title-color">{{ translate('from_branch') }}</label>
                            <select name="from_branch" class="form-control">
                                <option value="">{{ translate('all') }}</option>
                                @foreach($branches as $id => $name)
                                <option value="{{ $id }}" {{ request('from_branch') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <label class="title-color">{{ translate('to_branch') }}</label>
                            <select name="to_branch" class="form-control">
                                <option value="">{{ translate('all') }}</option>
                                @foreach($branches as $id => $name)
                                <option value="{{ $id }}" {{ request('to_branch') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <label class="title-color">{{ translate('transfer_type') }}</label>
                            <select name="transfer_type" class="form-control">
                                <option value="">{{ translate('all') }}</option>
                                @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ request('transfer_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <label class="title-color">{{ translate('date_type') }}</label>
                            <select name="date_type" class="form-control">
                                <option value="">{{ translate('select_Date_Type') }}</option>
                                <option value="this_week" {{ request('date_type') == 'this_week' ? 'selected' : '' }}>{{ translate('this_Week') }}</option>
                                <option value="this_month" {{ request('date_type') == 'this_month' ? 'selected' : '' }}>{{ translate('this_Month') }}</option>
                                <option value="this_year" {{ request('date_type') == 'this_year' ? 'selected' : '' }}>{{ translate('this_Year') }}</option>
                                <option value="custom_date" {{ request('date_type') == 'custom_date' ? 'selected' : '' }}>{{ translate('custom_Date') }}</option>
                            </select>
                        </div>

                        <div class="col-sm-6 col-lg-4 col-xl-3" id="from_div">
                            <label class="title-color">{{ translate('start_date') }}</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                        </div>

                        <div class="col-sm-6 col-lg-4 col-xl-3" id="to_div">
                            <label class="title-color">{{ translate('end_date') }}</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-3 mt-3">
                            <a href="{{ route('admin.warranty.serial-transaction.list') }}" class="btn btn-secondary px-5">{{ translate('reset') }}</a>
                            <button type="submit" class="btn btn--primary px-5">{{ translate('show_data') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <h5 class="text-capitalize">{{ translate('transaction_list') }}
                        <span class="badge badge-soft-dark radius-50 fz-12">{{ $transactions->total() }}</span>
                    </h5>
                    <form method="GET">
                        <div class="input-group input-group-custom input-group-merge">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="tio-search"></i>
                                </div>
                            </div>
                            <input type="search" name="search" class="form-control" placeholder="{{ translate('search_by_serial_no') }}" value="{{ request('search') }}">
                            <button type="submit" class="btn btn--primary input-group-text">{{ translate('search') }}</button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('serial') }}</th>
                                <th>{{ translate('from') }}</th>
                                <th>{{ translate('to') }}</th>
                                <th>{{ translate('type') }}</th>
                                <th>{{ translate('date') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $t)
                            <tr>
                                <td><strong>{{ $t->serial_number }}</strong></td>
                                <td>{{ $t->fromBranch->branch_name ?? 'N/A' }}</td>
                                <td>
                                    @if($t->to_branch_id)
                                    {{ $t->toBranch->branch_name ?? 'N/A' }}
                                    @elseif($t->distributor_id)
                                    {{ $t->distributor->company_name ?? 'N/A' }}
                                    <span class="badge badge-soft-success ms-1">{{ translate('Wholesaler') }}</span>
                                    @else
                                    N/A
                                    @endif
                                </td>

                                <td><span class="badge badge-soft-info">{{ ucwords(str_replace('_', ' ', $t->transfer_type)) }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($t->transferred_at)->format('d M Y, h:i A') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary view-history-btn"
                                        data-serial="{{ $t->serial_number }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#historyModal">
                                        <i class="tio-visible"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">{{ translate('no_transactions_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    {!! $transactions->appends(request()->query())->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="tio-history"></i> {{ translate('transaction_history_for') }}: <span id="modalSerial"></span></h5>
                <button type="button" class="btn-close border-0" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}"><i class="tio-clear"></i></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Loaded via AJAX -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ dynamicAsset('public/assets/back-end/js/moment.min.js') }}"></script>
<script src="{{ dynamicAsset('public/assets/back-end/js/daterangepicker.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.daterange-picker').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD'
            },
            autoUpdateInput: false
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $(document).on('click', '.view-history-btn', function() {
            const serial = $(this).data('serial');
            $('#modalSerial').text(serial);
            $.get("{{ route('admin.warranty.serial-transaction.history-modal', '') }}/" + serial, function(data) {
                $('#modalBody').html(data);
            });
        });
    });
</script>
@endpush

