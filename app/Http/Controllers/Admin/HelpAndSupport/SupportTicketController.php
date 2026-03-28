<?php

namespace App\Http\Controllers\Admin\HelpAndSupport;

use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Enums\SupportTicketStatusGroup;
use App\Enums\ViewPaths\Admin\SupportTicket;
use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Contracts\Repositories\SupportTicketActivityRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\SupportTicketRequest;
use App\Repositories\SupportTicketRepository;
use App\Services\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\SupportTicketStatusMaster;
use Illuminate\Support\Facades\Log;
use App\Models\SupportTicketNotification;
use App\Models\CronConfiguration;
use App\Models\Service;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SupportTicketExport;
use App\Models\Departments;
use App\Services\Crm\EscalationService;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface; // Add this
use App\Support\CareerTicketWorkflow;
use App\Support\ComplaintTicketWorkflow;
use App\Support\RetailTicketWorkflow;
use App\Support\SupportTicketLifecycle;
use App\Support\ServiceTicketWorkflow;
use App\Support\WholesaleTicketWorkflow;
use Illuminate\Validation\ValidationException;
class SupportTicketController extends BaseController
{
    /**
     * @param SupportTicketRepository $supportTicketRepo
     */
    public function __construct(
        private readonly SupportTicketRepositoryInterface $supportTicketRepo,
        private readonly SupportTicketConvRepositoryInterface $supportTicketConvRepo,
        private readonly DepartmentRepositoryInterface          $departmentRepo,
        private readonly AdminRepositoryInterface               $adminRepo,
        private readonly SupportTicketActivityRepositoryInterface $activityRepo,
        private readonly AdminNotificationRepositoryInterface   $notificationRepo, // Add this
        private readonly EscalationService                      $escalationService,

    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return \Illuminate\Contracts\View\View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, $type = 'all'): View
    {
        return $this->getListView(request: $request, status: $type);
    }

    protected function logSupportActivity(int $ticketId, string $title, string $description, ?int $employeeId = null): void
    {
        $this->activityRepo->add([
            'support_ticket_id' => $ticketId,
            'employee_id' => $employeeId ?? auth('admin')->id(),
            'title' => $title,
            'description' => $description,
            'noted_at' => now(),
        ]);
    }



    public function getListView(Request $request, string $status): View
    {
        $defaultStatusIds = [
            'support'   => SupportTicketLifecycle::STATUS_NEW,
            'career'    => CareerTicketWorkflow::STATUS_NEW,
            'complaint' => ComplaintTicketWorkflow::STATUS_NEW,
            'retail'    => RetailTicketWorkflow::STATUS_NEW,
            'wholesale' => WholesaleTicketWorkflow::STATUS_NEW,
        ];
        $statusFilter = $status === 'service'
            ? $request->get('status', 'all')
            : $request->get('status', $defaultStatusIds[$status] ?? null);

        $relations = ['department', 'employee', 'status_details', 'relatedInboxMessages', 'customer'];
        if ($status === 'service') {
            $relations = array_merge($relations, ['service', 'relatedInboxMessage', 'latestServiceJob', 'latestServiceJob.service']);
        }

        $tickets = $this->supportTicketRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            relations: $relations,
            searchValue: $request->get('searchValue'),
            filters: [
                'priority' => $request->get('priority'),
                'type'     => $status,
                'status'   => $statusFilter,
            ],
            dataLimit: getWebConfig('pagination_limit')
        );

        $getDepartment  = $this->departmentRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );
        $employees = $this->adminRepo->getEmployeeListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );

        $masterIds = [
            'support'   => SupportTicketStatusGroup::Support->value,
            'service'   => ServiceTicketWorkflow::STATUS_MASTER_ID,
            'career'    => SupportTicketStatusGroup::Career->value,
            'complaint' => SupportTicketStatusGroup::Complaint->value,
            'retail'    => SupportTicketStatusGroup::Retail->value,
            'wholesale' => SupportTicketStatusGroup::Wholesale->value,
        ];

        $masterId = $masterIds[$status] ?? 0;
        $aAllStatus = SupportTicketStatusMaster::where([
            'master_id' => $masterId,
            'status'    => 'active'
        ])->get();

        $aInProgressStatus = SupportTicketStatusMaster::where([
            'master_id' => $masterId,
            'status'    => 'active'
        ])->get();
        $services = Service::all();
        $views = [
            'career'     => SupportTicket::CAREER[VIEW],
            'complaint'  => SupportTicket::COMPLAINT[VIEW],
            'support'    => SupportTicket::SUPPORT[VIEW],
            'service'    => SupportTicket::SERVICE[VIEW],
            'retail'     => SupportTicket::RETAIL[VIEW],
            'wholesale'  => SupportTicket::WHOLESALE[VIEW],
            'default'    => SupportTicket::LIST[VIEW],
        ];
        $view = $views[$status] ?? $views['default'];
        return view($view, compact('tickets', 'aAllStatus', 'aInProgressStatus', 'getDepartment', 'employees', 'services', 'status'));
    }

    public function export(Request $request, string $type)
    {
        $fileName = ucfirst($type) . '_Tickets_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
        return Excel::download(
            new SupportTicketExport($request, $type),
            $fileName
        );
    }


    public function updateStatus(Request $request): JsonResponse
    {
        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->id]);
        if (!$ticket) {
            return response()->json(['message' => translate('ticket_not_found')], 404);
        }

        $currentStatus = SupportTicketStatusMaster::find($ticket->status);
        $nextStatus = null;

        $requestedStatusId = (int)$request->input('status');
        if ($requestedStatusId > 0) {
            $masterId = $currentStatus?->master_id ?? $this->resolveStatusMasterIdByType((string)$ticket->type);
            $nextStatus = SupportTicketStatusMaster::query()
                ->where('id', $requestedStatusId)
                ->when($masterId, function ($query) use ($masterId) {
                    $query->where('master_id', $masterId);
                })
                ->where('status', 'active')
                ->first();

            if (!$nextStatus) {
                return response()->json([
                    'message' => translate('invalid_status'),
                ], 422);
            }
        } else {
            // Default next status
            $nextStatusName = 'Open';

            if ($currentStatus) {
                $currentName = strtolower($currentStatus->name);

                // 🔹 Status flow mapping (clear readable logic)
                $statusFlow = SupportTicketLifecycle::defaultStatusFlow();

                $nextStatusName = $statusFlow[$currentName] ?? 'closed';
            }

            // 🔹 Fetch next status record
            $nextStatus = SupportTicketStatusMaster::where('master_id', $currentStatus->master_id ?? null)
                ->whereRaw('LOWER(name) = ?', [strtolower($nextStatusName)])
                ->first();
        }

        // 🔹 Handle reopen logic
        $oldStatusSlug = strtolower($currentStatus?->name ?? '');
        $newStatusSlug = strtolower($nextStatus?->name ?? '');
        $isReopened = $oldStatusSlug === 'closed' && $newStatusSlug !== 'closed';

        if ($nextStatus && strcasecmp((string)$nextStatus->name, 'assigned') === 0 && (int)($ticket->employee_id ?? 0) <= 0) {
            return response()->json([
                'message' => translate('assign_employee_before_setting_assigned_status'),
            ], 422);
        }

        $ticket->update([
            'status' => $nextStatus?->id ?? $ticket->status,
            'reopen_count' => $isReopened ? (($ticket->reopen_count ?? 0) + 1) : ($ticket->reopen_count ?? 0),
        ]);

        // 🔹 Send reopen notifications
        if ($isReopened) {

            $link = route('admin.support-ticket.details', $ticket->id);

            $recipients = [];
            if ($ticket->employee_id) {
                $recipients[] = [
                    'type'    => 'employee',
                    'id'      => $ticket->employee_id,
                    'title'   => 'Ticket Reopened',
                    'message' => "Ticket #{$ticket->id} has been reopened. Please review and proceed.",
                ];
            }

            // 🔹 Department Message
            if ($ticket->department_id) {
                $department = Departments::find($ticket->department_id);
                $deptName = $department?->name ?? '';

                $recipients[] = [
                    'type'    => 'department',
                    'id'      => $ticket->department_id,
                    'title'   => 'Ticket Reopened (Department)',
                    'message' => "Ticket #{$ticket->id} has been reopened for your department: {$deptName}.",
                ];
            }

            if ($ticket->customer_id) {
                $recipients[] = [
                    'type'    => 'customer',
                    'id'      => $ticket->customer_id,
                    'title'   => 'Your Ticket is Reopened',
                    'message' => "Your ticket #{$ticket->id} has been reopened. Our team will update you shortly.",
                ];
            }

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
            $this->supportTicketConvRepo->add([
                'support_ticket_id' => $ticket->id,
                'admin_message'     => translate('crm_ticket_reopened_wait_review'),
                'admin_id'          => auth('admin')->id() ?? 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }


        $oldStatusName = $currentStatus?->name ?? 'Unknown';
        $newStatusName = $nextStatus?->name ?? 'Unknown';
        $this->logSupportActivity(
            $ticket->id,
            'Status Updated',
            strtr(translate('status_changed_from_to_reopened'), [
                ':from' => translate(strtolower(trim((string)$oldStatusName))),
                ':to' => translate(strtolower(trim((string)$newStatusName))),
                ':reopened' => translate($isReopened ? 'yes' : 'no'),
            ]),
        );

        return response()->json([
            'message' => translate('status_updated_successfully'),
            'new_status_name' => $nextStatus?->name ?? $currentStatus?->name,
            'reopen_count' => $ticket->reopen_count ?? 0,
        ]);
    }

    public function updatePriority(Request $request): JsonResponse
    {
        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->id]);
        if (!$ticket) {
            return response()->json(['message' => translate('ticket_not_found')], 404);
        }

        $newPriority = strtolower(trim((string)$request->input('priority')));
        $allowedPriorities = ['low', 'medium', 'high', 'urgent', 'critical', 'normal'];

        if (!in_array($newPriority, $allowedPriorities, true)) {
            return response()->json(['message' => translate('invalid_priority')], 422);
        }

        $oldPriority = (string)($ticket->priority ?? 'normal');
        if ($oldPriority === $newPriority) {
            return response()->json([
                'message' => translate('priority_updated_successfully'),
                'new_priority' => $newPriority,
            ]);
        }

        $ticket->update(['priority' => $newPriority]);

        $this->logSupportActivity(
            $ticket->id,
            'Priority Updated',
            "Priority changed from {$oldPriority} to {$newPriority}."
        );

        return response()->json([
            'message' => translate('priority_updated_successfully'),
            'new_priority' => $newPriority,
        ]);
    }


    public function getView($id): View
    {
        $supportTicket = $this->supportTicketRepo->getListWhere(filters: ['id' => $id], relations: ['conversations'], dataLimit: 'all');
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

        $this->logSupportActivity(
            $request['id'],
            'Reply Added',
            "Admin replied: " . substr($request['replay'] ?? '', 0, 255),
        );
        return back();
    }


    public function getDetailsView(int $id): View
    {
        $ticket = $this->supportTicketRepo->getFirstWhere(
            params: ['id' => $id],
            relations: [
                'customer',
                'department',
                'employee',
                'status_details',
                'relatedInboxMessages',
                'supportActivities',
                'escalations.escalatedBy',
            ]
        );

        if (!$ticket) {
            Toastr::error(translate('ticket_not_found'));
            return redirect()->back('');
        }

        // Log view activity (optional)
        $this->logSupportActivity(
            $ticket->id,
            'Ticket Viewed',
            'Admin viewed ticket details.'
        );

        return view(SupportTicket::DETAILS[VIEW], compact('ticket'));
    }


    public function escalate(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|exists:support_tickets,id',
            'reason' => 'required|string|max:1000',
        ]);

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->id]);

        $title   = 'Ticket Escalated';
        $message = "Ticket #{$ticket->id} escalated. Reason: {$request->reason}";
        $link    = route('admin.support-ticket.details', $ticket->id);

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

        $this->logSupportActivity($ticket->id, 'Escalated', $message, auth('admin')->id());

        Toastr::success(translate('Ticket escalated successfully'));
        return back();
    }

    public function escalateRetail(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|exists:support_tickets,id',
            'reason' => 'required|string|max:1000',
        ]);

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->id]);

        $title   = 'Ticket Escalated';
        $message = "Ticket #{$ticket->id} escalated. Reason: {$request->reason}";
        $link    = route('admin.support-ticket.details', $ticket->id);

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

        $this->logSupportActivity($ticket->id, 'Escalated', $message, auth('admin')->id());

        Toastr::success(translate('Ticket escalated successfully'));
        return back();
    }

    public function escalateWholesale(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|exists:support_tickets,id',
            'reason' => 'required|string|max:1000',
        ]);

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->id]);

        $title   = 'Ticket Escalated';
        $message = "Ticket #{$ticket->id} escalated. Reason: {$request->reason}";
        $link    = route('admin.support-ticket.details', $ticket->id);

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

        $this->logSupportActivity($ticket->id, 'Escalated', $message, auth('admin')->id());

        Toastr::success(translate('Ticket escalated successfully'));
        return back();
    }

    private function resolveStatusMasterIdByType(string $type): int
    {
        return match (strtolower($type)) {
            'support' => SupportTicketLifecycle::STATUS_MASTER_ID,
            'service' => ServiceTicketWorkflow::STATUS_MASTER_ID,
            'career' => CareerTicketWorkflow::STATUS_MASTER_ID,
            'complaint' => ComplaintTicketWorkflow::STATUS_MASTER_ID,
            'retail' => RetailTicketWorkflow::STATUS_MASTER_ID,
            'wholesale' => WholesaleTicketWorkflow::STATUS_MASTER_ID,
            default => 0,
        };
    }
}
