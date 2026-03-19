<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_warranty_id', 'new_warranty_id', 'replaced_at', 'technician_id', 'notes',
    ];

    protected $casts = [
        'replaced_at' => 'datetime',
    ];

    public function originalWarranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class, 'original_warranty_id');
    }

    public function newWarranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class, 'new_warranty_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'technician_id');
    }
}
