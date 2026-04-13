@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Inbox_List'))
@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/crm.css')}}">
@endpush
@section('content')

<div class="content container-fluid">
    @php
        $selectedStatus = request('status', 'new');
        $selectedChannel = request()->has('Channel')
            ? (request('Channel') === '' ? 'all' : request('Channel'))
            : 'all';
        $statusOptions = [
            'all' => translate('All'),
            'new' => translate('New'),
            'processing' => translate('processing'),
            'converted' => translate('converted'),
            'ignored' => translate('ignored'),
        ];
        $channelOptions = [
            'all' => translate('All'),
            'email' => translate('email'),
            'form' => translate('form'),
            'chat' => translate('chat'),
            'social' => translate('social'),
            'phone' => translate('phone'),
        ];
        $activeFilterDate = request('filter_date', request('fhilter_date'));
        $selectedChannelValue = $selectedChannel === 'all' ? '' : $selectedChannel;
        $toolbarFields = [
            [
                'type' => 'daterange',
                'name' => 'filter_date',
                'label' => translate('Select_Date'),
                'value' => $activeFilterDate,
                'placeholder' => translate('Select_Date'),
                'autocomplete' => 'off',
                'input_class' => 'js-daterangepicker-with-range form-control cursor-pointer',
                'attributes' => ['readonly' => 'readonly'],
            ],
            [
                'type' => 'select',
                'name' => 'status',
                'label' => translate('Status'),
                'value' => $selectedStatus,
                'options' => $statusOptions,
                'col_class' => 'col-xl-2 col-lg-6',
                'input_class' => 'form-control js-select2-custom set-filter',
            ],
            [
                'type' => 'select',
                'name' => 'Channel',
                'label' => translate('Channel'),
                'value' => $selectedChannelValue,
                'options' => [
                    '' => translate('All'),
                    'email' => translate('email'),
                    'form' => translate('form'),
                    'chat' => translate('chat'),
                    'social' => translate('social'),
                    'phone' => translate('phone'),
                ],
                'col_class' => 'col-xl-2 col-lg-6',
                'input_class' => 'form-control js-select2-custom set-filter',
            ],
            [
                'type' => 'number',
                'name' => 'choose_first',
                'label' => translate('Rows_to_show'),
                'value' => request('choose_first'),
                'placeholder' => translate('Ex') . ' : 200',
                'col_class' => 'col-xl-2 col-lg-6',
                'attributes' => ['min' => '1'],
            ],
            [
                'type' => 'search',
                'name' => 'searchValue',
                'label' => translate('search'),
                'value' => request('searchValue'),
                'placeholder' => translate('search_by_Name_or_Email_or_Phone'),
                'aria_label' => translate('search_by_Name_or_Email_or_Phone'),
                'col_class' => 'col-xl-3 col-lg-12',
            ],
        ];
        $toolbarSummary = [
            [
                'label' => translate('Status'),
                'value' => $statusOptions[$selectedStatus] ?? translate('All'),
            ],
        ];
        if (!request()->has('status')) {
            $toolbarSummary[] = [
                'value' => translate('default_status'),
                'muted' => true,
            ];
        }
        if ($selectedChannel !== 'all') {
            $toolbarSummary[] = [
                'label' => translate('Channel'),
                'value' => $channelOptions[$selectedChannel] ?? translate('All'),
                'muted' => true,
            ];
        }
        if (!empty($activeFilterDate)) {
            $toolbarSummary[] = [
                'label' => translate('Select_Date'),
                'value' => Str::limit($activeFilterDate, 28),
                'muted' => true,
            ];
        }
        if (request()->filled('searchValue')) {
            $toolbarSummary[] = [
                'label' => translate('search'),
                'value' => Str::limit(request('searchValue'), 28),
                'muted' => true,
            ];
        }
        if (request()->filled('choose_first')) {
            $toolbarSummary[] = [
                'label' => translate('Rows_to_show'),
                'value' => request('choose_first'),
                'muted' => true,
            ];
        }
        $headerActions = [
            [
                'type' => 'export',
                'url' => route('admin.crm.messages.export'),
                'form_id' => 'crm-inbox-toolbar',
                'label' => translate('export'),
            ],
            [
                'type' => 'button',
                'label' => translate('Bulk_convert'),
                'class' => 'btn btn-outline--primary text-nowrap bulk-convert-btn',
                'icon_html' => '🔀',
            ],
            [
                'type' => 'button',
                'label' => translate('Add Message'),
                'class' => 'btn btn-outline--primary text-nowrap',
                'icon_html' => '<i class="tio-add"></i>',
                'attributes' => [
                    'data-bs-toggle' => 'modal',
                    'data-bs-target' => '#addMessageModal',
                ],
            ],
        ];
    @endphp
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('Inbox_list')}}
            <span class="badge badge-soft-dark radius-50"></span>
        </h2>
    </div>
    @include('admin-views.crm.partials._list-toolbar', [
        'toolbarId' => 'crm-inbox-toolbar',
        'toolbarAction' => url()->current(),
        'toolbarResetUrl' => route('admin.crm.index'),
        'toolbarFields' => $toolbarFields,
        'toolbarSummary' => $toolbarSummary,
    ])
    <div class="card">
        @include('admin-views.crm.partials._list-card-header', [
            'listHeaderTitle' => translate('Inbox_list'),
            'listHeaderTotal' => $messages->total(),
            'listHeaderActions' => $headerActions,
        ])
        <div class="table-responsive datatable-custom">

            <table

                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th> <input type="checkbox" id="select-all">
                        </th>
                        <th>{{translate('SL')}}</th>
                        <th class="text-center">{{translate('Subject')}}</th>
                        <th>{{translate('Channel')}}</th>
                        <th>{{translate('Source')}}</th>
                        <th>{{translate('Name')}}</th>
                        <th>{{translate('Contact')}}</th>
                        <th>{{translate('Owner')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th>{{translate('Received_At')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $key=> $msg)
                    <tr id="row-{{ $msg->id }}" data-owner-id="{{ $msg->owner_id ?? '' }}">
                        <td> <input type="checkbox" class="message-checkbox" value="{{ $msg->id }}">
                        </td>
                        <td>
                            {{$messages->firstItem()+$key}}
                        </td>
                        <td>
                            <a href="{{ route('admin.crm.message.show', $msg->id) }}" class="crm-primary-link">
                                {{ $msg->subject ?? translate('No Subject') }}
                            </a>
                        </td>
                        <td>{{ ucfirst($msg->pipeline) }} - {{ $msg->message_type }}</td>
                        <!-- <td>


                            <div class=" text-success d-flex align-items-center gap-3 font-weight-bolder mb-2">
                                {{ $msg->message_type }}
                                <div class="ripple-animation edit-message-type"
                                    data-bs-toggle="modal"
                                    data-bs-target="#showTypeModal"
                                    data-id="{{ $msg->id }}"
                                    data-current-type="{{ $msg->message_type }}">
                                    <i class="tio-edit"></i>
                                </div>
                            </div>

                        </td> -->
                        <td>{{ $msg->source_id ??  translate('Not Available') }}</td>
                        <td>{{ $msg->sender_name ??  translate('Not Available') }}</td>

                        <td>
                            <div class="mb-1">
                                <strong><a class="title-color hover-c1"
                                        href="mailto:{{$msg->sender_email}}">{{$msg->sender_email}}</a></strong>
                            </div>
                            <a class="title-color hover-c1" href="tel:{{$msg->sender_phone}}">{{$msg->sender_phone}}</a>

                        </td>
                        <td>{{ $msg->owner?->name ?? translate('Not Assigned') }}</td>
                        <td>
                            @php
                            $status = strtolower($msg->status);
                            $statusClass = match ($status) {
                            'new' => 'text-primary bg-soft-primary',
                            'processing' => 'text-warning bg-soft-warning',
                            'converted' => 'text-success bg-soft-success',
                            'ignored' => 'text-secondary bg-soft-secondary',
                            'spam' => 'text-danger bg-soft-danger',
                            default => 'text-dark bg-soft-light',
                            };
                            @endphp

                            <span class="btn {{ $statusClass }} font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ \App\Utils\crm_status_label($msg->status) }}
                            </span>
                        </td>
                        <td><span class="bidi-ltr d-inline-block">{{ $msg->created_at->format('d M, Y H:i') }}</span></td>
                        <td>
                            @php
                            $canConvertMessage = $msg->status != 'ignored' && $msg->status != 'spam' && $msg->status != 'converted';
                            $suggestion = null;
                            if ($msg->contact_id == null) {
                                $suggestion = \App\Models\InboxSuggestion::where('inbox_message_id', $msg->id)
                                    ->where('status', 'pending')
                                    ->first();
                            }
                            @endphp
                            <div class="crm-row-actions">
                                <div class="crm-row-actions__primary">
                                    <a href="{{ route('admin.crm.message.show', $msg->id) }}" class="btn btn-sm btn-outline-info">
                                        {{ translate('View') }}
                                    </a>
                                    @if($canConvertMessage)
                                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-id="{{ $msg['id'] }}" data-bs-target="#convertModal">
                                        🔀 {{ translate('Convert') }}
                                    </a>
                                    @endif
                                </div>
                                @if(!$msg->owner_id)
                                <div class="crm-row-actions__chips">
                                    <span class="crm-row-actions__chip">{{ translate('No Owner') }}</span>
                                </div>
                                @endif
                                <div class="dropdown crm-row-actions__menu">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle crm-row-actions__toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ translate('More actions') }}">
                                        <i class="tio-more-horizontal"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        @if(\App\Utils\Helpers::module_permission_check('crm_section', 'inbox_assign_owner'))
                                        <a href="javascript:void(0)"
                                            class="dropdown-item assign-owner-btn"
                                            data-id="{{ $msg->id }}"
                                            data-owner-id="{{ $msg->owner_id ?? '' }}"
                                            data-department-id="{{ $msg->department_id ?? '' }}"
                                            data-bs-toggle="false"
                                            data-bs-target="none">
                                            {{ $msg->owner_id ? translate('Re-Assign Owner') : translate('Assign Owner') }}
                                        </a>
                                        @endif
                                        @if($suggestion)
                                        <button type="button" class="dropdown-item suggestion-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#suggestionModal"
                                            data-message-id="{{ $msg->id }}"
                                            data-user-id="{{ $suggestion->user_id }}">
                                            {{ translate('Suggestion') }}
                                            <span class="badge bg-danger ms-auto">{{ translate('Reg') }}</span>
                                        </button>
                                        @endif
                                        @if($canConvertMessage)
                                        <div class="dropdown-divider"></div>
                                        <a href="javascript:void(0)" class="dropdown-item text-danger mark-spam-btn" data-id="{{ $msg['id'] }}">
                                            {{ translate('Mark Spam') }}
                                        </a>
                                        <a href="javascript:void(0)"
                                            class="dropdown-item ignore-btn"
                                            data-id="{{ $msg['id'] }}">
                                            {{ translate('Ignore') }}
                                        </a>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item text-danger delete-btn" data-id="{{ $msg->id }}">
                                            {{ translate('Delete') }}
                                        </button>
                                    </div>
                                </div>
                                <span class="d-none delete-route" data-id="{{ $msg->id }}"
                                    data-url="{{ route('admin.crm.messages.destroy', $msg->id) }}"></span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $messages->links() !!}
            </div>
        </div>
        @if(count($messages)==0)
        @include('layouts.back-end._empty-state',['text'=>'no_record_found'],['image'=>'default'])
        @endif
    </div>
</div>

<div class="modal fade" id="suggestionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title">                                   {{ translate('User_Suggestion') }}
</h5>
                <button type="button" class="radius-50 btn-close border-0" data-bs-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="suggestion-user-info"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="connect-user-btn">                                      {{ translate('Yes_Connect') }}
</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">                                     {{ translate('Cancel') }}
</button>
            </div>
        </div>
    </div>
</div>

@include('admin-views.crm.partials.departments')
@include('admin-views.crm.partials.employee')
@include('admin-views.crm.partials.owner')
@include('admin-views.crm.partials.type')
@include('admin-views.crm.partials.convert')
@include('admin-views.crm.partials.convert-bulk')
@include('admin-views.crm.partials.add-message')

<span id="ignoreRoute" data-url="{{ route('admin.crm.ignore') }}"></span>
<span id="spamRoute" data-url="{{ route('admin.crm.mark-spam') }}"></span>
<span id="getEmployeeRoute" data-url="{{ route('admin.crm.getemployee') }}"></span>
<span id="assignOwnerRoute" data-url="{{ route('admin.crm.assignment-update') }}"></span>
<span id="assignEmployeeRoute" data-url="{{ route('admin.crm.assignment-update') }}"></span>
<span id="assignDepartmentRoute" data-url="{{ route('admin.crm.assignment-update') }}"></span>
<span id="getUserRoute" data-route="{{ url('admin/crm/inbox/user-info') }}"></span>
<span id="connectUserRoute" data-route="{{ route('admin.crm.inbox.connect-user') }}"></span>


@endsection

@push('script')
@include('admin-views.crm.partials._crm-js-text')
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/crm.js') }}"></script>
@endpush
