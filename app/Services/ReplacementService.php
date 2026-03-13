<?php
namespace App\Services;

use App\Models\WarrantyClaim;
use App\Models\Warranty;
use App\Models\WarrantyReplacement;
use App\Models\ProductStock;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReplacementService
{
    public static function processReplacement(WarrantyClaim $claim)
    {
        $newStock = ProductStock::available()->where('product_id', $claim->warranty->product_id)->first();
        if (!$newStock) {
            $claim->update(['status' => 'waiting_parts']);
            event(new \App\Events\NoStockEvent($claim));
            return;
        }

        $newSerial = $newStock->serial_number ?? Str::uuid();
        $remaining = $claim->warranty->remaining_days;
        $newEndDate = now()->addDays($remaining);

        $newWarranty = Warranty::create([
            'serial_number' => $newSerial,
            'product_id' => $claim->warranty->product_id,
            'product_stock_id' => $newStock->id,
            'status' => 'active',
            'start_date' => $claim->warranty->start_date,
            'end_date' => $newEndDate,
            'final_user_id' => $claim->warranty->final_user_id,
            'original_warranty_id' => $claim->warranty->id,
            'activation_method' => 'replacement',
        ]);

        $claim->warranty->update(['status' => 'replaced']);
        WarrantyReplacement::create([
            'original_warranty_id' => $claim->warranty->id,
            'new_warranty_id' => $newWarranty->id,
            'replaced_at' => now(),
            'technician_id' => auth()->id() ?? null,
        ]);

        $claim->update(['status' => 'shipped_ready']);
        event(new \App\Events\ReplacementShippedEvent($claim, $newWarranty));
    }
}