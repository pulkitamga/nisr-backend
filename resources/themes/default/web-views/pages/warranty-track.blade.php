@extends('layouts.front-end.app')
@section('title', translate('Warranty_services'))

@push('css_or_js')
    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}" />
    <meta property="og:title" content="{{ $web_config['company_name'] }}" />
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}" />
    <meta property="twitter:title" content="{{ $web_config['company_name'] }}" />
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">
    @include('web-views.partials._premium-page-styles')
    <style>
        .nisr-warranty-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(18rem, .85fr);
            gap: clamp(1rem, 2vw, 1.5rem);
            align-items: start;
        }

        .nisr-warranty-actions {
            display: grid;
            gap: 1rem;
        }

        .nisr-warranty-card {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            gap: 1rem;
            align-items: start;
            padding: 1.15rem;
            border: 1px solid rgba(16, 47, 58, 0.1);
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 1rem 2.4rem rgba(16, 56, 62, 0.08);
        }

        .nisr-warranty-card__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            inline-size: 3rem;
            block-size: 3rem;
            border-radius: 1rem;
            background: rgba(var(--bs-base-rgb), 0.12);
            color: var(--nisr-accent);
            flex: 0 0 auto;
        }

        .nisr-warranty-card__icon svg {
            inline-size: 1.45rem;
            block-size: 1.45rem;
        }

        .nisr-warranty-card__title {
            margin: 0;
            color: var(--nisr-ink);
            font-size: 1.12rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .nisr-warranty-card__copy {
            margin: .35rem 0 0;
            color: var(--nisr-muted);
            line-height: 1.7;
        }

        .nisr-warranty-card__action {
            min-inline-size: 10.5rem;
        }

        .nisr-warranty-card__action .btn {
            inline-size: 100%;
            white-space: nowrap;
        }

        @media (max-width: 991.98px) {
            .nisr-warranty-grid {
                grid-template-columns: 1fr;
            }

            .nisr-warranty-card {
                grid-template-columns: auto minmax(0, 1fr);
            }

            .nisr-warranty-card__action {
                grid-column: 1 / -1;
                min-inline-size: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .nisr-warranty-card {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="nisr-page-shell">
        <section class="container rtl text-align-direction">
            <div class="nisr-page-hero">
                <span class="nisr-page-eyebrow">{{ translate('warranty') }}</span>
                <h1 class="nisr-page-title">{{ translate('Warranty_services') }}</h1>
                <p class="nisr-page-lead">
                    {{ translate('Choose_the_right_path_to_activate_a_new_warranty_review_an_existing_record_or_read_coverage_terms') }}
                </p>
                <div class="nisr-hero-actions">
                    <span class="nisr-stat-pill">{{ translate('Three_warranty_paths_available') }}</span>
                    <a href="{{ route('contacts') }}" class="nisr-link-pill">{{ translate('Contact_us') }}</a>
                </div>
            </div>
        </section>

        <section class="container pb-5 rtl text-align-direction">
            <div class="nisr-warranty-grid">
                <div class="nisr-surface">
                    <div class="nisr-surface-head">
                        <h2 class="nisr-section-title">{{ translate('Choose_your_next_step') }}</h2>
                        <p class="nisr-section-copy mb-0">{{ translate('Start_with_the_option_that_matches_your_current_need') }}</p>
                    </div>

                    <div class="nisr-warranty-actions">
                        <article class="nisr-warranty-card">
                            <div class="nisr-warranty-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 4v16M4 12h16"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="nisr-warranty-card__title">{{ translate('Activate Warranty') }}</h3>
                                <p class="nisr-warranty-card__copy">{{ translate('Register_your_purchase_to_start_the_official_warranty_record') }}</p>
                            </div>
                            <div class="nisr-warranty-card__action">
                                <a href="{{ route('warranty.activate') }}" class="btn btn--primary">{{ translate('Activate Warranty') }}</a>
                            </div>
                        </article>

                        <article class="nisr-warranty-card">
                            <div class="nisr-warranty-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="11" cy="11" r="6"></circle>
                                    <path d="m20 20-3.5-3.5"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="nisr-warranty-card__title">{{ translate('Warranty Lookup') }}</h3>
                                <p class="nisr-warranty-card__copy">{{ translate('Use_serial_number_and_registered_contact_to_check_status') }}</p>
                            </div>
                            <div class="nisr-warranty-card__action">
                                <a href="{{ route('warranty.lookup.start') }}" class="btn btn--primary">{{ translate('Warranty Lookup') }}</a>
                            </div>
                        </article>

                        <article class="nisr-warranty-card">
                            <div class="nisr-warranty-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 3l8 4v5c0 5-3.4 8.4-8 9-4.6-.6-8-4-8-9V7l8-4Z"></path>
                                    <path d="M9 12h6"></path>
                                    <path d="M12 9v6"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="nisr-warranty-card__title">{{ translate('Warranty Policy') }}</h3>
                                <p class="nisr-warranty-card__copy">{{ translate('Review_the_policy_to_understand_coverage_terms_and_claim_requirements') }}</p>
                            </div>
                            <div class="nisr-warranty-card__action">
                                <a href="{{ route('warranty-policy') }}" class="btn btn--primary">{{ translate('Warranty Policy') }}</a>
                            </div>
                        </article>
                    </div>

                    <div class="nisr-mini-card mt-4">
                        <strong>{{ translate('Warranty Claim') }}</strong>
                        <p>{{ translate('Claims_are_opened_from_inside_an_existing_warranty_record') }}</p>
                    </div>
                </div>

                <aside class="nisr-surface nisr-surface--soft">
                    <div class="nisr-surface-head">
                        <h2 class="nisr-section-title">{{ translate('Before_you_begin') }}</h2>
                        <p class="nisr-section-copy mb-0">{{ translate('Have_your_serial_number_invoice_and_registered_contact_ready') }}</p>
                    </div>

                    <ul class="nisr-checklist mb-4">
                        <li>{{ translate('Serial_number_purchase_date_invoice_number_and_purchase_location') }}</li>
                        <li>{{ translate('Use_the_same_phone_or_email_linked_to_the_activation_record') }}</li>
                        <li>{{ translate('Review_the_policy_to_understand_coverage_terms_and_claim_requirements') }}</li>
                    </ul>

                    <div class="nisr-mini-card mb-3">
                        <strong>{{ translate('support') }}</strong>
                        <p>{{ translate('If_you_need_branch_or_support_help_contact_our_team_directly') }}</p>
                    </div>

                    <div class="nisr-inline-actions">
                        <a href="{{ route('contacts') }}" class="nisr-link-pill">{{ translate('Contact_us') }}</a>
                    </div>
                </aside>
            </div>
        </section>
    </div>
@endsection
