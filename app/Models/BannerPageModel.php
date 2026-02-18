<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerPageModel extends Model
{
    use HasFactory;
    protected $table = 'contact_banners'; 

    protected $fillable = [
        'heading',
        'subheading',
        'image',
        'is_active',
    ];
}
