<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationSeen;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // public function list(Request $request)
    // {

    //     $notification_data = Notification::active()->where(['sent_to'=>'customer']);

    //     $notification = $notification_data->with('notificationSeenBy')
    //         ->latest()->paginate($request['limit'], ['*'], 'page', $request['offset']);

    //     return [
    //         'total_size' => $notification->total(),
    //         'limit' => (int)$request['limit'],
    //         'offset' => (int)$request['offset'],
    //         'new_notification' => $notification_data->whereDoesntHave('notificationSeenBy')->count(),
    //         'notification' => $notification->items()
    //     ];
    // }


    public function list(Request $request)
    {
        $userId = auth('api')->id(); // null if not logged in

        $query = Notification::active()
            ->where('sent_to', 'customer')
            ->with(['notificationSeenBy' => function ($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                }
            }])
            ->latest();

        $notification = $query->paginate(
            $request->get('limit', 10),
            ['*'],
            'page',
            $request->get('offset', 1)
        );

        $newNotificationCount = $userId
            ? Notification::active()
            ->where('sent_to', 'customer')
            ->whereDoesntHave('notificationSeenBy', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->count()
            : 0; // guest has no seen logic

        return [
            'total_size' => $notification->total(),
            'limit' => (int) $request->get('limit', 10),
            'offset' => (int) $request->get('offset', 1),
            'new_notification' => $newNotificationCount,
            'notification' => $notification->items(),
        ];
    }


    public function notification_seen(Request $request)
    {
        $user = $request->user();
        // NotificationSeen::updateOrInsert(['user_id' => $user->id, 'notification_id' => $request->id], [
        //     'created_at' => Carbon::now(),
        // ]);
        NotificationSeen::updateOrInsert(
            [
                'user_id' => $user->id,
                'notification_id' => $request->id
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $notification_count = Notification::active()
            ->where('sent_to', 'customer')
            ->whereDoesntHave('notificationSeenBy')
            ->count();

        return [
            'notification_count' => $notification_count,
        ];
    }
}
