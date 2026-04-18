@extends('layouts.back-end.app')

@section('title', translate('SLA Policy Details'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 d-flex align-items-center gap-2">
            <i class="tio-filter-list"></i>
            {{ translate('Create_SLA_Policy') }}
        </h2>
    </div>
    <div class="card mb-3">
        <div class="px-3 py-4">

            <form action="{{ route('admin.sla.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>{{ translate('Entity Type') }}</label>
                    <select name="entity_type" class="form-control custom-select" required>
                        <option value="inbox_message">{{ translate('Inbox Message') }}</option>
                        <option value="lead">{{ translate('Lead') }}</option>
                        <option value="retail_deal">{{ translate('Retail Deal') }}</option>
                        <option value="wholesale_deal">{{ translate('Wholesale Deal') }}</option>
                        <option value="warranty_claim">{{ translate('Warranty Claim') }}</option>
                        <option value="complaint_ticket">{{ translate('complaint_Ticket') }}</option>
                        <option value="service_ticket">{{ translate('service_Ticket') }}</option>
                        <option value="career_ticket">{{ translate('Career Ticket') }}</option>
                        <option value="support_ticket">{{ translate('support_Ticket') }}</option>
                        <option value="retail_ticket">{{ translate('retail_ticket') }}</option>
                        <option value="wholesale_ticket">{{ translate('Wholesale Ticket') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ translate('Priority') }}</label>
                    <select name="priority" class="form-control custom-select" required>
                        <option value="low">{{ translate('Low') }}</option>
                        <option value="medium">{{ translate('Medium') }}</option>
                        <option value="high">{{ translate('High') }}</option>
                        <option value="urgent">{{ translate('Urgent') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ translate('Response Time (minutes)') }}</label>
                    <input type="number" name="response_time_minutes" class="form-control" required min="1">
                </div>
                <div class="form-group">
                    <label>{{ translate('Resolution Time (minutes)') }}</label>
                    <input type="number" name="resolution_time_minutes" class="form-control" required min="1">
                </div>
                <button type="submit" class="btn btn-primary">{{ translate('Create') }}</button>
            </form>
        </div>
    </div>

</div>
@endsection
