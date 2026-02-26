<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Warranty;

class WarrantyPolicy
{
    public function view($user, Warranty $warranty): bool
    {
        if ($user instanceof Admin) {
            return true;
        }

        if (is_object($user) && isset($user->id)) {
            return (int) $warranty->final_user_id === (int) $user->id;
        }

        return false;
    }
}
