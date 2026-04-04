<?php

namespace App\Services;

use App\Models\SlaPolicy;
use App\Models\SlaBreach;
use App\Models\InboxActivities;
use App\Models\LeadActivity;
use App\Models\DealActivity;
use App\Models\CareerActivity;
use App\Models\ServiceJobActivity;
use App\Models\SupportTicketActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SlaBreachNotification;
use App\Repositories\AdminNotificationRepository;
use Illuminate\Support\Facades\Log;

class SlaService
{

    public function __construct(
        private readonly AdminNotificationRepository               $notificationRepo,

    ) {}

    public function startSlaTimers($entity)
    {
        $policy = SlaPolicy::where('entity_type', $this->getEntityType($entity))
            ->where('priority', $entity->priority ?? 'medium')
            ->where('is_active', true)
            ->first();
        if (!$policy) return;

        $startTime = now();
        $entity->response_due = $startTime->clone()->addMinutes($policy->response_time_minutes);
        $entity->resolution_due = $startTime->clone()->addMinutes($policy->resolution_time_minutes);
        $entity->escalation_level = 'none';
        $entity->sla_paused_at = null;
        $entity->save();

        $this->logActivity($entity, "SLA timers started: Response due {$entity->response_due}, Resolution due {$entity->resolution_due}");
    }

    public function checkForBreaches($entity)
    {
        if (!$entity->response_due || !$entity->resolution_due) return false;
        $now = Carbon::now();
        if (in_array($entity->status, ['waiting', 'on_hold', 'paused', 'screening']) || $entity->sla_paused_at) {
            return false;
        }

        $breached = false;
        if ($entity->status === 'new' && $now->gt($entity->response_due)) {
            $this->createBreach($entity, 'response');
            $breached = true;
        }

        $ignoredStatuses = [
            'resolved',
            'closed',
            'rejected',
            'hired',
            'converted',
            'ignore',
            'spam',
            'won',
            'lost',
            'new',
            1,
            10,
            19,
            25,
            26,
            30,
            35,
            33,
            34,
            40,
            41,
            42,
            43,
            48,
            49,
            54,
            55,
            56,
            60,
            61,
            62,
            20,
            27,
            36
        ];

        if (!in_array($entity->status, $ignoredStatuses) && $now->gt($entity->resolution_due)) {
            $this->createBreach($entity, 'resolution');
            $breached = true;
        }

        return $breached;
    }


    private function createBreach($entity, $type)
    {
        $currentLevel = $entity->escalation_level ?? 'none';
        $newLevel = ($currentLevel == 'none') ? 'l1' : 'l2';

        $breach = SlaBreach::create([
            'entity_type' => $this->getEntityType($entity),
            'entity_id' => $entity->id,
            'breach_type' => $type,
            'occurred_at' => now(),
            'escalation_level' => $newLevel,
            'notified' => false,
        ]);

        $entity->escalation_level = $newLevel;
        $entity->escalated_at = now();
        $entity->escalated_by = auth('admin')->id() ?? 1;
        $entity->save();
        $this->notifyEscalation($entity, $breach);
        $this->logActivity($entity, "SLA breach: {$type} at level {$newLevel}");
    }

    private function notifyEscalation($entity, $breach)
    {
        $users = [];
        $recipients = [];

        if ($entity->employee_id) {
            $employee = \App\Models\Admin::find($entity->employee_id);
            if ($employee) {
                $users[] = $employee;
                $recipients[] = ['type' => 'employee', 'id' => $employee->id];
            }
        }

        if ($breach->escalation_level == 'l2' && $entity->department_id) {
            $department = \App\Models\Departments::find($entity->department_id);
            if ($department && $department->head_id) {
                $head = \App\Models\Admin::find($department->head_id);
                if ($head && !in_array($head, $users)) {
                    $users[] = $head;
                    $recipients[] = ['type' => 'employee', 'id' => $head->id];
                }
            }
        }

        foreach ($users as $user) {
            try {
                $user->notify(new SlaBreachNotification($breach, $entity));
            } catch (\Exception $e) {
                Log::error("Email SLA Notification Failed: " . $e->getMessage());
            }
        }

        if (!empty($recipients)) {
            $entityType = $this->getEntityType($entity);
            $entityClass = get_class($entity);

            $title = "SLA Breach: " . ucfirst($breach->breach_type);
            $message = "SLA breached for {$entityType} #{$entity->id} at level {$breach->escalation_level}";
            $link = $this->getEntityLink($entity);

            try {
                $this->notificationRepo->notifyRecipients(
                    $entity->id,
                    $entityClass,
                    $title,
                    $message,
                    $link,
                    $recipients
                );
            } catch (\Exception $e) {
                Log::error("DB SLA Notification Failed: " . $e->getMessage());
            }
        }

        // Update breach
        $breach->notified = true;
        $breach->save();
    }

    private function getEntityLink($entity)
    {
        $type = $this->getEntityType($entity);

        return match ($type) {
            'inbox_message' => route('admin.crm.message.show', $entity->id),
            'lead'          => route('admin.crm.lead.show', $entity->id),
            'company_deal' => route('admin.crm.deals.wholesale.view', $entity->id),
            'contact_deal' => route('admin.crm.deals.retail.view', $entity->id),
            'career_ticket' => route('admin.support-ticket.career.single', $entity->id),
            'complaint_ticket' => route('admin.support-ticket.details', $entity->id),
            'support_ticket' => route('admin.support-ticket.details', $entity->id),
            'retail_ticket' => route('admin.support-ticket.details', $entity->id),
            'wholesale_ticket' => route('admin.support-ticket.details', $entity->id),
            'service_ticket' => route('admin.support-ticket.service.singleTicket', $entity->id),
            'warranty_claim' => route('admin.warranty.claim.view', $entity->id),
            default => '#',
        };
    }

    private function logActivity($entity, $message)
    {
        $entityType = $this->getEntityType($entity);

        $activityModel = match ($entityType) {
            'inbox_message' => InboxActivities::class,
            'lead' => LeadActivity::class,
            'contact_deal', 'company_deal' => DealActivity::class,
            'support_ticket', 'complaint_ticket', 'retail_ticket', 'wholesale_ticket' => SupportTicketActivity::class,
            'career_ticket' => CareerActivity::class,
            'service_ticket' => ServiceJobActivity::class,
            default => null,
        };

        if (!$activityModel) {
            Log::warning("No activity model found for entity type: {$entityType}");
            return;
        }

        $data = [
            'activity_type' => 'sla_event',
            'created_by'    => auth('admin')->id() ?? 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        switch ($entityType) {
            case 'inbox_message':
                $data['message_id'] = $entity->id;
                $data['title'] = 'SLA Event';
                $data['subject'] = $message;
                $data['details'] = ['description' => $message];
                break;

            case 'lead':
                $data['lead_id'] = $entity->id;
                $data['title'] = 'SLA Event';
                $data['subject'] = $message;
                $data['details'] = ['description' => $message];
                break;

            case 'contact_deal':
            case 'company_deal':
                $data['deal_id'] = $entity->id;
                $data['title'] = 'SLA Event';
                $data['subject'] = $message;
                $data['details'] = ['description' => $message];
                break;

            case 'support_ticket':
            case 'complaint_ticket':
            case 'retail_ticket':
            case 'wholesale_ticket':
                $data['support_ticket_id'] = $entity->id;
                $data['title'] = 'SLA Breach';
                $data['description'] = $message;
                $data['noted_at'] = now();
                break;

            case 'career_ticket':
                $data['ticket_id'] = $entity->id;
                $data['description'] = $message;
                $data['attachments'] = null;
                break;

            case 'service_ticket':
                $data['job_id'] = $entity->id;
                $data['description'] = $message;
                $data['attachments'] = null;
                break;

            default:
                return;
        }
        try {
            $activityModel::create($data);
        } catch (\Exception $e) {
            Log::error("SLA Activity Log Failed [{$entityType} #{$entity->id}]: " . $e->getMessage());
        }
    }

    private function getEntityType($entity)
    {
        $class = get_class($entity);
        if ($class == \App\Models\SupportTicket::class) {
            return $entity->type . '_ticket';
        } elseif ($class == \App\Models\Deal::class) {
            return $entity->related_party_type . '_deal';
        } elseif ($class == \App\Models\InboxMessage::class) {
            return 'inbox_message';
        } elseif ($class == \App\Models\WarrantyClaim::class) {
            return 'warranty_claim';
        } elseif ($class == \App\Models\Lead::class) {
            return 'lead';
        }
        return 'unknown';
    }
}
