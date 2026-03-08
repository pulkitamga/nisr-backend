<?php

namespace App\Http\Controllers\Admin\HelpAndSupport;

use Carbon\Carbon;
use App\Contracts\Repositories\SupportTicketConvRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Contracts\Repositories\SupportTicketActivityRepositoryInterface;
use App\Enums\ViewPaths\Admin\SupportTicket;
use App\Enums\ViewPaths\Admin\Complaint;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\SupportTicketRequest;
use App\Repositories\SupportTicketRepository;
use App\Services\SupportTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\SupportTicketDepartmentEmployee;
use App\Models\SupportTicketStatusMaster;
use App\Models\SupportTicketNotification;
use App\Models\CronConfiguration;
use App\Models\CronSenderDetail;
use App\Models\SupportTicketActivity; // Add this import
use App\Services\Crm\EscalationService;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface; // Add this
use Illuminate\Validation\ValidationException;
class ComplaintController extends BaseController
{
    /**
     * @param SupportTicketRepository $supportTicketRepo
     */
    public function __construct(
        private readonly SupportTicketRepositoryInterface $supportTicketRepo,
        private readonly SupportTicketConvRepositoryInterface $supportTicketConvRepo,
        private readonly DepartmentRepositoryInterface $departmentRepo,
        private readonly AdminRepositoryInterface $adminRepo,
        private readonly SupportTicketActivityRepositoryInterface $activityRepo, // Add activity repo
        private readonly AdminNotificationRepositoryInterface   $notificationRepo, // Add this
        private readonly EscalationService                     $escalationService,
    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return \Illuminate\Contracts\View\View Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
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

    private function resolveTicketId(Request $request): ?int
    {
        $rawTicketId = $request->input('ticket_id', $request->input('id', $request->input('support_ticket_id')));
        if ($rawTicketId === null || $rawTicketId === '') {
            return null;
        }

        $ticketId = (int)$rawTicketId;
        return $ticketId > 0 ? $ticketId : null;
    }

    private function isAssignedStatusForMaster(int $statusId, int $masterId): bool
    {
        return SupportTicketStatusMaster::query()
            ->where('id', $statusId)
            ->where('master_id', $masterId)
            ->where('status', 'active')
            ->whereRaw('LOWER(name) = ?', ['assigned'])
            ->exists();
    }

    private function isInProgressStatusForMaster(int $statusId, int $masterId): bool
    {
        $statusName = (string) SupportTicketStatusMaster::query()
            ->where('id', $statusId)
            ->where('master_id', $masterId)
            ->where('status', 'active')
            ->value('name');

        $normalizedStatusName = str_replace(['-', ' '], '_', strtolower(trim($statusName)));

        return in_array($normalizedStatusName, ['in_progress', 'inprogress'], true);
    }

    private function resolveStatusMasterIdByTicketType(?string $ticketType): int
    {
        return match (strtolower(trim((string)$ticketType))) {
            'support' => 1,
            'service' => 2,
            'career' => 3,
            'complaint' => 4,
            'retail' => 5,
            'wholesale' => 6,
            default => 0,
        };
    }

    private function resolveAssignedStatusIdByTicketType(?string $ticketType): int
    {
        $masterId = $this->resolveStatusMasterIdByTicketType($ticketType);
        if ($masterId <= 0) {
            return 0;
        }

        return (int)(SupportTicketStatusMaster::query()
            ->where('master_id', $masterId)
            ->where('status', 'active')
            ->whereRaw('LOWER(name) = ?', ['assigned'])
            ->value('id') ?? 0);
    }

    public function getListView(Request $request): View
    {
        $status = $request->get('status', 36);

        $tickets = $this->supportTicketRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            relations: ['department', 'employee', 'status_details'],
            searchValue: $request->get('searchValue'),
            filters: [
                'priority' => $request['priority'],
                'status' => $status,
                'request_type' => 1
            ],
            dataLimit: getWebConfig('pagination_limit')
        );

        $getDepartment = $this->departmentRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['status' => 1],
            dataLimit: 'all'
        );

        $aInProgressStatus = SupportTicketStatusMaster::where(['master_id' => 4, 'status' => 'active'])->orderBy('position')->get();
        $aAllStatus = SupportTicketStatusMaster::where(['master_id' => 4, 'status' => 'active'])->orderBy('position')->get();
        return view(Complaint::INDEX[VIEW], compact('tickets', 'getDepartment', 'aInProgressStatus', 'aAllStatus'));
    }

    /**
     * Update ticket status and log activity
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $ticket = $this->supportTicketRepo->getFirstWhere(params: ['id' => $request['id']]);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $oldStatus = $ticket['status'];
        $newStatus = (int)$request->input('status', $oldStatus); // Assuming status comes in request
        $status = SupportTicketStatusMaster::find($newStatus);
        $statusName = $status?->name ?? 'Unknown';

        if ($status && strcasecmp($statusName, 'assigned') === 0 && (int)($ticket->employee_id ?? 0) <= 0) {
            return response()->json(['message' => translate('assign_employee_before_setting_assigned_status')], 422);
        }

        $this->supportTicketRepo->update(id: $ticket['id'], data: ['status' => $newStatus]);

        // 🔹 Log activity
        $this->activityRepo->add([
            'support_ticket_id' => $ticket['id'],
            'employee_id' => auth('admin')->id(),
            'title' => 'Status Updated',
            'description' => "Ticket status changed from {$oldStatus} to {$newStatus} ({$statusName})",
            'noted_at' => now(),
        ]);

        return response()->json([
            'message' => translate('status_updated_successfully')
        ], 200);
    }

    public function getView($id): View
    {
        $supportTicket = $this->supportTicketRepo->getListWhere(
            filters: ['id' => $id],
            relations: ['conversations', 'activities', 'escalations.escalatedBy'], // Include activities
            dataLimit: 'all'
        );
        $departments = [];
        return view(Complaint::VIEW[VIEW], compact('supportTicket', 'departments'));
    }

    /**
     * Handle admin reply and log activity
     */
    public function reply(SupportTicketRequest $request, SupportTicketService $supportTicketService): RedirectResponse
    {
        if ($request['image'] == null && $request['replay'] == null) {
            Toastr::warning(translate('type_something') . '!');
            return back();
        }

        $dataArray = $supportTicketService->getAddData(request: $request);
        $ticketId = $dataArray['support_ticket_id'] ?? $request->input('support_ticket_id');

        $this->supportTicketConvRepo->add(data: $dataArray);

        // 🔹 Log activity
        $this->activityRepo->add([
            'support_ticket_id' => $ticketId,
            'employee_id' => auth('admin')->id(),
            'title' => 'Admin Reply Added',
            'description' => "Admin replied to ticket: " . substr($request['replay'] ?? '', 0, 200),
            'noted_at' => now(),
        ]);

        Toastr::success(translate('reply_added_successfully'));
        return back();
    }

    public function getDepartments(Request $request)
    {
        $success = 1;
        $getDepartment = $this->departmentRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            dataLimit: 'all'
        );

        return response()->json([
            'success' => $success,
            'data' => $getDepartment
        ], 200);
    }

    /**
     * Update ticket department/employee assignment and log activity
     */
    public function updateTicketDepartment(Request $request): JsonResponse
    {
        $ticketId = $this->resolveTicketId($request);
        if (!$ticketId) {
            return response()->json([
                'success' => false,
                'message' => translate('Ticket ID is required.'),
            ], 422);
        }

        $departmentId = $request->input('department_id') ?? 0;
        $deptEmployeeId = $request->input('employee_id') ?? 0;
        $success = 1;

        if ($departmentId == 0 || $departmentId == '') {
            $success = 0;
            return response()->json([
                'success' => false,
                'message' => translate('Please_select_at_least_one_department.'),
            ], 400);
        }

        $custRequestTicket = $this->supportTicketRepo->getListWhere(
            filters: ['id' => $ticketId],
            relations: []
        );

        if ($custRequestTicket->isEmpty()) {
            Toastr::error(translate('requested_ticket_details_not_found') . '!');
            return redirect()->route('admin.complaints.index');
        }

        $ticket = $custRequestTicket->first();
        $oldDepartmentId = $ticket->department_id ?? 0;
        $oldEmployeeId = $ticket->employee_id ?? 0;
        $assignedStatusId = $this->resolveAssignedStatusIdByTicketType((string)$ticket->type);
        $historyStatusId = $deptEmployeeId != 0
            ? ($assignedStatusId > 0 ? $assignedStatusId : (int)($ticket->status ?? 0))
            : (int)($ticket->status ?? 0);

        $updateData = ['department_id' => $departmentId, 'employee_id' => $deptEmployeeId];
        $this->supportTicketRepo->update(id: $ticketId, data: $updateData);

        SupportTicketDepartmentEmployee::create([
            'ticket_id' => $ticketId,
            'department_id' => $departmentId,
            'employee_id' => $deptEmployeeId,
            'status_id' => $historyStatusId,
            'status_type_id' => $historyStatusId,
            'created_by' => auth('admin')->check() ? auth('admin')->id() : 0
        ]);

        // 🔹 Log activity
        $description = "Department assigned: ID {$departmentId}";
        if ($oldDepartmentId != $departmentId) {
            $description .= ". Changed from department ID {$oldDepartmentId}";
        }
        if ($deptEmployeeId != 0) {
            $description .= ". Employee assigned: ID {$deptEmployeeId}";
            if ($oldEmployeeId != $deptEmployeeId) {
                $description .= ". Changed from employee ID {$oldEmployeeId}";
            }
            if ($assignedStatusId > 0) {
                $this->supportTicketRepo->update(id: $ticketId, data: ['status' => $assignedStatusId]);
            }
            SupportTicketNotification::create([
                'ticket_id' => $ticketId,
                'notification_for' => 1,
                'user_id' => $deptEmployeeId,
                'title' => 'Task Assigned to You',
                'message' => 'A new task has been assigned to you. Please review and take necessary action.',
                'status' => 0,
                'is_active' => 0,
            ]);
        } else {
            $aDepartmentData = $this->departmentRepo->getFirstWhere(params: ['id' => $departmentId]);
            $description .= ". Department name: " . ($aDepartmentData['name'] ?? 'Unknown');
            $aReplyJourney = [
                'support_ticket_id' => $ticketId,
                'admin_message' => 'Your ticket has been assigned to ' . $aDepartmentData['name'] . ' department.',
                'admin_id' => auth('admin')->check() ? auth('admin')->id() : 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
            $this->supportTicketConvRepo->add(data: $aReplyJourney);
        }

        $this->activityRepo->add([
            'support_ticket_id' => $ticketId,
            'employee_id' => auth('admin')->id(),
            'title' => 'Department/Employee Assignment',
            'description' => $description,
            'noted_at' => now(),
        ]);

        return response()->json([
            'success' => $success,
            'department_id' => $departmentId,
            'employee_id' => $deptEmployeeId,
            'message' => $success ? translate("department_updated_successfully") : translate("department_updated_failed"),
        ], 200);
    }

    public function getDepartmentEmployee(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        $departmentId = $request->input('department_id');
        $success = 1;

        if ($departmentId == 0 || $departmentId == '') {
            $success = 0;
            return response()->json([
                'success' => false,
                'message' => translate('Please_select_at_least_one_department.'),
            ], 400);
        }

        $aDepartmentEmployee = $this->adminRepo->getEmployeeListWhere(
            filters: ['department_id' => $departmentId],
            relations: [],
            dataLimit: 'all'
        );

        return response()->json([
            'success' => $success,
            'employee' => $aDepartmentEmployee,
            'message' => translate("department_employee_list")
        ], 200);
    }

    /**
     * Update ticket follow-up and log activity
     */
    public function updateTicketFollowUp(Request $request)
    {
        $iTicketId = $this->resolveTicketId($request);
        if (!$iTicketId) {
            return response()->json([
                'success' => 0,
                'message' => translate('Ticket ID is required.')
            ], 422);
        }

        $iDepartmentId = $request->input('department_id');
        $iEmployeeId = $request->input('employee_id');
        $success = 1;

        // Validate ticket existence
        $custRequestTicket = $this->supportTicketRepo->getListWhere(filters: ['id' => $iTicketId]);
        if ($custRequestTicket->isEmpty()) {
            return response()->json([
                'success' => 0,
                'message' => translate("Requested ticket details not found, please try again.")
            ], 400);
        }

        $oldTicket = $custRequestTicket->first();
        $iTicketStatus = (int)$request->input('ticket-follow-up-status');
        $dTicketFollowUpDate = $request->input('ticket-next-follow-up-date');
        $iTicketNote = $request->input('ticket-follow-up-note');
        $iTicketRemainderDayAfter = $request->input('ticket-remainder-days-after');
        $iTicketRemainderInterval = $request->input('ticket-remainder-interval');
        $iTicketRemainderCycle = $request->input('ticket-remainder-cycle');

        if (empty($iTicketStatus)) {
            return response()->json([
                'success' => 0,
                'message' => translate("Please select ticket follow-up status.")
            ], 400);
        }

        $statusExists = SupportTicketStatusMaster::where([
            'id' => $iTicketStatus,
            'master_id' => 5, // Retail master_id
            'status' => 'active'
        ])->exists();

        if (!$statusExists) {
            return response()->json([
                'success' => 0,
                'message' => translate("Invalid ticket status for Retail.")
            ], 400);
        }

        if ($this->isAssignedStatusForMaster($iTicketStatus, 5) && (int)($oldTicket->employee_id ?? 0) <= 0) {
            return response()->json([
                'success' => 0,
                'message' => translate('assign_employee_before_setting_assigned_status')
            ], 422);
        }

        if ($iTicketStatus == 46 && empty($dTicketFollowUpDate)) {
            return response()->json([
                'success' => 0,
                'message' => translate("Please select ticket follow-up date for In Progress status.")
            ], 400);
        }

        if ($iTicketStatus == 54 && (empty($iTicketRemainderDayAfter) || empty($iTicketRemainderInterval) || empty($iTicketRemainderCycle))) {
            return response()->json([
                'success' => 0,
                'message' => translate("Please select ticket remainder day, interval, and cycle for Refund Rejected status.")
            ], 400);
        }

        if (empty($iTicketNote)) {
            return response()->json([
                'success' => 0,
                'message' => translate("Please provide ticket follow-up details.")
            ], 400);
        }

        SupportTicketDepartmentEmployee::create([
            'ticket_id' => $iTicketId,
            'department_id' => $iDepartmentId,
            'employee_id' => $iEmployeeId,
            'status_id' => $iTicketStatus,
            'status_type_id' => $iTicketStatus,
            'created_by' => auth('admin')->check() ? auth('admin')->id() : 0
        ]);

        $aTicketUpdate = ['status' => $iTicketStatus];
        if ($iTicketStatus == 46) { // In Progress
            $aTicketUpdate['follow_up_date'] = date('Y-m-d', strtotime($dTicketFollowUpDate));
        }
        $this->supportTicketRepo->update(id: $iTicketId, data: $aTicketUpdate);

        if ($iTicketStatus == 48) {
            $aAutoReply = [
                'support_ticket_id' => $iTicketId,
                'admin_message' => 'The support team has marked this support ticket as closed. All related processes have been completed. If any further assistance is required, the customer may choose to reopen the ticket ',
                'admin_id' => auth('admin')->check() ? auth('admin')->id() : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $this->supportTicketConvRepo->add(data: $aAutoReply);
        }

        // Log ticket conversation
        $aReplyJourney = [
            'support_ticket_id' => $iTicketId,
            'admin_message' => $iTicketNote,
            'admin_id' => auth('admin')->check() ? auth('admin')->id() : 0,
            'created_at' => now(),
            'updated_at' => now()
        ];
        $this->supportTicketConvRepo->add(data: $aReplyJourney);

        // 🔹 Log activity for follow-up update
        $statusName = SupportTicketStatusMaster::find($iTicketStatus)?->name ?? 'Unknown';
        $description = "Follow-up updated - Status: {$statusName} ({$iTicketStatus}), Note: " . substr($iTicketNote, 0, 150);
        if ($dTicketFollowUpDate) {
            $description .= ", Follow-up Date: {$dTicketFollowUpDate}";
        }
        if ($iTicketRemainderDayAfter) {
            $description .= ", Reminder Days: {$iTicketRemainderDayAfter}, Interval: {$iTicketRemainderInterval}h, Cycles: {$iTicketRemainderCycle}";
        }
        if ($oldTicket->status != $iTicketStatus) {
            $description .= ". Status changed from {$oldTicket->status}";
        }

        $this->activityRepo->add([
            'support_ticket_id' => $iTicketId,
            'employee_id' => auth('admin')->id(),
            'title' => 'Ticket Follow-Up Updated',
            'description' => $description,
            'noted_at' => now(),
        ]);

        // Fetch cron configurations for the selected status_id
        $aGetCronConfig = CronConfiguration::where(['ticket_status_id' => $iTicketStatus, 'status' => 'active'])->get();
        $custRequestTicket = $this->supportTicketRepo->getFirstWhere(
            params: ['id' => $iTicketId],
            relations: ['department'],
        );

        $aTicketCronData = [];
        foreach ($aGetCronConfig as $config) {
            $dScheduleDate = null;
            $sTitle = null;
            $sMessage = null;

            switch ($iTicketStatus) {
                case 44: // Open
                    $sTitle = "Pending Task: Action Required";
                    $sMessage = "A task assigned to you has not been responded to. Please take action or escalate as needed.";
                    $dScheduleDate = Carbon::now()->addHours($config['duration'])->toDateTimeString();

                    $aTicketCronData[] = [
                        'ticket_id' => $iTicketId,
                        'send_for' => 0, // Dept Head
                        'sender_id' => $custRequestTicket->department->head_id,
                        'title' => $sTitle,
                        'message' => $sMessage,
                        'send_date' => $dScheduleDate,
                        'ticket_status' => $iTicketStatus,
                        'status' => 0,
                        'is_active' => 0
                    ];

                    $aTicketCronData[] = [
                        'ticket_id' => $iTicketId,
                        'send_for' => 1, // Dept Emp
                        'sender_id' => $custRequestTicket->employee_id,
                        'title' => $sTitle,
                        'message' => $sMessage,
                        'send_date' => $dScheduleDate,
                        'ticket_status' => $iTicketStatus,
                        'status' => 0,
                        'is_active' => 0
                    ];
                    break;

                case 46: // In Progress
                    $dFollowUpDate = Carbon::parse($dTicketFollowUpDate);
                    if ($config['type'] == 'after') {
                        $sTitle = "Follow-up Reminder: Task Pending";
                        $sMessage = "The assigned task requires your response. Please review and follow up within the set time.";
                        $dScheduleDate = $dFollowUpDate->copy()->addHours($config['duration'])->toDateTimeString();
                    } elseif ($config['type'] == 'before') {
                        $sTitle = "Follow-up Missed: Immediate Action Needed";
                        $sMessage = "A scheduled follow-up was missed. Please take action immediately to avoid further delay.";
                        $dScheduleDate = $dFollowUpDate->copy()->subHours($config['duration'])->toDateTimeString();
                    }

                    $aTicketCronData[] = [
                        'ticket_id' => $iTicketId,
                        'send_for' => 0, // Dept Head
                        'sender_id' => $custRequestTicket->department->head_id,
                        'title' => $sTitle,
                        'message' => $sMessage,
                        'send_date' => $dScheduleDate,
                        'ticket_status' => $iTicketStatus,
                        'status' => 0,
                        'is_active' => 0
                    ];

                    $aTicketCronData[] = [
                        'ticket_id' => $iTicketId,
                        'send_for' => 1, // Dept Emp
                        'sender_id' => $custRequestTicket->employee_id,
                        'title' => $sTitle,
                        'message' => $sMessage,
                        'send_date' => $dScheduleDate,
                        'ticket_status' => $iTicketStatus,
                        'status' => 0,
                        'is_active' => 0
                    ];
                    break;

                case 51:
                    $sTitle = "Your Response is Needed";
                    $sMessage = "We are waiting for your response regarding your RMA request. Please provide an update at the earliest.";
                    $dScheduleDate = Carbon::now()->addHours($config['duration'])->toDateTimeString();

                    $aTicketCronData[] = [
                        'ticket_id' => $iTicketId,
                        'send_for' => 2, // Customer
                        'sender_id' => $custRequestTicket->customer_id,
                        'title' => $sTitle,
                        'message' => $sMessage,
                        'send_date' => $dScheduleDate,
                        'ticket_status' => $iTicketStatus,
                        'status' => 0,
                        'is_active' => 0
                    ];
                    break;

                case 52:
                    $sTitle = "Task Completed: Please Verify";
                    $sMessage = "The department has marked your RMA request as received. Please verify and close the ticket if resolved.";
                    $dScheduleDate = Carbon::now()->addHours($config['duration'])->toDateTimeString();

                    $aTicketCronData[] = [
                        'ticket_id' => $iTicketId,
                        'send_for' => 2,
                        'sender_id' => $custRequestTicket->customer_id,
                        'title' => $sTitle,
                        'message' => $sMessage,
                        'send_date' => $dScheduleDate,
                        'ticket_status' => $iTicketStatus,
                        'status' => 0,
                        'is_active' => 0
                    ];

                    SupportTicketNotification::create([
                        'ticket_id' => $iTicketId,
                        'notification_for' => 2, // Customer
                        'user_id' => 0,
                        'customer_id' => $custRequestTicket->customer_id,
                        'title' => $sTitle,
                        'message' => $sMessage,
                        'status' => 0,
                        'is_active' => 0
                    ]);
                    break;

                case 54:
                    $dRemainderDate = Carbon::now()->addDays($iTicketRemainderDayAfter)->toDateTimeString();
                    $dFollowUpDate = Carbon::parse($dRemainderDate);
                    $iHours = 0;
                    for ($i = 0; $i < $iTicketRemainderCycle; $i++) {
                        $iHours = $i * $iTicketRemainderInterval;
                        $dScheduleDate = $dFollowUpDate->copy()->addHours($iHours)->toDateTimeString();
                        $aTicketCronData[] = [
                            'ticket_id' => $iTicketId,
                            'send_for' => 1,
                            'sender_id' => $custRequestTicket->employee_id,
                            'title' => "Reminder: Refund Rejected Task on Hold",
                            'message' => "Reminder: The refund request (Ticket ID: {$iTicketId}) is on hold due to rejection. Please take necessary steps or escalate if needed.",
                            'send_date' => $dScheduleDate,
                            'ticket_status' => $iTicketStatus,
                            'status' => 0,
                            'is_active' => 0
                        ];
                    }
                    SupportTicketNotification::create([
                        'ticket_id' => $iTicketId,
                        'notification_for' => 2, // Customer
                        'user_id' => 0,
                        'customer_id' => $custRequestTicket->customer_id,
                        'title' => "Reminder: Refund Request Rejected",
                        'message' => "Dear Customer, your refund request (Ticket ID: {$iTicketId}) has been rejected. Please review the details or contact our support team for further assistance.",
                        'status' => 0,
                        'is_active' => 0
                    ]);
                    break;

                default:
                    break;
            }
        }

        if (!empty($aTicketCronData)) {
            CronSenderDetail::insert($aTicketCronData);
        }

        return response()->json([
            'success' => $success,
            'message' => translate("Successfully updated ticket follow-up details.")
        ], 200);
    }

    /**
     * Update support ticket follow-up and log activity
     */
    public function updateSupportTicketFollowUp(Request $request)
    {
        $ticketId = $this->resolveTicketId($request);
        if (!$ticketId) {
            return response()->json([
                'success' => 0,
                'message' => translate('Ticket ID is required.')
            ], 422);
        }

        $departmentId = $request->input('department_id');
        $employeeId = $request->input('employee_id');
        $statusId = (int)$request->input('ticket-follow-up-status');
        $followUpDate = $request->input('ticket-next-follow-up-date');
        $note = $request->input('ticket-follow-up-note');

        $ticket = $this->supportTicketRepo->getListWhere(filters: ['id' => $ticketId]);
        if ($ticket->isEmpty()) {
            return response()->json(['success' => 0, 'message' => translate('ticket_not_found')], 404);
        }

        $oldTicket = $ticket->first();
        $oldStatusName = SupportTicketStatusMaster::find((int)$oldTicket->status)?->name ?? (string)$oldTicket->status;

        if (!SupportTicketStatusMaster::where(['id' => $statusId, 'master_id' => 1, 'status' => 'active'])->exists()) {
            return response()->json(['success' => 0, 'message' => translate('invalid_status')], 422);
        }

        if ($this->isAssignedStatusForMaster($statusId, 1) && (int)($oldTicket->employee_id ?? 0) <= 0) {
            return response()->json([
                'success' => 0,
                'message' => translate('assign_employee_before_setting_assigned_status')
            ], 422);
        }

        $isInProgressStatus = $this->isInProgressStatusForMaster($statusId, 1);

        if (empty($note)) {
            return response()->json(['success' => 0, 'message' => translate('note_required')], 422);
        }

        if ($isInProgressStatus && empty($followUpDate)) {
            return response()->json(['success' => 0, 'message' => translate('follow_up_date_required_for_in_progress')], 422);
        }

        SupportTicketDepartmentEmployee::create([
            'ticket_id' => $ticketId,
            'department_id' => $departmentId,
            'employee_id' => $employeeId,
            'status_id' => $statusId,
            'status_type_id' => $statusId,
            'created_by' => auth('admin')->id()
        ]);

        $updateData = [
            'status' => $statusId,
            // Keep follow-up date only while ticket is In Progress.
            'follow_up_date' => $isInProgressStatus ? date('Y-m-d', strtotime($followUpDate)) : null,
        ];
        $this->supportTicketRepo->update(id: $ticketId, data: $updateData);

        $this->supportTicketConvRepo->add([
            'support_ticket_id' => $ticketId,
            'admin_message' => $note,
            'admin_id' => auth('admin')->id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $statusName = SupportTicketStatusMaster::find($statusId)?->name ?? 'Unknown';
        $description = "Support follow-up - Status: {$statusName} ({$statusId}), Note: " . substr($note, 0, 150);

        if ($isInProgressStatus && $followUpDate) {
            $description .= ", Follow-up Date: {$followUpDate}";
        }
        if ($oldTicket->status != $statusId) {
            $description .= ". Status changed from {$oldStatusName}";
        }

        $this->activityRepo->add([
            'support_ticket_id' => $ticketId,
            'employee_id' => auth('admin')->id(),
            'title' => 'Support Ticket Follow-Up',
            'description' => $description,
            'noted_at' => now(),
        ]);

        $title = "Support Ticket Updated";
        $message = "Support Ticket #{$ticketId} updated. Status changed & follow-up added.";
        $link = route('admin.support-ticket.details', $ticketId);

        $recipients = [];
        if ($employeeId) {
            $recipients[] = ['type' => 'employee', 'id' => $employeeId];
        }
        if ($departmentId) {
            $recipients[] = ['type' => 'department', 'id' => $departmentId];
        }

        if (!empty($recipients)) {
            $this->notificationRepo->notifyRecipients(
                $ticketId,
                \App\Models\SupportTicket::class,
                $title,
                $message,
                $link,
                $recipients
            );
        }

        $cronData = [];
        $cronConfigs = CronConfiguration::where(['ticket_status_id' => $statusId, 'status' => 'active'])->get();

        foreach ($cronConfigs as $config) {
            if ($isInProgressStatus) {
                $cronData[] = [
                    'ticket_id' => $ticketId,
                    'send_for' => 1,
                    'sender_id' => $employeeId ?? 0,
                    'title' => 'Follow-up Reminder',
                    'message' => 'Please follow up on the support ticket.',
                    'send_date' => Carbon::parse($followUpDate)->copy()->addHours($config['duration']),
                    'ticket_status' => $statusId,
                    'status' => 0,
                    'is_active' => 0,
                ];
            }
        }

        if (!empty($cronData)) {
            CronSenderDetail::insert($cronData);
        }

        return response()->json(['success' => 1, 'message' => translate('support_ticket_follow_up_updated')]);
    }

   public function updateComplainTicketFollowUp(Request $request)
{
    $ticketId     = $this->resolveTicketId($request);
    if (!$ticketId) {
        return response()->json(['success' => 0, 'message' => translate('Ticket ID is required.')], 422);
    }

    $departmentId = $request->input('department_id');
    $employeeId   = $request->input('employee_id');
    $statusId     = $request->input('ticket-follow-up-status');
    $followUpDate = $request->input('ticket-next-follow-up-date');
    $note         = $request->input('ticket-follow-up-note');

    $complaintMasterId = 4;

    $ticket = $this->supportTicketRepo->getListWhere(filters: ['id' => $ticketId]);
    if ($ticket->isEmpty()) {
        return response()->json(['success' => 0, 'message' => 'Complaint not found'], 400);
    }

    $oldTicket = $ticket->first();

    // Validate status
    if (!SupportTicketStatusMaster::where([
        'id' => $statusId, 
        'master_id' => $complaintMasterId, 
        'status' => 'active'
    ])->exists()) 
    {
        return response()->json(['success' => 0, 'message' => 'Invalid complaint status'], 400);
    }

    if ($this->isAssignedStatusForMaster((int)$statusId, $complaintMasterId) && (int)($oldTicket->employee_id ?? 0) <= 0) {
        return response()->json([
            'success' => 0,
            'message' => translate('assign_employee_before_setting_assigned_status')
        ], 422);
    }

    if (empty($note)) {
        return response()->json(['success' => 0, 'message' => 'Note required'], 400);
    }

    if ($statusId == 39 && empty($followUpDate)) {
        return response()->json(['success' => 0, 'message' => 'Follow-up date required for In Progress'], 400);
    }

    // Save DE Status History
    SupportTicketDepartmentEmployee::create([
        'ticket_id'     => $ticketId,
        'department_id' => $departmentId,
        'employee_id'   => $employeeId,
        'status_id'     => $statusId,
        'status_type_id'=> $statusId,
        'created_by'    => auth('admin')->id(),
    ]);

    // Update Ticket
    $updateData = ['status' => $statusId];
    if ($statusId == 39) {
        $updateData['follow_up_date'] = date('Y-m-d', strtotime($followUpDate));
    }

    $this->supportTicketRepo->update(id: $ticketId, data: $updateData);

    // Conversation
    $this->supportTicketConvRepo->add([
        'support_ticket_id' => $ticketId,
        'admin_message'     => $note,
        'admin_id'          => auth('admin')->id(),
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);


    // =====================================================================
    // 🔥 NOTIFICATION SYSTEM (Employee + Department + Customer)
    // =====================================================================

    $title = "Complaint Status Updated";
    $statusName = SupportTicketStatusMaster::find($statusId)?->name ?? 'Status Updated';
    $message = "Complaint Ticket #{$ticketId} updated to '{$statusName}'. Note: {$note}";
    $link = route('admin.support-ticket.details', $ticketId);

    $recipients = [];

    // Send to Employee
    if ($employeeId) {
        $recipients[] = ['type' => 'employee', 'id' => $employeeId];
    }

    // Send to Department
    if ($departmentId) {
        $recipients[] = ['type' => 'department', 'id' => $departmentId];
    }

    // Send to Customer on specific statuses
    if (in_array($statusId, [41, 42])) { // Resolved / Closed
        $recipients[] = ['type' => 'customer', 'id' => $oldTicket->customer_id];
    }

    // Notify if recipients exist
    if (!empty($recipients)) {
        $this->notificationRepo->notifyRecipients(
            $ticketId,
            \App\Models\SupportTicket::class,
            $title,
            $message,
            $link,
            $recipients
        );
    }


    // =====================================================================
    // 🔥 CRON JOB SYSTEM (AS IT IS — NOT REMOVED)
    // =====================================================================

    $cronData = [];
    $cronConfigs = CronConfiguration::where([
        'ticket_status_id' => $statusId,
        'status' => 'active'
    ])->get();

    foreach ($cronConfigs as $config) {
        switch ($statusId) {
            case 37: // Open
            case 38: // Assigned
                $cronData[] = [
                    'ticket_id'     => $ticketId,
                    'send_for'      => 0,
                    'sender_id'     => $oldTicket->department->head_id ?? 0,
                    'title'         => 'Action Required',
                    'message'       => 'A complaint requires your attention.',
                    'send_date'     => now()->addHours($config['duration']),
                    'ticket_status' => $statusId,
                    'status'        => 0,
                    'is_active'     => 0,
                ];
                $cronData[] = [
                    'ticket_id'     => $ticketId,
                    'send_for'      => 1,
                    'sender_id'     => $oldTicket->employee_id ?? 0,
                    'title'         => 'Action Required',
                    'message'       => 'A complaint has been assigned to you.',
                    'send_date'     => now()->addHours($config['duration']),
                    'ticket_status' => $statusId,
                    'status'        => 0,
                    'is_active'     => 0,
                ];
                break;

            case 39: // In Progress
                $cronData[] = [
                    'ticket_id'     => $ticketId,
                    'send_for'      => 1,
                    'sender_id'     => $oldTicket->employee_id ?? 0,
                    'title'         => 'Follow-up Reminder',
                    'message'       => 'Please follow up on the complaint ticket.',
                    'send_date'     => Carbon::parse($followUpDate)->addHours($config['duration']),
                    'ticket_status' => $statusId,
                    'status'        => 0,
                    'is_active'     => 0,
                ];
                break;
        }
    }

    if (!empty($cronData)) {
        CronSenderDetail::insert($cronData);
    }

    return response()->json(['success' => 1, 'message' => 'Follow-up updated successfully']);
}


    /**
     * Update wholesale follow-up and log activity
     */
   public function updateWholesaleFollowUp(Request $request)
{
    $ticketId      = $this->resolveTicketId($request);
    if (!$ticketId) {
        return response()->json(['success' => 0, 'message' => translate('Ticket ID is required.')], 422);
    }

    $departmentId  = $request->input('department_id');
    $employeeId    = $request->input('employee_id');
    $statusId      = $request->input('ticket-follow-up-status');
    $followUpDate  = $request->input('ticket-next-follow-up-date');
    $note          = $request->input('ticket-follow-up-note');

    // Wholesale Ticket Master
    $wholesaleMasterId = 6;

    $ticket = $this->supportTicketRepo->getListWhere(filters: ['id' => $ticketId]);
    if ($ticket->isEmpty()) {
        return response()->json(['success' => 0, 'message' => 'Wholesale ticket not found'], 400);
    }

    $oldTicket = $ticket->first();

    // Validate status
    if (!SupportTicketStatusMaster::where([
        'id'        => $statusId,
        'master_id' => $wholesaleMasterId,
        'status'    => 'active'
    ])->exists()) {
        return response()->json(['success' => 0, 'message' => 'Invalid wholesale status'], 400);
    }

    if ($this->isAssignedStatusForMaster((int)$statusId, $wholesaleMasterId) && (int)($oldTicket->employee_id ?? 0) <= 0) {
        return response()->json([
            'success' => 0,
            'message' => translate('assign_employee_before_setting_assigned_status')
        ], 422);
    }

    if (empty($note)) {
        return response()->json(['success' => 0, 'message' => 'Note required'], 400);
    }

    // Follow-up requirement
    if ($statusId == 59 && empty($followUpDate)) {
        return response()->json(['success' => 0, 'message' => 'Follow-up date required for In Progress'], 400);
    }

    // Save dept/emp history
    SupportTicketDepartmentEmployee::create([
        'ticket_id'      => $ticketId,
        'department_id'  => $departmentId,
        'employee_id'    => $employeeId,
        'status_id'      => $statusId,
        'status_type_id' => $statusId,
        'created_by'     => auth('admin')->id(),
    ]);

    // Update ticket
    $updateData = ['status' => $statusId];
    if ($statusId == 59) {
        $updateData['follow_up_date'] = date('Y-m-d', strtotime($followUpDate));
    }
    $this->supportTicketRepo->update(id: $ticketId, data: $updateData);

    // Add conversation entry
    $this->supportTicketConvRepo->add([
        'support_ticket_id' => $ticketId,
        'admin_message'     => $note,
        'admin_id'          => auth('admin')->id(),
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);


    /* ---------------------------------------------------
     *  NEW — Notification System (same as escalated)
     * --------------------------------------------------- */

    $title   = "Wholesale Ticket Updated";
    $message = "Wholesale Ticket #{$ticketId} updated. Status changed & follow-up added.";
    $link    = route('admin.support-ticket.details', $ticketId);

    $recipients = [];

    if ($employeeId) {
        $recipients[] = ['type' => 'employee', 'id' => $employeeId];
    }

    if ($departmentId) {
        $recipients[] = ['type' => 'department', 'id' => $departmentId];
    }

    // Also customer notification when status = resolved/closed
    if (in_array($statusId, [61, 62])) {
        $recipients[] = ['type' => 'customer', 'id' => $oldTicket->customer_id];
    }

    if ($recipients) {
        $this->notificationRepo->notifyRecipients(
            $ticketId,
            \App\Models\SupportTicket::class,
            $title,
            $message,
            $link,
            $recipients
        );
    }



    $cronData = [];
    $cronConfigs = CronConfiguration::where([
        'ticket_status_id' => $statusId,
        'status'           => 'active'
    ])->get();

    foreach ($cronConfigs as $config) {

        switch ($statusId) {
            case 57: // Open
            case 58: // Assigned

                // To Department Head
                $cronData[] = [
                    'ticket_id'     => $ticketId,
                    'send_for'      => 0,
                    'sender_id'     => $oldTicket->department->head_id ?? 0,
                    'title'         => 'Wholesale Action Required',
                    'message'       => 'A wholesale ticket requires your attention.',
                    'send_date'     => now()->addHours($config['duration']),
                    'ticket_status' => $statusId,
                    'status'        => 0,
                    'is_active'     => 0,
                ];

                // To Employee
                $cronData[] = [
                    'ticket_id'     => $ticketId,
                    'send_for'      => 1,
                    'sender_id'     => $oldTicket->employee_id ?? 0,
                    'title'         => 'Wholesale Ticket Assigned',
                    'message'       => 'A wholesale ticket has been assigned to you.',
                    'send_date'     => now()->addHours($config['duration']),
                    'ticket_status' => $statusId,
                    'status'        => 0,
                    'is_active'     => 0,
                ];
                break;

            case 59: // In Progress  
                $cronData[] = [
                    'ticket_id'     => $ticketId,
                    'send_for'      => 1,
                    'sender_id'     => $oldTicket->employee_id ?? 0,
                    'title'         => 'Wholesale Follow-up Reminder',
                    'message'       => 'Please follow up on this wholesale ticket.',
                    'send_date'     => Carbon::parse($followUpDate)->copy()->addHours($config['duration']),
                    'ticket_status' => $statusId,
                    'status'        => 0,
                    'is_active'     => 0,
                ];
                break;

            case 61: // Resolved
            case 62: // Closed

                $recipients = [
                    ['type' => 'customer', 'id' => $oldTicket->customer_id]
                ];

                $title = $statusId == 61 ? 'Wholesale Ticket Resolved' : 'Wholesale Ticket Closed';
                $message = $statusId == 61 ? 'Your wholesale ticket has been resolved.' : 'Your wholesale ticket has been closed.';
                $link = route('admin.support-ticket.details', $ticketId);

                $this->notificationRepo->notifyRecipients(
                    $ticketId,
                    \App\Models\SupportTicket::class,
                    $title,
                    $message,
                    $link,
                    $recipients
                );
                break;
        }
    }

    if (!empty($cronData)) {
        CronSenderDetail::insert($cronData);
    }


    return response()->json(['success' => 1, 'message' => 'Wholesale follow-up updated']);
}


    public function escalate(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|exists:support_tickets,id',
            'reason' => 'required|string|max:1000',
        ]);

        $ticket = $this->supportTicketRepo->getFirstWhere(['id' => $request->id]);

        $title   = 'Ticket Escalated';
        $message = "Complaint Ticket #{$ticket->id} escalated. Reason: {$request->reason}";
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

        $this->activityRepo->add([
            'support_ticket_id' => $ticket->id,
            'employee_id' => auth('admin')->id(),
            'title' => 'Escalated',
            'description' => $message,
            'noted_at' => now(),
        ]);

        Toastr::success(translate('Ticket escalated successfully'));
        return back();
    }
}
