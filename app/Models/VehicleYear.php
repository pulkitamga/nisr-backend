<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

class VehicleYear extends Model
{
    use HasFactory;
    protected $table = 'vehicle_years';
    protected $fillable = ['year'];

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function getYearAttribute($year): ?string
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $year;
        }

        return $this->translations[0]->value ?? $year;
    }

    public function getDefaultYearAttribute(): ?string
    {
        return $this->getRawOriginal('year');
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
