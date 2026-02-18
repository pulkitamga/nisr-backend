<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyDistributionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_id', 'from_distributor_id', 'to_distributor_id',
        'from_branch_id', 'to_branch_id', 'timestamp', 'note',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }
}