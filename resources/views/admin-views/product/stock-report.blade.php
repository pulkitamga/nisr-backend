@extends('layouts.back-end.app')

@section('title', translate('stock_report'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <h2 class="h1 text-capitalize d-flex gap-2 align-items-center mb-0">
                {{ translate('stock_report') }}
            </h2>

            <a class="btn btn-outline-secondary" href="{{ url()->previous() }}">
                <i class="tio-back-ui me-1"></i>{{ translate('back') }}
            </a>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.products.stock-report') }}">
                    <div class="row gx-2">
                        <div class="col-12">
                            <h4 class="mb-3">{{ translate('filter_Products') }}</h4>
                        </div>

                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="title-color">{{ translate('date') }}</label>
                                <select class="form-control" name="date_type" id="date_type">
                                    <option value="this_year"
                                        {{ ($filters['date_type'] ?? 'this_year') === 'this_year' ? 'selected' : '' }}>
                                        {{ translate('this_Year') }}
                                    </option>
                                    <option value="this_month"
                                        {{ ($filters['date_type'] ?? '') === 'this_month' ? 'selected' : '' }}>
                                        {{ translate('this_Month') }}
                                    </option>
                                    <option value="this_week"
                                        {{ ($filters['date_type'] ?? '') === 'this_week' ? 'selected' : '' }}>
                                        {{ translate('this_Week') }}
                                    </option>
                                    <option value="today"
                                        {{ ($filters['date_type'] ?? '') === 'today' ? 'selected' : '' }}>
                                        {{ translate('today') }}
                                    </option>
                                    <option value="custom_date"
                                        {{ ($filters['date_type'] ?? '') === 'custom_date' ? 'selected' : '' }}>
                                        {{ translate('custom_Date') }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3" id="from_div">
                            <div class="form-group">
                                <label class="title-color">{{ translate('from') }}</label>
                                <input type="date" name="from" id="from_date" class="form-control"
                                    value="{{ $filters['from'] ?? '' }}">
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3" id="to_div">
                            <div class="form-group">
                                <label class="title-color">{{ translate('to') }}</label>
                                <input type="date" name="to" id="to_date" class="form-control"
                                    value="{{ $filters['to'] ?? '' }}">
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3 filter-btn">
                            <div class="form-group">
                                <label class="title-color">{{ translate('category') }}</label>
                                <select class="js-select2-custom form-control" name="category_id">
                                    <option value="">{{ translate('select_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}"
                                            {{ (int) ($filters['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' }}>
                                            {{ $category['defaultName'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="title-color">{{ translate('product') }}</label>
                                <select class="js-select2-custom form-control" name="product_id">
                                    <option value="">{{ translate('select_product') }}</option>
                                    @foreach ($productsForFilter as $listProduct)
                                        @if (!empty(trim($listProduct->name)))
                                            <option value="{{ $listProduct->id }}"
                                                {{ (int) ($filters['product_id'] ?? 0) === (int) $listProduct->id ? 'selected' : '' }}>
                                                {{ $listProduct->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if (!empty($filters['variation']))
                            <input type="hidden" name="variation" value="{{ $filters['variation'] }}">
                        @endif
                        <input type="hidden" name="include_internal_transfer"
                            value="{{ !empty($filters['include_internal_transfer']) ? 1 : 0 }}">

                        <div class="col-12">
                            <div class="d-flex gap-3 justify-content-start">

                                <!-- Filter Button -->
                                <button type="submit" class="btn btn--primary px-5">
                                    {{ translate('filters') }}
                                </button>

                                <!-- Reset Button -->
                                <a href="{{ route('admin.products.stock-report') }}" class="btn btn-secondary px-5">
                                    {{ translate('reset') }}
                                </a>

                                @if ($reportReady)
                                    <!-- Excel -->
                                    <a href="{{ route('admin.products.stock-report', array_merge(request()->query(), ['download' => 'excel'])) }}"
                                        class="btn btn-outline-success px-4">
                                        <i class="tio-download-to me-1"></i>
                                        {{ translate('excel') }}
                                    </a>

                                    <!-- PDF -->
                                    <a href="{{ route('admin.products.stock-report', array_merge(request()->query(), ['download' => 'pdf'])) }}"
                                        class="btn btn-outline-danger px-4">
                                        <i class="tio-download-to me-1"></i>
                                        {{ translate('PDF') }}
                                    </a>
                                @endif

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($reportReady)
            <div class="card">
                <div class="card-body">
                    @include('admin-views.product.partials._stock-report-content')
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-4 text-muted">
                    {{ translate('please_select_product') }}.
                </div>
            </div>
        @endif
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/product-report.js') }}"></script>
    <script>
        "use strict";

        $(document).on("change", ".action-toggle-internal-transfer", function() {
            const baseUrl = $(this).data("base-url");
            const include = $(this).is(":checked") ? 1 : 0;
            window.location.href = `${baseUrl}&include_internal_transfer=${include}`;
        });
    </script>
@endpush
