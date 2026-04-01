<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class VehicleYear extends Model
{
    use HasFactory, HasTranslations;
    protected $table = 'vehicle_years';
    protected $fillable = ['year'];

    public function getYearAttribute($year): ?string
    {
        if (strpos(url()->current(), '/admin') || strpos(url()->current(), '/vendor') || strpos(url()->current(), '/seller')) {
            return $year;
        }

        $translation = $this->translations
            ->first(fn ($item) => $item->locale === App::getLocale() && $item->key === 'year');

        if ($translation) {
            return $translation->value;
        }

        return $this->translations()
            ->where('locale', App::getLocale())
            ->where('key', 'year')
            ->value('value') ?? $year;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                return $query->where('locale', App::getLocale());
            }]);
        });
    }
}
