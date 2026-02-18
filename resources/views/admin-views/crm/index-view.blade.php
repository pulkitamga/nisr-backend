@extends('layouts.back-end.app')

@section('title', translate('Massage Details'))

@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<style>
    .detail-card {
        border-left: 4px solid var('');
    }

    .detail-card p {
        margin-bottom: 0.75rem;
        line-height: 1.5;
    }

    .detail-card strong {
        color: #2c3e50;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/customer.png') }}" alt="">
            {{ translate('Massage Details') }}
        </h2>
    </div>

    <div class="row">
        <div class="col-md-12">
<div class="card mb-4 shadow-sm detail-card" style=" direction : {{Session::get('direction') === "rtl" ? 'ltr' : 'rtl'}};">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ translate('Message Details') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ translate('Subject') }}:</strong> {{ $inbox->subject ?? translate('No Subject') }}</p>
                    <p><strong>{{ translate('Body') }}:</strong> {{ $inbox->body ?? translate('No Message') }}</p>
                    <p><strong>{{ translate('Sender Name') }}:</strong> {{ $inbox->sender_name ?? translate('Unassigned') }}</p>
                    <p><strong>{{ translate('Sender Email') }}:</strong> {{ $inbox->sender_email ?? 'Not Available' }}</p>
                    <p><strong>{{ translate('Sender Phone') }}:</strong> {{ $inbox->sender_phone ?? 'Not Available' }}</p>
                    <p><strong>{{ translate('Pipeline') }}:</strong> {{ $inbox->pipeline ?? '-' }}</p>
                    <p><strong>{{ translate('Message Type') }}:</strong> {{ $inbox->message_type ?? '-' }}</p>
                    <p><strong>{{ translate('Status') }}:</strong> {{ $inbox->status ?? '-' }}</p>
                    <p><strong>{{ translate('Recevied At') }}:</strong> {{ $inbox->created_at?->format('d M, Y H:i A') }}</p>
                    @if(is_array($inbox->details))
                    <div class="mb-3">
                        <strong>{{ translate('Details') }}:</strong>
                        <ul class="list-group mt-2">
                            @foreach($inbox->details as $key => $value)
                            @if(!empty($value)) 
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="fw-bold">{{ translate($key) }}</span>
                                <span>{{ $value }}</span>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @else
                    <p><strong>{{ translate('Details') }}:</strong> {{ $inbox->details ?? '-' }}</p>
                    @endif


                </div>

            </div>
        </div>
        <!-- Actions Card -->
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">{{ translate('Related Actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="inline-page-menu my-4">
                        <ul class="list-unstyled d-flex gap-2" id="actionTabs">
                            <li class="active">
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseActivity-{{ $inbox->id }}" data-collapse-target="activity">
                                    {{ translate('Activity') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseTask-{{ $inbox->id }}" data-collapse-target="task">
                                    {{ translate('Task') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseNote-{{ $inbox->id }}" data-collapse-target="note">
                                    {{ translate('Note') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseCall-{{ $inbox->id }}" data-collapse-target="call">
                                    {{ translate('Call') }}
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" class="nav-link action-btn" data-bs-toggle="collapse" data-bs-target="#collapseFile-{{ $inbox->id }}" data-collapse-target="file">
                                    {{ translate('Upload File') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="collapse show" id="collapseActivity-{{ $inbox->id }}">
                        <div class="border-0 shadow-sm mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mt-3 mb-2">{{ translate('activities') }}</h6>
                                </div>

                                <div id="activity-list-{{ $inbox->id }} ">
                                    @include('admin-views.crm.inbox.partials.activity_list', ['activities' => $inbox->activities])
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Note Section -->
                    <div class="collapse" id="collapseNote-{{ $inbox->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Add New Note') }}</h6>
                            <form id="note-form-{{ $inbox->id }}" action="{{ route('admin.crm.inbox.note.store', $inbox->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="noteContent-{{ $inbox->id }}" class="form-label">{{ translate('Content') }}</label>
                                    <textarea class="form-control" id="noteContent-{{ $inbox->id }}" name="note" placeholder="{{ translate('Enter note') }}"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="noteDate-{{ $inbox->id }}" class="form-label">{{ translate('Noted At') }}</label>
                                    <input type="date" class="form-control" id="noteDate-{{ $inbox->id }}" name="noted_at">
                                </div>
                                <button type="submit" class="btn btn-primary">{{ translate('Save Note') }}</button>
                            </form>

                        </div>
                        <div class="card">
                            <div class="card-header">

                                <h6 class="mt-3 mb-2">{{ translate('Existing Notes') }}</h6>
                            </div>

                            <div id="note-list-{{ $inbox->id }}">
                                @include('admin-views.crm.inbox.partials.note_list', ['notes' => $inbox->notes])
                            </div>
                        </div>

                    </div>

                    <!-- Task Section -->
                    <div class="collapse" id="collapseTask-{{ $inbox->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Add/Edit Task') }}</h6>
                            <form id="task-form-{{ $inbox->id }}" action="{{ route('admin.crm.inbox.task.store', $inbox->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="_method" id="method-{{ $inbox->id }}" value="POST">
                                <input type="hidden" name="task_id" id="task-id-{{ $inbox->id }}">
                                <div class="mb-3">
                                    <label for="taskName-{{ $inbox->id }}" class="form-label">{{ translate('Name') }}</label>
                                    <input type="text" class="form-control" id="taskName-{{ $inbox->id }}" name="name" placeholder="{{ translate('Enter name') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="taskDesc-{{ $inbox->id }}" class="form-label">{{ translate('Description') }}</label>
                                    <textarea class="form-control" id="taskDesc-{{ $inbox->id }}" name="description" placeholder="{{ translate('Enter description') }}"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="taskDue-{{ $inbox->id }}" class="form-label">{{ translate('Due Date') }}</label>
                                    <input type="date" class="form-control" id="taskDue-{{ $inbox->id }}" name="due_date">
                                </div>
                                <div class="mb-3">
                                    <label for="taskStatus-{{ $inbox->id }}" class="form-label">{{ translate('Status') }}</label>
                                    <select class="form-control" id="taskStatus-{{ $inbox->id }}" name="status">
                                        <option value="pending">{{ translate('Pending') }}</option>
                                        <option value="complete">{{ translate('Complete') }}</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary" id="task-submit-btn-{{ $inbox->id }}">{{ translate('Save Task') }}</button>
                            </form>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Existing Tasks') }}</h6>
                            </div>

                            <div id="task-list-{{ $inbox->id }}">
                                @include('admin-views.crm.inbox.partials.task_list', ['tasks' => $inbox->tasks])
                            </div>
                        </div>

                    </div>

                    <div class="collapse" id="collapseCall-{{ $inbox->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Add New Call') }}</h6>
                            <form id="call-form-{{ $inbox->id }}" action="{{ route('admin.crm.inbox.call.store', $inbox->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="callTitle-{{ $inbox->id }}" class="form-label">{{ translate('Title') }}</label>
                                    <input type="text" class="form-control" id="callTitle-{{ $inbox->id }}" name="title" placeholder="{{ translate('Enter title') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="callFrom-{{ $inbox->id }}" class="form-label">{{ translate('From') }}</label>
                                    <input type="datetime-local" class="form-control" id="callFrom-{{ $inbox->id }}" name="from" required>
                                </div>
                                <div class="mb-3">
                                    <label for="callTo-{{ $inbox->id }}" class="form-label">{{ translate('To') }}</label>
                                    <input type="datetime-local" class="form-control" id="callTo-{{ $inbox->id }}" name="to" required>
                                </div>
                                <div class="mb-3">
                                    <label for="callGuests-{{ $inbox->id }}" class="form-label">{{ translate('Guests') }}</label>
                                    <select class="form-control" id="callGuests-{{ $inbox->id }}" name="guests">
                                        <option value="">{{ translate('Select Guest') }}</option>
                                        @foreach(\App\Models\User::all() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="callLocation-{{ $inbox->id }}" class="form-label">{{ translate('Location') }}</label>
                                    <input type="text" class="form-control" id="callLocation-{{ $inbox->id }}" name="location" placeholder="{{ translate('Enter location') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="callDesc-{{ $inbox->id }}" class="form-label">{{ translate('Description') }}</label>
                                    <textarea class="form-control" id="callDesc-{{ $inbox->id }}" name="description" placeholder="{{ translate('Enter description') }}"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">{{ translate('Save Call') }}</button>
                            </form>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Existing Calls') }}</h6>
                            </div>
                            <div id="call-list-{{ $inbox->id }}">
                                @include('admin-views.crm.inbox.partials.call_list', ['calls' => $inbox->calls])
                            </div>
                        </div>
                    </div>

                    <!-- File Section -->
                    <div class="collapse" id="collapseFile-{{ $inbox->id }}">
                        <div class="card card-body border-0 shadow-sm mb-3">
                            <h6 class="mb-2">{{ translate('Upload New File') }}</h6>
                            <form id="file-form-{{ $inbox->id }}" action="{{ route('admin.crm.inbox.file.store', $inbox->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="fileUpload-{{ $inbox->id }}" class="form-label">{{ translate('File') }}</label>
                                    <input type="file" class="form-control" id="fileUpload-{{ $inbox->id }}" name="file">
                                </div>
                                <button type="submit" class="btn btn-primary">{{ translate('Upload') }}</button>
                            </form>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h6 class="mt-3 mb-2">{{ translate('Existing Files') }}</h6>
                            </div>
                            <div id="file-list-{{ $inbox->id }}">
                                @include('admin-views.crm.inbox.partials.file_list', ['files' => $inbox->files])
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
        initInboxDetails('{{ $inbox->id }}', {
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