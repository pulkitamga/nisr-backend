<?php

namespace App\Http\Controllers\RestAPI\V1;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use Illuminate\Http\JsonResponse;

class PageApiController extends Controller
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo
    ) {}

    /**
     * PRIVACY POLICY API
     */
    public function privacyPolicy(): JsonResponse
    {
        // Get privacy policy content from business settings
        $privacyPolicy = getWebConfig('privacy_policy');

        // Get banner if exists
        $banner = $this->businessSettingRepo->whereJsonContains(
            params: ['type' => 'banner_privacy_policy'],
            value: ['status' => 1] // use number 1, not string '1'
        );

        return response()->json([
            'status' => true,
            'data' => [
                'content' => $privacyPolicy,
                'banner'  => $banner,
            ],
        ]);
    }

    /**
     * TERMS & CONDITIONS API
     */
    public function termsAndConditions(): JsonResponse
    {
        // Get terms content from business settings
        $termsCondition = getWebConfig('terms_condition');

        // Get banner if exists
        $banner = $this->businessSettingRepo->whereJsonContains(
            params: ['type' => 'banner_terms_conditions'],
            value: ['status' => 1] // use number 1, not string '1'
        );

        return response()->json([
            'status' => true,
            'data' => [
                'content' => $termsCondition,
                'banner'  => $banner,
            ],
        ]);
    }
}
