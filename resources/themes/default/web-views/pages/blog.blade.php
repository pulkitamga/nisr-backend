@extends('layouts.front-end.app')

@section('title', translate('Blog'))

@section('content')
<!-- Tailwind CSS -->
<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.tailwindcss.com"></script>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
<!-- Hero Section with Swiper -->
<section class="w-full h-[80vh] px-4 py-6">
    <div class="h-full rounded-xl overflow-hidden relative">
        <div class="swiper mySwiper h-full rounded-xl">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <img src="https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=1600" class="w-full h-full object-cover rounded-xl" alt="Tech">
                </div>
                <div class="swiper-slide">
                    <img src="https://images.pexels.com/photos/1051077/pexels-photo-1051077.jpeg?auto=compress&cs=tinysrgb&w=1600" class="w-full h-full object-cover rounded-xl" alt="Travel">
                </div>
                <div class="swiper-slide">
                    <img src="https://images.pexels.com/photos/7972787/pexels-photo-7972787.jpeg?auto=compress&cs=tinysrgb&w=1600" class="w-full h-full object-cover rounded-xl" alt="Lifestyle">
                </div>
            </div>
        </div>

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center text-white text-center rounded-xl pointer-events-none">
            <div>
                <h1 class="text-4xl font-bold mb-4">{{ __('Explore Inspiring Stories') }}</h1>
                <p class="text-lg">{{ __('Travel, tech, lifestyle and more!') }}</p>
            </div>
        </div>
    </div>
</section>


<!-- Featured Posts -->
<section class="py-12 bg-white">
    <div class="max-w-6xl mx-auto px-2">
        <h2 class="text-3xl font-bold mb-8 text-center text-[#119d90]">{{ __('Featured Posts') }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            @foreach($featuredPosts as $post)
            @if($post->status == 1)
            <div class="bg-white shadow-xl rounded-lg overflow-hidden">
                <img src="{{ asset('storage/' . $post->image) }}" class="w-full h-48 object-cover" alt="{{ $post->heading }}">
                <div class="p-4">
                    <h3 class="text-xl font-semibold">
                        <a href="{{ route('blog.details', $post->id) }}" class="text-[#119d90] hover:underline">
                            {{ $post->heading }}
                        </a>
                    </h3>
                    <p class="text-sm mt-2">
                        {{ Str::limit(strip_tags($post->description), 100) }}
                    </p>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        @if($featuredPosts->where('status', 1)->count() === 0)
        <p class="text-center text-gray-500 mt-6">{{ __('No active featured blog available') }}</p>
        @endif
    </div>
</section>


<!-- Newsletter CTA -->
<section class="py-16 bg-gray-100 text-center">
    <h2 class="text-3xl font-bold mb-4 text-[#119d90]">{{ __('Subscribe to Our Newsletter') }}</h2>
    <p class="mb-6 text-gray-700">{{ __('Weekly updates and blog ideas in your inbox') }}</p>
    <form class="flex justify-center gap-2 flex-wrap">
        <input type="email" placeholder="{{ __('Enter your email') }}" class="px-4 py-2 rounded border border-gray-300 focus:outline-none">
        <button class="bg-[#119d90] hover:bg-[#119d90] text-white px-6 py-2 rounded">{{ __('Subscribe') }}</button>
    </form>
</section>

<!-- Latest Blog Cards -->
<section class="py-12 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center text-[#119d90]">{{ __('Latest Blog Posts') }}</h2>

        <div class="grid md:grid-cols-2 gap-6">
            @foreach($latestPosts as $post)
            @if($post->status == 1)
            <div class="flex flex-col md:flex-row gap-4 bg-gray-50 p-4 rounded hover:shadow-md transition duration-300">
                <a href="{{ route('blog.details', $post->id) }}">
                    <div class="w-full md:w-[200px] h-[200px] overflow-hidden rounded mx-auto md:mx-0">
                        <img src="{{ asset('storage/' . $post->image) }}" class="w-full h-full object-cover" alt="{{ $post->heading }}">
                    </div>
                </a>
                <div class="text-center md:text-start">
                    <a href="{{ route('blog.details', $post->id) }}">
                        <h3 class="text-xl font-semibold hover:text-[#119d90] mt-2 md:mt-0">
                            {{ $post->heading }}
                        </h3>
                    </a>
                    <p class="text-sm mt-2 text-gray-600">
                        {{ Str::limit(strip_tags($post->description), 100) }}
                    </p>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        @if($latestPosts->where('status', 1)->count() === 0)
        <p class="text-center text-gray-500 mt-6">{{ __('No latest blog posts available.') }}</p>
        @endif

        <!-- Pagination and Show More Button -->
        <div class="mt-6 text-center">
            @if($latestPosts->hasMorePages())
            <a href="{{ $latestPosts->nextPageUrl() }}" class="inline-block text-[#119d90] hover:text-[#119d90] font-semibold py-2 px-4 border border-[#119d90] rounded-full transition duration-300">
                {{ __('Show More') }}
            </a>
            @endif
        </div>
    </div>
</section>




<!-- Categories Grid Section -->
<section class="py-16 bg-gray-100">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-5 text-center text-[#119d90]">{{ __('Popular Categories') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-6">
            @php
            $categoryImages = [
            'Technology' => 'https://images.pexels.com/photos/1181675/pexels-photo-1181675.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Food' => 'https://images.pexels.com/photos/164631/pexels-photo-164631.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Travel' => 'https://images.pexels.com/photos/19670/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=800',
            'Health' => 'https://images.pexels.com/photos/716276/pexels-photo-716276.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Social Media' => 'https://images.pexels.com/photos/2004161/pexels-photo-2004161.jpeg?auto=compress&cs=tinysrgb&w=800',
            'Business' => 'https://images.pexels.com/photos/3184418/pexels-photo-3184418.jpeg?auto=compress&cs=tinysrgb&w=800',
            ];
            @endphp

            @foreach($categoryImages as $category => $image)
            <a href="{{ route('blogs.byCategory', ['category' => $category]) }}">
                <div class="bg-white rounded-lg shadow-lg hover:scale-105 transition-transform duration-300">
                    <img src="{{ $image }}" class="w-full h-40 object-cover rounded-t-lg" alt="{{ $category }}">
                    <div class="p-4 text-center">
                        <h3 class="font-semibold text-lg text-[#119d90]">{{ $category }}</h3>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>



@if($socialMediaBlogs->isNotEmpty())
<section class="py-16 bg-gray-100">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-6 text-[#119d90]">{{ __('Follow on Instagram') }}</h2>
        <p class="text-gray-600 mb-10">{{ __('Peek into my life - behind the scenes and snapshots') }}</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($socialMediaBlogs as $blog)
            <img src="{{ asset('storage/' . $blog->image) }}" class="w-full h-40 object-cover rounded-lg hover:scale-105 transition-transform duration-300" alt="{{ $blog->heading }}">
            @endforeach
        </div>
    </div>
</section>
@endif


<!-- Swiper Init Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper(".mySwiper", {
            loop: true
            , autoplay: {
                delay: 4000
            , }
            , effect: 'fade'
        , });
    });

</script>

@endsection
