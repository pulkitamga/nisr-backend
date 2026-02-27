<?php
namespace App\Services;

use App\Models\Warranty;
use App\Models\WarrantyDistributionHistory;

class WarrantyService
{
    public static function preloadSerials(array $serials, ?int $productId = null): array
    {
        $normalized = collect($serials)
            ->map(fn ($serial) => strtoupper(trim((string) $serial)))
            ->filter(fn ($serial) => $serial !== '')
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return [
                'serials' => [],
                'matched' => collect(),
                'missing' => [],
            ];
        }

        $query = Warranty::query()->whereIn('serial_number', $normalized);
        if ($productId) {
            $query->where('product_id', $productId);
        }

        $matchedBySerial = $query->get()->keyBy('serial_number');
        $missing = $normalized
            ->filter(fn ($serial) => !$matchedBySerial->has($serial))
            ->values()
            ->all();

        return [
            'serials' => $normalized->all(),
            'matched' => $matchedBySerial->values(),
            'missing' => $missing,
        ];
    }

    public static function transfer(Warranty $warranty, int $fromBranchId, int $toBranchId, ?int $distributorFrom = null, ?int $distributorTo = null, ?string $note = null)
    {
        $history = WarrantyDistributionHistory::create([
            'warranty_id' => $warranty->id,
            'from_distributor_id' => $distributorFrom ?? $warranty->distributor_id,
            'to_distributor_id' => $distributorTo,
            'from_branch_id' => $fromBranchId,
            'to_branch_id' => $toBranchId,
            'timestamp' => now(),
            'note' => $note,
        ]);

        $warranty->update([
            'branch_id' => $toBranchId,
            'distributor_id' => $distributorTo ?? $warranty->distributor_id,
        ]);

        return $history;
    }
}
