<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationMeta extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "quotation_metas";
    protected $fillable = [
        'wholesale_quotation_id',
        'type',
        'key',
        'value',
    ];
    protected $dates = ['deleted_at'];

    public function quotation()
    {
        return $this->belongsTo(WholesaleQuotation::class, 'wholesale_quotation_id');
    }
}

