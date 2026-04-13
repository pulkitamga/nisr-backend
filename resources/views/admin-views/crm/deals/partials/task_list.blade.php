@forelse($tasks as $task)
@if($loop->first)
<div class="table-responsive">
    <table

        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
        <thead class="thead-light thead-50 text-capitalize">
            <tr>
                <th>{{ translate('Name') }}</th>
                <th>{{ translate('Description') }}</th>
                <th>{{ translate('Due Date') }}</th>
                <th>{{ translate('Status') }}</th>
                <th>{{ translate('Employee') }}</th>
                <th>{{ translate('Department') }}</th>
                <th>{{ translate('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @endif
            <tr>
                <td>{{ $task->name }}</td>
                <td class="text-truncate white-space-initial">{{ $task->description ?? translate('N/A') }}</td>
                <td>{{ $task->due_date }}</td>
                <td>{{ \App\Utils\crm_status_label($task->status) }}</td>
                <td>{{ $task->employee->name ?? translate('Unassigned') }}</td>
                <td>{{ $task->department?->getTranslatedField('name') ?? translate('Unassigned') }}</td>
                <td>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary task-edit-btn-inbox"
                            data-task-id="{{ $task->id }}"
                            data-name="{{ $task->name }}"
                            data-description="{{ $task->description ?? '' }}"
                            data-due-date="{{ $task->due_date }}"
                            data-status="{{ $task->status }}"
                            title="{{ translate('Edit') }}">
                            <i class="tio-edit"></i>
                        </button>
                        @if($task->status != 'complete')
                        <button class="btn btn-sm btn-outline-success task-complete-btn-inbox"
                            data-task-id="{{ $task->id }}"
                            title="{{ translate('Complete') }}">
                            <i class="tio-done"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @if($loop->last)
        </tbody>
    </table>
</div>
@endif
@empty
@include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
@endforelse

