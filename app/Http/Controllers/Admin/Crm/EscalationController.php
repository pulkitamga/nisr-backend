<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Models\Escalation;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\SupportTicket;
use App\Models\SupportTicketActivity;
use App\Services\Crm\EscalationService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EscalationController extends Controller
{
    public function __construct(
        private readonly EscalationService $escalationService,
    ) {}

    public function updateStatus(Request $request, Escalation $escalation): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string|in:acknowledged,in_progress,resolved,rejected,cancelled',
        ]);

        $previousStatus = strtolower(trim((string)($escalation->status ?? 'open')));
        if ($previousStatus === '') {
            $previousStatus = 'open';
        }

        try {
            $escalation = $this->escalationService->transitionEscalationStatus(
                escalation: $escalation,
                actorId: (int)auth('admin')->id(),
                targetStatus: (string)$request->status
            );
        } catch (ValidationException $exception) {
            Toastr::error($exception->errors()['escalation'][0] ?? translate('Request failed.'));
            return back();
        }

        $this->logEscalationTransition(
            escalation: $escalation,
            previousStatus: $previousStatus,
            nextStatus: (string)$escalation->status
        );

        Toastr::success(translate('escalation_status_updated_successfully'));
        return back();
    }

    private function logEscalationTransition(Escalation $escalation, string $previousStatus, string $nextStatus): void
    {
        $entity = $escalation->escalatable;
        $message = "Escalation #{$escalation->id} status changed from {$previousStatus} to {$nextStatus}.";
        $actorId = auth('admin')->id();

        if ($entity instanceof SupportTicket) {
            SupportTicketActivity::create([
                'support_ticket_id' => $entity->id,
                'employee_id' => $actorId,
                'title' => 'Escalation Updated',
                'description' => $message,
                'noted_at' => now(),
            ]);

            return;
        }

        if ($entity instanceof Lead) {
            LeadActivity::create([
                'lead_id' => $entity->id,
                'employee_id' => $actorId,
                'activity_type' => 'escalation_updated',
                'title' => 'Escalation updated.',
                'subject' => $message,
                'details' => $message,
                'note_date' => now(),
            ]);

            return;
        }

        if ($entity instanceof Deal) {
            DealActivity::create([
                'deal_id' => $entity->id,
                'employee_id' => $actorId,
                'activity_type' => 'escalation_updated',
                'title' => 'Escalation updated.',
                'subject' => $message,
                'details' => $message,
                'note_date' => now(),
            ]);
        }
    }
}
