@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('retail_Deal_View'))

@push('css_or_js')

@endpush

@section('content')


<div class="content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card" style=" direction : {{Session::get('direction') === "rtl" ? 'rtl' : 'ltr'}};">
                <div class="card-header">
                    <h2 class="h1 mb-0 text-capitalize">
                        {{ translate('Deal Details') }} :{{ $deal->id }}
                    </h2>
                    <div class="d-flex flex-wrap gap-2">
                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'deal_retail_view') || auth('admin')->user()->id == ($deal->owner_id))
                        <a href="{{ route('admin.orders.details',['id'=>$deal['order_id']]) }}"
                            class="btn btn-sm btn-primary create-quotation-btn"
                            data-id="{{ $deal->id }}">
                            Check Order </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>{{ translate('Customer Information') }}</h4>
                            <p><strong>{{ translate('Name') }}:</strong> {{ $deal->user->name ?? translate('N/A') }}</p>
                            <p><strong>{{ translate('Email') }}:</strong> {{ $deal->user->email ?? translate('N/A') }}</p>
                            <p><strong>{{ translate('Phone') }}:</strong> {{ $deal->user->phone ?? translate('N/A') }}</p>
                        </div>
                        <div class="col-md-6">

                            <h4>{{ translate('Deal Information') }}</h4>
                            <p><strong>{{ translate('Created At') }}:</strong> {{ $deal->created_at->format('d M, Y H:i A') }}</p>
                            <p><strong>{{ translate('Employee') }}:</strong> {{ $deal->employee->name ?? translate('N/A') }}</p>
                            <p><strong>{{ translate('Order Status') }}:</strong>
                                <span class="text-primary bg-soft-dark font-weight-bold px-3 py-1 mb-0 fz-12">{{ ucfirst($deal->order->order_status) }}</span>
                            </p>
                            <p><strong>{{ translate('Status') }}:</strong>
                                <span class="text-success bg-soft-success font-weight-bold px-3 py-1 mb-0 fz-12">{{ ucfirst($deal->status) }}</span>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-12">
            @include('admin-views.crm.partials.escalation-panel', ['escalations' => $deal->escalations ?? collect()])
        </div>

        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="h1 mb-0 text-capitalize">{{ translate('Related Actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="inline-page-menu my-4">
                        <ul class="list-unstyled" id="actionTabs">
                            <li class="active">
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseActivity-{{ $deal->id }}" data-collapse-target="activity">
                                    {{ translate('Activity') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseTask-{{ $deal->id }}" data-collapse-target="task">
                                    {{ translate('Task') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseNote-{{ $deal->id }}" data-collapse-target="note">
                                    {{ translate('Note') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseCall-{{ $deal->id }}" data-collapse-target="call">
                                    {{ translate('Call') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseFile-{{ $deal->id }}" data-collapse-target="file">
                                    {{ translate('Upload File') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="collapse show" id="collapseActivity-{{ $deal->id }}">
                        <div class="border-0 shadow-sm mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mt-3 mb-2">{{ translate('activities') }}</h6>
                                </div>

                                <div id="activity-list-{{ $deal->id }} ">
                                    @include('admin-views.crm.deals.partials.activity_list', ['activities' => $deal->activities])
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Note Section -->
                    <div class="collapse" id="collapseNote-{{ $deal->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Add New Note') }}</h6>
                            <form id="note-form-{{ $deal->id }}" action="{{ route('admin.crm.deal.note.store', $deal->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="noteContent-{{ $deal->id }}" class="form-label">{{ translate('Content') }}</label>
                                    <textarea class="form-control" id="noteContent-{{ $deal->id }}" name="note" placeholder="{{ translate('Enter note') }}"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="noteDate-{{ $deal->id }}" class="form-label">{{ translate('Noted At') }}</label>
                                    <input type="date" class="form-control" id="noteDate-{{ $deal->id }}" name="noted_at">
                                </div>
                                <button type="submit" class="btn btn-primary">{{ translate('Save Note') }}</button>
                            </form>

                        </div>
                        <div class="card">
                            <div class="card-header">

                                <h6 class="mt-3 mb-2">{{ translate('Existing Notes') }}</h6>
                            </div>

                            <div id="note-list-{{ $deal->id }}">
                                @include('admin-views.crm.deals.partials.note_list', ['notes' => $deal->notes])
                            </div>
                        </div>

                    </div>

                    <!-- Task Section -->
                    <div class="collapse" id="collapseTask-{{ $deal->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Add/Edit Task') }}</h6>
                            <form id="task-form-{{ $deal->id }}" action="{{ route('admin.crm.deal.task.store', $deal->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="_method" id="method-{{ $deal->id }}" value="POST">
                                <input type="hidden" name="task_id" id="task-id-{{ $deal->id }}">
                                <div class="mb-3">
                                    <label for="taskName-{{ $deal->id }}" class="form-label">{{ translate('Name') }}</label>
                                    <input type="text" class="form-control" id="taskName-{{ $deal->id }}" name="name" placeholder="{{ translate('Enter name') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="taskDesc-{{ $deal->id }}" class="form-label">{{ translate('Description') }}</label>
                                    <textarea class="form-control" id="taskDesc-{{ $deal->id }}" name="description" placeholder="{{ translate('Enter description') }}"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="taskDue-{{ $deal->id }}" class="form-label">{{ translate('Due Date') }}</label>
                                    <input type="date" class="form-control" id="taskDue-{{ $deal->id }}" name="due_date">
                                </div>
                                <div class="mb-3">
                                    <label for="taskStatus-{{ $deal->id }}" class="form-label">{{ translate('Status') }}</label>
                                    <select class="form-control" id="taskStatus-{{ $deal->id }}" name="status">
                                        <option value="pending">{{ translate('Pending') }}</option>
                                        <option value="complete">{{ translate('Complete') }}</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary" id="task-submit-btn-{{ $deal->id }}">{{ translate('Save Task') }}</button>
                            </form>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Existing Tasks') }}</h6>
                            </div>

                            <div id="task-list-{{ $deal->id }}">
                                @include('admin-views.crm.deals.partials.task_list', ['tasks' => $deal->tasks])
                            </div>
                        </div>

                    </div>

                    <div class="collapse" id="collapseCall-{{ $deal->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Add New Call') }}</h6>
                            <form id="call-form-{{ $deal->id }}" action="{{ route('admin.crm.deal.call.store', $deal->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="callTitle-{{ $deal->id }}" class="form-label">{{ translate('Title') }}</label>
                                    <input type="text" class="form-control" id="callTitle-{{ $deal->id }}" name="title" placeholder="{{ translate('Enter title') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="callFrom-{{ $deal->id }}" class="form-label">{{ translate('From') }}</label>
                                    <input type="datetime-local" class="form-control" id="callFrom-{{ $deal->id }}" name="from" required>
                                </div>
                                <div class="mb-3">
                                    <label for="callTo-{{ $deal->id }}" class="form-label">{{ translate('To') }}</label>
                                    <input type="datetime-local" class="form-control" id="callTo-{{ $deal->id }}" name="to" required>
                                </div>
                                <div class="mb-3">
                                    <label for="callGuests-{{ $deal->id }}" class="form-label">{{ translate('Guests') }}</label>
                                    <select class="form-control" id="callGuests-{{ $deal->id }}" name="guests">
                                        <option value="">{{ translate('Select Guest') }}</option>
                                        @foreach(\App\Models\User::all() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="callLocation-{{ $deal->id }}" class="form-label">{{ translate('Location') }}</label>
                                    <input type="text" class="form-control" id="callLocation-{{ $deal->id }}" name="location" placeholder="{{ translate('Enter location') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="callDesc-{{ $deal->id }}" class="form-label">{{ translate('Description') }}</label>
                                    <textarea class="form-control" id="callDesc-{{ $deal->id }}" name="description" placeholder="{{ translate('Enter description') }}"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">{{ translate('Save Call') }}</button>
                            </form>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Existing Calls') }}</h6>
                            </div>
                            <div id="call-list-{{ $deal->id }}">
                                @include('admin-views.crm.deals.partials.call_list', ['calls' => $deal->calls])
                            </div>
                        </div>
                    </div>

                    <!-- File Section -->
                    <div class="collapse" id="collapseFile-{{ $deal->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Upload New File') }}</h6>
                            <form id="file-form-{{ $deal->id }}" action="{{ route('admin.crm.deal.file.store', $deal->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="fileUpload-{{ $deal->id }}" class="form-label">{{ translate('File') }}</label>
                                    <input type="file" class="form-control" id="fileUpload-{{ $deal->id }}" name="file">
                                </div>
                                <button type="submit" class="btn btn-primary">{{ translate('Upload') }}</button>
                            </form>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Existing Files') }}</h6>
                            </div>
                            <div id="file-list-{{ $deal->id }}">
                                @include('admin-views.crm.deals.partials.file_list', ['files' => $deal->files])
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
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/crm.js') }}"></script>

<script>
    $(document).ready(function() {
        $(document).on('click', '.create-quotation-btn', function(e) {
            e.preventDefault();
            let dealId = $(this).data('id');
            let url = $(this).attr('href');

            // Check if URL already has query parameters
            if (url.indexOf('?') > -1) {
                url += '&deal_id=' + dealId;
            } else {
                url += '?deal_id=' + dealId;
            }

            window.location.href = url;
        });
    });


    $(document).on('click', '.request-quotation-btn', function(e) {
        e.preventDefault();
        let dealId = $(this).data('id');

        $.ajax({
            url: '/admin/crm/deals/retail/request-quotation/' + dealId,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res.status) {
                    toastr.success(res.message);
                } else {
                    toastr.error('Something went wrong');
                }
            }
        });
    });
</script>

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
        initDealDetails('{{ $deal->id }}', {
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
