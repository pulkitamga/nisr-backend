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
            <form action="{{ route('admin.sla.update', $policy->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label>{{ translate('Entity Type') }}</label>
                    <select name="entity_type" class="form-control" required>
                        <option value="inbox_message" {{ $policy->entity_type == 'inbox_message' ? 'selected' : '' }}>{{ translate('Inbox Message') }}</option>
                        <option value="lead" {{ $policy->entity_type == 'lead' ? 'selected' : '' }}>{{ translate('Lead') }}</option>
                        <option value="retail_deal" {{ $policy->entity_type == 'retail_deal' ? 'selected' : '' }}>{{ translate('Retail Deal') }}</option>
                        <option value="wholesale_deal" {{ $policy->entity_type == 'wholesale_deal' ? 'selected' : '' }}>{{ translate('Wholesale Deal') }}</option>
                        <option value="warranty_claim" {{ $policy->entity_type == 'warranty_claim' ? 'selected' : '' }}>{{ translate('Warranty Claim') }}</option>
                        <option value="complaint_ticket" {{ $policy->entity_type == 'complaint_ticket' ? 'selected' : '' }}>{{ translate('complaint_Ticket') }}</option>
                        <option value="service_ticket" {{ $policy->entity_type == 'service_ticket' ? 'selected' : '' }}>{{ translate('service_Ticket') }}</option>
                        <option value="career_ticket" {{ $policy->entity_type == 'career_ticket' ? 'selected' : '' }}>{{ translate('Career Ticket') }}</option>
                        <option value="support_ticket" {{ $policy->entity_type == 'support_ticket' ? 'selected' : '' }}>{{ translate('support_Ticket') }}</option>
                        <option value="retail_ticket" {{ $policy->entity_type == 'retail_ticket' ? 'selected' : '' }}>{{ translate('retail_ticket') }}</option>
                        <option value="wholesale_ticket" {{ $policy->entity_type == 'wholesale_ticket' ? 'selected' : '' }}>{{ translate('Wholesale Ticket') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ translate('Priority') }}</label>
                    <select name="priority" class="form-control" required>
                        <option value="low" {{ $policy->priority == 'low' ? 'selected' : '' }}>{{ translate('Low') }}</option>
                        <option value="medium" {{ $policy->priority == 'medium' ? 'selected' : '' }}>{{ translate('Medium') }}</option>
                        <option value="high" {{ $policy->priority == 'high' ? 'selected' : '' }}>{{ translate('High') }}</option>
                        <option value="urgent" {{ $policy->priority == 'urgent' ? 'selected' : '' }}>{{ translate('Urgent') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ translate('Response Time (minutes)') }}</label>
                    <input type="number" name="response_time_minutes" class="form-control" value="{{ $policy->response_time_minutes }}" required min="1">
                </div>
                <div class="form-group">
                    <label>{{ translate('Resolution Time (minutes)') }}</label>
                    <input type="number" name="resolution_time_minutes" class="form-control" value="{{ $policy->resolution_time_minutes }}" required min="1">
                </div>
                <div class="form-group">
                    <label>{{ translate('Active') }}</label>
                    <input type="checkbox" name="is_active" value="1" {{ $policy->is_active ? 'checked' : '' }}>
                </div>
                <button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
