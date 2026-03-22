<?php

namespace App\Http\Controllers\Admin\Product;

use Carbon\Carbon;
use App\Enums\StockReason;
use App\Enums\WebConfigKey;
use App\Models\VehicleMake;
use App\Models\VehicleYear;
use Illuminate\Support\Str;
use App\Models\ProductStock;
use App\Models\VehicleModel;
use App\Traits\ProductTrait;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Services\InventoryMutationService;
use App\Services\ServiceService;
use App\Services\ReportPdfService;
use App\Traits\FileManagerTrait;
use Illuminate\Http\JsonResponse;
use App\Exports\ProductListExport;
use App\Models\Product as Products;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\VehicleMakeService;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\VehicleModelService;
use Illuminate\Http\RedirectResponse;
use App\Enums\ViewPaths\Admin\Product;
use App\Models\ProductStockTransaction;
use App\Models\WholeSaleProducts;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductAddRequest;
use App\Models\ManageBranchProductStock;
use App\Exports\RestockProductListExport;
use App\Http\Requests\ProductUpdateRequest;
use App\Events\ProductRequestStatusUpdateEvent;
use App\Http\Requests\Admin\ProductDenyRequest;
use App\Http\Requests\Admin\TransferRequestAddProduct;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\ColorRepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Contracts\Repositories\AuthorRepositoryInterface;
use App\Contracts\Repositories\BannerRepositoryInterface;
use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ServiceRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\WishlistRepositoryInterface;
use App\Contracts\Repositories\AttributeRepositoryInterface;
use App\Contracts\Repositories\ProductSeoRepositoryInterface;
use App\Repositories\DigitalProductPublishingHouseRepository;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\VehicleMakeRepositoryInterface;
use App\Contracts\Repositories\DealOfTheDayRepositoryInterface;
use App\Contracts\Repositories\VehicleModelRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Contracts\Repositories\FlashDealProductRepositoryInterface;
use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Contracts\Repositories\DigitalProductAuthorRepositoryInterface;
use App\Contracts\Repositories\StockClearanceProductRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\DigitalProductVariationRepositoryInterface;
use Illuminate\Validation\ValidationException;


class ProductController extends BaseController
{
    use ProductTrait;

    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }

    public function __construct(
        private readonly AuthorRepositoryInterface                  $authorRepo,
        private readonly DigitalProductAuthorRepositoryInterface    $digitalProductAuthorRepo,
        private readonly DigitalProductPublishingHouseRepository    $digitalProductPublishingHouseRepo,
        private readonly PublishingHouseRepositoryInterface         $publishingHouseRepo,
        private readonly CategoryRepositoryInterface                $categoryRepo,
        private readonly BranchRepositoryInterface                  $branchRepo,
        private readonly BrandRepositoryInterface                   $brandRepo,
        private readonly ProductRepositoryInterface                 $productRepo,
        private readonly CustomerRepositoryInterface                $customerRepo,
        private readonly RestockProductRepositoryInterface          $restockProductRepo,
        private readonly RestockProductCustomerRepositoryInterface  $restockProductCustomerRepo,
        private readonly DigitalProductVariationRepositoryInterface $digitalProductVariationRepo,
        private readonly StockClearanceProductRepositoryInterface   $stockClearanceProductRepo,
        private readonly VehicleModelRepositoryInterface             $vehicleModelRepo,
        private readonly VehicleMakeRepositoryInterface             $vehicleMakeRepo,
        private readonly StockClearanceSetupRepositoryInterface     $stockClearanceSetupRepo,
        private readonly ProductSeoRepositoryInterface              $productSeoRepo,
        private readonly VendorRepositoryInterface                  $sellerRepo,
        private readonly ColorRepositoryInterface                   $colorRepo,
        private readonly AttributeRepositoryInterface               $attributeRepo,
        private readonly TranslationRepositoryInterface             $translationRepo,
        private readonly CartRepositoryInterface                    $cartRepo,
        private readonly WishlistRepositoryInterface                $wishlistRepo,
        private readonly FlashDealProductRepositoryInterface        $flashDealProductRepo,
        private readonly DealOfTheDayRepositoryInterface            $dealOfTheDayRepo,
        private readonly ReviewRepositoryInterface                  $reviewRepo,
        private readonly BannerRepositoryInterface                  $bannerRepo,
        private readonly ServiceRepositoryInterface                 $serviceRepo,
        private readonly ProductService                             $productService,
        private readonly InventoryMutationService                   $inventoryMutationService,
        private readonly ServiceService                             $serviceService,
        private readonly VehicleModelService                        $modelService,
        private readonly VehicleMakeService                         $makeService,

    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView(request: $request, type: ($type == 'vendor' ? 'seller' : 'in_house'));
    }

    public function getAddView(): View
    {
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $brands = $this->brandRepo->getListWhere(dataLimit: 'all');
        $branches = $this->branchRepo->getListWhere(dataLimit: 'all');
        $makes = $this->vehicleMakeRepo->all();
        // $models = $this->vehicleModelRepo->all();
        $models = [];
        $years = VehicleYear::orderBy('year')->get();
        $brandSetting = getWebConfig(name: 'product_brand');
        $servicesSetting = getWebConfig(name: 'services');
        $colors = $this->colorRepo->getList(orderBy: ['name' => 'desc'], dataLimit: 'all');
        $attributes = $this->attributeRepo->getList(orderBy: ['name' => 'desc'], dataLimit: 'all');
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $languages[0];
        $digitalProductFileTypes = ['audio', 'video', 'document', 'software'];
        $digitalProductAuthors = $this->authorRepo->getListWhere(dataLimit: 'all');
        $publishingHouseList = $this->publishingHouseRepo->getListWhere(dataLimit: 'all');

        return view(Product::ADD[VIEW], compact('categories', 'brands', 'branches', 'brandSetting', 'servicesSetting', 'colors', 'attributes', 'languages', 'defaultLanguage', 'digitalProductFileTypes', 'digitalProductAuthors', 'publishingHouseList', 'makes', 'models', 'years'));
    }
    public function getProductMakeView(Request $request): View
    {
        $query = VehicleMake::with('models')->orderBy('name');

        if ($request->filled('searchValue')) {
            $query->where('name', 'like', '%' . trim($request->searchValue) . '%');
        }

        $makes = $query->get();
        $languages = getWebConfig(name: 'pnc_language') ?? ['en'];
        $defaultLanguage = $languages[0] ?? 'en';

        return view(Product::PRODUCT_MAKE[VIEW], compact('makes', 'languages', 'defaultLanguage'));
    }

    public function getProductYearView(Request $request): View
    {
        $query = VehicleYear::query()->orderBy('year', 'desc');

        if ($request->filled('searchValue')) {
            $query->where('year', 'like', '%' . trim($request->searchValue) . '%');
        }

        $years = $query->get();
        $languages = getWebConfig(name: 'pnc_language') ?? ['en'];
        $defaultLanguage = $languages[0] ?? 'en';

        return view(Product::PRODUCT_YEAR[VIEW], compact('years', 'languages', 'defaultLanguage'));
    }

    public function getModelsByMakes(Request $request)
    {
        $makeNames = $request->input('makes', []);

        if (empty($makeNames)) {
            return response()->json(['models' => []]);
        }

        $makes = VehicleMake::whereIn('name', $makeNames)->pluck('id');
        $models = VehicleModel::whereIn('make_id', $makes)
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->get();

        return response()->json([
            'models' => $models->pluck('name')
        ]);
    }



    public function storeOrUpdateMake(Request $request): RedirectResponse
    {
        $languages = collect($request->input('lang', []))->values();
        $defaultLanguage = $languages->first() ?? 'en';
        $defaultIndex = $languages->search($defaultLanguage);
        $defaultIndex = $defaultIndex === false ? 0 : $defaultIndex;
        $defaultName = trim($request->input("name.$defaultIndex", ''));
        $defaultModels = $this->extractTagValues($request->input("model.$defaultIndex", ''));

        Validator::make($request->all(), [
            'name' => 'required|array',
            'name.*' => 'nullable|string|max:255',
            'model' => 'required|array',
            'model.*' => 'nullable|string',
        ])->after(function ($validator) use ($defaultName, $defaultModels, $request) {
            if ($defaultName === '') {
                $validator->errors()->add('name.0', translate('make_is_required'));
            }

            if (empty($defaultModels)) {
                $validator->errors()->add('model.0', translate('model_is_required'));
            }

            $uniqueRule = Validator::make(
                ['name' => $defaultName],
                ['name' => 'required|string|max:255|unique:vehicle_makes,name,' . $request->make_id]
            );

            if ($uniqueRule->fails()) {
                foreach ($uniqueRule->errors()->all() as $message) {
                    $validator->errors()->add('name.0', $message);
                }
            }
        })->validate();

        $this->ensureTranslatedModelCountsMatch($request, $defaultModels);

        if ($request->filled('make_id')) {
            $make = $this->makeService->update(new Request(['name' => $defaultName]), $request->make_id);
            $this->translationRepo->update($request, VehicleMake::class, $make->id);
            VehicleModel::withoutGlobalScopes()->where('make_id', $make->id)->get()->each(function (VehicleModel $model) {
                $this->translationRepo->delete(VehicleModel::class, $model->id);
            });
            $this->modelService->deleteByMakeId($make->id);
        } else {
            $make = $this->makeService->store(new Request(['name' => $defaultName]));
            $this->translationRepo->add($request, VehicleMake::class, $make->id);
        }

        $savedModels = [];
        foreach ($defaultModels as $modelName) {
            $savedModels[] = $this->modelService->store(new Request([
                'make_id' => $make->id,
                'name' => $modelName,
            ]));
        }

        $this->syncVehicleModelTranslations($request, collect($savedModels));

        Toastr::success(translate('make_and_models_saved_successfully'));

        return redirect()->back();
    }


    public function getMakeModels($id): JsonResponse
    {
        $languages = getWebConfig(name: 'pnc_language') ?? ['en'];
        $defaultLanguage = $languages[0] ?? 'en';
        $make = VehicleMake::withoutGlobalScopes()
            ->with([
                'translations',
                'models' => fn($query) => $query->withoutGlobalScopes()->with('translations')->orderBy('name'),
            ])
            ->findOrFail($id);

        $models = $make->models->map(fn($model) => $model->getRawOriginal('name'))->values()->toArray();

        return response()->json([
            'make' => $make->getRawOriginal('name'),
            'names' => $this->buildTranslationPayload(
                defaultValue: $make->getRawOriginal('name'),
                translations: $make->translations,
                languages: $languages,
                defaultLanguage: $defaultLanguage
            ),
            'models' => $models,
            'models_by_lang' => $this->buildVehicleModelTranslationsPayload($make->models, $languages, $defaultLanguage),
        ]);
    }

    public function destroyMake($id): JsonResponse
    {
        $this->translationRepo->delete(VehicleMake::class, $id);
        VehicleModel::withoutGlobalScopes()->where('make_id', $id)->get()->each(function (VehicleModel $model) {
            $this->translationRepo->delete(VehicleModel::class, $model->id);
        });
        $this->makeService->delete($id);
        return response()->json(['message' => translate('make_deleted_successfully')]);
    }

    public function storeOrUpdateYear(Request $request): RedirectResponse
    {
        $languages = collect($request->input('lang', []))->values();
        $defaultLanguage = $languages->first() ?? 'en';
        $defaultIndex = $languages->search($defaultLanguage);
        $defaultIndex = $defaultIndex === false ? 0 : $defaultIndex;
        $defaultYear = trim((string)$request->input('year', ''));

        Validator::make($request->all(), [
            'year' => 'required|digits:4|integer|unique:vehicle_years,year,' . $request->year_id,
            'name' => 'required|array',
            'name.*' => 'nullable|string|max:255',
        ])->after(function ($validator) use ($request, $defaultIndex) {
            if (trim((string)$request->input("name.$defaultIndex", '')) === '') {
                $validator->errors()->add("name.$defaultIndex", translate('enter_year'));
            }
        })->validate();

        if ($request->filled('year_id')) {
            $year = VehicleYear::withoutGlobalScopes()->findOrFail($request->year_id);
            $year->update(['year' => $defaultYear]);
            $this->translationRepo->update($request, VehicleYear::class, $year->id);
            Toastr::success(translate('year_updated_successfully'));
        } else {
            $year = VehicleYear::create(['year' => $defaultYear]);
            $this->translationRepo->add($request, VehicleYear::class, $year->id);
            Toastr::success(translate('year_added_successfully'));
        }

        return redirect()->back();
    }

    public function getYearData($id): JsonResponse
    {
        $languages = getWebConfig(name: 'pnc_language') ?? ['en'];
        $defaultLanguage = $languages[0] ?? 'en';
        $year = VehicleYear::withoutGlobalScopes()->with('translations')->findOrFail($id);

        return response()->json([
            'id' => $year->id,
            'year' => $year->getRawOriginal('year'),
            'names' => $this->buildTranslationPayload(
                defaultValue: (string)$year->getRawOriginal('year'),
                translations: $year->translations,
                languages: $languages,
                defaultLanguage: $defaultLanguage
            ),
        ]);
    }

    public function destroyYear($id): JsonResponse
    {
        $this->translationRepo->delete(VehicleYear::class, $id);
        VehicleYear::where('id', $id)->delete();

        return response()->json(['message' => translate('year_deleted_successfully')]);
    }

    public function add(ProductAddRequest $request, ProductService $service): JsonResponse|RedirectResponse
    {
        Log::info('Product Add Request:', ['request' => $request->all()]);

        if ($request->ajax()) {
            return response()->json([], 200);
        }

        // 1️⃣ Create the main product
        $dataArray = $service->getAddProductData(request: $request, addedBy: 'admin');
        $savedProduct = $this->productRepo->add(data: $dataArray);

        // 2️⃣ Add related tags, translations, authors, publishing houses
        $this->productRepo->addRelatedTags(request: $request, product: $savedProduct);
        $this->translationRepo->add(request: $request, model: 'App\Models\Product', id: $savedProduct->id);
        $this->updateProductAuthorAndPublishingHouse(request: $request, product: $savedProduct);

        // 3️⃣ Service products
        if ($request->product_type === 'services') {
            $serviceData = $this->serviceService->getServiceData($request, $savedProduct->id);
            $this->serviceRepo->add($serviceData);
        }

        // 4️⃣ Digital product variations
        $digitalFileArray = $service->getAddProductDigitalVariationData(request: $request, product: $savedProduct);
        foreach ($digitalFileArray as $digitalFile) {
            $this->digitalProductVariationRepo->add(data: $digitalFile);
        }

        // 5️⃣ SEO
        $this->productSeoRepo->add(data: $service->getProductSEOData(request: $request, product: $savedProduct, action: 'add'));

        // 6️⃣ Handle stock for branch and product stock
        if ($savedProduct->product_type === 'physical' && $request->filled('branch_id')) {

            $branchId  = $request->branch_id;
            $productId = $savedProduct->id;

            $variations = json_decode($savedProduct->variation, true) ?? [];

            // CASE 1: Product WITHOUT variations
            if (empty($variations)) {
                $qty = (int) ($request->current_stock ?? 0);
                $sku = $request->sku ?? $savedProduct->code ?? 'SKU-' . strtoupper(Str::random(10));

                // Insert into ProductStock
                $productStock = ProductStock::create([
                    'product_id' => $productId,
                    'variant'    => null,
                    'sku'        => $sku,
                    'price'      => $savedProduct->unit_price,
                    'qty'        => $qty,
                ]);

                // Log the initial stock
                ProductStockTransaction::logStockIn(
                    $productStock,
                    $qty,
                    StockReason::INITIAL_STOCK,
                    'Initial stock added on product creation',
                    $branchId
                );

                // Insert/Update Branch Stock
                ManageBranchProductStock::updateOrCreate(
                    [
                        'branch_id'     => $branchId,
                        'product_id'    => $productId,
                        'variation_key' => null,
                    ],
                    [
                        'current_stock' => $qty,
                    ]
                );
            }

            // CASE 2: Product WITH variations
            else {
                foreach ($variations as $variation) {

                    $qty = (int) ($variation['qty'] ?? 0);
                    if ($qty <= 0) continue;

                    $type  = $variation['type']; // e.g., YellowGreen-Left
                    $sku   = $variation['sku'] ?? ($savedProduct->code ?? 'SKU-' . strtoupper(Str::random(10)));
                    $price = $variation['price'] ?? $savedProduct->unit_price;

                    // Insert into ProductStock
                    $productStock = ProductStock::create([
                        'product_id' => $productId,
                        'variant'    => $type,
                        'sku'        => $sku,
                        'price'      => $price,
                        'qty'        => $qty,
                    ]);

                    // Log the initial stock
                    ProductStockTransaction::logStockIn(
                        $productStock,
                        $qty,
                        StockReason::INITIAL_STOCK,
                        'Initial stock added on product creation',
                        $branchId
                    );
                    // Insert/Update Branch Stock
                    ManageBranchProductStock::updateOrCreate(
                        [
                            'branch_id'     => $branchId,
                            'product_id'    => $productId,
                            'variation_key' => $type,
                        ],
                        [
                            'variation_type' => $type,
                            'attributes'     => $type,
                            'current_stock'  => $qty,
                        ]
                    );
                }
            }
        }

        Toastr::success(translate('product_added_successfully'));
        return redirect()->route('admin.products.list', ['in_house']);
    }
    // public function add(ProductAddRequest $request, ProductService $service): JsonResponse|RedirectResponse
    // {

    //     Log::info('the request is', ['request ' => $request->all()]);
    //     if ($request->ajax()) {
    //         return response()->json([], 200);
    //     }

    //     $dataArray = $service->getAddProductData(request: $request, addedBy: 'admin');
    //     $savedProduct = $this->productRepo->add(data: $dataArray);

    //     $this->productRepo->addRelatedTags(request: $request, product: $savedProduct);
    //     $this->translationRepo->add(request: $request, model: 'App\Models\Product', id: $savedProduct->id);
    //     $this->updateProductAuthorAndPublishingHouse(request: $request, product: $savedProduct);

    //     if ($request->product_type === 'services') {
    //         $serviceData = $this->serviceService->getServiceData($request, $savedProduct->id);
    //         $this->serviceRepo->add($serviceData);
    //     }

    //     $digitalFileArray = $service->getAddProductDigitalVariationData(request: $request, product: $savedProduct);
    //     foreach ($digitalFileArray as $digitalFile) {
    //         $this->digitalProductVariationRepo->add(data: $digitalFile);
    //     }

    //     $this->productSeoRepo->add(data: $service->getProductSEOData(request: $request, product: $savedProduct, action: 'add'));

    //     if ($request->filled('branch_id')) {

    //         $branchId  = $request->branch_id;
    //         $productId = $savedProduct->id;

    //         $variations = json_decode($savedProduct->variation, true) ?? [];

    //         // CASE 1: Product WITHOUT variations
    //         if (empty($variations)) {

    //             ManageBranchProductStock::updateOrCreate(
    //                 [
    //                     'branch_id'     => $branchId,
    //                     'product_id'    => $productId,
    //                     'variation_key' => null,
    //                 ],
    //                 [
    //                     'current_stock' => $request->current_stock ?? 0,
    //                 ]
    //             );
    //         }
    //         // CASE 2: Product WITH variations
    //         else {

    //             foreach ($variations as $variation) {

    //                 $typeString = $variation['type']; // e.g. Yellow-left
    //                 $qty        = (int) ($variation['qty'] ?? 0);

    //                 if ($qty <= 0) {
    //                     continue;
    //                 }

    //                 // 🔑 IMPORTANT FIX
    //                 // variation_key MUST be full variation type
    //                 $variationKey = $typeString;

    //                 ManageBranchProductStock::updateOrCreate(
    //                     [
    //                         'branch_id'     => $branchId,
    //                         'product_id'    => $productId,
    //                         'variation_key' => $variationKey,
    //                     ],
    //                     [
    //                         'variation_type' => $typeString,
    //                         'attributes'     => $typeString,
    //                         'current_stock'  => $qty,
    //                     ]
    //                 );
    //             }
    //         }
    //     }
    //     // if ($request->filled('branch_id')) {
    //     //     $branchId = $request->branch_id;
    //     //     $productId = $savedProduct->id;

    //     //     $choiceAttributes = $request->input('choice_attributes', []); 
    //     //     $choiceTitles = $request->input('choice', []);               

    //     //     $rawChoiceOptions = [];
    //     //     foreach ($choiceAttributes as $attrId) {
    //     //         $field = "choice_options_{$attrId}";
    //     //         if (!$request->has($field)) continue;

    //     //         $inputValue = $request->input($field);

    //     //         if (is_array($inputValue)) {
    //     //             $values = $inputValue;
    //     //         } else {
    //     //             $values = $inputValue ? explode(',', $inputValue) : [];
    //     //         }

    //     //         $rawChoiceOptions[$attrId] = array_filter(array_map('trim', $values));
    //     //     }

    //     //     $variations = json_decode($savedProduct->variation, true) ?? [];

    //     //     if (empty($variations)) {
    //     //         ManageBranchProductStock::updateOrCreate([
    //     //             'branch_id' => $branchId,
    //     //             'product_id' => $productId,
    //     //             'variation_key' => null,
    //     //         ], [
    //     //             'current_stock' => $request->current_stock ?? 0,
    //     //         ]);
    //     //     } else {
    //     //         foreach ($variations as $variation) {
    //     //             $typeString = $variation['type']; 
    //     //             $qty = (int)($variation['qty'] ?? 0);

    //     //             if ($qty <= 0) continue;

    //     //             $values = explode('-', $typeString); 

    //     //             $keyParts = [];
    //     //             foreach ($choiceAttributes as $index => $attrId) {
    //     //                 $title = strtolower($choiceTitles[$index] ?? "attr{$attrId}");
    //     //                 $value = $values[$index] ?? '';
    //     //                 $keyParts[] = "{$title}:{$value}";
    //     //             }
    //     //             $variationKey = implode(' | ', $keyParts);

    //     //             ManageBranchProductStock::updateOrCreate([
    //     //                 'branch_id' => $branchId,
    //     //                 'product_id' => $productId,
    //     //                 'variation_key' => $variationKey,
    //     //             ], [
    //     //                 'variation_type' => $typeString,
    //     //                 'variation_key' => $variationKey,
    //     //                 'attributes' => $variationKey, 
    //     //                 'current_stock' => $qty,
    //     //             ]);
    //     //         }
    //     //     }
    //     // }

    //     Toastr::success(translate('product_added_successfully'));
    //     return redirect()->route('admin.products.list', ['in_house']);
    // }
    public function updateProductAuthorAndPublishingHouse(object|array $request, object|array $product): void
    {
        if ($request['product_type'] == 'digital') {
            if ($request->has('authors')) {
                $authorIds = [];
                foreach ($request['authors'] as $author) {
                    $authorId = $this->authorRepo->updateOrCreate(params: ['name' => $author], value: ['name' => $author]);
                    $authorIds[] = $authorId?->id;
                }

                foreach ($authorIds as $author) {
                    $productAuthorData = ['author_id' => $author, 'product_id' => $product->id];
                    $this->digitalProductAuthorRepo->updateOrCreate(params: $productAuthorData, value: $productAuthorData);
                }

                $this->digitalProductAuthorRepo->deleteWhereNotIn(filters: ['product_id' => $product->id], whereNotIn: ['author_id' => $authorIds]);
            } else {
                $this->digitalProductAuthorRepo->delete(params: ['product_id' => $product->id]);
            }

            if ($request->has('publishing_house')) {
                $publishingHouseIds = [];
                foreach ($request['publishing_house'] as $publishingHouse) {
                    $publishingHouseId = $this->publishingHouseRepo->updateOrCreate(params: ['name' => $publishingHouse], value: ['name' => $publishingHouse]);
                    $publishingHouseIds[] = $publishingHouseId?->id;
                }

                foreach ($publishingHouseIds as $publishingHouse) {
                    $publishingHouseData = ['publishing_house_id' => $publishingHouse, 'product_id' => $product->id];
                    $this->digitalProductPublishingHouseRepo->updateOrCreate(params: $publishingHouseData, value: $publishingHouseData);
                }
                $this->digitalProductPublishingHouseRepo->deleteWhereNotIn(filters: ['product_id' => $product->id], whereNotIn: ['publishing_house_id' => $publishingHouseIds]);
            } else {
                $this->digitalProductPublishingHouseRepo->delete(params: ['product_id' => $product->id]);
            }
        } else {
            $this->digitalProductAuthorRepo->delete(params: ['product_id' => $product->id]);
            $this->digitalProductPublishingHouseRepo->delete(params: ['product_id' => $product->id]);
        }
    }

    public function getListView(Request $request, string $type): View
    {
        $filters = [
            'added_by' => $type,
            'request_status' => $request['status'],
            'seller_id' => $request['seller_id'],
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
            'sub_sub_category_id' => $request['sub_sub_category_id'],
        ];

        $products = $this->productRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request['searchValue'], filters: $filters, relations: ['clearanceSale' => function ($query) {
            return $query->active();
        }], dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT));
        $sellers = $this->sellerRepo->getByStatusExcept(status: 'pending', relations: ['shop'], paginateBy: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT));
        $brands = $this->brandRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]);
        $subSubCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_sub_category_id']]);
        return view(Product::LIST[VIEW], compact(
            'products',
            'sellers',
            'brands',
            'categories',
            'subCategory',
            'subSubCategory',
            'filters',
            'type'
        ));
    }

    public function getUpdateView(string|int $id): View|RedirectResponse
    {
        $product = $this->productRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $id], relations: ['digitalVariation', 'translations', 'seoInfo', 'digitalProductAuthors.author', 'digitalProductPublishingHouse.publishingHouse']);
        if (!$product) {
            Toastr::error(translate('product_not_found') . '!');
            return redirect()->route('admin.products.list', ['in_house']);
        }
        $productAuthorIds = $this->productService->getProductAuthorsInfo(product: $product)['ids'];
        $productPublishingHouseIds = $this->productService->getProductPublishingHouseInfo(product: $product)['ids'];

        $product['colors'] = json_decode($product['colors']);
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $makes = $this->vehicleMakeRepo->all();
        $models = $this->vehicleModelRepo->all();
        $years = VehicleYear::orderBy('year')->get();
        $branches = $this->branchRepo->getListWhere(dataLimit: 'all', filters: ['id' => 1]);
        $brands = $this->brandRepo->getListWhere(dataLimit: 'all');
        $brandSetting = getWebConfig(name: 'product_brand');
        $servicesSetting = getWebConfig(name: 'services');
        $languages = getWebConfig(name: 'pnc_language') ?? null;
        $colors = $this->colorRepo->getList(orderBy: ['name' => 'desc'], dataLimit: 'all');
        $attributes = $this->attributeRepo->getList(orderBy: ['name' => 'desc'], dataLimit: 'all');
        $defaultLanguage = $languages[0];
        $digitalProductFileTypes = ['audio', 'video', 'document', 'software'];
        $digitalProductAuthors = $this->authorRepo->getListWhere(dataLimit: 'all');
        $publishingHouseList = $this->publishingHouseRepo->getListWhere(dataLimit: 'all');

        return view(Product::UPDATE[VIEW], compact('product', 'categories', 'branches', 'brands', 'brandSetting', 'servicesSetting', 'colors', 'attributes', 'languages', 'defaultLanguage', 'digitalProductFileTypes', 'digitalProductAuthors', 'publishingHouseList', 'productAuthorIds', 'productPublishingHouseIds', 'makes', 'models', 'years'));
    }

    public function update(ProductUpdateRequest $request, ProductService $service, string|int $id): JsonResponse|RedirectResponse
    {
        if ($request->ajax()) {
            return response()->json([], 200);
        }

        $product = $this->productRepo->getFirstWhereWithoutGlobalScope(
            params: ['id' => $id],
            relations: ['digitalVariation', 'seoInfo']
        );

        $dataArray = $service->getUpdateProductData(request: $request, product: $product, updateBy: 'admin');
        $serviceArray =  $this->serviceService->getUpdateServiceData(request: $request);

        $this->updateProductAuthorAndPublishingHouse(request: $request, product: $product);

        $this->productRepo->update(id: $id, data: $dataArray);
        if ($request->product_type === 'services') {

            $serviceExists = $this->serviceRepo->getFirstWhere(['product_id' => $id]);

            if ($serviceExists) {
                $this->serviceRepo->update(id: $serviceExists->id, data: $serviceArray);
            } else {
                $serviceArray['product_id'] = $id;
                $this->serviceRepo->add($serviceArray);
            }

            ProductStockTransaction::deleteForProduct((int)$id);
            ProductStock::where('product_id', $id)->delete();
            ManageBranchProductStock::where('product_id', $id)->delete();
        }

        $this->productRepo->addRelatedTags(request: $request, product: $product);
        $this->translationRepo->update(request: $request, model: 'App\Models\Product', id: $id);

        self::getDigitalProductUpdateProcess($request, $product);

        $this->productSeoRepo->updateOrInsert(
            params: ['product_id' => $product['id']],
            data: $service->getProductSEOData(request: $request, product: $product, action: 'update')
        );

        $updatedProduct = $this->productRepo->getFirstWhere(params: ['id' => $product['id']]);

        $this->updateRestockRequestListAndNotify(product: $product, updatedProduct: $updatedProduct);
        $this->updateStockClearanceProduct(product: $updatedProduct);

        Toastr::success(translate('product_updated_successfully'));

        return redirect()->route(Product::VIEW[ROUTE], ['addedBy' => $product['added_by'], 'id' => $product['id']]);
    }


    public function updateStockClearanceProduct($product): void
    {
        $config = $this->stockClearanceSetupRepo->getFirstWhere(params: [
            'setup_by' => $product['added_by'] == 'admin' ? $product['added_by'] : 'vendor',
            'shop_id' => $product['added_by'] == 'admin' ? 0 : $product?->seller?->shop?->id,
        ]);
        $stockClearanceProduct = $this->stockClearanceProductRepo->getFirstWhere(params: ['product_id' => $product['id']]);

        if ($config && $config['discount_type'] == 'product_wise' && $stockClearanceProduct && $stockClearanceProduct['discount_type'] == 'flat') {
            $minimumPrice = $product['unit_price'];
            foreach ((json_decode($product['variation'], true) ?? []) as $variation) {
                if ($variation['price'] < $minimumPrice) {
                    $minimumPrice = $variation['price'];
                }
            }

            if ($minimumPrice < $stockClearanceProduct['discount_amount']) {
                $this->stockClearanceProductRepo->updateByParams(params: ['product_id' => $product['id']], data: ['is_active' => 0]);
            }
        }
    }

    public function getDigitalProductUpdateProcess($request, $product): void
    {
        if ($request->has('digital_product_variant_key') && !$request->hasFile('digital_file_ready')) {
            $getAllVariation = $this->digitalProductVariationRepo->getListWhere(filters: ['product_id' => $product['id']]);
            $getAllVariationKey = $getAllVariation->pluck('variant_key')->toArray();
            $getRequestVariationKey = $request['digital_product_variant_key'];
            $differenceFromDB = array_diff($getAllVariationKey, $getRequestVariationKey);
            $differenceFromRequest = array_diff($getRequestVariationKey, $getAllVariationKey);
            $newCombinations = array_merge($differenceFromDB, $differenceFromRequest);

            foreach ($newCombinations as $newCombination) {
                if (in_array($newCombination, $request['digital_product_variant_key'])) {
                    $uniqueKey = strtolower(str_replace('-', '_', $newCombination));

                    $fileItem = null;
                    if ($request['digital_product_type'] == 'ready_product') {
                        $fileItem = $request->file('digital_files.' . $uniqueKey);
                    }
                    $uploadedFile = '';
                    if ($fileItem) {
                        $uploadedFile = $this->fileUpload(dir: 'product/digital-product/', format: $fileItem->getClientOriginalExtension(), file: $fileItem);
                    }
                    $this->digitalProductVariationRepo->add(data: [
                        'product_id' => $product['id'],
                        'variant_key' => $request->input('digital_product_variant_key.' . $uniqueKey),
                        'sku' => $request->input('digital_product_sku.' . $uniqueKey),
                        'price' => currencyConverter(amount: $request->input('digital_product_price.' . $uniqueKey)),
                        'file' => $uploadedFile,
                    ]);
                }
            }

            foreach ($differenceFromDB as $variation) {
                $variation = $this->digitalProductVariationRepo->getFirstWhere(params: ['product_id' => $product['id'], 'variant_key' => $variation]);
                if ($variation) {
                    $this->digitalProductVariationRepo->delete(params: ['id' => $variation['id']]);
                }
            }

            foreach ($getAllVariation as $variation) {
                if (in_array($variation['variant_key'], $request['digital_product_variant_key'])) {
                    $uniqueKey = strtolower(str_replace('-', '_', $variation['variant_key']));

                    $fileItem = null;
                    if ($request['digital_product_type'] == 'ready_product') {
                        $fileItem = $request->file('digital_files.' . $uniqueKey);
                    }
                    $uploadedFile = $variation['file'] ?? '';
                    $variation = $this->digitalProductVariationRepo->getFirstWhere(params: ['product_id' => $product['id'], 'variant_key' => $variation['variant_key']]);
                    if ($fileItem) {
                        $uploadedFile = $this->fileUpload(dir: 'product/digital-product/', format: $fileItem->getClientOriginalExtension(), file: $fileItem);
                    }
                    $this->digitalProductVariationRepo->updateByParams(params: ['product_id' => $product['id'], 'variant_key' => $variation['variant_key']], data: [
                        'variant_key' => $request->input('digital_product_variant_key.' . $uniqueKey),
                        'sku' => $request->input('digital_product_sku.' . $uniqueKey),
                        'price' => currencyConverter(amount: $request->input('digital_product_price.' . $uniqueKey)),
                        'file' => $uploadedFile,
                    ]);
                }

                if ($request['product_type'] == 'physical' || $request['digital_product_type'] == 'ready_after_sell') {
                    $variation = $this->digitalProductVariationRepo->getFirstWhere(params: ['product_id' => $product['id'], 'variant_key' => $variation['variant_key']]);
                    if ($variation && $variation['file']) {
                        $this->digitalProductVariationRepo->updateByParams(params: ['id' => $variation['id']], data: ['file' => '']);
                    }
                    if ($request['product_type'] == 'physical') {
                        $variation->delete();
                    }
                }
            }
        } else {
            $this->digitalProductVariationRepo->delete(params: ['product_id' => $product['id']]);
        }
    }

    public function getView(string $addedBy, string|int $id): View|RedirectResponse
    {
        $productActive = $this->productRepo->getFirstWhere(params: ['id' => $id], relations: ['digitalVariation', 'seoInfo']);
        if (!$productActive) {
            Toastr::error(translate('product_not_found') . '!');
            return redirect()->route('admin.products.list', ['in_house']);
        }
        $isActive = $this->productRepo->getWebFirstWhereActive(params: ['id' => $id]);
        $relations = ['branches', 'category', 'brand', 'reviews', 'rating', 'orderDetails', 'orderDelivered', 'digitalVariation', 'seoInfo', 'clearanceSale' => function ($query) {
            return $query->active();
        }];
        $product = $this->productRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $id], relations: $relations);
        $product['priceSum'] = $product?->orderDelivered->sum('price');
        $product['qtySum'] = $product?->orderDelivered->sum('qty');
        $product['discountSum'] = $product?->orderDelivered->sum('discount');
        $productColors = [];
        $colors = json_decode($product['colors']);
        foreach ($colors as $color) {
            $getColor = $this->colorRepo->getFirstWhere(params: ['code' => $color]);
            if ($getColor) {
                $productColors[$getColor['name']] = $colors;
            }
        }

        $reviews = $this->reviewRepo->getListWhere(filters: ['product_id' => ['product_id' => $id], 'whereNull' => ['column' => 'delivery_man_id']], relations: ['customer', 'reply'], dataLimit: getWebConfig(name: 'pagination_limit'));

        return view(Product::VIEW[VIEW], compact('product', 'reviews', 'productActive', 'productColors', 'addedBy', 'isActive'));
    }

    public function getSkuCombinationView(Request $request, ProductService $service): JsonResponse
    {
        $product = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']], relations: ['digitalVariation', 'seoInfo']);
        $combinationView = $service->getSkuCombinationView(request: $request, product: $product);
        return response()->json(['view' => $combinationView]);
    }

    public function getDigitalVariationCombinationView(Request $request, ProductService $service): JsonResponse
    {
        $product = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']], relations: ['digitalVariation', 'seoInfo']);
        $combinationView = $service->getDigitalVariationCombinationView(request: $request, product: $product);
        return response()->json(['view' => $combinationView]);
    }

    public function deleteDigitalVariationFile(Request $request, ProductService $service): JsonResponse
    {
        $variation = $this->digitalProductVariationRepo->getFirstWhere(params: ['product_id' => $request['product_id'], 'variant_key' => $request['variant_key']]);
        if ($variation) {
            $this->deleteFile(filePath: '/product/digital-product/' . $variation['file']);
            $this->digitalProductVariationRepo->updateByParams(params: ['id' => $variation['id']], data: ['file' => null]);
            return response()->json([
                'status' => 1,
                'message' => translate('delete_successful')
            ]);
        }
        return response()->json([
            'status' => 0,
            'message' => translate('delete_unsuccessful')
        ]);
    }

    public function updateFeaturedStatus(Request $request): JsonResponse
    {
        $status = $request['status'];

        $productId = $request['id'];
        $product = $this->productRepo->getFirstWhere(params: ['id' => $productId]);
        $updateData = [
            'featured' => is_null($product['featured']) || $product['featured'] == 0 ? 1 : 0
        ];
        $this->productRepo->update(id: $productId, data: $updateData);

        return response()->json($status);
    }
    public function updateProductCmsStatus(Request $request): JsonResponse
    {
        $status = $request['status'];

        $productId = $request['id'];
        $product = $this->productRepo->getFirstWhere(params: ['id' => $productId]);
        $updateData = [
            'show_cms' => $product['show_cms'] == 0 ? 1 : 0
        ];

        $this->productRepo->update(id: $productId, data: $updateData);

        return response()->json($status);
    }
    public function updateProductShowcaseStatus(Request $request): JsonResponse
    {
        $status = $request['status'];

        $productId = $request['id'];
        $product = $this->productRepo->getFirstWhere(params: ['id' => $productId]);
        $updateData = [
            'showcase_product' => $product['showcase_product'] == 0 ? 1 : 0
        ];

        $this->productRepo->update(id: $productId, data: $updateData);

        return response()->json($status);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $status = $request->get('status', 0);
        $productId = $request['id'];
        $product = $this->productRepo->getFirstWhere(params: ['id' => $productId]);

        $success = 1;
        if ($status == 1) {
            $success = $product->added_by == 'seller' && ($product['request_status'] == 0 || $product['request_status'] == 2) ? 0 : 1;
        }
        $updateData = ['status' => $status];
        $data = $success ? $this->productRepo->update(id: $productId, data: $updateData) : null;

        return response()->json([
            'success' => $success,
            'data' => $data,
            'message' => $success ? translate("status_updated_successfully") : translate("status_updated_failed") . ' ' . translate("Product_must_be_approved"),
        ], 200);
    }

    public function deleteImage(Request $request, ProductService $service): RedirectResponse
    {
        $this->deleteFile(filePath: '/product/' . $request['image']);
        $product = $this->productRepo->getFirstWhere(params: ['id' => $request['id']]);

        if (count(json_decode($product['images'])) < 2) {
            Toastr::warning(translate('you_can_not_delete_all_images'));
            return back();
        }

        $imageProcessing = $service->deleteImage(request: $request, product: $product);

        $updateData = [
            'images' => json_encode($imageProcessing['images']),
            'color_image' => json_encode($imageProcessing['color_images']),
        ];
        $this->productRepo->update(id: $request['id'], data: $updateData);

        Toastr::success(translate('product_image_removed_successfully'));
        return back();
    }

    public function getCategories(Request $request, ProductService $service): JsonResponse
    {
        $parentId = $request->input('parent_id', 0);
        $categories = $this->categoryRepo->getListWhere(
            filters: ['parent_id' => $parentId],
            dataLimit: 'all'
        );

        $dropdown = $service->getCategoryDropdown(request: $request, categories: $categories);

        $childCategories = '<option value="" disabled selected>---Select---</option>';

        if ($categories->isNotEmpty()) {
            $firstChild = $categories->first(); // id=19
            $grandChildren = $this->categoryRepo->getListWhere(
                filters: ['parent_id' => $firstChild->id],
                dataLimit: 'all'
            );

            if ($grandChildren->isNotEmpty()) {
                $childCategories = $service->getCategoryDropdown(
                    request: $request,
                    categories: $grandChildren
                );
            }
        }

        return response()->json([
            'select_tag' => $dropdown,
            'sub_categories' => $childCategories,
        ]);
    }



    public function getSubCategories(Request $request, ProductService $service): JsonResponse
    {
        $parentId = $request->input('parent_id', 0);

        // Fetch direct child categories (sub-categories)
        $subCategories = $this->categoryRepo->getListWhere(
            filters: ['parent_id' => $parentId],
            dataLimit: 'all'
        );

        // Build dropdown for sub-categories
        $childCategories = '<option value="" disabled selected>---Select---</option>';
        if ($subCategories->isNotEmpty()) {
            $childCategories = $service->getCategoryDropdown(
                request: $request,
                categories: $subCategories
            );
        }

        return response()->json([
            'sub_categories' => $childCategories,
        ]);
    }


    public function getProductAttributes($productId): JsonResponse
    {
        $product = Products::find($productId);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found']);
        }

        $attributes = $product->productAttributes;

        return response()->json([
            'success' => true,
            'data' => $attributes
        ]);
    }


    public function exportList(Request $request, string $type): BinaryFileResponse
    {
        $filters = [
            'added_by' => $type == 'in-house' ? 'in_house' : $type,
            'request_status' => $request['status'],
            'seller_id' => $request['seller_id'],
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
            'sub_sub_category_id' => $request['sub_sub_category_id'],
        ];

        $products = $this->productRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request['searchValue'], filters: $filters, dataLimit: 'all');

        $category = (!empty($request['category_id']) && $request->has('category_id')) ? $this->categoryRepo->getFirstWhere(params: ['id' => $request['category_id']]) : 'all';
        $subCategory = (!empty($request->sub_category_id) && $request->has('sub_category_id')) ? $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]) : 'all';
        $subSubCategory = (!empty($request->sub_sub_category_id) && $request->has('sub_sub_category_id')) ? $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_sub_category_id']]) : 'all';
        $brand = (!empty($request->brand_id) && $request->has('brand_id')) ? $this->brandRepo->getFirstWhere(params: ['id' => $request->brand_id]) : 'all';
        $seller = (!empty($request->seller_id) && $request->has('seller_id')) ? $this->sellerRepo->getFirstWhere(params: ['id' => $request->seller_id]) : '';
        $data = [
            'products' => $products,
            'category' => $category,
            'sub_category' => $subCategory,
            'sub_sub_category' => $subSubCategory,
            'brand' => $brand,
            'searchValue' => $request['searchValue'],
            'type' => $request->type ?? '',
            'seller' => $seller,
            'status' => $request->status ?? '',
        ];
        return Excel::download(new ProductListExport($data), ucwords($request['type']) . '-' . 'product-list.xlsx');
    }

    public function getBarcodeView(Request $request, string|int $id): View|RedirectResponse
    {
        if ($request['limit'] > 270) {
            Toastr::warning(translate('you_can_not_generate_more_than_270_barcode'));
            return back();
        }
        $product = $this->productRepo->getFirstWhere(params: ['id' => $id]);
        $rangeData = range(1, $request->limit ?? 4);
        $barcodes = array_chunk($rangeData, 24);
        return view(Product::BARCODE_VIEW[VIEW], compact('product', 'barcodes'));
    }

    public function getStockLimitListView(Request $request, string $type): View
    {
        $stockLimit = getWebConfig(name: 'stock_limit');
        $sortOrderQty = $request['sortOrderQty'];
        $searchValue = $request['searchValue'];
        $withCount = ['orderDetails'];
        $status = $request['status'];
        $filters = [
            'added_by' => $type,
            'product_type' => 'physical',
            'request_status' => $request['status'],
        ];

        $orderBy = [];
        if ($sortOrderQty == 'quantity_asc') {
            $orderBy = ['current_stock' => 'asc'];
        } else if ($sortOrderQty == 'quantity_desc') {
            $orderBy = ['current_stock' => 'desc'];
        } elseif ($sortOrderQty == 'order_asc') {
            $orderBy = ['order_details_count' => 'asc'];
        } elseif ($sortOrderQty == 'order_desc') {
            $orderBy = ['order_details_count' => 'desc'];
        } elseif ($sortOrderQty == 'default') {
            $orderBy = ['id' => 'asc'];
        }
        $products = $this->productRepo->getStockLimitListWhere(orderBy: $orderBy, searchValue: $searchValue, filters: $filters, withCount: $withCount, dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT));
        return view(Product::STOCK_LIMIT[VIEW], compact('products', 'searchValue', 'status', 'sortOrderQty', 'stockLimit'));
    }
    public function getStockLimitListViewProducts(Request $request, string $type): View
    {
        $sortOrderQty = $request['sortOrderQty'] ?? 'default';
        $searchValue  = $request['searchValue'];
        $status       = $request['status'];

        $withCount = ['orderDetails'];
        $filters = [
            'added_by' => $type,
            'product_type' => 'physical',
            'request_status' => $status,
        ];

        $orderBy = [];
        if ($sortOrderQty === 'quantity_asc') {
            $orderBy = ['current_stock' => 'asc'];
        } elseif ($sortOrderQty === 'quantity_desc') {
            $orderBy = ['current_stock' => 'desc'];
        } elseif ($sortOrderQty === 'order_asc') {
            $orderBy = ['order_details_count' => 'asc'];
        } elseif ($sortOrderQty === 'order_desc') {
            $orderBy = ['order_details_count' => 'desc'];
        } else {
            $orderBy = ['id' => 'asc'];
        }

        $products = $this->productRepo->getStockLimitProductsWhere(
            orderBy: $orderBy,
            searchValue: $searchValue,
            filters: $filters,
            withCount: ['orderDetails'],
            relations: [],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );

        return view(
            Product::STOCK_LIMIT_PRODUCTS[VIEW],
            compact('products', 'searchValue', 'status', 'sortOrderQty')
        );
    }
    // public function getStockLimitListViewProducts(Request $request, string $type): View
    // {
    //     $sortOrderQty = $request['sortOrderQty'];
    //     $searchValue = $request['searchValue'];
    //     $withCount = ['orderDetails'];
    //     $status = $request['status'];
    //     $filters = [
    //         'added_by' => $type,
    //         'product_type' => 'physical',
    //         'request_status' => $request['status'],
    //     ];

    //     $orderBy = [];
    //     if ($sortOrderQty == 'quantity_asc') {
    //         $orderBy = ['current_stock' => 'asc'];
    //     } else if ($sortOrderQty == 'quantity_desc') {
    //         $orderBy = ['current_stock' => 'desc'];
    //     } elseif ($sortOrderQty == 'order_asc') {
    //         $orderBy = ['order_details_count' => 'asc'];
    //     } elseif ($sortOrderQty == 'order_desc') {
    //         $orderBy = ['order_details_count' => 'desc'];
    //     } elseif ($sortOrderQty == 'default') {
    //         $orderBy = ['id' => 'asc'];
    //     }
    //     $products = $this->productRepo->getStockLimitProductsWhere(orderBy: $orderBy, searchValue: $searchValue, filters: $filters, withCount: $withCount, dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT));
    //     return view(Product::STOCK_LIMIT_PRODUCTS[VIEW], compact('products', 'searchValue', 'status', 'sortOrderQty'));
    // }

    public function delete(string|int $id, ProductService $service): RedirectResponse
    {
        $product = $this->productRepo->getFirstWhere(params: ['id' => $id]);

        if ($product) {
            $this->translationRepo->delete(model: 'App\Models\Product', id: $id);
            $this->cartRepo->delete(params: ['product_id' => $id]);
            $this->wishlistRepo->delete(params: ['product_id' => $id]);
            $this->flashDealProductRepo->delete(params: ['product_id' => $id]);
            $this->dealOfTheDayRepo->delete(params: ['product_id' => $id]);
            ProductStockTransaction::deleteForProduct((int)$id);
            ProductStock::where('product_id', $id)->delete();
            ManageBranchProductStock::where('product_id', $id)->delete();
            WholeSaleProducts::where('product_id', $id)->delete();

            $service->deleteImages(product: $product);

            $product->delete();
            $bannerIds = $this->bannerRepo->getListWhere(filters: ['resource_type' => 'product', 'resource_id' => $product['id']])->pluck('id');
            $bannerIds->map(function ($bannerId) {
                $this->bannerRepo->update(id: $bannerId, data: ['published' => 0, 'resource_id' => null]);
            });

            Toastr::success(translate('product_removed_successfully'));
        } else {
            Toastr::error(translate('invalid_product'));
        }

        return back();
    }


    public function deleteRestock(string|int $id): RedirectResponse
    {
        $this->restockProductRepo->delete(params: ['id' => $id]);
        $this->restockProductCustomerRepo->delete(params: ['restock_product_id' => $id]);
        Toastr::success(translate('product_restock_removed_successfully'));
        return back();
    }

public function getVariations(Request $request): JsonResponse
{
    $product = $this->productRepo->getFirstWhere(params: ['id' => $request['id']]);
    $restockId = $request['restock_id'];

    try {
        $restockVariants = $this->restockProductRepo->getListWhereBetween(filters: ['product_id' => $request['id']])?->pluck('variant')->toArray() ?? [];
    } catch (\Exception $e) {
        \Log::error('Error in getListWhereBetween', ['message' => $e->getMessage()]);
        $restockVariants = [];
    }

    $branches = \App\Models\Branch::query()
        ->select('id', 'branch_name')
        ->orderBy('id')
        ->get();

    $selectedVariation = $request->variation; // Red / Yellow

    if ($selectedVariation && !empty($product->variation)) {
        $variations = collect(json_decode($product->variation, true))
            ->filter(fn ($v) => $v['type'] === $selectedVariation)
            ->values()
            ->toArray();

        // override product variations
        $product->variation = json_encode($variations);
    }
    
    return response()->json([
        'view' => view(Product::GET_VARIATIONS[VIEW], compact('product', 'restockId', 'restockVariants', 'branches'))->render()
    ]);
}

public function getStockReport(Request $request): JsonResponse|View|BinaryFileResponse|Response
{
    $isJsonRequest = $request->ajax() || $request->expectsJson();

    $request->validate([
        'product_id' => ($isJsonRequest ? 'required' : 'nullable') . '|integer|exists:products,id',
        'category_id' => 'nullable|integer|exists:categories,id',
        'variation' => 'nullable|string',
        'date_type' => 'nullable|in:this_year,this_month,this_week,today,custom_date',
        'from' => 'nullable|date',
        'to' => 'nullable|date|after_or_equal:from',
        'from_date' => 'nullable|date',
        'to_date' => 'nullable|date|after_or_equal:from_date',
        'include_internal_transfer' => 'nullable|boolean',
    ]);

    $selectedCategoryId = !empty($request->category_id) ? (int)$request->category_id : null;
    $selectedProductId = !empty($request->product_id) ? (int)$request->product_id : null;
    [$dateType, $fromDate, $toDate] = $this->resolveStockReportDateRange($request);

    $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
    $productsForFilter = Products::query()
        ->select(['id', 'name', 'category_id', 'added_by'])
        ->when($selectedCategoryId, fn($query) => $query->where('category_id', $selectedCategoryId))
        ->orderBy('name')
        ->get();

    if ($selectedProductId && !$productsForFilter->contains('id', $selectedProductId)) {
        $selectedProduct = Products::query()
            ->select(['id', 'name', 'category_id', 'added_by'])
            ->where('id', $selectedProductId)
            ->first();

        if ($selectedProduct) {
            $productsForFilter->prepend($selectedProduct);
        }
    }

    if (!$selectedProductId) {
        return view('admin-views.product.stock-report', [
            'reportReady' => false,
            'categories' => $categories,
            'productsForFilter' => $productsForFilter,
            'filters' => [
                'category_id' => $selectedCategoryId,
                'product_id' => null,
                'variation' => null,
                'date_type' => $dateType,
                'from' => $fromDate,
                'to' => $toDate,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'include_internal_transfer' => (bool)$request->boolean('include_internal_transfer'),
            ],
        ]);
    }

    $product = $this->productRepo->getFirstWhere(params: ['id' => $selectedProductId]);
    if (!$product) {
        if ($isJsonRequest) {
            return response()->json(['view' => ''], 404);
        }

        return view('admin-views.product.stock-report', [
            'reportReady' => false,
            'categories' => $categories,
            'productsForFilter' => $productsForFilter,
            'filters' => [
                'category_id' => $selectedCategoryId,
                'product_id' => null,
                'variation' => null,
                'date_type' => $dateType,
                'from' => $fromDate,
                'to' => $toDate,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'include_internal_transfer' => (bool)$request->boolean('include_internal_transfer'),
            ],
        ]);
    }

    $variation = $this->normalizeReportVariation((string)$request->query('variation', ''));
    $includeInternalTransfer = (bool)$request->boolean('include_internal_transfer');

    $productStockQuery = ProductStock::query()->where('product_id', (int)$product->id);
    if ($variation !== null) {
        $productStockQuery->where('variant', $variation);
    } else {
        $productStockQuery->where(function ($query) {
            $query->whereNull('variant')->orWhere('variant', '');
        });
    }

    $productStocks = $productStockQuery->get();
    $stockIds = $productStocks->pluck('id')->all();

    $transactionsQuery = ProductStockTransaction::query()
        ->with(['fromBranch:id,branch_name', 'toBranch:id,branch_name'])
        ->whereIn('product_stock_id', $stockIds)
        ->orderByDesc('id');

    if (!empty($fromDate)) {
        $transactionsQuery->where('created_at', '>=', Carbon::parse($fromDate)->startOfDay());
    }

    if (!empty($toDate)) {
        $transactionsQuery->where('created_at', '<=', Carbon::parse($toDate)->endOfDay());
    }

    if (!$includeInternalTransfer) {
        $transactionsQuery->where('reason', '!=', StockReason::BRANCH_TRANSFER);
    }

    $transactions = $transactionsQuery->get();

    $summary = [
        'stock_in' => [
            'initial_stock' => 0,
            'manual_adjust_add' => 0,
            'returns' => 0,
        ],
        'stock_out' => [
            'sales_pos' => 0,
            'sales_online' => 0,
            'sales_wholesale_transfer' => 0,
            'manual_adjust_negative' => 0,
        ],
        'internal_transfer' => [
            'in' => 0,
            'out' => 0,
        ],
    ];

    $historyRows = [];
    foreach ($transactions as $transaction) {
        $classified = $this->classifyStockTransaction($transaction);

        if ($classified['summaryGroup'] === 'stock_in' && isset($summary['stock_in'][$classified['summaryKey']])) {
            $summary['stock_in'][$classified['summaryKey']] += (int)$transaction->quantity;
        } elseif ($classified['summaryGroup'] === 'stock_out' && isset($summary['stock_out'][$classified['summaryKey']])) {
            $summary['stock_out'][$classified['summaryKey']] += (int)$transaction->quantity;
        } elseif ($classified['summaryGroup'] === 'internal_transfer') {
            $transferKey = strtoupper((string)$transaction->type) === 'IN' ? 'in' : 'out';
            $summary['internal_transfer'][$transferKey] += (int)$transaction->quantity;
        }

        $historyRows[] = [
            'date' => $transaction->created_at,
            'type' => strtoupper((string)$transaction->type),
            'quantity' => (int)$transaction->quantity,
            'reason' => (string)$transaction->reason,
            'category' => $classified['label'],
            'remarks' => (string)($transaction->remarks ?? ''),
            'from_branch' => $transaction->fromBranch?->branch_name,
            'to_branch' => $transaction->toBranch?->branch_name,
        ];
    }

    $currentStock = (int)$productStocks->sum('qty');
    if ($variation === null && $productStocks->isEmpty()) {
        $currentStock = (int)($product->current_stock ?? 0);
    }

    $baseParams = ['product_id' => (int)$product->id];
    if ($variation !== null) {
        $baseParams['variation'] = $variation;
    }
    if ($selectedCategoryId) {
        $baseParams['category_id'] = $selectedCategoryId;
    }
    if (!empty($dateType)) {
        $baseParams['date_type'] = $dateType;
    }
    if ($dateType === 'custom_date') {
        if (!empty($fromDate)) {
            $baseParams['from'] = $fromDate;
        }
        if (!empty($toDate)) {
            $baseParams['to'] = $toDate;
        }
    }
    $reportBaseUrl = route('admin.products.stock-report') . '?' . http_build_query($baseParams);

    $stockReportData = [
        'product' => $product,
        'variation' => $variation,
        'currentStock' => $currentStock,
        'summary' => $summary,
        'historyRows' => $historyRows,
        'includeInternalTransfer' => $includeInternalTransfer,
        'reportBaseUrl' => $reportBaseUrl,
        'categories' => $categories,
        'productsForFilter' => $productsForFilter,
        'filters' => [
            'category_id' => $selectedCategoryId,
            'product_id' => $selectedProductId,
            'variation' => $variation,
            'date_type' => $dateType,
            'from' => $fromDate,
            'to' => $toDate,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'include_internal_transfer' => $includeInternalTransfer,
        ],
        'reportReady' => true,
    ];

    $download = strtolower((string)$request->query('download', ''));
    if ($download === 'excel') {
        return $this->exportStockReportExcel($stockReportData);
    }
    if ($download === 'pdf') {
        return $this->exportStockReportPdf($stockReportData);
    }

    if ($request->ajax() || $request->expectsJson()) {
        $view = view(Product::STOCK_REPORT[VIEW], $stockReportData)->render();
        return response()->json(['view' => $view]);
    }

    return view('admin-views.product.stock-report', $stockReportData);
}

private function exportStockReportExcel(array $reportData): BinaryFileResponse
{
    $rows = collect($reportData['historyRows'] ?? [])->map(function (array $row) {
        return [
            Carbon::parse($row['date'])->format('Y-m-d H:i:s'),
            strtoupper((string)$row['type']) === 'IN' ? 'Stock In' : 'Stock Out',
            (int)$row['quantity'],
            (string)($row['category'] ?? ''),
            str_replace('_', ' ', (string)($row['reason'] ?? '')),
            (string)($row['remarks'] ?? ''),
            (string)($row['from_branch'] ?? ''),
            (string)($row['to_branch'] ?? ''),
        ];
    })->values()->all();

    return Excel::download(new class($rows) implements FromArray, WithHeadings {
        public function __construct(private readonly array $rows) {}
        public function array(): array
        {
            return $this->rows;
        }
        public function headings(): array
        {
            return ['Date', 'Type', 'Quantity', 'Category', 'Reference', 'Remarks', 'From Branch', 'To Branch'];
        }
    }, 'stock-report.xlsx');
}

private function exportStockReportPdf(array $reportData): Response
{
    return app(ReportPdfService::class)->download(
        view: 'admin-views.product.stock-report-pdf',
        data: $reportData,
        fileName: 'stock-report.pdf',
        orientation: 'landscape'
    );
}

private function resolveStockReportDateRange(Request $request): array
{
    $legacyFrom = $request->input('from_date');
    $legacyTo = $request->input('to_date');
    $dateType = (string)$request->input('date_type', (!empty($legacyFrom) || !empty($legacyTo)) ? 'custom_date' : 'this_year');
    $fromInput = $request->input('from', $legacyFrom);
    $toInput = $request->input('to', $legacyTo);

    switch ($dateType) {
        case 'this_month':
            $fromDate = now()->startOfMonth()->toDateString();
            $toDate = now()->endOfMonth()->toDateString();
            break;
        case 'this_week':
            $fromDate = now()->startOfWeek()->toDateString();
            $toDate = now()->endOfWeek()->toDateString();
            break;
        case 'today':
            $fromDate = now()->toDateString();
            $toDate = now()->toDateString();
            break;
        case 'custom_date':
            $fromDate = $fromInput ? Carbon::parse($fromInput)->toDateString() : '';
            $toDate = $toInput ? Carbon::parse($toInput)->toDateString() : '';
            break;
        case 'this_year':
        default:
            $dateType = 'this_year';
            $fromDate = now()->startOfYear()->toDateString();
            $toDate = now()->endOfYear()->toDateString();
            break;
    }

    if (!empty($fromDate) && !empty($toDate) && Carbon::parse($fromDate)->gt(Carbon::parse($toDate))) {
        [$fromDate, $toDate] = [$toDate, $fromDate];
    }

    return [$dateType, $fromDate, $toDate];
}
     
public function updateQuantity(Request $request): RedirectResponse
{
    $product = $this->productRepo->getFirstWhere(['id' => $request->product_id]);
    if (!$product) {
        Toastr::error(translate('invalid_product'));
        return back();
    }

    return $this->updateQuantityByInventoryService($request, $product);
}

    private function updateQuantityByInventoryService(Request $request, Products $product): RedirectResponse
    {
        $systemBranchId = 1;
        $deductionBranchId = (int)($request->input('deduction_branch_id') ?? 0);
        $hasValidDeductionBranch = $deductionBranchId > 0
            ? \App\Models\Branch::query()->where('id', $deductionBranchId)->exists()
            : false;
        $reason = StockReason::MANUAL_ADJUSTMENT;
        $variationReasons = (array)$request->input('variation_reason', []);

        // Simple product (no variants): adjust absolute quantity.
        if (empty($product->variation)) {
            $requestedQty = (int)$request->current_stock;
            if ($requestedQty < 0) {
                Toastr::warning(translate('product_quantity_can_not_be_less_than_0_'));
                return back();
            }

            $currentQty = (int)ProductStock::where('product_id', $product->id)
                ->where(function ($query) {
                    $query->whereNull('variant')->orWhere('variant', '');
                })
                ->value('qty');
            if ($currentQty <= 0) {
                $currentQty = (int)$product->current_stock;
            }

            $delta = $requestedQty - $currentQty;
            if ($delta !== 0) {
                if ($delta < 0 && !$hasValidDeductionBranch) {
                    Toastr::warning(translate('please_select_deduction_branch_for_stock_reduction'));
                    return back();
                }

                $targetBranchId = $delta < 0 ? $deductionBranchId : $systemBranchId;
                $response = $this->inventoryMutationService->manualAdjust(
                    productId: (int)$product->id,
                    branchId: $targetBranchId,
                    variant: null,
                    delta: $delta,
                    note: (string)($request->stock_reason ?? ''),
                    stockReason: $reason,
                    referenceId: (int)$product->id,
                    context: 'Admin Product Quantity Update'
                );
                if (!($response['status'] ?? false)) {
                    Toastr::error($response['message'] ?? translate('something_went_wrong'));
                    return back();
                }
            }

            Toastr::success(translate('product_quantity_updated_successfully'));
            return back();
        }

        // Variant product: each submitted variant is adjusted to requested total qty.
        $variations = json_decode($product->variation, true);
        if (!is_array($variations)) {
            $variations = [];
        }

        $allVariationTypes = array_column($variations, 'type');
        $submittedVariations = [];
        foreach ($allVariationTypes as $type) {
            $fieldName = 'qty_' . str_replace('.', '_', $type);
            if ($request->has($fieldName)) {
                $submittedVariations[$type] = (int)$request->$fieldName;
            }
        }

        if (empty($submittedVariations)) {
            Toastr::warning(translate('no_stock_changes_detected'));
            return back();
        }

        $variationQtyMap = [];
        foreach ($variations as $variationRow) {
            $variationType = trim((string)($variationRow['type'] ?? ''));
            if ($variationType === '') {
                continue;
            }
            $variationQtyMap[$variationType] = max(0, (int)($variationRow['qty'] ?? 0));
        }

        foreach ($submittedVariations as $type => $requestedTotalQty) {
            if ($requestedTotalQty < 0) {
                Toastr::warning(translate('product_quantity_can_not_be_less_than_0_'));
                return back();
            }

            $ledgerQty = ProductStock::where('product_id', $product->id)
                ->where('variant', $type)
                ->value('qty');
            if (is_null($ledgerQty)) {
                $branchQty = (int)ManageBranchProductStock::where('product_id', $product->id)
                    ->where(function ($query) use ($type) {
                        $query->where('variation_key', $type)
                            ->orWhere('variation_type', $type);
                    })->sum('current_stock');

                $ledgerQty = $branchQty > 0 ? $branchQty : (int)($variationQtyMap[$type] ?? 0);
            }
            $currentTotalQty = max(0, (int)$ledgerQty);

            $delta = $requestedTotalQty - $currentTotalQty;
            if ($delta === 0) {
                continue;
            }

            if ($delta < 0 && !$hasValidDeductionBranch) {
                Toastr::warning(translate('please_select_deduction_branch_for_stock_reduction'));
                return back();
            }

            $targetBranchId = $delta < 0 ? $deductionBranchId : $systemBranchId;
            $response = $this->inventoryMutationService->manualAdjust(
                productId: (int)$product->id,
                branchId: $targetBranchId,
                variant: $type,
                delta: $delta,
                note: (string)($variationReasons[$type] ?? ''),
                stockReason: $reason,
                referenceId: (int)$product->id,
                context: 'Admin Product Variation Quantity Update'
            );
            if (!($response['status'] ?? false)) {
                Toastr::error($response['message'] ?? translate('something_went_wrong'));
                return back();
            }
        }

        // Keep compatibility mirrors synced to ProductStock totals.
        $freshProduct = $this->productRepo->getFirstWhere(['id' => $product->id]);
        $updatedVariations = json_decode($freshProduct->variation, true);
        if (!is_array($updatedVariations)) {
            $updatedVariations = [];
        }

        $newTotalStock = 0;
        foreach ($updatedVariations as $idx => $variation) {
            $type = trim((string)($variation['type'] ?? ''));
            if ($type === '') {
                continue;
            }
            $variantTotal = (int)ProductStock::where('product_id', $freshProduct->id)
                ->where('variant', $type)
                ->value('qty');
            $updatedVariations[$idx]['qty'] = max(0, $variantTotal);
            $newTotalStock += max(0, $variantTotal);
        }

        $freshProduct->current_stock = max(0, $newTotalStock);
        $freshProduct->variation = json_encode($updatedVariations);
        $freshProduct->save();

        Toastr::success(translate('product_quantity_updated_successfully'));
        return back();
    }

    private function normalizeReportVariation(string $variation): ?string
    {
        $value = trim($variation);
        if ($value === '' || strtolower($value) === 'null') {
            return null;
        }
        return $value;
    }

    private function classifyStockTransaction(ProductStockTransaction $transaction): array
    {
        $reason = strtoupper(trim((string)$transaction->reason));
        $type = strtoupper(trim((string)$transaction->type));
        $remarks = strtoupper((string)($transaction->remarks ?? ''));

        if ($reason === StockReason::INITIAL_STOCK && $type === 'IN') {
            return [
                'summaryGroup' => 'stock_in',
                'summaryKey' => 'initial_stock',
                'label' => 'Initial Stock',
            ];
        }

        if ($reason === StockReason::MANUAL_ADJUSTMENT && $type === 'IN') {
            return [
                'summaryGroup' => 'stock_in',
                'summaryKey' => 'manual_adjust_add',
                'label' => 'Manual Adjustment (+)',
            ];
        }

        if (in_array($reason, [StockReason::RETURN, StockReason::ORDER_CANCELLED], true) && $type === 'IN') {
            return [
                'summaryGroup' => 'stock_in',
                'summaryKey' => 'returns',
                'label' => 'Returns / Cancellations',
            ];
        }

        if ($reason === StockReason::WHOLESALE_DELIVERY && $type === 'OUT') {
            return [
                'summaryGroup' => 'stock_out',
                'summaryKey' => 'sales_wholesale_transfer',
                'label' => 'Wholesale Transfer',
            ];
        }

        if ($reason === StockReason::MANUAL_ADJUSTMENT && $type === 'OUT') {
            return [
                'summaryGroup' => 'stock_out',
                'summaryKey' => 'manual_adjust_negative',
                'label' => 'Manual Adjustment (-)',
            ];
        }

        if ($reason === StockReason::ORDER_PLACED && $type === 'OUT') {
            if (str_contains($remarks, 'POS')) {
                return [
                    'summaryGroup' => 'stock_out',
                    'summaryKey' => 'sales_pos',
                    'label' => 'POS Sale',
                ];
            }

            return [
                'summaryGroup' => 'stock_out',
                'summaryKey' => 'sales_online',
                'label' => 'Online Sale',
            ];
        }

        if ($reason === StockReason::BRANCH_TRANSFER) {
            return [
                'summaryGroup' => 'internal_transfer',
                'summaryKey' => 'branch_transfer',
                'label' => 'Internal Branch Transfer',
            ];
        }

        if ($type === 'IN') {
            return [
                'summaryGroup' => 'stock_in',
                'summaryKey' => 'returns',
                'label' => 'Stock In (Other)',
            ];
        }

        return [
            'summaryGroup' => 'stock_out',
            'summaryKey' => 'sales_online',
            'label' => 'Stock Out (Other)',
        ];
    }

    // public function updateQuantity(Request $request): RedirectResponse
    // {
    //     $product = $this->productRepo->getFirstWhere(['id' => $request->product_id]);
    //     $branchId = $product->branch_id;
    //     $reason  = StockReason::MANUAL_ADJUSTMENT;
    //     $variationReasons = $request->input('variation_reason', []);

    //     if (empty($product->variation)) {

    //         $requestedQty = (int) $request->current_stock; // now absolute stock
    //         if ($requestedQty < 0) {
    //             Toastr::warning(translate('product_quantity_can_not_be_less_than_0_'));
    //             return back();
    //         }

    //         $qtyChange = $requestedQty - $product->current_stock; // positive = IN, negative = OUT
    //         $movementQty = abs($qtyChange);
    //         $movementType = $qtyChange >= 0 ? 'IN' : 'OUT';

    //         $product->current_stock = $requestedQty;
    //         $product->save();

    //         // ProductStock row
    //         $productStock = ProductStock::firstOrCreate(
    //             ['product_id' => $product->id, 'variant' => null],
    //             ['sku' => $product->code, 'price' => $product->unit_price, 'qty' => 0]
    //         );

    //         $productStock->qty = $requestedQty;
    //         $productStock->save();

    //         // Log transaction
    //         if ($movementType === 'IN') {
    //             ProductStockTransaction::logStockIn($productStock, $movementQty, $reason, $request->stock_reason ?? '', $branchId);
    //         } elseif ($movementType === 'OUT') {
    //             ProductStockTransaction::logStockOut($productStock, $movementQty, $reason, $request->stock_reason ?? '', $branchId);
    //         }

    //         ManageBranchProductStock::updateOrCreate(
    //             ['branch_id' => $branchId, 'product_id' => $product->id, 'variation_key' => null],
    //             ['current_stock' => $requestedQty]
    //         );
    //     } else {
    //         $variations = json_decode($product->variation, true);
    //         $newTotalStock = 0;

    //         foreach ($variations as &$variation) {
    //             $type = $variation['type'];
    //             $requestedQty = (int) ($request['qty_' . str_replace('.', '_', $type)] ?? 0);

    //             if ($requestedQty < 0) continue;

    //             $productStock = ProductStock::where(['product_id' => $product->id, 'variant' => $type])->first();
    //             if (!$productStock) continue;

    //             $qtyChange = $requestedQty - $productStock->qty; // positive = IN, negative = OUT
    //             $movementQty = abs($qtyChange);
    //             $movementType = $qtyChange >= 0 ? 'IN' : 'OUT';

    //             // Update stock
    //             $productStock->qty = $requestedQty;
    //             $productStock->save();

    //             // Log transaction
    //             $varRemarks = $variationReasons[$type] ?? '';
    //             if ($movementType === 'IN') {
    //                 ProductStockTransaction::logStockIn($productStock, $movementQty, $reason, $varRemarks, $branchId);
    //             } elseif ($movementType === 'OUT') {
    //                 ProductStockTransaction::logStockOut($productStock, $movementQty, $reason, $varRemarks, $branchId);
    //             }

    //             $variation['qty'] = $requestedQty;
    //             $newTotalStock += $requestedQty;

    //             ManageBranchProductStock::updateOrCreate(
    //                 ['branch_id' => $branchId, 'product_id' => $product->id, 'variation_key' => $type],
    //                 ['current_stock' => $requestedQty]
    //             );
    //         }

    //         $product->current_stock = $newTotalStock;
    //         $product->variation = json_encode($variations);
    //         $product->save();
    //     }

    //     Toastr::success(translate('product_quantity_updated_successfully'));
    //     return back();
    // }
    // public function updateQuantity(Request $request): RedirectResponse
    // {
    //     $variations = [];
    //     $stockCount = $request['current_stock'];
    //     $product = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']]);
    //      $variationReasons = $request->input('variation_reason', []); 
    //     if ($request->has('type')) {
    //         foreach ($request['type'] as $key => $str) {
    //             $item = [];
    //             $item['type'] = $str;
    //             $item['price'] = currencyConverter(amount: abs($request['price_' . str_replace('.', '_', $str)]));
    //             $item['sku'] = $request['sku_' . str_replace('.', '_', $str)];
    //             $item['qty'] = abs($request['qty_' . str_replace('.', '_', $str)]);
    //             $item['reason'] = $variationReasons[$str] ?? null; 
    //             $variations[] = $item;
    //         }
    //     }
    //     $dataArray = [
    //         'current_stock' => $product->current_stock + $stockCount, // 👈 add karega
    //         'variation' => json_encode($variations),
    //     ];

    //     if ($stockCount >= 0) {
    //         $product = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']]);
    //         $this->productRepo->updateByParams(params: ['id' => $request['product_id']], data: $dataArray);
    //         $updatedProduct = $this->productRepo->getFirstWhere(params: ['id' => $request['product_id']]);
    //         $this->updateRestockRequestListAndNotify(product: $product, updatedProduct: $updatedProduct);

    //         $branchProduct = ManageBranchProductStock::where('branch_id', 1)
    //             ->where('product_id', $request['product_id'])
    //             ->first();

    //         if ($branchProduct) {
    //             $branchProduct->current_stock += $stockCount;
    //             $branchProduct->save();
    //         } else {
    //             ManageBranchProductStock::create([
    //                 'branch_id' => 1,
    //                 'product_id' => $request['product_id'],
    //                 'attributes' => null, // set if needed
    //                 'current_stock' => $stockCount,
    //             ]);
    //         }

    //         Toastr::success(translate('product_quantity_updated_successfully'));
    //         return back();
    //     }
    //     Toastr::warning(translate('product_quantity_can_not_be_less_than_0_'));
    //     return back();
    // }

    public function getBulkImportView(): View
    {
        return view(Product::BULK_IMPORT[VIEW]);
    }

    public function importBulkProduct(Request $request, ProductService $service): RedirectResponse
    {
        $dataArray = $service->getImportBulkProductData(request: $request, addedBy: 'admin');
        if (!$dataArray['status']) {
            Toastr::error($dataArray['message']);
            return back();
        }

        $this->productRepo->addArray(data: $dataArray['products']);
        Toastr::success($dataArray['message']);
        return back();
    }

    public function updatedProductList(Request $request): View
    {
        $filters = [
            'added_by' => 'seller',
            'is_shipping_cost_updated' => 0,
        ];
        $searchValue = $request['searchValue'];

        $products = $this->productRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $searchValue, filters: $filters, dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT));
        return view(Product::UPDATED_PRODUCT_LIST[VIEW], compact('products', 'searchValue'));
    }

    public function updatedShipping(Request $request): JsonResponse
    {
        $product = $this->productRepo->getFirstWhere(params: ['id' => $request['id']]);
        $dataArray = ['is_shipping_cost_updated' => $request['status']];
        if ($request['status'] == 1) {
            $dataArray += [
                'shipping_cost' => $product['temp_shipping_cost']
            ];
        }
        $this->productRepo->update(id: $request['id'], data: $dataArray);

        return response()->json(['message' => translate('status_updated_successfully')], 200);
    }

    public function deny(ProductDenyRequest $request): JsonResponse
    {
        $dataArray = [
            'request_status' => 2,
            'status' => 0,
            'denied_note' => $request['denied_note'],
        ];
        $this->productRepo->update(id: $request['id'], data: $dataArray);
        $product = $this->productRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $request['id']]);
        $vendor = $this->sellerRepo->getFirstWhere(params: ['id' => $product['user_id']]);
        if ($vendor['cm_firebase_token']) {
            ProductRequestStatusUpdateEvent::dispatch('product_request_rejected_message', 'seller', $vendor['app_language'] ?? getDefaultLanguage(), $vendor['cm_firebase_token']);
        }
        return response()->json(['message' => translate('product_request_denied') . '.']);
    }

    public function approveStatus(Request $request): JsonResponse
    {
        $product = $this->productRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $request['id']]);
        $dataArray = [
            'request_status' => ($product['request_status'] == 0) ? 1 : 0
        ];
        $this->productRepo->update(id: $request['id'], data: $dataArray);
        $vendor = $this->sellerRepo->getFirstWhere(params: ['id' => $product['user_id']]);
        if ($vendor['cm_firebase_token']) {
            ProductRequestStatusUpdateEvent::dispatch('product_request_approved_message', 'seller', $vendor['app_language'] ?? getDefaultLanguage(), $vendor['cm_firebase_token']);
        }
        return response()->json(['message' => translate('product_request_approved') . '.']);
    }

    public function getSearchedProductsView(Request $request): JsonResponse
    {
        $searchValue = $request['searchValue'] ?? null;
        $products = $this->productRepo->getListWhere(
            searchValue: $searchValue,
            filters: [
                // 'added_by' => 'in_house',
                'status' => 1,
                'category_id' => $request['category_id'],
                'code' => $request['name'],
                'product_type' => 'physical',

            ],
            dataLimit: getWebConfig(name: 'pagination_limit')
        );
        // dd($products);
        return response()->json([
            'count' => $products->count(),
            'result' => view(Product::SEARCH[VIEW], compact('products'))->render(),
        ]);
    }

    public function getProductGalleryView(Request $request): View
    {

        $searchValue = $request['searchValue'];
        $filters = [
            'searchValue' => $searchValue,
            'request_status' => 1,
            'product_search_type' => 'product_gallery',
            'seller_id' => ($request['vendor_id'] == 'in_house' || $request['added_by'] == 'in_house') ? '' : $request['vendor_id'],
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'product_type' => 'physical',
        ];
        $products = $this->productRepo->getListWhere(orderBy: ['id' => 'desc'], searchValue: $request['searchValue'], filters: $filters, dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT));

        $products->map(function ($product) {
            if ($product->product_type == 'physical' && count(json_decode($product->choice_options)) > 0 || count(json_decode($product->colors)) > 0) {
                $colorName = [];
                $colorsCollection = collect(json_decode($product->colors));
                $colorsCollection->map(function ($color) use (&$colorName) {
                    $colorName[] = $this->colorRepo->getFirstWhere(['code' => $color])->name;
                });
                $product['colorsName'] = $colorName;
            }
        });

        $vendors = $this->sellerRepo->getListWhere(filters: ['status' => 'approved'], relations: ['shop'], dataLimit: 'all');
        $brands = $this->brandRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        return view(Product::PRODUCT_GALLERY[VIEW], compact('products', 'vendors', 'brands', 'categories', 'searchValue'));
    }

    public function getStockLimitStatus(Request $request, string $type): JsonResponse
    {
        $filters = [
            'added_by' => $type,
            'product_type' => 'physical',
            'request_status' => $request['status'],
        ];
        $products = $this->productRepo->getStockLimitListWhere(filters: $filters, dataLimit: 'all');
        if ($products->count() == 1) {
            $product = $products->first();
            $thumbnail = getStorageImages(path: $product->thumbnail_full_url, type: 'backend-product');
            return response()->json(['status' => 'one_product', 'product_count' => 1, 'product' => $product, 'thumbnail' => $thumbnail]);
        } else {
            return response()->json(['status' => 'multiple_product', 'product_count' => $products->count()]);
        }
    }

    public function getMultipleProductDetailsView(Request $request): JsonResponse
    {
        $selectedProducts = $this->productRepo->getListWhere(
            filters: [
                'productIds' => $request['productIds'],
            ],
            dataLimit: 'all'
        );
        return response()->json([
            'result' => view(Product::MULTIPLE_PRODUCT_DETAILS[VIEW], compact('selectedProducts'))->render(),
        ]);
    }

    public function deletePreviewFile(Request $request): JsonResponse
    {
        $product = $this->productRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $request['product_id']]);
        $this->productService->deletePreviewFile(product: $product);
        $this->productRepo->update(id: $request['product_id'], data: ['preview_file' => null]);
        return response()->json([
            'status' => 1,
            'message' => translate('Preview_file_deleted')
        ]);
    }

    public function getRequestRestockListView(Request $request): View|RedirectResponse
    {
        $filters = [
            'added_by' => 'in_house',
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
        ];

        [$startDate, $endDate, $dateError] = $this->resolveRestockDateRange($request);
        if ($dateError !== null) {
            Toastr::error($dateError);
            return back();
        }

        $restockProducts = $this->restockProductRepo->getListWhereBetween(
            orderBy: ['updated_at' => 'desc'],
            searchValue: $request['searchValue'],
            filters: $filters,
            relations: ['product'],
            whereBetween: 'created_at',
            whereBetweenFilters: ($startDate && $endDate) ? [$startDate, $endDate] : [],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );
        $brands = $this->brandRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]);
        $totalRestockProducts = $this->restockProductRepo->getListWhere(filters: $filters, dataLimit: 'all')->count();
        return view(Product::REQUEST_RESTOCK_LIST[VIEW], compact(
            'restockProducts',
            'brands',
            'categories',
            'subCategory',
            'filters',
            'totalRestockProducts'
        ));
    }

    public function exportRestockList(Request $request): BinaryFileResponse
    {
        $filters = [
            'added_by' => 'in_house',
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
        ];

        [$startDate, $endDate] = $this->resolveRestockDateRange($request);

        $restockProducts = $this->restockProductRepo->getListWhereBetween(
            orderBy: ['updated_at' => 'desc'],
            searchValue: $request['searchValue'],
            filters: $filters,
            relations: ['product'],
            whereBetween: 'created_at',
            whereBetweenFilters: ($startDate && $endDate) ? [$startDate, $endDate] : [],
            dataLimit: 'all',
        );
        $brand = (!empty($request->brand_id) && $request->has('brand_id')) ? $this->brandRepo->getFirstWhere(params: ['id' => $request->brand_id]) : 'all';
        $category = (!empty($request['category_id']) && $request->has('category_id')) ? $this->categoryRepo->getFirstWhere(params: ['id' => $request['category_id']]) : 'all';
        $subCategory = (!empty($request->sub_category_id) && $request->has('sub_category_id')) ? $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]) : 'all';

        $data = [
            'products' => $restockProducts,
            'category' => $category,
            'subCategory' => $subCategory,
            'brand' => $brand,
            'searchValue' => $request['searchValue'],
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
        return Excel::download(new RestockProductListExport($data), 'restock-product-list.xlsx');
    }

    private function resolveRestockDateRange(Request $request): array
    {
        $legacyDateRange = (string)$request->input('restock_date', '');
        $dateType = (string)$request->input('date_type', $legacyDateRange !== '' ? 'custom_date' : 'this_year');
        $fromInput = (string)$request->input('from', '');
        $toInput = (string)$request->input('to', '');

        if ($legacyDateRange !== '' && ($fromInput === '' || $toInput === '')) {
            $dates = explode(' - ', $legacyDateRange);
            if (count($dates) === 2 && checkDateFormatInMDY($dates[0]) && checkDateFormatInMDY($dates[1])) {
                $fromInput = Carbon::createFromFormat('m/d/Y', $dates[0])->toDateString();
                $toInput = Carbon::createFromFormat('m/d/Y', $dates[1])->toDateString();
            }
        }

        $startDate = null;
        $endDate = null;
        $dateError = null;

        try {
            switch ($dateType) {
                case 'this_month':
                    $startDate = now()->startOfMonth()->startOfDay();
                    $endDate = now()->endOfMonth()->endOfDay();
                    break;
                case 'this_week':
                    $startDate = now()->startOfWeek()->startOfDay();
                    $endDate = now()->endOfWeek()->endOfDay();
                    break;
                case 'today':
                    $startDate = now()->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case 'custom_date':
                    if ($fromInput === '' || $toInput === '') {
                        $dateError = translate('Invalid_date_range_format');
                        break;
                    }
                    $startDate = Carbon::parse($fromInput)->startOfDay();
                    $endDate = Carbon::parse($toInput)->endOfDay();
                    if ($startDate->gt($endDate)) {
                        $dateError = translate('Invalid_date_range_format');
                    }
                    break;
                case 'this_year':
                default:
                    $startDate = now()->startOfYear()->startOfDay();
                    $endDate = now()->endOfYear()->endOfDay();
                    break;
            }
        } catch (\Throwable) {
            $dateError = translate('Invalid_date_range_format');
        }

        return [$startDate, $endDate, $dateError];
    }

    public function getProducts(Request $request, ProductService $service): JsonResponse
    {
        $parentId = $request['parent_id'];
        $filter = ['sub_category_id' => $parentId, 'product_type' => 'physical'];
        $products = $this->productRepo->getListWhere(filters: $filter, dataLimit: 'all');
        $dropdown = $service->getProductsDropdown(request: $request, products: $products);

        return response()->json([
            'select_tag' => $dropdown,
        ]);
    }
    public function getUnitPrice($id): JsonResponse
    {
        $product = Products::find($id);

        if ($product) {
            return response()->json([
                'unit_price' => $product->unit_price ?? 0,
            ]);
        }

        return response()->json([
            'unit_price' => 0,
        ], 404);
    }

    private function extractTagValues(?string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string)$value))));
    }

    private function ensureTranslatedModelCountsMatch(Request $request, array $defaultModels): void
    {
        $languages = collect($request->input('lang', []))->values();
        $defaultLanguage = $languages->first() ?? 'en';

        foreach ($languages as $index => $language) {
            if ($language === $defaultLanguage) {
                continue;
            }

            $translatedModels = $this->extractTagValues($request->input("model.$index", ''));
            if (!empty($translatedModels) && count($translatedModels) !== count($defaultModels)) {
                throw ValidationException::withMessages([
                    "model.$index" => translate('translated_models_must_match_the_default_model_count'),
                ]);
            }
        }
    }

    private function syncVehicleModelTranslations(Request $request, \Illuminate\Support\Collection $models): void
    {
        $languages = collect($request->input('lang', []))->values();
        $defaultLanguage = $languages->first() ?? 'en';

        foreach ($languages as $index => $language) {
            if ($language === $defaultLanguage) {
                continue;
            }

            $translatedModels = $this->extractTagValues($request->input("model.$index", ''));

            foreach ($models as $modelIndex => $model) {
                $translatedValue = $translatedModels[$modelIndex] ?? null;

                if ($translatedValue !== null && $translatedValue !== '') {
                    $this->translationRepo->updateData(VehicleModel::class, (string)$model->id, $language, 'name', $translatedValue);
                }
            }
        }
    }

    private function buildTranslationPayload(string $defaultValue, $translations, array $languages, string $defaultLanguage): array
    {
        $payload = [];

        foreach ($languages as $language) {
            $payload[$language] = $language === $defaultLanguage
                ? $defaultValue
                : optional($translations->firstWhere('locale', $language))->value ?? '';
        }

        return $payload;
    }

    private function buildVehicleModelTranslationsPayload(\Illuminate\Support\Collection $models, array $languages, string $defaultLanguage): array
    {
        $payload = [];

        foreach ($languages as $language) {
            $payload[$language] = $models->map(function (VehicleModel $model) use ($language, $defaultLanguage) {
                if ($language === $defaultLanguage) {
                    return $model->getRawOriginal('name');
                }

                return optional($model->translations->firstWhere('locale', $language))->value ?? '';
            })->values()->toArray();
        }

        return $payload;
    }
}
