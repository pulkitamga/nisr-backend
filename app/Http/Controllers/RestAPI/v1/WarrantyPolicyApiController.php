<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarrantyPolicyApiController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $locale = Policy::normalizeLocale($request->query('locale', app()->getLocale()));

        $policy = Policy::query()
            ->with('translations')
            ->published()
            ->orderByDesc('effective_date')
            ->orderByDesc('published_at')
            ->first();

        if (!$policy) {
            return response()->json([
                'success' => false,
                'message' => 'Warranty policy not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'policy' => [
                'version' => $policy->version,
                'locale' => $locale,
                'effective_date' => optional($policy->effective_date)->toDateString(),
                'published_at' => optional($policy->published_at)?->toIso8601String(),
                'content_html' => $policy->getLocalizedContentHtml($locale),
                'content_text' => $policy->getLocalizedContentText($locale),
            ],
        ]);
    }
}
