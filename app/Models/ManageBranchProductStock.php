<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ManageBranchProductStock extends Model
{
    use HasFactory;

    protected $table = 'manage_branch_product_stock';

    private static ?array $cachedTableColumns = null;

    protected $fillable = [
        'branch_id',
        'product_id',
        'attribute_id',         // NEW
        'variation_type',       // NEW
        'variation_key',        // NEW (e.g., "color:Red", "power:50")
        'attributes',           // OLD (keep for backward compatibility)
        'current_stock',
    ];

    protected $casts = [
        'current_stock' => 'integer',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    // Helper: Get readable variation
    public function getVariationLabelAttribute()
    {
        return $this->variation_key 
            ? str_replace(':', ' : ', $this->variation_key)
            : ($this->attributes ?? 'Default');
    }

    // Scope: Find stock by variation key
    public function scopeForVariation($query, $branchId, $productId, $variationKey)
    {
        return $query->where('branch_id', $branchId)
                     ->where('product_id', $productId)
                     ->where(function ($variationQuery) use ($variationKey) {
                         $variationQuery->where('variation_key', $variationKey)
                             ->orWhere('variation_type', $variationKey);
                     });
    }

    // Scope: Find default stock (no variation)
    public function scopeDefaultStock($query, $branchId, $productId)
    {
        return $query->where('branch_id', $branchId)
                     ->where('product_id', $productId)
                     ->where(function ($variationQuery) {
                         $variationQuery->where(function ($keyQuery) {
                             $keyQuery->whereNull('variation_key')
                                 ->orWhere('variation_key', '');
                         })->where(function ($typeQuery) {
                             $typeQuery->whereNull('variation_type')
                                 ->orWhere('variation_type', '');
                         });
                     });
    }

    public static function buildInventoryLookup(int $branchId, int $productId, ?string $variantType): array
    {
        $normalizedVariant = self::normalizeVariantType($variantType);

        $lookup = [
            'branch_id' => $branchId,
            'product_id' => $productId,
        ];

        if (self::hasTableColumn('variation_type')) {
            $lookup['variation_type'] = $normalizedVariant;
        }

        if (self::hasTableColumn('variation_key')) {
            $lookup['variation_key'] = $normalizedVariant;
        }

        if (
            !array_key_exists('variation_type', $lookup)
            && !array_key_exists('variation_key', $lookup)
            && self::hasTableColumn('attributes')
        ) {
            $lookup['attributes'] = $normalizedVariant;
        }

        return $lookup;
    }

    public static function buildInventoryValues(int $currentStock, ?string $variantType): array
    {
        $normalizedVariant = self::normalizeVariantType($variantType);

        $values = [
            'current_stock' => max(0, $currentStock),
        ];

        if (self::hasTableColumn('variation_type')) {
            $values['variation_type'] = $normalizedVariant;
        }

        if (self::hasTableColumn('variation_key')) {
            $values['variation_key'] = $normalizedVariant;
        }

        if (self::hasTableColumn('attributes')) {
            $values['attributes'] = $normalizedVariant;
        }

        return $values;
    }

    private static function normalizeVariantType(?string $variantType): ?string
    {
        $normalizedValue = trim((string) $variantType);

        return $normalizedValue !== '' ? $normalizedValue : null;
    }

    private static function hasTableColumn(string $column): bool
    {
        return in_array($column, self::getTableColumns(), true);
    }

    private static function getTableColumns(): array
    {
        if (self::$cachedTableColumns !== null) {
            return self::$cachedTableColumns;
        }

        try {
            if (!Schema::hasTable((new self())->getTable())) {
                return self::$cachedTableColumns = [];
            }

            return self::$cachedTableColumns = Schema::getColumnListing((new self())->getTable());
        } catch (Throwable) {
            return self::$cachedTableColumns = [];
        }
    }
}
