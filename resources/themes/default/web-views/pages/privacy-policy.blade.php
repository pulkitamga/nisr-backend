@extends('layouts.front-end.app')

@section('title', translate('privacy_policy'))

@section('content')

@php
$setting = getWebConfig('privacy_policy');

$settingValue = json_decode($setting ?? '{}', true);

$translatedContent = getBusinessSettingTranslation(
'privacy_policy',
'value',
$settingValue ?? ''
);
@endphp
<div class="container py-5 rtl text-align-direction">
    <h2 class="text-center mb-3 headerTitle">{{translate('privacy_policy')}}</h2>
    <div class="card __card">
        <div class="card-body text-justify">
            {!! $translatedContent !!}
        </div>
    </div>
</div>
@endsection