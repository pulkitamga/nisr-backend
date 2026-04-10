<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class CmsService extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'type',
        'heading',
        'description',
        'image',
        'button_text',
        'selected_item_ids',
    ];

    protected $casts = [
        'selected_item_ids' => 'array',
    ];

    public function showcaseItems(): HasMany
    {
        return $this->hasMany(CmsServiceShowcaseItem::class)->orderBy('sort_order')->orderBy('id');
    }

}
