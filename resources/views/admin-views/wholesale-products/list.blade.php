@extends('layouts.back-end.app')

@section('title', translate('Whole_Sellers'))

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
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
            {{translate('Wholesale_Products_List')}}
        </h2>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="px-3 py-4">
                    <div class="d-flex justify-content-between gap-10 flex-wrap align-items-center">
                        <div class="">
                            <form action="{{ url()->current() }}" method="GET">
                                <div class="input-group input-group-merge input-group-custom width-500px">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="tio-search"></i>
                                        </div>
                                    </div>
                                    <input id="datatableSearch_" type="search" name="searchValue" class="form-control" placeholder="{{translate('search_by_Product_name')}}" aria-label="{{ translate('Search orders') }}" value="{{ request('searchValue') }}">
                                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                </div>
                            </form>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <div class="dropdown">
                                <a type="button" class="btn btn-outline--primary text-nowrap btn-block" href="{{ route('admin.wholesale.product.export-excel', ['searchValue' => request('searchValue')]) }}
">
                                    <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                    <span class="ps-2">{{ translate('export') }}</span>
                                </a>
                            </div>
                            <a href="{{route('admin.wholesale.product.add')}}" type="button" class="btn btn--primary text-nowrap">
                                <i class="tio-add"></i>
                                {{translate('add_New_Product')}}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Product_name')}}</th>
                                <th>{{translate('Product_Category')}}</th>
                                <th>{{translate('product_Sub_Category')}}</th>
                                <th>{{translate('variation')}}</th>
                                <th>{{translate('Status')}}</th>
                                <th class="text-center">{{translate('action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wholesale_products as $key => $product)
                            <tr>
                                <td>{{ $wholesale_products->firstItem() + $key }}</td>
                                <td>{{ $product->product->getTranslatedField('name') ?? __('N/A') }}</td>
                                <td>{{ $product->category->getTranslatedField('name') ?? __('N/A') }}</td>
                                <td>{{ $product->subcategory->getTranslatedField('name') ?? __('N/A') }}</td>
                                <td>{{ $product->variation_type ?? __('No Variation') }}</td>

                                <td>
                                    <label class="switcher mx-auto">
                                        <input type="checkbox"
                                            class="switcher_input product-status-toggle"
                                            data-id="{{ $product->id }}"
                                            {{ $product->status ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a title="{{translate('view')}}" class="btn btn-outline-info btn-sm square-btn"
                                            href="{{ route('admin.wholesale.product.view', $product->id) }}">
                                            <i class="tio-invisible"></i>
                                        </a>

                                        <a title="{{translate('edit')}}" class="btn btn-outline--primary btn-sm square-btn"
                                            href="{{ route('admin.wholesale.product.edit', $product->id) }}">
                                            <i class="tio-edit"></i>
                                        </a>

                                        <form method="POST" action="{{ route('admin.wholesale.product.delete', $product->id) }}"
                                            class="delete-product-form d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm square-btn confirm-delete-btn"
                                                title="{{ translate('delete') }}">
                                                <i class="tio-delete"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            @endforeach
                        </tbody>

                    </table>
                </div>
                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-lg-end">
                        {!! $wholesale_products->appends(request()->query())->links() !!}
                    </div>
                </div>

                @if($wholesale_products->isEmpty())
                @include('layouts.back-end._empty-state', ['text'=>'no_record_found','image'=>'default'])
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')

<script>
    const csrfToken = @json(csrf_token());

    $(document).on('change', '.product-status-toggle', function() {
        let checkbox = $(this);
        let productId = checkbox.data('id');
        let url = "{{ route('admin.wholesale.product.toggle-status', ['id' => '__id__']) }}".replace('__id__', productId);


        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: csrfToken
            },
            success: function(response) {
                toastr.success(response.message); // Show success message
            },
            error: function() {
                toastr.error(@json(__('Something went wrong!')));
            }
        });
    });

    $(document).on('click', '.confirm-delete-btn', function(e) {
        e.preventDefault();

        let form = $(this).closest('form');

        Swal.fire({
            title: @json(__('Are you sure?')),
            text: @json(__('This action cannot be undone.')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: @json(__('Yes, delete it!'))
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

    });
</script>
@endpush
