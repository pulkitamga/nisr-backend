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
        $locale = $request->query('locale', app()->getLocale());
        if (!preg_match('/^[a-z]{2,3}(_[a-z]{2,3})?$/', (string)$locale)) {
            $locale = app()->getLocale();
        }

        $policy = Policy::query()
            ->published()
            ->where('locale', $locale)
            ->orderByDesc('effective_date')
            ->orderByDesc('published_at')
            ->first();

        if (!$policy) {
            $policy = Policy::query()
                ->published()
                ->orderByDesc('effective_date')
                ->orderByDesc('published_at')
                ->first();
        }

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
                'locale' => $policy->locale,
                'effective_date' => optional($policy->effective_date)->toDateString(),
                'published_at' => optional($policy->published_at)?->toIso8601String(),
                'content_html' => $policy->content_html,
                'content_text' => $policy->content_text,
            ],
        ]);
    }
}
