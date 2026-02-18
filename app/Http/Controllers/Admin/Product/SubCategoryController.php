<?php

namespace App\Http\Controllers\Admin\Product;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Enums\ExportFileNames\Admin\Category as SubCategoryExport;
use App\Enums\ViewPaths\Admin\SubCategory;
use App\Exports\CategoryListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\CategoryUpdateRequest;
use App\Http\Requests\Admin\SubCategoryAddRequest;
use App\Services\CategoryService;
use App\Traits\PaginatorTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use  App\Models\ManageExtraCharge;

class SubCategoryController extends BaseController
{
    use PaginatorTrait;

    public function __construct(
        private readonly CategoryRepositoryInterface        $categoryRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,
    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getAddView($request);
    }

    public function getAddView(Request $request): View
    {
        $categories = $this->categoryRepo->getListWhere(
            searchValue: $request->get('searchValue'),
            filters: ['position' => 1],
            dataLimit: getWebConfig(name: 'pagination_limit')
        );

        $parentCategories = $this->categoryRepo->getListWhere(
            filters: ['position' => 0],
            dataLimit: 'all'
        );

        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];

        // Fetch extra charges for each category
        $charges = \App\Models\ManageExtraCharge::whereIn('category_id', $categories->pluck('id'))->get();

        return view(SubCategory::LIST[VIEW], [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
            'charges' => $charges, // Pass charges to the view
        ]);
    }


    public function getUpdateView(string|int $id): View
    {
        $category = $this->categoryRepo->getFirstWhere(params: ['id' => $id], relations: ['translations']);
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];

        // Fetch the charges for the category (assuming they are stored in a related model like `ManageExtraCharge`)
        $categoryCharges = ManageExtraCharge::where('category_id', $id)->get();

        return view(SubCategory::UPDATE[VIEW], [
            'category' => $category,
            'languages' => $languages,
            'defaultLanguage' => $defaultLanguage,
            'categoryCharges' => $categoryCharges, // Pass charges to the view
        ]);
    }

    public function add(SubCategoryAddRequest $request, CategoryService $categoryService): RedirectResponse
    {
        $dataArray = $categoryService->getAddData(request: $request);
        $savedCategory = $this->categoryRepo->add(data: $dataArray);

        // Add translation
        $this->translationRepo->add(request: $request, model: 'App\Models\Category', id: $savedCategory->id);

        $charges = [
            'installation' => $request->installation_charge,
            'exchange' => $request->exchange_charge
        ];
    
        foreach ($charges as $type => $chargeValue) {
            if ($chargeValue) {  // Only process if charge value exists
                // Check if the charge of this type already exists for the category
                $existingCharge = ManageExtraCharge::where('category_id', $savedCategory->id)
                                                    ->where('type', $type)
                                                    ->first();
    
                if ($existingCharge) {
                    // If it exists, update it
                    $existingCharge->charges = $chargeValue;
                    $existingCharge->save();
                } else {
                    // If it does not exist, create a new charge
                    ManageExtraCharge::create([
                        'type' => $type,
                        'category_id' => $savedCategory->id,
                        'charges' => $chargeValue,
                        'status' => 1, // default active
                    ]);
                }
            }
        }

        Toastr::success(translate('category_added_successfully'));
        return back();
    }


    public function update(CategoryUpdateRequest $request, CategoryService $categoryService): JsonResponse
    {
        $category = $this->categoryRepo->getFirstWhere(params: ['id' => $request['id']]);
        $dataArray = $categoryService->getUpdateData(request: $request, data: $category);
    
        // Update category data
        $this->categoryRepo->update(id: $request['id'], data: $dataArray);
        
        // Update charges
        if ($request->has('charges')) {
            foreach ($request->charges as $chargeId => $newChargeValue) {
                $charge = ManageExtraCharge::find($chargeId);
                if ($charge) {
                    $charge->charges = $newChargeValue;
                    $charge->save();
                }
            }
        }
    
        // Update translations
        $this->translationRepo->update(request: $request, model: 'App\Models\Category', id: $request['id']);
    
        Toastr::success(translate('category_updated_successfully'));
        return response()->json();
    }
    

    public function delete(Request $request): JsonResponse
    {
        $this->categoryRepo->delete(params: ['id' => $request['id']]);
        return response()->json(['message' => translate('deleted_successfully')]);
    }
    public function getExportList(Request $request): BinaryFileResponse
    {
        $subCategories = $this->categoryRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request->get('searchValue'), filters: ['position' => 1], dataLimit: getWebConfig(name: 'pagination_limit'));
        $active = $subCategories->where('home_status', 1)->count();
        $inactive = $subCategories->where('home_status', 0)->count();
        return Excel::download(
            new CategoryListExport([
                'categories' => $subCategories,
                'title' => 'sub_category',
                'search' => $request['searchValue'],
                'active' => $active,
                'inactive' => $inactive,
            ]),
            SubCategoryExport::SUB_CATEGORY_LIST_XLSX
        );
    }

    public function updateExtraChargeStatus(Request $request)
    {
        // Validate incoming request
        $validated = $request->validate([
            'id' => 'required|exists:manage_extra_charges,id',
            'type' => 'required|string|in:exchange,installation',
            'status' => 'required|boolean',
        ]);

        // Find the specific charge and update the status
        $charge = ManageExtraCharge::find($validated['id']);

        if (!$charge) {
            return response()->json(['success' => false, 'message' => 'Charge not found']);
        }

        $charge->status = $validated['status'];
        $charge->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }
}
