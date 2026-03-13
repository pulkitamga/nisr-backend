@extends('layouts.back-end.app')
@section('title', translate('Add Perk'))

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0] ?? 'en';
@endphp

<div class="content container-fluid">
    <h2 class="h1 mb-4">{{ translate('Add Perk') }}</h2>

    <form action="{{ route('admin.content-management.career.store', ['section' => 'perks']) }}" method="POST">
        @csrf

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

        {{-- Language-specific Fields --}}
        @foreach($language as $lang)
        <div class="form-system-language-form {{ $lang != $defaultLanguage ? 'd-none' : '' }}" id="{{ $lang }}-form">

            <!-- Title -->
            <div class="form-group">
                <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                <input type="text" name="title[]" class="form-control" {{ $lang==$defaultLanguage ? 'required' : '' }}>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                <textarea name="description[]" rows="4" class="form-control"></textarea>
            </div>

            <input type="hidden" name="lang[]" value="{{ $lang }}">
        </div>
        @endforeach

        <!-- Icon Class -->
        <div class="form-group">
            <label>{{ translate('Icon Class (e.g. fa-heart)') }}</label>
            <input type="text" name="icon" class="form-control" placeholder="e.g. fa-heart">
        </div>

        <!-- Is Active -->
        <div class="form-group">
            <label>{{ translate('Is Active') }}</label>
            <select name="is_active" class="form-control">
                <option value="1">{{ translate('Active') }}</option>
                <option value="0">{{ translate('Inactive') }}</option>
            </select>
        </div>

        <!-- Submit -->
        <div class="form-group mt-3">
            <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
        </div>
    </form>
</div>
@endsection

@push('script')
<script>
    'use strict';

    // Language tab toggle
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
@endpush