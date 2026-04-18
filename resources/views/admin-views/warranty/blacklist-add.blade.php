@extends('layouts.back-end.app')
@section('title', translate('Add to Blacklist'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>{{ translate('Add to Blacklist') }}</h5>
        </div>
        @if ($errors->any())
        @foreach ($errors->all() as $error)
        <script>
            toastr.error("{{ $error }}");
        </script>
        @endforeach
        @endif

        <div class="card-body">
            <form action="{{ route('admin.warranty.blacklist.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>{{ translate('serial_number') }}</label>
                    <div class="input-group">
                        <input type="text" id="blacklistSerialNumber" name="serial_number" class="form-control" placeholder="{{ translate('Enter Serial Number') }}" required>
                        <div class="input-group-append">
                            @include('partials.serial-scan-button', ['targetInput' => '#blacklistSerialNumber'])
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label>{{ translate('Reason') }}</label>
                    <textarea name="reason" class="form-control" placeholder="{{ translate('Enter reason') }}" required></textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn--primary">{{ translate('Add to Blacklist') }}</button>
                    <a href="{{ route('admin.warranty.blacklist') }}" class="btn btn-secondary">{{ translate('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.serial-scanner-assets')
@endsection
