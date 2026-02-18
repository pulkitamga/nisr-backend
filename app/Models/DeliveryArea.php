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
class DeliveryArea extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'area' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'area', 'area_id'
    ];


   public function areaInfo(): BelongsTo
{
    return $this->belongsTo(Area::class, 'area_id', 'id');
}


}
