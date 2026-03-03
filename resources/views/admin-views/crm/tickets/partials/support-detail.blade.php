@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $isRtl = session('direction') === 'rtl';

    $toI18nKey = static function (?string $value): string {
        return Str::of((string)$value)
            ->trim()
            ->replace(['-', '/', ':'], ' ')
            ->replaceMatches('/\s+/', '_')
            ->lower()
            ->toString();
    };

    $translateDynamic = static function (?string $value) use ($toI18nKey): string {
        $text = trim((string)$value);
        if ($text === '') {
            return translate('N/A');
        }

        $normalized = $toI18nKey($text);
        $translated = translate($normalized);

        return $translated !== $normalized ? $translated : $text;
    };

    $translateWithReplace = static function (string $key, array $replace = []): string {
        $message = (string) translate($key);
        if (empty($replace)) {
            return $message;
        }

        $pairs = [];
        foreach ($replace as $placeholder => $value) {
            $pairs[':' . $placeholder] = (string) $value;
        }

        return strtr($message, $pairs);
    };

    $translateActivityTitle = static function (?string $title) use ($translateDynamic): string {
        $titleText = trim((string)$title);
        if ($titleText === '') {
            return translate('N/A');
        }

        $titleMap = [
            'Ticket Viewed' => 'ticket_viewed',
            'Status Updated' => 'status_updated',
            'Priority Updated' => 'priority_updated',
            'Reply Added' => 'reply_added',
            'Escalated' => 'escalated',
            'Escalation Updated' => 'escalation_updated',
            'Support Ticket Follow-Up' => 'support_ticket_follow_up',
            'Admin Reply Added' => 'admin_reply_added',
        ];

        if (isset($titleMap[$titleText])) {
            return translate($titleMap[$titleText]);
        }

        return $translateDynamic($titleText);
    };

    $translateActivityDescription = static function (?string $description) use ($translateDynamic): string {
        $descText = trim((string)$description);
        if ($descText === '') {
            return translate('N/A');
        }

        if ($descText === 'Admin viewed ticket details.') {
            return translate('admin_viewed_ticket_details');
        }

        if (preg_match('/^Priority changed from (.+) to (.+)\.$/i', $descText, $m)) {
            return $translateWithReplace('priority_changed_from_to', [
                'from' => $translateDynamic($m[1]),
                'to' => $translateDynamic($m[2]),
            ]);
        }

        if (preg_match('/^Status changed from (.+) to (.+)\. Reopened: (Yes|No)$/i', $descText, $m)) {
            return $translateWithReplace('status_changed_from_to_reopened', [
                'from' => $translateDynamic($m[1]),
                'to' => $translateDynamic($m[2]),
                'reopened' => $m[3] === 'Yes' ? translate('yes') : translate('no'),
            ]);
        }

        if (preg_match('/^Task added: (.+), Due: (.+)$/i', $descText, $m)) {
            return $translateWithReplace('task_added_due', [
                'task' => $m[1],
                'due' => $m[2],
            ]);
        }

        if (preg_match('/^Status changed from (.+)$/i', $descText, $m)) {
            return $translateWithReplace('status_changed_from_only', [
                'from' => $translateDynamic($m[1]),
            ]);
        }

        if (preg_match('/^Ticket #(\d+) escalated\. Reason:\s*(.+)$/i', $descText, $m)) {
            return $translateWithReplace('ticket_escalated_reason', [
                'id' => $m[1],
                'reason' => $m[2],
            ]);
        }

        if (preg_match('/^Escalation #(\d+) status changed from (.+) to (.+)\.$/i', $descText, $m)) {
            return $translateWithReplace('escalation_status_changed_from_to', [
                'id' => $m[1],
                'from' => $translateDynamic($m[2]),
                'to' => $translateDynamic($m[3]),
            ]);
        }

        if (preg_match('/^Support follow-up - Status:\s*(.+?)\s*\((\d+)\),\s*Note:\s*(.+?)(?:,\s*Follow-up Date:\s*([^,.]+))?(?:\.\s*Status changed from\s*([^,.]+))?(?:,\s*Task added:\s*(.+?),\s*Due:\s*(.+))?$/i', $descText, $m)) {
            $translated = $translateWithReplace('support_follow_up_status_note', [
                'status' => $translateDynamic($m[1]),
                'status_id' => $m[2],
                'note' => trim($m[3]),
            ]);

            if (!empty($m[4])) {
                $translated .= ', ' . translate('follow_up_date') . ': ' . trim($m[4]);
            }

            if (!empty($m[5])) {
                $translated .= '. ' . $translateWithReplace('status_changed_from_only', [
                    'from' => $translateDynamic($m[5]),
                ]);
            }

            if (!empty($m[6]) || !empty($m[7])) {
                $translated .= ', ' . $translateWithReplace('task_added_due', [
                    'task' => trim((string)$m[6]),
                    'due' => trim((string)$m[7]),
                ]);
            }

            return $translated;
        }

        if (preg_match('/^Admin replied(?: to ticket)?:\s*(.*)$/i', $descText, $m)) {
            return translate('admin_replied') . ': ' . trim($m[1]);
        }

        return $translateDynamic($descText);
    };
@endphp
@extends('layouts.back-end.app')

@section('title', translate('ticket_details') . ' #' . $ticket->id)

@section('content')
<div class="content container-fluid">
    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset('public/assets/back-end/img/support_ticket.png') }}" alt="">
            {{ translate('ticket_details') }} #{{ $ticket->id }}
        </h2>
        <a href="javascript:history.back()" class="btn btn--primary">{{ translate('back') }}</a>
    </div>

    <div class="row">
        <!-- Ticket Info -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                <div class="card-header">
                    <h5 class="mb-0">{{ translate('ticket_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <strong>{{ translate('subject') }}</strong>
                        <span class="text-end" dir="auto">{{ $ticket->subject ?? translate('N/A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <strong>{{ translate('type') }}</strong>
                        <span class="text-end" dir="auto">{{ $translateDynamic($ticket->type) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <strong>{{ translate('sub_type') }}</strong>
                        <span class="text-end" dir="auto">{{ $translateDynamic($ticket->sub_type ? Str::replace('_', ' ', $ticket->sub_type) : null) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <strong>{{ translate('priority') }}</strong>
                        <span class="badge badge-soft-{{ $ticket->priority == 'low' ? 'primary' : ($ticket->priority == 'medium' ? 'info' : ($ticket->priority == 'high' ? 'warning' : ($ticket->priority == 'critical' ? 'danger' : 'secondary'))) }}">
                            {{ $translateDynamic($ticket->priority) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <strong>{{ translate('status') }}</strong>
                        <span class="badge badge-soft-info">{{ $translateDynamic($ticket->status_details->name ?? null) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <strong>{{ translate('department') }}</strong>
                        <span class="text-end" dir="auto">{{ $translateDynamic($ticket->department->name ?? translate('unassigned')) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <strong>{{ translate('assigned_employee') }}</strong>
                        <span class="text-end" dir="auto">{{ $ticket->employee->name ?? translate('unassigned') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <strong>{{ translate('created_at') }}</strong>
                        <span class="text-end" dir="auto">{{ $ticket->created_at?->translatedFormat('d M, Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <strong>{{ translate('reopen_count') }}</strong>
                        <span class="text-end" dir="auto">{{ $ticket->reopen_count ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer / Inbox Message Info -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                <div class="card-header">
                    <h5 class="mb-0">
                        @if($ticket->customer)
                            {{ translate('customer_information') }}
                        @else
                            {{ translate('inbox_message_source') }}
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if($ticket->customer)
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <strong>{{ translate('name') }}</strong>
                            <span class="text-end" dir="auto">{{ $ticket->customer->f_name }} {{ $ticket->customer->l_name }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <strong>{{ translate('email') }}</strong>
                            <span class="text-end" dir="ltr">{{ $ticket->customer->email }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <strong>{{ translate('phone') }}</strong>
                            <span class="text-end" dir="ltr">{{ $ticket->customer->phone ?? translate('N/A') }}</span>
                        </div>
                    @elseif($ticket->relatedInboxMessages->isNotEmpty())
                        @php
                            $msg = $ticket->relatedInboxMessages->first();
                        @endphp
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <strong>{{ translate('Sender Name') }}</strong>
                            <span class="text-end" dir="auto">{{ $msg->sender_name ?? translate('N/A') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <strong>{{ translate('Sender Email') }}</strong>
                            <span class="text-end" dir="ltr">{{ $msg->sender_email ?? translate('N/A') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <strong>{{ translate('Sender Phone') }}</strong>
                            <span class="text-end" dir="ltr">{{ $msg->sender_phone ?? translate('N/A') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <strong>{{ translate('subject') }}</strong>
                            <span class="text-end" dir="auto">{{ $msg->subject ?? translate('N/A') }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>{{ translate('message') }}</strong>
                            <div class="pt-1" dir="auto">{!! nl2br(e($msg->message)) !!}</div>
                        </div>

                        @if($msg->attachment)
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <strong>{{ translate('attachment') }}</strong>
                                <a href="{{ $msg->attachment_full_url }}" target="_blank">{{ translate('view') }}</a>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">{{ translate('no_customer_or_source_found') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('admin-views.crm.partials.escalation-panel', ['escalations' => $ticket->escalations ?? collect()])

    <!-- Activity Log -->
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">{{ translate('activity_log') }}</h5>
        </div>
        <div class="card-body">
            @if($ticket->supportActivities->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('title') }}</th>
                                <th>{{ translate('description') }}</th>
                                <th>{{ translate('employee') }}</th>
                                <th>{{ translate('noted_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ticket->supportActivities as $act)
                                <tr>
                                    <td dir="auto">{{ $translateActivityTitle($act->title) }}</td>
                                    <td dir="auto">{{ $translateActivityDescription($act->description) }}</td>
                                    <td>{{ $act->employee?->name ?? translate('System') }}</td>
                                    <td>{{ Carbon::parse($act->noted_at)->translatedFormat('d M, Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">{{ translate('no_activity_logged') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection

