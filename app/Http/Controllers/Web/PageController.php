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
use App\Models\Product;
use App\Models\CmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CmsProduct;

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
        $dealers = AboutDealerSection::with('translations')->where('is_active', 1)->latest()->get();
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_about_us'], value: ['status' => '1']);

        return view(VIEW_FILE_NAMES['about_us'], compact('aboutUs', 'pageTitleBanner', 'robotsMetaContentData', 'heroItems', 'whoWeAre', 'products', 'mission', 'timelines', 'dealers'));
    }
    public function getProductShowcaseView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'product_showcase']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }

        $cmsData = CmsProduct::with('translations')->where('is_active', 1)->get();


        $products = Product::where('showcase_product', 1)
            ->where('product_type', 'physical')
            ->where('status', 1)
            ->get();

        $featuredProducts = Product::where('showcase_product', 1)
            ->where('status', 1)
            ->where('product_type', 'physical')
            ->inRandomOrder()
            ->take(4)
            ->get()
            ->shuffle();
        session(['last_featured_refresh' => now()]);


        return view(VIEW_FILE_NAMES['product_showcase'], compact('robotsMetaContentData', 'products', 'featuredProducts', 'cmsData'));
    }
    public function getServicesShowcaseView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'product_showcase']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }

        $cmsData = CmsService::with('translations')->where('is_active', 1)->get();

        $products = Product::with('service')
            ->where('product_type', 'services')
            ->where('showcase_product', 1)
            ->where('status', 1)
            ->get();
        $featuredProducts = Product::with('service')
            ->where('showcase_product', 1)
            ->where('product_type', 'services')
            ->where('status', 1)
            ->get();

        return view(VIEW_FILE_NAMES['services_showcase'], compact('robotsMetaContentData', 'products', 'featuredProducts', 'cmsData'));
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
            $query->where('branch_name', 'like', '%' . $request->search . '%');
        }

        $branchesTable = $query->paginate(10);
        return view(VIEW_FILE_NAMES['contacts'], compact('recaptcha', 'robotsMetaContentData', 'branches', 'branchesTable'));
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
        $refundPolicy = getWebConfig(name: 'refund-policy');
        if (!$refundPolicy['status']) {
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
        $returnPolicy = getWebConfig(name: 'return-policy');
        if (!$returnPolicy['status']) {
            return redirect()->route('home');
        }
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_return_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['return_policy_page'], compact('returnPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getPrivacyPolicyView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'privacy-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $privacyPolicy = getWebConfig(name: 'privacy_policy');
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_privacy_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['privacy_policy_page'], compact('privacyPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getCancellationPolicyView(): View|RedirectResponse
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'cancellation-policy']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $cancellationPolicy = getWebConfig(name: 'cancellation-policy');
        if (!$cancellationPolicy['status']) {
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
        $shippingPolicy = getWebConfig(name: 'shipping-policy');
        if (!$shippingPolicy['status']) {
            return redirect()->route('home');
        }
        $pageTitleBanner = $this->businessSettingRepo->whereJsonContains(params: ['type' => 'banner_shipping_policy'], value: ['status' => '1']);
        return view(VIEW_FILE_NAMES['shipping_policy_page'], compact('shippingPolicy', 'pageTitleBanner', 'robotsMetaContentData'));
    }

    public function getTermsAndConditionView(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'terms']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        $termsCondition = getWebConfig(name: 'terms_condition');
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
        $refund_policy = getWebConfig(name: 'refund-policy');
        $return_policy = $this->normalizePolicyStatus(getWebConfig(name: 'return-policy'));
        $cancellation_policy = getWebConfig(name: 'cancellation-policy');
        $shipping_policy = $this->normalizeShippingPolicy(getWebConfig(name: 'shipping-policy'));

        // String content policies - convert to array
        $privacy_policy = $this->createPolicyFromString('privacy_policy');
        $service_policy = $this->createPolicyFromString('service_policy');

        return view('web-views.pages.our-policies', compact(
            'robotsMetaContentData',
            'refund_policy',
            'return_policy',
            'cancellation_policy',
            'shipping_policy',
            'service_policy',
            'privacy_policy'
        ));
    }

    /**
     * Convert string status to integer
     */
    private function normalizePolicyStatus($policy)
    {
        if (isset($policy['status'])) {
            $policy['status'] = (int) $policy['status'];
        }
        return $policy;
    }

    /**
     * Special handling for shipping policy
     */
    private function normalizeShippingPolicy($policy)
    {
        if (!$policy) return null;

        $record = DB::table('business_settings')
            ->where('type', 'shipping-policy')
            ->first();

        if ($record && $record->is_active == 1 && isset($policy['status'])) {
            $policy['status'] = 1;
        }

        return $this->normalizePolicyStatus($policy);
    }

    /**
     * Create policy array from string content
     */
    private function createPolicyFromString($type)
    {
        $content = getWebConfig(name: $type);
        if (empty($content)) return null;

        $record = DB::table('business_settings')
            ->where('type', $type)
            ->first();

        return [
            'status' => $record->is_active ?? 1,
            'content' => $content
        ];
    }
}
