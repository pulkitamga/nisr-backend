<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string $user_type
 * @property string $key
 * @property string $message
 * @property integer $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 **/
class NotificationMessage extends Model
{
    use HasFactory, HasTranslations;
    protected $fillable = [
        'user_type',
        'key',
        'message',
        'status',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'user_type' => 'string',
        'key' => 'string',
        'message' => 'string',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
