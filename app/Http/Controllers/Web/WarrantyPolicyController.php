<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;

class WarrantyPolicyController extends Controller
{
    public function show(Request $request)
    {
        $locale = Policy::normalizeLocale($request->query('locale', app()->getLocale()));

        $policy = Policy::with('translations')
            ->published()
            ->orderByDesc('effective_date')
            ->orderByDesc('published_at')
            ->first();

        $policyValue = $policy?->getLocalizedContentHtml($locale);

        return view(VIEW_FILE_NAMES['warranty_policy'], compact('policy', 'policyValue'));
    }

    public function showVersion(Request $request, $version)
    {
        $locale = Policy::normalizeLocale($request->query('locale', app()->getLocale()));

        $policy = Policy::with('translations')
            ->where('version', $version)
            ->published()
            ->firstOrFail();

        $isOutdated = Policy::published()
            ->where('effective_date', '>', $policy->effective_date)
            ->exists();

        $policyValue = $policy->getLocalizedContentHtml($locale);

        return view(VIEW_FILE_NAMES['warranty_policy'], compact('policy', 'isOutdated', 'policyValue'));
    }
}
