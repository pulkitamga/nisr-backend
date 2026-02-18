@extends('layouts.back-end.app')
@section('title', translate('product_make'))
@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

@endpush
@section('content')
<div class="content container-fluid">
    <div>
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <h2 class="h1 mb-0">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/all-orders.png')}}" class="mb-1 mr-1" alt="">
                {{translate('product_makes')}}
            </h2>
        </div>

        <div class="card shadow rounded">
            <div class="card-header">
                <h5 class="mb-0">Add Make & Model</h5>
            </div>
            <div class="card-body">
                <form id="makeModelForm" method="POST" action="{{ route('admin.products.make.store') }}">
                    @csrf
                    <input type="hidden" name="make_id" id="make_id">
                    <input type="hidden" name="_method" id="form_method" value="POST">

                    <div class="row">
                        <div class="mb-3 col-lg-6">
                            <label for="make" class="form-label">Make</label>
                            <input type="text" class="form-control" id="make" name="make" required>
                        </div>
                        <div class="mb-3 col-lg-6">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" class="form-control" id="model" name="model" data-role="tagsinput" required>
                            <small class="text-muted">Press Enter to add multiple models.</small>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" id="submitBtn" class="btn btn--primary w-10rem">{{ translate('Submit') }}</button>
                    </div>
                </form>


            </div>
        </div>
    </div>


    <div class="row mt-20">
        <div class="col-md-12">
            <div class="card">
                <div class="px-3 py-4">
                    <div class="row align-items-center">
                        <div class="col-lg-4">

                            <form action="{{ url()->current() }}" method="GET">
                                <div class="input-group input-group-custom input-group-merge">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="tio-search"></i>
                                        </div>
                                    </div>
                                    <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                        placeholder="{{ translate('search_by_Product_Name') }}"
                                        aria-label="Search orders" value="{{ request('searchValue') }}">
                                    <input type="hidden" value="{{ request('status') }}" name="status">
                                    <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

                <div class="table-responsive">
                    <table id="datatable"
                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('Make Name') }}</th>
                                <th class="text-center">{{ translate('Make models') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($makes as $index => $make)
                            <tr id="make-row-{{ $make->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $make->name }}</td>
                                <td class="text-center">
                                    @if($make->models && count($make->models))
                                    {{ $make->models->pluck('name')->join(', ') }}
                                    @else
                                    <span class="badge badge-soft-secondary">{{ translate('No Model') }}</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-info edit-make-btn"
                                            data-id="{{ $make->id }}"
                                            data-name="{{ $make->name }}"
                                            data-url="{{ route('admin.products.make.models', $make->id) }}">
                                            <i class="tio-edit"></i>
                                        </button>



                                        <button type="button" class="btn btn-sm btn-outline-danger delete-make-btn"
                                            data-id="{{ $make->id }}">
                                            <i class="tio-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">{{ translate('No records found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>


                    </table>
                </div>

            </div>
        </div>
    </div>
    <!-- 
    <div class="modal fade" id="editMakeModal" tabindex="-1" role="dialog" aria-labelledby="editMakeLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="editMakeForm">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ translate('Edit Make & Models') }}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_make_id">

                        <div class="form-group">
                            <label>{{ translate('Make Name') }}</label>
                            <input type="text" class="form-control" id="edit_make_name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label>{{ translate('Models') }}</label>
                            <input type="text" id="edit_model_input" class="form-control" placeholder="{{ translate('Type model and press Enter') }}">
                            <div class="mt-2" id="model-tags-container" style="min-height: 40px; border: 1px solid #ccc; padding: 5px; border-radius: 5px;"></div>
                            <input type="hidden" name="models" id="model_hidden_input">
                            <small class="text-muted">{{ translate('Press Enter to add multiple models') }}</small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Close') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div> -->


</div>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/tags-input.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $('.edit-make-btn').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            // Reset previous tagsinput
            $('#model').tagsinput('removeAll');

            // Set make name and id
            $('#make').val(name);
            $('#make_id').val(id);
            $('#submitBtn').text('Update');

            $.get(`/admin/products/models/${id}`, function(data) {
                if (Array.isArray(data.models)) {
                    data.models.forEach(function(model) {
                        $('#model').tagsinput('add', model);
                    });
                }
            });

            // Scroll to form
            $('html, body').animate({
                scrollTop: $("#makeModelForm").offset().top - 100
            }, 600);
        });

        // Reset form after submit
        $('#makeModelForm').on('submit', function() {
            $('#submitBtn').text('Submit');
        });

        $('.delete-make-btn').on('click', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: "{{ translate('Are you sure?') }}",
                text: "{{ translate('This action cannot be undone.') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: "{{ translate('Yes, delete it!') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.products.make.destroy', ['id' => 'MAKE']) }}".replace('MAKE', id),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function() {
                            $('#make-row-' + id).remove();
                            Swal.fire(
                                "{{ translate('Deleted!') }}",
                                "{{ translate('Make has been deleted.') }}",
                                'success'
                            );
                        },
                        error: function() {
                            Swal.fire(
                                "{{ translate('Failed!') }}",
                                "{{ translate('Could not delete make.') }}",
                                'error'
                            );
                        }
                    });
                }
            });
        });

    });
</script>
@endpush