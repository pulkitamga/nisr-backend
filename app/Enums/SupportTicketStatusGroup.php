<?php

namespace App\Enums;

enum SupportTicketStatusGroup: int
{
    case Support = 1;
    case Service = 2;
    case Career = 3;
    case Complaint = 4;
    case Retail = 5;
    case Wholesale = 6;
}
