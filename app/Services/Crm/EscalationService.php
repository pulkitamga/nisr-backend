<?php

namespace App\Services\Crm;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Models\Deal;
use App\Models\Escalation;
use App\Models\Lead;
use App\Models\SupportTicket;
use App\Models\SupportTicketStatusMaster;
use Illuminate\Validation\ValidationException;

class EscalationService
{
    private const ALLOWED_ESCALATION_STATUSES = ['open', 'acknowledged', 'in_progress', 'resolved', 'rejected', 'cancelled'];
    private const ACTIVE_ESCALATION_STATUSES = ['open', 'acknowledged', 'in_progress'];
    private const TERMINAL_ESCALATION_STATUSES = ['resolved', 'rejected', 'cancelled'];
    private const TERMINAL_ENTITY_STATUSES = ['closed', 'resolved', 'cancelled', 'canceled', 'won', 'lost', 'disqualified', 'converted'];

    public function __construct(
        private readonly AdminNotificationRepositoryInterface $notificationRepo,
    ) {}

    public function escalateSupportTicket(
        SupportTicket $ticket,
        int $actorId,
        string $reason,
        string $title,
        string $message,
        string $link
    ): Escalation {
        $statusName = $this->resolveSupportTicketStatusName($ticket);
        $this->ensureEscalationAllowed($statusName);
        $this->ensureNoActiveEscalation($ticket->id, SupportTicket::class);

        $escalation = Escalation::create([
            'escalatable_id' => $ticket->id,
            'escalatable_type' => SupportTicket::class,
            'escalated_by' => $actorId,
            'reason' => $reason,
            'status' => 'open',
        ]);

        $ticket->update([
            'escalation_level' => $this->nextEscalationLevel($ticket->escalation_level),
            'escalated_at' => now(),
            'escalated_by' => $actorId,
        ]);

        $this->notifyRecipients(
            notifiableId: $ticket->id,
            notifiableType: SupportTicket::class,
            title: $title,
            message: $message,
            link: $link,
            ownerId: (int)$ticket->owner_id,
            employeeId: (int)$ticket->employee_id,
            departmentId: (int)$ticket->department_id,
        );

        return $escalation;
    }

    public function escalateLead(
        Lead $lead,
        int $actorId,
        string $reason,
        string $title,
        string $message,
        string $link
    ): Escalation {
        $this->ensureEscalationAllowed((string)$lead->status);
        $this->ensureNoActiveEscalation($lead->id, Lead::class);

        $escalation = Escalation::create([
            'escalatable_id' => $lead->id,
            'escalatable_type' => Lead::class,
            'escalated_by' => $actorId,
            'reason' => $reason,
            'status' => 'open',
        ]);

        $lead->update([
            'escalation_level' => $this->nextEscalationLevel($lead->escalation_level),
            'escalated_at' => now(),
            'escalated_by' => $actorId,
        ]);

        $this->notifyRecipients(
            notifiableId: $lead->id,
            notifiableType: Lead::class,
            title: $title,
            message: $message,
            link: $link,
            ownerId: (int)$lead->owner_id,
            employeeId: (int)$lead->employee_id,
            departmentId: (int)$lead->department_id,
        );

        return $escalation;
    }

    public function escalateDeal(
        Deal $deal,
        int $actorId,
        string $reason,
        string $title,
        string $message,
        string $link
    ): Escalation {
        $this->ensureEscalationAllowed((string)$deal->status);
        $this->ensureNoActiveEscalation($deal->id, Deal::class);

        $escalation = Escalation::create([
            'escalatable_id' => $deal->id,
            'escalatable_type' => Deal::class,
            'escalated_by' => $actorId,
            'reason' => $reason,
            'status' => 'open',
        ]);

        $deal->update([
            'escalation_level' => $this->nextEscalationLevel($deal->escalation_level),
            'escalated_at' => now(),
            'escalated_by' => $actorId,
        ]);

        $this->notifyRecipients(
            notifiableId: $deal->id,
            notifiableType: Deal::class,
            title: $title,
            message: $message,
            link: $link,
            ownerId: (int)$deal->owner_id,
            employeeId: (int)$deal->employee_id,
            departmentId: (int)$deal->department_id,
        );

        return $escalation;
    }

    public function transitionEscalationStatus(
        Escalation $escalation,
        int $actorId,
        string $targetStatus
    ): Escalation {
        $currentStatus = strtolower(trim((string)($escalation->status ?? 'open')));
        $targetStatus = strtolower(trim($targetStatus));

        if ($currentStatus === '') {
            $currentStatus = 'open';
        }

        if (!in_array($targetStatus, self::ALLOWED_ESCALATION_STATUSES, true)) {
            throw ValidationException::withMessages([
                'escalation' => translate('invalid_escalation_status'),
            ]);
        }

        if (in_array($currentStatus, self::TERMINAL_ESCALATION_STATUSES, true)) {
            throw ValidationException::withMessages([
                'escalation' => translate('escalation_already_closed'),
            ]);
        }

        if ($currentStatus === $targetStatus) {
            return $escalation;
        }

        $allowedTransitions = match ($currentStatus) {
            'open' => ['acknowledged', 'in_progress', 'resolved', 'rejected', 'cancelled'],
            'acknowledged' => ['in_progress', 'resolved', 'rejected', 'cancelled'],
            'in_progress' => ['resolved', 'rejected', 'cancelled'],
            default => [],
        };

        if (!in_array($targetStatus, $allowedTransitions, true)) {
            throw ValidationException::withMessages([
                'escalation' => translate('escalation_status_transition_not_allowed'),
            ]);
        }

        $escalation->update(['status' => $targetStatus]);

        $escalatable = $escalation->escalatable;
        if ($escalatable) {
            $this->notifyRecipients(
                notifiableId: (int)$escalatable->id,
                notifiableType: get_class($escalatable),
                title: 'Escalation Status Updated',
                message: "Escalation #{$escalation->id} status changed from {$currentStatus} to {$targetStatus}.",
                link: $this->resolveEscalationLink($escalatable),
                ownerId: (int)($escalatable->owner_id ?? 0),
                employeeId: (int)($escalatable->employee_id ?? 0),
                departmentId: (int)($escalatable->department_id ?? 0),
            );

        }

        return $escalation->fresh();
    }

    private function resolveSupportTicketStatusName(SupportTicket $ticket): string
    {
        $statusName = (string)($ticket->status_details?->name ?? '');
        if ($statusName !== '') {
            return $statusName;
        }

        return (string)(SupportTicketStatusMaster::find($ticket->status)?->name ?? '');
    }

    private function ensureEscalationAllowed(string $status): void
    {
        $normalized = strtolower(trim($status));
        if ($normalized !== '' && in_array($normalized, self::TERMINAL_ENTITY_STATUSES, true)) {
            throw ValidationException::withMessages([
                'escalation' => translate('escalation_not_allowed_for_current_status'),
            ]);
        }
    }

    private function ensureNoActiveEscalation(int $entityId, string $entityType): void
    {
        $hasActiveEscalation = Escalation::query()
            ->where('escalatable_id', $entityId)
            ->where('escalatable_type', $entityType)
            ->whereIn('status', self::ACTIVE_ESCALATION_STATUSES)
            ->exists();

        if ($hasActiveEscalation) {
            throw ValidationException::withMessages([
                'escalation' => translate('escalation_already_open'),
            ]);
        }
    }

    private function notifyRecipients(
        int $notifiableId,
        string $notifiableType,
        string $title,
        string $message,
        string $link,
        int $ownerId = 0,
        int $employeeId = 0,
        int $departmentId = 0
    ): void {
        $employeeRecipients = array_values(array_unique(array_filter([$ownerId, $employeeId])));
        $recipients = [];

        foreach ($employeeRecipients as $employeeId) {
            $recipients[] = ['type' => 'employee', 'id' => $employeeId];
        }

        if ($departmentId > 0) {
            $recipients[] = ['type' => 'department', 'id' => $departmentId];
        }

        if (empty($recipients)) {
            return;
        }

        $this->notificationRepo->notifyRecipients(
            $notifiableId,
            $notifiableType,
            $title,
            $message,
            $link,
            $recipients
        );
    }

    private function nextEscalationLevel(?string $currentLevel): string
    {
        return match (strtoupper(trim((string)$currentLevel))) {
            'L1' => 'L2',
            'L2' => 'L3',
            default => 'L1',
        };
    }

    private function resolveEscalationLink(object $escalatable): string
    {
        try {
            if ($escalatable instanceof SupportTicket) {
                return match (strtolower((string)$escalatable->type)) {
                    'service' => route('admin.support-ticket.service.singleTicket', $escalatable->id),
                    'career' => route('admin.support-ticket.career.single', $escalatable->id),
                    'complaint' => route('admin.complaints.singleTicket', $escalatable->id),
                    default => route('admin.support-ticket.details', $escalatable->id),
                };
            }

            if ($escalatable instanceof Lead) {
                return route('admin.crm.lead.show', $escalatable->id);
            }

            if ($escalatable instanceof Deal) {
                return $escalatable->related_party_type === 'contact'
                    ? route('admin.crm.deals.retail.view', $escalatable->id)
                    : route('admin.crm.deals.wholesale.view', $escalatable->id);
            }
        } catch (\Throwable) {
            return '#';
        }

        return '#';
    }
}
