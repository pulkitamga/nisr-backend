<?php

namespace App\Services;

use App\Models\Policy;
use Illuminate\Support\Facades\Schema;

class WarrantyPolicyVersionResolver
{
    public function resolvePublishedVersion(): ?string
    {
        if (!Schema::hasTable('policies')) {
            return null;
        }

        return Policy::query()
            ->published()
            ->orderByDesc('effective_date')
            ->orderByDesc('published_at')
            ->value('version');
    }
}
