<?php

namespace App\Utils;

use App\Traits\PdfGenerator;
use App\Models\SupportTicketNotification;
use App\Models\LeadNotification;
use App\Traits\CommonTrait;

class Notifications
{
    use CommonTrait, PdfGenerator;
    public static function getNotifications($userId, $statuses = [0])
    {
        return app(\App\Contracts\Repositories\AdminNotificationRepositoryInterface::class)
            ->getForEmployee($userId, $statuses);
    }
    public static function getUserNotifications($userId)
    {
        return app(\App\Contracts\Repositories\AdminNotificationRepositoryInterface::class)
            ->getForUser($userId);
    }
    public static function getDepartmentNotifications($userId, $statuses = [0])
    {
        return app(\App\Contracts\Repositories\AdminNotificationRepositoryInterface::class)
            ->getForEmployee($userId, $statuses);
    }
    public static function notify($userId, $title, $message, $type = 'general', $relatedId = null, $fromUserId = null)
    {
        return LeadNotification::create([
            'user_id' => $userId,
            'from_user_id' => $fromUserId ?? auth('admin')->id(),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'related_id' => $relatedId,
        ]);
    }
}
