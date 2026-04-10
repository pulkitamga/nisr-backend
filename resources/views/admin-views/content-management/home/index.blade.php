@extends('layouts.back-end.app')

@section('title', translate('home_page'))
@push('css')

@endpush
@section('content')
@php($sectionIsActive = (bool) ($currentSection?->is_active ?? false))
<style>
    .home-cms-shell .content {
        padding-bottom: 2rem;
    }

    .home-cms-shell .home-cms-nav {
        margin-bottom: 1.5rem;
    }

    .home-cms-shell .home-cms-nav__scroll {
        display: flex;
        gap: 0.75rem;
        overflow-x: auto;
        padding: 0.25rem 0.125rem 0.5rem;
        scrollbar-width: thin;
        scroll-snap-type: x proximity;
    }

    .home-cms-shell .home-cms-nav__link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 2.875rem;
        padding: 0.8rem 1.15rem;
        border: 1px solid #d9e6f3;
        border-radius: 999px;
        background: #fff;
        color: #4a617a;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
        scroll-snap-align: start;
        transition: color .2s ease, border-color .2s ease, background-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .home-cms-shell .home-cms-nav__link:hover {
        color: #1455ac;
        border-color: #9fc0ea;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .home-cms-shell .home-cms-nav__link.active {
        color: #1455ac;
        background: linear-gradient(180deg, #f7fbff 0%, #edf5ff 100%);
        border-color: #1455ac;
        box-shadow: 0 14px 28px rgba(20, 85, 172, 0.12);
    }

    .home-cms-shell .home-cms-card {
        border: 0;
        border-radius: 1.25rem;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(5, 36, 78, 0.08);
    }

    .home-cms-shell .home-cms-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        padding: 1.5rem;
        border-bottom: 1px solid #e6edf7;
        background:
            radial-gradient(circle at top right, rgba(20, 85, 172, 0.08), transparent 42%),
            linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
    }

    .home-cms-shell .home-cms-card__title {
        margin-bottom: 0.35rem;
        color: #1e3250;
    }

    .home-cms-shell .home-cms-card__meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        color: #6b7c93;
    }

    .home-cms-shell .home-cms-card__toggle {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        flex-shrink: 0;
    }

    .home-cms-shell .home-cms-card__body {
        padding: 1.5rem;
        background: #f7faff;
    }

    .home-cms-shell .home-cms-editor .nav-tabs {
        display: flex;
        gap: 0.75rem;
        border-bottom: 0;
        margin-bottom: 1.5rem !important;
        flex-wrap: wrap;
    }

    .home-cms-shell .home-cms-editor .nav-tabs .nav-link {
        margin: 0;
        border: 1px solid #dbe6f3;
        border-radius: 999px;
        background: #eef5fb;
        color: #60748a;
        font-weight: 600;
        padding: 0.7rem 1.05rem;
        transition: all .2s ease;
    }

    .home-cms-shell .home-cms-editor .nav-tabs .nav-link:hover {
        border-color: #1455ac;
        color: #1455ac;
    }

    .home-cms-shell .home-cms-editor .nav-tabs .nav-link.active {
        color: #1455ac;
        border-color: #1455ac;
        background: #fff;
        box-shadow: 0 10px 20px rgba(20, 85, 172, 0.08);
    }

    .home-cms-shell .home-cms-editor .card,
    .home-cms-shell .home-cms-editor .table-responsive {
        border: 1px solid #e2ebf4;
        border-radius: 1rem;
        box-shadow: 0 16px 32px rgba(15, 44, 84, 0.05);
        overflow: hidden;
        background: #fff;
    }

    .home-cms-shell .home-cms-editor .card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #ebf1f6;
        background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
    }

    .home-cms-shell .home-cms-editor .card-body {
        padding: 1.25rem;
    }

    .home-cms-shell .home-cms-editor .table {
        margin-bottom: 0;
        background: #fff;
    }

    .home-cms-shell .home-cms-editor .table thead th {
        background: #f7faff;
        color: #4a617a;
        border-bottom: 1px solid #e2ebf4;
        vertical-align: middle;
    }

    .home-cms-shell .home-cms-editor .table td,
    .home-cms-shell .home-cms-editor .table th {
        vertical-align: middle;
    }

    .home-cms-shell .home-cms-editor .form-control,
    .home-cms-shell .home-cms-editor .custom-select,
    .home-cms-shell .home-cms-editor textarea {
        border-radius: 0.9rem;
        border-color: #d7e3ef;
        min-height: calc(1.5em + 1rem + 2px);
        box-shadow: none;
    }

    .home-cms-shell .home-cms-editor .form-control:focus,
    .home-cms-shell .home-cms-editor .custom-select:focus,
    .home-cms-shell .home-cms-editor textarea:focus {
        border-color: #1455ac;
        box-shadow: 0 0 0 0.2rem rgba(20, 85, 172, 0.1);
    }

    .home-cms-shell .home-cms-editor .form-label,
    .home-cms-shell .home-cms-editor .title-color {
        display: inline-flex;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #1e3250;
    }

    .home-cms-shell .home-cms-editor .btn {
        border-radius: 999px;
    }

    .home-cms-shell .home-cms-editor .btn--primary,
    .home-cms-shell .home-cms-editor .btn-primary {
        box-shadow: 0 14px 28px rgba(20, 85, 172, 0.16);
    }

    .home-cms-shell .home-cms-editor .btn-secondary {
        border-color: #d4e1ec;
        background: #eef4f9;
        color: #48627f;
    }

    .home-cms-shell .home-cms-editor form > .d-flex.justify-content-end {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        padding-top: 1rem;
        margin-top: 1.5rem !important;
        border-top: 1px solid #e2ebf4;
    }

    .home-cms-shell .home-cms-editor .badge {
        border-radius: 999px;
        padding: 0.45rem 0.75rem;
    }

    .home-cms-shell .home-cms-editor .modal-content {
        border: 1px solid #e2ebf4;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(15, 44, 84, 0.12);
    }

    .home-cms-shell .home-cms-editor .modal-header,
    .home-cms-shell .home-cms-editor .modal-footer {
        border-color: #e8eef5;
    }

    .home-cms-shell .home-cms-editor .modal-header {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .cms-modal-close {
        border: 0;
        background: transparent;
        font-size: 1.5rem;
        line-height: 1;
        padding: 0.25rem 0.5rem;
        opacity: 0.75;
        cursor: pointer;
    }

    .cms-modal-close:hover {
        opacity: 1;
    }

    .cms-image-preview {
        filter: none !important;
        mix-blend-mode: normal !important;
        opacity: 1 !important;
        background-color: #fff;
        object-fit: cover;
        border: 1px solid #e3e9ef;
        border-radius: 1rem;
        width: 100%;
        max-width: 240px;
        aspect-ratio: 11 / 7;
    }

    html[dir="rtl"] .home-cms-shell .home-cms-card__title,
    html[dir="rtl"] .home-cms-shell .home-cms-card__meta,
    html[dir="rtl"] .home-cms-shell .home-cms-editor .card-header,
    html[dir="rtl"] .home-cms-shell .home-cms-editor .table td,
    html[dir="rtl"] .home-cms-shell .home-cms-editor .table th {
        text-align: right;
    }

    @media (max-width: 991.98px) {
        .home-cms-shell .home-cms-card__header {
            flex-direction: column;
            align-items: stretch;
        }

        .home-cms-shell .home-cms-card__toggle {
            justify-content: space-between;
        }
    }

    @media (max-width: 575.98px) {
        .home-cms-shell .home-cms-card__body,
        .home-cms-shell .home-cms-card__header,
        .home-cms-shell .home-cms-editor .card-body,
        .home-cms-shell .home-cms-editor .card-header {
            padding: 1rem;
        }

        .home-cms-shell .home-cms-nav__link {
            padding-inline: 0.95rem;
        }
    }
</style>
<section class="home-cms-shell">
    <div class="content container-fluid">
        <div class="home-cms-nav">
            <div class="home-cms-nav__scroll">
                @foreach($typeList as $key => $label)
                    <a href="{{ route('admin.content-management.home', ['section' => $key]) }}"
                        class="home-cms-nav__link {{ $currentType == $key ? 'active' : '' }}">
                        {{ translate($key) }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="card home-cms-card">
            <div class="card-header home-cms-card__header">
                <div>
                    <h4 class="home-cms-card__title text-capitalize">{{ translate($currentType) }}</h4>
                    <div class="home-cms-card__meta">
                        <span class="badge {{ $sectionIsActive ? 'badge-soft-success' : 'badge-soft-danger' }} js-section-status-badge">
                            {{ $sectionIsActive ? translate('active') : translate('inactive') }}
                        </span>
                        <span>{{ translate('status') }}</span>
                    </div>
                </div>

                <div class="form-group home-cms-card__toggle mb-0">
                    <label class="switcher mb-0">
                        <input type="checkbox" class="switcher_input section-toggle" data-type="{{ $currentType }}" {{
                            $sectionIsActive ? 'checked' : '' }}>
                        <span class="switcher_control"></span>
                    </label>
                </div>
            </div>

            <div class="card-body home-cms-card__body">
                <div class="home-cms-editor">
                    @if (in_array($currentType, ['main_banner']))
                        @include('admin-views.content-management.home.partials.main-banner')
                    @endif

                    @if (in_array($currentType, ['client_review']))
                        @include('admin-views.content-management.home.partials.clint-review')
                    @endif

                    @if (in_array($currentType, ['categories']))
                        @include('admin-views.content-management.home.partials.deals')
                    @endif

                    @if (in_array($currentType, ['find_perfect_match']))
                        @include('admin-views.content-management.home.partials.find-match')
                    @endif

                    @if (in_array($currentType, ['flagship_battery_families']))
                        @include('admin-views.content-management.home.partials.flagship-battery-families')
                    @endif

                    @if (in_array($currentType, ['core_capabilities']))
                        @include('admin-views.content-management.home.partials.core-capabilities')
                    @endif

                    @if (in_array($currentType, ['closed_loop_lifecycle']))
                        @include('admin-views.content-management.home.partials.closed-loop-lifecycle')
                    @endif

                    @if (in_array($currentType, ['next_step']))
                        @include('admin-views.content-management.home.partials.next-step')
                    @endif

                    @if (in_array($currentType, ['download_app']))
                        @include('admin-views.content-management.home.partials.download-mobile')
                    @endif

                    @if (in_array($currentType, ['products']))
                        @include('admin-views.content-management.home.partials.products')
                    @endif

                    @if (in_array($currentType, ['blog']))
                        @include('admin-views.content-management.home.partials.blog')
                    @endif

                    @if (in_array($currentType, ['trusted_by']))
                        @include('admin-views.content-management.home.partials.trusted-by')
                    @endif

                    @if (in_array($currentType, ['wholesaler_section']))
                        @include('admin-views.content-management.home.partials.wholesaler')
                    @endif

                    @if (in_array($currentType, ['why_choose_us']))
                        @include('admin-views.content-management.home.partials.why-choose-us')
                    @endif

                    @if (in_array($currentType, ['why_join_us']))
                        @include('admin-views.content-management.home.partials.why-join-us')
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/sweet_alert.js') }}"></script>

<script>
    $(document).on('click', '.form-system-language-tab', function (e) {
        e.preventDefault();

        var $tab = $(this);
        var lang = $tab.data('language') || (($tab.attr('id') || '').replace('-link', ''));
        var $scope = $tab.closest('.modal-content');

        if (!$scope.length) {
            $scope = $tab.closest('form');
        }

        if (!$scope.length) {
            $scope = $tab.closest('.home-cms-editor');
        }

        $tab.closest('.nav-tabs').find('.form-system-language-tab').removeClass('active');
        $scope.find('.form-system-language-form').addClass('d-none');
        $tab.addClass('active');

        var $target = $scope.find('.form-system-language-form[data-language="' + lang + '"]').first();
        if (!$target.length) {
            $target = $scope.find('.form-system-language-form[id="' + lang + '-form"]').first();
        }

        $target.removeClass('d-none');
    });
</script>

<script>
    $(document).ready(function () {
        const activeStatusLabel = @json(translate('active'));
        const inactiveStatusLabel = @json(translate('inactive'));

        const syncSectionStatusBadge = function (isActive) {
            const $badge = $('.js-section-status-badge');

            $badge
                .text(isActive ? activeStatusLabel : inactiveStatusLabel)
                .toggleClass('badge-soft-success', isActive)
                .toggleClass('badge-soft-danger', !isActive);
        };

        $(document).on('click', '.cms-modal-close, [data-dismiss="modal"], [data-bs-dismiss="modal"]', function () {
            const modalElement = this.closest('.modal');
            if (!modalElement) {
                return;
            }

            if (window.bootstrap && window.bootstrap.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                return;
            }

            if (window.jQuery && typeof $(modalElement).modal === 'function') {
                $(modalElement).modal('hide');
            }
        });

        $(document).on('change', '.section-toggle', function () {
            let $toggle = $(this);
            let type = $toggle.data('type');
            let isChecked = $toggle.is(':checked');
            let status = isChecked ? 1 : 0;

            $toggle.prop('disabled', true);
            syncSectionStatusBadge(isChecked);

            $.ajax({
                url: "{{ route('admin.content-management.section.toggle-status') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    type: type,
                    status: status
                },
                success: function () {
                    toastr.success(@json(__('Status updated successfully')));
                },
                error: function () {
                    $toggle.prop('checked', !isChecked);
                    syncSectionStatusBadge(!isChecked);
                    toastr.error(@json(__('Failed to update status')));
                },
                complete: function () {
                    $toggle.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush
