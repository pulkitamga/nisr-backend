@extends('layouts.back-end.app')

@section('title', translate('edit_blog'))

@section('content')
@php
$language = getWebConfig('pnc_language') ?? ['en'];
$defaultLanguage = getConfiguredDefaultLanguage();
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
        <div class="card-header">
            <div class="cms-admin-heading mb-0">
                <h1 class="cms-admin-heading__title h3">{{ translate('edit_blog') }}</h1>
            </div>
        </div>

        <form action="{{ route('admin.content-management.blog.update', $blog->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Language Tabs --}}
            @php
                    $activeLanguage = $errors->any() ? $defaultLanguage : (in_array(getDefaultLanguage(), $language ?? $languages ?? [], true) ? getDefaultLanguage() : $defaultLanguage);
                @endphp
<ul class="nav nav-tabs mb-4">
                @foreach($language as $lang)
                <li class="nav-item">
                    <a class="nav-link form-system-language-tab {{ $lang == $activeLanguage ? 'active' : '' }}"
                        href="javascript:" id="{{ $lang }}-link">
                        {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                    </a>
                </li>
                @endforeach
            </ul>

            @foreach($language as $lang)
            <div class="form-system-language-form {{ $lang != $activeLanguage ? 'd-none' : '' }}"
                id="{{ $lang }}-form">
                <div class="form-group">
                    <label>{{ translate('Heading') }} ({{ strtoupper($lang) }})</label>
                    <input type="text" name="heading[]" class="form-control"
                        placeholder="{{ translate('enter_heading') }}"
                        value="{{ $lang == $defaultLanguage ? $blog->heading : ($translations[$lang]['heading'] ?? '') }}">
                </div>

                <div class="form-group">
                    <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                    <textarea name="description[]" class="form-control" rows="5"
                        placeholder="{{ translate('Enter Description') }}">
               {!! $lang == $defaultLanguage ? $blog->description : ($translations[$lang]['description'] ?? '') !!}                </textarea>
                </div>

                <input type="hidden" name="lang[]" value="{{ $lang }}">
            </div>
            @endforeach

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ translate('Category') }}</label>
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
                <label>{{ translate('Image') }}</label>
                <input type="file" name="image" class="form-control">
                @if ($blog->image)
                <img src="{{ Storage::url($blog->image) }}" class="mt-2" width="100" alt="Current Image">
                @endif
            </div>

            <div class="d-flex gap-2 flex-wrap mt-4">
                <a href="{{ route('admin.content-management.blog') }}" class="btn btn-secondary">{{ translate('Cancel') }}</a>
                <button type="submit" class="btn btn--primary">{{ translate('Update') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection
