<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\AboutHeroSection;
use App\Models\AboutWhoWeAreSection;
use App\Models\AboutProductSection;
use App\Models\AboutMissionSection;
use App\Models\AboutTimelineSection;
use App\Models\AboutDealerSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Support\CmsContentSanitizer;
use App\Traits\AuthorizesCmsSection;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;
use App\Utils\ImageManager;
use Illuminate\Validation\ValidationException;

class AboutController extends Controller
{
    use PaginatorTrait;
    use CommonTrait;
    use AuthorizesCmsSection;

    public function __construct(
        private readonly ProductRepositoryInterface     $productRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,

    ) {
        $this->middleware($this->cmsPermissionMiddleware('cms_section.read'))->only(['index', 'pages', 'create', 'edit']);
        $this->middleware($this->cmsPermissionMiddleware('cms_section.create'))->only(['store']);
        $this->middleware($this->cmsPermissionMiddleware('cms_section.update'))->only(['update', 'toggleStatus']);
        $this->middleware($this->cmsPermissionMiddleware('cms_section.delete'))->only(['destroy']);
    }

    private $modelMap = [
        'hero' => AboutHeroSection::class,
        'who_we_are' => AboutWhoWeAreSection::class,
        'products' => AboutProductSection::class,
        'mission' => AboutMissionSection::class,
        'timeline' => AboutTimelineSection::class,
        'dealers' => AboutDealerSection::class,
    ];

    private function getSectionValidationMap(bool $isUpdate): array
    {
        if ($isUpdate) {
            return [
                'hero' => [
                    'heading' => ['message' => 'The_heading_in_english_is_required'],
                    'subheading' => ['message' => 'The_subheading_in_english_is_required'],
                ],
                'who_we_are' => [
                    'title' => ['message' => 'The_title_in_english_is_required'],
                ],
                'mission' => [
                    'title' => ['message' => 'The_title_in_english_is_required'],
                ],
                'products' => [
                    'title' => ['message' => 'The_title_in_english_is_required'],
                ],
                'timeline' => [
                    'title' => ['message' => 'The_title_in_english_is_required'],
                    'year' => ['message' => 'The_year_is_required', 'multilingual' => false],
                ],
                'dealers' => [
                    'dealer_name' => ['message' => 'The_dealer_name_in_english_is_required'],
                ],
            ];
        }

        return [
            'hero' => [
                'heading' => ['message' => 'The_heading_in_english_is_required'],
            ],
            'who_we_are' => [
                'title' => ['message' => 'The_title_in_english_is_required'],
                'content' => ['message' => 'The_content_in_english_is_required', 'rich_text' => true],
            ],
            'mission' => [
                'title' => ['message' => 'The_title_in_english_is_required'],
                'content' => ['message' => 'The_content_in_english_is_required', 'rich_text' => true],
            ],
            'products' => [
                'title' => ['message' => 'The_title_in_english_is_required'],
            ],
            'timeline' => [
                'title' => ['message' => 'The_title_in_english_is_required'],
                'description' => ['message' => 'The_description_in_english_is_required', 'rich_text' => true],
                'year' => ['message' => 'The_year_is_required', 'multilingual' => false],
            ],
            'dealers' => [
                'dealer_name' => ['message' => 'The_dealer_name_in_english_is_required'],
                'location' => ['message' => 'The_location_in_english_is_required'],
                'description' => ['message' => 'The_description_in_english_is_required', 'rich_text' => true],
            ],
        ];
    }

    private function validateSectionRequest(Request $request, string $section, bool $isUpdate = false): void
    {
        $validationMap = $this->getSectionValidationMap($isUpdate)[$section] ?? [];
        if ($validationMap === []) {
            return;
        }

        $englishIndex = getLanguageInputIndex($request, 'en');
        $errors = [];

        foreach ($validationMap as $field => $options) {
            $isMultilingual = $options['multilingual'] ?? true;
            $value = $isMultilingual
                ? $request->input($field . '.' . ($englishIndex ?? 0))
                : $request->input($field);

            $isBlank = ($options['rich_text'] ?? false)
                ? richTextToPlainText($value) === ''
                : trim((string)($value ?? '')) === '';

            if (!$isBlank) {
                continue;
            }

            $errors[$isMultilingual ? $field . '.' . ($englishIndex ?? 0) : $field] = [
                translate($options['message']),
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public function index($section = 'hero')
    {
        if (!array_key_exists($section, $this->modelMap)) {
            $section = 'hero';
        }

        $model = $this->modelMap[$section];
        $data = $model::with('translations')->get();
        $items = $model::with('translations')->latest()->paginate(10);
        return view('admin-views.content-management.about.index', compact('section', 'data', 'items'));
    }
    public function pages($section = 'hero')
    {


        if (!array_key_exists($section, $this->modelMap)) {
            $section = 'hero';
        }

        $model = $this->modelMap[$section];
        $data = $model::with('translations')->get();
        $items = $model::with('translations')->latest()->paginate(10);
        return view('admin-views.content-management.about.index', compact('section', 'data', 'items'));
    }

    public function create(Request $request)
    {
        $section = $request->get('section', 'hero'); // default: hero

        $viewPath = "admin-views.content-management.about.sections.create." . $section;


        return view($viewPath, compact('section'));
    }

    public function store(Request $request, $section)
    {
        $sanitizedInput = [];

        foreach ([
            'heading' => 'sanitizePlainTextArray',
            'subheading' => 'sanitizePlainTextArray',
            'title' => 'sanitizePlainTextArray',
            'label' => 'sanitizePlainTextArray',
            'card_label' => 'sanitizePlainTextArray',
            'card_note' => 'sanitizePlainTextArray',
            'content' => 'sanitizeRichTextArray',
            'description' => 'sanitizeRichTextArray',
            'dealer_name' => 'sanitizePlainTextArray',
            'partner_type' => 'sanitizePlainTextArray',
            'location' => 'sanitizePlainTextArray',
            'coverage_area' => 'sanitizePlainTextArray',
        ] as $field => $method) {
            if ($request->has($field)) {
                $sanitizedInput[$field] = CmsContentSanitizer::$method($request->input($field, []));
            }
        }

        $request->merge($sanitizedInput);
        $this->validateSectionRequest($request, $section);

        $modelMap = [
            'hero' => AboutHeroSection::class,
            'who_we_are' => AboutWhoWeAreSection::class,
            'products' => AboutProductSection::class,
            'mission' => AboutMissionSection::class,
            'timeline' => AboutTimelineSection::class,
            'dealers' => AboutDealerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return back()->withErrors(['Invalid section']);
        }

        $modelClass = $modelMap[$section];

        $defaultLangIndex = getDefaultLanguageIndex($request);

        $input = $request->except('_token', 'lang');

        foreach ($input as $key => $value) {
            if (is_array($value) && isset($value[$defaultLangIndex])) {
                $input[$key] = $value[$defaultLangIndex];
            }
        }

        if ($request->hasFile('image')) {
            $imageName = ImageManager::upload('about/', 'webp', $request->file('image'));
            $input['image'] = 'about/' . $imageName;
        }

        if ($section === 'dealers') {
            $input['show_partner_type_filter'] = $request->boolean('show_partner_type_filter');
            $input['show_location_filter'] = $request->boolean('show_location_filter');
        }

        $model = new $modelClass;
        $model->fill($input);
        $model->save();

        $this->translationRepo->add($request, $modelClass, $model->id);

        return redirect()->route('admin.content-management.about-us.pages', ['section' => $section])
            ->with('success', translate('Data added successfully.'));
    }

    public function destroy($section, $id)
    {
        $modelMap = [
            'hero' => AboutHeroSection::class,
            'who_we_are' => AboutWhoWeAreSection::class,
            'products' => AboutProductSection::class,
            'mission' => AboutMissionSection::class,
            'timeline' => AboutTimelineSection::class,
            'dealers' => AboutDealerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return redirect()->route('admin.content-management.about-us.index')
                ->withErrors(['Invalid section']);
        }
        $model = $modelMap[$section]::findOrFail($id);
        if (!empty($model->image)) {
            ImageManager::delete($model->image);
        }
        $model->delete();

        return redirect()->route('admin.content-management.about-us.pages', ['section' => $section])
            ->with('success', 'Item deleted successfully');
    }

    public function edit($section, $id)
    {
        $modelMap = [
            'hero' => AboutHeroSection::class,
            'who_we_are' => AboutWhoWeAreSection::class,
            'products' => AboutProductSection::class,
            'mission' => AboutMissionSection::class,
            'timeline' => AboutTimelineSection::class,
            'dealers' => AboutDealerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return redirect()->route('admin.content-management.about-us.index')
                ->withErrors(['Invalid section']);
        }

        $model = $modelMap[$section]::with('translations')->findOrFail($id);

        return view("admin-views.content-management.about.sections.edit.$section", compact('model', 'section'));
    }

    public function update(Request $request, $section, $id)
    {
        $sanitizedInput = [];

        foreach ([
            'heading' => 'sanitizePlainTextArray',
            'subheading' => 'sanitizePlainTextArray',
            'title' => 'sanitizePlainTextArray',
            'label' => 'sanitizePlainTextArray',
            'card_label' => 'sanitizePlainTextArray',
            'card_note' => 'sanitizePlainTextArray',
            'content' => 'sanitizeRichTextArray',
            'description' => 'sanitizeRichTextArray',
            'dealer_name' => 'sanitizePlainTextArray',
            'partner_type' => 'sanitizePlainTextArray',
            'location' => 'sanitizePlainTextArray',
            'coverage_area' => 'sanitizePlainTextArray',
        ] as $field => $method) {
            if ($request->has($field)) {
                $sanitizedInput[$field] = CmsContentSanitizer::$method($request->input($field, []));
            }
        }

        $request->merge($sanitizedInput);
        $this->validateSectionRequest($request, $section, true);

        $modelMap = [
            'hero' => AboutHeroSection::class,
            'who_we_are' => AboutWhoWeAreSection::class,
            'products' => AboutProductSection::class,
            'mission' => AboutMissionSection::class,
            'timeline' => AboutTimelineSection::class,
            'dealers' => AboutDealerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return back()->withErrors(['Invalid section']);
        }

        $modelClass = $modelMap[$section];
        $model = $modelClass::findOrFail($id);


        $defaultLangIndex = getDefaultLanguageIndex($request);

        $data = $request->except('_token', '_method', 'lang', 'heading', 'subheading', 'title', 'label', 'card_label', 'card_note', 'content', 'description', 'section');

        if ($defaultLangIndex !== false) {
            if ($request->has('heading')) {
                $data['heading'] = $request->input('heading')[$defaultLangIndex];
            }
            if ($request->has('subheading')) {
                $data['subheading'] = $request->input('subheading')[$defaultLangIndex];
            }
            if ($request->has('title')) {
                $data['title'] = $request->input('title')[$defaultLangIndex];
            }
            if ($request->has('label')) {
                $data['label'] = $request->input('label')[$defaultLangIndex];
            }
            if ($request->has('card_label')) {
                $data['card_label'] = $request->input('card_label')[$defaultLangIndex];
            }
            if ($request->has('card_note')) {
                $data['card_note'] = $request->input('card_note')[$defaultLangIndex];
            }
            if ($request->has('content')) {
                $data['content'] = $request->input('content')[$defaultLangIndex];
            }
            if ($request->has('description')) {
                $data['description'] = $request->input('description')[$defaultLangIndex];
            }
            if ($request->has('dealer_name')) {
                $data['dealer_name'] = $request->input('dealer_name')[$defaultLangIndex];
            }
            if ($request->has('partner_type')) {
                $data['partner_type'] = $request->input('partner_type')[$defaultLangIndex];
            }
            if ($request->has('location')) {
                $data['location'] = $request->input('location')[$defaultLangIndex];
            }
            if ($request->has('coverage_area')) {
                $data['coverage_area'] = $request->input('coverage_area')[$defaultLangIndex];
            }
        }

        if ($request->remove_image == 1) {
            if ($model->image) {
                ImageManager::delete($model->image);
            }

            $data['image'] = null; // set column to null
        }

        if ($request->hasFile('image')) {
            if (!empty($model->image)) {
                ImageManager::delete($model->image);
            }
            $imageName = ImageManager::upload('about/', 'webp', $request->file('image'));
            $data['image'] = 'about/' . $imageName;
        }

        if ($section === 'dealers') {
            $data['show_partner_type_filter'] = $request->boolean('show_partner_type_filter');
            $data['show_location_filter'] = $request->boolean('show_location_filter');
        }

        $model->fill($data);
        $model->save();


        $this->translationRepo->update($request, $modelClass, $id);

        return redirect()->back()
            ->with('success', 'Data updated successfully.');
    }


    public function toggleStatus(Request $request)
    {

        $request->validate([
            'id' => 'required|integer',
            'section' => 'required|string',
        ]);

        $section = $request->input('section');
        $id = $request->input('id');

        $modelMap = [
            'hero' => AboutHeroSection::class,
            'who_we_are' => AboutWhoWeAreSection::class,
            'products' => AboutProductSection::class,
            'mission' => AboutMissionSection::class,
            'timeline' => AboutTimelineSection::class,
            'dealers' => AboutDealerSection::class,
        ];

        if (!isset($modelMap[$section])) {
            return response()->json(['message' => 'Invalid section.'], 400);
        }

        $modelClass = $modelMap[$section];
        $item = $modelClass::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $item->is_active = $item->is_active ? 0 : 1;
        $item->save();

        return response()->json(['message' => 'Status updated successfully.']);
    }
}
