<?php

namespace App\Utils;

use App\Utils\Helpers;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class BranchHelper
{
 public static function getAccessibleBranches()
    {
        $admin = Auth::guard('admin')->user();

        if ($admin->admin_role_id == 1) {
            return Branch::all();
        }

        $branchIdRaw = $admin->branch_id;

        if (is_string($branchIdRaw) && str_starts_with($branchIdRaw, '[') && str_ends_with($branchIdRaw, ']')) {
            $branchIds = json_decode($branchIdRaw, true);
        } else {
            $branchIds = str_contains($branchIdRaw, ',') ? explode(',', $branchIdRaw) : [$branchIdRaw];
        }

        $branchIds = array_map('intval', $branchIds);
        return Branch::whereIn('id', $branchIds)->get();
    }
}
