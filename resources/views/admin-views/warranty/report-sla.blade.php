@extends('layouts.back-end.app')
@section('title', translate('sla_report'))

@section('content')
<div class="content container-fluid">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>{{translate('sla_compliance')}}</h5>
                    <h3 class="text-success">{{$sla['compliance']}}%</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5>{{translate('breached_claims')}}</h5>
                    <h3 class="text-danger">{{$breached}}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>{{translate('sla_details')}}</h5>
        </div>
        <div class="table-responsive datatable-custom">

            <table
                style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{translate('sla_type')}}</th>
                        <th>{{translate('claim_number')}}</th>
                        <th>{{translate('serial_no')}}</th>
                        <th>{{translate('due_date')}}</th>
                        <th>{{translate('status')}}</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach($slaDetails as $detail)
                    <tr>
                        <td>{{$detail->type}}</td>
                        <td>{{$detail->claim_number}}</td>
                        <td>{{$detail->serial_number}}</td>
                        <td>{{$detail->due_date}}</td>
                        <td>
                            <span class="badge badge-soft-{{ $detail->is_within_sla ? 'success' : 'danger' }}">
                                {{ $detail->is_within_sla ? translate('on_track') : translate('breached') }}
                            </span>
                        </td>
                    </tr>

                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $slaDetails->links() !!}
            </div>
        </div>
        @if(count($slaDetails)==0)
        @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
        @endif
    </div>
</div>
@endsection