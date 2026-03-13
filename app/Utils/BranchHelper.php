<?php

namespace App\Utils;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchHelper
{
    public static function getAccessibleBranches()
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return collect();
        }

        if ($admin->isSuperAdmin()) {
            return Branch::all();
        }

        $branchIdRaw = (string)($admin->branch_id ?? '');

        if ($branchIdRaw === '') {
            return collect();
        }

        if (is_string($branchIdRaw) && str_starts_with($branchIdRaw, '[') && str_ends_with($branchIdRaw, ']')) {
            $branchIds = json_decode($branchIdRaw, true);
        } else {
            $branchIds = str_contains($branchIdRaw, ',') ? explode(',', $branchIdRaw) : [$branchIdRaw];
        }

        $branchIds = array_values(array_filter(array_map('intval', (array)$branchIds), fn ($id) => $id > 0));

        if (empty($branchIds)) {
            return collect();
        }

        return Branch::whereIn('id', $branchIds)->get();
    }
}
