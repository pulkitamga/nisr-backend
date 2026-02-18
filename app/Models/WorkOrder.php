<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_claim_id', 'status', 'checklist_items', 'diagnosis',
        'parts_used', 'labor_hours',
    ];

    protected $casts = [
        'checklist_items' => 'array',
        'parts_used' => 'array',
        'labor_hours' => 'float',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(WarrantyClaim::class, 'warranty_claim_id');
    }
}