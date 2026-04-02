@php
    $listHeaderActions = $listHeaderActions ?? [];
@endphp

<div class="card-header gap-3 align-items-center">
    <h5 class="mb-0 me-auto">
        {{ $listHeaderTitle }}
        <span class="badge badge-soft-dark radius-50 fz-14 ms-1">{{ $listHeaderTotal }}</span>
    </h5>
    @if($listHeaderActions)
        <div class="crm-card-header__actions">
            @foreach($listHeaderActions as $action)
                @php
                    $type = $action['type'] ?? 'button';
                    $class = $action['class'] ?? 'btn btn-outline--primary text-nowrap';
                    $attributes = $action['attributes'] ?? [];
                @endphp

                @if($type === 'export')
                    <a
                        href="{{ $action['url'] }}"
                        class="{{ $class }}"
                        data-crm-export-button="true"
                        data-base-url="{{ $action['url'] }}"
                        data-form="#{{ $action['form_id'] ?? 'crm-list-toolbar' }}"
                    >
                        <img width="14" src="{{ dynamicAsset(path: $action['icon_path'] ?? 'public/assets/back-end/img/excel.png') }}" alt="" class="excel">
                        <span class="ps-2">{{ $action['label'] ?? translate('export') }}</span>
                    </a>
                @elseif(!empty($action['href']))
                    <a
                        href="{{ $action['href'] }}"
                        class="{{ $class }}"
                        @foreach($attributes as $attribute => $attributeValue)
                            {{ $attribute }}="{{ $attributeValue }}"
                        @endforeach
                    >
                        @if(!empty($action['icon_html']))
                            {!! $action['icon_html'] !!}
                        @endif
                        <span @if(!empty($action['icon_html'])) class="ps-2" @endif>{{ $action['label'] }}</span>
                    </a>
                @else
                    <button
                        type="{{ $action['button_type'] ?? 'button' }}"
                        class="{{ $class }}"
                        @foreach($attributes as $attribute => $attributeValue)
                            {{ $attribute }}="{{ $attributeValue }}"
                        @endforeach
                    >
                        @if(!empty($action['icon_html']))
                            {!! $action['icon_html'] !!}
                        @endif
                        <span @if(!empty($action['icon_html'])) class="ps-2" @endif>{{ $action['label'] }}</span>
                    </button>
                @endif
            @endforeach
        </div>
    @endif
</div>
