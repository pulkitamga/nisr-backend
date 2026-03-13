@extends('layouts.back-end.app')
@section('title', translate('claim_reviews'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>{{translate('pending_claim_reviews')}}</h5>
        </div>
        <div class="card-body p-0">
             <div class="table-responsive datatable-custom">

            <table
                style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{translate('claim_number')}}</th>
                            <th>{{translate('serial')}}</th>
                            <th>{{translate('description')}}</th>
                            <th>{{translate('action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $claim)
                        <tr>
                            <td>{{$claim->claim_number}}</td>
                            <td>{{$claim->serial_number}}</td>
                            <td>{{$claim->description}}</td>
                            <td>
                                <form action="{{route('admin.warranty.claim.decide', $claim)}}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="decision" value="approve">
                                    <input type="hidden" name="reason_code" value="approved_by_reviewer">
                                    <input type="hidden" name="reason_message" value="{{ translate('Approved during review queue processing.') }}">
                                    <button type="submit" class="btn btn-sm btn-success">{{ translate('Approve') }}</button>
                                </form>
                                <form action="{{route('admin.warranty.claim.decide', $claim)}}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="decision" value="reject">
                                    <input type="hidden" name="reason_code" value="rejected_by_reviewer">
                                    <input type="hidden" name="reason_message" value="{{ translate('Rejected during review queue processing.') }}">
                                    <button type="submit" class="btn btn-sm btn-danger">{{ translate('Reject') }}</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end">
                    {!! $reviews->links() !!}
                </div>
            </div>
            @if(count($reviews)==0)
            @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
            @endif
        </div>
    </div>
</div>
@endsection
