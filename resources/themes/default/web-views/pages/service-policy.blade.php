@extends('layouts.front-end.app')

@section('title', translate('service_policy'))

@section('content')
@php
    $setting = getWebConfig('service_policy');

    $translatedContent = getBusinessSettingTranslation(
        'service_policy',
        'value',
        $setting ?? ''
    );
@endphp
<div class="container py-5 rtl text-align-direction">
    <h2 class="text-center mb-3 headerTitle">{{ translate('service_policy') }}</h2>
    <div class="card __card">
        <div class="card-body text-justify">
            {!! $translatedContent !!}
        </div>
    </div>
</div>
@endsection
