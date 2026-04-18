@extends('layouts.back-end.app')

@section('title', translate('Return_Policy'))

@section('content')
<div class="content container-fluid">
    <div class="cms-admin-heading">
        <h1 class="cms-admin-heading__title h3">{{ translate('Return_Policy') }}</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="cms-admin-empty">
                <h2 class="h4 mb-3">{{ translate('cms_page_managed_elsewhere') }}</h2>
                <p class="mb-0">{{ translate('return_policy_management_note') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
