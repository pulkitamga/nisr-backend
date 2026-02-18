@extends('layouts.back-end.app')
@section('title', translate('activation_report'))

@section('content')
<div class="content container-fluid">
   <div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5>{{translate('activation_rate')}}</h5>
                <h3 class="text-primary">{{ number_format($rate, 2) }}%</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    @foreach($methodCounts as $m)
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6 class="mb-1 text-uppercase">{{ $m['label'] }}</h6>
                <h3 class="mb-2">{{ $m['count'] }}</h3>
                <span class="badge badge-soft-primary">{{ $m['percentage'] }}%</span>
            </div>
        </div>
    </div>
    @endforeach
</div>

</div>
@endsection

@push('script')
<script>
    // Search filter for table
    $('.form-control').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
</script>
@endpush