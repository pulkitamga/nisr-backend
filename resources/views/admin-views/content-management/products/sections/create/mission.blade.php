@extends('layouts.back-end.app')
@section('title', translate('create_mission_section'))

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? ['en'];
$defaultLanguage = $language[0] ?? 'en';
@endphp

<div class="content container-fluid">
    <div class="card">
        <div class="card-header">

            <h2 class="h1 text-capitalize mb-3">{{ translate('create_mission_section') }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.content-management.about-us.store', ['section' => 'mission']) }}"
                method="POST">
                @csrf

                {{-- Language Tabs --}}
                <ul class="nav nav-tabs mb-4">
                    @foreach ($language as $lang)
                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $defaultLanguage ? 'active' : '' }}"
                            href="javascript:" id="{{ $lang }}-link">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>

                {{-- Language Field Groups --}}
                @foreach ($language as $lang)
                <div class="form-group language-input-group {{ $lang != $defaultLanguage ? 'd-none' : '' }}"
                    id="{{ $lang }}-form">
                    <div class="form-group">
                        <label>{{ translate('title') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="title[]" class="form-control" {{ $lang==$defaultLanguage ? 'required'
                            : '' }}>
                    </div>

                    <div class="form-group">
                        <label>{{ translate('content') }} ({{ strtoupper($lang) }})</label>
                        <textarea name="content[]" rows="5" class="form-control" {{ $lang==$defaultLanguage ? 'required'
                            : '' }}></textarea>
                    </div>

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Language tab switching --}}
<script>
    document.querySelectorAll('.form-system-language-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            const lang = this.id.replace('-link', '');
            document.querySelectorAll('.form-system-language-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            document.querySelectorAll('.language-input-group').forEach(group => {
                group.classList.add('d-none');
            });
            document.getElementById(lang + '-form').classList.remove('d-none');
        });
    });
</script>
@endsection