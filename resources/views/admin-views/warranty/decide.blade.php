<!-- resources/views/admin-views/warranty/decide.blade.php -->
@extends('layouts.back-end.app')

@section('title', translate('Decide Claim'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>{{ translate('Decide on Claim') }}: {{ $claim->claim_number }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.warranty.claim.decide', $claim->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>{{ translate('Decision') }}</label>
                    <select name="decision" class="form-control" required>
                        <option value="approve">{{ translate('Approve') }}</option>
                        <option value="reject">{{ translate('Reject') }}</option>
                        <option value="waiting_customer">{{ translate('Waiting for Customer') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ translate('reason_code') }}</label>
                    <select name="reason_code" class="form-control" required>
                        <option value="valid_issue">{{ translate('Valid Issue') }}</option>
                        <option value="invalid_claim">{{ translate('Invalid Claim') }}</option>
                        <option value="need_more_info">{{ translate('Need More Info') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ translate('Reason Message') }}</label>
                    <textarea name="reason_message" class="form-control" required></textarea>
                </div>
                <div class="form-group d-none" id="repair_replace_group">
                    <label>{{ translate('Repair or Replace') }}</label>
                    <select name="repair_or_replace" class="form-control">
                        <option value="repair">{{ translate('Repair') }}</option>
                        <option value="replace">{{ translate('Replace') }}</option>
                    </select>
                </div>
                <div class="form-group d-none" id="replacement_mode_group">
                    <label>{{ translate('Replacement Mode') }}</label>
                    <select name="replacement_mode" class="form-control">
                        <option value="remaining">{{ translate('Remaining Term') }}</option>
                        <option value="full">{{ translate('Full Term') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ translate('Admin Override') }}</label>
                    <input type="checkbox" name="is_admin_override" value="1">
                </div>
                <div class="form-group d-none" id="override_reason_group">
                    <label>{{ translate('Override Reason') }}</label>
                    <textarea name="override_reason" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    document.querySelector('select[name="decision"]').addEventListener('change', function() {
        document.getElementById('repair_replace_group').classList.toggle('d-none', this.value !== 'approve');
        document.getElementById('replacement_mode_group').classList.add('d-none');
    });
    document.querySelector('select[name="repair_or_replace"]').addEventListener('change', function() {
        document.getElementById('replacement_mode_group').classList.toggle('d-none', this.value !== 'replace');
    });
    document.querySelector('input[name="is_admin_override"]').addEventListener('change', function() {
        document.getElementById('override_reason_group').classList.toggle('d-none', !this.checked);
    });
</script>
@endpush
