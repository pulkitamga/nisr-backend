<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsService;
use Illuminate\Http\Request;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Support\CmsContentSanitizer;
use App\Traits\AuthorizesCmsSection;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;
use App\Traits\ValidatesCmsEnglishMultilingualInput;
use App\Utils\ImageManager;

class ServiceCmsController extends Controller
{


    use PaginatorTrait;
    use CommonTrait;
    use AuthorizesCmsSection;
    use ValidatesCmsEnglishMultilingualInput;

    public function __construct(
        private readonly ProductRepositoryInterface     $productRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,

    ) {
        $this->middleware($this->cmsPermissionMiddleware('cms_section.read'))->only(['index', 'edit']);
        $this->middleware($this->cmsPermissionMiddleware('cms_section.update'))->only(['update', 'toggleStatus']);
    }


    public function index(Request $request)
    {
        $products = CmsService::with('translations')->orderBy('created_at', 'desc')->get();

        return view('admin-views.content-management.services.index', compact('products'));
    }


   

    public function edit($id)
    {

        $products = CmsService::with('translations')->findOrFail($id);

        return view("admin-views.content-management.services.sections.edit.edit", compact('products'));
    }

   
    public function update(Request $request, $id)
    {
        $request->validate([
            'heading' => 'required|array',
            'heading.*' => 'nullable|string|max:255',
            'description' => 'required|array',
            'description.*' => 'nullable|string',
            'button_text' => 'nullable|array',
            'button_text.*' => 'nullable|string|max:255',
            'button_link' => [
                'required',
                'string',
                'max:500',
                static function ($attribute, $value, $fail) {
                    if (CmsContentSanitizer::sanitizeLink($value) === '') {
                        $fail(translate('invalid_URL'));
                    }
                },
            ],
            'image' => 'nullable|image',
            'lang' => 'required|array'
        ]);

        $cmsProduct = CmsService::findOrFail($id);
        $sanitizedDescriptions = CmsContentSanitizer::sanitizeRichTextArray($request->input('description', []));
        $sanitizedButtonText = CmsContentSanitizer::sanitizePlainTextArray($request->input('button_text', []));
        $sanitizedButtonLink = CmsContentSanitizer::sanitizeLink($request->button_link);

        $request->merge([
            'description' => $sanitizedDescriptions,
            'button_text' => $sanitizedButtonText,
            'button_link' => $sanitizedButtonLink,
        ]);
        $this->validateRequiredCmsEnglishFields($request, [
            'heading' => ['message' => 'The_heading_in_english_is_required'],
        ]);

        $defaultLangIndex = getDefaultLanguageIndex($request);
        if ($defaultLangIndex !== false) {
            $cmsProduct->heading = $request->heading[$defaultLangIndex];
            $cmsProduct->description = $request->description[$defaultLangIndex];
            $cmsProduct->button_text = $request->button_text[$defaultLangIndex] ?? null;
        }

        if ($request->hasFile('image')) {
            if (!empty($cmsProduct->image)) {
                ImageManager::delete($cmsProduct->image);
            }
            $imageName = ImageManager::upload('cms-service/', 'webp', $request->file('image'));
            $cmsProduct->image = 'cms-service/' . $imageName;
        }
         $cmsProduct->button_link = $request->button_link;

        $cmsProduct->type = $request->type;
        $cmsProduct->save();
        $this->translationRepo->update(
            request: $request,
            model: CmsService::class,
            id: $id
        );


        return redirect()->route('admin.content-management.services')->with('success', 'Updated successfully!');
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
            'message' => 'Service Section status updated successfully!',
            'new_status' => $product->is_active
        ]);
    }
}
