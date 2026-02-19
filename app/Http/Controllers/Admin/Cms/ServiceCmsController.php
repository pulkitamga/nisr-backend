<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsService;
use Illuminate\Http\Request;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Traits\CommonTrait;
use App\Traits\PaginatorTrait;
use App\Utils\ImageManager;

class ServiceCmsController extends Controller
{


    use PaginatorTrait;
    use CommonTrait;

    public function __construct(
        private readonly ProductRepositoryInterface     $productRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,

    ) {}


    public function index(Request $request)
    {
        $products = CmsService::orderBy('created_at', 'desc')->get();

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
            'button_link' => 'required',
            'image' => 'nullable|image',
            'lang' => 'required|array'
        ]);

        $cmsProduct = CmsService::findOrFail($id);

        $defaultLangIndex = array_search('en', $request->lang);
        if ($defaultLangIndex !== false) {
            $cmsProduct->heading = $request->heading[$defaultLangIndex];
            $cmsProduct->description = $request->description[$defaultLangIndex];
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
            'new_status' => $product->status
        ]);
    }
}
