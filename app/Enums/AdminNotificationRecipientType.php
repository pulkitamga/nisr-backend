<?php

namespace App\Enums;

enum AdminNotificationRecipientType: int
{
    case Employee = 1;
    case Department = 2;
    case Customer = 3;
}
