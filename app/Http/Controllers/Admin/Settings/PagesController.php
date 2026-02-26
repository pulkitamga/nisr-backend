<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ViewPaths\Admin\Pages;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\AboutUsRequest;
use App\Http\Requests\Admin\PageUpdateRequest;
use App\Http\Requests\Admin\PrivacyPolicyRequest;
use App\Http\Requests\Admin\TermsConditionRequest;
use App\Http\Requests\Admin\ServicePolicyRequest;
use App\Http\Requests\Admin\WarrantyPolicyRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Models\BusinessSetting;
use App\Models\Policy;
use Illuminate\Support\Str;



class PagesController extends BaseController
{

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,

    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getTermsConditionView();
    }

    public function getTermsConditionView(): View
    {
        $terms_condition = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'terms_condition'], relations: ['translations']);
        return view(Pages::TERMS_CONDITION[VIEW], compact('terms_condition'));
    }
    public function getServicePolicyView(): View
    {
        $service_policy = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'service_policy'], relations: ['translations']);
        return view(Pages::SERVICE_POLICY[VIEW], compact('service_policy'));
    }
    public function getWarrantyPolicyView(): View
    {
        $warranty_policy = Policy::with('translations')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->first();
        return view(Pages::WARRANTY_POLICY[VIEW], compact('warranty_policy'));
    }

    public function updateTermsCondition(TermsConditionRequest $request): RedirectResponse
    {
        $terms_condition = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'terms_condition']);
        $dataArray = $request->value ?? []; // safe fallback
        $defaultLang = getDefaultLanguage() ?? 'en';
        $defaultLangIndex = array_search($defaultLang, $request->lang);

        if ($defaultLangIndex !== false && $terms_condition && isset($dataArray[$defaultLangIndex])) {
            $this->businessSettingRepo->updateWhere(
                params: ['type' => 'terms_condition'],
                data: ['value' => $dataArray[$defaultLangIndex]]
            );

            $this->translationRepo->update(
                request: $request,
                model: BusinessSetting::class,
                id: $terms_condition->id
            );

            Toastr::success(translate('terms_condition_updated_successfully'));
        } else {
            Toastr::error(translate('default_language_data_not_found'));
        }
        clearWebConfigCacheKeys();
        return back();
    }
    public function updateServicePolicy(ServicePolicyRequest $request): RedirectResponse
    {

        $service_policy = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'service_policy']);

        $dataArray = $request->value ?? []; // safe fallback
        $defaultLang = getDefaultLanguage() ?? 'en';
        $defaultLangIndex = array_search($defaultLang, $request->lang);

        if ($defaultLangIndex !== false && $service_policy && isset($dataArray[$defaultLangIndex])) {
            $this->businessSettingRepo->updateWhere(
                params: ['type' => 'service_policy'],
                data: ['value' => $dataArray[$defaultLangIndex]]
            );
            $this->translationRepo->update(
                request: $request,
                model: BusinessSetting::class,
                id: $service_policy->id
            );
            Toastr::success(translate('service_policy_updated_successfully'));
        } else {
            Toastr::error(translate('default_language_data_not_found'));
        }


        clearWebConfigCacheKeys();
        return back();
    }
    public function updateWarrantyPolicy(WarrantyPolicyRequest $request): RedirectResponse
    {
        $defaultLang = getDefaultLanguage() ?? 'en';
        $data = $request->validated();
        $version = $data['version'] ?? '1.0';
        $defaultLangIndex = array_search($defaultLang, $data['lang'], true);
        if ($defaultLangIndex === false) {
            $defaultLangIndex = 0;
        }
        $value = $data['value'][$defaultLangIndex] ?? ($data['value'][0] ?? '');
        $locale = $data['locale'] ?? ($data['lang'][$defaultLangIndex] ?? $defaultLang);
        $publishedAt = isset($data['published_at']) ? \Carbon\Carbon::parse($data['published_at']) : now();

        $slugBase = Str::slug("warranty-policy-{$version}-{$locale}");
        $slug = $slugBase;

        $policy = Policy::where('version', $version)
            ->where('locale', $locale)
            ->first();

        if ($policy) {
            $slugExists = Policy::where('slug', $slug)->where('id', '!=', $policy->id)->exists();
            if ($slugExists) {
                $slug = "{$slugBase}-" . now()->timestamp;
            }

            $policy->update([
                'locale' => $locale,
                'status' => 'published',
                'effective_date' => $publishedAt->toDateString(),
                'content_html' => $value,
                'content_text' => strip_tags($value),
                'slug' => $slug,
                'published_at' => $publishedAt,
                'created_by' => auth()->id(),
            ]);

            $this->translationRepo->update(
                request: $request,
                model: Policy::class,
                id: $policy->id
            );
            Toastr::success(translate('warranty_policy_updated_successfully'));
        } else {
            $slugExists = Policy::where('slug', $slug)->exists();
            if ($slugExists) {
                $slug = "{$slugBase}-" . now()->timestamp;
            }

            $policy = Policy::create([
                'version' => $version,
                'locale' => $locale,
                'status' => 'published',
                'effective_date' => $publishedAt->toDateString(),
                'content_html' => $value,
                'content_text' => strip_tags($value),
                'slug' => $slug,
                'published_at' => $publishedAt,
                'created_by' => auth()->id(),
            ]);

            $this->translationRepo->add(
                request: $request,
                model: Policy::class,
                id: $policy->id
            );
            Toastr::success(translate('warranty_policy_created_successfully'));
        }

        clearWebConfigCacheKeys();
        return back();
    }

    public function getPrivacyPolicyView(): View
    {
        $privacy_policy = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'privacy_policy'], relations: ['translations']);
        return view(Pages::PRIVACY_POLICY[VIEW], compact('privacy_policy'));
    }

    public function updatePrivacyPolicy(PrivacyPolicyRequest $request): RedirectResponse
    {
        $privacy_policy = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'privacy_policy']);

        $dataArray = $request->value ?? [];
        $defaultLang = getDefaultLanguage() ?? 'en';
        $defaultLangIndex = array_search($defaultLang, $request->lang);

        if ($defaultLangIndex !== false && $privacy_policy && isset($dataArray[$defaultLangIndex])) {
            $this->businessSettingRepo->updateWhere(
                params: ['type' => 'privacy_policy'],
                data: ['value' => $dataArray[$defaultLangIndex]]
            );

            $this->translationRepo->update(
                request: $request,
                model: BusinessSetting::class,
                id: $privacy_policy->id
            );

            Toastr::success(translate('privacy_policy_updated_successfully'));
        } else {
            Toastr::error(translate('default_language_data_not_found'));
        }
        clearWebConfigCacheKeys();
        return back();
    }


    public function getPageView($page): View|RedirectResponse
    {
        $pages = ['refund-policy', 'return-policy', 'cancellation-policy', 'shipping-policy'];
        if (in_array($page, $pages)) {
            $data = $this->businessSettingRepo->getFirstWhere(params: ['type' => $page], relations: ['translations']);
            return view(Pages::VIEW[VIEW], compact('page', 'data'));
        }
        Toastr::error(translate('invalid_page'));
        return back();
    }

    public function updatePage(PageUpdateRequest $request, $page): RedirectResponse
    {
        $pages = ['refund-policy', 'return-policy', 'cancellation-policy', 'shipping-policy'];

        if (!in_array($page, $pages)) {
            Toastr::error(translate('invalid_page'));
            return back();
        }

        $defaultLang = getDefaultLanguage() ?? 'en';
        $defaultLangIndex = array_search($defaultLang, $request->lang);

        if ($defaultLangIndex === false) {
            Toastr::error(translate('default_language_data_not_found'));
            return back();
        }

        // Get or insert the record
        $setting = $this->businessSettingRepo->getFirstWhere(params: ['type' => $page]);
        if (!$setting) {
            $this->businessSettingRepo->updateOrInsert(type: $page, value: json_encode([
                'status' => $request->get('status', 0),
                'content' => $request->value[$defaultLangIndex],
            ]));
            $setting = $this->businessSettingRepo->getFirstWhere(params: ['type' => $page]);
        } else {
            $this->businessSettingRepo->updateWhere(
                params: ['type' => $page],
                data: ['value' => json_encode([
                    'status' => $request->get('status', 0),
                    'content' => $request->value[$defaultLangIndex],
                ])]
            );
        }

        // Translation update
        if ($setting) {
            $this->translationRepo->update(
                request: $request,
                model: BusinessSetting::class,
                id: $setting->id
            );
        }

        clearWebConfigCacheKeys();
        Toastr::success(translate('updated_successfully'));
        return back();
    }


    public function getAboutUsView(): View
    {
        $pageData = $this->businessSettingRepo->getFirstWhere(
            params: ['type' => 'about_us'],
            relations: ['translations']
        );
        return view(Pages::ABOUT_US[VIEW], compact('pageData'));
    }

    public function updateAboutUs(AboutUsRequest $request): RedirectResponse
    {
        $aboutUs = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'about_us']);

        $defaultLang = getDefaultLanguage() ?? 'en';
        $defaultLangIndex = array_search($defaultLang, $request->lang);

        if ($defaultLangIndex !== false && $aboutUs) {
            $this->businessSettingRepo->updateWhere(
                params: ['type' => 'about_us'],
                data: ['value' => $request->about_us[$defaultLangIndex]]
            );

            $this->translationRepo->update(
                request: $request,
                model: BusinessSetting::class,
                id: $aboutUs->id
            );

            Toastr::success(translate('about_us_updated_successfully'));
        } else {
            Toastr::error(translate('default_language_data_not_found'));
        }

        clearWebConfigCacheKeys();
        return back();
    }



    public function getCookieSettingsView(Request $request): View
    {
        $cookieSetting = $this->businessSettingRepo->getFirstWhere(
            params: ['type' => 'cookie_setting'],
            relations: ['translations']
        );

        return view(Pages::COOKIE_SETTINGS[VIEW], compact('cookieSetting'));
    }

    public function updateCookieSetting(Request $request): RedirectResponse
    {
        $defaultLangIndex = array_search(config('app.locale'), $request->lang);
        $cookie = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'cookie_setting']);
        $this->businessSettingRepo->updateOrInsert(type: 'cookie_setting', value: json_encode([
            'status' => $request->get('status', 0),
            'cookie_text' => $request['cookie_text'][$defaultLangIndex],
        ]));
        $this->translationRepo->update(
            request: $request,
            model: BusinessSetting::class,
            id: $cookie->id
        );
        clearWebConfigCacheKeys();
        Toastr::success(translate('cookie_settings_updated_successfully'));
        return redirect()->back();
    }
}
