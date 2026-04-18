<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\CmsProduct;
use App\Models\CmsProductShowcaseItem;
use App\Support\CmsContentSanitizer;
use App\Traits\AuthorizesCmsSection;
use App\Traits\ValidatesCmsEnglishMultilingualInput;
use App\Utils\ImageManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductCmsController extends Controller
{
    use AuthorizesCmsSection;
    use ValidatesCmsEnglishMultilingualInput;

    private const HEADER_SECTION_TYPE = 'main_banner';
    private const HERO_SECTION_TYPE = 'core_product_slider';
    private const SHOWCASE_SECTION_TYPE = 'feature_product';
    private const HERO_SLIDE_TYPE = 'hero';
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

    public function __construct(
        private readonly TranslationRepositoryInterface $translationRepo,
    ) {
        $this->middleware($this->cmsPermissionMiddleware('cms_section.read'))->only(['index']);
        $this->middleware($this->cmsPermissionMiddleware('cms_section.update'))->only([
            'update',
            'toggleStatus',
            'storeShowcaseItem',
            'updateShowcaseItem',
            'destroyShowcaseItem',
        ]);
    }

    public function index()
    {
        $sections = CmsProduct::with(['translations', 'showcaseItems.translations'])
            ->whereIn('type', self::SECTION_ORDER)
            ->get()
            ->sortBy(fn (CmsProduct $item) => array_search($item->type, self::SECTION_ORDER, true))
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

        return view('admin-views.content-management.products.index', [
            'headerSection' => $sections->get(self::HEADER_SECTION_TYPE),
            'heroSection' => $heroSection,
            'showcaseSection' => $showcaseSection,
            'supportSections' => $supportSections,
            'showcaseCardTypes' => self::SHOWCASE_CARD_TYPES,
        ]);
    }

    public function storeShowcaseItem(Request $request, $id)
    {
        $section = CmsProduct::query()->findOrFail($id);
        abort_unless($this->sectionSupportsSlides($section->type), 404);
        $isShowcaseSection = $section->type === self::SHOWCASE_SECTION_TYPE;

        $validated = $request->validate($this->showcaseItemRules($isShowcaseSection));

        $preparedRequest = $this->prepareShowcaseItemRequest($request);
        $this->validateRequiredCmsEnglishFields($preparedRequest, [
            'title' => ['message' => 'The_title_in_english_is_required'],
            'description' => ['message' => 'The_description_in_english_is_required'],
        ]);

        $defaultLangIndex = getDefaultLanguageIndex($preparedRequest);

        $item = new CmsProductShowcaseItem();
        $item->cms_product_id = $section->id;
        $item->card_type = $isShowcaseSection ? $validated['card_type'] : self::HERO_SLIDE_TYPE;
        $item->sort_order = (int) ($validated['sort_order'] ?? ((int) CmsProductShowcaseItem::query()
            ->where('cms_product_id', $section->id)
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

        $this->translationRepo->add($preparedRequest, CmsProductShowcaseItem::class, $item->id);

        return redirect()->route('admin.content-management.products')
            ->with('success', translate($isShowcaseSection ? 'Showcase_card_added_successfully' : 'Hero_slide_added_successfully'));
    }

    public function updateShowcaseItem(Request $request, $id)
    {
        $item = CmsProductShowcaseItem::query()->with('section')->findOrFail($id);
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

        $this->translationRepo->update($preparedRequest, CmsProductShowcaseItem::class, $item->id);

        return redirect()->route('admin.content-management.products')
            ->with('success', translate($isShowcaseSection ? 'Showcase_card_updated_successfully' : 'Hero_slide_updated_successfully'));
    }

    public function destroyShowcaseItem($id)
    {
        $item = CmsProductShowcaseItem::query()->with('section')->findOrFail($id);
        abort_unless($item->section && $this->sectionSupportsSlides($item->section->type), 404);
        $isShowcaseSection = $item->section->type === self::SHOWCASE_SECTION_TYPE;

        if (!empty($item->image)) {
            ImageManager::delete($item->image);
        }

        $this->translationRepo->delete(CmsProductShowcaseItem::class, $item->id);
        $item->delete();

        return redirect()->route('admin.content-management.products')
            ->with('success', translate($isShowcaseSection ? 'Showcase_card_deleted_successfully' : 'Hero_slide_deleted_successfully'));
    }

    public function update(Request $request, $id)
    {
        $section = CmsProduct::query()->findOrFail($id);

        $request->validate($this->sectionRules($section));

        $preparedRequest = $this->prepareSectionRequest($request, $section);
        $this->validateRequiredCmsEnglishFields($preparedRequest, [
            'heading' => ['message' => 'The_heading_in_english_is_required'],
            'description' => ['message' => 'The_description_in_english_is_required'],
        ]);

        $defaultLangIndex = getDefaultLanguageIndex($preparedRequest);

        if ($defaultLangIndex !== false) {
            $section->heading = $preparedRequest->heading[$defaultLangIndex] ?? $section->heading;
            $section->description = $preparedRequest->description[$defaultLangIndex] ?? $section->description;

            if ($section->type === self::HEADER_SECTION_TYPE || $this->isSupportSection($section->type)) {
                $section->button_text = $preparedRequest->button_text[$defaultLangIndex] ?? null;
            }
        }

        if ($this->sectionSupportsImage($section->type)) {
            $this->syncModelImage($section, $request);
        }

        if ($this->sectionSupportsLink($section->type)) {
            $section->button_link = $preparedRequest->button_link;
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
            request: $preparedRequest,
            model: CmsProduct::class,
            id: $id
        );

        return redirect()->route('admin.content-management.products')
            ->with('success', translate('Updated_Successfully'));
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $section = CmsProduct::query()->findOrFail($request->integer('id'));
        $section->is_active = $section->is_active == 1 ? 0 : 1;
        $section->save();

        return response()->json([
            'status' => true,
            'message' => translate('Status_updated_successfully'),
            'new_status' => $section->is_active,
        ]);
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

    private function sectionRules(CmsProduct $section): array
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

    private function prepareSectionRequest(Request $request, CmsProduct $section): Request
    {
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

        return $request;
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

    private function syncModelImage(CmsProduct|CmsProductShowcaseItem $model, Request $request): void
    {
        if ($request->hasFile('image')) {
            if (!empty($model->image)) {
                ImageManager::delete($model->image);
            }

            $imageName = ImageManager::upload('cms-product/', 'webp', $request->file('image'));
            $model->image = 'cms-product/' . $imageName;

            return;
        }

        if ((int) $request->input('remove_image', 0) === 1 && !empty($model->image)) {
            ImageManager::delete($model->image);
            $model->image = null;
        }
    }

    private function isSupportSection(string $type): bool
    {
        return in_array($type, self::SUPPORT_SECTION_TYPES, true);
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
}
