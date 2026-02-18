{{-- resources/views/admin-views/content-management/contact-us/index.blade.php --}}
@extends('layouts.back-end.app')

@section('title', 'Manage Sections')

@section('content')

    <div class="container-fluid">

        {{-- Section Tabs --}}
        <div class="mb-4 d-flex flex-wrap gap-3">
            <a href="{{ route('admin.content-management.contact-us', ['section' => 'contact_us']) }}" 
               class="btn btn-outline-primary {{ $currentSection == 'contact_us' ? 'active' : '' }}">Contact Us</a>
            <a href="{{ route('admin.content-management.contact-us', ['section' => 'banner']) }}" 
               class="btn btn-outline-primary {{ $currentSection == 'banner' ? 'active' : '' }}">Banner</a>
        </div>

        {{-- Display Data Based on Section --}}
        @if($currentSection == 'contact_us')
            <h3>Contact Us Data</h3>
            @foreach($data as $contact)
                <div class="contact-card">
                    <p>Phone: {{ $contact->phone }}</p>
                    <p>Email: {{ $contact->email }}</p>
                    <p>Location: {{ $contact->location }}</p>
                </div>
            @endforeach
        @elseif($currentSection == 'follow_us')
            <h3>Follow Us Data</h3>
            @foreach($data as $follow)
                <div class="follow-card">
                    <p>Platform: {{ $follow->platform }}</p>
                    <p>Username: <a href="{{ $follow->link }}" target="_blank">{{ $follow->username }}</a></p>
                </div>
            @endforeach
        @elseif($currentSection == 'banner')
            <h3>Banner Data</h3>
            @foreach($data as $banner)
                <div class="banner-card">
                    <h4>{{ $banner->heading }}</h4>
                    <p>{{ $banner->subheading }}</p>
                    @if($banner->image)
                        <img src="{{ Storage::url($banner->image) }}" alt="Banner Image">
                    @else
                        <p>No Image Available</p>
                    @endif
                </div>
            @endforeach
        @endif

    </div>

@endsection
