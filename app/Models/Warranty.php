<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Str;


class Warranty extends Model
{
    use HasFactory, StorageTrait, SoftDeletes;

    protected $fillable = [
        'serial_number',
        'product_id',
        'product_stock_id',
        'warranty_months',
        'status',
        'activation_date',
        'start_date',
        'end_date',
        'final_user_id',
        'distributor_id',
        'branch_id',
        'activated_by_name',
        'activated_by_phone',
        'activated_by_email',
        'activated_ip',
        'activation_method',
        'is_admin_manual_activation',
        'is_admin_override',
        'original_warranty_id',
        'purchase_date',
        'retailer_name',
        'retailer_branch_id',
        'invoice_number',
        'receipt_path',
        'warranty_public_id',
        'policy_version',
        'consent_checked',
        'consent_timestamp',
        'consent_ip',
    ];

    protected $casts = [
        'activation_date' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'purchase_date' => 'datetime',
        'warranty_months' => 'integer',
        'is_admin_manual_activation' => 'boolean',
        'is_admin_override' => 'boolean',
        'consent_checked' => 'boolean', // Added for consent flag
        'consent_timestamp' => 'datetime', // Added for consent timestamp
    ];

    protected $appends = ['receipt_full_url', 'remaining_days'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productStock(): BelongsTo
    {
        return $this->belongsTo(ProductStock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_user_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    public function replacements(): HasMany
    {
        return $this->hasMany(WarrantyReplacement::class, 'original_warranty_id');
    }
    public function originalWarranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class, 'original_warranty_id');
    }
    public function distributionHistory(): HasMany
    {
        return $this->hasMany(WarrantyDistributionHistory::class);
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(WarrantyTimelineEvent::class, 'warranty_id');
    }

    public function activationReview(): HasOne
    {
        return $this->hasOne(ActivationReview::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && Carbon::now()->lt($this->end_date);
    }

    public function getRemainingDaysAttribute(): int
    {
        if (!$this->end_date) {
            return 0;
        }

        return max(0, Carbon::now()->diffInDays($this->end_date, false));
    }


    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('end_date', '>', now());
    }
    // Add to Warranty model
    public function scopeInBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeWithDistributor($query, $distributorId)
    {
        return $query->where('distributor_id', $distributorId);
    }
    public function scopeNotBlacklisted($query)
    {
        return $query->whereDoesntHave('blacklistedSerial');
    }

    // Scope for blacklist relation (morph or direct)
    public function blacklistedSerial()
    {
        return $this->hasOne(Blacklist::class, 'serial_number', 'serial_number');
    }

    // Warranty.php
    public function statusLabel(): string
    {
        if ($this->status === 'preactivated') {
            return 'preactivated';
        }

        if ($this->status === 'active' && $this->end_date && now()->lte($this->end_date)) {
            return 'active';
        }

        if ($this->status === 'active' && $this->end_date && now()->gt($this->end_date)) {
            return 'expired';
        }

        return $this->status; // default fallback
    }


    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->warranty_public_id)) {
                $model->warranty_public_id = Str::uuid();
            }
        });
    }

    public function getReceiptFullUrlAttribute()
    {
        if (!$this->receipt_path) {
            return null;
        }
 
        return $this->getFileUrl($this->receipt_path);
    }
}
