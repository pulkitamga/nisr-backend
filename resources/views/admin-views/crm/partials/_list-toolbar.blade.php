@php
    $toolbarId = $toolbarId ?? 'crm-list-toolbar';
    $toolbarAction = $toolbarAction ?? url()->current();
    $toolbarResetUrl = $toolbarResetUrl ?? url()->current();
    $toolbarFields = $toolbarFields ?? [];
    $toolbarSummary = $toolbarSummary ?? [];
    $toolbarCardClass = $toolbarCardClass ?? 'card mb-4';
    $toolbarBodyClass = $toolbarBodyClass ?? 'card-body';
    $toolbarApplyText = $toolbarApplyText ?? translate('apply_filters');
    $toolbarResetText = $toolbarResetText ?? translate('reset');
@endphp

<div class="{{ $toolbarCardClass }}">
    <div class="{{ $toolbarBodyClass }}">
        <form id="{{ $toolbarId }}" action="{{ $toolbarAction }}" method="GET" class="crm-list-toolbar">
            <div class="row g-3 align-items-end">
                @foreach($toolbarFields as $field)
                    @php
                        $type = $field['type'] ?? 'text';
                        $name = $field['name'] ?? null;
                        $label = $field['label'] ?? null;
                        $value = $field['value'] ?? null;
                        $colClass = $field['col_class'] ?? 'col-xl-3 col-lg-6';
                        $inputClass = $field['input_class'] ?? 'form-control';
                        $placeholder = $field['placeholder'] ?? null;
                        $autocomplete = $field['autocomplete'] ?? null;
                        $attributes = $field['attributes'] ?? [];
                        $options = $field['options'] ?? [];
                    @endphp
                    <div class="{{ $colClass }}">
                        @if($label)
                            <label class="form-label">{{ $label }}</label>
                        @endif

                        @if($type === 'daterange')
                            <div class="position-relative">
                                <span class="tio-calendar icon-absolute-on-right"></span>
                                <input
                                    type="text"
                                    name="{{ $name }}"
                                    class="{{ $inputClass }}"
                                    value="{{ $value }}"
                                    @if($placeholder) placeholder="{{ $placeholder }}" @endif
                                    @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                                    @foreach($attributes as $attribute => $attributeValue)
                                        {{ $attribute }}="{{ $attributeValue }}"
                                    @endforeach
                                >
                            </div>
                        @elseif($type === 'select')
                            <select
                                name="{{ $name }}"
                                class="{{ $inputClass }}"
                                @foreach($attributes as $attribute => $attributeValue)
                                    {{ $attribute }}="{{ $attributeValue }}"
                                @endforeach
                            >
                                @foreach($options as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" {{ (string) $value === (string) $optionValue ? 'selected' : '' }}>
                                        {{ $optionLabel }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif($type === 'search')
                            <div class="input-group input-group-merge input-group-custom">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input
                                    type="search"
                                    id="{{ $field['id'] ?? 'datatableSearch_' }}"
                                    name="{{ $name }}"
                                    class="{{ $inputClass }}"
                                    value="{{ $value }}"
                                    @if($placeholder) placeholder="{{ $placeholder }}" @endif
                                    aria-label="{{ $field['aria_label'] ?? $placeholder }}"
                                    @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                                    @foreach($attributes as $attribute => $attributeValue)
                                        {{ $attribute }}="{{ $attributeValue }}"
                                    @endforeach
                                >
                            </div>
                        @else
                            <input
                                type="{{ $type }}"
                                name="{{ $name }}"
                                class="{{ $inputClass }}"
                                value="{{ $value }}"
                                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                                @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                                @foreach($attributes as $attribute => $attributeValue)
                                    {{ $attribute }}="{{ $attributeValue }}"
                                @endforeach
                            >
                        @endif
                    </div>
                @endforeach

                @if($toolbarSummary)
                    <div class="col-12">
                        <div class="crm-list-toolbar__summary {{ $toolbarSummaryClass ?? '' }}">
                            @foreach($toolbarSummary as $chip)
                                <span class="crm-list-toolbar__chip {{ !empty($chip['muted']) ? 'crm-list-toolbar__chip--muted' : '' }}">
                                    @if(!empty($chip['label']))
                                        <span class="crm-list-toolbar__chip-label">{{ $chip['label'] }}</span>
                                    @endif
                                    <span>{{ $chip['value'] }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="col-12">
                    <div class="crm-list-toolbar__actions">
                        <a href="{{ $toolbarResetUrl }}" class="btn btn-secondary px-5">
                            {{ $toolbarResetText }}
                        </a>
                        <button type="submit" class="btn btn--primary">{{ $toolbarApplyText }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@once
    @push('script')
        <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/crm-list.js') }}"></script>
    @endpush
@endonce
