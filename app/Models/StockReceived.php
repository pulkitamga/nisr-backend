<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReceived extends Model
{
    use HasFactory;

    protected $table = 'stock_received';

    protected $fillable = [
        'branch_id',
        'product_id',
        'quantity_received',
        'received_date',
        'status',
        'approved_by',
    ];

    // Relationship with Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relationship with Category through Product
    public function category()
    {
        return $this->hasOneThrough(Category::class, Product::class, 'id', 'id', 'product_id', 'category_id');
    }

    // Relationship with From Branch
    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id'); 
    }
}
