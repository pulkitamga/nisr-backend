<?php

namespace App\Http\Controllers\Admin\HelpAndSupport;

use App\Enums\TicketDispatchTarget;
use App\Enums\SupportTicketStatusGroup;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Enums\ViewPaths\Admin\SupportTicket;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\SupportTicketRequest;
use App\Services\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\SupportTicketStatusMaster;
use App\Models\SupportTicketNotification;
use Carbon\Carbon;
use App\Models\CareerInterview;
use App\Models\CareerOffer;
use App\Models\CareerRejection;
use App\Models\CareerActivity;
use App\Models\Departments;
use App\Models\CareerTalentPool;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CareerTicketExport;
use App\Services\Crm\EscalationService;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface; 
use App\Support\CareerTicketWorkflow;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
class CareerTicketController extends BaseController
{
    public function __construct(
        private readonly SupportTicketRepositoryInterface $supportTicketRepo,
        private readonly SupportTicketConvRepositoryInterface $supportTicketConvRepo,
        private readonly DepartmentRepositoryInterface $departmentRepo,
        private readonly AdminRepositoryInterface $adminRepo,
        private readonly AdminNotificationRepositoryInterface   $notificationRepo, // Add this
        private readonly EscalationService                     $escalationService,

    ) {}
    public function index(?Request $request, string $type = null): View|RedirectResponse|\Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator|null|callable
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        $status = $request->input('status', '27');
        $talentPoolFilter = $request->input('talent_pool', 'all');
        $priority = $request->input('priority', 'all');

        // Start query
        $query = $this->supportTicketRepo->getQuery()
            ->with([
                'department',
                'employee',
                'status_details',
                'customer',
                'conversations',
                'careerInterviews',
                'careerActivities',
                'careerOffers',
                'careerRejections',
                'relatedInboxMessage',
                'careerTalentPool',
            ])
            ->where('type', 'career')
            ->when($request->filled('searchValue'), function ($q) use ($request) {
                $q->where(function ($searchQuery) use ($request) {
                    $searchQuery->where('support_tickets.subject', 'like', '%' . $request->get('searchValue') . '%')
                        ->orWhereHas('status_details', function ($sq) use ($request) {
                            $sq->where('name', 'like', '%' . $request->get('searchValue') . '%');
                        });
                });
            })
            ->when($priority !== 'all', function ($q) use ($priority) {
                $q->where('support_tickets.priority', $priority);
            })
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('support_tickets.status', $status);
            })
            ->leftJoin('career_talent_pool as ctp', 'support_tickets.id', '=', 'ctp.ticket_id')
            ->when($talentPoolFilter === 'yes', function ($q) {
                $q->where('ctp.consent', 1);
            })
            ->when($talentPoolFilter === 'no', function ($q) {
                $q->where('ctp.consent', 0);
            })

            ->select('support_tickets.*')
            ->orderByDesc('support_tickets.id');
        $tickets = $query->paginate(getWebConfig('pagination_limit'));
        $departments = $this->departmentRepo->getListWhere(orderBy: ['id' => 'desc'], dataLimit: 'all');
        $employees = $this->adminRepo->getEmployeeListWhere(orderBy: ['id' => 'desc'], dataLimit: 'all');
        $statuses = SupportTicketStatusMaster::where([
            'master_id' => SupportTicketStatusGroup::Career->value,
            'status' => 'active',
        ])
            ->orderBy('position', 'asc')
            ->get();

        return view(SupportTicket::CAREER[VIEW], compact('tickets', 'statuses', 'departments', 'employees'));
    }



    public function getDetails($id, Request $request): View
    {
        $supportTicket = $this->supportTicketRepo->getListWhere(
            filters: ['id' => $id, 'type' => 'career'],
            relations: ['customer', 'careerInterviews', 'careerActivities', 'careerActivities.createdBy', 'careerOffers', 'careerRejections', 'conversations', 'escalations.escalatedBy'],
            dataLimit: 'all'
        )->first();

        if (!$supportTicket) {
            abort(404, translate('ticket_not_found'));
        }

        return view('admin-views.crm.tickets.partials.career-ticket-detail', compact('supportTicket'));
    }

   public function updateStatus(Request $request): JsonResponse
{
    $request->validate([
        'id' => ['required', Rule::exists('support_tickets', 'id')->where('type', 'career')]
    ]);

    $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->id]);
    if (!$ticket) {
        return response()->json(['message' => translate('ticket_not_found')], 404);
    }

    $currentStatusId = $ticket->status;

    /**
     * 🔄 Status flow
     */
    $statusFlow = CareerTicketWorkflow::nextStatusMap();

    $nextStatusId = $statusFlow[$currentStatusId] ?? null;

    if (!$nextStatusId) {
        return response()->json(['message' => translate('no_next_status')], 400);
    }

    $nextStatus = SupportTicketStatusMaster::find($nextStatusId);
    if (!$nextStatus) {
        return response()->json(['message' => translate('invalid_status')], 422);
    }

    if (strcasecmp((string)$nextStatus->name, 'assigned') === 0 && (int)($ticket->employee_id ?? 0) <= 0) {
        return response()->json(['message' => translate('assign_employee_before_setting_assigned_status')], 422);
    }

    $updateData = ['status' => $nextStatusId];

    /**
     * 🕒 SLA Pause / Resume
     */
    if ($nextStatusId === CareerTicketWorkflow::STATUS_SCREENING) {
        $updateData['sla_paused_at'] = now();
        $this->logCareerActivity(
            $ticket->id,
            'status_change',
            'Moved to waiting: ' . ($request->waiting_reason ?? 'No reason')
        );
    } elseif ($ticket->sla_paused_at && $nextStatusId !== CareerTicketWorkflow::STATUS_SCREENING) {
        $updateData['sla_paused_at'] = null;
    }

    // Update the ticket
    $this->supportTicketRepo->update($ticket->id, $updateData);

    $this->autoManageTasks($ticket->id, $nextStatusId);
    $this->logCareerActivity($ticket->id, 'status_update', "Status changed to {$nextStatus->name}");

    /**
     * 🔔 Prepare Notification Recipients (Employee + Department + Customer)
     */
    $recipients = [];

    // EMPLOYEE
    if ($ticket->employee_id) {
        $recipients[] = [
            'type'    => 'employee',
            'id'      => $ticket->employee_id,
            'title'   => 'Status Updated',
            'message' => "Ticket #{$ticket->id} moved to {$nextStatus->name}.",
        ];
    }

    // DEPARTMENT
    if ($ticket->department_id) {
        $department = Departments::find($ticket->department_id);
        $deptName = $department?->name ?? '';

        $recipients[] = [
            'type'    => 'department',
            'id'      => $ticket->department_id,
            'title'   => 'Department Ticket Update',
            'message' => "Ticket #{$ticket->id} status changed to {$nextStatus->name} in department: {$deptName}.",
        ];
    }
    if ($ticket->customer_id) {
        $recipients[] = [
            'type'    => 'customer',
            'id'      => $ticket->customer_id,
            'title'   => 'Your Ticket Status Updated',
            'message' => "Your ticket #{$ticket->id} is now in status: {$nextStatus->name}.",
        ];
    }
    $link = route('admin.support-ticket.career.single', $ticket->id);

    foreach ($recipients as $rec) {
        $this->notificationRepo->notifyRecipients(
            notifiableId: $ticket->id,
            notifiableType: \App\Models\SupportTicket::class,
            title: $rec['title'],
            message: $rec['message'],
            link: $link,
            recipients: [
                ['type' => $rec['type'], 'id' => $rec['id']]
            ]
        );
    }

    return response()->json([
        'message' => translate('status_updated_successfully'),
        'new_status_name' => $nextStatus->name
    ], 200);
}


    public function export(Request $request)
    {
        $fileName =  'Career_Tickets_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
        return Excel::download(
            new CareerTicketExport($request),
            $fileName
        );
    }

    private function autoManageTasks(int $ticketId, int $statusId): void
    {
        if ($statusId === 30) {
            SupportTicketNotification::create([
                'ticket_id' => $ticketId,
                'notification_for' => TicketDispatchTarget::Employee->value,
                'user_id' => auth('admin')->user()->employee_id ?? 0,
                'title' => 'Review Candidate',
                'message' => 'Review due within 1 business day.',
                'due_date' => Carbon::now()->addBusinessDays(1),
                'status' => 0,
                'is_active' => 1,
            ]);
        }
    }

  public function assignRecruiter(Request $request): RedirectResponse
{
    $request->validate([
        'ticket_id' => ['required', Rule::exists('support_tickets', 'id')->where('type', 'career')],
        'recruiter_id' => 'required|exists:admins,id',
        'priority' => 'required|in:low,medium,high,urgent',
    ]);

    $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->ticket_id]);
    if (!in_array((int) $ticket->status, [CareerTicketWorkflow::STATUS_NEW, CareerTicketWorkflow::STATUS_OPEN], true)) {
        Toastr::error(translate('invalid_status'));
        return back();
    }

    // Update ticket
    $this->supportTicketRepo->update($request->ticket_id, [
        'employee_id' => $request->recruiter_id,
        'priority' => $request->priority,
        'status' => CareerTicketWorkflow::STATUS_ASSIGNED,
    ]);

    $recruiter = $this->adminRepo->getById($request->recruiter_id);
    $recruiterName = $recruiter ? $recruiter->name : 'Unknown Recruiter';

    // Log activity
    $this->logCareerActivity(
        $request->ticket_id,
        'assign_recruiter',
        "Assigned to recruiter {$recruiterName}"
    );

    // Add conversation
    $this->supportTicketConvRepo->add([
        'support_ticket_id' => $request->ticket_id,
        'admin_message' => "Ticket assigned to recruiter {$recruiterName}.",
        'admin_id' => auth('admin')->id(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 🔔 Notifications for Employee and Customer
    $link = route('admin.support-ticket.career.single', $ticket->id);

    $recipients = [];

    // Employee notification
    if ($request->recruiter_id) {
        $recipients[] = [
            'type' => 'employee',
            'id' => $request->recruiter_id,
            'title' => 'New Candidate Assigned',
            'message' => "New candidate assigned to you: Ticket #{$ticket->id}",
        ];
    }

    // Customer notification
    if ($ticket->customer_id) {
        $recipients[] = [
            'type' => 'customer',
            'id' => $ticket->customer_id,
            'title' => 'Your Ticket is Assigned',
            'message' => "Your ticket #{$ticket->id} has been assigned to a recruiter {$recruiterName}.",
        ];
    }

    // Send notifications via NotificationRepo
    foreach ($recipients as $rec) {
        $this->notificationRepo->notifyRecipients(
            notifiableId: $ticket->id,
            notifiableType: \App\Models\SupportTicket::class,
            title: $rec['title'],
            message: $rec['message'],
            link: $link,
            recipients: [
                ['type' => $rec['type'], 'id' => $rec['id']]
            ]
        );
    }

    Toastr::success(translate('recruiter_assigned_successfully'));
    return redirect()->back();
}

   public function logScreening(Request $request): RedirectResponse
{
    $request->validate([
        'ticket_id' => ['required', Rule::exists('support_tickets', 'id')->where('type', 'career')],
        'notes' => 'required|string',
        'qualified' => 'required|boolean',
        'reason_code' => 'nullable|string',
    ]);

    $ticketId = $request->ticket_id;
    $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $ticketId]);
    if (!in_array((int) $ticket->status, [CareerTicketWorkflow::STATUS_ASSIGNED, CareerTicketWorkflow::STATUS_SCREENING], true)) {
        Toastr::error(translate('invalid_status'));
        return back();
    }

    // Append screening notes to ticket description
    $ticket->update([
        'description' => $ticket->description . "\nScreening Notes: " . $request->notes
    ]);

    $link = route('admin.support-ticket.career.single', $ticketId);
    $recipients = [];

    if (!$request->qualified) {
        // Not qualified: Close ticket
        CareerRejection::create([
            'ticket_id' => $ticketId,
            'reason_code' => $request->reason_code ?? 'Not Qualified',
            'closure_message' => "Screened and not selected. Notes: {$request->notes}",
            'created_at' => now(),
        ]);

        $this->supportTicketRepo->update($ticketId, ['status' => CareerTicketWorkflow::STATUS_CLOSED]);
        $this->logCareerActivity($ticketId, 'screening_rejected', $request->notes);

        // Customer notification
        if ($ticket->customer_id) {
            $recipients[] = [
                'type' => 'customer',
                'id' => $ticket->customer_id,
                'title' => 'Screening Result',
                'message' => 'Unfortunately, you were not selected for this position.',
            ];
        }

        // Employee notification (optional)
        if ($ticket->employee_id) {
            $recipients[] = [
                'type' => 'employee',
                'id' => $ticket->employee_id,
                'title' => 'Candidate Screening Result',
                'message' => "Candidate for Ticket #{$ticket->id} was not qualified.",
            ];
        }

    } else {
        // Qualified: Move to Interview
        $this->supportTicketRepo->update($ticketId, ['status' => CareerTicketWorkflow::STATUS_INTERVIEW]);
        $this->logCareerActivity($ticketId, 'screening_qualified', $request->notes);

        // Customer notification
        if ($ticket->customer_id) {
            $recipients[] = [
                'type' => 'customer',
                'id' => $ticket->customer_id,
                'title' => 'Screening Passed',
                'message' => 'Congratulations! You have passed the screening stage.',
            ];
        }

        // Employee notification
        if ($ticket->employee_id) {
            $recipients[] = [
                'type' => 'employee',
                'id' => $ticket->employee_id,
                'title' => 'Candidate Passed Screening',
                'message' => "Candidate for Ticket #{$ticket->id} passed the screening stage.",
            ];
        }
    }

    // Send all notifications via NotificationRepo
    foreach ($recipients as $rec) {
        $this->notificationRepo->notifyRecipients(
            notifiableId: $ticketId,
            notifiableType: \App\Models\SupportTicket::class,
            title: $rec['title'],
            message: $rec['message'],
            link: $link,
            recipients: [
                ['type' => $rec['type'], 'id' => $rec['id']]
            ]
        );
    }

    // Add admin conversation log
    $this->supportTicketConvRepo->add([
        'support_ticket_id' => $ticketId,
        'admin_message' => "Screening logged: Qualified - " . ($request->qualified ? 'Yes' : 'No'),
        'admin_id' => auth('admin')->id(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Toastr::success(translate('screening_logged_successfully'));
    return redirect()->back();
}


 public function scheduleInterview(Request $request): RedirectResponse
{
    $request->validate([
        'ticket_id' => ['required', Rule::exists('support_tickets', 'id')->where('type', 'career')],
        'scheduled_at' => 'required|date',
        'panel' => 'required|array',
        'panel.*' => 'exists:admins,id',
    ]);

    $ticketId = $request->ticket_id;
    $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $ticketId]);
    if ((int) $ticket->status !== CareerTicketWorkflow::STATUS_INTERVIEW) {
        Toastr::error(translate('invalid_status'));
        return back();
    }

    $panelAdmins = collect($request->panel)
        ->map(fn($adminId) => $this->adminRepo->getById($adminId)?->name ?? 'Unknown')
        ->toArray();

    CareerInterview::create([
        'ticket_id' => $ticketId,
        'scheduled_at' => Carbon::parse($request->scheduled_at),
        'panel' => json_encode($request->panel),
        'created_at' => now(),
    ]);

    $this->supportTicketRepo->update($ticketId, ['status' => CareerTicketWorkflow::STATUS_INTERVIEW]);
    $scheduledAtFormatted = Carbon::parse($request->scheduled_at)->format('d M Y, g:i A');

    $this->logCareerActivity(
        $ticketId,
        'interview_scheduled',
        "Scheduled for {$scheduledAtFormatted} with panel: " . implode(', ', $panelAdmins)
    );

    $this->supportTicketConvRepo->add([
        'support_ticket_id' => $ticketId,
        'admin_message' => "Interview scheduled with panel: " . implode(', ', $panelAdmins),
        'admin_id' => auth('admin')->id(),
    ]);

    $link = route('admin.support-ticket.career.single', $ticketId);
    $recipients = [];

    // Panel members notifications
    foreach ($request->panel as $empId) {
        $recipients[] = [
            'type' => 'employee',
            'id' => $empId,
            'title' => 'Interview Scheduled',
            'message' => "You are on the interview panel for Ticket #{$ticketId} scheduled at {$scheduledAtFormatted}.",
        ];
    }

    // Customer notification
    if ($ticket?->customer_id) {
        $recipients[] = [
            'type' => 'customer',
            'id' => $ticket->customer_id,
            'title' => 'Interview Scheduled',
            'message' => "Your interview for Ticket #{$ticketId} is scheduled at {$scheduledAtFormatted}.",
        ];
    }

    // Send notifications
    foreach ($recipients as $rec) {
        $this->notificationRepo->notifyRecipients(
            notifiableId: $ticketId,
            notifiableType: \App\Models\SupportTicket::class,
            title: $rec['title'],
            message: $rec['message'],
            link: $link,
            recipients: [
                ['type' => $rec['type'], 'id' => $rec['id']]
            ]
        );
    }

    Toastr::success(translate('interview_scheduled_successfully'));
    return redirect()->back();
}


public function conductInterview(Request $request): RedirectResponse
{
    $request->validate([
        'ticket_id' => ['required', Rule::exists('support_tickets', 'id')->where('type', 'career')],
        'interview_id' => 'required|exists:career_interviews,id',
        'outcome' => 'required|in:pass,fail,no_show',
        'notes' => 'required|string',
    ]);

    $interview = CareerInterview::findOrFail($request->interview_id);
    if ((int) $request->ticket_id !== (int) $interview->ticket_id) {
        Toastr::error(translate('invalid_request'));
        return back();
    }

    $ticketId = $interview->ticket_id;
    $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $ticketId]);
    if ((int) $ticket->status !== CareerTicketWorkflow::STATUS_INTERVIEW) {
        Toastr::error(translate('invalid_status'));
        return back();
    }
    if ($interview->conducted_at !== null) {
        Toastr::error(translate('invalid_request'));
        return back();
    }

    $interview->update([
        'outcome' => $request->outcome,
        'notes' => $request->notes,
        'conducted_at' => now(),
    ]);

    $link = route('admin.support-ticket.career.single', $ticketId);
    $recipients = [];

    if ($request->outcome === 'pass') {
        CareerOffer::create([
            'ticket_id' => $ticketId,
            'status' => 'sent',
            'created_at' => now(),
        ]);
        $this->supportTicketRepo->update($ticketId, ['status' => CareerTicketWorkflow::STATUS_OFFER]);
        $this->logCareerActivity($ticketId, 'interview_pass', $request->notes);

        // Customer notification
        if ($ticket?->customer_id) {
            $recipients[] = [
                'type' => 'customer',
                'id' => $ticket->customer_id,
                'title' => 'Interview Passed',
                'message' => "Congratulations! You passed the interview for Ticket #{$ticketId}.",
            ];
        }

        // Panel notification
        foreach (json_decode($interview->panel, true) as $empId) {
            $recipients[] = [
                'type' => 'employee',
                'id' => $empId,
                'title' => 'Interview Outcome',
                'message' => "Candidate for Ticket #{$ticketId} passed the interview.",
            ];
        }

    } else {
        CareerRejection::create([
            'ticket_id' => $ticketId,
            'reason_code' => $request->outcome,
            'closure_message' => $request->notes,
        ]);
        $this->supportTicketRepo->update($ticketId, ['status' => CareerTicketWorkflow::STATUS_CLOSED]);
        $this->logCareerActivity($ticketId, 'interview_fail', $request->notes);

        // Customer notification
        if ($ticket?->customer_id) {
            $recipients[] = [
                'type' => 'customer',
                'id' => $ticket->customer_id,
                'title' => 'Interview Outcome',
                'message' => "Unfortunately, you did not pass the interview for Ticket #{$ticketId}.",
            ];
        }

        // Panel notification
        foreach (json_decode($interview->panel, true) as $empId) {
            $recipients[] = [
                'type' => 'employee',
                'id' => $empId,
                'title' => 'Interview Outcome',
                'message' => "Candidate for Ticket #{$ticketId} did not pass the interview.",
            ];
        }
    }

    // Send notifications via NotificationRepo
    foreach ($recipients as $rec) {
        $this->notificationRepo->notifyRecipients(
            notifiableId: $ticketId,
            notifiableType: \App\Models\SupportTicket::class,
            title: $rec['title'],
            message: $rec['message'],
            link: $link,
            recipients: [
                ['type' => $rec['type'], 'id' => $rec['id']]
            ]
        );
    }

    $this->supportTicketConvRepo->add([
        'support_ticket_id' => $ticketId,
        'admin_message' => "Interview conducted: Outcome - {$request->outcome}, Notes: {$request->notes}",
        'admin_id' => auth('admin')->id(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Toastr::success(translate('interview_conducted_successfully'));
    return redirect()->back();
}

   public function attachSignedOffer(Request $request): RedirectResponse
{
    $request->validate([
        'ticket_id' => ['required', Rule::exists('support_tickets', 'id')->where('type', 'career')],
        'offer_file' => 'required|file|mimes:pdf|max:5120',
        'start_date' => 'required|date',
    ]);

    $ticketId = $request->ticket_id;
    $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $ticketId]);
    if ((int) $ticket->status !== CareerTicketWorkflow::STATUS_OFFER) {
        Toastr::error(translate('invalid_status'));
        return back();
    }

    $file = $request->file('offer_file');
    $attachment = $file->store('career-offers', 'local');

    $offer = CareerOffer::where('ticket_id', $ticketId)->latest()->first();
    if ($offer) {
        $offer->update([
            'attachment' => $attachment,
            'start_date' => $request->start_date,
            'signed_at' => now(),
            'status' => 'signed',
        ]);
    } else {
        CareerOffer::create([
            'ticket_id' => $ticketId,
            'attachment' => $attachment,
            'start_date' => $request->start_date,
            'signed_at' => now(),
            'status' => 'signed',
        ]);
    }

    $this->supportTicketRepo->update($ticketId, ['status' => CareerTicketWorkflow::STATUS_HIRED]);
    $this->logCareerActivity($ticketId, 'offer_signed', "Signed offer attached, start date: {$request->start_date}");

    $link = route('admin.support-ticket.career.single', $ticketId);
    $recipients = [];

    // Customer notification
    if ($ticket?->customer_id) {
        $recipients[] = [
            'type' => 'customer',
            'id' => $ticket->customer_id,
            'title' => 'Offer Signed',
            'message' => "Congratulations! Your offer for Ticket #{$ticketId} has been signed. Start date: {$request->start_date}.",
        ];
    }

    // Employee notification
    if ($ticket?->employee_id) {
        $recipients[] = [
            'type' => 'employee',
            'id' => $ticket->employee_id,
            'title' => 'Candidate Hired',
            'message' => "Ticket #{$ticketId} has a signed offer. Candidate starts on {$request->start_date}.",
        ];
    }

    // Send notifications via NotificationRepo
    foreach ($recipients as $rec) {
        $this->notificationRepo->notifyRecipients(
            notifiableId: $ticketId,
            notifiableType: \App\Models\SupportTicket::class,
            title: $rec['title'],
            message: $rec['message'],
            link: $link,
            recipients: [['type' => $rec['type'], 'id' => $rec['id']]]
        );
    }

    Toastr::success(translate('offer_attached_successfully'));
    return redirect()->back();
}


public function recordDeclinedOffer(Request $request): RedirectResponse
{
    $request->validate([
        'ticket_id' => ['required', Rule::exists('support_tickets', 'id')->where('type', 'career')],
        'reason' => 'required|string',
    ]);

    $ticketId = $request->ticket_id;
    $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $ticketId]);
    if ((int) $ticket->status !== CareerTicketWorkflow::STATUS_OFFER) {
        Toastr::error(translate('invalid_status'));
        return back();
    }

    $offer = CareerOffer::where('ticket_id', $ticketId)->latest()->first();
    if ($offer) {
        $offer->update(['status' => 'declined']);
    }

    CareerRejection::create([
        'ticket_id' => $ticketId,
        'reason_code' => 'declined_offer',
        'closure_message' => $request->reason,
    ]);

    $this->supportTicketRepo->update($ticketId, ['status' => CareerTicketWorkflow::STATUS_CLOSED]);
    $this->logCareerActivity($ticketId, 'offer_declined', $request->reason);

    $link = route('admin.support-ticket.career.single', $ticketId);
    $recipients = [];

    // Customer notification
    if ($ticket?->customer_id) {
        $recipients[] = [
            'type' => 'customer',
            'id' => $ticket->customer_id,
            'title' => 'Offer Declined',
            'message' => "Your offer for Ticket #{$ticketId} has been declined. Reason: {$request->reason}",
        ];
    }

    // Employee notification
    if ($ticket?->employee_id) {
        $recipients[] = [
            'type' => 'employee',
            'id' => $ticket->employee_id,
            'title' => 'Candidate Declined Offer',
            'message' => "Ticket #{$ticketId} candidate declined the offer. Reason: {$request->reason}",
        ];
    }

    // Send notifications via NotificationRepo
    foreach ($recipients as $rec) {
        $this->notificationRepo->notifyRecipients(
            notifiableId: $ticketId,
            notifiableType: \App\Models\SupportTicket::class,
            title: $rec['title'],
            message: $rec['message'],
            link: $link,
            recipients: [['type' => $rec['type'], 'id' => $rec['id']]]
        );
    }

    Toastr::success(translate('decline_recorded_successfully'));
    return redirect()->back();
}

   public function rejectCandidate(Request $request): RedirectResponse
{
    $request->validate([
        'ticket_id' => ['required', Rule::exists('support_tickets', 'id')->where('type', 'career')],
        'reason_code' => 'required|string',
        'closure_message' => 'required|string',
    ]);

    $ticketId = $request->ticket_id;
    $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $ticketId]);
    if (!in_array((int) $ticket->status, [CareerTicketWorkflow::STATUS_ASSIGNED, CareerTicketWorkflow::STATUS_SCREENING, CareerTicketWorkflow::STATUS_INTERVIEW, CareerTicketWorkflow::STATUS_OFFER], true)) {
        Toastr::error(translate('invalid_status'));
        return back();
    }

    CareerRejection::create([
        'ticket_id' => $ticketId,
        'reason_code' => $request->reason_code,
        'closure_message' => $request->closure_message,
    ]);

    $this->supportTicketRepo->update($ticketId, ['status' => CareerTicketWorkflow::STATUS_REJECTED]);
    $this->logCareerActivity($ticketId, 'rejected', $request->closure_message);

    $link = route('admin.support-ticket.career.single', $ticketId);

    $recipients = [];

    // Customer notification
    if ($ticket?->customer_id) {
        $recipients[] = [
            'type' => 'customer',
            'id' => $ticket->customer_id,
            'title' => 'Candidate Rejected',
            'message' => "Ticket #{$ticketId} has been rejected. Reason: {$request->closure_message}",
        ];
    }

    // Employee notification
    if ($ticket?->employee_id) {
        $recipients[] = [
            'type' => 'employee',
            'id' => $ticket->employee_id,
            'title' => 'Candidate Rejected',
            'message' => "Ticket #{$ticketId} assigned candidate has been rejected. Reason: {$request->closure_message}",
        ];
    }

    // Send notifications via NotificationRepo
    foreach ($recipients as $rec) {
        $this->notificationRepo->notifyRecipients(
            notifiableId: $ticketId,
            notifiableType: \App\Models\SupportTicket::class,
            title: $rec['title'],
            message: $rec['message'],
            link: $link,
            recipients: [['type' => $rec['type'], 'id' => $rec['id']]]
        );
    }

    Toastr::success(translate('candidate_rejected_successfully'));
    return redirect()->back();
}


    public function addToTalentPool(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => ['required', Rule::exists('support_tickets', 'id')->where('type', 'career')],
            'consent' => 'required|boolean',
            'recontact_date' => 'nullable|date',
        ]);

        $ticketId = $request->ticket_id;
        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $ticketId]);
        if (!in_array((int) $ticket->status, [CareerTicketWorkflow::STATUS_REJECTED, CareerTicketWorkflow::STATUS_CLOSED], true)) {
            Toastr::error(translate('invalid_status'));
            return back();
        }

        CareerTalentPool::updateOrCreate(['ticket_id' => $ticketId], [
            'ticket_id' => $ticketId,
            'consent' => $request->consent,
            'recontact_date' => $request->recontact_date,
        ]);

        $this->supportTicketRepo->update($ticketId, ['status' => CareerTicketWorkflow::STATUS_CLOSED]);
        $this->logCareerActivity($ticketId, 'talent_pool', "Added to talent pool with consent: " . ($request->consent ? 'Yes' : 'No'));

        Toastr::success(translate('added_to_talent_pool_successfully'));
        return redirect()->back();
    }

    public function reply(SupportTicketRequest $request, SupportTicketService $supportTicketService): RedirectResponse
    {
        $request->validate([
            'id' => ['required', Rule::exists('support_tickets', 'id')->where('type', 'career')]
        ]);

        if ($request['image'] == null && $request['replay'] == null) {
            Toastr::warning(translate('type_something'));
            return back();
        }
        $dataArray = $supportTicketService->getAddData($request);
        $this->supportTicketConvRepo->add($dataArray);
        $this->logCareerActivity($request->support_ticket_id, 'communication', "Replied to candidate: {$dataArray['admin_message']}");
        Toastr::success(translate('reply_added_successfully'));
        return back();
    }

    private function logCareerActivity(int $ticketId, string $type, string $description, array $attachments = []): void
    {
        CareerActivity::create([
            'ticket_id' => $ticketId,
            'activity_type' => $type,
            'description' => $description,
            'attachments' => !empty($attachments) ? json_encode($attachments) : null,
            'created_by' => auth('admin')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function escalate(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => ['required', Rule::exists('support_tickets', 'id')->where('type', 'career')],
            'reason' => 'required|string|max:1000',
        ]);

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->ticket_id]);
        if (in_array((int) $ticket->status, [CareerTicketWorkflow::STATUS_HIRED, CareerTicketWorkflow::STATUS_CLOSED], true)) {
            Toastr::error(translate('invalid_status'));
            return back();
        }

        // Send notifications
        $title   = 'Ticket Escalated';
        $message = "Career Ticket #{$ticket->id} escalated. Reason: {$request->reason}";
        $link    = route('admin.support-ticket.career.single', $ticket->id);

        try {
            $this->escalationService->escalateSupportTicket(
                ticket: $ticket,
                actorId: (int)auth('admin')->id(),
                reason: (string)$request->reason,
                title: $title,
                message: $message,
                link: $link
            );
        } catch (ValidationException $exception) {
            Toastr::error($exception->errors()['escalation'][0] ?? translate('Request failed.'));
            return back();
        }

        $this->logCareerActivity($ticket->id, 'escalated', $message);


        Toastr::success(translate('Ticket escalated successfully'));
        return back();
    }

    public function downloadOffer(CareerOffer $offer): StreamedResponse
    {
        if ((string) optional($offer->ticket)->type !== 'career') {
            abort(404, translate('ticket_not_found'));
        }
        if (!Storage::disk('local')->exists($offer->attachment)) {
            abort(404, translate('file_not_found'));
        }

        return Storage::disk('local')->download($offer->attachment, basename($offer->attachment));
    }
}
