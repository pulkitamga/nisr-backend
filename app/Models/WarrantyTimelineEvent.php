<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyTimelineEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_id', 'warranty_claim_id', 'event_type', 'description', 'timestamp', 'user_id',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(WarrantyClaim::class, 'warranty_claim_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}