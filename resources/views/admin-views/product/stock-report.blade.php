@extends('layouts.back-end.app')

@section('title', translate('stock_report'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center gap-2 flex-wrap">
            <h2 class="h1 text-capitalize d-flex gap-2 align-items-center mb-0">
                {{ translate('stock_report') }}
            </h2>

            <a class="btn btn-outline-secondary" href="{{ url()->previous() }}">
                <i class="tio-back-ui mr-1"></i>{{ translate('back') }}
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
                                <label class="title-color">{{ translate('from') }}</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="{{ $filters['from_date'] ?? '' }}">
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="title-color">{{ translate('to') }}</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="{{ $filters['to_date'] ?? '' }}">
                            </div>
                        </div>

                        <div class="col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label class="title-color">{{ translate('category') }}</label>
                                <select class="js-select2-custom form-control" name="category_id">
                                    <option value="">{{ translate('select_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}"
                                            {{ (int)($filters['category_id'] ?? 0) === (int)$category['id'] ? 'selected' : '' }}>
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
                                        <option value="{{ $listProduct->id }}"
                                            {{ (int)($filters['product_id'] ?? 0) === (int)$listProduct->id ? 'selected' : '' }}>
                                            {{ $listProduct->name }}
                                        </option>
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
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="{{ route('admin.products.stock-report') }}" class="btn btn-secondary px-5">
                                    {{ translate('reset') }}
                                </a>
                                <button type="submit" class="btn btn--primary px-5">
                                    {{ translate('show_data') }}
                                </button>
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
    <script>
        "use strict";

        $(document).on("change", ".action-toggle-internal-transfer", function() {
            const baseUrl = $(this).data("base-url");
            const include = $(this).is(":checked") ? 1 : 0;
            window.location.href = `${baseUrl}&include_internal_transfer=${include}`;
        });
    </script>
@endpush
