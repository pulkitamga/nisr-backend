{{-- resources/views/admin/cms/about/index.blade.php --}}
@extends('layouts.back-end.app')

@section('title', translate('Product Page Sections'))

@section('content')


<div class="content container-fluid">

    <div class="card">
        <div class="card-header flex-wrap gap-10">
            <div class="px-sm-3 py-4 flex-grow-1">
                <div class="d-flex justify-content-between gap-3 flex-wrap align-items-center">
                    <div>
                        <h5 class="mb-0 text-capitalize gap-2">
                            {{ translate('Products') }}
                            <span class="badge badge-soft-dark radius-50 fz-12">{{ $products->count() }}</span>
                        </h5>
                    </div>
                    <div
                        class="align-items-center d-flex gap-3 justify-content-lg-end flex-wrap flex-lg-nowrap flex-grow-1">
                        {{-- Add New Button --}}
                        <div>
                            <!-- <a href="" class="btn btn--primary text-nowrap">
                                <i class="tio-add"></i>
                                <span class="text">{{ __('Add New') }}</span>
                            </a> -->
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-align-middle card-table w-100">
                    <thead class="thead-light text-capitalize">
                        <tr>
                            <th>{{ translate('sl') }}</th>
                            <th>{{ translate('Type') }}</th>
                            <th>{{ translate('Heading') }}</th>
                            <th>{{ translate('Description') }}</th>
                            <th>{{ translate('Image') }}</th>
                            <th>{{ translate('button_link') }}</th>
                            <th>{{ translate('status') }}</th>
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>

                    </thead>

                    <tbody>
                        @foreach ($products as $index => $product)

                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ translate($product->type) }}</td>
                            <td>{{ $product->getTranslatedField('heading') }}</td>
                            <td>{!! $product->getTranslatedField('description') !!}</td>
                            <td>
                                @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->heading }}"
                                    width="100" />
                                @else
                                {{ translate('no_image') }}
                                @endif
                            </td>
                            <td>{{ $product->button_link }}</td>
                            <td>
                                <label class="switcher mx-auto">
                                    <input type="checkbox" class="switcher_input status-toggle"
                                        data-id="{{ $product->id }}" {{ $product->is_active ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </td>

                            {{-- Action buttons --}}
                            <td class="text-center d-flex gap-2 mx-auto">
                                <a href="{{ route('admin.content-management.products.edit', ['id' => $product->id]) }}"
                                    class="btn btn-outline-primary btn-sm square-btn" title="{{ translate('Edit') }}">
                                    <i class="tio-edit"></i>
                                </a>

                                {{-- <form action="" method="POST"
                                    onsubmit="return confirm('{{ translate('Delete this item?') }}');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm square-btn"
                                    title="{{ translate('Delete') }}">
                                    <i class="tio-delete"></i>
                                </button>
                                </form> --}}
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



@endsection


@push('script')

<script>
    $(document).on('change', '.status-toggle', function() {
        let id = $(this).data('id');

        $.ajax({
            url: "{{ route('admin.content-management.products.status') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            },
            success: function(res) {
                toastr.success(res.message);
            },
            error: function() {
                toastr.error(@json(__('Something went wrong!')));
            }
        });
    });
</script>
@endpush

