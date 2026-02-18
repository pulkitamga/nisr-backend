<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * Class Area
 *
 * @property int $id Primary
 * @property string $area
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class DeliveryState extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'state_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'state_id',
    ];

    public function state():BelongsTo
    {
        return $this->belongsTo(State::class,'state_id');
    }

}
