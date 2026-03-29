@extends('layouts.back-end.app')

@section('title', translate('service_Ticket'))

@push('css_or_js')
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

@php($isRtl = Session::get('direction') === 'rtl')
<div class="content container-fluid">
    <h2 class="mt-3">{{ translate('ticket_details') }} #<span class="bidi-ltr">{{ $supportTicket->id }}</span></h2>
    <div class="row">
        <div class="col-md-6">
            <div class="card" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                <div class="card-header">{{ translate('candidate_details') }}</div>
                <div class="card-body">
                    <p><strong>{{ translate('name') }}:</strong> <span class="bidi-auto">{{ $supportTicket->customer->name ?? translate('N/A') }}</span></p>
                    <p><strong>{{ translate('email') }}:</strong> <span class="bidi-ltr">{{ $supportTicket->customer->email ?? translate('N/A') }}</span></p>
                    <p><strong>{{ translate('phone') }}:</strong> <span class="bidi-ltr">{{ $supportTicket->customer->phone ?? translate('N/A') }}</span></p>
                    @if($supportTicket->conversations->whereNotNull('attachment')->count() > 0)
                    <p><strong>{{ translate('cv') }}:</strong>
                        @foreach($supportTicket->conversations->whereNotNull('attachment') as $conv)
                        <a href="{{ $conv->attachment }}" target="_blank">{{ translate('view') }}</a>
                        @endforeach
                    </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                <div class="card-header">{{ translate('ticket_details') }}</div>
                <div class="card-body">
                    <p><strong>{{ translate('subject') }}:</strong> <span class="bidi-auto">{{ $supportTicket->subject }}</span></p>
                    <p><strong>{{ translate('status') }}:</strong> <span class="bidi-auto">{{ $supportTicket->status_details?->getTranslatedField('name') ?? translate('N/A') }}</span></p>
                    <p><strong>{{ translate('recruiter') }}:</strong> <span class="bidi-auto">{{ $supportTicket->employee->name ?? translate('Unassigned') }}</span></p>
                    <p><strong>{{ translate('created_at') }}:</strong> <span class="bidi-ltr">{{ $supportTicket->created_at->format('d-m-Y H:i') }}</span></p>
                </div>
            </div>
        </div>
    </div>

    @include('admin-views.crm.partials.escalation-panel', ['escalations' => $supportTicket->escalations ?? collect()])

    <div class="card mt-3" >
        <div class="card-header">
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#activity">{{ translate('Activity') }}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#interviews">{{ translate('interviews') }}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#offers">{{ translate('offers') }}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#rejections">{{ translate('rejections') }}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#talent-pool">{{ translate('talent_pool') }}</a></li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane active" id="activity">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ translate('Type') }}</th>
                                <th>{{ translate('Description') }}</th>
                                <th>{{ translate('Created By') }}</th>
                                <th>{{ translate('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supportTicket->careerActivities as $activity)
                            <tr>
                                <td>{{ $activity->activity_type }}</td>
                                <td>{{ $activity->description }}</td>
                                <td>{{ $activity->createdBy->name ?? translate('System') }}</td>
                                <td><span class="bidi-ltr">{{ $activity->created_at->format('d-m-Y H:i') }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4">{{ translate('no_activity_logged') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane" id="interviews">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ translate('Scheduled At') }}</th>
                                <th>{{ translate('outcome') }}</th>
                                <th>{{ translate('panel') }}</th>
                                <th>{{ translate('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supportTicket->careerInterviews as $interview)
                            <tr>
                                <td>{{ $interview->scheduled_at }}</td>
                                <td>{{ $interview->outcome ?? translate('Scheduled') }}</td>
                                <td>{{ implode(', ', (array) ($interview->panel ?? [])) }}</td>
                                <td>{{ $interview->notes }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane" id="offers">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ translate('status') }}</th>
                                <th>{{ translate('start_date') }}</th>
                                <th>{{ translate('attachment') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supportTicket->careerOffers as $offer)
                            <tr>
                                <td>{{ $offer->status }}</td>
                                <td>{{ $offer->start_date }}</td>
                                <td><a href="{{ route('admin.support-ticket.career.offer.download', $offer->id) }}" target="_blank">{{ translate('view') }}</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane" id="rejections">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ translate('reason_code') }}</th>
                                <th>{{ translate('message') }}</th>
                                <th>{{ translate('date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supportTicket->careerRejections as $rejection)
                            <tr>
                                <td>{{ $rejection->reason_code }}</td>
                                <td>{{ $rejection->closure_message }}</td>
                                <td><span class="bidi-ltr">{{ $rejection->created_at }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane" id="talent-pool">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ translate('consent') }}</th>
                                <th>{{ translate('recontact_date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($pool = $supportTicket->careerTalentPool)

                            @if($pool)
                            <tr>
                                <td>{{ $pool->consent ? translate('yes') : translate('no') }}</td>
                                <td>{{ $pool->recontact_date ?? translate('N/A') }}</td>
                            </tr>
                            @else
                            <tr>
                                <td colspan="2">{{ translate('no_records_found') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
