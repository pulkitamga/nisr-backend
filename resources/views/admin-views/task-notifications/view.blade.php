@extends('layouts.back-end.app')

@section('title', translate('View'))

@section('content')
<style>
    .pair-list .key {
        min-width: 150px;
    }
</style>
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_5926_1152)">
                    <path d="M10 20.5C11.1046 20.5 12 19.6046 12 18.5H8C8 19.6046 8.89543 20.5 10 20.5ZM16 14.5V9.5C16 6.57436 14.3682 4.15379 11.75 3.53235V3C11.75 2.30964 11.1904 1.75 10.5 1.75C9.80964 1.75 9.25 2.30964 9.25 3V3.53235C6.63184 4.15379 5 6.57436 5 9.5V14.5L3 16.5V17.5H17V16.5L16 14.5Z" fill="#073b74"></path>
                </g>
                <defs>
                    <clipPath id="clip0_5926_1152">
                        <rect width="20" height="20" fill="white" transform="translate(0 0.5)"></rect>
                    </clipPath>
                </defs>
            </svg>
            {{translate('View_Notifications')}}
        </h2>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2 border-bottom pb-3 mb-4 pl-4">
                        {{ translate('notification_Information') }}
                    </h5>
                    <div class="col-md-12">
                        <div class="col-sm-12 col-md-12">
                            <div class="pair-list">
                                <!-- <div>
                                    <span class="key text-nowrap">{{ translate('support_ticket_title') }}</span>
                                    <span>:</span>
                                    <span class="value ">{{ $SupportnotificationData->supportTicket->subject }}</span>
                                </div>
                                <div>
                                    <span class="key">{{ translate('support_ticket_description') }}</span>
                                    <span>:</span>
                                    <span class="value">{{ $SupportnotificationData->supportTicket->description }}</span>
                                </div>
                                <div>
                                    <span class="key">{{ translate('support_ticket_type') }}</span>
                                    <span>:</span>
                                    <span class="value">{{ $SupportnotificationData->supportTicket->type }}</span>
                                </div>
                                <div>
                                    <span class="key">{{ translate('priority') }}</span>
                                    <span>:</span>
                                    <span class="value">{{ $SupportnotificationData->supportTicket->priority }}</span>
                                </div> -->
                                <div>
                                    <span class="key">{{ translate('title') }}</span>
                                    <span>:</span>
                                    <span class="value">{{ $SupportnotificationData->title }}</span>
                                </div>
                                <div>
                                    <span class="key">{{ translate('message') }}</span>
                                    <span>:</span>
                                    <span class="value">{{ $SupportnotificationData->message }}</span>
                                </div>
                                <div>
                                    <span class="key">{{ translate('occur') }}</span>
                                    <span>:</span>
                                    <?php use Carbon\Carbon;  ?>
                                    <span class="value">
                                    <?php 
                                            if (!empty($SupportnotificationData->created_at)) {
                                                $createdAt = Carbon::parse($SupportnotificationData->created_at);
                                                echo ($createdAt->diffInDays(Carbon::now()) < 7) 
                                                    ? $createdAt->format('D h:i A') 
                                                    : $createdAt->format('d M Y h:i A');
                                            } else {
                                                echo "N/A"; 
                                            }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection