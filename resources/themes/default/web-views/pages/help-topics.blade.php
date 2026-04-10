@extends('layouts.front-end.app')

@section('title', translate('FAQ'))

@push('css_or_js')
    @include('web-views.partials._premium-page-styles')
@endpush

@section('content')
    @php
        $helpCount = count($helps);
    @endphp

    <div class="nisr-page-shell">
        <section class="container rtl text-align-direction">
            <div class="nisr-page-hero">
                <span class="nisr-page-eyebrow">{{ translate('FAQ') }}</span>
                <h1 class="nisr-page-title">{{ translate('frequently_asked_questions') }}</h1>
                <p class="nisr-page-lead">
                    {{ translate('Find_quick_answers_about_products_warranty_and_support_before_reaching_out_to_our_team') }}
                </p>
                <div class="nisr-hero-actions">
                    <span class="nisr-stat-pill">{{ $helpCount }} {{ translate('Support_answers_available') }}</span>
                    <a href="{{ route('contacts') }}" class="nisr-link-pill">{{ translate('Contact_us') }}</a>
                    <a href="{{ route('warranty.lookup.start') }}" class="nisr-link-pill">{{ translate('Warranty Lookup') }}</a>
                </div>
            </div>
        </section>

        <section class="container pb-5 rtl text-align-direction">
            @if($helpCount > 0)
                <div class="nisr-surface nisr-surface--soft mb-4">
                    <div class="nisr-surface-head mb-0">
                        <h2 class="nisr-section-title">{{ translate('Explore_answers_or_contact_our_team_if_you_need_more_help') }}</h2>
                        <p class="nisr-section-copy mb-0">{{ translate('We_are_here_to_help') }}</p>
                    </div>
                </div>

                <div class="nisr-faq-grid">
                    @foreach($helps as $topic)
                        <article class="nisr-faq-item">
                            <button class="nisr-faq-trigger collapsed" type="button"
                                    data-toggle="collapse"
                                    data-target="#faqCollapse{{ $topic->id }}"
                                    aria-expanded="false"
                                    aria-controls="faqCollapse{{ $topic->id }}">
                                <span>{{ $topic['question'] }}</span>
                                <span class="nisr-faq-icon" aria-hidden="true">+</span>
                            </button>
                            <div id="faqCollapse{{ $topic->id }}" class="collapse">
                                <div class="nisr-faq-answer">{{ $topic['answer'] }}</div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="nisr-surface nisr-empty-state">
                    <img src="{{ dynamicStorage(path: 'public/assets/front-end/img/empty-icons/empty-faqs.svg') }}"
                         alt="{{ translate('FAQ') }}"
                         class="img-fluid"
                         width="96">
                    <h2 class="nisr-section-title">{{ translate('there_is_no_FAQs') }}</h2>
                </div>
            @endif
        </section>
    </div>
@endsection
