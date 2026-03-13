<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class WholeSaleProducts extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = "wholesale_products";

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'product_id',
        'variation_type',     // NEW: Size, Color, Model etc.
        'variation_key',      // NEW: color:Red|size:XL
        'status',
    ];

    public $timestamps = false;

    public function price_list()
    {
        return $this->hasMany(WholesaleProductPriceRange::class, 'wholesale_id');
    }


       public function price_list_for_user()
    {
        $userTier = auth()->guard('customer')->user()->tier;
        return $this->hasMany(WholesaleProductPriceRange::class, 'wholesale_id', 'id')
            ->where('tier', $userTier);
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    // Helper: Readable variation
    public function getVariationLabelAttribute()
    {
        if ($this->resolved_variation_type) {
            $resolvedKey = $this->resolved_variation_key;
            $key = $resolvedKey ? str_replace([':', '|'], [' : ', ' • '], $resolvedKey) : '';
            return $this->resolved_variation_type . ($key ? " → ({$key})" : '');
        }
        return 'Default';
    }

    public function getResolvedVariationTypeAttribute(): ?string
    {
        $type = trim((string)($this->variation_type ?? ''));
        if ($type !== '') {
            return $type;
        }

        return self::extractTypeFromVariationKey($this->variation_key);
    }

    public function getResolvedVariationKeyAttribute(): ?string
    {
        return self::normalizeVariationKey($this->variation_type, $this->variation_key);
    }

    public function getResolvedVariationDisplayAttribute(): string
    {
        $type = $this->resolved_variation_type;
        if ($type && $type !== '') {
            return $type;
        }

        $display = self::extractDisplayFromVariationKey($this->variation_key);
        if ($display !== null && $display !== '') {
            return $display;
        }
        return 'Default';
    }

    public static function normalizeVariationKey(?string $variationType, ?string $variationKey): ?string
    {
        $pairs = self::parseVariationKeyPairs($variationKey);
        if (!empty($pairs)) {
            $parts = [];
            foreach ($pairs as $key => $value) {
                $parts[] = "{$key}:{$value}";
            }
            return implode(' | ', $parts);
        }

        $type = trim((string)($variationType ?? ''));
        if ($type === '') {
            return null;
        }

        return 'variant:' . $type;
    }

    public static function extractTypeFromVariationKey(?string $variationKey): ?string
    {
        $key = trim((string)($variationKey ?? ''));
        if ($key === '') {
            return null;
        }

        $pairs = self::parseVariationKeyPairs($key);
        if (!empty($pairs)) {
            if (array_key_exists('variant', $pairs)) {
                return trim((string)$pairs['variant']) ?: null;
            }

            $last = end($pairs);
            $last = trim((string)$last);
            return $last !== '' ? $last : null;
        }

        return $key;
    }

    public static function extractDisplayFromVariationKey(?string $variationKey): ?string
    {
        $type = self::extractTypeFromVariationKey($variationKey);
        if ($type === null || $type === '') {
            return null;
        }

        return $type;
    }

    private static function parseVariationKeyPairs(?string $variationKey): array
    {
        $key = trim((string)($variationKey ?? ''));
        if ($key === '') {
            return [];
        }

        $pairs = [];
        $segments = preg_split('/\|/', $key) ?: [];
        foreach ($segments as $segment) {
            $segment = trim((string)$segment);
            if ($segment === '') {
                continue;
            }

            if (str_contains($segment, ':')) {
                [$rawKey, $rawValue] = array_map('trim', explode(':', $segment, 2));
                $normalizedKey = self::normalizeVariationSegmentKey($rawKey);
                $normalizedValue = trim((string)$rawValue);
                if ($normalizedKey === '' || $normalizedValue === '') {
                    continue;
                }
                $pairs[$normalizedKey] = $normalizedValue;
                continue;
            }

            $pairs['variant'] = $segment;
        }

        return $pairs;
    }

    private static function normalizeVariationSegmentKey(mixed $key): string
    {
        $normalized = strtolower(trim((string)$key));
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return str_replace(' ', '_', $normalized);
    }

    protected static function booted()
    {
        static::deleting(function ($product) {
            $product->price_list()->delete();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('wholesale_products')
            ->logOnly(['category_id', 'sub_category_id', 'product_id', 'variation_type', 'variation_key', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
