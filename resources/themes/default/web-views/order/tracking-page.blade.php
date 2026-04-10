@extends('layouts.front-end.app')

@section('title', translate('track_Order_Result'))

@push('css_or_js')
    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ $web_config['company_name'] }} "/>
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">
    @include('web-views.partials._premium-page-styles')
    <style>
        .nisr-track-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(18rem, .85fr);
            gap: 1rem;
            align-items: start;
        }

        .nisr-track-aside {
            display: grid;
            gap: 1rem;
        }

        .nisr-track-help {
            display: grid;
            gap: .85rem;
        }

        .nisr-track-form .btn {
            width: 100%;
        }

        @media (max-width: 991.98px) {
            .nisr-track-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="nisr-page-shell">
        <div class="container">
            <section class="nisr-page-hero">
                <span class="nisr-page-eyebrow">{{ translate('track_order') }}</span>
                <h1 class="nisr-page-title">{{ translate('Track_your_order') }}</h1>
                <p class="nisr-page-lead">
                    {{ translate('Enter_your_order_id_and_the_phone_number_used_during_checkout_to_view_the_latest_delivery_status') }}
                </p>
                <div class="nisr-hero-actions">
                    <span class="nisr-stat-pill">{{ translate('Order_status_updates') }}</span>
                    <a href="{{ route('contacts') }}" class="nisr-link-pill">{{ translate('Need_help_contact_us') }}</a>
                </div>
            </section>

            <div class="nisr-track-grid">
                <section class="nisr-surface nisr-track-form">
                    <div class="nisr-surface-head">
                        <span class="nisr-section-kicker" aria-hidden="true">1</span>
                        <h2 class="nisr-section-title">{{ translate('Track_your_order') }}</h2>
                        <p class="nisr-section-copy mb-0">{{ translate('Use_the_same_phone_number_you_used_during_checkout_for_the_fastest_match') }}</p>
                    </div>

                    <form action="{{ route('track-order.result') }}" method="post" class="d-grid gap-4">
                        @csrf

                        @if(session()->has('Error'))
                            <div class="alert alert-danger mb-0">
                                <strong>{{ session()->get('Error') }}</strong>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="trackOrderId">{{ translate('order_id') }}</label>
                                <input id="trackOrderId"
                                       class="form-control"
                                       type="number"
                                       value="{{ request('order_id') }}"
                                       name="order_id"
                                       placeholder="{{ translate('order_id') }}"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="trackOrderPhone">{{ translate('your_phone_number') }}</label>
                                <input id="trackOrderPhone"
                                       class="form-control"
                                       name="phone_number"
                                       type="tel"
                                       value="{{ request('phone_number') }}"
                                       placeholder="{{ translate('your_phone_number') }}"
                                       required>
                            </div>
                        </div>

                        <button class="btn btn--primary" type="submit" name="trackOrder">
                            {{ translate('track_order') }}
                        </button>
                    </form>
                </section>

                <aside class="nisr-track-aside">
                    <section class="nisr-surface nisr-surface--soft">
                        <div class="nisr-surface-head">
                            <span class="nisr-section-kicker" aria-hidden="true">2</span>
                            <h2 class="nisr-section-title">{{ translate('Before_you_start') }}</h2>
                        </div>
                        <div class="nisr-track-help">
                            <p class="nisr-section-copy mb-0">{{ translate('Have_your_order_id_and_checkout_phone_number_ready') }}</p>
                            <p class="nisr-section-copy mb-0">{{ translate('Guest_and_registered_customer_orders_can_be_tracked_from_here') }}</p>
                            <p class="nisr-section-copy mb-0">{{ translate('If_you_enter_incorrect_details_the_system_will_not_find_the_order') }}</p>
                        </div>
                    </section>

                    <section class="nisr-surface">
                        <div class="nisr-surface-head">
                            <span class="nisr-section-kicker" aria-hidden="true">3</span>
                            <h2 class="nisr-section-title">{{ translate('Need_help_contact_us') }}</h2>
                        </div>
                        <div class="nisr-inline-actions">
                            <a href="{{ route('contacts') }}" class="nisr-link-pill">{{ translate('Contact_us') }}</a>
                            <a href="{{ route('our-policies') }}" class="nisr-link-pill">{{ translate('our_policies') }}</a>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection
