@extends('layouts.back-end.app')

@section('title', translate('Wholesale Tiers'))

@section('content')

@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0] ?? 'en';
@endphp
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ asset('public/assets/back-end/img/add-new-seller.png') }}" alt="">
            {{ translate('Wholesale Tiers') }}
        </h2>

    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="px-3 py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ translate('Tier List') }}</h4>
                        <button class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#addTierModal">
                            <i class="tio-add"></i>
                            {{ translate('Add Tier') }}
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table
                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('Tier_ID') }}</th>
                                <th>{{ translate('Rank') }}</th>
                                <th>{{ translate('Name') }}</th>
                                <th class="text-center">{{ translate('Status') }}</th>
                                <th class="text-center">{{ translate('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tiers as $key => $tier)
                            <tr>
                                <td>{{ $tiers->firstItem() + $key }}</td>
                                <td>{{ $tier->id }}</td>
                                <td>{{ $tier->rank }}</td>
                                <td>{{ translate($tier->name) }}</td>
                                <td>
                                    <label class="switcher mx-auto">
                                        <input type="checkbox" class="switcher_input status-toggle"
                                            data-id="{{ $tier->id }}" {{ $tier->is_active ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>

                                <!-- Action Buttons -->
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-outline-info btn-sm square-btn" data-bs-toggle="modal"
                                            data-bs-target="#editTierModal-{{ $tier->id }}"
                                            title="{{ translate('Edit') }}">
                                            <i class="tio-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.wholesale.business.wholesaler.tier.delete', $tier->id) }}"
                                            method="POST" class="tier-delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger btn-sm square-btn"
                                                title="Delete"
                                                onclick="confirmTierDelete(this)">
                                                <i class="tio-delete"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editTierModal-{{ $tier->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('admin.wholesale.business.wholesaler.tier.update', $tier->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <ul class="nav nav-tabs w-fit-content mb-4">
                                                    @foreach($language as $lang)
                                                    <li class="nav-item text-capitalize">
                                                        <a class="nav-link form-lang-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                                                            id="tab-{{ $lang }}" type="button" role="tab">
                                                            {{ getLanguageName($lang) . ' (' . strtoupper($lang) . ')' }}
                                                        </a>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                                <button type="button" class="close custom-close" data-bs-dismiss="modal" aria-label="Close">
                                                    &times;
                                                </button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="form-group mb-3">
                                                    <label>{{ translate('Rank') }}</label>
                                                    <input type="number" min="1" step="1" name="rank" class="form-control"
                                                        value="{{ old('rank', $tier->rank) }}" required>
                                                </div>

                                                @foreach($language as $lang)
                                                <div class="form-lang-content {{ $lang != $defaultLanguage ? 'd-none' : '' }}"
                                                    id="content-{{ $lang }}" role="tabpanel">
                                                    <div class="form-group">
                                                        <label>{{ translate('Tier Name') }} ({{ strtoupper($lang) }})</label>
                                                        <input type="text" name="name[]" class="form-control"
                                                            value="{{ $lang == $defaultLanguage ? $tier->name : ($tier->getTranslation('name', $lang) ?? '') }}"
                                                            required>
                                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn--primary">{{ translate('Update') }}</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    {{ translate('Cancel') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    {{ translate('No Tier Available') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>


                    </table>
                </div>

                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-center justify-content-md-end">
                        {{ $tiers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Tier Modal -->
<div class="modal fade" id="addTierModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.wholesale.business.wholesaler.tier.add') }}" method="POST">
            @csrf



            <div class="modal-content">

                <div class="modal-header">

                    <ul class="nav nav-tabs w-fit-content mb-4">
                        @foreach($language as $lang)
                        <li class="nav-item text-capitalize">
                            <a class="nav-link form-system-language-tab cursor-pointer {{ $lang == $defaultLanguage ? 'active' : '' }}"
                                id="{{ $lang }}-link">
                                {{ getLanguageName($lang) . ' (' . strtoupper($lang) . ')' }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    <button type="button" class="close custom-close" data-bs-dismiss="modal" aria-label="Close">
                        &times;
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>{{ translate('Rank') }}</label>
                        <input type="number" min="1" step="1" name="rank" class="form-control"
                            value="{{ old('rank', 1) }}" required>
                    </div>

                    @foreach($language as $lang)
                    <div class="form-group {{ $lang != $defaultLanguage ? 'd-none' : '' }} form-system-language-form"
                        id="{{ $lang }}-form">
                        <label>{{ translate('Tier Name') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="name[]" class="form-control"
                            placeholder="{{ translate('Enter tier name') }}" {{ $lang==$defaultLanguage ? 'required'
                            : '' }}>
                    </div>
                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                    @endforeach

                </div>
                <div class="modal-footer">
                    <button class="btn btn--primary">{{ translate('Create') }}</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel')
                        }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.status-toggle').on('change', function() {
            var tierId = $(this).data('id');
            var isChecked = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: '{{ route("admin.wholesale.business.wholesaler.tier.status") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: tierId,
                    status: isChecked
                },
                success: function(response) {
                    toastr.success(response.message || 'Status updated successfully');
                },
                error: function() {
                    toastr.error('Something went wrong');
                }
            });
        });
    });

    function confirmTierDelete(button) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                const form = button.closest('form');
                if (form) {
                    form.submit();
                } else {
                    Swal.fire("Form not found!", "", "error");
                }
            }
        });
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modals = document.querySelectorAll('.modal');

        modals.forEach(modal => {
            const tabs = modal.querySelectorAll('.form-lang-tab');
            const contents = modal.querySelectorAll('.form-lang-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const lang = this.id.replace('tab-', '');

                    // Remove active from all tabs inside this modal
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.add('d-none'));

                    // Activate selected tab and show its content
                    this.classList.add('active');
                    const targetContent = modal.querySelector(`#content-${lang}`);
                    if (targetContent) {
                        targetContent.classList.remove('d-none');
                    }
                });
            });
        });
    });
</script>

@endpush


@endsection
