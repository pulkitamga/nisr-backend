<?php

namespace App\Http\Controllers\Admin\Product;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Warranty;
use App\Models\Attribute;
use App\Enums\StockReason;
use App\Enums\WebConfigKey;
use App\Models\ProductStock;
use App\Traits\ProductTrait;
use Illuminate\Http\Request;
use App\Models\StockRequests;
use App\Models\StockTransfers;
use App\Services\ProductService;
use App\Traits\FileManagerTrait;
use Illuminate\Http\JsonResponse;
use App\Exports\ProductListExport;
use Illuminate\Support\Facades\DB;
use App\Models\StockRequestProduct;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use App\Models\StockTransferProduct;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SerialTransferHistory;
use App\Services\StockRequestService;
use App\Services\InventoryMutationService;
use Illuminate\Http\RedirectResponse;
use App\Models\ProductStockTransaction;
use App\Domain\Stock\Support\VariantMatcher;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductAddRequest;
use App\Models\ManageBranchProductStock;
use App\Exports\RestockProductListExport;
use App\Enums\ViewPaths\Admin\StockRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Events\ProductRequestStatusUpdateEvent;
use App\Http\Requests\Admin\ProductDenyRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\Admin\StockRequestAddProduct;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\ColorRepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Contracts\Repositories\AuthorRepositoryInterface;
use App\Contracts\Repositories\BannerRepositoryInterface;
use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\WishlistRepositoryInterface;
use App\Contracts\Repositories\AttributeRepositoryInterface;
use App\Contracts\Repositories\ProductSeoRepositoryInterface;
use App\Repositories\DigitalProductPublishingHouseRepository;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\DealOfTheDayRepositoryInterface;
use App\Contracts\Repositories\StockRequestRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Contracts\Repositories\FlashDealProductRepositoryInterface;
use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Contracts\Repositories\DigitalProductAuthorRepositoryInterface;
use App\Contracts\Repositories\StockClearanceProductRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\DigitalProductVariationRepositoryInterface;

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
        private readonly InventoryMutationService                    $inventoryMutationService,
        private readonly VariantMatcher                              $variantMatcher,

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
        $products = $this->productRepo->getListWithScope(filters: ['scope' => 'active'], dataLimit: 'all');
        // addStockRequestListView() method mein ye pura replace kar de




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
            'attributes',
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

    public function saveStockRequest(StockRequestAddProduct $request, StockRequestService $service): JsonResponse|RedirectResponse
    {
        try {
            DB::beginTransaction();

            $dataArray = $service->getAddData(request: $request);
            $savedRequest = $this->stockRequestRepo->add(data: $dataArray);

            $products = $service->getAddRequestProducts($request->products, $savedRequest->id);

            foreach ($products as $productData) {
                $this->stockRequestRepo->stockRequestProduct($productData);
            }

            DB::commit();

            $message = translate('Stock request created successfully!');

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('admin.stock-request.list')
                ]);
            }

            Toastr::success($message);
            return redirect()->route('admin.stock-request.list');
        } catch (\Throwable $e) {
            DB::rollBack();
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
        $productId = $request->input('product_id'); // stock_request_products.id
        $requestId = $request->input('request_id');

        $stockRequestProduct = StockRequestProduct::with(['product'])
            ->findOrFail($productId);
        $toBranchId = $stockRequestProduct->stockRequest->from_branch_id;

        $variationType = $stockRequestProduct->variation_type;
        $variationKey  = $stockRequestProduct->variation_key;
        $hasVariation  = !empty($variationType);

        $query = ManageBranchProductStock::where('product_id', $stockRequestProduct->product_id)
            ->where('current_stock', '>', 0)
            ->where('branch_id', '!=', $toBranchId);

        if ($hasVariation) {
            $stockEntries = $query->get()->filter(function ($entry) use ($variationType, $variationKey) {
                $typeMatches = $this->variantMatcher->matches($variationType, $entry->variation_type)
                    || $this->variantMatcher->matches($variationType, $entry->variation_key);
                if (!$typeMatches) {
                    return false;
                }

                if (!$variationKey) {
                    return true;
                }

                return $this->variantMatcher->matches($variationKey, $entry->variation_key)
                    || $this->variantMatcher->matches($variationKey, $entry->variation_type);
            })->values();
        } else {
            $stockEntries = $query->where(function ($defaultQuery) {
                $defaultQuery->whereNull('variation_type')->orWhere('variation_type', '');
            })->get();
        }

        $branchesStock = $stockEntries->map(function ($entry) {
            $branch = Branch::find($entry->branch_id);
            return [
                'branch_id'       => $branch->id,
                'branch_name'     => $branch->branch_name,
                'branch_address'  => $branch->branch_address,
                'available_stock' => $entry->current_stock,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'stockRequestProduct' => $stockRequestProduct,
                'branchesStock'       => $branchesStock,
                'product' => [
                    'is_warranty' => (int)$stockRequestProduct->product->is_warranty,
                    'quantity'    => (int)$stockRequestProduct->quantity
                ]
            ]
        ]);
    }

       public function updateProductStockRequestStatus(Request $request): JsonResponse
    {
        Log::info('=== STOCK TRANSFER REQUEST STARTED ===', [
            'raw_input' => $request->all(),
            'files' => $request->hasFile('serial_csv') ? 'Yes' : 'No'
        ]);


        $request->validate([
            'selected_branches.*' => 'exists:branches,id',
            'serial_csv' => 'required_if:is_warranty,1|file|mimes:csv,txt',
        ]);

        $selectedBranches = $request->input('selected_branches', []);
        if (empty($selectedBranches)) {
            return response()->json(['success' => false, 'message' => 'Select at least one branch.'], 400);
        }

        $stockRequestProductId = $request->input('product_id');  // stock_request_products.id
        $stockRequestId        = $request->input('request_id');

        Log::info('Finding stock_request_product', ['id' => $stockRequestProductId]);

        $stockRequestProduct = $this->stockRequestRepo->getStockReqProductFirstWhere(
            params: ['id' => $stockRequestId],
            relations: ['product', 'stockRequest']
        );

        if (!$stockRequestProduct) {
            Log::error('Stock request product NOT FOUND', ['id' => $stockRequestProductId]);
            return response()->json(['success' => false, 'message' => 'Product not found.'], 400);
        }

        $realProductId   = $stockRequestProduct->product_id;
        $toBranchId      = $stockRequestProduct->stockRequest->from_branch_id;
        $requiredQty     = $stockRequestProduct->quantity;
        $variationType   = $stockRequestProduct->variation_type;     // NEW
        $variationKey    = $stockRequestProduct->variation_key;      // NEW
        $attributes      = $stockRequestProduct->attributes;         // NEW
        $hasVariation    = !empty($variationType);
        $isWarranty      = $stockRequestProduct->product->is_warranty == 1;

        // Get the ProductStock row for this product and variation
        $productStock = ProductStock::where('product_id', $realProductId)
            ->get()
            ->first(function ($row) use ($variationType) {
                if (!$variationType) {
                    return is_null($row->variant) || $row->variant === '';
                }
                return $this->variantMatcher->matches($variationType, $row->variant);
            });

        if (!$productStock) {
            Log::warning("ProductStock not found for product {$realProductId} variation {$variationType}");
        }
        $serials = [];
        $errors = [];
        $csvPath = null;
        if ($isWarranty) {
            $csvFile = $request->file('serial_csv');
            if (!$csvFile) {
                return response()->json(['success' => false, 'message' => 'CSV file is required for warranty product.'], 400);
            }

            $parseResult = $this->parseCsvSerials($csvFile, $errors);
            $serials = $parseResult['valid_serials'];

            if (count($serials) !== (int)$requiredQty) {
                $errors[] = "CSV must contain exactly {$requiredQty} valid serials. Found: " . count($serials);
            }

            $validSerials = [];
            $fromBranchIdForValidation = $selectedBranches[0] ?? null;
            if (!$fromBranchIdForValidation) {
                return response()->json(['success' => false, 'message' => 'No branch selected for validation.'], 400);
            }

            foreach ($serials as $serial) {
                $warranty = Warranty::where('serial_number', $serial)->first();

                if (!$warranty) {
                    $errors[] = "Serial {$serial} does not exist in system.";
                    continue;
                }

                // Validation logic
                if (is_null($warranty->branch_id) && is_null($warranty->distributor_id)) {
                    if ($fromBranchIdForValidation != 1) {
                        $errors[] = "Serial {$serial} belongs to system and can only be transferred from branch 1.";
                        continue;
                    }
                } elseif (!is_null($warranty->branch_id) && is_null($warranty->distributor_id)) {
                    if ($warranty->branch_id != $fromBranchIdForValidation) {
                        $errors[] = "Serial {$serial} belongs to branch {$warranty->branch_id} and cannot be transferred from branch {$fromBranchIdForValidation}.";
                        continue;
                    }
                } elseif (!is_null($warranty->distributor_id)) {
                    $errors[] = "Serial {$serial} has already been distributed to wholesaler (Distributor ID: {$warranty->distributor_id}).";
                    continue;
                }

                if ($warranty->branch_id == $toBranchId) {
                    $errors[] = "Serial {$serial} already exists in the target branch (Branch ID: {$toBranchId}).";
                    continue;
                }

                // Serial passed validation
                $validSerials[] = $serial;
            }


            if (!empty($errors)) {
                $this->generateErrorCsv($errors);
                return response()->json([
                    'success' => false,
                    'message' => 'CSV validation failed. Download error report.',
                    'error_csv' => session('error_csv'),
                    'error_count' => count($errors)
                ], 400);
            }


            $csvPath = $csvFile->store('stock_transfers', 'public');  // storage/app/public/stock_transfers
            $serials = $validSerials;
        }


        DB::beginTransaction();
        try {
            $remainingQty = $requiredQty;
            $transferredFrom = [];
            $transferredSerialsCount = 0;

            foreach ($selectedBranches as $fromBranchId) {
                if ($remainingQty <= 0) break;

                $fromStockQuery = ManageBranchProductStock::where('branch_id', $fromBranchId)
                    ->where('product_id', $realProductId)
                    ->when(!$variationType, function ($q) {
                        $q->where(function ($defaultQuery) {
                            $defaultQuery->whereNull('variation_type')->orWhere('variation_type', '');
                        });
                    });

                $fromStock = $variationType
                    ? $fromStockQuery->get()->first(function ($row) use ($variationType, $variationKey) {
                        $typeMatches = $this->variantMatcher->matches($variationType, $row->variation_type)
                            || $this->variantMatcher->matches($variationType, $row->variation_key);
                        if (!$typeMatches) {
                            return false;
                        }
                        if (!$variationKey) {
                            return true;
                        }

                        return $this->variantMatcher->matches($variationKey, $row->variation_key)
                            || $this->variantMatcher->matches($variationKey, $row->variation_type);
                    })
                    : $fromStockQuery->first();

                if (!$fromStock) {
                    continue;
                }
                $transferQty = min($fromStock->current_stock, $remainingQty);

                if ($transferQty <= 0) {
                    continue;
                }

                $transferResponse = $this->inventoryMutationService->transferBetweenBranches(
                    productId: (int)$realProductId,
                    qty: (int)$transferQty,
                    fromBranchId: (int)$fromBranchId,
                    toBranchId: (int)$toBranchId,
                    variant: $variationType,
                    referenceId: (int)$stockRequestId,
                    context: 'Stock Request Transfer',
                    stockReason: StockReason::BRANCH_TRANSFER
                );
                if (!($transferResponse['status'] ?? false)) {
                    DB::rollBack();
                    Log::error("Transfer failed by service", [
                        'from_branch' => $fromBranchId,
                        'to_branch' => $toBranchId,
                        'product_id' => $realProductId,
                        'variation' => $variationType,
                        'qty' => $transferQty,
                        'message' => $transferResponse['message'] ?? null,
                    ]);
                    return response()->json(['success' => false, 'message' => ($transferResponse['message'] ?? 'Not enough stock.')], 400);
                }
                $transferredFrom[] = $fromBranchId;
                $remainingQty -= $transferQty;




                if ($remainingQty > 0) {
                    DB::rollBack();
                    Log::error("Transfer failed - not enough stock", ['remaining' => $remainingQty]);
                    return response()->json(['success' => false, 'message' => 'Not enough stock.'], 400);
                }
                $updateData = [
                    'status' => 'transferred',
                    'received_from_branch' => implode(',', $transferredFrom),
                    'received_time' => now(),
                ];

                $this->stockRequestRepo->updateStockRequestProduct(id: $stockRequestId, data: $updateData);
                $transfer = StockTransfers::create([
                    'from_branch_id' => implode(',', $transferredFrom),
                    'to_branch_id' => $toBranchId,
                    'created_at' => now(),
                    'transfer_date' => now(),
                    'updated_at' => now(),
                ]);


                if ($isWarranty && !empty($serials)) {

                    $branchSerials = Warranty::whereIn('serial_number', $serials)
                        ->where(function ($q) use ($fromBranchId) {
                            $q->where(function ($sub) use ($fromBranchId) {
                                if ($fromBranchId == 1) {
                                    // Warehouse: branch_id NULL AND distributor_id NULL → valid
                                    $sub->whereNull('branch_id')->whereNull('distributor_id');
                                } else {
                                    // Normal branch: branch_id = fromBranchId AND distributor_id NULL
                                    $sub->where('branch_id', $fromBranchId)->whereNull('distributor_id');
                                }
                            });
                        })
                        ->get();

                    Log::info("Eligible serials for transfer from branch {$fromBranchId}", ['count' => $branchSerials->count()]);

                    if ($branchSerials->isNotEmpty()) {
                        $transferBatch = $branchSerials->take($transferQty);

                        Warranty::whereIn('id', $transferBatch->pluck('id'))
                            ->update(['branch_id' => $toBranchId, 'updated_at' => now()]);

                        $history = $transferBatch->map(fn($w) => [
                            'stock_transfer_id' => $transfer->id,
                            'serial_number' => $w->serial_number,
                            'from_branch_id' => $fromBranchId,
                            'to_branch_id' => $toBranchId,
                            'transfer_type' => 'branch_to_branch',
                            'transferred_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ])->toArray();

                        SerialTransferHistory::insert($history);

                        $transferredSerialsCount += count($transferBatch);

                        // Remove transferred serials from remaining list
                        $serials = array_values(array_diff($serials, $transferBatch->pluck('serial_number')->toArray()));

                        Log::info("Transferred serials from branch {$fromBranchId}", ['count' => count($transferBatch)]);
                    }
                }
            }

            StockTransferProduct::create([
                'stock_transfers_id' => $transfer->id,
                'product_id'         => $realProductId,
                'category_id'        => $stockRequestProduct->category_id,
                'variation_type'      => $variationType,
                'variation_key'      => $variationKey,
                'attributes'         => $attributes,
                'quantity'           => $requiredQty,
                'serial_csv_path'    => $csvPath,
                'status'             => 'Transferred'
            ]);

            Log::info('=== STOCK TRANSFER COMPLETED SUCCESSFULLY ===', [
                'stock_request_product_id' => $stockRequestProduct->id,
                'real_product_id' => $realProductId,
                'transferred_qty' => $requiredQty,
                'from_branches' => $transferredFrom,
                'to_branch' => $toBranchId,
                'serials_transferred' => $transferredSerialsCount
            ]);
            DB::commit();

            Log::info('=== STOCK TRANSFER COMPLETED SUCCESSFULLY ===', [
                'stock_request_product_id' => $stockRequestProduct->id,
                'real_product_id' => $realProductId,
                'transferred_qty' => $requiredQty,
                'from_branches' => $transferredFrom,
                'to_branch' => $toBranchId,
                'serials_transferred' => $transferredSerialsCount
            ]);

            return response()->json(['success' => true, 'message' => 'Stock transferred successfully!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('=== TRANSFER FAILED WITH EXCEPTION ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }
    // public function updateProductStockRequestStatus(Request $request): JsonResponse
    // {
    //     Log::info('=== STOCK TRANSFER REQUEST STARTED ===', [
    //         'raw_input' => $request->all(),
    //         'files' => $request->hasFile('serial_csv') ? 'Yes' : 'No'
    //     ]);


    //     $request->validate([
    //         'selected_branches.*' => 'exists:branches,id',
    //         'serial_csv' => 'required_if:is_warranty,1|file|mimes:csv,txt',
    //     ]);

    //     $selectedBranches = $request->input('selected_branches', []);
    //     if (empty($selectedBranches)) {
    //         return response()->json(['success' => false, 'message' => 'Select at least one branch.'], 400);
    //     }

    //     $stockRequestProductId = $request->input('product_id');  // stock_request_products.id
    //     $stockRequestId        = $request->input('request_id');

    //     Log::info('Finding stock_request_product', ['id' => $stockRequestProductId]);

    //     $stockRequestProduct = $this->stockRequestRepo->getStockReqProductFirstWhere(
    //         params: ['id' => $stockRequestId],
    //         relations: ['product', 'stockRequest']
    //     );

    //     if (!$stockRequestProduct) {
    //         Log::error('Stock request product NOT FOUND', ['id' => $stockRequestProductId]);
    //         return response()->json(['success' => false, 'message' => 'Product not found.'], 400);
    //     }

    //     $realProductId   = $stockRequestProduct->product_id;
    //     $toBranchId      = $stockRequestProduct->stockRequest->from_branch_id;
    //     $requiredQty     = $stockRequestProduct->quantity;
    //     $variationType   = $stockRequestProduct->variation_type;     // NEW
    //     $variationKey    = $stockRequestProduct->variation_key;      // NEW
    //     $attributes      = $stockRequestProduct->attributes;         // NEW
    //     $hasVariation    = !empty($variationType);
    //     $isWarranty      = $stockRequestProduct->product->is_warranty == 1;


    //     $serials = [];
    //     $errors = [];
    //     $csvPath = null;
    //     if ($isWarranty) {
    //         $csvFile = $request->file('serial_csv');
    //         if (!$csvFile) {
    //             return response()->json(['success' => false, 'message' => 'CSV file is required for warranty product.'], 400);
    //         }

    //         $parseResult = $this->parseCsvSerials($csvFile, $errors);
    //         $serials = $parseResult['valid_serials'];

    //         if (count($serials) !== (int)$requiredQty) {
    //             $errors[] = "CSV must contain exactly {$requiredQty} valid serials. Found: " . count($serials);
    //         }

    //         $validSerials = [];
    //         $fromBranchIdForValidation = $selectedBranches[0] ?? null;
    //         if (!$fromBranchIdForValidation) {
    //             return response()->json(['success' => false, 'message' => 'No branch selected for validation.'], 400);
    //         }

    //         foreach ($serials as $serial) {
    //             $warranty = Warranty::where('serial_number', $serial)->first();

    //             if (!$warranty) {
    //                 $errors[] = "Serial {$serial} does not exist in system.";
    //                 continue;
    //             }

    //             // Validation logic
    //             if (is_null($warranty->branch_id) && is_null($warranty->distributor_id)) {
    //                 if ($fromBranchIdForValidation != 1) {
    //                     $errors[] = "Serial {$serial} belongs to system and can only be transferred from branch 1.";
    //                     continue;
    //                 }
    //             } elseif (!is_null($warranty->branch_id) && is_null($warranty->distributor_id)) {
    //                 if ($warranty->branch_id != $fromBranchIdForValidation) {
    //                     $errors[] = "Serial {$serial} belongs to branch {$warranty->branch_id} and cannot be transferred from branch {$fromBranchIdForValidation}.";
    //                     continue;
    //                 }
    //             } elseif (!is_null($warranty->distributor_id)) {
    //                 $errors[] = "Serial {$serial} has already been distributed to wholesaler (Distributor ID: {$warranty->distributor_id}).";
    //                 continue;
    //             }

    //             if ($warranty->branch_id == $toBranchId) {
    //                 $errors[] = "Serial {$serial} already exists in the target branch (Branch ID: {$toBranchId}).";
    //                 continue;
    //             }

    //             // Serial passed validation
    //             $validSerials[] = $serial;
    //         }


    //         if (!empty($errors)) {
    //             $this->generateErrorCsv($errors);
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'CSV validation failed. Download error report.',
    //                 'error_csv' => session('error_csv'),
    //                 'error_count' => count($errors)
    //             ], 400);
    //         }


    //         $csvPath = $csvFile->store('stock_transfers', 'public');  // storage/app/public/stock_transfers
    //         $serials = $validSerials;
    //     }


    //     DB::beginTransaction();
    //     try {
    //         $remainingQty = $requiredQty;
    //         $transferredFrom = [];
    //         $transferredSerialsCount = 0;

    //         foreach ($selectedBranches as $fromBranchId) {
    //             if ($remainingQty <= 0) break;

    //             $fromStock = ManageBranchProductStock::where('branch_id', $fromBranchId)
    //                 ->where('product_id', $realProductId)
    //                 ->when($variationType, function ($q) use ($variationType, $variationKey) {
    //                     $q->where('variation_type', $variationType);
    //                     if ($variationKey) {
    //                         $q->where('variation_key', $variationKey);
    //                     }
    //                 }, function ($q) {
    //                     $q->whereNull('variation_type');
    //                 })
    //                 ->first();

    //             if (!$fromStock) {
    //                 continue;
    //             }
    //             $transferQty = min($fromStock->current_stock, $remainingQty);

    //             if ($transferQty <= 0) {
    //                 continue;
    //             }

    //             // Stock transfer
    //             $fromStock->decrement('current_stock', $transferQty);
    //             Log::info("Deducted {$transferQty} from branch {$fromBranchId}");

    //             $toStock = ManageBranchProductStock::firstOrCreate([
    //                 'branch_id'       => $toBranchId,
    //                 'product_id'      => $realProductId,
    //                 'variation_type'  => $variationType,
    //                 'variation_key'   => $variationKey,
    //                 'attributes'      => $stockRequestProduct->attributes,
    //             ], ['current_stock' => 0]);

    //             $toStock->increment('current_stock', $transferQty);

    //             $transferredFrom[] = $fromBranchId;
    //             $remainingQty -= $transferQty;




    //             if ($remainingQty > 0) {
    //                 DB::rollBack();
    //                 Log::error("Transfer failed - not enough stock", ['remaining' => $remainingQty]);
    //                 return response()->json(['success' => false, 'message' => 'Not enough stock.'], 400);
    //             }
    //             $updateData = [
    //                 'status' => 'transferred',
    //                 'received_from_branch' => implode(',', $transferredFrom),
    //                 'received_time' => now(),
    //             ];

    //             $this->stockRequestRepo->updateStockRequestProduct(id: $stockRequestId, data: $updateData);
    //             $transfer = StockTransfers::create([
    //                 'from_branch_id' => implode(',', $transferredFrom),
    //                 'to_branch_id' => $toBranchId,
    //                 'created_at' => now(),
    //                 'transfer_date' => now(),
    //                 'updated_at' => now(),
    //             ]);


    //             if ($isWarranty && !empty($serials)) {

    //                 $branchSerials = Warranty::whereIn('serial_number', $serials)
    //                     ->where(function ($q) use ($fromBranchId) {
    //                         $q->where(function ($sub) use ($fromBranchId) {
    //                             if ($fromBranchId == 1) {
    //                                 // Warehouse: branch_id NULL AND distributor_id NULL → valid
    //                                 $sub->whereNull('branch_id')->whereNull('distributor_id');
    //                             } else {
    //                                 // Normal branch: branch_id = fromBranchId AND distributor_id NULL
    //                                 $sub->where('branch_id', $fromBranchId)->whereNull('distributor_id');
    //                             }
    //                         });
    //                     })
    //                     ->get();

    //                 Log::info("Eligible serials for transfer from branch {$fromBranchId}", ['count' => $branchSerials->count()]);

    //                 if ($branchSerials->isNotEmpty()) {
    //                     $transferBatch = $branchSerials->take($transferQty);

    //                     Warranty::whereIn('id', $transferBatch->pluck('id'))
    //                         ->update(['branch_id' => $toBranchId, 'updated_at' => now()]);

    //                     $history = $transferBatch->map(fn($w) => [
    //                         'stock_transfer_id' => $transfer->id,
    //                         'serial_number' => $w->serial_number,
    //                         'from_branch_id' => $fromBranchId,
    //                         'to_branch_id' => $toBranchId,
    //                         'transfer_type' => 'branch_to_branch',
    //                         'transferred_at' => now(),
    //                         'created_at' => now(),
    //                         'updated_at' => now(),
    //                     ])->toArray();

    //                     SerialTransferHistory::insert($history);

    //                     $transferredSerialsCount += count($transferBatch);

    //                     // Remove transferred serials from remaining list
    //                     $serials = array_values(array_diff($serials, $transferBatch->pluck('serial_number')->toArray()));

    //                     Log::info("Transferred serials from branch {$fromBranchId}", ['count' => count($transferBatch)]);
    //                 }
    //             }
    //         }

    //         StockTransferProduct::create([
    //             'stock_transfers_id' => $transfer->id,
    //             'product_id'         => $realProductId,
    //             'category_id'        => $stockRequestProduct->category_id,
    //             'variation_type'      => $variationType,
    //             'variation_key'      => $variationKey,
    //             'attributes'         => $attributes,
    //             'quantity'           => $requiredQty,
    //             'serial_csv_path'    => $csvPath,
    //             'status'             => 'Transferred'
    //         ]);

    //         Log::info('=== STOCK TRANSFER COMPLETED SUCCESSFULLY ===', [
    //             'stock_request_product_id' => $stockRequestProduct->id,
    //             'real_product_id' => $realProductId,
    //             'transferred_qty' => $requiredQty,
    //             'from_branches' => $transferredFrom,
    //             'to_branch' => $toBranchId,
    //             'serials_transferred' => $transferredSerialsCount
    //         ]);
    //         DB::commit();

    //         Log::info('=== STOCK TRANSFER COMPLETED SUCCESSFULLY ===', [
    //             'stock_request_product_id' => $stockRequestProduct->id,
    //             'real_product_id' => $realProductId,
    //             'transferred_qty' => $requiredQty,
    //             'from_branches' => $transferredFrom,
    //             'to_branch' => $toBranchId,
    //             'serials_transferred' => $transferredSerialsCount
    //         ]);

    //         return response()->json(['success' => true, 'message' => 'Stock transferred successfully!'], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('=== TRANSFER FAILED WITH EXCEPTION ===', [
    //             'error' => $e->getMessage(),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json(['success' => false, 'message' => 'Server error.'], 500);
    //     }
    // }
    private function storeCsvFile($file, string $folder): string
    {
        $path = $file->store($folder, 'public');
        return 'storage/' . $path;
    }
    public function downloadErrorCsv($filename)
    {
        $path = storage_path('app/public/errors/' . $filename);
        if (!file_exists($path)) abort(404);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    private function parseCsvSerials($file, &$errors = []): array
    {
        $serials = [];
        $handle = fopen($file->getRealPath(), 'r');
        $row = 0;
        $totalLines = 0;
        $validCount = 0;

        // Skip BOM if present
        $bom = pack('H*', 'EFBBBF');
        $content = file_get_contents($file->getRealPath());
        if (substr($content, 0, 3) === $bom) {
            $content = substr($content, 3);
            file_put_contents($file->getRealPath(), $content);
        }

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $totalLines++;

            // Skip completely empty rows
            if (count($data) === 1 && trim($data[0]) === '') {
                $errors[] = "Row {$totalLines}: Empty line";
                continue;
            }

            foreach ($data as $idx => $cell) {
                $row++;
                $serial = trim($cell);

                // Skip empty cells
                if ($serial === '') {
                    $errors[] = "Row {$totalLines}, Column " . ($idx + 1) . ": Empty serial";
                    continue;
                }

                // Length check
                if (strlen($serial) > 50) {
                    $errors[] = "Row {$totalLines}: Serial '{$serial}' too long (>50 chars)";
                    continue;
                }

                // Duplicate check
                if (in_array($serial, $serials)) {
                    $errors[] = "Row {$totalLines}: Duplicate serial '{$serial}'";
                    continue;
                }

                $serials[] = $serial;
                $validCount++;
            }
        }
        fclose($handle);

        return [
            'valid_serials' => $serials,
            'total_lines'   => $totalLines,
            'valid_count'   => $validCount
        ];
    }

    private function generateErrorCsv(array $errors)
    {
        $filename = 'transfer_errors_' . now()->format('Ymd_His') . '.csv';
        $path = storage_path('app/public/errors/' . $filename);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $file = fopen($path, 'w');
        fputcsv($file, ['Error']);
        foreach ($errors as $error) {
            fputcsv($file, [$error]);
        }
        fclose($file);
        session()->flash('error_csv', $filename);
        session()->flash('error_count', count($errors));
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
