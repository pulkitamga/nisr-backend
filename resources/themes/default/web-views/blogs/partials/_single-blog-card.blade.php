@php
    $isAppBlog = isset($blogPlatform) && $blogPlatform == 'app';
    $blogLink = $isAppBlog
        ? route('app.blog.details', ['slug' => $blogItem?->slug, 'locale' => request('locale'), 'theme' => request('theme')])
        : route('frontend.blog.details', ['slug' => $blogItem?->slug]);
    $categoryLink = $isAppBlog
        ? route('app.blog.index', ['category' => $blogItem?->category?->name, 'locale' => request('locale'), 'theme' => request('theme')])
        : route('frontend.blog.index', ['category' => $blogItem?->category?->name]);
    $writerLink = $isAppBlog
        ? route('app.blog.index', ['writer' => $blogItem?->writer, 'locale' => request('locale'), 'theme' => request('theme')])
        : route('frontend.blog.index', ['writer' => $blogItem?->writer]);
    $excerptLimit = !empty($featured) ? 180 : 110;
    $excerpt = \Illuminate\Support\Str::limit(strip_tags((string) ($blogItem?->description ?? '')), $excerptLimit);
@endphp

<div class="blog-card nisr-blog-card border-0 h-100 blog-single-card-item {{ !empty($featured) ? 'nisr-blog-card--feature' : '' }}"
     data-route="{{ $blogLink }}">
    <div class="nisr-blog-card__media">
        <img src="{{ getStorageImages(path: $blogItem?->thumbnail_full_url, type:'wide-banner') }}"
             alt="{{ $blogItem?->title }}">
    </div>

    <div class="nisr-blog-card__body">
        @if($blogItem?->category?->name)
            <a href="{{ $categoryLink }}" title="{{ $blogItem?->category?->name }}" class="nisr-chip w-fit-content">
                {{ \Illuminate\Support\Str::limit($blogItem?->category?->name, 25) ?? translate('Uncategorized') }}
            </a>
        @endif

        <h3 class="nisr-blog-card__title line-clamp-2">
            <a href="{{ $blogLink }}" class="line-clamp-2">
                {{ $blogItem?->title }}
            </a>
        </h3>

        @if(filled($excerpt))
            <p class="nisr-blog-card__excerpt">{{ $excerpt }}</p>
        @endif

        <div class="nisr-blog-card__footer">
            <div class="d-flex flex-column gap-1">
                @if($blogItem?->writer)
                    <span class="opacity-80 fs-14 fs-12-mobile">
                        {{ translate('By') }}
                        <a href="{{ $writerLink }}" class="fw-semibold max-width-20ch line-clamp-1 fs-12-mobile"
                           title="{{ $blogItem?->writer }}">
                            {{ \Illuminate\Support\Str::limit($blogItem?->writer, 40, '...') }}
                        </a>
                    </span>
                @endif
                <span class="opacity-70 text-nowrap fs-14 fs-12-mobile">{{ $blogItem->publish_date->diffForHumans() }}</span>
            </div>

            <a href="{{ $blogLink }}" class="nisr-inline-link">
                {{ translate('Read More') }}
            </a>
        </div>
    </div>
</div>
