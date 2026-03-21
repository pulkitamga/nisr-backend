@extends('layouts.back-end.app')
@section('title', translate('Transfer Warranty'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>{{ translate('Transfer Warranty') }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.warranty.transfer') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>{{ translate('Serial Number') }}</label>
                    <div class="input-group">
                        <input type="text" id="transferWarrantySerialNumber" name="serial_number" class="form-control" placeholder="{{ translate('Enter serial number') }}" required>
                        <div class="input-group-append">
                            @include('partials.serial-scan-button', ['targetInput' => '#transferWarrantySerialNumber'])
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ translate('From Branch') }}</label>
                    <select name="from_branch_id" class="form-control" required>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ translate('To Branch') }}</label>
                    <select name="to_branch_id" class="form-control" required>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>{{ translate('Note') }}</label>
                    <textarea name="note" class="form-control" placeholder="{{ translate('Optional note') }}"></textarea>
                </div>
                <button type="submit" class="btn btn--primary">{{ translate('Transfer') }}</button>
                <a href="{{ route('admin.warranty.dashboard') }}" class="btn btn-secondary">{{ translate('Cancel') }}</a>
            </form>
        </div>
    </div>
</div>

@include('partials.serial-scanner-assets')
@endsection
