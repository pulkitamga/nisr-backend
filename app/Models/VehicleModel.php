<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

class VehicleModel extends Model
{
    use HasFactory;

    protected $table = 'vehicle_models';
    protected $fillable = ['make_id', 'name', 'year'];

    public function make(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class, 'make_id');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getNameAttribute($name): ?string
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $name;
        }

        return $this->translations[0]->value ?? $name;
    }

    public function getDefaultNameAttribute(): ?string
    {
        return $this->getRawOriginal('name');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                if (strpos(url()->current(), '/api')) {
                    return $query->where('locale', App::getLocale());
                }

                return $query->where('locale', getDefaultLanguage());
            }]);
        });
    }
}
