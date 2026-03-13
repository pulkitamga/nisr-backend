<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ViewPaths\Admin\Notifications;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Contracts\Repositories\SupportTicketRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskNotificationsController extends BaseController
{
    use PaginatorTrait;
    use CommonTrait;

    public function __construct(
        private readonly AdminNotificationRepositoryInterface  $notifRepo,
        private readonly SupportTicketRepositoryInterface $supportTicketRepo
    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

  public function getNotifView(Request $request): View
    {
        $notifications = $this->notifRepo->getForEmployee(auth('admin')->id(), [0, 1], 10);
        return view(Notifications::DROPDOWN[VIEW], compact('notifications'));
    }

    /** Full list (paginated) */
    public function list(Request $request): View
    {

        $paginate = getWebConfig('pagination_limit');
        $notifications = $this->notifRepo->getAllForEmployee(
            auth('admin')->id(),
            $request->all(),
            $paginate
        );
        return view(Notifications::LIST[VIEW], compact('notifications'));
    }

    public function view(string $id): RedirectResponse
    {
        $notif = $this->notifRepo->markAsRead($id);
        return redirect($notif->link ?? route('admin.dashboard.index'));
    }

    public function show(string $id): View
    {
        $notif = $this->notifRepo->find($id);
        return view(Notifications::VIEW[VIEW], compact('notif'));
    }

    /* ------------------------------------------------------------------
     *  Example: fire a notification from anywhere (service, job, etc.)
     * -----------------------------------------------------------------*/
    /**
     *  Usage:
     *  app(TaskNotificationsController::class)->fireSupportTicketNotification($ticket);
     */
    public function fireSupportTicketNotification($ticket)
    {
        $recipients = [
            ['type' => 'employee',   'id' => $ticket->assigned_admin_id],
            ['type' => 'department', 'id' => $ticket->department_id],
            ['type' => 'customer',   'id' => $ticket->customer_id],
        ];

        $this->notifRepo->notifyRecipients(
            $ticket->id,
            get_class($ticket),
            'New support ticket #' . $ticket->code,
            'Subject: ' . $ticket->subject,
            route('admin.support-ticket.show', $ticket->id),
            $recipients
        );
    }
}
