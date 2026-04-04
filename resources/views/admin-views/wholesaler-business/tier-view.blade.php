@extends('layouts.back-end.app')

@section('title', translate('Wholesale Tiers'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/crm.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/wholesale-list.css') }}">
@endpush

@section('content')

@php
    $language = getWebConfig(name: 'pnc_language');
    $language = is_array($language) ? $language : [];
    $defaultLanguage = $language[0] ?? config('app.locale', 'en');
    $activeLanguage = !empty($language) && in_array(getDefaultLanguage(), $language, true) ? getDefaultLanguage() : $defaultLanguage;
    $visibleTierCollection = $tiers->getCollection();
    $activeTierCount = $visibleTierCollection->where('is_active', 1)->count();
    $inactiveTierCount = $visibleTierCollection->count() - $activeTierCount;
@endphp

<div class="content container-fluid wholesale-tier-page">
    <div class="wholesale-tier-hero mb-4">
        <div class="wholesale-tier-hero__content">
            <div class="d-flex align-items-center gap-2 mb-2">
                <img src="{{ asset('public/assets/back-end/img/add-new-seller.png') }}" alt="">
                <h2 class="h1 mb-0 text-capitalize">{{ translate('Wholesale Tiers') }}</h2>
            </div>

            <div class="crm-list-toolbar__summary">
                <span class="crm-list-toolbar__chip">
                    <span class="crm-list-toolbar__chip-label">{{ translate('Tier List') }}</span>
                    <span>{{ $tiers->total() }}</span>
                </span>
                <span class="crm-list-toolbar__chip crm-list-toolbar__chip--muted">
                    <span class="crm-list-toolbar__chip-label">{{ translate('Active') }}</span>
                    <span>{{ $activeTierCount }}</span>
                </span>
                <span class="crm-list-toolbar__chip crm-list-toolbar__chip--muted">
                    <span class="crm-list-toolbar__chip-label">{{ translate('Inactive') }}</span>
                    <span>{{ $inactiveTierCount }}</span>
                </span>
            </div>
        </div>

        <div class="wholesale-tier-hero__actions">
            <button class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#addTierModal">
                <i class="tio-add"></i>
                {{ translate('Add Tier') }}
            </button>
        </div>
    </div>

    <div class="card wholesale-tier-card">
        <div class="card-header wholesale-tier-card__header">
            <div>
                <h4 class="mb-1">{{ translate('Tier List') }}</h4>
                <div class="crm-list-toolbar__summary">
                    <span class="crm-list-toolbar__chip crm-list-toolbar__chip--muted">
                        <span class="crm-list-toolbar__chip-label">{{ translate('Status') }}</span>
                        <span>{{ translate('Active') }} / {{ translate('Inactive') }}</span>
                    </span>
                </div>
            </div>
            <div class="crm-card-header__actions">
                <button class="btn btn-outline--primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addTierModal">
                    <i class="tio-add"></i>
                    <span class="ps-2">{{ translate('Add Tier') }}</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start mb-0">
                <thead class="thead-light text-capitalize">
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
                        @php
                            $localizedTierName = $tier->getTranslation('name', $activeLanguage) ?? $tier->getTranslation('name', $defaultLanguage) ?? $tier->name;
                            $defaultTierName = $tier->name;
                        @endphp
                        <tr>
                            <td>{{ $tiers->firstItem() + $key }}</td>
                            <td>
                                <span class="wholesale-tier-id">#{{ $tier->id }}</span>
                            </td>
                            <td>
                                <span class="wholesale-tier-rank">{{ $tier->rank }}</span>
                            </td>
                            <td>
                                <div class="wholesale-tier-name">
                                    <span class="wholesale-tier-name__primary bidi-auto">{{ $localizedTierName }}</span>
                                    @if($localizedTierName !== $defaultTierName)
                                        <span class="wholesale-tier-name__secondary bidi-auto">{{ $defaultTierName }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="wholesale-tier-status-cell">
                                    <span class="wholesale-status-pill {{ $tier->is_active ? 'wholesale-status-pill--success' : 'wholesale-status-pill--muted' }}">
                                        {{ $tier->is_active ? translate('Active') : translate('Inactive') }}
                                    </span>
                                    <label class="switcher mx-auto">
                                        <input type="checkbox"
                                               class="switcher_input status-toggle"
                                               data-id="{{ $tier->id }}"
                                               {{ $tier->is_active ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="crm-row-actions">
                                    <div class="crm-row-actions__primary">
                                        <button class="btn btn-outline-info btn-sm square-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editTierModal-{{ $tier->id }}"
                                                title="{{ translate('Edit') }}">
                                            <i class="tio-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.wholesale.business.wholesaler.tier.delete', $tier->id) }}"
                                              method="POST"
                                              class="tier-delete-form m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm square-btn"
                                                    title="{{ translate('Delete') }}"
                                                    onclick="confirmTierDelete(this)">
                                                <i class="tio-delete"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade wholesale-tier-modal"
                             id="editTierModal-{{ $tier->id }}"
                             tabindex="-1"
                             aria-labelledby="editTierModalLabel-{{ $tier->id }}"
                             aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <form action="{{ route('admin.wholesale.business.wholesaler.tier.update', $tier->id) }}"
                                      method="POST"
                                      class="modal-content wholesale-tier-modal__content">
                                    @csrf
                                    @method('PUT')

                                    <div class="modal-header wholesale-tier-modal__header">
                                        <div>
                                            <h5 class="modal-title mb-1" id="editTierModalLabel-{{ $tier->id }}">{{ translate('Edit') }}</h5>
                                            <div class="crm-list-toolbar__summary">
                                                <span class="crm-list-toolbar__chip crm-list-toolbar__chip--muted">
                                                    <span class="crm-list-toolbar__chip-label">{{ translate('Tier_ID') }}</span>
                                                    <span>#{{ $tier->id }}</span>
                                                </span>
                                                <span class="crm-list-toolbar__chip crm-list-toolbar__chip--muted">
                                                    <span class="crm-list-toolbar__chip-label">{{ translate('Status') }}</span>
                                                    <span>{{ $tier->is_active ? translate('Active') : translate('Inactive') }}</span>
                                                </span>
                                            </div>
                                        </div>
                                        <button type="button" class="close custom-close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                                            &times;
                                        </button>
                                    </div>

                                    <div class="modal-body wholesale-tier-modal__body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label wholesale-tier-modal__label">{{ translate('Rank') }}</label>
                                                <input type="number"
                                                       min="1"
                                                       step="1"
                                                       name="rank"
                                                       class="form-control wholesale-tier-modal__control"
                                                       value="{{ old('rank', $tier->rank) }}"
                                                       required>
                                            </div>
                                        </div>

                                        <div class="wholesale-tier-lang mt-4" data-tier-language-group>
                                            <ul class="nav nav-tabs wholesale-tier-lang__tabs" role="tablist">
                                                @foreach($language as $lang)
                                                    <li class="nav-item text-capitalize" role="presentation">
                                                        <button type="button"
                                                                class="nav-link wholesale-tier-lang__tab {{ $lang === $activeLanguage ? 'active' : '' }}"
                                                                data-tier-lang-tab="{{ $lang }}">
                                                            {{ getLanguageName($lang) . ' (' . strtoupper($lang) . ')' }}
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>

                                            <div class="wholesale-tier-lang__panes">
                                                @foreach($language as $lang)
                                                    <div class="wholesale-tier-lang__pane {{ $lang !== $activeLanguage ? 'd-none' : '' }}"
                                                         data-tier-lang-pane="{{ $lang }}">
                                                        <label class="form-label wholesale-tier-modal__label">
                                                            {{ translate('Tier Name') }} ({{ strtoupper($lang) }})
                                                        </label>
                                                        <input type="text"
                                                               name="name[]"
                                                               class="form-control wholesale-tier-modal__control"
                                                               value="{{ $lang === $defaultLanguage ? $tier->name : ($tier->getTranslation('name', $lang) ?? '') }}"
                                                               {{ $lang === $defaultLanguage ? 'required' : '' }}>
                                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer wholesale-tier-modal__footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            {{ translate('Cancel') }}
                                        </button>
                                        <button class="btn btn--primary">{{ translate('Update') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                {{ translate('No Tier Available') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 pb-4 d-flex justify-content-center justify-content-md-end">
                {{ $tiers->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade wholesale-tier-modal" id="addTierModal" tabindex="-1" aria-labelledby="addTierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('admin.wholesale.business.wholesaler.tier.add') }}"
              method="POST"
              class="modal-content wholesale-tier-modal__content">
            @csrf

            <div class="modal-header wholesale-tier-modal__header">
                <div>
                    <h5 class="modal-title mb-1" id="addTierModalLabel">{{ translate('Add Tier') }}</h5>
                    <div class="crm-list-toolbar__summary">
                        <span class="crm-list-toolbar__chip crm-list-toolbar__chip--muted">
                            <span class="crm-list-toolbar__chip-label">{{ translate('Status') }}</span>
                            <span>{{ translate('Active') }}</span>
                        </span>
                    </div>
                </div>
                <button type="button" class="close custom-close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    &times;
                </button>
            </div>

            <div class="modal-body wholesale-tier-modal__body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label wholesale-tier-modal__label">{{ translate('Rank') }}</label>
                        <input type="number"
                               min="1"
                               step="1"
                               name="rank"
                               class="form-control wholesale-tier-modal__control"
                               value="{{ old('rank', 1) }}"
                               required>
                    </div>
                </div>

                <div class="wholesale-tier-lang mt-4" data-tier-language-group>
                    <ul class="nav nav-tabs wholesale-tier-lang__tabs" role="tablist">
                        @foreach($language as $lang)
                            <li class="nav-item text-capitalize" role="presentation">
                                <button type="button"
                                        class="nav-link wholesale-tier-lang__tab {{ $lang === $activeLanguage ? 'active' : '' }}"
                                        data-tier-lang-tab="{{ $lang }}">
                                    {{ getLanguageName($lang) . ' (' . strtoupper($lang) . ')' }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="wholesale-tier-lang__panes">
                        @foreach($language as $lang)
                            <div class="wholesale-tier-lang__pane {{ $lang !== $activeLanguage ? 'd-none' : '' }}"
                                 data-tier-lang-pane="{{ $lang }}">
                                <label class="form-label wholesale-tier-modal__label">
                                    {{ translate('Tier Name') }} ({{ strtoupper($lang) }})
                                </label>
                                <input type="text"
                                       name="name[]"
                                       class="form-control wholesale-tier-modal__control"
                                       placeholder="{{ translate('Enter tier name') }}"
                                       {{ $lang === $defaultLanguage ? 'required' : '' }}>
                            </div>
                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="modal-footer wholesale-tier-modal__footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Cancel') }}</button>
                <button class="btn btn--primary">{{ translate('Create') }}</button>
            </div>
        </form>
    </div>
</div>

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.status-toggle').on('change', function() {
            const tierId = $(this).data('id');
            const isChecked = $(this).is(':checked') ? 1 : 0;

            $.ajax({
                url: '{{ route("admin.wholesale.business.wholesaler.tier.status") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: tierId,
                    status: isChecked
                },
                success: function(response) {
                    toastr.success(response.message || @json(translate('Status updated successfully')));
                },
                error: function() {
                    toastr.error(@json(translate('Something went wrong')));
                }
            });
        });
    });

    function confirmTierDelete(button) {
        Swal.fire({
            title: @json(translate('Are you sure?')),
            text: @json(translate('You won\'t be able to revert this!')),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: @json(translate('Yes, delete it!')),
            cancelButtonText: @json(translate('Cancel'))
        }).then((result) => {
            if (result.isConfirmed) {
                const form = button.closest('form');
                if (form) {
                    form.submit();
                } else {
                    Swal.fire(@json(translate('Form not found!')), "", "error");
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-tier-language-group]').forEach((group) => {
            const tabs = group.querySelectorAll('[data-tier-lang-tab]');
            const panes = group.querySelectorAll('[data-tier-lang-pane]');

            tabs.forEach((tab) => {
                tab.addEventListener('click', function() {
                    const selectedLang = this.getAttribute('data-tier-lang-tab');

                    tabs.forEach((item) => item.classList.remove('active'));
                    panes.forEach((pane) => pane.classList.add('d-none'));

                    this.classList.add('active');

                    const targetPane = group.querySelector(`[data-tier-lang-pane="${selectedLang}"]`);
                    if (targetPane) {
                        targetPane.classList.remove('d-none');
                    }
                });
            });
        });
    });
</script>
@endpush

@endsection
