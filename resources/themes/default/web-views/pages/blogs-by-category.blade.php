@extends('layouts.front-end.app')

@section('title', $category . ' ' . __('Blogs'))

@section('content')


<section class="py-12 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">{{ $category }} {{ __('Blogs') }}</h2>

        @if($blogs->count())
        <!-- First Blog with Large Image and Text on the Right -->
        <div class="mb-8">
            <a href="{{ route('blog.details', $blogs->first()->id) }}">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="flex gap-6">
                        <div class="flex-1">
                            <img src="{{ asset('storage/' . $blogs->first()->image) }}" class="w-full h-72 object-cover" alt="{{ $blogs->first()->heading }}">
                        </div>
                        <div class="flex-1 p-6">
                            <h3 class="text-2xl font-semibold">{{ $blogs->first()->heading }}</h3>
                            <p class="text-sm text-gray-600 mt-3">
                                {{ Str::limit(strip_tags($blogs->first()->description), 150) }}
                            </p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Rest Blogs as Cards (4 per row) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($blogs->skip(1) as $blog)
            <!-- Skipping the first blog -->
            <a href="{{ route('blog.details', $blog->id) }}">
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                    <img src="{{ asset('storage/' . $blog->image) }}" class="w-full h-48 object-cover" alt="{{ $blog->heading }}">
                    <div class="p-4">
                        <h3 class="text-xl font-semibold">{{ $blog->heading }}</h3>
                        <p class="text-sm text-gray-600 mt-2">
                            {{ Str::limit(strip_tags($blog->description), 100) }}
                        </p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $blogs->links() }}
        </div>
        @else
        <p class="text-center text-gray-500">{{ __('No blog found in this category.') }}</p>
        @endif
    </div>
</section>
@endsection
