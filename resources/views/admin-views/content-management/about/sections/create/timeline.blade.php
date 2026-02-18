@extends('layouts.back-end.app')
@section('title', translate('create_timeline_section'))

@section('content')
@php
$language = getWebConfig(name: 'pnc_language') ?? null;
$defaultLanguage = $language[0] ?? 'en';
@endphp

<div class="content container-fluid">
    <div class="card">
        <div class="card-header">

            <h2 class="h1 text-capitalize mb-3">{{ translate('create_timeline_section') }}</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.content-management.about-us.store', ['section' => 'timeline']) }}"
                method="POST" enctype="multipart/form-data">
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

                {{-- Language Based Fields --}}
                @foreach($language as $lang)
                <div class="form-system-language-form {{ $lang != $defaultLanguage ? 'd-none' : '' }}"
                    id="{{ $lang }}-form">
                    <div class="form-group">
                        <label>{{ translate('title') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="title[]" class="form-control"
                            placeholder="{{ translate('Enter Title') }}" {{ $lang==$defaultLanguage ? 'required' : ''
                            }}>
                    </div>

                    <div class="form-group">
                        <label>{{ translate('description') }} ({{ strtoupper($lang) }})</label>
                        <textarea name="description[]" rows="5" class="form-control"
                            placeholder="{{ translate('Enter Description') }}" {{ $lang==$defaultLanguage ? 'required'
                            : '' }}></textarea>
                    </div>

                    <input type="hidden" name="lang[]" value="{{ $lang }}">
                </div>
                @endforeach

                {{-- Common Fields --}}
                <div class="form-group">
                    <label>{{ translate('year') }}</label>
                    <input type="text" name="year" class="form-control" placeholder="{{ translate('Enter Year') }}"
                        required>
                </div>

                <div class="form-group">
                    <label>{{ translate('Image Upload') }}</label>
                    <input type="file" name="image" class="form-control" accept="image/*" onchange="previewIcon(this)">
                </div>

                <div class="form-group">
                    <label>Image Preview:</label><br>
                    <img id="iconPreview" src="#" alt="No image selected" style="max-height: 60px; max-width: 60px; display: none; border: 1px solid #ddd; padding: 5px;">
                </div>




                <div class="form-group mt-3">
                    <button type="submit" class="btn btn--primary">{{ translate('submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.form-system-language-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const lang = this.id.replace('-link', '');

            document.querySelectorAll('.form-system-language-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            document.querySelectorAll('.form-system-language-form').forEach(f => {
                f.classList.add('d-none');
                if (f.id === `${lang}-form`) {
                    f.classList.remove('d-none');
                }
            });
        });
    });
</script>
<script>
    function previewIcon(input) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();

            reader.onload = function(e) {
                $('#iconPreview')
                    .attr('src', e.target.result)
                    .show();
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
</script>


@endsection