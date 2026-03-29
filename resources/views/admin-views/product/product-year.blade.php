@extends('layouts.back-end.app')
@section('title', translate('product_year_setup'))

@push('css_or_js')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <h2 class="h1 mb-0">
            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/all-orders.png') }}" class="mb-1 me-1" alt="">
            {{ translate('product_year_setup') }}
        </h2>
    </div>

    <div class="card shadow rounded">
        <div class="card-header">
            <h5 class="mb-0">{{ translate('add_year') }}</h5>
        </div>
        <div class="card-body">
            <form id="yearForm" method="POST" action="{{ route('admin.products.year.store') }}">
                @csrf
                <input type="hidden" name="year_id" id="year_id">

                <div class="row">
                    <div class="mb-3 col-lg-4">
                        <label for="year_value" class="form-label">{{ translate('year') }}</label>
                        <input type="number" class="form-control" id="year_value" name="year" min="1900" max="{{ date('Y') + 1 }}" required>
                    </div>
                    <div class="col-lg-8">
                        @php
                    $activeLanguage = in_array(getDefaultLanguage(), $language ?? $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage;
                @endphp
                        <ul class="nav nav-tabs mb-3">
                            @foreach($languages as $index => $language)
                            <li class="nav-item">
                                <a class="nav-link {{ $index === 0 ? 'active' : '' }}" data-bs-toggle="tab" href="#year-lang-{{ $language }}">
                                    {{ strtoupper($language) }}
                                </a>
                            </li>
                            @endforeach
                        </ul>

                        <div class="tab-content">
                            @foreach($languages as $index => $language)
                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="year-lang-{{ $language }}">
                                <div class="mb-3">
                                    <label for="year_name_{{ $language }}" class="form-label">{{ translate('year') }} ({{ strtoupper($language) }})</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="year_name_{{ $language }}"
                                        name="name[]"
                                        {{ $language === $defaultLanguage ? 'required' : '' }}
                                        placeholder="{{ translate('enter_year') }}"
                                    >
                                </div>
                                <input type="hidden" name="lang[]" value="{{ $language }}">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" id="yearSubmitBtn" class="btn btn--primary w-10rem">{{ translate('submit') }}</button>
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
                                        placeholder="{{ translate('search_by_year') }}"
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
                                <th>{{ translate('year') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($years as $index => $year)
                            <tr id="year-row-{{ $year->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $year->getRawOriginal('year') }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-info edit-year-btn"
                                            data-id="{{ $year->id }}"
                                            data-url="{{ route('admin.products.year.data', $year->id) }}">
                                            <i class="tio-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-year-btn"
                                            data-id="{{ $year->id }}">
                                            <i class="tio-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">{{ translate('no_records_found') }}</td>
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
<script>
    const yearLanguages = @json(array_values($languages));

    function resetYearForm() {
        $('#year_id').val('');
        $('#year_value').val('');
        $('#yearSubmitBtn').text(@json(translate('submit')));

        yearLanguages.forEach(function (language) {
            $('#year_name_' + language).val('');
        });
    }

    $(document).ready(function () {
        $('.edit-year-btn').on('click', function () {
            resetYearForm();

            $.get($(this).data('url'), function (data) {
                $('#year_id').val(data.id);
                $('#year_value').val(data.year);
                $('#yearSubmitBtn').text(@json(translate('update')));

                yearLanguages.forEach(function (language) {
                    $('#year_name_' + language).val(data.names[language] || '');
                });

                $('html, body').animate({
                    scrollTop: $("#yearForm").offset().top - 100
                }, 600);
            });
        });

        $('.delete-year-btn').on('click', function () {
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
                    url: @json(route('admin.products.year.destroy', ['id' => 'YEAR'])).replace('YEAR', id),
                    type: 'POST',
                    data: {
                        _token: @json(csrf_token()),
                        _method: 'DELETE'
                    },
                    success: function() {
                        $('#year-row-' + id).remove();
                        Swal.fire(@json(translate('deleted')), @json(translate('year_deleted_successfully')), 'success');
                    },
                    error: function() {
                        Swal.fire(@json(translate('failed')), @json(translate('could_not_delete_year')), 'error');
                    }
                });
            });
        });
    });
</script>
@endpush
