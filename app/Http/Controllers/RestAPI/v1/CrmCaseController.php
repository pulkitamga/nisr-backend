<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\InboxActivities;
use App\Models\InboxMessage;
use App\Models\SupportTicket;
use App\Models\SupportTicketActivity;
use App\Models\SupportTicketConv;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CrmCaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cases = $this->customerCaseQuery($request->user())
            ->whereNotIn('status', ['spam', 'ignored'])
            ->with(['employee', 'owner', 'ticket.status_details'])
            ->latest()
            ->get()
            ->map(fn (InboxMessage $message): array => $this->transformCase($message))
            ->sortByDesc('updated_at')
            ->values();

        return response()->json([
            'cases' => $cases,
        ], 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $case = $this->findCustomerCaseOrFail($request, $id);

        return response()->json([
            'case' => $this->transformCase($case, true),
        ], 200);
    }

    public function timeline(Request $request, int $id): JsonResponse
    {
        $case = $this->findCustomerCaseOrFail($request, $id);

        return response()->json([
            'timeline' => $this->buildTimeline($case),
        ], 200);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $case = $this->findCustomerCaseOrFail($request, $id);
        $ticket = $case->ticket;

        if (!$ticket) {
            return response()->json([
                'message' => translate('crm_case_reply_requires_ticket'),
            ], 422);
        }

        $status = $this->resolveStatusData($case, $ticket);
        if ($status['status'] === 'closed') {
            return response()->json([
                'message' => translate('crm_ticket_already_closed'),
            ], 422);
        }

        $reply = new SupportTicketConv();
        $reply->support_ticket_id = $ticket->id;
        $reply->admin_id = 0;
        $reply->customer_message = trim((string)$request->input('message'));
        $reply->save();

        return response()->json([
            'message' => translate('crm_reply_sent_successfully'),
        ], 200);
    }

    protected function findCustomerCaseOrFail(Request $request, int $id): InboxMessage
    {
        $case = $this->customerCaseQuery($request->user())
            ->with(['employee', 'owner', 'ticket.status_details'])
            ->find($id);

        abort_if(!$case, 404, translate('crm_case_not_found'));

        return $case;
    }

    protected function customerCaseQuery($customer)
    {
        return InboxMessage::query()
            ->where(function ($query) use ($customer) {
                $query->where('contact_id', $customer->id);

                if (!empty($customer->email)) {
                    $query->orWhere('sender_email', $customer->email);
                }

                if (!empty($customer->phone)) {
                    $query->orWhere('sender_phone', $customer->phone);
                }
            });
    }

    protected function transformCase(InboxMessage $message, bool $withTimeline = false): array
    {
        $ticket = $message->ticket;
        $status = $this->resolveStatusData($message, $ticket);
        $category = data_get($message->details, 'category')
            ?? $this->normalizeCategory($message->message_type);

        $lastUpdateAt = collect([
            $message->updated_at,
            $ticket?->updated_at,
            optional($ticket?->conversations()->latest()->first())->created_at,
            optional($message->activities()->latest()->first())->created_at,
        ])->filter()->max();

        $payload = [
            'id' => (string)$message->id,
            'reference' => 'CASE-' . $message->id,
            'category' => (string)$category,
            'subject' => (string)(data_get($message->details, 'subject') ?: $message->subject),
            'status' => $status['status'],
            'status_key' => $status['status_key'],
            'status_label' => $status['status_label'],
            'priority' => (string)($ticket?->priority ?: $message->priority ?: 'medium'),
            'created_at' => optional($message->created_at)?->toIso8601String(),
            'updated_at' => optional($lastUpdateAt)?->toIso8601String(),
            'is_converted' => (bool)$message->related_ticket_id,
            'ticket_id' => $message->related_ticket_id ? (string)$message->related_ticket_id : null,
            'last_update' => $lastUpdateAt ? Carbon::parse($lastUpdateAt)->toDateTimeString() : null,
            'next_step' => $this->resolveNextStep($message, $ticket, $status['status']),
        ];

        if ($withTimeline) {
            $payload['timeline'] = $this->buildTimeline($message);
        }

        return $payload;
    }

    protected function buildTimeline(InboxMessage $message): array
    {
        $timeline = collect();

        $timeline->push([
            'event_type' => 'inquiry_submitted',
            'title' => translate('crm_timeline_inquiry_submitted'),
            'description' => (string)(data_get($message->details, 'message') ?: $message->message ?: $message->subject),
            'actor_type' => 'customer',
            'actor_id' => (string)($message->contact_id ?? ''),
            'actor_name' => $message->sender_name ?: translate('crm_actor_customer'),
            'case_id' => (string)$message->id,
            'ticket_id' => $message->related_ticket_id ? (string)$message->related_ticket_id : null,
            'created_at' => optional($message->created_at)?->toIso8601String(),
        ]);

        $message->loadMissing(['activities.employee', 'ticket.conversations.adminInfo', 'ticket.activities.employee']);

        foreach ($message->activities as $activity) {
            $timeline->push($this->transformInboxActivity($message, $activity));
        }

        if ($message->ticket) {
            foreach ($message->ticket->activities as $activity) {
                $timeline->push($this->transformTicketActivity($message, $activity));
            }

            foreach ($message->ticket->conversations as $conversation) {
                $timeline->push($this->transformConversation($message, $conversation));
            }
        }

        return $timeline
            ->filter()
            ->sortBy('created_at')
            ->values()
            ->all();
    }

    protected function transformInboxActivity(InboxMessage $message, InboxActivities $activity): ?array
    {
        $eventType = match ($activity->activity_type) {
            'conversion' => 'converted_to_ticket',
            'assignment_update' => 'case_assigned',
            default => 'status_updated',
        };

        return [
            'event_type' => $eventType,
            'title' => (string)($activity->title ?: ucfirst(str_replace('_', ' ', $eventType))),
            'description' => $this->localizeTimelineDescription(
                (string)($activity->subject ?: data_get($activity->details, 'description', ''))
            ),
            'actor_type' => 'admin',
            'actor_id' => (string)($activity->employee_id ?? ''),
            'actor_name' => $activity->employee?->name ?: translate('crm_actor_crm'),
            'case_id' => (string)$message->id,
            'ticket_id' => $message->related_ticket_id ? (string)$message->related_ticket_id : null,
            'created_at' => optional($activity->created_at ?: $activity->note_date)?->toIso8601String(),
        ];
    }

    protected function transformTicketActivity(InboxMessage $message, SupportTicketActivity $activity): ?array
    {
        $description = trim((string)($activity->description ?? ''));
        $lowerDescription = strtolower($description);
        $eventType = str_contains($lowerDescription, 'priority')
            ? 'priority_updated'
            : (str_contains($lowerDescription, 'close') ? 'ticket_closed' : 'status_updated');

        return [
            'event_type' => $eventType,
            'title' => (string)($activity->title ?: ucfirst(str_replace('_', ' ', $eventType))),
            'description' => $this->localizeTimelineDescription($description),
            'actor_type' => 'admin',
            'actor_id' => (string)($activity->employee_id ?? ''),
            'actor_name' => $activity->employee?->name ?: translate('crm_actor_support'),
            'case_id' => (string)$message->id,
            'ticket_id' => (string)$activity->support_ticket_id,
            'created_at' => optional($activity->noted_at ?: $activity->created_at)?->toIso8601String(),
        ];
    }

    protected function transformConversation(InboxMessage $message, SupportTicketConv $conversation): ?array
    {
        $isCustomerReply = !empty($conversation->customer_message);

        return [
            'event_type' => $isCustomerReply ? 'customer_replied' : 'agent_replied',
            'title' => $isCustomerReply
                ? translate('crm_timeline_customer_replied')
                : translate('crm_timeline_agent_replied'),
            'description' => (string)($conversation->customer_message ?: $conversation->admin_message ?: ''),
            'actor_type' => $isCustomerReply ? 'customer' : 'admin',
            'actor_id' => (string)($isCustomerReply ? ($message->contact_id ?? '') : ($conversation->admin_id ?? '')),
            'actor_name' => $isCustomerReply
                ? ($message->sender_name ?: translate('crm_actor_customer'))
                : ($conversation->adminInfo?->name ?: translate('crm_actor_support')),
            'case_id' => (string)$message->id,
            'ticket_id' => (string)$conversation->support_ticket_id,
            'created_at' => optional($conversation->created_at)?->toIso8601String(),
        ];
    }

    protected function resolveStatusData(InboxMessage $message, ?SupportTicket $ticket): array
    {
        if ($ticket) {
            $statusLabel = trim((string) (
                $ticket->status_details?->name
                ?? $ticket->status_details?->status
                ?? ''
            ));
            $statusKey = $this->normalizeStatusKey($statusLabel !== '' ? $statusLabel : $ticket->status);

            return [
                'status' => $this->mapStatusGroup($statusKey),
                'status_key' => $statusKey,
                'status_label' => $statusLabel !== '' ? $statusLabel : $this->formatStatusLabel($statusKey),
            ];
        }

        $statusKey = $this->normalizeStatusKey($message->status);

        return [
            'status' => $this->mapStatusGroup($statusKey),
            'status_key' => $statusKey,
            'status_label' => $this->formatStatusLabel($statusKey),
        ];
    }

    protected function normalizeStatusKey(mixed $rawStatus): string
    {
        $normalized = Str::of((string)$rawStatus)
            ->trim()
            ->lower()
            ->replace(['-', ' '], '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->value();

        return match ($normalized) {
            '', 'null' => 'new',
            'close' => 'closed',
            'opened' => 'open',
            'inprogress' => 'in_progress',
            'onhold' => 'on_hold',
            default => $normalized,
        };
    }

    protected function mapStatusGroup(string $statusKey): string
    {
        if ($statusKey === 'new') {
            return 'new';
        }

        if ($statusKey === 'converted') {
            return 'converted';
        }

        if (
            in_array($statusKey, ['closed', 'resolved', 'done', 'completed', 'complete', 'cancelled', 'canceled'], true)
            || str_contains($statusKey, 'clos')
            || str_contains($statusKey, 'resolv')
            || str_contains($statusKey, 'cancel')
            || str_contains($statusKey, 'complet')
        ) {
            return 'closed';
        }

        if (
            in_array($statusKey, ['pending', 'hold', 'on_hold'], true)
            || str_contains($statusKey, 'wait')
            || str_contains($statusKey, 'hold')
        ) {
            return 'waiting';
        }

        return 'processing';
    }

    protected function formatStatusLabel(string $statusKey): string
    {
        return Str::of($statusKey)
            ->replace('_', ' ')
            ->headline()
            ->value();
    }

    protected function localizeTimelineDescription(string $description): string
    {
        $description = trim($description);
        if ($description === '') {
            return '';
        }

        if (strcasecmp($description, 'Submitted from mobile contact form') === 0) {
            return translate('crm_submitted_from_mobile_contact_form');
        }

        $segments = preg_split('/\.\s*/', trim($description, ". \t\n\r\0\x0B"));
        if (!$segments || count($segments) === 0) {
            return $description;
        }

        $translatedSegments = [];
        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }

            $translatedSegments[] = $this->localizeTimelineSegment($segment);
        }

        if (empty($translatedSegments)) {
            return $description;
        }

        return implode('. ', $translatedSegments) . '.';
    }

    protected function localizeTimelineSegment(string $segment): string
    {
        if (preg_match('/^Status changed from (.+?) to (.+?)$/i', $segment, $matches) === 1) {
            return strtr(translate('crm_status_changed_from_to'), [
                ':from' => $this->translateStatusName($matches[1]),
                ':to' => $this->translateStatusName($matches[2]),
            ]);
        }

        if (preg_match('/^Status changed from (.+)$/i', $segment, $matches) === 1) {
            return strtr(translate('status_changed_from_only'), [
                ':from' => $this->translateStatusName($matches[1]),
            ]);
        }

        if (preg_match('/^Reopened:\s*(Yes|No)$/i', $segment, $matches) === 1) {
            return strtr(translate('crm_reopened_label'), [
                ':value' => translate(strtolower($matches[1])),
            ]);
        }

        if (preg_match('/^Department assigned:\s*ID\s*(\d+)$/i', $segment, $matches) === 1) {
            return strtr(translate('crm_department_assigned_with_id'), [
                ':id' => $matches[1],
            ]);
        }

        if (preg_match('/^Changed from department ID\s*(\d+)$/i', $segment, $matches) === 1) {
            return strtr(translate('crm_changed_from_department_id'), [
                ':id' => $matches[1],
            ]);
        }

        if (preg_match('/^Employee assigned:\s*ID\s*(\d+)$/i', $segment, $matches) === 1) {
            return strtr(translate('crm_employee_assigned_with_id'), [
                ':id' => $matches[1],
            ]);
        }

        if (preg_match('/^Changed from employee ID\s*(\d+)$/i', $segment, $matches) === 1) {
            return strtr(translate('crm_changed_from_employee_id'), [
                ':id' => $matches[1],
            ]);
        }

        if (preg_match('/^Department name:\s*(.+)$/i', $segment, $matches) === 1) {
            return strtr(translate('crm_department_name_label'), [
                ':name' => trim($matches[1]),
            ]);
        }

        return $segment;
    }

    protected function translateStatusName(string $statusName): string
    {
        $statusKey = $this->normalizeStatusKey($statusName);

        return match ($statusKey) {
            'new' => translate('New'),
            'open', 'opened' => translate('open'),
            'processing' => translate('processing'),
            'in_progress' => translate('in_Progress'),
            'assigned' => translate('assigned'),
            'waiting', 'pending' => translate('Pending'),
            'hold', 'on_hold' => translate('hold'),
            'closed', 'close' => translate('Closed'),
            'resolved' => translate('resolved'),
            'completed', 'complete' => translate('completed'),
            default => Str::of($statusName)
                ->replace('_', ' ')
                ->headline()
                ->value(),
        };
    }

    protected function normalizeCategory(?string $messageType): string
    {
        return match ($messageType) {
            'service' => 'service',
            'career' => 'career',
            'warranty' => 'warranty',
            'contact' => 'partnership',
            default => 'support',
        };
    }

    protected function resolveNextStep(InboxMessage $message, ?SupportTicket $ticket, string $normalizedStatus): ?string
    {
        if ($ticket) {
            if ($normalizedStatus === 'closed') {
                return translate('crm_case_closed_by_support');
            }

            return translate('crm_case_reply_on_ticket_thread');
        }

        if (in_array($normalizedStatus, ['new', 'waiting'], true)) {
            return translate('crm_case_waiting_for_triage');
        }

        if ($normalizedStatus === 'converted') {
            return translate('crm_case_converted_to_ticket');
        }

        return translate('crm_case_under_review');
    }
}
