@extends('layouts.back-end.app')
@section('title', translate('manual_activate'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>{{translate('manual_warranty_activation')}}</h5>
        </div>
        <div class="card-body">
            <form action="{{route('admin.warranty.activation.manual')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>{{translate('serial_number')}}</label>
                    <div class="input-group">
                        <input type="text" id="manualActivationSerialNumber" name="serial_number" value="{{ old('serial_number', $prefillSerial ?? '') }}" class="form-control" required>
                        <div class="input-group-append">
                            @include('partials.serial-scan-button', ['targetInput' => '#manualActivationSerialNumber'])
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>{{translate('purchase_date')}}</label>
                    <input type="date" name="purchase_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>{{translate('reason')}}</label>
                    <textarea name="reason" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label>{{translate('docs')}}</label>
                    <input type="file" name="docs" class="form-control" accept="pdf,jpg">
                </div>
                <button type="submit" class="btn btn--primary">{{translate('activate')}}</button>
            </form>
        </div>
    </div>
</div>

@include('partials.serial-scanner-assets')
@endsection
