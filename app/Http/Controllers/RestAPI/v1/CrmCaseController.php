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
use Illuminate\Support\Facades\Validator;

class CrmCaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cases = $this->customerCaseQuery($request->user())
            ->whereNotIn('status', ['spam', 'ignored'])
            ->with(['employee', 'owner', 'ticket'])
            ->latest()
            ->get()
            ->map(fn (InboxMessage $message): array => $this->transformCase($message))
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
                'message' => 'This case cannot accept replies until it is converted to a ticket.',
            ], 422);
        }

        if (in_array($ticket->status, ['close', 'closed'], true)) {
            return response()->json([
                'message' => 'This ticket is already closed.',
            ], 422);
        }

        $reply = new SupportTicketConv();
        $reply->support_ticket_id = $ticket->id;
        $reply->admin_id = 0;
        $reply->customer_message = trim((string)$request->input('message'));
        $reply->save();

        return response()->json([
            'message' => 'Reply sent successfully.',
        ], 200);
    }

    protected function findCustomerCaseOrFail(Request $request, int $id): InboxMessage
    {
        $case = $this->customerCaseQuery($request->user())
            ->with(['employee', 'owner', 'ticket'])
            ->find($id);

        abort_if(!$case, 404, 'Case not found.');

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
        $status = $this->normalizeStatus($message, $ticket);
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
            'status' => $status,
            'priority' => (string)($ticket?->priority ?: $message->priority ?: 'medium'),
            'created_at' => optional($message->created_at)?->toIso8601String(),
            'updated_at' => optional($lastUpdateAt)?->toIso8601String(),
            'is_converted' => (bool)$message->related_ticket_id,
            'ticket_id' => $message->related_ticket_id ? (string)$message->related_ticket_id : null,
            'last_update' => $lastUpdateAt ? Carbon::parse($lastUpdateAt)->toDateTimeString() : null,
            'next_step' => $this->resolveNextStep($message, $ticket),
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
            'title' => 'Inquiry submitted',
            'description' => (string)(data_get($message->details, 'message') ?: $message->message ?: $message->subject),
            'actor_type' => 'customer',
            'actor_id' => (string)($message->contact_id ?? ''),
            'actor_name' => $message->sender_name ?: 'Customer',
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
            'description' => (string)($activity->subject ?: data_get($activity->details, 'description', '')),
            'actor_type' => 'admin',
            'actor_id' => (string)($activity->employee_id ?? ''),
            'actor_name' => $activity->employee?->name ?: 'CRM',
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
            'description' => $description,
            'actor_type' => 'admin',
            'actor_id' => (string)($activity->employee_id ?? ''),
            'actor_name' => $activity->employee?->name ?: 'Support',
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
            'title' => $isCustomerReply ? 'Customer replied' : 'Agent replied',
            'description' => (string)($conversation->customer_message ?: $conversation->admin_message ?: ''),
            'actor_type' => $isCustomerReply ? 'customer' : 'admin',
            'actor_id' => (string)($isCustomerReply ? ($message->contact_id ?? '') : ($conversation->admin_id ?? '')),
            'actor_name' => $isCustomerReply
                ? ($message->sender_name ?: 'Customer')
                : ($conversation->adminInfo?->name ?: 'Support'),
            'case_id' => (string)$message->id,
            'ticket_id' => (string)$conversation->support_ticket_id,
            'created_at' => optional($conversation->created_at)?->toIso8601String(),
        ];
    }

    protected function normalizeStatus(InboxMessage $message, ?SupportTicket $ticket): string
    {
        if ($ticket) {
            return in_array($ticket->status, ['close', 'closed'], true) ? 'closed' : 'converted';
        }

        return match ($message->status) {
            'new' => 'new',
            'pending', 'open', 'processing', 'in_progress' => 'processing',
            'waiting' => 'waiting',
            'closed', 'close' => 'closed',
            default => 'processing',
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

    protected function resolveNextStep(InboxMessage $message, ?SupportTicket $ticket): ?string
    {
        if ($ticket) {
            if (in_array($ticket->status, ['close', 'closed'], true)) {
                return 'This case has been closed by support.';
            }

            return 'Reply on the ticket thread for updates from support.';
        }

        if ($message->status === 'new') {
            return 'Your case is waiting for CRM triage.';
        }

        if ($message->status === 'converted') {
            return 'Your case has been converted to a support ticket.';
        }

        return 'Your case is currently under review.';
    }
}
