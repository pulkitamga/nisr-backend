@extends('layouts.back-end.app')

@section('title', translate('Service Ticket Details'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<style>
    .bidi-auto {
        unicode-bidi: plaintext;
    }
    .bidi-ltr {
        direction: ltr;
        unicode-bidi: isolate;
        display: inline-block;
        text-align: left;
    }
</style>
@endpush

@section('content')
@php
    $pageDirection = Session::get('direction') === 'rtl' ? 'rtl' : 'ltr';
    $localizedTicketPriority = translate($supportTicket->priority);
    $localizedTicketStatus = translate($supportTicket->status_details->name ?? $supportTicket->status);
@endphp
<div class="content container-fluid" dir="{{ $pageDirection }}">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/support_ticket.png') }}" alt="">
            {{ translate('Service Ticket Details') }}
        </h2>
    </div>

    <div class="row">
        <div class="row col-12 d-flex align-items-stretch mb-4">
            <div class="col-lg-6 col-md-12">
                <div class="card mb-4 shadow-sm h-100 detail-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ translate('Customer Details') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($supportTicket->customer)
                        <p><strong>{{ translate('Name') }}:</strong> <span class="bidi-auto">{{ $supportTicket->customer->f_name ?? '' }}
                            {{ $supportTicket->customer->l_name ?? '' }}</span>
                        </p>
                        <p><strong>{{ translate('Email') }}:</strong>
                            <span class="bidi-ltr">{{ $supportTicket->customer->email ?? translate('Not Available') }}</span>
                        </p>
                        <p><strong>{{ translate('Phone') }}:</strong>
                            <span class="bidi-ltr">{{ $supportTicket->customer->phone ?? translate('Not Available') }}</span>
                        </p>
                        @else
                        <p>{{ translate('Customer Not Found') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Service Details Card -->
            <div class="col-lg-6 col-md-12">
                <div class="card mb-4 shadow-sm h-100 detail-card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ translate('Service Details') }}</h5>
                    </div>
                    <div class="card-body">
                        @php
                        $service = $supportTicket->latestServiceJob?->service;
                        @endphp
                        <p><strong>{{ translate('Service') }}:</strong>
                            <span class="bidi-auto">{{ $service ? $service->title : translate('No Service Picked') }}</span>
                        </p>
                        <p><strong>{{ translate('Ticket ID') }}:</strong> <span class="bidi-ltr">{{ $supportTicket->id }}</span></p>
                        <p><strong>{{ translate('Subject') }}:</strong>
                            <span class="bidi-auto">{{ $supportTicket->subject ?? translate('No Subject') }}</span>
                        </p>
                        <p><strong>{{ translate('Priority') }}:</strong> <span class="bidi-auto">{{ $localizedTicketPriority }}</span></p>
                        <p><strong>{{ translate('Status') }}:</strong>
                            <span class="bidi-auto">{{ $localizedTicketStatus }}</span>
                        </p>
                        <p><strong>{{ translate('Created At') }}:</strong>
                            <span class="bidi-ltr">{{ $supportTicket->created_at->format('d M, Y H:i A') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            @include('admin-views.crm.partials.escalation-panel', ['escalations' => $supportTicket->escalations ?? collect()])
        </div>

        <!-- Related Actions Card -->
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ translate('Related Actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="inline-page-menu my-4">
                        <ul class="list-unstyled d-flex gap-2" id="actionTabs">
                            <li class="active">
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse"
                                    data-bs-target="#collapseActivity-{{ $supportTicket->id }}"
                                    data-collapse-target="activity">
                                    {{ translate('Activity') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseService-{{ $supportTicket->id }}" data-collapse-target="service">
                                    {{ translate('Service') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse"
                                    data-bs-target="#collapseEstimate-{{ $supportTicket->id }}"
                                    data-collapse-target="estimate">
                                    {{ translate('Estimate') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse"
                                    data-bs-target="#collapseInvoice-{{ $supportTicket->id }}"
                                    data-collapse-target="invoice">
                                    {{ translate('Invoice') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse"
                                    data-bs-target="#collapseChangeOrder-{{ $supportTicket->id }}"
                                    data-collapse-target="change-order">
                                    {{ translate('Change Order') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse"
                                    data-bs-target="#collapseCancellation-{{ $supportTicket->id }}"
                                    data-collapse-target="cancellation">
                                    {{ translate('Cancellation') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Activity Section -->
                    @php
                    $ticketActivities = collect($supportTicket->serviceJobs ?? [])
                        ->flatMap(fn($job) => $job->activities ?? collect())
                        ->sortByDesc('created_at')
                        ->values();
                    @endphp
                    <div class="collapse show" id="collapseActivity-{{ $supportTicket->id }}">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Activities') }}</h6>
                            </div>
                            <div class="table-responsive datatable-custom">
                                <table
                                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle w-100">
                                    <thead class="thead-light text-capitalize">
                                        <tr>
                                            <th>{{ translate('SL') }}</th>
                                            <th>{{ translate('Description') }}</th>
                                            <th>{{ translate('Created By') }}</th>
                                            <th>{{ translate('Created At') }}</th>
                                            <th class="text-center">{{ translate('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ticketActivities as $key => $activity)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $activity->description }}</td>
                                            <td>{{ $activity->createdBy ? $activity->createdBy->name : translate('System') }}
                                            </td>
                                            <td>{{ $activity->created_at->format('d M, Y H:i A') }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-info view-details"
                                                    data-details='@json($activity)' data-bs-toggle="modal"
                                                    data-bs-target="#activityDetailsModal">
                                                    <i class="tio-invisible"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">{{ translate('No activity found') }}</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Estimate Section -->
                    <div class="collapse" id="collapseEstimate-{{ $supportTicket->id }}">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Estimates') }}</h6>
                            </div>
                            <div class="table-responsive datatable-custom">
                                <table
                                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle w-100">
                                    <thead class="thead-light text-capitalize">
                                        <tr>
                                            <th>{{ translate('SL') }}</th>
                                            <th>{{ translate('Service') }}</th>
                                            <th>{{ translate('Subtotal') }}</th>
                                            <th>{{ translate('Tax') }}</th>
                                            <th>{{ translate('Total') }}</th>
                                            <th>{{ translate('Created At') }}</th>
                                            <th class="text-center">{{ translate('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($supportTicket->estimates ?? [] as $key => $estimate)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $estimate->service ? $estimate->service->title : translate('N/A') }}
                                            </td>
                                            <td> {{ webCurrencyConverter(amount:  $estimate->subtotal) }}</td>
                                            <td> {{ webCurrencyConverter(amount:  $estimate->tax) }}</td>
                                            <td> {{ webCurrencyConverter(amount:  $estimate->total) }}</td>
                                            <td>{{ $estimate->created_at->format('d M, Y H:i A') }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-info view-details"
                                                    data-details='{{ json_encode($estimate) }}' data-bs-toggle="modal"
                                                    data-bs-target="#estimateDetailsModal">
                                                    <i class="tio-invisible"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Section -->
                    <div class="collapse" id="collapseInvoice-{{ $supportTicket->id }}">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Invoices') }}</h6>
                            </div>
                            <div class="table-responsive datatable-custom">
                                <table
                                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle w-100">
                                    <thead class="thead-light text-capitalize">
                                        <tr>
                                            <th>{{ translate('SL') }}</th>
                                            <th>{{ translate('Subtotal') }}</th>
                                            <th>{{ translate('Tax') }}</th>
                                            <th>{{ translate('Total') }}</th>
                                            <th>{{ translate('Payment Link') }}</th>
                                            <th>{{ translate('Payment Status') }}</th>
                                            <th>{{ translate('Generated At') }}</th>
                                            <th>{{ translate('Link Expires At') }}</th>
                                            <th class="text-center">{{ translate('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($supportTicket->invoices ?? [] as $key => $invoice)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td> {{ webCurrencyConverter(amount:  $invoice->subtotal) }}</td>
                                            <td> {{ webCurrencyConverter(amount:  $invoice->tax) }}</td>
                                            <td> {{ webCurrencyConverter(amount:  $invoice->total) }}</td>
                                            <td><a href="{{ $invoice->payment_link }}">{{ $invoice->payment_link }}</a></td>
                                            <td>{{ translate($invoice->payment_status) }}</td>
                                            <td>{{ $invoice->generated_at->format('d M, Y H:i A') }}</td>
                                            <td>{{ $invoice->payment_link_expires_at?->format('d M, Y H:i A') ?? '-' }}</td>
                                            <td class="text-center">
                                                @if(in_array($invoice->payment_status, ['pending', 'expired'], true))
                                                <form method="POST"
                                                    action="{{ route('admin.support-ticket.service.invoice-remind') }}"
                                                    class="d-inline-block">
                                                    @csrf
                                                    <input type="hidden" name="ticket_id" value="{{ $supportTicket->id }}">
                                                    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary"
                                                        title="{{ translate('Send Reminder') }}">
                                                        <i class="tio-email"></i>
                                                    </button>
                                                </form>
                                                @endif
                                                <button class="btn btn-sm btn-outline-info view-details"
                                                    data-details='{{ json_encode($invoice) }}' data-bs-toggle="modal"
                                                    data-bs-target="#invoiceDetailsModal">
                                                    <i class="tio-invisible"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Change Order Section -->
                    <div class="collapse" id="collapseChangeOrder-{{ $supportTicket->id }}">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Change Orders') }}</h6>
                            </div>
                            <div class="table-responsive datatable-custom">
                                <table
                                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle w-100">
                                    <thead class="thead-light text-capitalize">
                                        <tr>
                                            <th>{{ translate('SL') }}</th>
                                            <th>{{ translate('Additional Charges') }}</th>
                                            <th>{{ translate('Description') }}</th>
                                            <th>{{ translate('Created At') }}</th>
                                            <th class="text-center">{{ translate('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($supportTicket->changeOrders ?? [] as $key => $changeOrder)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td> {{ webCurrencyConverter(amount:  $changeOrder->additional_charges) }}</td>
                                            <td>{{ $changeOrder->description }}</td>
                                            <td>{{ $changeOrder->created_at->format('d M, Y H:i A') }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-info view-details"
                                                    data-details='{{ json_encode($changeOrder) }}'
                                                    data-bs-toggle="modal" data-bs-target="#changeOrderDetailsModal">
                                                    <i class="tio-invisible"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellation Section -->
                    <div class="collapse" id="collapseCancellation-{{ $supportTicket->id }}">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Cancellations') }}</h6>
                            </div>
                            <div class="table-responsive datatable-custom">
                                <table
                                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle w-100">
                                    <thead class="thead-light text-capitalize">
                                        <tr>
                                            <th>{{ translate('SL') }}</th>
                                            <th>{{ translate('Reason') }}</th>
                                            <th>{{ translate('Fee Amount') }}</th>
                                            <th>{{ translate('Refund Amount') }}</th>
                                            <th>{{ translate('Created At') }}</th>
                                            <th class="text-center">{{ translate('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($supportTicket->cancellations ?? [] as $key => $cancellation)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $cancellation->cancellation_reason }}</td>
                                            <td> {{ webCurrencyConverter(amount: $cancellation->fee_amount) }}</td>
                                            <td> {{ webCurrencyConverter(amount: $cancellation->refund_amount) }}</td>
                                            <td>{{ $cancellation->created_at->format('d M, Y H:i A') }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-info view-details"
                                                    data-details='{{ json_encode($cancellation) }}'
                                                    data-bs-toggle="modal" data-bs-target="#cancellationDetailsModal">
                                                    <i class="tio-invisible"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="collapse" id="collapseService-{{ $supportTicket->id }}">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Services') }}</h6>
                            </div>
                            <div class="table-responsive datatable-custom">
                                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle w-100">
                                    <thead class="thead-light text-capitalize">
                                        <tr>
                                            <th>{{ translate('SL') }}</th>
                                            <th>{{ translate('Technician') }}</th>
                                            <th>{{ translate('Status') }}</th>
                                            <th>{{ translate('Remark') }}</th>
                                            <th>{{ translate('Attachments') }}</th>
                                            <th>{{ translate('Customer Signature') }}</th>
                                            <th>{{ translate('Created At') }}</th>
                                            <th class="text-center">{{ translate('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($supportTicket->serviceJobs ?? [] as $key => $serviceJob)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $serviceJob->technician ? $serviceJob->technician->name : translate('N/A') }}</td>
                                            <td>{{ translate($serviceJob->status) }}</td>
                                            <td>{{ $serviceJob->remarks }}</td>

                                            <td>
                                                @php
                                                $attachments = is_array($serviceJob->attachments)
                                                ? $serviceJob->attachments
                                                : json_decode($serviceJob->attachments, true);
                                                @endphp

                                                @if(!empty($attachments))
                                                <button type="button" class="btn btn-sm btn-outline-primary view-attachments-btn"
                                                    data-attachments='@json($attachments)'>
                                                    {{ translate('View All') }}
                                                </button>
                                                @else
                                                <span class="text-muted">{{ translate('No Attachment') }}</span>
                                                @endif
                                            </td>

                                            <td>
                                                <button class="btn btn-sm btn-outline-info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#customerSignatureModal"
                                                    data-signature="{{ $serviceJob->customer_signature }}">
                                                    {{ translate('View Signature') }}
                                                </button>
                                            </td>
                                            <td>{{ $serviceJob->created_at->format('d M, Y H:i A') }}</td>

                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-info view-details"
                                                    data-details='{{ json_encode($serviceJob) }}'
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#serviceDetailsModal">
                                                    <i class="tio-invisible"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="customerSignatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="signatureModalLabel">{{ translate('Customer Signature') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="signatureImage" src="" alt="{{ translate('Signature') }}" class="img-fluid" />
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="activityDetailsModal" tabindex="-1" role="dialog"
    aria-labelledby="activityDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityDetailsModalLabel">{{ translate('Activity Details') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>{{ translate('Description') }}:</strong> <span id="activity-description"></span></p>
                <p><strong>{{ translate('Created By') }}:</strong> <span id="activity-created-by"></span></p>
                <p><strong>{{ translate('Created At') }}:</strong> <span id="activity-created-at"></span></p>
                <p><strong>{{ translate('Attachments') }}:</strong> <span id="activity-attachments"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">{{ translate('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="estimateDetailsModal" tabindex="-1" role="dialog"
    aria-labelledby="estimateDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="estimateDetailsModalLabel">{{ translate('Estimate Details') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>{{ translate('Service') }}:</strong> <span id="estimate-service"></span></p>
                <p><strong>{{ translate('Subtotal') }}:</strong> <span id="estimate-subtotal"></span></p>
                <p><strong>{{ translate('Tax') }}:</strong> <span id="estimate-tax"></span></p>
                <p><strong>{{ translate('Total') }}:</strong> <span id="estimate-total"></span></p>
                <p><strong>{{ translate('Service Mode') }}:</strong> <span id="estimate-is-mobile"></span></p>
                <p><strong>{{ translate('Created At') }}:</strong> <span id="estimate-created-at"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">{{ translate('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Details Modal -->
<div class="modal fade" id="invoiceDetailsModal" tabindex="-1" role="dialog" aria-labelledby="invoiceDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceDetailsModalLabel">{{ translate('Invoice Details') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>{{ translate('Subtotal') }}:</strong> <span id="invoice-subtotal"></span></p>
                <p><strong>{{ translate('Tax') }}:</strong> <span id="invoice-tax"></span></p>
                <p><strong>{{ translate('Total') }}:</strong> <span id="invoice-total"></span></p>
                <p><strong>{{ translate('Payment Status') }}:</strong> <span id="invoice-payment-status"></span></p>
                <p><strong>{{ translate('Generated At') }}:</strong> <span id="invoice-generated-at"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">{{ translate('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Change Order Details Modal -->
<div class="modal fade" id="changeOrderDetailsModal" tabindex="-1" role="dialog"
    aria-labelledby="changeOrderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeOrderDetailsModalLabel">{{ translate('Change Order Details') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>{{ translate('Additional Charges') }}:</strong> <span
                        id="change-order-additional-charges"></span></p>
                <p><strong>{{ translate('Description') }}:</strong> <span id="change-order-description"></span></p>
                <p><strong>{{ translate('Created At') }}:</strong> <span id="change-order-created-at"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">{{ translate('Close') }}</button>
            </div>
        </div>
    </div>
</div>
<!-- Attachment Preview Modal -->
<div class="modal fade" id="attachmentsModal" tabindex="-1" aria-labelledby="attachmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachmentsModalLabel">{{ translate('Job Attachments') }}</h5>
 <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>            </div>
            <div class="modal-body text-center" id="attachmentsModalBody">
                <p class="text-muted">{{ translate('No attachments found.') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Cancellation Details Modal -->
<div class="modal fade" id="cancellationDetailsModal" tabindex="-1" role="dialog"
    aria-labelledby="cancellationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancellationDetailsModalLabel">{{ translate('Cancellation Details') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>{{ translate('Reason') }}:</strong> <span id="cancellation-reason"></span></p>
                <p><strong>{{ translate('Fee Amount') }}:</strong> <span id="cancellation-fee-amount"></span></p>
                <p><strong>{{ translate('Refund Amount') }}:</strong> <span id="cancellation-refund-amount"></span></p>
                <p><strong>{{ translate('Created At') }}:</strong> <span id="cancellation-created-at"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">{{ translate('Close') }}</button>
            </div>
        </div>
    </div>
</div>


<!-- Service Details Modal -->
<div class="modal fade" id="serviceDetailsModal" tabindex="-1" role="dialog" aria-labelledby="serviceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceDetailsModalLabel">{{ translate('Service Details') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>{{ translate('Technician') }}:</strong> <span id="service-technician"></span></p>
                <p><strong>{{ translate('Status') }}:</strong> <span id="service-status"></span></p>
                <p><strong>{{ translate('Scheduled At') }}:</strong> <span id="service-scheduled-at"></span></p>
                <p><strong>{{ translate('Started At') }}:</strong> <span id="service-started-at"></span></p>
                <p><strong>{{ translate('Completed At') }}:</strong> <span id="service-completed-at"></span></p>
                <p><strong>{{ translate('Odometer Start') }}:</strong> <span id="service-odometer-start"></span></p>
                <p><strong>{{ translate('Odometer End') }}:</strong> <span id="service-odometer-end"></span></p>
                <p><strong>{{ translate('GPS Location') }}:</strong> <span id="service-gps-location"></span></p>
                <p><strong>{{ translate('Remarks') }}:</strong> <span id="service-remarks"></span></p>
                <p><strong>{{ translate('Priority') }}:</strong> <span id="service-priority"></span></p>
                <p><strong>{{ translate('SLA Hours') }}:</strong> <span id="service-sla-hours"></span></p>
                <p><strong>{{ translate('Service Mode') }}:</strong> <span id="service-is-mobile"></span></p>
                <p><strong>{{ translate('Created At') }}:</strong> <span id="service-created-at"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Close') }}</button>
            </div>
        </div>
    </div>
</div>



@endsection

@push('script')
@php
    $textNA = translate('N/A');
    $textNone = translate('None');
    $textSystem = translate('System');
    $textMobile = translate('Mobile');
    $textInShop = translate('In-shop');
    $workflowStatusMap = [
        'new' => translate('new'),
        'assigned' => translate('assigned'),
        'scheduled' => translate('scheduled'),
        'ready_to_start' => translate('ready_to_start'),
        'in_progress' => translate('in_progress'),
        'qa_pending' => translate('qa_pending'),
        'completed' => translate('completed'),
        'closed' => translate('closed'),
        'cancelled' => translate('cancelled'),
    ];
    $paymentStatusMap = [
        'pending' => translate('pending'),
        'paid' => translate('paid'),
        'expired' => translate('expired'),
        'failed' => translate('failed'),
        'refunded' => translate('refunded'),
        'cancelled' => translate('cancelled'),
    ];
    $priorityMap = [
        'low' => translate('low'),
        'medium' => translate('medium'),
        'high' => translate('high'),
        'urgent' => translate('urgent'),
    ];
@endphp
<script>
    $(document).ready(function() {
        const textNA = @json($textNA);
        const textNone = @json($textNone);
        const textSystem = @json($textSystem);
        const textMobile = @json($textMobile);
        const textInShop = @json($textInShop);
        const workflowStatusMap = @json($workflowStatusMap);
        const paymentStatusMap = @json($paymentStatusMap);
        const priorityMap = @json($priorityMap);

        const normalizeKey = (value) => String(value ?? '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '_');

        const getLocalizedValue = (map, value) => {
            const key = normalizeKey(value);
            return map[key] || value || textNA;
        };

        const parseAttachmentList = (value) => {
            if (Array.isArray(value)) {
                return value;
            }
            if (typeof value === 'string' && value.length > 0) {
                try {
                    const parsedValue = JSON.parse(value);
                    return Array.isArray(parsedValue) ? parsedValue : [];
                } catch (error) {
                    return [];
                }
            }
            return [];
        };

        // Handle tab switching
        $('.action-btn').click(function() {
            $('.action-btn').parent().removeClass('active');
            $(this).parent().addClass('active');
            let target = $(this).data('bs-target');
            $('.collapse').not(target).collapse('hide');
            $(target).collapse('show');
        });
        $('.view-details').click(function() {
            let details = $(this).data('details');
            let modalId = $(this).data('bs-target');

            if (modalId === '#activityDetailsModal') {
                $('#activity-description').text(details.description || textNA);
                $('#activity-created-by').text(details.created_by && details.created_by.name ? details.created_by.name : textSystem);
                $('#activity-created-at').text(details.created_at ? new Date(details.created_at).toLocaleString() : textNA);
                const activityAttachments = parseAttachmentList(details.attachments);
                $('#activity-attachments').text(activityAttachments.length ? activityAttachments.join(', ') : textNone);
            } else if (modalId === '#estimateDetailsModal') {
                $('#estimate-service').text(details.service ? details.service.title : textNA);
                $('#estimate-subtotal').text(details.subtotal ? details.subtotal : '0.00');
                $('#estimate-tax').text(details.tax ? details.tax : '0.00');
                $('#estimate-total').text(details.total ? details.total : '0.00');
                $('#estimate-is-mobile').text(details.is_mobile ? textMobile : textInShop);
                $('#estimate-created-at').text(details.created_at ? new Date(details.created_at).toLocaleString() : textNA);
            } else if (modalId === '#invoiceDetailsModal') {
                $('#invoice-subtotal').text(details.subtotal ? details.subtotal : '0.00');
                $('#invoice-tax').text(details.tax ? details.tax : '0.00');
                $('#invoice-total').text(details.total ? details.total : '0.00');
                $('#invoice-payment-status').text(getLocalizedValue(paymentStatusMap, details.payment_status));
                $('#invoice-generated-at').text(details.generated_at ? new Date(details.generated_at).toLocaleString() : textNA);
            } else if (modalId === '#changeOrderDetailsModal') {
                $('#change-order-additional-charges').text(details.additional_charges ? details.additional_charges : '0.00');
                $('#change-order-description').text(details.description || textNA);
                $('#change-order-created-at').text(details.created_at ? new Date(details.created_at).toLocaleString() : textNA);
            } else if (modalId === '#cancellationDetailsModal') {
                $('#cancellation-reason').text(details.cancellation_reason || textNA);
                $('#cancellation-fee-amount').text(details.fee_amount ? details.fee_amount : '0.00');
                $('#cancellation-refund-amount').text(details.refund_amount ? details.refund_amount : '0.00');
                $('#cancellation-created-at').text(details.created_at ? new Date(details.created_at).toLocaleString() : textNA);
            } else if (modalId === '#serviceDetailsModal') {
                $('#service-technician').text(details.technician && details.technician.name ? details.technician.name : textNA);
                $('#service-status').text(getLocalizedValue(workflowStatusMap, details.status));
                $('#service-scheduled-at').text(details.scheduled_at ? new Date(details.scheduled_at).toLocaleString() : textNA);
                $('#service-started-at').text(details.started_at ? new Date(details.started_at).toLocaleString() : textNA);
                $('#service-completed-at').text(details.completed_at ? new Date(details.completed_at).toLocaleString() : textNA);
                $('#service-odometer-start').text(details.odometer_start || textNA);
                $('#service-odometer-end').text(details.odometer_end || textNA);
                $('#service-gps-location').text(details.gps_location || textNA);
                $('#service-remarks').text(details.remarks || textNA);
                $('#service-priority').text(getLocalizedValue(priorityMap, details.priority));
                $('#service-sla-hours').text(details.sla_hours || textNA);
                $('#service-is-mobile').text(details.is_mobile ? textMobile : textInShop);
                $('#service-created-at').text(details.created_at ? new Date(details.created_at).toLocaleString() : textNA);
            }
        });

    });

    document.addEventListener('DOMContentLoaded', function() {
        var signatureModal = document.getElementById('customerSignatureModal');
        signatureModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var signature = button.getAttribute('data-signature');

            var img = signatureModal.querySelector('#signatureImage');
            img.src = signature;
        });
    });
</script>

<script>
document.addEventListener('click', function (e) {
    const trigger = e.target.closest('.view-attachments-btn');
    if (trigger) {
        let attachments = [];
        try {
            const parsed = JSON.parse(trigger.dataset.attachments || '[]');
            attachments = Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            attachments = [];
        }

        const container = document.getElementById('attachmentsModalBody');
        container.innerHTML = '';

        if (attachments.length === 0) {
            container.innerHTML = `<p class="text-muted">{{ translate('No attachments found.') }}</p>`;
        } else {
            attachments.forEach(fileItem => {
                const file = typeof fileItem === 'string' ? fileItem : (fileItem.file_name || '');
                if (!file) {
                    return;
                }

                const ext = file.split('.').pop().toLowerCase();
                let content = '';

                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                    content = `<img src="{{ asset('storage/service-attachments') }}/${file}" 
                               class="img-fluid m-2 rounded shadow-sm" 
                               style="max-height: 200px;">`;
                } else {
                    content = `<a href="{{ asset('storage/service-attachments') }}/${file}" 
                               target="_blank" class="btn btn-outline-secondary m-1">
                               ${file}</a>`;
                }

                container.innerHTML += content;
            });
        }

        const modal = new bootstrap.Modal(document.getElementById('attachmentsModal'));
        modal.show();
    }
});
</script>

@endpush

