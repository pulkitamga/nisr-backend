<?php

namespace App\Http\Controllers\Admin\HelpAndSupport;

use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
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
use App\Models\ServiceJob;
use App\Models\ServiceJobActivity;
use App\Models\ServiceInvoice;
use App\Models\ServiceEstimate;
use App\Models\ServiceChangeOrder;
use App\Models\ServiceCancellation;
use App\Models\Service;
use App\Models\ServiceJobItem;
use App\Models\SupportTicket as SupportTicketModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SupportTicketNotification;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Services\Crm\EscalationService;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface; // New
use App\Services\ServiceWorkflowNotificationService;
use App\Support\ServiceTicketWorkflow;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
class ServiceTicketController extends BaseController
{
    private const SERVICE_INVOICE_LINK_EXPIRE_HOURS = 24;

    public function __construct(
        private readonly SupportTicketRepositoryInterface $supportTicketRepo,
        private readonly SupportTicketConvRepositoryInterface $supportTicketConvRepo,
        private readonly DepartmentRepositoryInterface $departmentRepo,
        private readonly AdminRepositoryInterface $adminRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,
        private readonly AdminNotificationRepositoryInterface $notificationRepo, // New
        private readonly ServiceWorkflowNotificationService $workflowNotifier,
        private readonly EscalationService                 $escalationService,

    ) {}

    public function index(Request|null $request, $type = 'all'): View
    {
        return $this->getListView(request: $request, status: $type);
    }

    public function getListView(Request $request, string $status): View
    {
        $tickets = $this->supportTicketRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            relations: ['department', 'employee', 'status_details', 'relatedInboxMessages', 'latestServiceJob'],
            searchValue: $request->get('searchValue'),
            filters: [
                'priority' => $request->get('priority'),
                'type' => $status,
                'status' => $request->get('status'),
            ],
            dataLimit: getWebConfig('pagination_limit')
        );

        $getDepartment = $this->departmentRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );
        $employees = $this->adminRepo->getEmployeeListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );
        $services = Service::all();

        $masterIds = [
            'support' => 1,
            'service' => 2,
            'career' => 3,
            'complaint' => 4,
            'retail' => 5,
            'wholesale' => 6,
        ];

        $masterId = $masterIds[$status] ?? 0;

        $aAllStatus = SupportTicketStatusMaster::where([
            'master_id' => $masterId,
            'status' => 'active'
        ])->get();

        $aInProgressStatus = SupportTicketStatusMaster::where([
            'master_id' => $masterId,
            'status' => 'active'
        ])->get();

        $views = [
            'career' => SupportTicket::CAREER[VIEW],
            'complaint' => SupportTicket::COMPLAINT[VIEW],
            'support' => SupportTicket::SUPPORT[VIEW],
            'service' => SupportTicket::SERVICE[VIEW],
            'retail' => SupportTicket::RETAIL[VIEW],
            'wholesale' => SupportTicket::WHOLESALE[VIEW],
            'default' => SupportTicket::LIST[VIEW],
        ];

        $view = $views[$status] ?? $views['default'];
        return view($view, compact('tickets', 'aAllStatus', 'aInProgressStatus', 'getDepartment', 'employees', 'services'));
    }

    public function logJobActivity(int $jobId, string $type, string $description, array $attachments = []): void
    {
        ServiceJobActivity::create([
            'job_id' => $jobId,
            'activity_type' => $type,
            'description' => $description,
            'attachments' => $attachments ? json_encode($attachments) : null,
            'created_by' => auth('admin')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveJobStatus(ServiceJob $job): string
    {
        $status = Str::of((string) $job->status)
            ->trim()
            ->lower()
            ->replace([' ', '-'], '_')
            ->value();

        if ($status !== '') {
            return $status;
        }

        if ($job->completed_at) {
            return 'completed';
        }

        if ($job->started_at) {
            return 'in_progress';
        }

        if ($job->scheduled_at) {
            return 'scheduled';
        }

        return 'assigned';
    }

    private function ensureJobStatus(ServiceJob $job, array $allowedStatuses, string $actionLabel): bool
    {
        $jobStatus = $this->resolveJobStatus($job);

        if ($jobStatus !== (string) $job->status) {
            $job->update(['status' => $jobStatus]);
            $job->refresh();
        }

        if (!in_array($jobStatus, $allowedStatuses, true)) {
            Toastr::error("Cannot {$actionLabel} while job status is '{$jobStatus}'.");
            return false;
        }

        return true;
    }


    public function getDetails($id, Request $request): View
    {
        $supportTicket = $this->supportTicketRepo->getListWhere(
            filters: ['id' => $id],
            relations: [
                'customer',
                'serviceJobs',
                'serviceJobs.service',
                'serviceJobs.technician',
                'serviceJobs.activities',
                'serviceJobs.activities.createdBy',
                'latestServiceJob',
                'latestServiceJob.service',
                'estimates',
                'estimates.service',
                'invoices',
                'changeOrders',
                'cancellations',
                'escalations.escalatedBy',
            ],
            dataLimit: 'all'
        )->first();

        if (!$supportTicket) {
            abort(404, translate('ticket_not_found'));
        }

        return view('admin-views.crm.tickets.partials.service-ticket-detail', compact('supportTicket'));
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:support_tickets,id',
        ]);

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->id]);
        if (!$ticket) {
            return response()->json(['message' => translate('ticket_not_found')], 404);
        }

        $currentStatusId = $ticket->status;
        $currentStatus = SupportTicketStatusMaster::find($currentStatusId);
        if (!$currentStatus) {
            return response()->json(['message' => translate('invalid_status')], 422);
        }

        $statusFlow = [
            'new' => 'assigned',
            'assigned' => 'scheduled',
            'scheduled' => 'in_progress',
            'in_progress' => 'completed',
            'completed' => 'closed',
            'closed' => 'new',
        ];

        $currentName = strtolower($currentStatus->name);
        $nextStatusName = $statusFlow[$currentName] ?? 'new';

        $nextStatus = SupportTicketStatusMaster::where('master_id', $currentStatus->master_id)
            ->where('name', 'like', ucfirst($nextStatusName))
            ->first();
        if (!$nextStatus) {
            return response()->json(['message' => translate('invalid_status')], 422);
        }

        if (strcasecmp((string)$nextStatus->name, 'assigned') === 0 && (int)($ticket->employee_id ?? 0) <= 0) {
            return response()->json(['message' => translate('assign_employee_before_setting_assigned_status')], 422);
        }

        $updateData = [
            'status' => $nextStatus?->id ?? $ticket->status,
            'reopen_count' => ($currentStatus->name === 'closed' ? ($ticket->reopen_count ?? 0) + 1 : $ticket->reopen_count ?? 0),
        ];

        if ($nextStatusName === 'waiting') {
            $updateData['sla_paused_at'] = now();
        } elseif ($ticket->sla_paused_at && in_array($nextStatusName, ['in_progress', 'completed', 'closed'])) {
            $updateData['sla_paused_at'] = null;
        }

        $this->supportTicketRepo->update(id: $ticket->id, data: $updateData);

        if ($nextStatusName === 'new' && $currentStatus->name === 'closed') {
            $message = "Ticket #{$ticket->id} has been reopened. Please review and proceed.";
            $link = route('admin.support-ticket.service.singleTicket', $ticket->id);

            $recipients = [];
            if ($ticket->employee_id) {
                $recipients[] = ['type' => 'employee', 'id' => $ticket->employee_id];
            }
            if ($ticket->department_id) {
                $recipients[] = ['type' => 'department', 'id' => $ticket->department_id];
            }

            if (!empty($recipients)) {
                $this->notificationRepo->notifyRecipients(
                    $ticket->id,
                    \App\Models\SupportTicket::class,
                    'Ticket Reopened',
                    $message,
                    $link,
                    $recipients
                );
            }
        }

        return response()->json([
            'message' => translate('status_updated_successfully'),
            'new_status_name' => $nextStatus?->name,
            'reopen_count' => $ticket->reopen_count ?? 0
        ], 200);
    }

    public function createEstimate(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'service_id' => 'required|exists:services,id',
            'is_mobile' => 'required|in:0,1',
            'parts_cost' => 'nullable|numeric|min:0',
            'labor_charge' => 'nullable|numeric|min:0',
            'extra_charge' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'description' => 'required|array',
        ]);

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->ticket_id]);
        $service = Service::findOrFail($request->service_id);

        $estimate = ServiceEstimate::create([
            'ticket_id' => $ticket->id,
            'service_id' => $service->id,
            'parts_cost' => $request->parts_cost ?? 0,
            'labor_charge' => $request->labor_charge ?? 0,
            'extra_charge' => $request->extra_charge ?? 0,
            'subtotal' => $request->subtotal,
            'tax' => $request->tax,
            'total' => $request->total,
            'is_mobile' => $request->is_mobile,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $defaultLangIndex = array_search(config('app.locale'), $request->lang);
        $defaultLangIndex = $defaultLangIndex === false ? 0 : $defaultLangIndex;
        $estimate->description = $request->description[$defaultLangIndex];
        $estimate->save();

        // Keep workflow moving forward, but do not regress if estimate is revised later.
        $nextStatus = (int)$ticket->status < ServiceTicketWorkflow::STATUS_OPEN
            ? ServiceTicketWorkflow::STATUS_OPEN
            : (int)$ticket->status;
        $this->supportTicketRepo->update($ticket->id, ['status' => $nextStatus]);

        // ADD CONVERSATION LOG
        $this->supportTicketConvRepo->add([
            'support_ticket_id' => $ticket->id,
            'admin_message' => "Estimate created for service {$service->title}. Total amount: " . number_format((float)$estimate->total, 2),
            'admin_id' => auth('admin')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $title = 'Service Estimate Created';
        $message = "A new estimate has been generated for Ticket #{$ticket->id}. Total: " . number_format($estimate->total, 2);
        $link = route('admin.support-ticket.service.singleTicket', $ticket->id);
        $this->workflowNotifier->notify(
            ticket: $ticket->id,
            eventKey: 'estimate_created',
            title: $title,
            message: $message,
            link: $link,
            recipients: [['type' => 'customer', 'id' => $ticket->customer_id]],
            templateData: ['amount' => number_format($estimate->total, 2)]
        );

        // TRANSLATION
        $this->translationRepo->add($request, 'App\Models\ServiceEstimate', $estimate->id);

        Toastr::success(translate('estimate_created_successfully'));
        return redirect()->back();
    }



    public function assignTicket(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'employee_id' => 'required|exists:admins,id',
            'service_id' => 'required|exists:services,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'sla_hours' => 'required|numeric|min:1',
        ]);

        $ticketId = $request->input('ticket_id');
        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $ticketId]);
        if (!$ticket || !$ticket->subject || !$ticket->description || !$ticket->customer_id) {
            Toastr::error(translate('invalid_ticket_details'));
            return redirect()->back();
        }

        $hasEstimate = ServiceEstimate::where('ticket_id', $ticketId)->exists();
        if (!$hasEstimate) {
            Toastr::error(translate('please_create_estimate_before_assigning_ticket'));
            return redirect()->back();
        }

        $employeeId = $request->input('employee_id');
        $serviceId = $request->input('service_id');
        $priority = $request->input('priority');
        $slaHours = $request->input('sla_hours');

        $this->supportTicketRepo->update($ticketId, [
            'employee_id' => $employeeId,
            'priority' => $priority,
            'status' => ServiceTicketWorkflow::STATUS_ASSIGNED,
            'sla_hours' => $slaHours,
        ]);

        $service = Service::findOrFail($serviceId);

        $job = ServiceJob::create([
            'ticket_id' => $ticketId,
            'technician_id' => $employeeId,
            'status' => 'assigned',
            'service_sku' => $serviceId,
            'priority' => $priority,
            'sla_hours' => $slaHours,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logJobActivity($job->id, 'assign_job', "Ticket assigned to technician for: {$service->title}");

        $this->supportTicketConvRepo->add([
            'support_ticket_id' => $ticketId,
            'admin_message' => "Ticket assigned to technician for: {$service->title}",
            'admin_id' => auth('admin')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $title = 'Ticket Assigned';
        $message = "Ticket #{$ticketId} has been assigned for {$service->title}.";
        $link = route('admin.support-ticket.service.singleTicket', $ticketId);
        $this->workflowNotifier->notify(
            ticket: $ticketId,
            eventKey: 'ticket_assigned',
            title: $title,
            message: $message,
            link: $link,
            recipients: [
                ['type' => 'customer', 'id' => $ticket->customer_id],
                ['type' => 'employee', 'id' => $employeeId],
            ],
            templateData: ['service_title' => $service->title]
        );

        Toastr::success(translate('ticket_assigned_successfully'));
        return redirect()->back();
    }

    public function scheduleTicket(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'job_id' => 'required|exists:service_jobs,id',
            'technician_id' => 'required|exists:admins,id',
            'scheduled_at' => 'required|date',
            'is_mobile' => 'required|in:0,1',
        ]);

        $ticketId = $request->input('ticket_id');
        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $ticketId]);
        if (!$ticket) {
            Toastr::error(translate('ticket_not_found'));
            return redirect()->back();
        }
        $hasEstimate = ServiceEstimate::where('ticket_id', $ticketId)->exists();
        if (!$hasEstimate) {
            Toastr::error(translate('please_create_estimate_before_assigning_ticket'));
            return redirect()->back();
        }

        $job = ServiceJob::find($request->job_id);
        if (!$job) {
            Toastr::error(translate('job_not_found'));
            return redirect()->back();
        }

        if ((int)$job->ticket_id !== (int)$ticketId) {
            Toastr::error(translate('invalid_ticket_details'));
            return redirect()->back();
        }

        if (!$this->ensureJobStatus($job, ['assigned', 'scheduled'], 'schedule this job')) {
            return redirect()->back();
        }

        $job->update([
            'technician_id' => $request->technician_id,
            'status' => 'scheduled',
            'scheduled_at' => Carbon::parse($request->scheduled_at),
            'is_mobile' => $request->is_mobile,
        ]);

        $this->logJobActivity($job->id, 'schedule_job', "Job scheduled for " . Carbon::parse($request->scheduled_at)->format('d M, Y H:i') . ($request->is_mobile ? " (Mobile)" : " (In-shop)"));


        $this->supportTicketRepo->update($ticketId, [
            'status' => ServiceTicketWorkflow::STATUS_SCHEDULED,
        ]);

        $this->supportTicketConvRepo->add([
            'support_ticket_id' => $ticketId,
            'admin_message' => "Ticket scheduled for " . Carbon::parse($request->scheduled_at)->format('d M, Y H:i') . ($request->is_mobile ? " (Mobile)" : " (In-shop)"),
            'admin_id' => auth('admin')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SupportTicketNotification::create([
            'ticket_id' => $ticketId,
            'recipient_id' => $ticket->customer_id,
            'message' => "Your ticket #{$ticketId} is scheduled for " . Carbon::parse($request->scheduled_at)->format('d M, Y H:i'),
            'type' => 'email',
            'created_at' => now(),
        ]);


        $scheduledAt = Carbon::parse($request->scheduled_at)->format('d M, Y H:i');
        $title = 'Ticket Scheduled';
        $message = "Ticket #{$ticketId} is scheduled for {$scheduledAt}.";
        $link = route('admin.support-ticket.service.singleTicket', $ticketId);
        $this->workflowNotifier->notify(
            ticket: $ticketId,
            eventKey: 'ticket_scheduled',
            title: $title,
            message: $message,
            link: $link,
            recipients: [['type' => 'customer', 'id' => $ticket->customer_id]],
            templateData: ['scheduled_at' => $scheduledAt]
        );

        Toastr::success(translate('ticket_scheduled_successfully'));
        return redirect()->back();
    }

    public function startJob(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'job_id' => 'required|exists:service_jobs,id',
            'gps_coordinates' => 'nullable|string',
            'odometer_reading' => 'nullable|numeric',
            'description' => 'required|array',
            'lang' => 'required|array',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        $ticketId = $request->input('ticket_id');
        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $ticketId]);
        if (!$ticket) {
            Toastr::error(translate('ticket_not_found'));
            return redirect()->back();
        }

        $job = ServiceJob::find($request->job_id);
        if (!$job) {
            Toastr::error(translate('job_not_found'));
            return redirect()->back();
        }

        if ((int)$job->ticket_id !== (int)$ticketId) {
            Toastr::error(translate('invalid_ticket_details'));
            return redirect()->back();
        }

        if (!$this->ensureJobStatus($job, ['scheduled', 'in_progress'], 'start this job')) {
            return redirect()->back();
        }

        $defaultLangIndex = array_search(config('app.locale'), $request->lang);
        $defaultLangIndex = $defaultLangIndex === false ? 0 : $defaultLangIndex;

        $hasEstimate = ServiceEstimate::where('ticket_id', $ticketId)->exists();
        if (!$hasEstimate) {
            Toastr::error(translate('please_create_estimate_before_assigning_ticket'));
            return redirect()->back();
        }

        $attachments = [];
        foreach ($request->file('images') as $file) {
            $name = $file->getClientOriginalName();
            $file->storeAs('service-attachments', $name, 'public'); // store in storage/app/public/job_images
            $attachments[] = $name;
        }

        $job->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'gps_location' => $request->gps_coordinates,
            'odometer_start' => $request->odometer_reading,
            'description' => $request->description[$defaultLangIndex],
            'attachments' => $attachments,
        ]);

        $this->supportTicketRepo->update($request->ticket_id, [
            'status' => ServiceTicketWorkflow::STATUS_IN_PROGRESS,
        ]);
        ServiceJobActivity::create([
            'job_id' => $job->id,
            'activity_type' => 'start_job',
            'description' => 'Job started' . ($request->gps_coordinates ? " at {$request->gps_coordinates}" : ''),
            'created_by' => auth('admin')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->translationRepo->add($request, 'App\Models\ServiceJob', $job->id);

        $title = 'Service Started';
        $message = "Service for ticket #{$ticketId} has started.";
        $link = route('admin.support-ticket.service.singleTicket', $ticketId);
        $this->workflowNotifier->notify(
            ticket: $ticketId,
            eventKey: 'job_started',
            title: $title,
            message: $message,
            link: $link,
            recipients: [['type' => 'customer', 'id' => $ticket->customer_id]]
        );

        Toastr::success(translate('job_started_successfully'));
        return redirect()->back();
    }

    public function createChangeOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'job_id' => 'required|exists:service_jobs,id',
            'additional_charges' => 'required|numeric|min:0',
            'description' => 'required|array',
            'lang' => 'required|array',
            'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $ticketId = $request->input('ticket_id');
        $jobId = $request->input('job_id');
        $additionalCharges = $request->input('additional_charges');
        $defaultLangIndex = array_search(config('app.locale'), $request->lang);
        $defaultLangIndex = $defaultLangIndex === false ? 0 : $defaultLangIndex;
        $description = $request->description[$defaultLangIndex];
        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->ticket_id]);
        $job = ServiceJob::find($jobId);

        if (!$ticket || !$job || (int)$job->ticket_id !== (int)$ticketId) {
            Toastr::error(translate('invalid_ticket_details'));
            return redirect()->back();
        }

        if (!$this->ensureJobStatus($job, ['in_progress', 'completed'], 'create change order')) {
            return redirect()->back();
        }


        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = $file->getClientOriginalName();
            $file->storeAs('service-attachments', $name, 'public');
            $image = $name;
        } else {
            $image = null;
        }

        $changeOrder = ServiceChangeOrder::create([
            'ticket_id' => $ticketId,
            'job_id' => $jobId,
            'additional_charges' => $additionalCharges,
            'description' => $description,
            'image' => $image,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        $this->supportTicketConvRepo->add([
            'support_ticket_id' => $ticketId,
            'admin_message' => "Change order created: $description (Additional Charges: $additionalCharges)",
            'admin_id' => auth('admin')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->logJobActivity($jobId, 'change_order', "Change order created: $description (Additional Charges: $additionalCharges)");
        $title = 'Change Order Added';
        $message = "Change order added for ticket #{$ticketId}: {$description}";
        $link = route('admin.support-ticket.service.singleTicket', $ticketId);
        $this->workflowNotifier->notify(
            ticket: $ticketId,
            eventKey: 'change_order_created',
            title: $title,
            message: $message,
            link: $link,
            recipients: [['type' => 'customer', 'id' => $ticket->customer_id]],
            templateData: ['description' => $description]
        );

        $this->translationRepo->add($request, 'App\Models\ServiceChangeOrder', $changeOrder->id);
        Toastr::success(translate('change_order_created_successfully'));
        return redirect()->back();
    }


    public function completeJob(Request $request): RedirectResponse
    {
        Log::info('=== Job Completion Started ===', ['request' => $request->all()]);

        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'job_id' => 'required|exists:service_jobs,id',
            'odometer_end' => 'nullable|numeric',
            'remarks' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:jpg,png,pdf',
            'customer_signature' => 'required_if:is_mobile,1|string',
            'items.*.item_type' => 'required|in:part,labor',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        $ticketId = (int)$request->ticket_id;
        $job = ServiceJob::find((int)$request->job_id);
        if (!$job) {
            Log::error('❌ Job not found', ['job_id' => $request->job_id]);
            Toastr::error(translate('job_not_found'));
            return redirect()->back();
        }

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $ticketId]);
        if (!$ticket) {
            Toastr::error(translate('ticket_not_found'));
            return redirect()->back();
        }

        if ((int)$job->ticket_id !== $ticketId) {
            Toastr::error(translate('invalid_ticket_details'));
            return redirect()->back();
        }

        if (!$this->ensureJobStatus($job, ['in_progress', 'completed'], 'complete this job')) {
            return redirect()->back();
        }

        $isMobile = $job->is_mobile;

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('service-attachments', 'public');
                $filename = basename($path);
                $attachments[] = $filename;
            }
        }

        Log::info('Job updating...', ['job_id' => $job->id]);

        $invoice = null;
        $paymentLink = null;
        $newTotal = null;
        DB::transaction(function () use (
            $job,
            $request,
            $isMobile,
            $attachments,
            &$invoice,
            &$paymentLink,
            &$newTotal,
            $ticketId
        ) {
            if ($job->status !== 'completed') {
                $job->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'odometer_end' => $request->odometer_end,
                    'remarks' => $request->remarks,
                    'attachments' => $attachments ?: $job->attachments,
                    'customer_signature' => $isMobile ? $request->customer_signature : null,
                ]);
            }

            if ($request->has('items') && !ServiceJobItem::where('job_id', $job->id)->exists()) {
                foreach ($request->items as $item) {
                    ServiceJobItem::create([
                        'job_id' => $job->id,
                        'item_type' => $item['item_type'],
                        'item_name' => $item['item_name'],
                        'quantity' => $item['quantity'],
                        'rate' => $item['rate'],
                        'total' => $item['quantity'] * $item['rate'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            Log::info('Job items synced', ['job_id' => $job->id]);

            $alreadyCompletedLogged = ServiceJobActivity::where('job_id', $job->id)
                ->where('activity_type', 'complete_job')
                ->exists();
            if (!$alreadyCompletedLogged) {
                $this->logJobActivity($job->id, 'complete_job', 'Job completed: ' . ($request->remarks ?? ''), $attachments);
            }

            $estimate = ServiceEstimate::where('ticket_id', $job->ticket_id)->latest('id')->first();
            Log::info('Estimate check', [
                'ticket_id' => $job->ticket_id,
                'estimate_found' => (bool)$estimate,
            ]);

            if ($estimate) {
                $changeOrdersSum = ServiceChangeOrder::where('ticket_id', $job->ticket_id)->sum('additional_charges');
                $jobItemsSum = ServiceJobItem::where('job_id', $job->id)->sum('total');
                $newSubtotal = (float)$estimate->subtotal + (float)$changeOrdersSum + (float)$jobItemsSum + (float)$estimate->extra_charge + (float)$estimate->labor_charge;
                $taxRate = (float)$estimate->subtotal > 0 ? ((float)$estimate->tax / (float)$estimate->subtotal) : 0.1;
                $newTax = $newSubtotal * $taxRate;
                $newTotal = $newSubtotal + $newTax;

                $invoice = ServiceInvoice::where('job_id', $job->id)->latest('id')->first();
                if (!$invoice) {
                    $invoice = ServiceInvoice::create([
                        'ticket_id' => $job->ticket_id,
                        'job_id' => $job->id,
                        'subtotal' => $newSubtotal,
                        'tax' => $newTax,
                        'total' => $newTotal,
                        'payment_status' => 'pending',
                        'payment_link_expires_at' => now()->addHours(self::SERVICE_INVOICE_LINK_EXPIRE_HOURS),
                        'generated_at' => now(),
                    ]);
                } elseif ($invoice->payment_status !== 'paid') {
                    $invoice->update([
                        'subtotal' => $newSubtotal,
                        'tax' => $newTax,
                        'total' => $newTotal,
                        'payment_status' => 'pending',
                        'payment_link_expires_at' => now()->addHours(self::SERVICE_INVOICE_LINK_EXPIRE_HOURS),
                    ]);
                    $invoice->refresh();
                } else {
                    $newTotal = (float)$invoice->total;
                }

                if (!$invoice->payment_link) {
                    $paymentLink = route('pay-service-invoice', $invoice->id);
                    $invoice->update(['payment_link' => $paymentLink]);
                } else {
                    $paymentLink = $invoice->payment_link;
                }

                Log::info('Invoice synced', ['invoice_id' => $invoice->id]);
            } else {
                Log::warning('No estimate found for completed job', [
                    'ticket_id' => $job->ticket_id,
                    'job_id' => $job->id,
                ]);
            }

            $this->supportTicketRepo->update((string)$ticketId, ['status' => ServiceTicketWorkflow::STATUS_COMPLETED]);
        });

        SupportTicketNotification::create([
            'ticket_id' => $ticketId,
            'recipient_id' => $ticket->customer_id,
            'message' => "Your ticket #{$ticketId} job has been completed.",
            'type' => 'email',
            'created_at' => now(),
        ]);


        if ($invoice && $paymentLink) {
            $invoiceMessage = "Invoice generated for ticket #{$ticket->id}. Pay here: {$paymentLink}";
            $this->workflowNotifier->notify(
                ticket: $ticket->id,
                eventKey: 'invoice_generated',
                title: 'Invoice Generated',
                message: $invoiceMessage,
                link: route('admin.support-ticket.service.singleTicket', $ticket->id),
                recipients: [['type' => 'customer', 'id' => $ticket->customer_id]],
                templateData: [
                    'amount' => number_format((float)$invoice->total, 2),
                    'payment_link' => $paymentLink,
                ]
            );
        }

        $completionMessage = "Your ticket #{$ticketId} job has been completed.";
        if ($invoice && $paymentLink) {
            $completionMessage .= " Total payable amount is " . number_format((float)($newTotal ?? $invoice->total), 2) . ". Pay here: {$paymentLink}";
        }

        $this->workflowNotifier->notify(
            ticket: $ticket->id,
            eventKey: 'job_completed',
            title: 'Service Completed',
            message: "Your ticket #{$ticketId} job has been completed.",
            link: route('admin.support-ticket.service.singleTicket', $ticket->id),
            recipients: [['type' => 'customer', 'id' => $ticket->customer_id]]
        );

        $alreadyPosted = DB::table('support_ticket_convs')
            ->where('support_ticket_id', $ticket->id)
            ->where('admin_message', $completionMessage)
            ->exists();
        if (!$alreadyPosted) {
            $this->supportTicketConvRepo->add([
                'support_ticket_id' => $ticket->id,
                'admin_message' => $completionMessage,
                'admin_id' => auth('admin')->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Log::info('=== Job Completion Ended ===', ['job_id' => $job->id]);

        Toastr::success(translate('job_completed_successfully'));
        return redirect()->back();
    }

    public function remindInvoicePayment(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'invoice_id' => 'required|exists:service_invoices,id',
        ]);

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->ticket_id]);
        if (!$ticket) {
            Toastr::error(translate('ticket_not_found'));
            return redirect()->back();
        }

        $invoice = ServiceInvoice::where('id', $request->invoice_id)
            ->where('ticket_id', $ticket->id)
            ->first();

        if (!$invoice) {
            Toastr::error(translate('Invoice not found or already paid'));
            return redirect()->back();
        }

        if ($invoice->payment_status === 'paid') {
            Toastr::error(translate('Invoice not found or already paid'));
            return redirect()->back();
        }

        $paymentLink = $invoice->payment_link ?: route('pay-service-invoice', $invoice->id);
        $invoice->update([
            'payment_status' => 'pending',
            'payment_link' => $paymentLink,
            'payment_link_expires_at' => now()->addHours(self::SERVICE_INVOICE_LINK_EXPIRE_HOURS),
        ]);
        $invoice->refresh();

        $reminderMessage = "Payment reminder for ticket #{$ticket->id}. Pay here: {$invoice->payment_link}";
        $recipients = array_values(array_filter([
            $ticket->customer_id ? ['type' => 'customer', 'id' => $ticket->customer_id] : null,
        ]));
        if (empty($recipients)) {
            Toastr::error(translate('Customer Not Found'));
            return redirect()->back();
        }

        $this->workflowNotifier->notify(
            ticket: $ticket->id,
            eventKey: 'invoice_reminder',
            title: 'Invoice Payment Reminder',
            message: $reminderMessage,
            link: route('admin.support-ticket.service.singleTicket', $ticket->id),
            recipients: $recipients,
            templateData: [
                'amount' => number_format((float)$invoice->total, 2),
                'payment_link' => $invoice->payment_link,
            ]
        );

        if ($invoice->job_id) {
            $this->logJobActivity(
                $invoice->job_id,
                'invoice_reminder',
                "Invoice payment reminder sent for invoice #{$invoice->id}"
            );
        }

        Toastr::success('Invoice payment reminder sent.');
        return redirect()->back();
    }

    public function qaConfirmation(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'job_id' => 'required|exists:service_jobs,id',
            'qa_passed' => 'required|boolean',
            'qa_notes' => 'required|string',
        ]);

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->ticket_id]);
        $job = ServiceJob::find($request->job_id);

        if (!$ticket || !$job || (int)$job->ticket_id !== (int)$ticket->id) {
            Toastr::error(translate('ticket_and_job_not_found'));
            return redirect()->back();
        }

        if (!$this->ensureJobStatus($job, ['completed'], 'submit QA confirmation')) {
            return redirect()->back();
        }

        $service = Service::find($job->service_sku);

        if ($request->qa_passed) {
            if ($service) {
                $service->update(['call_center_flag' => true]);
            }
            ServiceJobActivity::create([
                'job_id' => $job->id,
                'activity_type' => 'qa_confirmation',
                'description' => 'QA passed: ' . $request->qa_notes,
                'created_by' => auth('admin')->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->supportTicketRepo->update($ticket->id, [
                'status' => ServiceTicketWorkflow::STATUS_ASSIGNED, // Reopen
                'reopen_count' => ($ticket->reopen_count ?? 0) + 1,
            ]);
            ServiceJobActivity::create([
                'job_id' => $job->id,
                'activity_type' => 'qa_failed',
                'description' => 'QA failed: ' . $request->qa_notes,
                'created_by' => auth('admin')->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            SupportTicketNotification::create([
                'ticket_id' => $ticket->id,
                'recipient_id' => $ticket->employee_id,
                'message' => "Ticket #{$ticket->id} has been reopened due to QA failure.",
                'type' => 'email',
                'created_at' => now(),
            ]);



            $this->workflowNotifier->notify(
                ticket: $ticket->id,
                eventKey: 'qa_failed',
                title: 'Ticket Reopened',
                message: "Ticket #{$ticket->id} has been reopened due to QA failure.",
                link: route('admin.support-ticket.service.singleTicket', $ticket->id),
                recipients: array_filter([
                    ['type' => 'customer', 'id' => $ticket->customer_id],
                    $ticket->employee_id ? ['type' => 'employee', 'id' => $ticket->employee_id] : null,
                ])
            );
        }
        Toastr::success(translate('qa_confirmation_processed'));
        return redirect()->back();
    }

    public function closeTicket(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'qa_notes' => 'required|string',
            'force_close' => 'nullable|boolean',
        ]);

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->ticket_id]);
        if (!$ticket) {
            Toastr::error(translate('ticket_not_found'));
            return redirect()->back();
        }

        $forceClose = (bool)$request->force_close;
        if ((int)$ticket->status !== ServiceTicketWorkflow::STATUS_COMPLETED && !$forceClose) {
            Toastr::error("Ticket #{$ticket->id} must be completed before closing.");
            return redirect()->back();
        }

        $latestJob = ServiceJob::where('ticket_id', $ticket->id)->latest('id')->first();
        if ($latestJob && $latestJob->status !== 'completed' && !$forceClose) {
            Toastr::error("Cannot close ticket while job status is '{$latestJob->status}'.");
            return redirect()->back();
        }

        $hasUnpaidInvoices = ServiceInvoice::where('ticket_id', $ticket->id)
            ->where('payment_status', '!=', 'paid')
            ->exists();

        if ($hasUnpaidInvoices) {
            if (!$request->has('force_close') || !$request->force_close) {
                return redirect()->back()->with('force_close_prompt', $ticket->id);
            }

            if (auth('admin')->id() != 1 && auth('admin')->id() != $ticket->owner_id) {
                Toastr::error(translate('You have no access to force close tickets'));
                return redirect()->back();
            }
        }

        $this->supportTicketRepo->update($ticket->id, [
            'status' => ServiceTicketWorkflow::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        $latestJobId = ServiceJob::where('ticket_id', $ticket->id)->latest('id')->value('id');
        if ($latestJobId) {
            $this->logJobActivity($latestJobId, 'close_ticket', 'Ticket closed after QA: ' . $request->qa_notes);
        }

        SupportTicketNotification::create([
            'ticket_id' => $ticket->id,
            'recipient_id' => $ticket->customer_id,
            'message' => "Your ticket #{$ticket->id} has been closed.",
            'type' => 'email',
            'created_at' => now(),
        ]);

        $this->workflowNotifier->notify(
            ticket: $ticket->id,
            eventKey: 'ticket_closed',
            title: 'Ticket Closed',
            message: "Your ticket #{$ticket->id} has been closed.",
            link: route('admin.support-ticket.service.singleTicket', $ticket->id),
            recipients: [['type' => 'customer', 'id' => $ticket->customer_id]]
        );
        Toastr::success(translate('ticket_closed_successfully'));
        return redirect()->back();
    }


    public function cancelTicket(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'job_id' => 'required|exists:service_jobs,id',
            'reason' => 'required|string',
            'fee_amount' => 'required|numeric|min:0',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        $ticketId = $request->input('ticket_id');
        $jobId = $request->input('job_id');
        $reason = $request->input('reason');
        $feeAmount = $request->input('fee_amount');
        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->ticket_id]);
        $job = ServiceJob::with(['items', 'invoice'])->find($jobId);

        if (!$ticket || !$job || (int)$job->ticket_id !== (int)$ticketId) {
            Toastr::error(translate('invalid_ticket_details'));
            return redirect()->back();
        }

        if ((int)$ticket->status === ServiceTicketWorkflow::STATUS_CLOSED || $job->status === 'cancelled') {
            Toastr::error(translate('ticket_already_closed'));
            return redirect()->back();
        }

        if (!$this->ensureJobStatus($job, ['assigned', 'scheduled', 'in_progress'], 'cancel this ticket')) {
            return redirect()->back();
        }

        // Calculate maximum refundable amount from job invoice or items
        $maxRefundAmount = $this->calculateMaxRefundAmount($job);

        // Use server-calculated amount if not provided or exceeds maximum
        $refundAmount = $request->input('refund_amount');
        if ($refundAmount === null || $refundAmount > $maxRefundAmount) {
            $refundAmount = $maxRefundAmount;
        }

        ServiceCancellation::create([
            'ticket_id' => $ticketId,
            'job_id' => $jobId,
            'cancellation_reason' => $reason,
            'fee_amount' => $feeAmount,
            'refund_amount' => $refundAmount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->supportTicketRepo->update($ticketId, [
            'status' => ServiceTicketWorkflow::STATUS_CLOSED,
            'closed_at' => now(),
        ]);
        $job->update(['status' => 'cancelled']);

        $this->supportTicketConvRepo->add([
            'support_ticket_id' => $ticketId,
            'admin_message' => "Ticket cancelled: $reason (Fee: $feeAmount, Refund: $refundAmount)",
            'admin_id' => auth('admin')->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->logJobActivity($jobId, 'cancel_ticket', "Ticket cancelled: $reason (Fee: $feeAmount, Refund: $refundAmount)");

        SupportTicketNotification::create([
            'ticket_id' => $ticketId,
            'recipient_id' => $ticket->customer_id,
            'message' => "Your ticket #{$ticketId} has been cancelled: $reason",
            'type' => 'email',
            'created_at' => now(),
        ]);

        $this->workflowNotifier->notify(
            ticket: $ticketId,
            eventKey: 'ticket_cancelled',
            title: 'Ticket Cancelled',
            message: "Your ticket #{$ticketId} has been cancelled: $reason",
            link: route('admin.support-ticket.service.singleTicket', $ticketId),
            recipients: [['type' => 'customer', 'id' => $ticket->customer_id]],
            templateData: ['reason' => $reason]
        );

        Toastr::success(translate('ticket_cancelled_successfully'));
        return redirect()->back();
    }

    public function getView($id): View
    {
        $supportTicket = $this->supportTicketRepo->getListWhere(
            filters: ['id' => $id],
            relations: ['conversations', 'latestServiceJob', 'invoices', 'estimates', 'changeOrders', 'cancellations'],
            dataLimit: 'all'
        );
        return view(SupportTicket::VIEW[VIEW], compact('supportTicket'));
    }

    public function reply(SupportTicketRequest $request, SupportTicketService $supportTicketService): RedirectResponse
    {
        if ($request['image'] == null && $request['replay'] == null) {
            Toastr::warning(translate('type_something') . '!');
            return back();
        }
        $dataArray = $supportTicketService->getAddData(request: $request);
        $this->supportTicketConvRepo->add(data: $dataArray);
        Toastr::success(translate('reply_added_successfully'));
        return back();
    }

    public function checkSLABreaches(): void
    {
        $tickets = SupportTicketModel::query()
            ->with('latestServiceJob')
            ->whereIn('status', ServiceTicketWorkflow::activeSlaStatuses())
            ->get();

        foreach ($tickets as $ticket) {
            $responseTime = now()->diffInHours($ticket->created_at);
            $resolutionTime = $ticket->latestServiceJob && $ticket->latestServiceJob->started_at ? now()->diffInHours($ticket->latestServiceJob->started_at) : null;

            if ($responseTime > $ticket->sla_hours && (int)$ticket->status === ServiceTicketWorkflow::STATUS_ASSIGNED) {
                SupportTicketNotification::create([
                    'ticket_id' => $ticket->id,
                    'recipient_id' => 1, // Owner ID
                    'message' => "Response SLA breached for ticket #{$ticket->id}",
                    'type' => 'email',
                    'created_at' => now(),
                ]);
                $this->supportTicketRepo->update((string)$ticket->id, ['escalation_level' => 'L1']);
            }

            if ($resolutionTime && $resolutionTime > $ticket->sla_hours) {
                $this->supportTicketRepo->update((string)$ticket->id, [
                    'escalation_level' => 'L2',
                    'escalated_at' => now(),
                    'escalated_by' => 1, // System
                ]);
                SupportTicketNotification::create([
                    'ticket_id' => $ticket->id,
                    'recipient_id' => 2, // L2 Manager ID
                    'message' => "Resolution SLA breached for ticket #{$ticket->id}",
                    'type' => 'email',
                    'created_at' => now(),
                ]);
            }
        }
    }

    public function escalate(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_id' => 'required|exists:support_tickets,id',
            'reason' => 'required|string|max:1000',
        ]);

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->ticket_id]);

        $title   = 'Ticket Escalated';
        $message = "Service Ticket #{$ticket->id} escalated. Reason: {$request->reason}";
        $link    = route('admin.support-ticket.service.singleTicket', $ticket->id);

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

        $latestJobId = ServiceJob::where('ticket_id', $ticket->id)->latest('id')->value('id');
        if ($latestJobId) {
            $this->logJobActivity($latestJobId, 'escalated', $message);
        }

        Toastr::success(translate('Ticket escalated successfully'));
        return back();
    }

    /**
     * Calculate the maximum refundable amount for a service job.
     * Based on the job's invoice total paid amount or sum of items if no invoice exists.
     */
    private function calculateMaxRefundAmount(ServiceJob $job): float
    {
        // If job has an invoice with paid amount, use that (less any fee)
        if ($job->invoice && $job->invoice->paid_amount > 0) {
            return (float) $job->invoice->paid_amount;
        }

        // Otherwise, calculate from job items
        if ($job->items && $job->items->isNotEmpty()) {
            return (float) $job->items->sum('total_amount');
        }

        // Default to 0 if no items or invoice
        return 0.0;
    }
}
