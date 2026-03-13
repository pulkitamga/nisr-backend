@extends('layouts.back-end.app')
@section('title', translate('Diagnose Claim'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>{{ translate('Diagnose Claim') }}: {{ $claim->claim_number }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('warranty.claim.diagnose', $claim) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>{{ translate('Diagnosis Notes') }}</label>
                    <textarea name="diagnosis_notes" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label>{{ translate('Action') }}</label>
                    <select name="repair_or_replace" class="form-control" required>
                        <option value="repair">{{ translate('Repair') }}</option>
                        <option value="replace">{{ translate('Replace') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ translate('Tamper Detected') }}</label>
                    <input type="checkbox" name="tamper_detected" value="1">
                </div>
                <div class="form-group">
                    <label>{{ translate('Inspection Fee') }}</label>
                    <input type="number" name="inspection_fee" class="form-control" step="0.01" min="0">
                </div>
                <button type="submit" class="btn btn--primary">{{ translate('Submit Diagnosis') }}</button>
                <a href="{{ route('warranty.claim.view', $claim) }}" class="btn btn-secondary">{{ translate('Cancel') }}</a>
            </form>
        </div>
    </div>
</div>
@endsection