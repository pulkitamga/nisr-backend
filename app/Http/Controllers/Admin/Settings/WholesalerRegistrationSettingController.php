<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\HelpTopicRepositoryInterface;
use App\Enums\ViewPaths\Admin\WholesalerRegistrationSetting;
use App\Enums\WebConfigKey;
use App\Http\Controllers\BaseController;
use App\Repositories\WholesalerRegistrationReasonRepository;
use App\Services\WholesalerRegistrationSettingService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\BusinessSetting;
use App\Contracts\Repositories\TranslationRepositoryInterface;


class WholesalerRegistrationSettingController extends BaseController
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly WholesalerRegistrationSettingService $vendorRegistrationSettingService,
        private readonly WholesalerRegistrationReasonRepository $vendorRegistrationReasonRepo,
        private readonly HelpTopicRepositoryInterface $helpTopicRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,


    ) {}
    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getView();
    }
    public function getView(): View
    {
        $businessSetting = $this->businessSettingRepo->getFirstWhere(
            params: ['type' => 'wholesaler_registration_header'],
            relations: ['translations']
        );

        $wholesalerRegistrationHeader = json_decode($businessSetting->value);
        $type = BusinessSetting::where('type', 'wholesaler_registration_header')->first();

        $translations = [];
        foreach ($businessSetting->translations as $translation) {
            $translations[$translation->locale][$translation->key] = $translation->value;
        }

        return view(WholesalerRegistrationSetting::INDEX[VIEW], compact('wholesalerRegistrationHeader', 'type', 'translations'));
    }
    public function getSellWithUsView(): View
    {

        $businessSetting = $this->businessSettingRepo->getFirstWhere(
            params: ['type' => 'wholesaler_registration_sell_with_us'],
            relations: ['translations']
        );
        $translations = [];
        foreach ($businessSetting->translations as $translation) {
            $translations[$translation->locale][$translation->key] = $translation->value;
        }
        $sellWithUs = json_decode($businessSetting->value);
        $wholesalerRegistrationReasons = $this->vendorRegistrationReasonRepo->getList(orderBy: ['id' => 'desc'], dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT));
        $type = BusinessSetting::where('type', 'wholesaler_registration_sell_with_us')->first();
        return view(WholesalerRegistrationSetting::WITH_US[VIEW], compact('sellWithUs', 'wholesalerRegistrationReasons', 'type', 'translations'));
    }
    public function getBusinessProcessView(): View
    {

        $businessSetting = $this->businessSettingRepo->getFirstWhere(
            params: ['type' => 'wholesaler_process_main_section'],
            relations: ['translations']
        );
        $businessProcess = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'wholesaler_process_main_section'])['value']);
        $type = BusinessSetting::where('type', 'wholesaler_process_main_section')->first();
        $businessProcessStep = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'wholesaler_process_step'])['value']);
        $translations = [];
        foreach ($businessSetting->translations as $translation) {
            $translations[$translation->locale][$translation->key] = $translation->value;
        }
        return view(WholesalerRegistrationSetting::BUSINESS_PROCESS[VIEW], compact('businessProcess', 'businessProcessStep', 'type', 'translations'));
    }
    public function getDownloadAppView(): View
    {
        $downloadWholesalerApp = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'download_wholesaler_app'])['value']);
        $type = BusinessSetting::where('type', 'download_wholesaler_app')->first();

        return view(WholesalerRegistrationSetting::DOWNLOAD_APP[VIEW], compact('downloadWholesalerApp', 'type'));
    }
    public function getFAQView(Request $request): View
    {
        $helps = $this->helpTopicRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['type' => 'wholesaler_registration'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );

        $translations = [];

        foreach ($helps as $help) {
            foreach ($help->translations as $trans) {
                $translations[$help->id][$trans->locale][$trans->key] = $trans->value;
            }
        }


        return view(WholesalerRegistrationSetting::FAQ[VIEW], compact('helps', 'translations'));
    }
    public function updateHeaderSection(Request $request): RedirectResponse
    {
        $englishIndex = getLanguageInputIndex($request, 'en');
        $rules = [];
        $messages = [];

        if ($englishIndex !== null) {
            $rules['title.' . $englishIndex . '.en'] = ['required', 'string'];
            $messages['title.' . $englishIndex . '.en.required'] = translate('The_title_in_english_is_required');
        }

        $request->validate($rules, $messages);

        $vendorRegistrationHeader = json_decode(
            $this->businessSettingRepo->getFirstWhere(params: ['type' => 'wholesaler_registration_header'])['value'] ?? '{}'
        );



        $this->businessSettingRepo->updateOrInsert(
            type: 'wholesaler_registration_header',
            value: $this->vendorRegistrationSettingService->getHeaderAndSellWithUsUpdateData(request: $request, image: $vendorRegistrationHeader->image ?? null)
        );

        $businessSetting = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'wholesaler_registration_header']);
        $transRequestArray = [
            'lang' => $request->lang,
        ];

        foreach ($request->lang as $index => $locale) {
            foreach (['title', 'sub_title'] as $field) {
                $transRequestArray[$field][$index] = $request->$field[$index][$locale] ?? '';
            }
        }

        $transRequest = new Request($transRequestArray);


        $this->translationRepo->update(
            request: $transRequest,
            model: BusinessSetting::class,
            id: $businessSetting->id
        );
        Toastr::success(translate('Updated_Successfully'));
        return redirect()->back();
    }

    public function updateSellWithUsSection(Request $request): RedirectResponse
    {
        $englishIndex = getLanguageInputIndex($request, 'en');
        $rules = [];
        $messages = [];

        if ($englishIndex !== null) {
            $rules['title.' . $englishIndex . '.en'] = ['required', 'string'];
            $messages['title.' . $englishIndex . '.en.required'] = translate('The_title_in_english_is_required');
        }

        $request->validate($rules, $messages);

        $sellWithUs = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'wholesaler_registration_sell_with_us'])['value']);
        $this->businessSettingRepo->updateOrInsert(
            type: 'wholesaler_registration_sell_with_us',
            value: $this->vendorRegistrationSettingService->getHeaderAndSellWithUsUpdateData(request: $request, image: $sellWithUs->image ?? null)
        );

        $businessSetting = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'wholesaler_registration_sell_with_us']);
        $transRequestArray = [
            'lang' => $request->lang,
        ];

        foreach ($request->lang as $index => $locale) {
            foreach (['title', 'sub_title'] as $field) {
                $transRequestArray[$field][$index] = $request->$field[$index][$locale] ?? '';
            }
        }

        $transRequest = new Request($transRequestArray);


        $this->translationRepo->update(
            request: $transRequest,
            model: BusinessSetting::class,
            id: $businessSetting->id
        );
        Toastr::success(translate('Updated_Successfully'));
        return redirect()->back();
    }
    public function updateBusinessProcess(Request $request): RedirectResponse
    {
        $englishIndex = getLanguageInputIndex($request, 'en');
        $rules = [
            'section_1_title.en' => ['required', 'string'],
            'section_2_title.en' => ['required', 'string'],
            'section_3_title.en' => ['required', 'string'],
        ];
        $messages = [
            'section_1_title.en.required' => translate('The_title_in_english_is_required'),
            'section_2_title.en.required' => translate('The_title_in_english_is_required'),
            'section_3_title.en.required' => translate('The_title_in_english_is_required'),
        ];

        if ($englishIndex !== null) {
            $rules['title.' . $englishIndex . '.en'] = ['required', 'string'];
            $messages['title.' . $englishIndex . '.en.required'] = translate('The_title_in_english_is_required');
        }

        $request->validate($rules, $messages);

        $defaultLang = getConfiguredDefaultLanguage();
        $mainData = [];
        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['_token', 'lang'])) continue;

            if (is_array($value) && isset($value[0]) && is_array($value[0])) {
                foreach ($value as $item) {
                    if (isset($item[$defaultLang])) {
                        $mainData[$key] = $item[$defaultLang];
                        break;
                    }
                }
            } elseif (is_array($value) && isset($value[$defaultLang])) {
                $mainData[$key] = $value[$defaultLang];
            }
        }
        $this->businessSettingRepo->updateOrInsert(
            type: 'wholesaler_process_main_section',
            value: $this->vendorRegistrationSettingService->getBusinessProcessUpdateData($mainData)
        );
        $businessProcessStep = json_decode(
            $this->businessSettingRepo->getFirstWhere(params: ['type' => 'wholesaler_process_step'])['value'] ?? '[]'
        );
        $this->businessSettingRepo->updateOrInsert(
            type: 'wholesaler_process_step',
            value: $this->vendorRegistrationSettingService->getBusinessProcessStepUpdateData(
                request: $request,
                businessProcessStep: $businessProcessStep,
                defaultLang: $defaultLang
            )
        );
        $transRequestArray = ['lang' => $request->lang];
        $translatableFields = array_filter(array_keys($request->all()), function ($key) {
            return !in_array($key, ['_token', 'lang']);
        });

        foreach ($request->lang as $index => $locale) {
            if ($locale === $defaultLang) continue;

            foreach ($translatableFields as $field) {
                if (isset($request->$field)) {

                    if (is_array($request->$field) && isset($request->$field[0]) && is_array($request->$field[0])) {
                        $transRequestArray[$field][$index] = $request->$field[$index][$locale] ?? '';
                    } elseif (is_array($request->$field) && isset($request->$field[$locale])) {
                        $transRequestArray[$field][$index] = $request->$field[$locale];
                    }
                }
            }
        }
        $transRequest = new Request($transRequestArray);
        $businessSetting = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'wholesaler_process_main_section']);
        $this->translationRepo->update(
            request: $transRequest,
            model: BusinessSetting::class,
            id: $businessSetting->id
        );
        Toastr::success(translate('Updated_Successfully'));
        return redirect()->back();
    }
    public function updateDownloadAppSection(Request $request): RedirectResponse
    {

        $downloadVendorApp = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'download_wholesaler_app'])['value']);
        $this->businessSettingRepo->updateOrInsert(
            type: 'download_wholesaler_app',
            value: $this->vendorRegistrationSettingService->getDownloadVendorAppUpdateData(request: $request, image: $downloadVendorApp?->image)
        );
        Toastr::success(translate('Updated_Successfully'));
        return redirect()->back();
    }




    public function toggleActiveStatus(Request $request): \Illuminate\Http\JsonResponse
    {


        $setting = BusinessSetting::where('type', $request->type)->first();
        $setting->is_active = $request->is_active;
        $setting->save();


        return response()->json(['message' => 'Status updated successfully.']);
    }
}
