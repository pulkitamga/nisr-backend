<?php

namespace App\Http\Controllers\Admin\Product;

use App\Contracts\Repositories\AttributeRepositoryInterface;
use App\Contracts\Repositories\AuthorRepositoryInterface;
use App\Contracts\Repositories\BannerRepositoryInterface;
use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Contracts\Repositories\DealOfTheDayRepositoryInterface;
use App\Contracts\Repositories\DigitalProductAuthorRepositoryInterface;
use App\Contracts\Repositories\DigitalProductVariationRepositoryInterface;
use App\Contracts\Repositories\FlashDealProductRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StockRequestRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\ProductSeoRepositoryInterface;
use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\StockClearanceProductRepositoryInterface;
use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\WishlistRepositoryInterface;
use App\Enums\ViewPaths\Admin\StockRequest;
use App\Enums\WebConfigKey;
use App\Events\ProductRequestStatusUpdateEvent;
use App\Exports\ProductListExport;
use App\Exports\RestockProductListExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\ProductDenyRequest;
use App\Http\Requests\ProductAddRequest;
use App\Http\Requests\Admin\StockRequestAddProduct;
use App\Http\Requests\ProductUpdateRequest;
use App\Repositories\DigitalProductPublishingHouseRepository;
use App\Services\ProductService;
use App\Services\StockRequestService;
use App\Traits\FileManagerTrait;
use App\Traits\ProductTrait;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Models\StockRequests;
use App\Models\StockRequestProduct;
use App\Models\ManageBranchProductStock;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class StockRequestController extends BaseController
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
        private readonly ProductService                             $productService,
        private readonly StockRequestRepositoryInterface             $stockRequestRepo,
        private readonly StockRequestService                         $stockRequestService,

    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getStockRequestListView($request);
    }


    public function getStockRequestListView(Request $request): View|RedirectResponse
    {
        // Step 1: Get filtered collection (all results, no paginate yet)
        $filtered = StockRequests::with([
            'products.product',
            'products.category',
            'products.attribute',
            'fromBranch'
        ])
            ->when($request->restock_date, function ($query) use ($request) {
                return $query->whereDate('transfer_date', $request->restock_date);
            })
            ->when($request->category_id, function ($query) use ($request) {
                return $query->whereHas('products.product', function ($query) use ($request) {
                    $query->where('category_id', $request->category_id);
                });
            })
            ->when($request->sub_category_id, function ($query) use ($request) {
                return $query->whereHas('products.product', function ($query) use ($request) {
                    $query->where('sub_category_id', $request->sub_category_id);
                });
            })
            ->when($request->brand_id, function ($query) use ($request) {
                return $query->whereHas('products.product', function ($query) use ($request) {
                    $query->where('brand_id', $request->brand_id);
                });
            })
            ->when($request->searchValue, function ($query) use ($request) {
                return $query->whereHas('products.product', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->searchValue . '%');
                });
            })
            ->orderByDesc('id')
            ->get();

        $perPage = getWebConfig('pagination_limit');
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;

        $aStockRequests = new LengthAwarePaginator(
            $filtered->slice($offset, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        return view(StockRequest::LIST[VIEW], compact('aStockRequests'));
    }


    public function addStockRequestListView(Request $request): View|RedirectResponse
    {

        $user = auth('admin')->user();

        $filters = [
            'added_by' => 'in_house',
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'sub_category_id' => $request['sub_category_id'],
        ];
        $searchValue = $request['searchValue'] ?? null;
        $startDate = '';
        $cartItems = ['cartItemValue' => []];
        $endDate = '';
        $allBranches = $this->branchRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');

        if ($user->id == 1) {
            $fromBranches = $allBranches;
        } else {
            $userBranchIds = json_decode($user->branch_id, true);
            $userBranchIds = array_map('intval', (array) $userBranchIds);
            $fromBranches = $allBranches->filter(function ($branch) use ($userBranchIds) {
                return in_array($branch->id, $userBranchIds);
            })->values();
        }
        $toBranches = $this->branchRepo->getListWhere(filters: ['status' => 1, 'id' => 1], dataLimit: 'all');
        $brands = $this->brandRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $attributes = $this->attributeRepo->getListWhere(dataLimit: 'all');
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]);
        $totalRestockProducts = $this->restockProductRepo->getListWhere(filters: $filters, dataLimit: 'all')->count();
        $products = $this->productRepo->getListWhere(filters: ['scope' => 'active'], dataLimit: 'all');
        return view(StockRequest::ADD[VIEW], compact(
            'fromBranches',
            'toBranches',
            'products',
            'brands',
            'categories',
            'subCategory',
            'filters',
            'totalRestockProducts',
            'searchValue',
            'cartItems',
            'attributes'
        ));
    }

    public function getStockSearchedProductsView(Request $request): JsonResponse
    {
        $products = $this->productRepo->getListWithScope(
            scope: 'active',
            filters: [
                'added_by' => 'in_house',
                'keywords' => $request['name'],
                'search_from' => 'pos',
                'product_type' => 'physical',

            ],
            dataLimit: 'all'
        );
        $data = [
            'count' => $products->count(),
            'result' => view(Product::TRANSFER_SEARCH[VIEW], compact('products'))->render()
        ];
        if ($products->count() > 0) {
            $data += ['id' => $products[0]->id];
        }

        return response()->json($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getQuickView(Request $request): JsonResponse
    {
        $product = $this->productRepo->getFirstWhereWithCount(
            params: ['id' => $request['product_id']],
            withCount: ['reviews'],
            relations: ['brand', 'category', 'rating', 'tags', 'digitalVariation', 'clearanceSale' => function ($query) {
                return $query->active();
            }],
        );

        // print_r($product);die();
        return response()->json([
            'success' => 1,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category->name ?? 'Category not found',
                'brand' => $product->brand->name ?? 'Brand not found',
                'reviews_count' => $product->reviews_count,
            ],
            // 'view' => view(Product::TRANSFER_QUICK_VIEW[VIEW], compact('product'))->render(),
        ]);
    }

    public function saveStockRequest(StockRequestAddProduct $request, stockRequestService $service): JsonResponse|RedirectResponse
    {
        Log::info("saveStockRequest START", [
            'request_all' => $request->all(),
        ]);

        try {
            // 1️⃣ SERVICE: getAddData
            Log::info("Calling getAddData...");
            $dataArray = $service->getAddData(request: $request);

            Log::info("getAddData RESULT", [
                'dataArray' => $dataArray
            ]);

            // 2️⃣ REPO: Save main stock request
            Log::info("Saving stock request...");
            $savedRequest = $this->stockRequestRepo->add(data: $dataArray);

            Log::info("Saved Stock Request", [
                'saved_request_id' => $savedRequest->id,
                'saved_request_data' => $savedRequest
            ]);

            // 3️⃣ SERVICE: Get products list
            Log::info("Preparing product list...");
            $products = $service->getAddRequestProducts($request->products, $savedRequest->id);

            Log::info("Products Prepared", [
                'products' => $products
            ]);

            // 4️⃣ Save each product to stock request
            foreach ($products as $index => $product) {
                Log::info("Saving Product", [
                    'index'   => $index,
                    'product' => $product
                ]);

                $this->stockRequestRepo->stockRequestProduct($product);

                Log::info("Product Saved", [
                    'index' => $index
                ]);
            }

            Log::info("All products saved successfully!");

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => translate('product_added_successfully'),
                    'redirect' => route('admin.stock-request.list')
                ]);
            }

            Toastr::success(translate('product_added_successfully'));
            return redirect()->route('admin.stock-request.list');
        } catch (\Throwable $e) {

            // 5️⃣ Catch ANY error with full details
            Log::error("ERROR in saveStockRequest", [
                'error_message' => $e->getMessage(),
                'error_line'    => $e->getLine(),
                'error_file'    => $e->getFile(),
                'stack'         => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => "Something went wrong: " . $e->getMessage(),
            ], 500);
        }
    }

    public function getStockRequestView(string|int $id): View|RedirectResponse
    {
        $stockRequest = $this->stockRequestRepo->getFirstWhere(
            params: ['id' => $id],
            relations: [
                'products.product',
                'products.category',       // Load product and its category
                'products.attribute',       // Load product and its category
                'products.received_from',
                'fromBranch'
            ]
        );


        if (!$stockRequest) {
            Toastr::error(translate('stock_request_not_found') . '!');
            return redirect()->route('admin.stock-request.list');
        }
        $productData = $stockRequest->products->map(function ($stockRequestProduct) {
            return [
                'product_name' => $stockRequestProduct->product->name ?? null,
                'category_name' => $stockRequestProduct->product->category->name ?? null,
                'attributes' => $stockRequestProduct->product->attribute->name ?? null,
                'quantity' => $stockRequestProduct->quantity,
            ];
        });

        return view(StockRequest::VIEW[VIEW], compact('stockRequest', 'productData'));
    }

    public function getBranchesProductStock(Request $request)
    {
        $success = 1;
        $productId = $request->input('product_id');
        $requestId = $request->input('request_id');

        $stockRequestProduct = $this->stockRequestRepo->getStockReqProductFirstWhere(
            params: [
                'id' => $productId,
                'stock_requests_id' => $requestId
            ],
            relations: []
        );

        if (!$stockRequestProduct) {
            Toastr::error(translate('stock_request_product_not_found') . '!');
            return redirect()->route('admin.stock-request.view', ['id' => $requestId]);
        }

        $aProductDetails = $this->productRepo->getFirstWhere(params: ['id' => $stockRequestProduct->product_id], relations: []);

        if (!$aProductDetails) {
            Toastr::error(translate('product_not_found') . '!');
            return redirect()->route('admin.products.list', ['in_house']);
        }

        $stockEntries = ManageBranchProductStock::where('product_id', $stockRequestProduct->product_id)
            ->where('current_stock', '>', 0)
            ->get();


        $branchesStock = [];
        foreach ($stockEntries as $entry) {
            $branch = \App\Models\Branch::find($entry->branch_id);
            if ($branch) {
                $branchesStock[] = [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->branch_name,
                    'branch_address' => $branch->branch_address,
                    'available_stock' => $entry->current_stock,
                ];
            }
        }

        $isActive = $this->productRepo->getWebFirstWhereActive(params: ['id' => $stockRequestProduct->product_id]);
        $relations = ['branches'];
        $aProductDetails = $this->productRepo->getFirstWhereWithoutGlobalScope(params: ['id' => $stockRequestProduct->product_id], relations: $relations);

        $iAvailableStock = $aProductDetails->current_stock;
        if (count(json_decode($aProductDetails['variation'])) > 0) {
            $type = $stockRequestProduct['attribute'];
            foreach (json_decode($aProductDetails['variation'], true) as $var) {
                if ($type == $var['type']) {
                    $iAvailableStock = $var['qty'];
                }
            }
        }

        $response = [
            'stockRequestProduct' => $stockRequestProduct,
            'branchesStock' => $branchesStock
        ];

        return response()->json([
            'success' => $success,
            'data' => $response
        ], 200);
    }
    public function updateProductStockRequestStatus(Request $request): JsonResponse
    {
        $selectedBranches = $request->input('selected_branches');
        $productId = $request->input('product_id');
        $requestId = $request->input('request_id');

        if (empty($selectedBranches)) {
            return response()->json([
                'success' => false,
                'message' => translate('Please_select_at_least_one_branch.'),
            ], 400);
        }

        $stockRequestProduct = $this->stockRequestRepo->getStockReqProductFirstWhere(
            params: [
                'id' => $productId,
                'stock_requests_id' => $requestId
            ],
            relations: ['stockRequest']
        );

        if (!$stockRequestProduct) {
            return response()->json([
                'success' => false,
                'message' => translate('stock_request_product_not_found'),
            ], 400);
        }

        $requiredQty = $stockRequestProduct->quantity;
        $remainingQty = $requiredQty;
        $attribute = $stockRequestProduct->attribute;
        $toBranchId = $stockRequestProduct->stockRequest->from_branch_id;
        $transferredFrom = [];

        foreach ($selectedBranches as $fromBranchId) {
            if ($remainingQty <= 0) break;

            $fromStock = ManageBranchProductStock::where('branch_id', $fromBranchId)
                ->where('product_id', $stockRequestProduct->product_id)
                ->when($attribute, fn($q) => $q->where('attributes', $attribute))
                ->first();

            if (!$fromStock || $fromStock->current_stock <= 0) continue;

            $transferQty = min($fromStock->current_stock, $remainingQty);

            $fromStock->current_stock -= $transferQty;
            $fromStock->save();

            $toStock = ManageBranchProductStock::where('branch_id', $toBranchId)
                ->where('product_id', $stockRequestProduct->product_id)
                ->when($attribute, fn($q) => $q->where('attributes', $attribute))
                ->first();

            if ($toStock) {
                $toStock->current_stock += $transferQty;
                $toStock->save();
            } else {
                ManageBranchProductStock::create([
                    'branch_id' => $toBranchId,
                    'product_id' => $stockRequestProduct->product_id,
                    'attributes' => $attribute ?? null,
                    'current_stock' => $transferQty,
                ]);
            }

            $transferredFrom[] = $fromBranchId;
            $remainingQty -= $transferQty;
        }

        if ($remainingQty > 0) {
            return response()->json([
                'success' => false,
                'message' => translate("Not_enough_stock_across_selected_branches"),
            ], 400);
        }

        // Step 3: Update status
        $updateData = [
            'status' => 'transferred',
            'received_from_branch' => implode(',', $transferredFrom), // multiple branches
            'received_time' => now(),
        ];

        $this->stockRequestRepo->updateStockRequestProduct(id: $stockRequestProduct->id, data: $updateData);

        return response()->json([
            'success' => true,
            'message' => translate("status_updated_successfully"),
        ], 200);
    }
    public function getProductByCategory(Request $request): JsonResponse
    {
        $success = 1;
        $categoryId = $request->input('category_id');
        $productId = $request->input('product_id');
        $filters = [
            'category_id' => $categoryId,
            'product_type' => 'physical',
        ];

        if ($productId) {
            $filters['id'] = $productId;
        }
        $aProduct = $this->productRepo->getListWithScope(
            filters: $filters,
            orderBy: [],
            relations: [],
            dataLimit: 'all'
        );

        return response()->json([
            'success' => $success,
            'data' => $aProduct
        ], 200);
    }
}
