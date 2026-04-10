@extends(isset($blogPlatform) && $blogPlatform == 'app' ? 'web-views.blogs.blog-layouts' : 'layouts.front-end.app')

@section('title', translate('Popular_Blogs'))

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
                    <h1 class="nisr-page-title">{{ translate('Popular_Blogs') }}</h1>
                    <p class="nisr-page-lead">{{ translate('Browse_articles_updates_and_practical_guidance_from_NISR') }}</p>
                </section>

                <div class="nisr-surface nisr-surface--soft nisr-blog-toolbar">
                    <div class="row g-4 align-items-end">
                        <div class="col-lg-8 order-1 order-lg-0">
                            <div class="position-relative">
                                <ul class="blog-top-nav d-flex gap-3">
                                    <li class="{{ request('category') == '' ? 'active' : ''}}">
                                        <a href="{{ route('frontend.blog.popular-blog') }}" class="border rounded-10 px-3 py-2">
                                            <span>{{ translate('all') }}</span>
                                        </a>
                                    </li>
                                    @foreach($blogCategoryList as $blogCategory)
                                        <li class="{{ request('category') == $blogCategory?->name ? 'active' : ''}}">
                                            <a href="{{ route('frontend.blog.popular-blog', ['category' => $blogCategory?->name]) }}" class="border rounded-10 px-3 py-2">
                                                <span>{{ \Illuminate\Support\Str::limit($blogCategory->name, 25) }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="previous-button blog-top-nav_prev-btn">
                                    <button type="button" class="btn rounded-circle aspect-1">
                                        <i class="text-absolute-white bi bi-chevron-left"></i>
                                    </button>
                                </div>

                                <div class="next-button blog-top-nav_next-btn">
                                    <button type="button" class="btn rounded-circle aspect-1">
                                        <i class="text-absolute-white bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>

                            @if(request('search'))
                                <div class="mt-3">
                                    <span class="fw-semibold">{{ $popularBlogList->count() }}</span>
                                    <span class="px-1">{{ translate('Search_Result_Found') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="col-lg-4">
                            <form action="{{ isset($blogPlatform) && $blogPlatform == 'app' ? route('app.blog.popular-blog', ['locale' => request('locale'), 'theme' => request('theme')]) : route('frontend.blog.popular-blog') }}"
                                  id="popular-search-form"
                                  method="get">
                                <input type="hidden" name="locale" value="{{ request('locale') }}">
                                <input type="hidden" name="theme" value="{{ request('theme') }}">
                                <input type="hidden" name="category" value="{{ request('category') }}">
                                <div class="input-group-overlay input-group-sm position-relative">
                                    <input placeholder="{{ translate('Search_Blog') }}"
                                           name="search"
                                           value="{{ request('search') }}"
                                           id="popular-search"
                                           class="cz-filter-search form-control form-control-sm appended-form-control"
                                           type="text"
                                           required>
                                    <button type="submit" class="input-group-append-overlay p-0 shadow-none bg-transparent border-0 d-inline-block blog-search-btn">
                                        <span class="input-group-text p-0 pb-2">
                                            <i class="czi-search"></i>
                                        </span>
                                    </button>
                                </div>
                            </form>

                            @if(request('search'))
                                <div class="mt-3 d-flex gap-2 align-items-baseline justify-content-end clear-all-search-popular cursor-pointer">
                                    <h6 class="mb-0">{{ translate('Clear_Search') }}</h6>
                                    <button type="button" class="btn fs-14 fw-bold lh-1 m-0 p-0">
                                        <i class="czi-close fw-bold"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    @if($popularBlogList->total() <= 0 && !empty(request('search')))
                        <div class="col-12">
                            <div class="nisr-surface">
                                @include('web-views.blogs.partials._no-result-found')
                            </div>
                        </div>
                    @elseif($popularBlogList->total() <= 0)
                        <div class="col-12">
                            <div class="nisr-surface">
                                @include('web-views.blogs.partials._no-blog-found')
                            </div>
                        </div>
                    @endif

                    <div class="col-lg-12">
                        <div class="row g-4 mb-5">
                            @foreach($popularBlogList as $blogItem)
                                <div class="col-md-4">
                                    @include('web-views.blogs.partials._single-blog-card', ['blogItem' => $blogItem, 'featured' => $loop->first])
                                </div>
                            @endforeach
                            @if(count($popularBlogList) > 0)
                                <div class="col-12">
                                    <div class="d-flex justify-content-center">
                                        {!! $popularBlogList->links() !!}
                                    </div>
                                </div>
                            @endif
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
