<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;

class WarrantyPolicyController extends Controller
{
    public function show(Request $request)
    {
        $locale = $request->query('locale', app()->getLocale());
        if (!preg_match('/^[a-z]{2,3}(_[a-z]{2,3})?$/', $locale)) {
            $locale = app()->getLocale();
        }

        $policy = Policy::with('translations')
            ->published()
            ->where('locale', $locale)
            ->orderByDesc('effective_date')
            ->orderByDesc('published_at')
            ->first();

        if (!$policy) {
            $policy = Policy::with('translations')
                ->published()
                ->orderByDesc('effective_date')
                ->orderByDesc('published_at')
                ->first();
        }

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
            ->published()
            ->where('locale', $locale)
            ->firstOrFail();

        $isOutdated = Policy::published()
            ->where('locale', $locale)
            ->where('effective_date', '>', $policy->effective_date)
            ->exists();

        return view(VIEW_FILE_NAMES['warranty_policy'], compact('policy', 'isOutdated'));
    }
}
