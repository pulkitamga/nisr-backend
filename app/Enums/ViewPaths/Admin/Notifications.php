<?php

namespace App\Enums\ViewPaths\Admin;

enum Notifications
{
    const LIST = [
        URI => 'list',
        VIEW => 'admin-views.task-notifications.list'
    ];
    const VIEW = [
        URI => 'view',
        VIEW => 'admin-views.task-notifications.view'
    ];   
    const TICKET_VIEW = [
        URI => 'ticket',
        VIEW => 'admin-views.task-notifications.ticket-view'
    ];
}
