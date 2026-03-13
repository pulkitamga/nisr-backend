<?php

namespace App\Http\Controllers\Wholesaler\Auth;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\EmailTemplatesRepositoryInterface;
use App\Contracts\Repositories\HelpTopicRepositoryInterface;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Contracts\Repositories\WholeSalerRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Enums\ViewPaths\Vendor\Auth;
use App\Events\VendorRegistrationEvent;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Vendor\VendorAddRequest;
use App\Repositories\WholesalerRegistrationReasonRepository;
use App\Services\ShopService;
use App\Services\VendorService;
use App\Traits\EmailTemplateTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Brian2694\Toastr\Facades\Toastr;

class WholesalerRegisterController extends BaseController
{
    use EmailTemplateTrait;
    public function __construct(
        private readonly WholeSalerRepositoryInterface $vendorRepo,
        private readonly VendorWalletRepositoryInterface $vendorWalletRepo,
        private readonly ShopRepositoryInterface $shopRepo,
        private readonly VendorService $vendorService,
        private readonly ShopService $shopService,
        private readonly EmailTemplatesRepositoryInterface $emailTemplatesRepo,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly HelpTopicRepositoryInterface $helpTopicRepo,
        private readonly WholesalerRegistrationReasonRepository $vendorRegistrationReasonRepo,

    ) {}

    public function index(?Request $request, string $type = null): View|Collection|LengthAwarePaginator|null|callable|RedirectResponse
    {
        return $this->getView();
    }


    public function getView(): View|RedirectResponse
    {

        $currentLang = getDefaultLanguage();

        $vendorRegistrationHeaderSetting = $this->businessSettingRepo->getFirstWhere(
            ['type' => 'wholesaler_registration_header'],
            ['translations']
        );

        $vendorRegistrationHeader = json_decode($vendorRegistrationHeaderSetting['value']);
        $vendorRegistrationHeaderIsActive = $vendorRegistrationHeaderSetting['is_active'] ?? 0;

        $translatedData = $vendorRegistrationHeaderSetting->translations
            ->where('locale', $currentLang);

        $vendorRegistrationHeader->title = $translatedData->firstWhere('key', 'title')?->value
            ?? $vendorRegistrationHeader->title ?? '';
        $vendorRegistrationHeader->sub_title = $translatedData->firstWhere('key', 'sub_title')?->value
            ?? $vendorRegistrationHeader->sub_title ?? '';

        $vendorRegistrationReasons = $this->vendorRegistrationReasonRepo->getListWhere(
            orderBy: ['priority' => 'desc'],
            filters: ['status' => 1],
            dataLimit: 'all',
            relations: ['translations']
        );

        $vendorRegistrationReasons = $this->applyTranslations($vendorRegistrationReasons, $currentLang, ['title', 'description']);


        $sellWithUsSetting = $this->businessSettingRepo->getFirstWhere(
            ['type' => 'wholesaler_registration_sell_with_us'],
            ['translations']
        );
        $sellWithUs = json_decode($sellWithUsSetting['value']);
        $sellWithUsIsActive = $sellWithUsSetting['is_active'] ?? 0;
        $translatedData = $sellWithUsSetting->translations
            ->where('locale', $currentLang);

        foreach ($sellWithUs as $key => $value) {
            $translatedValue = $translatedData->firstWhere('key', $key)?->value;
            if ($translatedValue !== null) {
                $sellWithUs->{$key} = $translatedValue;
            }
        }

        $downloadVendorAppSetting = $this->businessSettingRepo->getFirstWhere(['type' => 'download_wholesaler_app']);
        $downloadVendorApp = json_decode($downloadVendorAppSetting['value']);
        $downloadVendorAppIsActive = $downloadVendorAppSetting['is_active'] ?? 0;


        $businessProcessSetting = $this->businessSettingRepo->getFirstWhere(
            ['type' => 'wholesaler_process_main_section'],
            ['translations']
        );


        $businessProcess = json_decode($businessProcessSetting['value']);

        $businessProcess->title = optional(
            $businessProcessSetting->translations->firstWhere(fn($t) => $t->locale === $currentLang && $t->key === 'title')
        )->value ?? $businessProcess->title;

        $businessProcess->sub_title = optional(
            $businessProcessSetting->translations->firstWhere(fn($t) => $t->locale === $currentLang && $t->key === 'sub_title')
        )->value ?? $businessProcess->sub_title;

        $businessProcessIsActive = $businessProcessSetting['is_active'] ?? 0;


        $businessProcessStepSetting = $this->businessSettingRepo->getFirstWhere(
            ['type' => 'wholesaler_process_step'],
            ['translations']
        );

        $businessProcessStep = json_decode($businessProcessStepSetting['value']);
        foreach ($businessProcessStep as $i => $step) {
            $titleKey = 'section_' . ($i + 1) . '_title';
            $descriptionKey = 'section_' . ($i + 1) . '_description';
            $step->title = optional(
                $businessProcessSetting->translations->firstWhere(
                    fn($t) =>
                    $t->locale === $currentLang && $t->key === $titleKey
                )
            )->value ?? $step->title;
            $step->description = optional(
                $businessProcessSetting->translations->firstWhere(
                    fn($t) =>
                    $t->locale === $currentLang && $t->key === $descriptionKey
                )
            )->value ?? $step->description;
        }
        $businessProcessStepIsActive = $businessProcessStepSetting['is_active'] ?? 0;


        $helpTopics = $this->helpTopicRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['type' => 'wholesaler_registration', 'status' => '1'],
            relations: ['translations'],
            dataLimit: 'all'
        );

        foreach ($helpTopics as $topic) {
            $questionTranslation = $topic->translations
                ->firstWhere('locale', $currentLang)
                ?->where('key', 'question');

            $questionTranslation = $topic->translations
                ->first(fn($t) => $t->locale === $currentLang && $t->key === 'question');

            $answerTranslation = $topic->translations
                ->first(fn($t) => $t->locale === $currentLang && $t->key === 'answer');

            $topic->question = $questionTranslation->value ?? $topic->question;
            $topic->answer = $answerTranslation->value ?? $topic->answer;
        }
        return view(VIEW_FILE_NAMES[Auth::WHOLESALER_REGISTRATION[VIEW]], compact('vendorRegistrationHeader', 'vendorRegistrationReasons', 'sellWithUs', 'downloadVendorApp', 'helpTopics', 'businessProcess', 'businessProcessStep', 'vendorRegistrationHeaderIsActive', 'sellWithUsIsActive', 'downloadVendorAppIsActive', 'businessProcessIsActive', 'businessProcessStepIsActive',));
    }


    function applyTranslations(Collection $collection, string $locale, array $keysToTranslate): Collection
    {
        return $collection->each(function ($item) use ($locale, $keysToTranslate) {
            $translations = $item->translations->where('locale', $locale);

            foreach ($keysToTranslate as $key) {
                $translatedValue = $translations->firstWhere('key', $key)?->value;
                if ($translatedValue !== null) {
                    $item->{$key} = $translatedValue;
                }
            }
        });
    }


    public function add(VendorAddRequest $request): JsonResponse
    {
        $vendor = $this->vendorRepo->add(data: $this->vendorService->getAddData($request));
        $this->shopRepo->add($this->shopService->getAddShopDataForRegistration(request: $request, vendorId: $vendor['id']));
        $this->vendorWalletRepo->add($this->vendorService->getInitialWalletData(vendorId: $vendor['id']));

        // $data = [
        //     'vendorName' => $request['f_name'],
        //     'status' => 'pending',
        //     'subject' => translate('Vendor_Registration_Successfully_Completed'),
        //     'title' => translate('Vendor_Registration_Successfully_Completed'),
        //     'userType' => 'vendor',
        //     'templateName' => 'registration',
        // ];
        // event(new VendorRegistrationEvent(email: $request['email'], data: $data));
        return response()->json(
            [
                'redirectRoute' => route('wholesaler.auth.login')
            ]
        );
    }

    public function submitRegisterData(VendorAddRequest $request): JsonResponse
    {
        return $this->add($request);
    }
}
