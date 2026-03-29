<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicketStatusMaster extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'support_ticket_status_master';

    protected $fillable = [
        'name',
        'master_id',
        'status',
    ];
}