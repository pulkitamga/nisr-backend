<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\HomePageSection;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Support\CmsContentSanitizer;
use App\Traits\AuthorizesCmsSection;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;
use App\Traits\ValidatesCmsEnglishMultilingualInput;
use App\Utils\ImageManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;




class HomeController extends Controller
{

    use PaginatorTrait;
    use CommonTrait;
    use AuthorizesCmsSection;
    use ValidatesCmsEnglishMultilingualInput;

    public function __construct(
        private readonly TranslationRepositoryInterface     $translationRepo,

    ) {
        $this->middleware($this->cmsPermissionMiddleware('cms_section.read'))->only(['index']);
        $this->middleware($this->cmsPermissionMiddleware('cms_section.update'))->except(['index']);
    }

    private function storeOptimizedImage(UploadedFile $image, string $directory): string
    {
        $normalizedDirectory = trim($directory, '/') . '/';
        $fileName = ImageManager::upload($normalizedDirectory, 'webp', $image);

        return 'storage/' . $normalizedDirectory . $fileName;
    }

    private function deleteImageIfExists(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $candidatePath = $path;
        if (filter_var($candidatePath, FILTER_VALIDATE_URL)) {
            $candidatePath = parse_url($candidatePath, PHP_URL_PATH) ?? '';
        }

        $candidatePath = ltrim((string)$candidatePath, '/');
        if ($candidatePath === '' || str_contains($candidatePath, '..')) {
            return;
        }

        $diskCandidates = [];
        if (str_starts_with($candidatePath, 'storage/')) {
            $diskCandidates[] = substr($candidatePath, strlen('storage/'));
        }
        $diskCandidates[] = $candidatePath;
        if (!str_contains($candidatePath, '/')) {
            $diskCandidates[] = 'uploads/' . $candidatePath;
        }

        foreach (array_unique($diskCandidates) as $diskPath) {
            if ($diskPath !== '' && Storage::disk('public')->exists($diskPath)) {
                Storage::disk('public')->delete($diskPath);
                return;
            }
        }

        $publicCandidates = [$candidatePath];
        if (!str_contains($candidatePath, '/')) {
            $publicCandidates[] = 'uploads/' . $candidatePath;
        }

        foreach (array_unique($publicCandidates) as $publicPath) {
            $absolutePath = public_path($publicPath);
            $resolvedPath = realpath($absolutePath);
            $publicRoot = realpath(public_path());

            if (
                $resolvedPath !== false
                && $publicRoot !== false
                && str_starts_with($resolvedPath, $publicRoot)
                && file_exists($resolvedPath)
            ) {
                unlink($resolvedPath);
                return;
            }
        }
    }

    private function validateRequiredWhyJoinUsCardTitles(Request $request): void
    {
        $defaultLanguage = getConfiguredDefaultLanguage();
        $cards = $request->input('cards', []);
        $errors = [];

        foreach ($cards as $index => $card) {
            $title = trim((string) data_get($card, 'title.' . $defaultLanguage, ''));
            if ($title === '') {
                $errors["cards.$index.title.$defaultLanguage"] = [translate('The_title_in_english_is_required')];
            }
        }

        if ($errors !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    public function index(Request $request)
    {

        $sections = HomePageSection::all();

        $typeList = $sections->pluck('name', 'type')->toArray();

        $defaultType = 'main_banner';

        $currentType = $request->get('section', $defaultType);

        $currentSection = $sections->where('type', $currentType)->first();

        $jsonData = $currentSection ? json_decode($currentSection->value, true) : [];

        $translations = [];

        if ($currentSection) {
            foreach ($currentSection->translations as $trans) {
                $locale = $trans->locale;
                $key = $trans->key;
                $value = $trans->value;
                $index = $trans->item_index;

                if (!isset($translations[$locale])) {
                    $translations[$locale] = [];
                }

                if ((int)$index === -1) {
                    if (!isset($translations[$locale]['section'])) {
                        $translations[$locale]['section'] = [];
                    }
                    $translations[$locale]['section'][$key] = $value;
                } elseif (is_numeric($index) && (int)$index >= 0) {
                    if (!isset($translations[$locale]['cards'][$index])) {
                        $translations[$locale]['cards'][$index] = [];
                    }
                    $translations[$locale]['cards'][$index][$key] = $value;
                } else {
                    $translations[$locale][$key] = $value;
                }
            }
        }

        return view('admin-views.content-management.home.index', compact(
            'typeList',
            'currentType',
            'currentSection',
            'jsonData',
            'translations'
        ));
    }


    public function updateTrustedBy(Request $request, $index)
    {
        $this->validateRequiredCmsEnglishFields($request, [
            'heading' => ['message' => 'The_heading_in_english_is_required'],
            'paragraph' => ['message' => 'The_description_in_english_is_required'],
        ]);

        $section = HomePageSection::where('type', 'trusted_by')->first();

        if (!$section) {
            return back()->withErrors(['msg' => 'Section not found']);
        }

        $data = json_decode($section->value, true);

        if (!isset($data[$index])) {
            return back()->withErrors(['msg' => 'Invalid index']);
        }

        $defaultLangIndex = getDefaultLanguageIndex($request);

        $data[$index]['heading'] = $request->heading[$defaultLangIndex] ?? '';
        $data[$index]['paragraph'] = $request->paragraph[$defaultLangIndex] ?? '';
        $data[$index]['year'] = $request->year;
        $section->value = json_encode($data, JSON_UNESCAPED_UNICODE);
        $section->save();

        $this->translationRepo->update(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );
        Toastr::success('Section updated successfully!');

        return back();
    }



    public function updateProducts(Request $request, $index)
    {
        $section = HomePageSection::where('type', 'products')->first();

        if (!$section) {
            return back()->withErrors(['msg' => 'Section not found']);
        }

        $data = json_decode($section->value, true);

        if (!isset($data[$index])) {
            return back()->withErrors(['msg' => 'Invalid index']);
        }
        $defaultLangIndex = getDefaultLanguageIndex($request);

        $data[$index]['section_title'] = $request->section_title[$defaultLangIndex];
        $data[$index]['section_paragraph'] = $request->section_paragraph[$defaultLangIndex];

        $section->value = json_encode($data, JSON_UNESCAPED_UNICODE);
        $section->save();


        $this->translationRepo->update(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );
        Toastr::success('Section updated successfully!');

        return back();
    }

    public function storeClientReview(Request $request)
    {
        $request->validate([
            'name' => 'required|array|max:255',
            'review' => 'required|array',
            'rating' => 'required',
            'image' => 'required|image|max:2048',
        ]);

        $defaultLangIndex = getDefaultLanguageIndex($request);
        $this->validateRequiredCmsEnglishFields($request, [
            'name' => ['message' => 'The_name_in_english_is_required'],
            'review' => ['message' => 'The_description_in_english_is_required'],
        ]);

        $imageUrl = $this->storeOptimizedImage(
            image: $request->file('image'),
            directory: 'reviews'
        );

        $section = HomePageSection::where('type', 'client_review')->first();
        $data = json_decode($section->value ?? '{}', true);
        $data['clients'] = $data['clients'] ?? [];

        $data['clients'][] = [
            'name' => $request->name[$defaultLangIndex],
            'review' => $request->review[$defaultLangIndex],
            'rating' => $request->rating,
            'image' => $imageUrl,
        ];

        $section->value = json_encode($data, JSON_UNESCAPED_UNICODE);
        $section->save();

        $newIndex = count($data['clients']) - 1;

        $request->merge(['index' => $newIndex]);

        $this->translationRepo->createArrayBasedSectionTranslations(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );

        Toastr::success('Review added successfully!');

        return back();
    }


    public function updateClientReview(Request $request)
    {
        $validated = $request->validate([
            'index' => 'required|integer',
            'rating' => 'required',
            'name' => 'required|array',
            'review' => 'required|array',
        ]);

        $section = HomePageSection::where('type', 'client_review')->first();
        $data = $section ? json_decode($section->value, true) : ['clients' => []];
        $defaultLangIndex = getDefaultLanguageIndex($request);
        $this->validateRequiredCmsEnglishFields($request, [
            'name' => ['message' => 'The_name_in_english_is_required'],
            'review' => ['message' => 'The_description_in_english_is_required'],
        ]);

        if (!isset($data['clients'][$validated['index']])) {
            return redirect()->back()->withErrors('Review not found.');
        }

        if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
            $this->deleteImageIfExists($data['clients'][$validated['index']]['image'] ?? null);
            $imageUrl = $this->storeOptimizedImage(
                image: $request->file('image_file'),
                directory: 'reviews'
            );
        } else {
            $imageUrl = $request->input('image_url', $data['clients'][$validated['index']]['image'] ?? '');
        }

        $data['clients'][$validated['index']] = [
            'rating' => $validated['rating'],
            'name' => $validated['name'][$defaultLangIndex],
            'review' => $validated['review'][$defaultLangIndex],
            'image' => $imageUrl,
        ];

        $section->value = json_encode($data);
        $section->save();
        $this->translationRepo->updateArrayBasedSectionTranslations(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );
        return redirect()->back()->with('success', 'Review updated successfully.');
    }
    public function deleteClientReview(Request $request)
    {
        $validated = $request->validate([
            'index' => 'required|integer',
        ]);
        $section = HomePageSection::where('type', 'client_review')->first();
        $data = $section ? json_decode($section->value, true) : ['clients' => []];

        if (!isset($data['clients'][$validated['index']])) {
            return redirect()->back()->withErrors('Review not found.');
        }
        $this->deleteImageIfExists($data['clients'][$validated['index']]['image'] ?? null);
        array_splice($data['clients'], $validated['index'], 1);
        $section->value = json_encode($data);
        $section->save();

        return redirect()->back()->with('success', 'Review deleted successfully.');
    }

    public function editWhyChooseCard($index)
    {
        $section = HomePageSection::where('type', 'why_choose_us')->first();
        $data = $section ? json_decode($section->value, true) : ['section' => ['cards' => []]];
        $cards = $data['section']['cards'] ?? [];

        if (!isset($cards[$index])) {
            Toastr::error('Card not found.');
            return redirect()->route('admin.content-management.home', ['section' => 'why_choose_us']);
        }

        $card = $cards[$index];
        $languages = getWebConfig('pnc_language') ?? [];
        $baseLanguage = getConfiguredDefaultLanguage();

        $translations = [];
        if ($section) {
            foreach ($section->translations as $trans) {
                if ($trans->item_index == $index) {
                    $translations[$trans->locale][$trans->key] = $trans->value;
                }
            }
        }

        return view('admin-views.content-management.home.partials.why-choose-us-edit-card', compact('card', 'index', 'languages', 'baseLanguage', 'translations'));
    }

    public function updateWhyChoose(Request $request)
    {
        $validated = $request->validate([
            'index'          => 'required|integer',
            'title'          => 'required|array',
            'description'    => 'required|array',
            'icon_name'      => 'required|string',
            'icon_color'     => 'nullable|string',
            'icon_animation' => 'nullable|string',
            'lang'           => 'required|array',
        ]);

        $section = HomePageSection::where('type', 'why_choose_us')->first();
        $data = $section ? json_decode($section->value, true) : ['section' => ['cards' => []]];
        $this->validateRequiredCmsEnglishFields($request, [
            'title' => ['message' => 'The_title_in_english_is_required'],
            'description' => ['message' => 'The_description_in_english_is_required'],
        ]);

        if (!isset($data['section']['cards'][$validated['index']])) {
            return redirect()->back()->withErrors('Card not found.');
        }
        $defaultIndex = getDefaultLanguageIndex(['lang' => $validated['lang']]);
        $data['section']['cards'][$validated['index']]['title'] = $validated['title'][$defaultIndex] ?? '';
        $data['section']['cards'][$validated['index']]['description'] = $validated['description'][$defaultIndex] ?? '';

        // ✅ Update icon info
        $data['section']['cards'][$validated['index']]['icon'] = [
            'type'      => 'svg',
            'name'      => $validated['icon_name'],
            'color'     => $validated['icon_color'] ?? '',
            'animation' => $validated['icon_animation'] ?? '',
        ];

        $section->value = json_encode($data, JSON_UNESCAPED_UNICODE);
        $section->save();

        $this->translationRepo->updateArrayBasedSectionTranslations($request, HomePageSection::class, $section->id);
        Toastr::success('Card updated successfully.');
        return redirect()->route('admin.content-management.home', ['section' => 'why_choose_us']);
    }




    public function deleteWhyChoose(Request $request)
    {
        $validated = $request->validate([
            'index' => 'required|integer',
        ]);

        $section = HomePageSection::where('type', 'why_choose_us')->first();
        $data = $section ? json_decode($section->value, true) : ['section' => ['cards' => []]];

        if (!isset($data['section']['cards'][$validated['index']])) {
            return redirect()->back()->withErrors('Card not found.');
        }

        array_splice($data['section']['cards'], $validated['index'], 1);

        $section->value = json_encode($data);
        $section->save();

        return redirect()->back()->with('success', 'Card deleted successfully.');
    }

    public function updateWhyJoinUs(Request $request)
    {
        $section = HomePageSection::where('type', 'why_join_us')->first();
        if (!$section) {
            return redirect()->back()->withErrors('Section not found.');
        }
        $request->validate([
            'cards' => 'required|array',
            'cards.*.title' => 'required|array',
            'cards.*.title.*' => 'nullable|string',
            'cards.*.image_alt' => 'nullable|array',
            'cards.*.image_alt.*' => 'nullable|string',
            'cards.*.description' => 'nullable|array',
            'cards.*.description.*' => 'nullable|string',
            'lang' => 'nullable|array',
            'lang.*' => 'nullable|string',
        ]);
        $this->validateRequiredWhyJoinUsCardTitles($request);

        $defaultLangIndex = getDefaultLanguageIndex($request);
        $defaultLang = $request->lang[$defaultLangIndex];
        $existingValue = json_decode($section->value, true) ?? [];
        $data = [
            'section' => [
                'title' => $existingValue['section']['title'] ?? '',
                'subtitle' => $existingValue['section']['subtitle'] ?? '',
                'cards' => []
            ]
        ];

        foreach ($request->cards as $index => $cardInput) {
            if ($request->hasFile("cards.$index.image")) {
                $this->deleteImageIfExists($cardInput['existing_image'] ?? null);
                $data['section']['cards'][$index]['image'] = $this->storeOptimizedImage(
                    image: $request->file("cards.$index.image"),
                    directory: 'uploads/why_join_us'
                );
            } else {
                $data['section']['cards'][$index]['image'] = $cardInput['existing_image'] ?? '';
            }
            $data['section']['cards'][$index]['title'] = $cardInput['title'][$defaultLang] ?? '';
            $data['section']['cards'][$index]['image_alt'] = $cardInput['image_alt'][$defaultLang] ?? '';
            $data['section']['cards'][$index]['description'] = $cardInput['description'][$defaultLang] ?? '';
        }


        $section->value = json_encode($data);
        $section->save();
        $flatInput = [];
        $languages = [];

        foreach ($request->cards as $card) {
            foreach (['title', 'description', 'image_alt'] as $field) {
                if (!isset($card[$field])) continue;

                foreach ($card[$field] as $lang => $val) {
                    if ($lang === $defaultLang || is_null($val)) continue;

                    if (!in_array($lang, $languages)) {
                        $languages[] = $lang;
                    }
                }
            }
        }
        foreach (['title', 'description', 'image_alt'] as $field) {
            $flatInput[$field] = [];

            foreach ($languages as $index => $lang) {
                $values = [];

                foreach ($request->cards as $card) {
                    if (!empty($card[$field][$lang])) {
                        $values[] = $card[$field][$lang];
                    }
                }

                $flatInput[$field][$index] = implode('|||', $values);
            }
        }


        $flatInput['lang'] = $languages;

        $fakeRequest = new Request($flatInput);

        $this->translationRepo->updateArrayBasedSectionTranslations(
            request: $fakeRequest,
            model: HomePageSection::class,
            id: $section->id
        );

        return redirect()->back()->with('success', 'Why Join Us section updated successfully.');
    }




    public function updateWholesalerSection(Request $request)
    {
        $request->merge([
            'title' => CmsContentSanitizer::sanitizePlainTextArray($request->input('title', [])),
            'description' => CmsContentSanitizer::sanitizeRichTextArray($request->input('description', [])),
            'button_text' => CmsContentSanitizer::sanitizePlainTextArray($request->input('button_text', [])),
            'button_link' => CmsContentSanitizer::sanitizeLink($request->input('button_link')),
        ]);

        $section = HomePageSection::where('type', 'wholesaler_section')->first();

        if (!$section) {
            return redirect()->back()->withErrors('Section not found.');
        }

        $validated = $request->validate([
            'title' => 'nullable|array|max:255',
            'description' => 'nullable|array',
            'button_text' => 'nullable|array|max:255',
            'button_link' => [
                'nullable',
                'string',
                'max:500',
                static function ($attribute, $value, $fail) {
                    if ($value && CmsContentSanitizer::sanitizeLink($value) === '') {
                        $fail(translate('invalid_URL'));
                    }
                },
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp',
        ]);

        $defaultLangIndex = getDefaultLanguageIndex($request);
        $data = $section->value ? json_decode($section->value, true) : [];

        $data['title'] = $validated['title'][$defaultLangIndex] ?? '';
        $data['description'] = CmsContentSanitizer::sanitizeRichText($validated['description'][$defaultLangIndex] ?? '');
        $data['button']['text'] = CmsContentSanitizer::sanitizePlainText($validated['button_text'][$defaultLangIndex] ?? '');
        $data['button']['link'] = CmsContentSanitizer::sanitizeLink($validated['button_link'] ?? '');

        if ($request->hasFile('image')) {
            $this->deleteImageIfExists($data['image'] ?? null);
            $data['image'] = $this->storeOptimizedImage(
                image: $request->file('image'),
                directory: 'uploads/wholesaler_section'
            );
        } else {
            $data['image'] = $data['image'] ?? '';
        }

        $section->value = json_encode($data);
        $section->save();
        $this->translationRepo->update($request, HomePageSection::class, $section->id);

        return redirect()->back()->with('success', 'Wholesaler Section updated successfully.');
    }

    public function updateFindPerfectMatch(Request $request)
    {
        $validated = $request->validate([
            'lang' => 'required|array',
            'section_heading' => 'required|array',
            'hero_heading' => 'required|array',
            'hero_description' => 'required|array',
            'filter_title' => 'required|array',
            'make_label' => 'required|array',
            'model_label' => 'required|array',
            'year_label' => 'required|array',
            'make_placeholder' => 'required|array',
            'model_placeholder' => 'required|array',
            'year_placeholder' => 'required|array',
            'apply_button_text' => 'required|array',
        ]);

        $section = HomePageSection::firstOrCreate(
            ['type' => 'find_perfect_match'],
            [
                'name' => 'Find Perfect Match',
                'value' => json_encode([]),
                'is_active' => 1,
            ]
        );

        $defaultLangIndex = getDefaultLanguageIndex($request);
        $this->validateRequiredCmsEnglishFields($request, [
            'section_heading' => ['message' => 'The_heading_in_english_is_required'],
            'hero_heading' => ['message' => 'The_heading_in_english_is_required'],
            'hero_description' => ['message' => 'The_description_in_english_is_required'],
            'filter_title' => ['message' => 'The_title_in_english_is_required'],
            'make_label' => ['message' => 'The_title_in_english_is_required'],
            'model_label' => ['message' => 'The_title_in_english_is_required'],
            'year_label' => ['message' => 'The_title_in_english_is_required'],
            'make_placeholder' => ['message' => 'The_title_in_english_is_required'],
            'model_placeholder' => ['message' => 'The_title_in_english_is_required'],
            'year_placeholder' => ['message' => 'The_title_in_english_is_required'],
            'apply_button_text' => ['message' => 'The_title_in_english_is_required'],
        ]);
        if ($defaultLangIndex === false) {
            $defaultLangIndex = 0;
        }

        $decoded = json_decode($section->value ?? '', true);
        $isList = is_array($decoded) && $decoded !== [] && array_keys($decoded) === range(0, count($decoded) - 1);
        $data = is_array($decoded) && !$isList ? $decoded : [];

        $fields = [
            'section_heading',
            'hero_heading',
            'hero_description',
            'filter_title',
            'make_label',
            'model_label',
            'year_label',
            'make_placeholder',
            'model_placeholder',
            'year_placeholder',
            'apply_button_text',
        ];

        foreach ($fields as $field) {
            $data[$field] = $validated[$field][$defaultLangIndex] ?? ($data[$field] ?? '');
        }

        $section->value = json_encode($data, JSON_UNESCAPED_UNICODE);
        $section->save();

        $this->translationRepo->update(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );

        return redirect()->back()->with('success', 'Find Perfect Match section updated successfully.');
    }


    public function addFaq(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:2000',
        ]);

        $section = HomePageSection::where('type', 'faq')->first();
        if (!$section) return response()->json(['error' => 'Section not found.'], 404);

        $data = json_decode($section->value, true) ?? [];
        $data['faqs'][] = [
            'question' => CmsContentSanitizer::sanitizePlainText($request->question),
            'answer' => CmsContentSanitizer::sanitizeRichText($request->answer),
        ];

        $section->value = json_encode($data);
        $section->save();

        return redirect()->back()->with('success', 'Faq add successfully.');
    }

    public function updateFaq(Request $request)
    {
        $request->validate([
            'index' => 'required|integer',
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:2000',
        ]);

        $section = HomePageSection::where('type', 'faq')->first();
        if (!$section) return response()->json(['error' => 'Section not found.'], 404);

        $data = json_decode($section->value, true) ?? [];
        if (!isset($data['faqs'][$request->index])) {
            return response()->json(['error' => 'FAQ not found.'], 404);
        }

        $data['faqs'][$request->index]['question'] = CmsContentSanitizer::sanitizePlainText($request->question);
        $data['faqs'][$request->index]['answer'] = CmsContentSanitizer::sanitizeRichText($request->answer);

        $section->value = json_encode($data);
        $section->save();

        return redirect()->back()->with('success', 'Faq updated successfully.');
    }

    public function deleteFaq(Request $request)
    {
        $request->validate([
            'index' => 'required|integer',
        ]);

        $section = HomePageSection::where('type', 'faq')->first();
        if (!$section) return response()->json(['error' => 'Section not found.'], 404);

        $data = json_decode($section->value, true) ?? [];

        if (!isset($data['faqs'][$request->index])) {
            return response()->json(['error' => 'FAQ not found.'], 404);
        }

        array_splice($data['faqs'], $request->index, 1); // Remove specific index

        $section->value = json_encode($data);
        $section->save();

        return redirect()->back()->with('success', 'Faq delete successfully.');
    }
    public function updateDownloadAppItem(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'key' => 'required|string',
            'alt' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_image' => 'nullable|in:0,1',
        ]);

        if ($validated['type'] !== 'download_app') {
            return redirect()->back()->withErrors('Invalid section type.');
        }

        $section = HomePageSection::where('type', 'download_app')->first();

        if (!$section) {
            return redirect()->back()->withErrors('Download App Section not found.');
        }

        $data = json_decode($section->value, true);

        $key = $validated['key'];
        $removeImage = (int) $request->input('remove_image', 0) === 1;

        if (!isset($data['content'][$key])) {
            return redirect()->back()->withErrors('Invalid key.');
        }

        $data['content'][$key]['alt'] = $validated['alt'];
        $existingImage = $data['content'][$key]['image'] ?? null;

        if ($removeImage && !$request->hasFile('image')) {
            $this->deleteImageIfExists($existingImage);
            $data['content'][$key]['image'] = '';
        }

        if ($request->hasFile('image')) {
            $this->deleteImageIfExists($existingImage);
            $data['content'][$key]['image'] = $this->storeOptimizedImage(
                image: $request->file('image'),
                directory: 'uploads'
            );
        }

        $section->value = json_encode($data);
        $section->save();

        return redirect()->back()->with('success', 'Item updated successfully.');
    }


    public function updateDownloadAppHeading(Request $request)
    {

        $request->validate([
            'lang' => 'required|array',
            'heading' => 'required|array',
        ]);

        $headings = $request->input('heading');
        $this->validateRequiredCmsEnglishFields($request, [
            'heading' => ['message' => 'The_heading_in_english_is_required'],
        ]);

        $section = HomePageSection::where('type', 'download_app')->first();

        if (!$section) {
            return redirect()->back()->withErrors('Section not found.');
        }

        $data = json_decode($section->value, true) ?? [];
        $defaultLangIndex = getDefaultLanguageIndex($request);

        $data['content']['heading'] = $headings[$defaultLangIndex];


        $section->value = json_encode($data);
        $section->save();




        $this->translationRepo->update(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );

        return redirect()->back()->with('success', 'Heading updated successfully.');
    }
    public function updateWhyChooseHeading(Request $request)
    {

        $request->validate([
            'lang' => 'required|array',
            'title' => 'required|array',
            'subtitle' => 'required|array',
        ]);

        $title = $request->input('title');
        $subtitle = $request->input('subtitle');
        $this->validateRequiredCmsEnglishFields($request, [
            'title' => ['message' => 'The_title_in_english_is_required'],
            'subtitle' => ['message' => 'The_subheading_in_english_is_required'],
        ]);

        $section = HomePageSection::where('type', 'why_choose_us')->first();

        if (!$section) {
            return redirect()->back()->withErrors('Section not found.');
        }

        $data = json_decode($section->value, true) ?? [];
        $defaultLangIndex = getDefaultLanguageIndex($request);

        $data['section']['title'] = $title[$defaultLangIndex];
        $data['section']['subtitle'] = $subtitle[$defaultLangIndex];


        $section->value = json_encode($data);
        $section->save();

        $request->merge([
            'index' => -1,
        ]);
        $this->translationRepo->updateArrayBasedSectionTranslations(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );

        return redirect()->back()->with('success', 'data updated successfully.');
    }
    public function updateCategory(Request $request)
    {

        $request->validate([
            'lang' => 'required|array',
            'heading' => 'required|array',
            'paragraph' => 'required|array',
        ]);

        $heading = $request->input('heading');
        $paragraph = $request->input('paragraph');

        $section = HomePageSection::where('type', 'categories')->first();

        if (!$section) {
            return redirect()->back()->withErrors('Section not found.');
        }

        $data = json_decode($section->value, true) ?? [];
        $defaultLangIndex = getDefaultLanguageIndex($request);

        $data['heading'] = $heading[$defaultLangIndex];
        $data['paragraph'] = $paragraph[$defaultLangIndex];


        $section->value = json_encode($data);
        $section->save();

        $this->translationRepo->update(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );

        return redirect()->back()->with('success', 'data updated successfully.');
    }
    public function updateBlog(Request $request)
    {

        $request->validate([
            'lang' => 'required|array',
            'heading' => 'required|array',
            'paragraph' => 'required|array',
        ]);

        $heading = $request->input('heading');
        $paragraph = $request->input('paragraph');

        $section = HomePageSection::where('type', 'blog')->first();

        if (!$section) {
            return redirect()->back()->withErrors('Section not found.');
        }

        $data = json_decode($section->value, true) ?? [];
        $defaultLangIndex = getDefaultLanguageIndex($request);

        $data['heading'] = $heading[$defaultLangIndex];
        $data['paragraph'] = $paragraph[$defaultLangIndex];


        $section->value = json_encode($data);
        $section->save();

        $this->translationRepo->update(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );

        return redirect()->back()->with('success', 'data updated successfully.');
    }
    public function updateWhyJoinHeading(Request $request)
    {

        $request->validate([
            'lang' => 'required|array',
            'title' => 'required|array',
            'subtitle' => 'required|array',
        ]);

        $title = $request->input('title');
        $subtitle = $request->input('subtitle');
        $this->validateRequiredCmsEnglishFields($request, [
            'title' => ['message' => 'The_title_in_english_is_required'],
            'subtitle' => ['message' => 'The_subheading_in_english_is_required'],
        ]);

        $section = HomePageSection::where('type', 'why_join_us')->first();

        if (!$section) {
            return redirect()->back()->withErrors('Section not found.');
        }

        $data = json_decode($section->value, true) ?? [];
        $defaultLangIndex = getDefaultLanguageIndex($request);

        $data['section']['title'] = $title[$defaultLangIndex];
        $data['section']['subtitle'] = $subtitle[$defaultLangIndex];


        $section->value = json_encode($data);
        $section->save();

        $request->merge([
            'index' => -1,
        ]);
        $this->translationRepo->updateArrayBasedSectionTranslations(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );

        return redirect()->back()->with('success', 'data updated successfully.');
    }



    public function storeBanner(Request $request)
    {
        $request->merge([
            'heading' => CmsContentSanitizer::sanitizePlainTextArray($request->input('heading', [])),
            'paragraph' => CmsContentSanitizer::sanitizePlainTextArray($request->input('paragraph', [])),
            'buttonText' => CmsContentSanitizer::sanitizePlainTextArray($request->input('buttonText', [])),
            'buttonLink' => CmsContentSanitizer::sanitizeLink($request->input('buttonLink')),
        ]);

        $request->validate([
            'heading' => 'required|array',
            'paragraph' => 'required|array',
            'buttonText' => 'required|array',
            'buttonLink' => [
                'required',
                'string',
                'max:500',
                static function ($attribute, $value, $fail) {
                    if (CmsContentSanitizer::sanitizeLink($value) === '') {
                        $fail(translate('invalid_URL'));
                    }
                },
            ],
            'image' => 'required|image',
            'section' => 'required|string',
        ]);

        $section = HomePageSection::where('type', $request->section)->firstOrFail();
        $data = json_decode($section->value, true) ?? [];
        $defaultLangIndex = getDefaultLanguageIndex($request);
        $this->validateRequiredCmsEnglishFields($request, [
            'heading' => ['message' => 'The_heading_in_english_is_required'],
            'paragraph' => ['message' => 'The_description_in_english_is_required'],
            'buttonText' => ['message' => 'The_title_in_english_is_required'],
        ]);

        $imagePath = $this->storeOptimizedImage(
            image: $request->file('image'),
            directory: 'banners'
        );

        $index = count($data); // This is the correct index for new entry

        $data[] = [
            'heading' => CmsContentSanitizer::sanitizePlainText($request->heading[$defaultLangIndex] ?? ''),
            'paragraph' => CmsContentSanitizer::sanitizeRichText($request->paragraph[$defaultLangIndex] ?? ''),
            'buttonText' => CmsContentSanitizer::sanitizePlainText($request->buttonText[$defaultLangIndex] ?? ''),
            'buttonLink' => CmsContentSanitizer::sanitizeLink($request->buttonLink),
            'image' => $imagePath,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        $section->value = json_encode($data);
        $section->save();

        $request->merge(['index' => $index]);

        $this->translationRepo->createArrayBasedSectionTranslations(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );

        return redirect()->back()->with('success', 'Banner created successfully.');
    }



    public function updateBanner(Request $request)
    {
        $request->merge([
            'heading' => CmsContentSanitizer::sanitizePlainTextArray($request->input('heading', [])),
            'paragraph' => CmsContentSanitizer::sanitizePlainTextArray($request->input('paragraph', [])),
            'buttonText' => CmsContentSanitizer::sanitizePlainTextArray($request->input('buttonText', [])),
            'buttonLink' => CmsContentSanitizer::sanitizeLink($request->input('buttonLink')),
        ]);

        $request->validate([
            'index' => 'required|integer',
            'section' => 'required|string',
            'remove_image' => 'nullable|in:0,1',
            'buttonLink' => [
                'required',
                'string',
                'max:500',
                static function ($attribute, $value, $fail) {
                    if (CmsContentSanitizer::sanitizeLink($value) === '') {
                        $fail(translate('invalid_URL'));
                    }
                },
            ],
        ]);

        $section = HomePageSection::where('type', $request->section)->firstOrFail();
        $data = json_decode($section->value, true);
        $defaultLangIndex = getDefaultLanguageIndex($request);
        $this->validateRequiredCmsEnglishFields($request, [
            'heading' => ['message' => 'The_heading_in_english_is_required'],
            'paragraph' => ['message' => 'The_description_in_english_is_required'],
            'buttonText' => ['message' => 'The_title_in_english_is_required'],
        ]);


        $item = &$data[$request->index];
        $removeImage = (int) $request->input('remove_image', 0) === 1;
        $existingImage = $item['image'] ?? null;
        $item['heading'] = CmsContentSanitizer::sanitizePlainText($request->heading[$defaultLangIndex] ?? '');
        $item['paragraph'] = CmsContentSanitizer::sanitizeRichText($request->paragraph[$defaultLangIndex] ?? '');
        $item['buttonText'] = CmsContentSanitizer::sanitizePlainText($request->buttonText[$defaultLangIndex] ?? '');
        $item['buttonLink'] = CmsContentSanitizer::sanitizeLink($request->buttonLink);

        if ($removeImage && !$request->hasFile('image')) {
            $this->deleteImageIfExists($existingImage);
            $item['image'] = '';
        }

        if ($request->hasFile('image')) {
            $this->deleteImageIfExists($existingImage);
            $item['image'] = $this->storeOptimizedImage(
                image: $request->file('image'),
                directory: 'banners'
            );
        }

        $section->value = json_encode($data);
        $section->save();

        $this->translationRepo->updateArrayBasedSectionTranslations(
            request: $request,
            model: HomePageSection::class,
            id: $section->id
        );


        return redirect()->back()->with('success', 'Banner updated successfully.');
    }

    public function deleteBanner(Request $request)
    {
        $request->validate([
            'index' => 'required|integer',
            'section' => 'required|string',
        ]);

        $section = HomePageSection::where('type', $request->section)->firstOrFail();
        $data = json_decode($section->value, true);
        $this->deleteImageIfExists($data[$request->index]['image'] ?? null);
        array_splice($data, $request->index, 1);

        $section->value = json_encode($data);
        $section->save();

        return response()->json(['success' => true]);
    }

    public function toggleStatusBanner(Request $request)
    {
        $request->validate([
            'index' => 'required|integer',
            'section' => 'required|string',
        ]);

        $section = HomePageSection::where('type', $request->section)->firstOrFail();
        $data = json_decode($section->value, true);

        $data[$request->index]['is_active'] = !$data[$request->index]['is_active'];

        $section->value = json_encode($data);
        $section->save();

        return response()->json(['success' => true]);
    }

    public function toggleStatusSection(Request $request)
    {

        $request->validate([
            'type' => 'required|string',
            'status' => 'required|boolean',
        ]);

        $section = HomePageSection::where('type', $request->type)->first();

        if (!$section) {
            return response()->json(['message' => 'Section not found'], 404);
        }

        $section->is_active = $request->status;
        $section->save();

        return response()->json(['message' => 'Status updated successfully']);
    }
}
