<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStockTransaction extends Model
{
    protected $table = 'product_stock_transactions';

    protected $fillable = [
        'product_stock_id',
        'type',
        'quantity',
         'reason',
        'remarks',
        'from_branch_id',
        'to_branch_id',
        
    ];

    /* ================= RELATIONS ================= */

    public function stock(): BelongsTo
    {
        return $this->belongsTo(ProductStock::class, 'product_stock_id');
    }

      public function fromBranch()
    {
        // Add ->withTrashed() to handle soft-deleted branches
        return $this->belongsTo(Branch::class, 'from_branch_id', 'id')->withTrashed();
    }
 
    public function toBranch()
    {
        // Add ->withTrashed() to handle soft-deleted branches
        return $this->belongsTo(Branch::class, 'to_branch_id', 'id')->withTrashed();
    }
    /* ================= HELPERS ================= */

    public static function deleteForProduct(int $productId): void
    {
        $stockIds = ProductStock::query()
            ->where('product_id', $productId)
            ->pluck('id');

        if ($stockIds->isEmpty()) {
            return;
        }

        self::query()
            ->whereIn('product_stock_id', $stockIds)
            ->delete();
    }

    public static function logStockIn(ProductStock $stock, int $qty,  string $reason,string $remarks = '', ?int $toBranchId = null)
    {
        self::create([
            'product_stock_id' => $stock->id,
            'type'             => 'IN',
            'quantity'         => $qty,
             'reason'           => $reason,
            'remarks'          => $remarks,
            'to_branch_id'     => $toBranchId,
        ]);
    }
    public static function logStockOut(ProductStock $stock, int $qty,  string $reason,string $remarks = '', ?int $fromBranchId = null)
{
    self::create([
        'product_stock_id' => $stock->id,
        'type'             => 'OUT',
        'quantity'         => $qty,
          'reason'           => $reason,
        'remarks'          => $remarks,
        'from_branch_id'   => $fromBranchId,
    ]);
}

}
