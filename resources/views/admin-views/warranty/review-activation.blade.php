@extends('layouts.back-end.app')
@section('title', translate('activation_reviews'))

@section('content')

<div class="content container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>{{ translate('pending_activation_reviews') }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100"
                    style="text-align: start;">
                    <thead class="thead-light thead-50 text-capitalize">
                        <tr>
                            <th>{{ translate('serial') }}</th>
                            <th>{{ translate('Product') }}</th>
                            <th>{{ translate('Customer') }}</th>
                            <th>{{ translate('flagged_reason') }}</th>
                            <th>{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                        <tr>
                            <td>
                                <a href="{{ route('admin.warranty.activation.view', $review->warranty_id) }}" class="text-primary">
                                    {{ $review->warranty->serial_number }}
                                </a>
                            </td>
                            <td>{{ $review->warranty->product->name ?? '—' }}</td>
                            <td>
                                {{ $review->warranty->activated_by_name }}<br>
                                <small class="text-muted">{{ $review->warranty->activated_by_phone }}</small>
                            </td>
                            <td>
                                <small>{{ $review->flagged_reason }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-2">

                                    <!-- Approve Button -->
                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal"
                                        data-target="#approveModal-{{ $review->id }}">
                                        {{ translate('Approve') }}
                                    </button>

                                    <!-- Reject Button -->
                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                        data-target="#rejectModal-{{ $review->id }}">
                                        {{ translate('Reject') }}
                                    </button>

                                </div>
                            </td>
                        </tr>

                        <!-- Approve Modal -->
                        <div class="modal fade" id="approveModal-{{ $review->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('admin.warranty.review.activation.approve', $review) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ translate('Approve Activation') }}</h5>
                                            <button type="button" class="close" data-dismiss="modal">×</button>
                                        </div>
                                        <div class="modal-body">
                                            <p>{{ translate('Are you sure you want to approve this activation?') }}</p>
                                            <div class="form-group">
                                                <label>{{ translate('Notes (Optional)') }}</label>
                                                <textarea name="review_notes" class="form-control" rows="3"
                                                    placeholder="{{ translate('Optional notes') }}"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-dismiss="modal">
                                                {{ translate('Cancel') }}
                                            </button>
                                            <button type="submit" class="btn btn--primary">
                                                {{ translate('Approve') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal-{{ $review->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('admin.warranty.review.activation.reject', $review) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ translate('Reject Activation') }}</h5>
                                            <button type="button" class="close" data-dismiss="modal">×</button>
                                        </div>
                                        <div class="modal-body">
                                            <p>{{ translate('Are you sure you want to reject this activation?') }}</p>
                                            <div class="form-group">
                                                <label>{{ translate('Rejection Reason (Required)') }}</label>
                                                <textarea name="review_notes" class="form-control" rows="3"
                                                    placeholder="{{ translate('Explain why this is rejected') }}" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-dismiss="modal">
                                                {{ translate('Cancel') }}
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                {{ translate('Reject') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4">
                <div class="px-4 d-flex justify-content-lg-end">
                    {!! $reviews->links() !!}
                </div>
            </div>

            @if($reviews->isEmpty())
            @include('layouts.back-end._empty-state', ['text'=>'no_record_found', 'image'=>'default'])
            @endif
        </div>
    </div>
</div>
@endsection