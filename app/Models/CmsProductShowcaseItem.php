<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CmsProductShowcaseItem extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'cms_product_id',
        'product_id',
        'sort_order',
        'is_active',
        'card_type',
        'title',
        'description',
        'image',
        'primary_button_text',
        'primary_button_link',
    ];

    protected $casts = [
        'cms_product_id' => 'integer',
        'product_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(CmsProduct::class, 'cms_product_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }
}
