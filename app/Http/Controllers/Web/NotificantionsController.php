<?php

namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use Illuminate\Contracts\View\View;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;
use Illuminate\Support\Facades\Redirect;

class NotificantionsController extends Controller 
{
    use PaginatorTrait;
    use CommonTrait;

    public function __construct(
        private readonly AdminNotificationRepositoryInterface $notificationsRepo, 
    ) {}

    public function getView(string|int $id): View
    {
        if (!$id) {
            return Redirect::route('notifications.index')->with('error', 'Notification ID not found.');
        }
        // view-notification
        $SupportnotificationData = $this->notificationsRepo->find($id);
        return view(VIEW_FILE_NAMES['view_notification'], compact('SupportnotificationData'));
    }
}
