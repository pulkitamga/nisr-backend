<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ProductStockTransaction extends Model
{
    protected $table = 'product_stock_transactions';

    protected $fillable = [
        'product_id',
        'product_stock_id',
        'type',
        'quantity',
        'branch_id',
        'notes',
        'created_by',
        'reason',
        'remarks',
        'from_branch_id',
        'to_branch_id',
    ];

    private static ?array $cachedTableColumns = null;

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
        self::create(self::buildLogPayload(
            stock: $stock,
            type: 'IN',
            qty: $qty,
            reason: $reason,
            remarks: $remarks,
            branchId: $toBranchId
        ));
    }
    public static function logStockOut(ProductStock $stock, int $qty,  string $reason,string $remarks = '', ?int $fromBranchId = null)
    {
        self::create(self::buildLogPayload(
            stock: $stock,
            type: 'OUT',
            qty: $qty,
            reason: $reason,
            remarks: $remarks,
            branchId: $fromBranchId
        ));
    }

    private static function buildLogPayload(
        ProductStock $stock,
        string $type,
        int $qty,
        string $reason,
        string $remarks = '',
        ?int $branchId = null
    ): array {
        $payload = [];

        if (self::hasTableColumn('product_id')) {
            $payload['product_id'] = $stock->product_id;
        }

        if (self::hasTableColumn('product_stock_id')) {
            $payload['product_stock_id'] = $stock->id;
        }

        if (self::hasTableColumn('type')) {
            $payload['type'] = $type;
        }

        if (self::hasTableColumn('quantity')) {
            $payload['quantity'] = $qty;
        }

        if (self::hasTableColumn('reason')) {
            $payload['reason'] = $reason;
        }

        if (self::hasTableColumn('remarks')) {
            $payload['remarks'] = $remarks;
        }

        if (self::hasTableColumn('notes')) {
            $payload['notes'] = $remarks;
        }

        if (self::hasTableColumn('branch_id')) {
            $payload['branch_id'] = $branchId;
        }

        if (self::hasTableColumn('from_branch_id') && $type === 'OUT') {
            $payload['from_branch_id'] = $branchId;
        }

        if (self::hasTableColumn('to_branch_id') && $type === 'IN') {
            $payload['to_branch_id'] = $branchId;
        }

        if (self::hasTableColumn('created_by')) {
            $payload['created_by'] = auth('admin')->id() ?? auth('seller')->id();
        }

        return $payload;
    }

    private static function hasTableColumn(string $column): bool
    {
        return in_array($column, self::getTableColumns(), true);
    }

    private static function getTableColumns(): array
    {
        if (self::$cachedTableColumns !== null) {
            return self::$cachedTableColumns;
        }

        try {
            if (!Schema::hasTable((new self())->getTable())) {
                return self::$cachedTableColumns = [];
            }

            return self::$cachedTableColumns = Schema::getColumnListing((new self())->getTable());
        } catch (Throwable) {
            return self::$cachedTableColumns = [];
        }
    }

}
