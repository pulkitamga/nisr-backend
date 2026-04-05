@extends('layouts.back-end.app')

@section('title', translate('Lead Details'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<style>
    .detail-card {
        border-inline-start: 4px solid var('');
    }

    .detail-card.rtl {
        direction: rtl;
        text-align: start;
    }

    .detail-card.ltr {
        direction: ltr;
        text-align: start;
    }

    .detail-card p {
        margin-bottom: 0.75rem;
        line-height: 1.5;
    }

    .detail-card strong {
        color: #2c3e50;
        font-weight: 600;
    }

    .bidi-auto {
        unicode-bidi: plaintext;
    }

    .bidi-ltr {
        direction: ltr;
        unicode-bidi: isolate;
        display: inline-block;
        text-align: left;
    }

    .detail-card .list-group-item {
        gap: .75rem;
    }
</style>
@endpush

@section('content')
@php
    $isRtl = Session::get('direction') === 'rtl';
@endphp
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/customer.png') }}" alt="">
            {{ translate('Lead Details') }}
        </h2>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4 shadow-sm detail-card {{ $isRtl ? 'rtl' : 'ltr' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ translate('Message Details') }}</h5>
                </div>
                <div class="card-body">
                    @php
                    $inbox = $lead->latestInboxMessage;
                    @endphp
                    @if($inbox)
                    <p><strong>{{ translate('Subject') }}:</strong> <span class="bidi-auto">{{ $inbox->subject ?? translate('No Subject') }}</span></p>
                    <p><strong>{{ translate('Sender') }}:</strong> <span class="bidi-auto">{{ $inbox->sender_name ?? translate('Unassigned') }}</span> (<span class="bidi-ltr">{{ $inbox->sender_email ?? translate('Not Available') }}</span>)</p>
                    <p><strong>{{ translate('Phone') }}:</strong> <span class="bidi-ltr">{{ $inbox->sender_phone ?? translate('Not Available') }}</span></p>
                    <p><strong>{{ translate('Message') }}:</strong> <span class="bidi-auto">{{ $inbox->body ?? translate('No Message') }}</span></p>
                    <p><strong>{{ translate('Received At') }}:</strong> <span class="bidi-ltr">{{ $lead->created_at->format('d M, Y H:i A') }}</span></p>
                    @if(is_array($inbox->details))
                    <div class="mb-3">
                        <strong>{{ translate('Details') }}:</strong>
                        <ul class="list-group mt-2">
                            @foreach($inbox->details as $key => $value)
                            @if(!empty($value)) {{-- Only show if value is not empty --}}
                            <li class="list-group-item d-flex justify-content-between align-items-center {{ $isRtl ? 'text-end' : '' }}">
                                <span class="fw-bold">{{ ucfirst($key) }}</span>
                                <span class="bidi-auto">{{ $value }}</span>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @else
                    <p><strong>{{ translate('Details') }}:</strong> <span class="bidi-auto">{{ $inbox->details ?? '-' }}</span></p>
                    @endif
                    @else
                    <p class="text-muted">{{ translate('No message details available') }}</p>
                    @endif
                </div>
            </div>

            @if($lead->purchaseOrder)
            <div class="card mb-4 shadow-sm detail-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ translate('Purchase Order Details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <p><strong>{{ translate('company') }}:</strong> <span class="bidi-auto">{{ $lead->purchaseOrder?->wholeseller?->wholesalerBusiness?->company_name ?? translate('Unknown')}}</span></p>
                            <p><strong>{{ translate('Email') }}:</strong> <span class="bidi-ltr">{{ $lead->purchaseOrder?->wholeseller?->email ?? translate('N/A') }}</span></p>
                            <p><strong>{{ translate('Phone') }}:</strong> <span class="bidi-ltr">{{ $lead->purchaseOrder?->wholeseller?->phone ?? translate('N/A') }}</span></p>
                        </div>
                        <div class="col-6">
                            <p><strong>{{ translate('Purchase Order No') }}:</strong> <span class="bidi-ltr">{{ $lead->purchaseOrder->purchase_order_no ?? translate('Not Assigned') }}</span></p>
                            <p><strong>{{ translate('Status') }}:</strong> <span class="bidi-auto">{{ $lead->purchaseOrder->status ?? translate('N/A') }}</span></p>
                            <p><strong>{{ translate('Created_At') }}:</strong> <span class="bidi-ltr">{{ $lead->purchaseOrder?->created_at?->format('d M, Y H:i A') ?? translate('N/A') }}</span></p>
                        </div>
                    </div>
                    @if($lead->purchaseOrder->items && $lead->purchaseOrder->items->count() > 0)
                    <h6 class="mt-3">{{ translate('Ordered_Items') }}</h6>
                    <table

                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('Product') }}</th>
                                <th>{{ translate('Quantity') }}</th>
                                <th>{{ translate('Base Price') }}</th>
                                <th>{{ translate('Tax') }}</th>
                                <th>{{ translate('Final Price') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lead->purchaseOrder->items as $item)
                            <tr>
                                <td>{{ $item->product?->getTranslatedField('name') ?? translate('N/A') }}</td>
                                <td>{{ $item->product_quantity ?? 0 }}</td>
                                <td>
                                    {{ setCurrencySymbol(
                                                    amount: usdToDefaultCurrency(amount: $item->base_price ?? 0),
                                                    currencyCode: getCurrencyCode()
                                                ) }}
                                </td>
                                <td>
                                    {{ setCurrencySymbol(
                                                    amount: usdToDefaultCurrency(amount: $item->tax ?? 0),
                                                    currencyCode: getCurrencyCode()
                                                ) }}
                                </td>
                                <td>
                                    {{ setCurrencySymbol(
                                                    amount: usdToDefaultCurrency(amount: $item->final_price ?? 0),
                                                    currencyCode: getCurrencyCode()
                                                ) }}
                                </td>
                            </tr>

                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="text-muted">{{ translate('No items available') }}</p>
                    @endif
                </div>
            </div>
            @endif

        </div>

        <div class="col-md-12">
            @include('admin-views.crm.partials.escalation-panel', ['escalations' => $lead->escalations ?? collect()])
        </div>

        <!-- Actions Card -->
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ translate('Related Actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="inline-page-menu my-4">
                        <ul class="list-unstyled" id="actionTabs">
                            <li class="active">
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseActivity-{{ $lead->id }}" data-collapse-target="activity">
                                    {{ translate('Activity') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseTask-{{ $lead->id }}" data-collapse-target="task">
                                    {{ translate('Task') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseNote-{{ $lead->id }}" data-collapse-target="note">
                                    {{ translate('Note') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseCall-{{ $lead->id }}" data-collapse-target="call">
                                    {{ translate('Call') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseFile-{{ $lead->id }}" data-collapse-target="file">
                                    {{ translate('Upload File') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="collapse show" id="collapseActivity-{{ $lead->id }}">
                        <div class="border-0 shadow-sm mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mt-3 mb-2">{{ translate('activities') }}</h6>
                                </div>

                                <div id="activity-list-{{ $lead->id }} ">
                                    @include('admin-views.crm.leads.partials.activity_list', ['activities' => $lead->activities])
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Note Section -->
                    <div class="collapse" id="collapseNote-{{ $lead->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Add New Note') }}</h6>
                            <form id="note-form-{{ $lead->id }}" action="{{ route('admin.crm.lead.note.store', $lead->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="noteContent-{{ $lead->id }}" class="form-label">{{ translate('Content') }}</label>
                                    <textarea class="form-control" id="noteContent-{{ $lead->id }}" name="note" placeholder="{{ translate('Enter note') }}"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="noteDate-{{ $lead->id }}" class="form-label">{{ translate('Noted At') }}</label>
                                    <input type="date" class="form-control" id="noteDate-{{ $lead->id }}" name="noted_at">
                                </div>
                                <button type="submit" class="btn btn-primary">{{ translate('Save Note') }}</button>
                            </form>

                        </div>
                        <div class="card">
                            <div class="card-header">

                                <h6 class="mt-3 mb-2">{{ translate('Existing Notes') }}</h6>
                            </div>

                            <div id="note-list-{{ $lead->id }}">
                                @include('admin-views.crm.leads.partials.note_list', ['notes' => $lead->notes])
                            </div>
                        </div>

                    </div>

                    <!-- Task Section -->
                    <div class="collapse" id="collapseTask-{{ $lead->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Add/Edit Task') }}</h6>
                            <form id="task-form-{{ $lead->id }}" action="{{ route('admin.crm.lead.task.store', $lead->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="_method" id="method-{{ $lead->id }}" value="POST">
                                <input type="hidden" name="task_id" id="task-id-{{ $lead->id }}">
                                <div class="mb-3">
                                    <label for="taskName-{{ $lead->id }}" class="form-label">{{ translate('Name') }}</label>
                                    <input type="text" class="form-control" id="taskName-{{ $lead->id }}" name="name" placeholder="{{ translate('Enter name') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="taskDesc-{{ $lead->id }}" class="form-label">{{ translate('Description') }}</label>
                                    <textarea class="form-control" id="taskDesc-{{ $lead->id }}" name="description" placeholder="{{ translate('Enter description') }}"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="taskDue-{{ $lead->id }}" class="form-label">{{ translate('Due Date') }}</label>
                                    <input type="date" class="form-control" id="taskDue-{{ $lead->id }}" name="due_date">
                                </div>
                                <div class="mb-3">
                                    <label for="taskStatus-{{ $lead->id }}" class="form-label">{{ translate('Status') }}</label>
                                    <select class="form-control" id="taskStatus-{{ $lead->id }}" name="status">
                                        <option value="pending">{{ translate('Pending') }}</option>
                                        <option value="complete">{{ translate('Complete') }}</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary" id="task-submit-btn-{{ $lead->id }}">{{ translate('Save Task') }}</button>
                            </form>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Existing Tasks') }}</h6>
                            </div>

                            <div id="task-list-{{ $lead->id }}">
                                @include('admin-views.crm.leads.partials.task_list', ['tasks' => $lead->tasks])
                            </div>
                        </div>

                    </div>

                    <div class="collapse" id="collapseCall-{{ $lead->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Add New Call') }}</h6>
                            <form id="call-form-{{ $lead->id }}" action="{{ route('admin.crm.lead.call.store', $lead->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="callTitle-{{ $lead->id }}" class="form-label">{{ translate('Title') }}</label>
                                    <input type="text" class="form-control" id="callTitle-{{ $lead->id }}" name="title" placeholder="{{ translate('Enter title') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="callFrom-{{ $lead->id }}" class="form-label">{{ translate('From') }}</label>
                                    <input type="datetime-local" class="form-control" id="callFrom-{{ $lead->id }}" name="from" required>
                                </div>
                                <div class="mb-3">
                                    <label for="callTo-{{ $lead->id }}" class="form-label">{{ translate('To') }}</label>
                                    <input type="datetime-local" class="form-control" id="callTo-{{ $lead->id }}" name="to" required>
                                </div>
                                <div class="mb-3">
                                    <label for="callGuests-{{ $lead->id }}" class="form-label">{{ translate('Guests') }}</label>
                                    <select class="form-control" id="callGuests-{{ $lead->id }}" name="guests">
                                        <option value="">{{ translate('Select Guest') }}</option>
                                        @foreach(\App\Models\User::all() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="callLocation-{{ $lead->id }}" class="form-label">{{ translate('Location') }}</label>
                                    <input type="text" class="form-control" id="callLocation-{{ $lead->id }}" name="location" placeholder="{{ translate('Enter location') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="callDesc-{{ $lead->id }}" class="form-label">{{ translate('Description') }}</label>
                                    <textarea class="form-control" id="callDesc-{{ $lead->id }}" name="description" placeholder="{{ translate('Enter description') }}"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">{{ translate('Save Call') }}</button>
                            </form>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Existing Calls') }}</h6>
                            </div>
                            <div id="call-list-{{ $lead->id }}">
                                @include('admin-views.crm.leads.partials.call_list', ['calls' => $lead->calls])
                            </div>
                        </div>
                    </div>

                    <!-- File Section -->
                    <div class="collapse" id="collapseFile-{{ $lead->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Upload New File') }}</h6>
                            <form id="file-form-{{ $lead->id }}" action="{{ route('admin.crm.lead.file.store', $lead->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="fileUpload-{{ $lead->id }}" class="form-label">{{ translate('File') }}</label>
                                    <input type="file" class="form-control" id="fileUpload-{{ $lead->id }}" name="file">
                                </div>
                                <button type="submit" class="btn btn-primary">{{ translate('Upload') }}</button>
                            </form>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Existing Files') }}</h6>
                            </div>
                            <div id="file-list-{{ $lead->id }}">
                                @include('admin-views.crm.leads.partials.file_list', ['files' => $lead->files])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
@include('admin-views.crm.partials._crm-js-text')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/crm.js') }}"></script>

<script>
    // Initialize Toastr options
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3000,
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut'
    };

    $(document).ready(function() {
        initLeadDetails('{{ $lead->id }}', {
            activitySaved: '{{ translate("Activity saved successfully!") }}',
            noteSaved: '{{ translate("Note saved successfully!") }}',
            taskSaved: '{{ translate("Task saved successfully!") }}',
            taskUpdated: '{{ translate("Task updated successfully!") }}',
            taskCompleted: '{{ translate("Task marked as complete!") }}',
            callSaved: '{{ translate("Call saved successfully!") }}',
            fileUploaded: '{{ translate("File uploaded successfully!") }}'
        });
    });
</script>
@endpush
