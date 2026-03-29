@extends('layouts.back-end.app')

@section('title', translate('Edit Card'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
<style>
/* Icon Picker */
.icon-picker-wrapper { position: relative; }
.icon-picker-dropdown {
    display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1050;
    background: #fff; border: 1px solid #e7eaf3; border-radius: 8px;
    box-shadow: 0 8px 25px rgba(0,0,0,.12); max-height: 320px; overflow: hidden;
    margin-top: 4px;
}
.icon-picker-dropdown.show { display: flex; flex-direction: column; }
.icon-picker-search {
    padding: 10px; border-bottom: 1px solid #edf2f9; position: sticky; top: 0; background: #fff; z-index: 1;
}
.icon-picker-search input {
    width: 100%; border: 1px solid #edf2f9; border-radius: 6px; padding: 8px 12px; font-size: 13px;
    outline: none;
}
.icon-picker-search input:focus { border-color: #377dff; }
.icon-picker-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 4px; padding: 10px; overflow-y: auto; max-height: 260px;
}
.icon-picker-item {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 10px 4px; border-radius: 6px; cursor: pointer; transition: all .15s; border: 2px solid transparent;
}
.icon-picker-item:hover { background: #f0f4ff; }
.icon-picker-item.selected { border-color: #377dff; background: #eef2ff; }
.icon-picker-item i { font-size: 22px; margin-bottom: 4px; color: #1e2022; }
.icon-picker-item span { font-size: 9px; color: #8c98a4; text-align: center; line-height: 1.2; word-break: break-all; }

/* Color Picker */
.color-picker-wrapper { display: flex; align-items: center; gap: 10px; }
.color-picker-wrapper input[type="color"] {
    width: 45px; height: 40px; border: 2px solid #e7eaf3; border-radius: 6px;
    cursor: pointer; padding: 2px;
}
.color-preview {
    width: 40px; height: 40px; border-radius: 6px; border: 2px solid #e7eaf3;
    display: flex; align-items: center; justify-content: center;
}

/* Animation Picker */
.animation-picker-wrapper { position: relative; }
.animation-picker-dropdown {
    display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1050;
    background: #fff; border: 1px solid #e7eaf3; border-radius: 8px;
    box-shadow: 0 8px 25px rgba(0,0,0,.12); max-height: 260px; overflow-y: auto;
    margin-top: 4px;
}
.animation-picker-dropdown.show { display: block; }
.animation-picker-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 14px; cursor: pointer; transition: all .15s; border-bottom: 1px solid #f5f7fa;
}
.animation-picker-item:hover { background: #f0f4ff; }
.animation-picker-item.selected { background: #eef2ff; color: #377dff; font-weight: 600; }
.animation-picker-item:last-child { border-bottom: none; }

@keyframes ani-bounce { 0%,20%,50%,80%,100%{transform:translateY(0)} 40%{transform:translateY(-8px)} 60%{transform:translateY(-4px)} }
@keyframes ani-pulse { 0%{transform:scale(1)} 50%{transform:scale(1.15)} 100%{transform:scale(1)} }
@keyframes ani-shake { 0%,100%{transform:translateX(0)} 10%,30%,50%,70%,90%{transform:translateX(-3px)} 20%,40%,60%,80%{transform:translateX(3px)} }
@keyframes ani-spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
@keyframes ani-float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
@keyframes ani-swing { 0%,100%{transform:rotate(0)} 25%{transform:rotate(8deg)} 75%{transform:rotate(-8deg)} }
@keyframes ani-flip { 0%{transform:perspective(400px) rotateY(0)} 100%{transform:perspective(400px) rotateY(360deg)} }
@keyframes ani-rubberBand { 0%{transform:scaleX(1)} 30%{transform:scaleX(1.25) scaleY(.75)} 40%{transform:scaleX(.75) scaleY(1.25)} 50%{transform:scaleX(1.15) scaleY(.85)} 65%{transform:scaleX(.95) scaleY(1.05)} 75%{transform:scaleX(1.05) scaleY(.95)} 100%{transform:scaleX(1) scaleY(1)} }

.anim-preview { display: inline-block; font-size: 16px; color: #377dff; }
.anim-preview.ani-bounce { animation: ani-bounce 1s infinite; }
.anim-preview.ani-pulse { animation: ani-pulse 1s infinite; }
.anim-preview.ani-shake { animation: ani-shake .6s infinite; }
.anim-preview.ani-spin { animation: ani-spin 1.5s linear infinite; }
.anim-preview.ani-float { animation: ani-float 2s ease-in-out infinite; }
.anim-preview.ani-swing { animation: ani-swing 1.5s ease-in-out infinite; }
.anim-preview.ani-flip { animation: ani-flip 2s ease-in-out infinite; }
.anim-preview.ani-rubberBand { animation: ani-rubberBand 1s infinite; }
</style>
@endpush

@section('content')
@php
$baseLanguage = getConfiguredDefaultLanguage();
if (!in_array($baseLanguage, $languages ?? [], true)) {
    $baseLanguage = $languages[0] ?? 'en';
}
$activeLanguage = $baseLanguage;
@endphp

<div class="content container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h1 mb-0">{{ translate('Edit Card') }}</h2>
            <a href="{{ route('admin.content-management.home', ['section' => 'why_choose_us']) }}" class="btn btn-secondary">
                {{ translate('back') }}
            </a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.content-management.why-choose.update') }}" id="editCardForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="index" value="{{ $index }}">
                <input type="hidden" name="icon_name" id="icon_name_input" value="{{ $card['icon_name'] ?? ($card['icon']['name'] ?? '') }}">
                <input type="hidden" name="icon_color" id="icon_color_input" value="{{ $card['icon_color'] ?? ($card['icon']['color'] ?? '') }}">
                <input type="hidden" name="icon_animation" id="icon_animation_input" value="{{ $card['icon_animation'] ?? ($card['icon']['animation'] ?? '') }}">

                <ul class="nav nav-tabs mb-4">
                    @foreach($languages as $lang)
                    <li class="nav-item">
                        <a class="nav-link form-system-language-tab {{ $lang == $activeLanguage ? 'active' : '' }}"
                            href="javascript:" id="{{ $lang }}-link">
                            {{ getLanguageName($lang) }} ({{ strtoupper($lang) }})
                        </a>
                    </li>
                    @endforeach
                </ul>

                @foreach($languages as $lang)
                @php
                $translatedTitle = $translations[$lang]['title'] ?? '';
                $translatedDescription = $translations[$lang]['description'] ?? '';
                @endphp
                <input type="hidden" name="lang[]" value="{{ $lang }}">
                <div class="form-group {{ $lang != $activeLanguage ? 'd-none' : '' }} form-system-language-form"
                    id="{{ $lang }}-form">
                    <div class="form-group mb-3">
                        <label>{{ translate('Title') }} ({{ strtoupper($lang) }})</label>
                        <input type="text" name="title[]" class="form-control"
                            value="{{ $lang == $baseLanguage ? ($card['title'] ?? '') : $translatedTitle }}"
                            placeholder="{{ translate('enter_title') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Description') }} ({{ strtoupper($lang) }})</label>
                        <textarea name="description[]" class="form-control summernote" rows="3">{{ $lang == $baseLanguage ? ($card['description'] ?? '') : $translatedDescription }}</textarea>
                    </div>
                </div>
                @endforeach

                <hr>
                <div class="row mt-3">
                    {{-- Icon Name Picker --}}
                    <div class="col-md-4">
                        <div class="mb-3 icon-picker-wrapper">
                            <label class="form-label">{{ translate('Icon') }}</label>
                            <div class="form-control d-flex align-items-center justify-content-between" id="icon_name_display"
                                style="cursor:pointer; min-height:42px;" onclick="toggleIconPicker()">
                                <span class="d-flex align-items-center gap-2">
                                    <i class="tio-{{ $card['icon_name'] ?? ($card['icon']['name'] ?? '') }}" id="icon_preview" style="font-size:20px"></i>
                                    <span id="icon_name_text">{{ $card['icon_name'] ?? ($card['icon']['name'] ?? 'Select icon') }}</span>
                                </span>
                                <i class="tio-arrow-drop-down" style="font-size:18px"></i>
                            </div>
                            <div class="icon-picker-dropdown" id="iconPickerDropdown">
                                <div class="icon-picker-search">
                                    <input type="text" placeholder="Search icons..." id="iconSearchInput" oninput="filterIcons(this.value)">
                                </div>
                                <div class="icon-picker-grid" id="iconPickerGrid">
                                    @php
                                    $iconCss = file_get_contents(public_path('assets/back-end/vendor/icon-set/style.css'));
                                    preg_match_all('/\.tio-([a-z0-9-]+):before/i', $iconCss, $matches);
                                    $iconNames = array_unique($matches[1]);
                                    sort($iconNames);
                                    @endphp
                                    @foreach($iconNames as $iconName)
                                    <div class="icon-picker-item" data-icon="{{ $iconName }}" onclick="selectIcon('{{ $iconName }}')">
                                        <i class="tio-{{ $iconName }}"></i>
                                        <span>{{ $iconName }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Color Picker --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">{{ translate('Icon Color') }}</label>
                            <div class="color-picker-wrapper">
                                <input type="color" id="colorPickerInput"
                                    value="{{ $card['icon_color'] ?? ($card['icon']['color'] ?? '#377dff') }}"
                                    onchange="updateColor(this.value)">
                                <input type="text" class="form-control" id="colorTextInput"
                                    value="{{ $card['icon_color'] ?? ($card['icon']['color'] ?? '') }}"
                                    placeholder="#377dff"
                                    oninput="updateColorFromText(this.value)"
                                    style="flex:1">
                                <div class="color-preview" id="colorPreviewBox"
                                    style="background-color: {{ $card['icon_color'] ?? ($card['icon']['color'] ?? '#377dff') }}">
                                    <i class="tio-palette" style="color:#fff; mix-blend-mode:difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Animation Picker --}}
                    <div class="col-md-4">
                        <div class="mb-3 animation-picker-wrapper">
                            <label class="form-label">{{ translate('Icon Animation') }}</label>
                            <div class="form-control d-flex align-items-center justify-content-between" id="animation_display"
                                style="cursor:pointer; min-height:42px;" onclick="toggleAnimationPicker()">
                                <span id="animation_text">{{ $card['icon_animation'] ?? ($card['icon']['animation'] ?? 'none') }}</span>
                                <i class="tio-arrow-drop-down" style="font-size:18px"></i>
                            </div>
                            <div class="animation-picker-dropdown" id="animationPickerDropdown">
                                @php
                                $animations = [
                                    'none' => 'None',
                                    'bounce' => 'Bounce',
                                    'pulse' => 'Pulse',
                                    'shake' => 'Shake',
                                    'spin' => 'Spin',
                                    'float' => 'Float',
                                    'swing' => 'Swing',
                                    'flip' => 'Flip',
                                    'rubberBand' => 'Rubber Band',
                                ];
                                @endphp
                                @foreach($animations as $value => $label)
                                <div class="animation-picker-item {{ ($card['icon_animation'] ?? ($card['icon']['animation'] ?? '')) == $value ? 'selected' : '' }}"
                                    data-animation="{{ $value }}" onclick="selectAnimation('{{ $value }}')">
                                    <span>{{ $label }}</span>
                                    @if($value !== 'none')
                                    <span class="anim-preview ani-{{ $value }}"><i class="tio-star"></i></span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Live preview --}}
                <div class="row mt-2 mb-3">
                    <div class="col-12">
                        <div class="p-3 rounded" style="background:#f8f9fa; display:flex; align-items:center; gap:12px;">
                            <span style="color:#677788; font-size:13px;">Preview:</span>
                            <div id="iconLivePreview" style="width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.08);">
                                <i class="tio-{{ $card['icon_name'] ?? ($card['icon']['name'] ?? '') }}"
                                   id="livePreviewIcon"
                                   style="font-size:24px; color:{{ $card['icon_color'] ?? ($card['icon']['color'] ?? '#377dff') }}"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.content-management.home', ['section' => 'why_choose_us']) }}" class="btn btn-secondary">{{ translate('Cancel') }}</a>
                    <button type="submit" class="btn btn--primary">{{ translate('Save changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('.summernote').summernote({
        height: 150,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']]
        ]
    });

    // Close pickers on outside click
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.icon-picker-wrapper').length) {
            $('#iconPickerDropdown').removeClass('show');
        }
        if (!$(e.target).closest('.animation-picker-wrapper').length) {
            $('#animationPickerDropdown').removeClass('show');
        }
    });
});

function toggleIconPicker() {
    var dd = document.getElementById('iconPickerDropdown');
    dd.classList.toggle('show');
    if (dd.classList.contains('show')) {
        document.getElementById('iconSearchInput').focus();
    }
}

function filterIcons(query) {
    var items = document.querySelectorAll('#iconPickerGrid .icon-picker-item');
    var q = query.toLowerCase();
    items.forEach(function(item) {
        var name = item.getAttribute('data-icon');
        item.style.display = name.indexOf(q) !== -1 ? '' : 'none';
    });
}

function selectIcon(name) {
    document.getElementById('icon_name_input').value = name;
    document.getElementById('icon_preview').className = 'tio-' + name;
    document.getElementById('icon_name_text').textContent = name;
    document.getElementById('livePreviewIcon').className = 'tio-' + name;
    document.querySelectorAll('.icon-picker-item').forEach(function(el) {
        el.classList.toggle('selected', el.getAttribute('data-icon') === name);
    });
    document.getElementById('iconPickerDropdown').classList.remove('show');
}

function updateColor(color) {
    document.getElementById('icon_color_input').value = color;
    document.getElementById('colorTextInput').value = color;
    document.getElementById('colorPreviewBox').style.backgroundColor = color;
    document.getElementById('livePreviewIcon').style.color = color;
}

function updateColorFromText(val) {
    if (/^#[0-9a-fA-F]{6}$/.test(val)) {
        document.getElementById('colorPickerInput').value = val;
        document.getElementById('colorPreviewBox').style.backgroundColor = val;
        document.getElementById('icon_color_input').value = val;
        document.getElementById('livePreviewIcon').style.color = val;
    }
}

function toggleAnimationPicker() {
    document.getElementById('animationPickerDropdown').classList.toggle('show');
}

function selectAnimation(name) {
    document.getElementById('icon_animation_input').value = name;
    document.getElementById('animation_text').textContent = name;
    var previewIcon = document.getElementById('livePreviewIcon');
    // Remove all animation classes
    previewIcon.className = previewIcon.className.replace(/\bani-\S+/g, '').trim();
    // Add the selected animation class
    if (name !== 'none') {
        previewIcon.classList.add('ani-' + name);
    }
    document.querySelectorAll('.animation-picker-item').forEach(function(el) {
        el.classList.toggle('selected', el.getAttribute('data-animation') === name);
    });
    document.getElementById('animationPickerDropdown').classList.remove('show');
}
</script>
@endpush
