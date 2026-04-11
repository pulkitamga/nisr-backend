@extends('layouts.front-end.app')

@section('title', translate('our_policies'))

@push('css_or_js')
    @include('web-views.partials._premium-page-styles')
    <style>
        .nisr-policy-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .nisr-policy-card {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 1rem;
            align-items: start;
            padding: 1.25rem;
            border: 1px solid rgba(16, 47, 58, 0.08);
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .nisr-policy-card:hover,
        .nisr-policy-card:focus-visible {
            transform: translateY(-3px);
            border-color: rgba(var(--bs-base-rgb), 0.22);
            box-shadow: 0 1rem 2rem rgba(16, 56, 62, 0.1);
            text-decoration: none;
        }

        .nisr-policy-card__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            inline-size: 3.2rem;
            block-size: 3.2rem;
            border-radius: 1rem;
            background: rgba(var(--bs-base-rgb), 0.12);
            color: var(--nisr-accent);
            font-size: 1.2rem;
        }

        .nisr-policy-card__title {
            margin: 0 0 .4rem;
            color: var(--nisr-ink);
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .nisr-policy-card__copy {
            margin: 0;
            color: var(--nisr-muted);
            line-height: 1.75;
        }

        .nisr-policy-card__meta {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            margin-top: .95rem;
            color: var(--nisr-accent);
            font-size: .92rem;
            font-weight: 700;
        }

        .nisr-policy-empty {
            text-align: center;
            padding: clamp(1.6rem, 3vw, 2.4rem);
        }

        .nisr-policy-empty .nisr-section-kicker {
            margin-inline: auto;
            margin-block-end: 1rem;
        }

        @media (max-width: 991.98px) {
            .nisr-policy-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $policies = [
            [
                'is_active' => isset($terms_policy['status']) && $terms_policy['status'] == 1,
                'route' => route('terms'),
                'title' => translate('Terms and Conditions'),
                'description' => translate('Policy_summary_terms'),
                'icon' => 'fa fa-file-text-o',
            ],
            [
                'is_active' => isset($shipping_policy['status']) && $shipping_policy['status'] == 1,
                'route' => route('shipping-policy'),
                'title' => translate('Shipping Policy'),
                'description' => translate('Policy_summary_shipping'),
                'icon' => 'fa fa-truck',
            ],
            [
                'is_active' => isset($return_policy['status']) && $return_policy['status'] == 1,
                'route' => route('return-policy'),
                'title' => translate('Return Policy'),
                'description' => translate('Policy_summary_return'),
                'icon' => 'fa fa-refresh',
            ],
            [
                'is_active' => isset($refund_policy['status']) && $refund_policy['status'] == 1,
                'route' => route('refund-policy'),
                'title' => translate('Refund Policy'),
                'description' => translate('Policy_summary_refund'),
                'icon' => 'fa fa-money',
            ],
            [
                'is_active' => isset($cancellation_policy['status']) && $cancellation_policy['status'] == 1,
                'route' => route('cancellation-policy'),
                'title' => translate('Cancellation Policy'),
                'description' => translate('Policy_summary_cancellation'),
                'icon' => 'fa fa-times-circle',
            ],
            [
                'is_active' => isset($service_policy['status']) && $service_policy['status'] == 1,
                'route' => route('service-policy'),
                'title' => translate('Service Policy'),
                'description' => translate('Policy_summary_service'),
                'icon' => 'fa fa-cogs',
            ],
            [
                'is_active' => isset($warranty_policy['status']) && $warranty_policy['status'] == 1,
                'route' => route('warranty-policy'),
                'title' => translate('Warranty Policy'),
                'description' => translate('Policy_summary_warranty'),
                'icon' => 'fa fa-shield',
            ],
            [
                'is_active' => isset($privacy_policy['status']) && $privacy_policy['status'] == 1,
                'route' => route('privacy-policy'),
                'title' => translate('Privacy Policy'),
                'description' => translate('Policy_summary_privacy'),
                'icon' => 'fa fa-shield',
            ],
        ];

        $activePolicies = array_values(array_filter($policies, static fn ($policy) => $policy['is_active']));
    @endphp

    <div class="nisr-page-shell">
        <div class="container">
            <section class="nisr-page-hero">
                <span class="nisr-page-eyebrow">{{ translate('our_policies') }}</span>
                <h1 class="nisr-page-title">{{ translate('Policy_center') }}</h1>
                <p class="nisr-page-lead">
                    {{ translate('Review_shipping_returns_refunds_privacy_and_service_terms_from_one_clear_policy_hub') }}
                </p>
                <div class="nisr-hero-actions">
                    <span class="nisr-stat-pill">{{ count($activePolicies) }} {{ translate('active_policies') }}</span>
                    <a href="{{ route('contacts') }}" class="nisr-link-pill">{{ translate('Need_help_contact_us') }}</a>
                </div>
            </section>

            <section class="nisr-surface">
                @if(count($activePolicies) > 0)
                    <div class="nisr-policy-grid">
                        @foreach($activePolicies as $policy)
                            <a href="{{ $policy['route'] }}" class="nisr-policy-card">
                                <div class="nisr-policy-card__icon" aria-hidden="true">
                                    <i class="{{ $policy['icon'] }}"></i>
                                </div>
                                <div>
                                    <h2 class="nisr-policy-card__title">{{ $policy['title'] }}</h2>
                                    <p class="nisr-policy-card__copy">{{ $policy['description'] }}</p>
                                    <span class="nisr-policy-card__meta">
                                        {{ translate('Read More') }}
                                        <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="nisr-policy-empty">
                        <span class="nisr-section-kicker" aria-hidden="true">
                            <i class="fa fa-file-text-o"></i>
                        </span>
                        <h2 class="nisr-section-title">{{ translate('No_Policies_Available') }}</h2>
                        <p class="nisr-section-copy mb-0">{{ translate('We_are_currently_updating_our_policies_Please_check_back_soon') }}</p>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
