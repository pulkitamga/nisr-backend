@extends(isset($blogPlatform) && $blogPlatform == 'app' ? 'web-views.blogs.blog-layouts' : 'layouts.front-end.app')

@section('title', translate('Blogs'))

@push('css_or_js')
    @if(isset($blogPlatform) && $blogPlatform == 'app')
        <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/app-blog.css') }}"/>
    @endif
    @if(!isset($blogPlatform) || $blogPlatform === 'web')
        @include('web-views.partials._premium-page-styles')
    @endif
@endpush

@section('content')
    @include('web-views.blogs.partials._app-blog-preloader')

    <div class="nisr-page-shell nisr-blog-shell">
        <div class="blog-root-container">
            <div class="container">
                <section class="nisr-page-hero">
                    <span class="nisr-page-eyebrow">{{ translate('Blog') }}</span>
                    <h1 class="nisr-page-title">{{ $blogTitle != '' ? $blogTitle : translate('Blog') }}</h1>
                    @if($blogSubTitle)
                        <p class="nisr-page-lead">{{ $blogSubTitle }}</p>
                    @endif
                    <div class="nisr-hero-actions">
                        <span class="nisr-stat-pill">{{ $blogList->total() }} {{ translate('Blogs') }}</span>
                        @if(request('category'))
                            <span class="nisr-link-pill">{{ request('category') }}</span>
                        @endif
                    </div>
                </section>

                @if($blogList->total() > 0 || request()->has('search') || request()->has('category') || request()->has('write'))
                    <div class="nisr-surface nisr-surface--soft nisr-blog-toolbar">
                        <div class="row g-4 align-items-end">
                            <div class="col-lg-8 order-1 order-lg-0">
                                <div class="position-relative">
                                    <ul class="blog-top-nav d-flex gap-3">
                                        <li class="{{ request('category') == '' ? 'active' : ''}}">
                                            <a href="{{ isset($blogPlatform) && $blogPlatform == 'app' ? route('app.blog.index', ['locale' => request('locale'), 'theme' => request('theme')]) : route('frontend.blog.index') }}"
                                               class="border rounded-10 px-3 py-2">
                                                <span>{{ translate('all') }}</span>
                                            </a>
                                        </li>
                                        @foreach($blogCategoryList as $blogCategory)
                                            @if(isset($blogPlatform) && $blogPlatform == 'app')
                                                <li class="{{ request('category') == $blogCategory?->name ? 'active' : ''}}">
                                                    <a href="{{ route('app.blog.index', ['category' => $blogCategory?->name, 'locale' => request('locale'), 'theme' => request('theme')]) }}" class="border rounded-10 px-3 py-2">
                                                        <span>{{ \Illuminate\Support\Str::limit($blogCategory->name, 25) }}</span>
                                                    </a>
                                                </li>
                                            @else
                                                <li class="{{ request('category') == $blogCategory?->name ? 'active' : ''}}">
                                                    <a href="{{ route('frontend.blog.index', ['category' => $blogCategory?->name]) }}" class="border rounded-10 px-3 py-2">
                                                        <span>{{ \Illuminate\Support\Str::limit($blogCategory->name, 25) }}</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>

                                    <div class="blog-top-nav_prev-btn align-items-center">
                                        <div class="previous-button">
                                            <button type="button" class="btn rounded-circle aspect-1">
                                                <i class="text-absolute-white bi bi-chevron-left"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="blog-top-nav_next-btn align-items-center">
                                        <div class="next-button d-flex justify-content-end">
                                            <button type="button" class="btn rounded-circle aspect-1">
                                                <i class="text-absolute-white bi bi-chevron-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                @if(request('search'))
                                    <div class="mt-3">
                                        <span class="fw-semibold">{{ $blogList->count() }}</span>
                                        <span class="px-1">{{ translate('Search_Result_Found') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="col-lg-4">
                                <form action="{{ isset($blogPlatform) && $blogPlatform == 'app' ? route('app.blog.index', ['locale' => request('locale'), 'theme' => request('theme')]) : route('frontend.blog.index') }}"
                                      method="get"
                                      id="search-form">
                                    <input type="hidden" name="locale" value="{{ request('locale') }}">
                                    <input type="hidden" name="theme" value="{{ request('theme') }}">
                                    <input type="hidden" name="category" value="{{ request('category') }}">
                                    <div class="input-group-overlay input-group-sm">
                                        <input placeholder="{{ translate('Search_Blog') }}"
                                               value="{{ request('search') }}"
                                               class="cz-filter-search form-control form-control-sm appended-form-control"
                                               type="text"
                                               name="search"
                                               id="search"
                                               required>
                                        <button type="submit" class="input-group-append-overlay p-0 shadow-none bg-transparent border-0 d-inline-block">
                                            <span class="input-group-text p-0 pb-2">
                                                <i class="czi-search"></i>
                                            </span>
                                        </button>
                                    </div>
                                </form>

                                @if(request('search'))
                                    <div class="mt-3 d-flex gap-2 align-items-baseline justify-content-end clear-all-search cursor-pointer">
                                        <h6 class="mb-0">{{ translate('Clear_Search') }}</h6>
                                        <button type="button" class="btn fs-14 fw-bold lh-1 m-0 p-0">
                                            <i class="czi-close fw-bold"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row g-4 mb-lg-5">
                    @if($blogList->total() <= 0 && !empty(request('search')))
                        <div class="col-12">
                            <div class="nisr-surface">
                                @include('web-views.blogs.partials._no-result-found')
                            </div>
                        </div>
                    @elseif($blogList->total() <= 0)
                        @php
                            $downloadAppStatus = getWebConfig(name: 'blog_feature_download_app_status') ?? 0;
                            $appTitleData = getWebConfig(name: 'blog_feature_download_app_title') ?? [];
                        @endphp
                        <div class="col-lg-8">
                            <div class="nisr-surface">
                                @include('web-views.blogs.partials._no-blog-found')
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="sticky-top-wrapper">
                                <div class="nisr-surface nisr-surface--soft mb-4">
                                    <div class="nisr-surface-head">
                                        <h2 class="nisr-section-title">{{ translate('Recent_Posts') }}</h2>
                                    </div>
                                    <div class="nisr-recent-post-list">
                                        @foreach($recentBlogList->take(6) as $blogItem)
                                            @php
                                                $recentLink = isset($blogPlatform) && $blogPlatform == 'app'
                                                    ? route('app.blog.details', ['slug' => $blogItem?->slug, 'locale' => request('locale'), 'theme' => request('theme')])
                                                    : route('frontend.blog.details', ['slug' => $blogItem?->slug]);
                                            @endphp
                                            <a href="{{ $recentLink }}" class="nisr-recent-post">
                                                <img src="{{ getStorageImages(path: $blogItem?->thumbnail_full_url, type:'wide-banner') }}"
                                                     alt="{{ $blogItem?->title }}">
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="nisr-recent-post__title line-clamp-2">{{ $blogItem?->title }}</span>
                                                    <span class="nisr-section-copy mb-0">{{ $blogItem->publish_date->diffForHumans() }}</span>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                @if($appTitleData && $downloadAppStatus)
                                    <div>
                                        @include('web-views.blogs.partials._download-app-card')
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="{{ !request('search') ? 'col-lg-8' : 'col-lg-12' }}">
                            <div class="row g-4 mb-0 mb-lg-5">
                                @foreach($blogList as $blogItem)
                                    <div class="{{ !request('search') ? ($loop->first ? 'col-lg-12' : 'col-md-6') : 'col-md-4' }}">
                                        @include('web-views.blogs.partials._single-blog-card', ['blogItem' => $blogItem, 'featured' => !request('search') && $loop->first])
                                    </div>
                                @endforeach
                            </div>
                            @if(count($blogList) > 0)
                                <div class="col-12">
                                    <div class="d-flex justify-content-start">
                                        {!! $blogList->links() !!}
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if(!request('search'))
                            <div class="col-lg-4">
                                <div class="sticky-top-wrapper">
                                    <div class="nisr-surface nisr-surface--soft mb-4">
                                        <div class="nisr-surface-head">
                                            <h2 class="nisr-section-title">{{ translate('Recent_Posts') }}</h2>
                                        </div>
                                        <div class="nisr-recent-post-list">
                                            @foreach($recentBlogList->take(6) as $blogItem)
                                                @php
                                                    $recentLink = isset($blogPlatform) && $blogPlatform == 'app'
                                                        ? route('app.blog.details', ['slug' => $blogItem?->slug, 'locale' => request('locale'), 'theme' => request('theme')])
                                                        : route('frontend.blog.details', ['slug' => $blogItem?->slug]);
                                                @endphp
                                                <a href="{{ $recentLink }}" class="nisr-recent-post">
                                                    <img src="{{ getStorageImages(path: $blogItem?->thumbnail_full_url, type:'wide-banner') }}"
                                                         alt="{{ $blogItem?->title }}">
                                                    <div class="d-flex flex-column gap-1">
                                                        <span class="nisr-recent-post__title line-clamp-2">{{ $blogItem?->title }}</span>
                                                        <span class="nisr-section-copy mb-0">{{ $blogItem->publish_date->diffForHumans() }}</span>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="pb-lg-5 pb-4">
                                        @include('web-views.blogs.partials._download-app-card')
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/blog.js') }}"></script>
@endpush
