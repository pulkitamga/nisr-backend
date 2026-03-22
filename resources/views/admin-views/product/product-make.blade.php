@extends('layouts.back-end.app')
@section('title', translate('product_make'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <h2 class="h1 mb-0">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/all-orders.png') }}" class="mb-1 me-1" alt="">
            {{ translate('product_makes') }}
        </h2>
    </div>

    <div class="card shadow rounded">
        <div class="card-header">
            <h5 class="mb-0">{{ translate('add_make') }}</h5>
        </div>
        <div class="card-body">
            <form id="makeModelForm" method="POST" action="{{ route('admin.products.make.store') }}">
                @csrf
                <input type="hidden" name="make_id" id="make_id">

                <ul class="nav nav-tabs mb-4">
                    @foreach($languages as $index => $language)
                    <li class="nav-item">
                        <a class="nav-link {{ $index === 0 ? 'active' : '' }}" data-bs-toggle="tab" href="#make-lang-{{ $language }}">
                            {{ strtoupper($language) }}
                        </a>
                    </li>
                    @endforeach
                </ul>

                <div class="tab-content">
                    @foreach($languages as $index => $language)
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="make-lang-{{ $language }}">
                        <div class="row">
                            <div class="mb-3 col-lg-6">
                                <label for="make_name_{{ $language }}" class="form-label">{{ translate('make') }} ({{ strtoupper($language) }})</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="make_name_{{ $language }}"
                                    name="name[]"
                                    {{ $language === $defaultLanguage ? 'required' : '' }}
                                >
                            </div>
                            <div class="mb-3 col-lg-6">
                                <label for="make_models_{{ $language }}" class="form-label">{{ translate('model') }} ({{ strtoupper($language) }})</label>
                                <input
                                    type="text"
                                    class="form-control vehicle-model-tags"
                                    id="make_models_{{ $language }}"
                                    name="model[]"
                                    data-role="tagsinput"
                                    {{ $language === $defaultLanguage ? 'required' : '' }}
                                >
                                <small class="text-muted">
                                    {{ $language === $defaultLanguage ? translate('press_enter_to_add_multiple_models') : translate('keep_the_same_model_order_as_the_default_language') }}
                                </small>
                            </div>
                        </div>
                        <input type="hidden" name="lang[]" value="{{ $language }}">
                    </div>
                    @endforeach
                </div>

                <div class="text-end">
                    <button type="submit" id="submitBtn" class="btn btn--primary w-10rem">{{ translate('submit') }}</button>
                </div>
            </form>
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
                                        placeholder="{{ translate('search_by_make_name') }}"
                                        value="{{ request('searchValue') }}">
                                    <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('make_name') }}</th>
                                <th class="text-center">{{ translate('make_models') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($makes as $index => $make)
                            <tr id="make-row-{{ $make->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $make->name }}</td>
                                <td class="text-center">
                                    @if($make->models->isNotEmpty())
                                    {{ $make->models->pluck('name')->join(', ') }}
                                    @else
                                    <span class="badge badge-soft-secondary">{{ translate('no_model') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-info edit-make-btn"
                                            data-id="{{ $make->id }}"
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
                                <td colspan="4" class="text-center text-muted">{{ translate('no_records_found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/tags-input.min.js') }}"></script>
<script>
    const makeLanguages = @json(array_values($languages));
    const defaultLanguage = @json($defaultLanguage);

    function resetMakeForm() {
        $('#make_id').val('');
        $('#submitBtn').text(@json(translate('submit')));

        makeLanguages.forEach(function (language) {
            $('#make_name_' + language).val('');
            $('#make_models_' + language).tagsinput('removeAll');
        });
    }

    $(document).ready(function() {
        $('.vehicle-model-tags').each(function () {
            $(this).tagsinput();
        });

        $('.edit-make-btn').on('click', function() {
            const id = $(this).data('id');

            resetMakeForm();
            $('#make_id').val(id);
            $('#submitBtn').text(@json(translate('update')));

            $.get($(this).data('url'), function(data) {
                makeLanguages.forEach(function (language) {
                    $('#make_name_' + language).val(data.names[language] || '');

                    const modelsInput = $('#make_models_' + language);
                    modelsInput.tagsinput('removeAll');

                    (data.models_by_lang[language] || []).forEach(function(modelName) {
                        if (modelName) {
                            modelsInput.tagsinput('add', modelName);
                        }
                    });
                });

                $('html, body').animate({
                    scrollTop: $("#makeModelForm").offset().top - 100
                }, 600);
            });
        });

        $('#makeModelForm').on('submit', function() {
            $('#submitBtn').text(@json(translate('submit')));
        });

        $('.delete-make-btn').on('click', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: @json(translate('are_you_sure')),
                text: @json(translate('this_action_cannot_be_undone')),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: @json(translate('yes_delete_it'))
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: @json(route('admin.products.make.destroy', ['id' => 'MAKE'])).replace('MAKE', id),
                    type: 'POST',
                    data: {
                        _token: @json(csrf_token()),
                        _method: 'DELETE'
                    },
                    success: function() {
                        $('#make-row-' + id).remove();
                        Swal.fire(@json(translate('deleted')), @json(translate('make_deleted_successfully')), 'success');
                    },
                    error: function() {
                        Swal.fire(@json(translate('failed')), @json(translate('could_not_delete_make')), 'error');
                    }
                });
            });
        });
    });
</script>
@endpush
