@extends('layouts.back-end.app')
@section('title', translate('warranty_reports'))

@section('content')
<div class="content container-fluid">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>{{translate('claim_rate')}}</h5>
                    <h3>{{$reports['claim_rate']}}%</h3>
                </div>
            </div>
        </div>
        <!-- More KPI cards for auto-approved %, SLA, etc. -->
    </div>

    <div class="card">
        <div class="card-header">
            <h5>{{translate('claims_by_status')}}</h5>
            <select class="form-control w-auto">
                <option>{{translate('last_30_days')}}</option>
                <option>{{translate('last_year')}}</option>
            </select>
        </div>
        <div class="card-body p-0">
      <div class="table-responsive datatable-custom">

            <table

                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('Status')}}</th>
                            <th>{{translate('count')}}</th>
                            <th>{{translate('percentage')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statusReports as $status => $data)
                        <tr>
                            <td>{{translate($status)}}</td>
                            <td>{{$data['count']}}</td>
                            <td>{{$data['percentage']}}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
             <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end">
                    {!! $statusReports->links() !!}
                </div>
            </div>
              @if(count($statusReports)==0)
        @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
        @endif
        </div>
    </div>
</div>
@endsection