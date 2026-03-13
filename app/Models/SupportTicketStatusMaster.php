<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicketStatusMaster extends Model
{
    use HasFactory;

    protected $table = 'support_ticket_status_master';

    protected $fillable = [
        'name',
        'master_id',
        'status',
    ];
}