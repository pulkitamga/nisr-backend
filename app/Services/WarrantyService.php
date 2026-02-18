<?php
namespace App\Services;

use App\Models\Warranty;
use App\Models\WarrantyDistributionHistory;

class WarrantyService
{
    public static function preloadSerials(array $serials, ?int $productId = null): array
    {
        // Same as before...
        // Use getWebConfig if needed, e.g., for batch limits
    }

    public static function transfer(Warranty $warranty, int $fromBranchId, int $toBranchId, ?int $distributorFrom = null, ?int $distributorTo = null, ?string $note = null)
    {
        // Same as before...
    }
}