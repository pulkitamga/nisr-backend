@extends('layouts.back-end.app')

@section('title', translate('edit_blog'))

@section('content')
@php
$language = getWebConfig('pnc_language') ?? ['en'];
$defaultLanguage = config('app.locale', 'en');
if (!in_array($defaultLanguage, $language ?? [], true)) {
    $defaultLanguage = $language[0] ?? 'en';
}
$translations = [];
foreach ($blog->translations as $translation) {
$translations[$translation->locale][$translation->key] = $translation->value;
}
@endphp

<div class="content container-fluid ">
    <div class="card">
        <div class="page-header">
            <h1 class="page-title">{{ translate('edit_blog') }}</h1>
        </div>

        <form action="{{ route('admin.content-management.blog.update', $blog->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Language Tabs --}}
            <ul class="nav nav-tabs mb-4">
                @foreach($language as $lang)
                <li class="nav-item">
                    <a class="nav-link form-system-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                        href="javascript:" id="{{ $lang }}-link">
                        {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                    </a>
                </li>
                @endforeach
            </ul>

            @foreach($language as $lang)
            <div class="form-system-language-form {{ $lang != $defaultLanguage ? 'd-none' : '' }}"
                id="{{ $lang }}-form">
                <div class="form-group">
                    <label>{{ translate('heading') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="heading[]" class="form-control"
                        placeholder="{{ translate('Enter Heading') }}"
                        value="{{ $lang == $defaultLanguage ? $blog->heading : ($translations[$lang]['heading'] ?? '') }}"
                        {{ $lang==$defaultLanguage ? 'required' : '' }}>
                </div>

                <div class="form-group">
                    <label>{{ translate('description') }} ({{ strtoupper($lang) }})</label>
                    <textarea name="description[]" class="form-control" rows="5"
                        placeholder="{{ translate('Enter Description') }}" {{ $lang==$defaultLanguage ? 'required' : ''
                        }}>
               {!! $lang == $defaultLanguage ? $blog->description : ($translations[$lang]['description'] ?? '') !!}                </textarea>
                </div>

                <input type="hidden" name="lang[]" value="{{ $lang }}">
            </div>
            @endforeach

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ translate('category') }}</label>
                    <select name="category" class="form-control" required>
                        @foreach($categories as $category)
                        <option value="{{ $category }}" {{ $blog->category == $category ? 'selected' : '' }}>{{
                            ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ translate('blog_type') }}</label>
                    <select name="blog_type" class="form-control" required>
                        @foreach($blogTypes as $type)
                        <option value="{{ $type }}" {{ $blog->blog_type == $type ? 'selected' : '' }}>{{ $type }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>{{ translate('image') }}</label>
                <input type="file" name="image" class="form-control">
                @if ($blog->image)
                <img src="{{ Storage::url($blog->image) }}" class="mt-2" width="100" alt="Current Image">
                @endif
            </div>

            <button type="submit" class="btn btn--primary">{{ translate('update') }}</button>
            <a href="{{ route('admin.content-management.blog') }}" class="btn btn-secondary">{{ translate('cancel')
                }}</a>
        </form>
    </div>
</div>

<script>
    // Language Tab Switching
    document.querySelectorAll('.form-system-language-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            const lang = this.id.replace('-link', '');
            document.querySelectorAll('.form-system-language-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.form-system-language-form').forEach(f => f.classList.add('d-none'));
            document.getElementById(lang + '-form').classList.remove('d-none');
        });
    });
</script>

@endsection
