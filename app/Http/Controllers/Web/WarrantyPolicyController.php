<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;

class WarrantyPolicyController extends Controller
{
    public function show(Request $request)
    {
        $policy = Policy::with('translations')
            ->orderBy('created_at', 'desc')
            ->first();

        return view(VIEW_FILE_NAMES['warranty_policy'], compact('policy'));
    }

    public function showVersion(Request $request, $version)
    {
        $locale = $request->query('locale', app()->getLocale());

        // Validate locale format - prevent license keys or other invalid values
        if (!preg_match('/^[a-z]{2,3}(_[a-z]{2,3})?$/', $locale)) {
            $locale = app()->getLocale();
        }

        $policy = Policy::where('version', $version)
            ->where('locale', $locale)
            ->firstOrFail();

        $isOutdated = $policy->status !== 'published' || Policy::published()
            ->where('locale', $locale)
            ->where('effective_date', '>', $policy->effective_date)
            ->exists();

        return view(VIEW_FILE_NAMES['warranty_policy'], compact('policy', 'isOutdated'));
    }
}
