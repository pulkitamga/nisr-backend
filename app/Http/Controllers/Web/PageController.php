<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\HelpTopicRepositoryInterface;
use App\Contracts\Repositories\RobotsMetaContentRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\AboutHeroSection;
use App\Models\AboutWhoWeAreSection;
use App\Models\AboutProductSection;
use App\Models\AboutMissionSection;
use App\Models\AboutTimelineSection;
use App\Models\AboutDealerSection;
use App\Models\CareerCard;
use App\Models\CareerJob;
use App\Models\CareerSection;
use App\Models\CareerBenefits;
use App\Models\Branch;
use App\Models\BusinessPage;
use App\Models\CmsService;
use App\Models\Policy;
use Illuminate\Http\Request;
use App\Models\CmsProduct;
use Illuminate\Support\Collection;

class PageController extends Controller
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface   $businessSettingRepo,
        private readonly HelpTopicRepositoryInterface         $helpTopicRepo,
        private readonly RobotsMetaContentRepositoryInterface $robotsMetaContentRepo,
    ) {}

    public function getAboutUsView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'about-us']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $aboutUs = getWebConfig(name: 'about_us');

        $heroItems = AboutHeroSection::with('translations')->where('is_active', 1)->latest()->get();
        $whoWeAre = AboutWhoWeAreSection::with('translations')->where('is_active', 1)->latest()->first();
        $products = AboutProductSection::with('translations')->where('is_active', 1)->latest()->get();
        $mission = AboutMissionSection::with('translations')->where('is_active', 1)->latest()->first();
        $timelines = AboutTimelineSection::with('translations')->where('is_active', 1)->latest()->get();
        $dealerQuery = AboutDealerSection::with('translations')->where('is_active', 1)->latest();
        $dealerFilterSource = (clone $dealerQuery)->get();
        $dealers = $dealerQuery->paginate(10);
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_about_us'], value: ['status' => '1']);

        return view(VIEW_FILE_NAMES['about_us'], compact('aboutUs', 'pageTitleBanner', 'robotsMetaContentData', 'heroItems', 'whoWeAre', 'products', 'mission', 'timelines', 'dealers', 'dealerFilterSource'));
    }
    public function getProductShowcaseView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'product_showcase']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }

        $cmsData = CmsProduct::with([
                'translations',
                'showcaseItems' => fn ($query) => $query
                    ->with('translations')
                    ->where('is_active', 1)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->where('is_active', 1)
            ->whereIn('type', ['main_banner', 'core_product_slider', 'feature_product', 'request_card_1', 'request_card_2', 'request_card_3'])
            ->get();

        $heroSection = $cmsData->firstWhere('type', 'core_product_slider');
        $heroSlides = $heroSection?->showcaseItems?->values() ?? collect();

        $featureSection = $cmsData->firstWhere('type', 'feature_product');
        $showcaseItems = $featureSection?->showcaseItems?->values() ?? collect();

        return view(VIEW_FILE_NAMES['product_showcase'], compact('robotsMetaContentData', 'heroSlides', 'showcaseItems', 'cmsData'));
    }
    public function getServicesShowcaseView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'services_showcase']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }

        $cmsData = CmsService::with([
                'translations',
                'showcaseItems' => fn ($query) => $query
                    ->with('translations')
                    ->where('is_active', 1)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->where('is_active', 1)
            ->whereIn('type', ['main_banner', 'hero_slider', 'service_showcase', 'request_card_1', 'request_card_2', 'request_card_3'])
            ->get();

        $heroSection = $cmsData->firstWhere('type', 'hero_slider');
        $heroSlides = $heroSection?->showcaseItems?->values() ?? collect();

        $showcaseSection = $cmsData->firstWhere('type', 'service_showcase');
        $showcaseItems = $showcaseSection?->showcaseItems?->values() ?? collect();

        return view(VIEW_FILE_NAMES['services_showcase'], compact('robotsMetaContentData', 'heroSlides', 'showcaseItems', 'cmsData'));
    }


    public function getContactView(Request $request): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'contacts']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $recaptcha = getWebConfig(name: 'recaptcha');
        $branches = Branch::where('id', '!=', 1)->get();

        $query = Branch::with('translations')->where('id', '!=', 1);

        if ($request->filled('search')) {
            $searchTerms = preg_split('/\s+/', trim((string)$request->search));
            $query->where(function ($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->orWhere('branch_name', 'like', "%{$term}%")
                        ->orWhere('branch_address', 'like', "%{$term}%")
                        ->orWhere('branch_state', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhereHas('translations', function ($translationQuery) use ($term) {
                            $translationQuery->whereIn('key', ['branch_name', 'branch_address'])
                                ->where('value', 'like', "%{$term}%");
                        });
                }
            });
        }

        $branchesTable = $query->paginate(10);
        return view(VIEW_FILE_NAMES['contacts'], compact('recaptcha', 'robotsMetaContentData', 'branches', 'branchesTable'));
    }

    private function applyCuratedSectionSelection(Collection $items, mixed $selectedIds, int $fallbackLimit): Collection
    {
        $ids = collect($selectedIds)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($ids->isEmpty()) {
            return $items->take($fallbackLimit)->values();
        }

        $selectedItems = $ids
            ->map(fn ($id) => $items->firstWhere('id', $id))
            ->filter()
            ->values();

        return $selectedItems->isNotEmpty()
            ? $selectedItems
            : $items->take($fallbackLimit)->values();
    }

    public function getHelpTopicView(): View
    {

        $currentLang = getDefaultLanguage();

        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'helpTopic']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }

        $helps = $this->helpTopicRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['type' => 'default', 'status' => '1'],
            relations: ['translations'],
            dataLimit: 'all'
        );

        foreach ($helps as $topic) {
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
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_faq_page'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['faq'], compact('helps', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getRefundPolicyView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'refund-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $refundPolicy = getBusinessPolicyConfig('refund-policy');
        if (!$refundPolicy || !($refundPolicy['status'] ?? 0)) {
            return redirect()->route('home');
        }
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_refund_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['refund_policy_page'], compact('refundPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getReturnPolicyView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'return-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $returnPolicy = getBusinessPolicyConfig('return-policy');
        if (!$returnPolicy || !($returnPolicy['status'] ?? 0)) {
            return redirect()->route('home');
        }
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_return_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['return_policy_page'], compact('returnPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getPrivacyPolicyView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'privacy-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $privacyPolicy = $this->resolveConfigBackedPolicy(
            slug: 'privacy-policy',
            type: 'privacy_policy',
            jsonContent: false,
        );
        if (!$privacyPolicy || !($privacyPolicy['status'] ?? 0)) {
            return redirect()->route('home');
        }
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_privacy_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['privacy_policy_page'], compact('privacyPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getServicePolicyView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'service-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }

        $servicePolicy = $this->resolveConfigBackedPolicy(
            slug: 'service-policy',
            type: 'service_policy',
            jsonContent: false,
        );
        if (!$servicePolicy || !($servicePolicy['status'] ?? 0)) {
            return redirect()->route('home');
        }

        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_service_policy'], value: ['status' => '1']);

        return view(VIEW_FILE_NAMES['service_policy_page'], compact('servicePolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getCancellationPolicyView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'cancellation-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $cancellationPolicy = getBusinessPolicyConfig('cancellation-policy');
        if (!$cancellationPolicy || !($cancellationPolicy['status'] ?? 0)) {
            return redirect()->route('home');
        }
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_cancellation_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['cancellation_policy_page'], compact('cancellationPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getShippingPolicyView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'shipping-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $shippingPolicy = getBusinessPolicyConfig('shipping-policy');
        if (!$shippingPolicy || !($shippingPolicy['status'] ?? 0)) {
            return redirect()->route('home');
        }
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_shipping_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['shipping_policy_page'], compact('shippingPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getTermsAndConditionView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'terms']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $termsPolicy = $this->resolveTermsPolicy();
        if (!$termsPolicy || !($termsPolicy['status'] ?? 0)) {
            return redirect()->route('home');
        }
        $termsCondition = $termsPolicy['content'];
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_terms_conditions'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['terms_conditions_page'], compact('termsCondition', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function career(): View
    {

        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'career']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $themeName = theme_root_path();

        $careerSection = CareerSection::with('translations')->where('is_active', 1)->get();

        $careerBenefits = CareerBenefits::with('translations')->where('is_active', 1)->get();

        $careerJobs = CareerJob::with('translations')->where('is_active', 1)->get();

        $careerCards = CareerCard::with('translations')->where('is_active', 1)->get();

        return match ($themeName) {
            'default' => view('default.web-views.pages.career', compact('careerSection', 'careerJobs', 'careerCards', 'careerBenefits', 'robotsMetaContentData')),
            'theme_aster' => view('theme_aster.web-views.pages.career', compact('careerSection', 'careerJobs', 'careerCards', 'careerBenefits')),
            'theme_fashion' => view('theme_fashion.web-views.pages.career', compact('careerSection', 'careerJobs', 'careerCards', 'careerBenefits')),
            default => abort(404),
        };
    }

        public function getOurPoliciesView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'our-policies']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }

        // JSON array policies
        $refund_policy = getBusinessPolicyConfig('refund-policy');
        $return_policy = getBusinessPolicyConfig('return-policy');
        $cancellation_policy = getBusinessPolicyConfig('cancellation-policy');
        $shipping_policy = getBusinessPolicyConfig('shipping-policy');

        // String content policies - convert to array
        $terms_policy = $this->resolveTermsPolicy();
        $privacy_policy = $this->resolveConfigBackedPolicy(
            slug: 'privacy-policy',
            type: 'privacy_policy',
            jsonContent: false,
        );
        $service_policy = $this->resolveConfigBackedPolicy(
            slug: 'service-policy',
            type: 'service_policy',
            jsonContent: false,
        );
        $warranty_policy = $this->resolveWarrantyPolicy();

        return view('web-views.pages.our-policies', compact(
            'robotsMetaContentData',
            'terms_policy',
            'refund_policy',
            'return_policy',
            'cancellation_policy',
            'shipping_policy',
            'service_policy',
            'privacy_policy',
            'warranty_policy'
        ));
    }

    private function resolveTermsPolicy(): ?array
    {
        if (!$this->isBusinessPageEnabled('terms-and-conditions')) {
            return null;
        }

        $termsContent = getWebConfig(name: 'terms_condition');

        if (!is_string($termsContent) || trim(strip_tags($termsContent)) === '') {
            return null;
        }

        return [
            'status' => 1,
            'content' => $termsContent,
        ];
    }

    private function resolveWarrantyPolicy(): ?array
    {
        if (!$this->isBusinessPageEnabled('warranty-policy')) {
            return null;
        }

        $policy = Policy::query()
            ->published()
            ->orderByDesc('effective_date')
            ->orderByDesc('published_at')
            ->first();

        if (!$policy) {
            return null;
        }

        return [
            'status' => 1,
            'content' => $policy->getLocalizedContentHtml(Policy::normalizeLocale(app()->getLocale())),
        ];
    }

    private function resolveConfigBackedPolicy(string $slug, string $type, bool $jsonContent = true): ?array
    {
        if (!$this->isBusinessPageEnabled($slug)) {
            return null;
        }

        $policy = getBusinessPolicyConfig($type, $jsonContent);

        if (!$policy || !($policy['status'] ?? 0)) {
            return null;
        }

        return $policy;
    }

    private function isBusinessPageEnabled(string $slug): bool
    {
        return BusinessPage::query()
            ->where('slug', $slug)
            ->where('status', 1)
            ->exists();
    }

}
