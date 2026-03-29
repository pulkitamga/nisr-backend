<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CareerCard extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [ 'icon', 'title', 'description'];


}
