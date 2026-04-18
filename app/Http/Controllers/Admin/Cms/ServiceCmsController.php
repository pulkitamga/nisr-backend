<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsService;
use App\Models\CmsServiceShowcaseItem;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Support\CmsContentSanitizer;
use App\Traits\AuthorizesCmsSection;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;
use App\Traits\ValidatesCmsEnglishMultilingualInput;
use App\Utils\ImageManager;
use Illuminate\Validation\Rule;

class ServiceCmsController extends Controller
{


    use PaginatorTrait;
    use CommonTrait;
    use AuthorizesCmsSection;
    use ValidatesCmsEnglishMultilingualInput;

    private const HEADER_SECTION_TYPE = 'main_banner';
    private const HERO_SECTION_TYPE = 'hero_slider';
    private const SHOWCASE_SECTION_TYPE = 'service_showcase';
    private const SUPPORT_SECTION_TYPES = [
        'request_card_1',
        'request_card_2',
        'request_card_3',
    ];
    private const SECTION_ORDER = [
        self::HEADER_SECTION_TYPE,
        self::HERO_SECTION_TYPE,
        self::SHOWCASE_SECTION_TYPE,
        'request_card_1',
        'request_card_2',
        'request_card_3',
    ];
    private const SHOWCASE_CARD_TYPES = [
        'product',
        'category',
        'case',
        'problem',
        'situation',
    ];
    private const HERO_SLIDE_TYPE = 'hero';

    public function __construct(
        private readonly ProductRepositoryInterface     $productRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,

    ) {
        $this->middleware($this->cmsPermissionMiddleware('cms_section.read'))->only(['index', 'edit']);
        $this->middleware($this->cmsPermissionMiddleware('cms_section.update'))->only(['update', 'toggleStatus']);
    }


    public function index(Request $request)
    {
        $sections = CmsService::with(['translations', 'showcaseItems.translations'])
            ->whereIn('type', self::SECTION_ORDER)
            ->get()
            ->sortBy(fn (CmsService $item) => array_search($item->type, self::SECTION_ORDER, true))
            ->keyBy('type');

        $showcaseSection = $sections->get(self::SHOWCASE_SECTION_TYPE);
        if ($showcaseSection) {
            $showcaseSection->setRelation(
                'showcaseItems',
                $showcaseSection->showcaseItems->sortBy('sort_order')->values()
            );
        }

        $heroSection = $sections->get(self::HERO_SECTION_TYPE);
        if ($heroSection) {
            $heroSection->setRelation(
                'showcaseItems',
                $heroSection->showcaseItems->sortBy('sort_order')->values()
            );
        }

        $supportSections = collect(self::SUPPORT_SECTION_TYPES)
            ->map(fn (string $type) => $sections->get($type))
            ->filter()
            ->values();

        return view('admin-views.content-management.services.index', [
            'headerSection' => $sections->get(self::HEADER_SECTION_TYPE),
            'heroSection' => $heroSection,
            'showcaseSection' => $showcaseSection,
            'supportSections' => $supportSections,
            'showcaseCardTypes' => self::SHOWCASE_CARD_TYPES,
        ]);
    }

    public function create()
    {
        return redirect()->route('admin.content-management.services');
    }


    

    public function edit($id)
    {
        return redirect()->route('admin.content-management.services');
    }

   
    public function update(Request $request, $id)
    {
        $section = CmsService::findOrFail($id);

        $request->validate($this->sectionRules($section));

        $request->merge([
            'heading' => CmsContentSanitizer::sanitizePlainTextArray($request->input('heading', [])),
            'description' => CmsContentSanitizer::sanitizeRichTextArray($request->input('description', [])),
        ]);

        if ($this->sectionUsesButtonText($section->type)) {
            $request->merge([
                'button_text' => CmsContentSanitizer::sanitizePlainTextArray($request->input('button_text', [])),
            ]);
        }

        if ($this->sectionSupportsLink($section->type)) {
            $request->merge([
                'button_link' => CmsContentSanitizer::sanitizeLink((string) $request->input('button_link', '')),
            ]);
        }

        $this->validateRequiredCmsEnglishFields($request, [
            'heading' => ['message' => 'The_heading_in_english_is_required'],
        ]);

        $defaultLangIndex = getDefaultLanguageIndex($request);
        if ($defaultLangIndex !== false) {
            $section->heading = $request->heading[$defaultLangIndex] ?? $section->heading;
            $section->description = $request->description[$defaultLangIndex] ?? $section->description;

            if ($this->sectionUsesButtonText($section->type)) {
                $section->button_text = $request->button_text[$defaultLangIndex] ?? null;
            }
        }

        if ($this->sectionSupportsImage($section->type)) {
            $this->syncModelImage($section, $request);
        }

        if ($this->sectionSupportsLink($section->type)) {
            $section->button_link = $request->button_link;
        } else {
            $section->button_link = null;
        }

        if (!$this->sectionUsesButtonText($section->type)) {
            $section->button_text = null;
        }

        if (!$this->sectionSupportsImage($section->type)) {
            $section->image = null;
        }

        $section->save();

        $this->translationRepo->update(
            request: $request,
            model: CmsService::class,
            id: $id
        );

        return redirect()->route('admin.content-management.services')->with('success', translate('Updated_Successfully'));
    }

    public function storeShowcaseItem(Request $request, $id)
    {
        $section = CmsService::query()->findOrFail($id);
        abort_unless($this->sectionSupportsSlides($section->type), 404);
        $isShowcaseSection = $section->type === self::SHOWCASE_SECTION_TYPE;

        $validated = $request->validate($this->showcaseItemRules($isShowcaseSection));

        $preparedRequest = $this->prepareShowcaseItemRequest($request);
        $this->validateRequiredCmsEnglishFields($preparedRequest, [
            'title' => ['message' => 'The_title_in_english_is_required'],
            'description' => ['message' => 'The_description_in_english_is_required'],
        ]);

        $defaultLangIndex = getDefaultLanguageIndex($preparedRequest);

        $item = new CmsServiceShowcaseItem();
        $item->cms_service_id = $section->id;
        $item->card_type = $isShowcaseSection ? $validated['card_type'] : self::HERO_SLIDE_TYPE;
        $item->sort_order = (int) ($validated['sort_order'] ?? ((int) CmsServiceShowcaseItem::query()
            ->where('cms_service_id', $section->id)
            ->max('sort_order')) + 1);
        $item->is_active = $request->boolean('is_active', true);

        if ($defaultLangIndex !== false) {
            $item->title = $preparedRequest->title[$defaultLangIndex] ?? '';
            $item->description = $preparedRequest->description[$defaultLangIndex] ?? '';
            $item->primary_button_text = $preparedRequest->primary_button_text[$defaultLangIndex] ?? null;
        }
        $item->primary_button_link = $preparedRequest->primary_button_link;

        $this->syncModelImage($item, $request);
        $item->save();

        $this->translationRepo->add($preparedRequest, CmsServiceShowcaseItem::class, $item->id);

        return redirect()->route('admin.content-management.services')
            ->with('success', translate($isShowcaseSection ? 'Showcase_card_added_successfully' : 'Hero_slide_added_successfully'));
    }

    public function updateShowcaseItem(Request $request, $id)
    {
        $item = CmsServiceShowcaseItem::query()->with('section')->findOrFail($id);
        abort_unless($item->section && $this->sectionSupportsSlides($item->section->type), 404);
        $isShowcaseSection = $item->section->type === self::SHOWCASE_SECTION_TYPE;

        $validated = $request->validate($this->showcaseItemRules($isShowcaseSection));

        $preparedRequest = $this->prepareShowcaseItemRequest($request);
        $this->validateRequiredCmsEnglishFields($preparedRequest, [
            'title' => ['message' => 'The_title_in_english_is_required'],
            'description' => ['message' => 'The_description_in_english_is_required'],
        ]);

        $defaultLangIndex = getDefaultLanguageIndex($preparedRequest);

        $item->card_type = $isShowcaseSection ? $validated['card_type'] : self::HERO_SLIDE_TYPE;
        $item->sort_order = (int) ($validated['sort_order'] ?? $item->sort_order);
        $item->is_active = $request->boolean('is_active');

        if ($defaultLangIndex !== false) {
            $item->title = $preparedRequest->title[$defaultLangIndex] ?? $item->title;
            $item->description = $preparedRequest->description[$defaultLangIndex] ?? $item->description;
            $item->primary_button_text = $preparedRequest->primary_button_text[$defaultLangIndex] ?? null;
        }
        $item->primary_button_link = $preparedRequest->primary_button_link;

        $this->syncModelImage($item, $request);
        $item->save();

        $this->translationRepo->update($preparedRequest, CmsServiceShowcaseItem::class, $item->id);

        return redirect()->route('admin.content-management.services')
            ->with('success', translate($isShowcaseSection ? 'Showcase_card_updated_successfully' : 'Hero_slide_updated_successfully'));
    }

    public function destroyShowcaseItem($id)
    {
        $item = CmsServiceShowcaseItem::query()->with('section')->findOrFail($id);
        abort_unless($item->section && $this->sectionSupportsSlides($item->section->type), 404);
        $isShowcaseSection = $item->section->type === self::SHOWCASE_SECTION_TYPE;

        if (!empty($item->image)) {
            ImageManager::delete($item->image);
        }

        $this->translationRepo->delete(CmsServiceShowcaseItem::class, $item->id);
        $item->delete();

        return redirect()->route('admin.content-management.services')
            ->with('success', translate($isShowcaseSection ? 'Showcase_card_deleted_successfully' : 'Hero_slide_deleted_successfully'));
    }

    public function toggleStatus(Request $request)
    {

        $request->validate([
            'id' => 'required|integer',
        ]);

        $id = $request->id;
        
        $product = CmsService::findOrFail($id);

        // Toggle the status
        $product->is_active = $product->is_active == 1 ? 0 : 1;
        $product->save();

        return response()->json([
            'status' => true,
            'message' => translate('Status_updated_successfully'),
            'new_status' => $product->is_active
        ]);
    }

    private function sectionRules(CmsService $section): array
    {
        $rules = [
            'heading' => 'required|array',
            'heading.*' => 'nullable|string|max:255',
            'description' => 'required|array',
            'description.*' => 'nullable|string',
            'lang' => 'required|array',
        ];

        if ($this->sectionUsesButtonText($section->type)) {
            $rules['button_text'] = 'nullable|array';
            $rules['button_text.*'] = 'nullable|string|max:255';
        }

        if ($this->sectionSupportsLink($section->type)) {
            $rules['button_link'] = [
                'nullable',
                'string',
                'max:500',
                static function ($attribute, $value, $fail) {
                    if ($value !== null && trim((string) $value) !== '' && CmsContentSanitizer::sanitizeLink($value) === '') {
                        $fail(translate('invalid_URL'));
                    }
                },
            ];
        }

        if ($this->sectionSupportsImage($section->type)) {
            $rules['image'] = 'nullable|image|mimes:jpg,jpeg,png,webp';
            $rules['remove_image'] = 'nullable|boolean';
        }

        return $rules;
    }

    private function sectionSupportsImage(string $type): bool
    {
        return $type === self::HERO_SECTION_TYPE || $this->isSupportSection($type);
    }

    private function sectionSupportsLink(string $type): bool
    {
        return $type === self::HERO_SECTION_TYPE || $this->isSupportSection($type);
    }

    private function sectionUsesButtonText(string $type): bool
    {
        return $type === self::HEADER_SECTION_TYPE || $type === self::HERO_SECTION_TYPE || $this->isSupportSection($type);
    }

    private function sectionSupportsSlides(string $type): bool
    {
        return in_array($type, [self::HERO_SECTION_TYPE, self::SHOWCASE_SECTION_TYPE], true);
    }

    private function isSupportSection(string $type): bool
    {
        return in_array($type, self::SUPPORT_SECTION_TYPES, true);
    }

    private function showcaseItemRules(bool $requiresCardType = true): array
    {
        $rules = [
            'title' => 'required|array',
            'title.*' => 'nullable|string|max:255',
            'description' => 'required|array',
            'description.*' => 'nullable|string',
            'primary_button_text' => 'nullable|array',
            'primary_button_text.*' => 'nullable|string|max:255',
            'primary_button_link' => [
                'nullable',
                'string',
                'max:500',
                static function ($attribute, $value, $fail) {
                    if ($value !== null && trim((string) $value) !== '' && CmsContentSanitizer::sanitizeLink($value) === '') {
                        $fail(translate('invalid_URL'));
                    }
                },
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'remove_image' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'lang' => 'required|array',
        ];

        if ($requiresCardType) {
            $rules['card_type'] = ['required', 'string', Rule::in(self::SHOWCASE_CARD_TYPES)];
        }

        return $rules;
    }

    private function prepareShowcaseItemRequest(Request $request): Request
    {
        $request->merge([
            'title' => CmsContentSanitizer::sanitizePlainTextArray($request->input('title', [])),
            'description' => CmsContentSanitizer::sanitizeRichTextArray($request->input('description', [])),
            'primary_button_text' => CmsContentSanitizer::sanitizePlainTextArray($request->input('primary_button_text', [])),
            'primary_button_link' => CmsContentSanitizer::sanitizeLink((string) $request->input('primary_button_link', '')),
        ]);

        return $request;
    }

    private function syncModelImage(CmsService|CmsServiceShowcaseItem $model, Request $request): void
    {
        if ($request->hasFile('image')) {
            if (!empty($model->image)) {
                ImageManager::delete($model->image);
            }

            $imageName = ImageManager::upload('cms-service/', 'webp', $request->file('image'));
            $model->image = 'cms-service/' . $imageName;

            return;
        }

        if ((int) $request->input('remove_image', 0) === 1 && !empty($model->image)) {
            ImageManager::delete($model->image);
            $model->image = null;
        }
    }
}
