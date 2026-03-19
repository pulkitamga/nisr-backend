<?php

namespace App\Enums;

enum TicketDispatchTarget: int
{
    case DepartmentHead = 0;
    case Employee = 1;
    case Customer = 2;
}
