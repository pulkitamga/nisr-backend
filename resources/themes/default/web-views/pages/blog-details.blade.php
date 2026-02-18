@extends('layouts.front-end.app')

@section('title', $blog->heading)

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<!-- Tailwind CSS CDN -->
\
    <div class="container mx-auto px-6 py-12">
        <div class="flex flex-col md:flex-row gap-10">
            
            <!-- Left Image -->
            <div class="md:w-1/2 mb-6 md:mb-0">
                <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->heading }}"
                    class="w-full h-auto object-cover rounded-lg shadow-lg">
            </div>
            
            <!-- Right Content -->
            <div class="md:w-1/2">
                <h1 class="text-4xl font-bold text-gray-800 mb-6">{{ $blog->heading }}</h1>
                <div class="text-gray-700 text-lg leading-relaxed">
                    {!! $blog->description !!}
                </div>
            </div>
        </div>

        @if($featuredPosts->count() > 0)
        <div class="mt-16">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">You may also like</h2>
        
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($featuredPosts as $post)
                <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition duration-300 overflow-hidden">
                    <a href="{{ route('blog.details', $post->id) }}" class="block">
                        <img src="{{ asset('storage/' . $post->image) }}" class="w-full h-40 object-cover" alt="{{ $post->heading }}">
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-800 hover:text-blue-600">{{ $post->heading }}</h3>
                            <p class="text-sm text-gray-600 mt-2">{{ Str::limit(strip_tags($post->description), 80) }}</p>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
@endsection
