@extends('layouts.back-end.app')

@section('title', translate('Massage Details'))

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
                    <label>Entity Type</label>
                    <select name="entity_type" class="form-control" required>
                        <option value="inbox_message" {{ $policy->entity_type == 'inbox_message' ? 'selected' : '' }}>Inbox Message</option>
                        <option value="lead" {{ $policy->entity_type == 'lead' ? 'selected' : '' }}>Lead</option>
                        <option value="retail_deal" {{ $policy->entity_type == 'retail_deal' ? 'selected' : '' }}>Retail Deal</option>
                        <option value="wholesale_deal" {{ $policy->entity_type == 'wholesale_deal' ? 'selected' : '' }}>Wholesale Deal</option>
                        <option value="warranty_claim" {{ $policy->entity_type == 'warranty_claim' ? 'selected' : '' }}>Warranty Claim</option>
                        <option value="complaint_ticket" {{ $policy->entity_type == 'complaint_ticket' ? 'selected' : '' }}>Complaint Ticket</option>
                        <option value="service_ticket" {{ $policy->entity_type == 'service_ticket' ? 'selected' : '' }}>Service Ticket</option>
                        <option value="career_ticket" {{ $policy->entity_type == 'career_ticket' ? 'selected' : '' }}>Career Ticket</option>
                        <option value="support_ticket" {{ $policy->entity_type == 'support_ticket' ? 'selected' : '' }}>Support Ticket</option>
                        <option value="retail_ticket" {{ $policy->entity_type == 'retail_ticket' ? 'selected' : '' }}>Retail Ticket</option>
                        <option value="wholesale_ticket" {{ $policy->entity_type == 'wholesale_ticket' ? 'selected' : '' }}>Wholesale Ticket</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority" class="form-control" required>
                        <option value="low" {{ $policy->priority == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ $policy->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ $policy->priority == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ $policy->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Response Time (minutes)</label>
                    <input type="number" name="response_time_minutes" class="form-control" value="{{ $policy->response_time_minutes }}" required min="1">
                </div>
                <div class="form-group">
                    <label>Resolution Time (minutes)</label>
                    <input type="number" name="resolution_time_minutes" class="form-control" value="{{ $policy->resolution_time_minutes }}" required min="1">
                </div>
                <div class="form-group">
                    <label>Active</label>
                    <input type="checkbox" name="is_active" value="1" {{ $policy->is_active ? 'checked' : '' }}>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection