@extends('layouts.back-end.app')
@section('title', translate('claims_report'))

@section('content')
<div class="content container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>{{translate('claim_rate')}}</h5>
                    <h3>{{$reports['claim_rate']}}%</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
      <div class="table-responsive datatable-custom">

            <table
                style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{translate('status')}}</th>
                        <th>{{translate('count')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($claimsByStatus as $status)
                    <tr>
                        <td>{{$status->status}}</td>
                        <td>{{$status->count}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
         <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end">
                    {!! $claimsByStatus->links() !!}
                </div>
            </div>
              @if(count($claimsByStatus)==0)
        @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
        @endif
    </div>
</div>
@endsection