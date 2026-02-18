<?php

namespace App\Http\Controllers\Admin\HelpAndSupport;

use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
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
use App\Models\Escalation;
use App\Models\Departments;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface; // Add this
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
            'support'   => 1,
            'service'   => 20,
            'career'    => 26,
            'complaint' => 36,
            'retail'    => 43,
            'wholesale' => 56,
        ];
        $statusFilter = $request->get('status', $defaultStatusIds[$status] ?? null);
        $tickets = $this->supportTicketRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            relations: ['department', 'employee', 'status_details', 'relatedInboxMessages'],
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
            'support'   => 1,
            'service'   => 2,
            'career'    => 3,
            'complaint' => 4,
            'retail'    => 5,
            'wholesale' => 6,
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
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $currentStatus = SupportTicketStatusMaster::find($ticket->status);

        // Default next status
        $nextStatusName = 'Open';

        if ($currentStatus) {
            $currentName = strtolower($currentStatus->name);

            // 🔹 Status flow mapping (clear readable logic)
            $statusFlow = [
                'new' => 'open',
                'open' => 'closed',
                'closed' => 'open', // Reopen leads to Open
            ];

            $nextStatusName = $statusFlow[$currentName] ?? 'closed';
        }

        // 🔹 Fetch next status record
        $nextStatus = SupportTicketStatusMaster::where('master_id', $currentStatus->master_id ?? null)
            ->whereRaw('LOWER(name) = ?', [strtolower($nextStatusName)])
            ->first();

        // 🔹 Handle reopen logic
        $isReopened = strtolower($currentStatus->name ?? '') === 'closed';

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
                'admin_message'     => 'Your ticket has been reopened. Please wait while we review.',
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
            "Status changed from {$oldStatusName} to {$newStatusName}. Reopened: " . ($isReopened ? 'Yes' : 'No'),
        );

        return response()->json([
            'message' => translate('status_updated_successfully'),
            'new_status_name' => $nextStatus?->name ?? $currentStatus?->name,
            'reopen_count' => $ticket->reopen_count ?? 0,
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

        Escalation::create([
            'escalatable_id' => $ticket->id,
            'escalatable_type' => SupportTicket::class,
            'escalated_by' => auth('admin')->id(),
            'reason' => $request->reason,
        ]);

        $title   = 'Ticket Escalated';
        $message = "Ticket #{$ticket->id} escalated. Reason: {$request->reason}";
        $link    = route('admin.support-ticket.details', $ticket->id);

        $recipients = [];
        if ($ticket->employee_id) { // Assuming employee_id is like owner_id
            $recipients[] = ['type' => 'employee', 'id' => $ticket->employee_id];
        }
        if ($ticket->department_id) {
            $recipients[] = ['type' => 'department', 'id' => $ticket->department_id];
        }

        if ($recipients) {
            $this->notificationRepo->notifyRecipients(
                $ticket->id,
                SupportTicket::class,
                $title,
                $message,
                $link,
                $recipients
            );
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

        Escalation::create([
            'escalatable_id' => $ticket->id,
            'escalatable_type' => SupportTicket::class,
            'escalated_by' => auth('admin')->id(),
            'reason' => $request->reason,
        ]);

        $title   = 'Ticket Escalated';
        $message = "Ticket #{$ticket->id} escalated. Reason: {$request->reason}";
        $link    = route('admin.support-ticket.details', $ticket->id);

        $recipients = [];
        if ($ticket->employee_id) { // Assuming employee_id is like owner_id
            $recipients[] = ['type' => 'employee', 'id' => $ticket->employee_id];
        }
        if ($ticket->department_id) {
            $recipients[] = ['type' => 'department', 'id' => $ticket->department_id];
        }

        if ($recipients) {
            $this->notificationRepo->notifyRecipients(
                $ticket->id,
                SupportTicket::class,
                $title,
                $message,
                $link,
                $recipients
            );
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

        Escalation::create([
            'escalatable_id' => $ticket->id,
            'escalatable_type' => SupportTicket::class,
            'escalated_by' => auth('admin')->id(),
            'reason' => $request->reason,
        ]);

        $title   = 'Ticket Escalated';
        $message = "Ticket #{$ticket->id} escalated. Reason: {$request->reason}";
        $link    = route('admin.support-ticket.details', $ticket->id);

        $recipients = [];
        if ($ticket->employee_id) { // Assuming employee_id is like owner_id
            $recipients[] = ['type' => 'employee', 'id' => $ticket->employee_id];
        }
        if ($ticket->department_id) {
            $recipients[] = ['type' => 'department', 'id' => $ticket->department_id];
        }

        if ($recipients) {
            $this->notificationRepo->notifyRecipients(
                $ticket->id,
                SupportTicket::class,
                $title,
                $message,
                $link,
                $recipients
            );
        }

        $this->logSupportActivity($ticket->id, 'Escalated', $message, auth('admin')->id());

        Toastr::success(translate('Ticket escalated successfully'));
        return back();
    }
}
