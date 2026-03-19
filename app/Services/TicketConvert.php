<?php

namespace App\Services;

use App\Enums\TicketDispatchTarget;
use App\Models\SupportTicket;
use App\Models\InboxMessage;
use App\Models\SupportTicketStatusMaster;
use App\Models\SupportTicketNotification;
use App\Models\SupportTicketDepartmentEmployee;
use App\Repositories\SupportTicketConvRepository;
use App\Repositories\DepartmentRepository;
use Illuminate\Support\Facades\Auth;

class TicketConvert
{
    public static function fromInboxMessage(
        InboxMessage $message,
        $subType = null,
        $reason = null,
        $departmentId = null,
        $priority = 'normal'
    ): SupportTicket {
        $authUser = Auth::guard('admin')->user();

        $masterIds = [
            'support'   => 1,
            'service'   => 2,
            'career'    => 3,
            'complaint' => 4,
            'retail'    => 5,
            'wholesale' => 6,
        ];

        $type = strtolower($subType) ?? 'support';
        $masterId = $masterIds[$type] ?? 1;

        $resoneType = $reason ?? 'General Inquiry';

        $defaultStatus = SupportTicketStatusMaster::where('master_id', $masterId)
            ->orderBy('position', 'asc')
            ->first();

        $ticket = SupportTicket::create([
            'source_id'      => $message->id,
            'subject'        => $message->subject ?? 'No Subject',
            'description'    => $message->body ?? null,
            'customer_id'    => $message->contact_id ?? null,
            'owner_id'    => $message->owner_id ?? null,
            'department_id'  => $departmentId,
            'employee_id'    => null,
            'priority'       => $priority ?? 'normal',
            'type'           => $type,
            'sub_type'       => $resoneType,
            'status' => $defaultStatus?->id ?? null,
        ]);

        if ($departmentId) {
            $deptEmployeeId = 0;
            $iTicketStatus = $deptEmployeeId != 0 ? 2 : 0;

            SupportTicketDepartmentEmployee::create([
                'ticket_id'    => $ticket->id,
                'department_id' => $departmentId,
                'employee_id'  => $deptEmployeeId,
                'status_id'    => $iTicketStatus,
                'status_type_id' => 0,
                'created_by'   => $authUser ? $authUser->id : 0
            ]);

            if ($deptEmployeeId != 0 && $iTicketStatus == 2) {
                $ticket->update(['status' => 2]);

                SupportTicketNotification::create([
                    'ticket_id'        => $ticket->id,
                    'notification_for' => TicketDispatchTarget::Employee->value,
                    'user_id'          => $deptEmployeeId,
                    'title'            => 'Task Assigned to You',
                    'message'          => 'A new task has been assigned to you. Please review and take necessary action.',
                    'status'           => 0,
                    'is_active'        => 0,
                ]);
            } else {
                $department = app(DepartmentRepository::class)->getFirstWhere(['id' => $departmentId]);
                app(SupportTicketConvRepository::class)->add([
                    'support_ticket_id' => $ticket->id,
                    'admin_message'     => 'Your ticket has been assigned to ' . $department['name'],
                    'admin_id'          => $authUser ? $authUser->id : 0,
                    'created_at'        => now(),
                    'updated_at'        => now()
                ]);
            }
        }

        return $ticket;
    }
}
