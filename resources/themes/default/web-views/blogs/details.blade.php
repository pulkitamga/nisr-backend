@extends(isset($blogPlatform) && $blogPlatform == 'app' ? 'web-views.blogs.blog-layouts' : 'layouts.front-end.app')

@section('title', $blogData['title'] ?? translate('Blog_Details'))

@push('css_or_js')
    @include(VIEW_FILE_NAMES['blog_seo_meta_content_partials'], ['metaContentData' => $blogData?->seoInfo, 'blogDetails' => $blogData])

    @if(isset($blogPlatform) && $blogPlatform == 'app')
        <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/app-blog.css') }}"/>
    @endif
    @if(!isset($blogPlatform) || $blogPlatform === 'web')
        @include('web-views.partials._premium-page-styles')
    @endif
    <style>
        .nisr-blog-shell .nisr-page-hero .nisr-page-title {
            max-inline-size: 18ch;
            font-size: clamp(2rem, 3.4vw, 3.4rem);
            line-height: 1.08;
        }

        @media (max-width: 991.98px) {
            .nisr-blog-shell .nisr-page-hero .nisr-page-title {
                max-inline-size: 100%;
                font-size: clamp(1.85rem, 7vw, 2.8rem);
            }
        }
    </style>
@endpush

@section('content')
    @include('web-views.blogs.partials._app-blog-preloader')

    @php
        $blogHomeRoute = isset($blogPlatform) && $blogPlatform == 'app'
            ? route('app.blog.index', ['locale' => request('locale'), 'theme' => request('theme')])
            : route('frontend.blog.index');
        $popularRoute = isset($blogPlatform) && $blogPlatform == 'app'
            ? route('app.blog.popular-blog', ['locale' => request('locale'), 'theme' => request('theme')])
            : route('frontend.blog.popular-blog');
    @endphp

    <div class="nisr-page-shell nisr-blog-shell">
        <div class="blog-root-container" data-platform="{{ isset($blogPlatform) && $blogPlatform == 'app' ? 'app' : 'web' }}">
            <div class="container">
                <section class="nisr-page-hero">
                    <span class="nisr-page-eyebrow">{{ translate('Blog') }}</span>
                    <div class="nisr-inline-actions mt-0 mb-3">
                        <a href="{{ $blogHomeRoute }}" class="nisr-link-pill">{{ translate('Back_to_blog') }}</a>
                        @if($blogData?->category?->name)
                            <span class="nisr-chip">{{ \Illuminate\Support\Str::limit($blogData?->category?->name, 25) }}</span>
                        @endif
                    </div>
                    <h1 class="nisr-page-title">{{ $blogData['title'] ?? null }}</h1>
                    <div class="nisr-blog-meta mt-4">
                        @if($blogData->writer)
                            <span>
                                {{ translate('By') }}
                                @if(isset($blogPlatform) && $blogPlatform == 'app')
                                    <a href="{{ route('app.blog.index', ['writer' => $blogData['writer'], 'locale' => request('locale'), 'theme' => request('theme')]) }}" title="{{ $blogData['writer'] }}">
                                        {{ \Illuminate\Support\Str::limit($blogData['writer'], 50, '...') }}
                                    </a>
                                @else
                                    <a href="{{ route('frontend.blog.index', ['writer' => $blogData['writer']]) }}" title="{{ $blogData['writer'] }}">
                                        {{ \Illuminate\Support\Str::limit($blogData['writer'], 50, '...') }}
                                    </a>
                                @endif
                            </span>
                        @endif
                        <span>{{ $blogData['click_count'] ?? 0 }} {{ translate('views') }}</span>
                        <span>{!! formatDateTimeForDisplay($blogData['publish_date'] ?? null, 'M d, Y') !!}</span>
                    </div>
                </section>

                <div class="row g-4 justify-content-center">
                    @if(count($articleLinks) > 0)
                        <div class="col-lg-3">
                            <div class="position-relative">
                                <div class="article-nav-wrapper_collapse">
                                    <i class="czi-menu open-icon fw-bold"></i>
                                    <i class="czi-close close-icon fw-bold fs-10 d-none"></i>
                                </div>
                            </div>
                            <div class="article-nav-wrapper sticky-top-wrapper nisr-surface nisr-surface--soft d-none d-lg-block">
                                <div class="nisr-surface-head">
                                    <h2 class="nisr-section-title">{{ translate('In_this_article') }}</h2>
                                </div>
                                <ul class="m-0 p-0 scrollspy-blog-details-menu">
                                    @foreach ($articleLinks as $link)
                                        @if(!empty($link['text']))
                                            <li>
                                                <a href="#{{ $link['id'] }}" class="line-clamp-1">{{ $link['text'] }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="{{ count($articleLinks) > 0 ? 'col-lg-6' : 'col-lg-8' }}">
                        @if(request('source') == 'draft')
                            <div class="nisr-alert text-center mb-4">
                                <span>{{ translate('This_is_a_draft_copy.') }} {{ translate('It_has_not_been_published_yet.') }}</span>
                            </div>
                        @endif

                        <div class="nisr-surface nisr-article-card">
                            <img class="w-100 aspect-2 mb-4"
                                 src="{{ getStorageImages(path: $blogData['thumbnail_full_url'] ?? null, type:'wide-banner') }}"
                                 alt="{{ $blogData['title'] ?? null }}">
                            <div data-bs-spy="scroll"
                                 data-bs-target="#simple-list-example"
                                 data-bs-offset="0"
                                 data-bs-smooth-scroll="true"
                                 class="scrollspy-blog-details nisr-richtext"
                                 tabindex="0">
                                {!! \App\Support\CmsContentSanitizer::sanitizeRichText($updatedDescription) !!}
                            </div>
                        </div>
                    </div>

                    @php
                        $downloadAppStatus = getWebConfig(name: 'blog_feature_download_app_status') ?? 0;
                        $appTitleData = getWebConfig(name: 'blog_feature_download_app_title') ?? [];
                    @endphp

                    @if(isset($blogPlatform) && $blogPlatform == 'web' && ($appTitleData && $downloadAppStatus))
                        <div class="col-lg-3 d-none d-lg-block">
                            <div class="sticky-top-wrapper">
                                <div class="nisr-surface nisr-surface--soft text-center">
                                    <div class="nisr-surface-head">
                                        <h2 class="nisr-section-title">{{ translate('Share_Now') }}</h2>
                                    </div>
                                    <div class="nisr-share-list">
                                        <a href="javascript:" class="share-on-social-media nisr-share-button"
                                           data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                           data-social-media-name="facebook.com/sharer/sharer.php?u=">
                                            <img src="{{theme_asset(path: 'public/assets/front-end/img/blogs/facebook.svg')}}" alt="Facebook">
                                        </a>
                                        <a href="javascript:" class="share-on-social-media nisr-share-button"
                                           data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                           data-social-media-name="twitter.com/intent/tweet?text=">
                                            <img src="{{theme_asset(path: 'public/assets/front-end/img/blogs/twitter.svg')}}" alt="Twitter">
                                        </a>
                                        <a href="javascript:" class="share-on-social-media nisr-share-button"
                                           data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                           data-social-media-name="linkedin.com/shareArticle?mini=true&url=">
                                            <img src="{{theme_asset(path: 'public/assets/front-end/img/blogs/linkedin.svg')}}" alt="LinkedIn">
                                        </a>
                                        <a href="javascript:" class="share-on-social-media nisr-share-button"
                                           data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                           data-social-media-name="api.whatsapp.com/send?text=">
                                            <img src="{{theme_asset(path: 'public/assets/front-end/img/blogs/whatsapp.svg')}}" alt="WhatsApp">
                                        </a>
                                    </div>

                                    <hr class="my-4">
                                    @include('web-views.blogs.partials._download-app-card')
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col-lg-12 mt-4 mt-lg-0">
                        <div class="nisr-surface nisr-surface--soft mb-4 text-center">
                            <div class="nisr-surface-head mb-3">
                                <h2 class="nisr-section-title">{{ translate('Share_this_article') }}</h2>
                            </div>
                            <div class="nisr-share-list">
                                <a href="javascript:" class="share-on-social-media nisr-share-button"
                                   data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                   data-social-media-name="facebook.com/sharer/sharer.php?u=">
                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/blogs/facebook.svg')}}" alt="Facebook">
                                </a>
                                <a href="javascript:" class="share-on-social-media nisr-share-button"
                                   data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                   data-social-media-name="twitter.com/intent/tweet?text=">
                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/blogs/twitter.svg')}}" alt="Twitter">
                                </a>
                                <a href="javascript:" class="share-on-social-media nisr-share-button"
                                   data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                   data-social-media-name="linkedin.com/shareArticle?mini=true&url=">
                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/blogs/linkedin.svg')}}" alt="LinkedIn">
                                </a>
                                <a href="javascript:" class="share-on-social-media nisr-share-button"
                                   data-action="{{ route('frontend.blog.details', ['slug' => $blogData['slug'] ?? null]) }}"
                                   data-social-media-name="api.whatsapp.com/send?text=">
                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/blogs/whatsapp.svg')}}" alt="WhatsApp">
                                </a>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                            <h2 class="nisr-section-title mb-0">{{ translate('Popular_articles') }}</h2>
                            <a href="{{ $popularRoute }}" class="nisr-inline-link">
                                {{ translate('See_more') }}
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17" fill="none">
                                    <path d="M10.8367 2.6847C10.6187 2.4591 10.256 2.4591 10.0304 2.6847C9.81239 2.90267 9.81239 3.26546 10.0304 3.48292L14.119 7.57158H0.626997C0.312484 7.57209 0.0625 7.82208 0.0625 8.13659C0.0625 8.4511 0.312484 8.70922 0.626997 8.70922H14.119L10.0304 12.7903C9.81239 13.0159 9.81239 13.3791 10.0304 13.5966C10.256 13.8222 10.6192 13.8222 10.8367 13.5966L15.8933 8.54002C16.1189 8.32204 16.1189 7.95926 15.8933 7.7418L10.8367 2.6847Z" fill="currentColor"/>
                                </svg>
                            </a>
                        </div>

                        <div class="row g-4 mb-3">
                            @foreach($popularBlogList as $blogItem)
                                <div class="col-lg-4 col-md-6">
                                    @include('web-views.blogs.partials._single-blog-card', ['blogItem' => $blogItem])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/blog.js') }}"></script>
@endpush
