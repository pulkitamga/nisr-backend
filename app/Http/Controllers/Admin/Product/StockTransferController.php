<?php

namespace App\Http\Controllers\Admin\Product;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Warranty;
use App\Models\Attribute;
use App\Enums\StockReason;
use App\Enums\WebConfigKey;
use App\Models\ProductStock;
use App\Traits\ProductTrait;
use Illuminate\Http\Request;
use App\Models\StockTransfers;
use App\Services\ProductService;
use App\Traits\FileManagerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Exports\ProductListExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use illuminate\Support\Facades\Log;
use App\Models\StockTransferProduct;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SerialTransferHistory;
use App\Services\StockRequestService;
use App\Services\InventoryMutationService;
use Illuminate\Http\RedirectResponse;
use App\Services\StockTransferService;
use App\Models\ProductStockTransaction;
use App\Domain\Stock\Support\VariantMatcher;
use illuminate\Support\Facades\Storage;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductAddRequest;
use App\Models\ManageBranchProductStock;
use App\Exports\RestockProductListExport;
use App\Http\Requests\ProductUpdateRequest;
use App\Enums\ViewPaths\Admin\StockTransfer;
use App\Events\ProductRequestStatusUpdateEvent;
use App\Http\Requests\Admin\ProductDenyRequest;
use App\Http\Requests\Admin\StockTransferAddProduct;
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
use App\Contracts\Repositories\StockTransferRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Contracts\Repositories\FlashDealProductRepositoryInterface;
use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Contracts\Repositories\DigitalProductAuthorRepositoryInterface;
use App\Contracts\Repositories\StockClearanceProductRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\DigitalProductVariationRepositoryInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockTransferController extends BaseController
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
        private readonly StockTransferRepositoryInterface             $stockTransferRepo,
        private readonly StockTransferService                         $stockTransferService,
        private readonly InventoryMutationService                     $inventoryMutationService,
        private readonly VariantMatcher                               $variantMatcher,

    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getStockTransferListView($request);
    }


    public function getStockTransferListView(Request $request): View|RedirectResponse
    {
        $aStockTransfers = $this->stockTransferListQuery($request)
            ->paginate($this->resolveListPerPage($request))
            ->appends($request->query());

        return view(StockTransfer::LIST[VIEW], compact('aStockTransfers'));
    }

    public function exportList(Request $request): StreamedResponse
    {
        $stockTransfers = $this->stockTransferListQuery($request)->get();

        return response()->streamDownload(function () use ($stockTransfers) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                translate('To Branch'),
                translate('Transfer Date'),
                translate('Products'),
                translate('Category'),
                translate('Variation'),
                translate('Qty'),
                translate('Status'),
            ]);

            foreach ($stockTransfers as $stockTransfer) {
                foreach ($stockTransfer->products as $product) {
                    $variationLabel = $product->variation_type ?: translate('Default');
                    if ($product->variation_key) {
                        $variationLabel .= ' (' . Str::replace(':', ' : ', Str::replace('|', ' • ', $product->variation_key)) . ')';
                    }

                    fputcsv($handle, [
                        $stockTransfer->toBranch?->getTranslatedField('branch_name') ?? translate('not_available'),
                        $stockTransfer->transfer_date ? date('M d, Y', strtotime($stockTransfer->transfer_date)) : translate('not_available'),
                        optional($product->product)->getTranslatedField('name') ?? translate('not_available'),
                        optional($product->category)->getTranslatedField('name') ?? translate('not_available'),
                        $variationLabel,
                        (string) $product->quantity,
                        translate($product->status),
                    ]);
                }
            }

            fclose($handle);
        }, 'stock-transfer-list.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
  public function getStock(Request $request)
{
    $query = ManageBranchProductStock::where('branch_id', $request->branch_id)
        ->where('product_id', $request->product_id)
        ->when(!$request->filled('variation_type'), function ($q) {
            return $q->where(function ($defaultQuery) {
                $defaultQuery->whereNull('variation_type')->orWhere('variation_type', '');
            });
        });

    $stock = 0;
    if ($request->filled('variation_type')) {
        $entry = $query->get()->first(function ($row) use ($request) {
            return $this->variantMatcher->matches($request->variation_type, $row->variation_type)
                || $this->variantMatcher->matches($request->variation_type, $row->variation_key);
        });
        $stock = (int)($entry->current_stock ?? 0);
    } else {
        $stock = (int)($query->value('current_stock') ?? 0);
    }

    return response()->json(['stock' => $stock]);
}
    public function addStockTransferListView(Request $request): View|RedirectResponse
    {
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
        $toBranches = $this->branchRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $fromBranches = $this->branchRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $brands = $this->brandRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $attributes = $this->attributeRepo->getListWhere(dataLimit: 'all');
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]);
        $totalRestockProducts = $this->restockProductRepo->getListWhere(filters: $filters, dataLimit: 'all')->count();
        $products = $this->productRepo->getListWhere(filters: ['scope' => 'active', 'product_type' => 'physical'], dataLimit: 'all');

        $productsAttributes = [];
        $allAttributeIds = collect();

        foreach ($products as $product) {
            $attrJson = $product->getRawOriginal('attributes'); // RAW JSON string from DB
            $choiceJson = $product->getRawOriginal('choice_options'); // NEW: Get choice_options
            $ids = [];

            if ($attrJson && $attrJson !== 'null') {
                // Decode JSON safely
                $decoded = json_decode($attrJson, true);

                // Agar double-encoded JSON hai (string ke andar JSON string)
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }

                if ($decoded) {
                    // Agar array hai
                    if (is_array($decoded)) {
                        foreach ($decoded as $item) {
                            if (is_numeric($item)) {
                                $ids[] = (int)$item;
                            } elseif (is_array($item) && isset($item['id'])) {
                                $ids[] = (int)$item['id'];
                            }
                        }
                    }
                    // Agar single numeric string/number
                    elseif (is_numeric($decoded)) {
                        $ids[] = (int)$decoded;
                    }
                }
            }

            $ids = array_unique($ids);
            $allAttributeIds = $allAttributeIds->merge($ids);

            // Temporarily store ids for this product
            $product->temp_attribute_ids = $ids;
            $product->temp_choice_options = json_decode($choiceJson, true) ?? []; // NEW: Store choice_options
        }

        // Load attributes from DB using Attribute model
        $attributesMap = [];
        if ($allAttributeIds->isNotEmpty()) {
            $attributesMap = Attribute::whereIn('id', $allAttributeIds->unique())
                ->get()
                ->keyBy('id');
        }

        // Prepare final productsAttributes array
        foreach ($products as $product) {
            $productAttrs = [];
            foreach ($product->temp_attribute_ids as $id) {
                if (isset($attributesMap[$id])) {
                    $attrName = $attributesMap[$id]->name;
                    // Find matching choice_option by title
                    $matchingChoice = collect($product->temp_choice_options)->firstWhere('title', $attrName);
                    $variants = $matchingChoice['options'] ?? [];
                    $productAttrs[] = [
                        'id' => $attributesMap[$id]->id,
                        'name' => $attrName,
                        'variants' => $variants // NEW: Add variants
                    ];
                }
            }
            $productsAttributes[$product->id] = $productAttrs;
        }
        return view(StockTransfer::ADD[VIEW], compact(
            'toBranches',
            'fromBranches',
            'products',
            'brands',
            'categories',
            'subCategory',
            'filters',
            'totalRestockProducts',
            'searchValue',
            'cartItems',
            'attributes',
            'productsAttributes'

        ));
    }

    private function stockTransferListQuery(Request $request): Builder
    {
        $searchValue = $this->sanitizeListSearch($request->input('searchValue'));

        return StockTransfers::query()
            ->with([
                'products' => fn ($query) => $this->applyStockTransferProductFilters($query, $request)
                    ->with(['product.translations', 'category.translations']),
                'toBranch.translations',
            ])
            ->when($request->filled('restock_date'), fn(Builder $query) => $query->whereDate('transfer_date', $request->restock_date))
            ->when($request->filled('category_id'), function (Builder $query) use ($request) {
                $query->whereHas('products.product', fn(Builder $productQuery) => $productQuery->where('category_id', $request->category_id));
            })
            ->when($request->filled('sub_category_id'), function (Builder $query) use ($request) {
                $query->whereHas('products.product', fn(Builder $productQuery) => $productQuery->where('sub_category_id', $request->sub_category_id));
            })
            ->when($request->filled('brand_id'), function (Builder $query) use ($request) {
                $query->whereHas('products.product', fn(Builder $productQuery) => $productQuery->where('brand_id', $request->brand_id));
            })
            ->when($searchValue !== '', function (Builder $query) use ($searchValue) {
                $query->whereHas('products.product', function (Builder $productQuery) use ($searchValue) {
                    $productQuery
                        ->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('code', 'like', '%' . $searchValue . '%');
                });
            })
            ->orderByDesc('id');
    }

    private function applyStockTransferProductFilters($query, Request $request)
    {
        $searchValue = $this->sanitizeListSearch($request->input('searchValue'));

        return $query
            ->when($request->filled('category_id'), function (Builder $innerQuery) use ($request) {
                $innerQuery->whereHas('product', fn (Builder $productQuery) => $productQuery->where('category_id', $request->category_id));
            })
            ->when($request->filled('sub_category_id'), function (Builder $innerQuery) use ($request) {
                $innerQuery->whereHas('product', fn (Builder $productQuery) => $productQuery->where('sub_category_id', $request->sub_category_id));
            })
            ->when($request->filled('brand_id'), function (Builder $innerQuery) use ($request) {
                $innerQuery->whereHas('product', fn (Builder $productQuery) => $productQuery->where('brand_id', $request->brand_id));
            })
            ->when($searchValue !== '', function (Builder $innerQuery) use ($searchValue) {
                $innerQuery->whereHas('product', function (Builder $productQuery) use ($searchValue) {
                    $productQuery
                        ->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('code', 'like', '%' . $searchValue . '%');
                });
            });
    }

    private function resolveListPerPage(Request $request): int
    {
        if ($request->filled('choose_first') && (int) $request->choose_first > 0) {
            return (int) $request->choose_first;
        }

        return (int) (getWebConfig('pagination_limit') ?? 10);
    }

    private function sanitizeListSearch(?string $value): string
    {
        return mb_substr(trim((string) $value), 0, 100);
    }
    // public function saveStockTransfer(StockTransferAddProduct $request, StockTransferService $service): JsonResponse|RedirectResponse
    // {
    //     DB::beginTransaction();
    //     try {
    //         $dataArray = $service->getAddData(request: $request);
    //         $transfer = $this->stockTransferRepo->add(data: $dataArray);

    //         $fromBranchId = $request->from_branch_id;
    //         $toBranchId = $request->to_branch_id;

    //         if ($fromBranchId == $toBranchId) {
    //         Toastr::error('Source and destination branch cannot be the same!');
    //         return redirect()->back()->withInput();
    //     }
    //         foreach ($request->products as $index => $item) {
    //             $productId = $item['product_id'];
    //             $qty = (int)($item['product_qty'] ?? 0);
    //             $variationType = $item['variation_type'] ?? null;

    //             if ($qty <= 0) continue;

    //             $product = Product::findOrFail($productId);

    //             // 1. Variation Validation
    //             $variations = $product->variation ? json_decode($product->variation, true) : [];
    //             $hasVariation = is_array($variations) && count($variations) > 0;

    //             if ($hasVariation && empty($variationType)) {
    //                 throw new \Exception("Row " . ($index + 1) . ": Please select a variation.");
    //             }

    //             $variationKey = $selectedVariation['variation_key'] ?? null;
    //             $attributes   = $selectedVariation['attributes'] ?? null;

    //             $csvFile = $request->file("products.{$index}.serial_csv");
    //             $csvPath = null;
    //             $serials = [];

    //             if ($product->is_traceable == 1) {
    //                 if (!$csvFile) {
    //                     throw new \Exception("Row " . ($index + 1) . ": CSV file is required for traceable product.");
    //                 }

    //                 $errors = [];
    //                 $serials = $this->parseCsvSerials($csvFile, $errors);

    //                 if (count($serials) !== $qty) {
    //                     $errors[] = "Row " . ($index + 1) . ": Expected {$qty} serials, found " . count($serials);
    //                 }

    //                 // FULL SERIAL VALIDATION (yeh pehle miss ho gaya tha)
    //                 if (!empty($serials)) {
    //                     // 1. Serial exists in warranty table?
    //                     $existingSerials = Warranty::whereIn('serial_number', $serials)
    //                         ->pluck('serial_number')->toArray();
    //                     $missingSerials = array_diff($serials, $existingSerials);
    //                     foreach ($missingSerials as $s) {
    //                         $errors[] = "Serial {$s} not found in system.";
    //                     }

    //                     // 2. Serial belongs to FROM branch?
    //                     $wrongBranch = Warranty::whereIn('serial_number', $serials)
    //                         ->where('branch_id', '!=', $fromBranchId)
    //                         ->pluck('serial_number')->toArray();
    //                     foreach ($wrongBranch as $s) {
    //                         $errors[] = "Serial {$s} does not belong to source branch.";
    //                     }

    //                     // 3. Serial already in TO branch?
    //                     $alreadyInTo = Warranty::whereIn('serial_number', $serials)
    //                         ->where('branch_id', $toBranchId)
    //                         ->pluck('serial_number')->toArray();
    //                     foreach ($alreadyInTo as $s) {
    //                         $errors[] = "Serial {$s} already exists in destination branch.";
    //                     }
    //                 }

    //                 if (!empty($errors)) {
    //                     $csvName = $this->generateErrorCsv($errors);
    //                     session(['error_csv' => $csvName, 'error_count' => count($errors)]);
    //                     throw new \Exception("Serial validation failed. Download error report.");
    //                 }

    //                 $csvPath = $csvFile->store('stock_transfers', 'public');
    //             }

    //             $stockQuery = ManageBranchProductStock::where('branch_id', $fromBranchId)
    //                 ->where('product_id', $productId);

    //             if ($hasVariation && $variationType) {
    //                 $stockQuery->where('variation_type', $variationType);
    //             } else {
    //                 $stockQuery->whereNull('variation_type');
    //             }

    //             $fromStock = $stockQuery->first();

    //             if (!$fromStock || $fromStock->current_stock < $qty) {
    //                 $var = $variationType ? " ($variationType)" : "";
    //                 throw new \Exception("Insufficient stock for {$product->name}{$var}");
    //             }

    //             $fromStock->decrement('current_stock', $qty);
    //             $toStock = ManageBranchProductStock::firstOrCreate(
    //                 [
    //                     'branch_id'      => $toBranchId,
    //                     'product_id'     => $productId,
    //                     'variation_type' => $variationType,
    //                     'variation_key'  => $fromStock->variation_key ?? "",
    //                     'attributes'     => $fromStock->attributes ?? '',
    //                 ],
    //                 ['current_stock' => 0]
    //             );
    //             $toStock->increment('current_stock', $qty);

    //             StockTransferProduct::create([
    //                 'stock_transfers_id' => $transfer->id,
    //                 'product_id'         => $productId,
    //                 'category_id'        => $item['category_id'],
    //                 'variation_type'     => $variationType,
    //                 'variation_key'  => $fromStock->variation_key ?? '',
    //                 'attributes'     => $fromStock->attributes ?? '',
    //                 'quantity'           => $qty,
    //                 'serial_csv_path'    => $csvPath,
    //             ]);

    //             if ($product->is_traceable == 1 && !empty($serials)) {
    //                 Warranty::whereIn('serial_number', $serials)
    //                     ->update(['branch_id' => $toBranchId]);

    //                 $history = array_map(fn($s) => [
    //                     'stock_transfer_id' => $transfer->id,
    //                     'serial_number' => $s,
    //                     'from_branch_id' => $fromBranchId,
    //                     'to_branch_id' => $toBranchId,
    //                     'transfer_type' => 'branch_to_branch',
    //                     'transferred_at' => now(),
    //                 ], $serials);

    //                 SerialTransferHistory::insert($history);
    //             }
    //         }
    //         DB::commit();
    //         Toastr::success('Stock transferred successfully!');
    //         return redirect()->route('admin.stock-transfer.list');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Transfer Failed: ' . $e->getMessage());

    //         // Agar CSV/serial error hai to session set karo
    //         if (str_contains($e->getMessage(), 'Serial') || str_contains($e->getMessage(), 'CSV')) {
    //             Toastr::error("Transfer failed. Check error report.");
    //             return redirect()->back()->withInput();
    //         }

    //         Toastr::error($e->getMessage());
    //         return redirect()->back()->withInput();
    //     }
    // }

     
       public function saveStockTransfer(StockTransferAddProduct $request, StockTransferService $service): JsonResponse|RedirectResponse
{
    DB::beginTransaction();
    try {
        $dataArray = $service->getAddData(request: $request);
        $transfer = $this->stockTransferRepo->add(data: $dataArray);

        $fromBranchId = $request->from_branch_id;
        $toBranchId   = $request->to_branch_id;

        if ($fromBranchId == $toBranchId) {
            Toastr::error('Source and destination branch cannot be the same!');
            return redirect()->back()->withInput();
        }

        foreach ($request->products as $index => $item) {
            $productId     = $item['product_id'];
            $qty           = (int)($item['product_qty'] ?? 0);
            $variationType = $item['variation_type'] ?? null;

            if ($qty <= 0) continue;

            $product = Product::findOrFail($productId);

            // Variation validation
            $variations   = $product->variation ? json_decode($product->variation, true) : [];
            $hasVariation = is_array($variations) && count($variations) > 0;

            if ($hasVariation && empty($variationType)) {
                throw new \Exception("Row " . ($index + 1) . ": Please select a variation.");
            }

            $selectedVariation = collect($variations)->first(
                fn($row) => $this->variantMatcher->matches($variationType, $row['type'] ?? null)
            ) ?? [];
            $variationKey      = $selectedVariation['variation_key'] ?? null;
            $attributes        = $selectedVariation['attributes'] ?? null;
            $variationType     = $selectedVariation['type'] ?? $variationType;

            // Handle CSV/traceability
            $csvFile = $request->file("products.{$index}.serial_csv");
            $csvPath = null;
            $serials = [];
            $isTraceable = (int)$product->is_traceable === 1;

            if ($isTraceable) {
                if (!$csvFile) {
                    throw new \Exception("Row " . ($index + 1) . ": CSV file is required for traceable product.");
                }

                $errors = [];
                $serials = $this->parseCsvSerials($csvFile, $errors);

                if (count($serials) !== $qty) {
                    $errors[] = "Row " . ($index + 1) . ": Expected {$qty} serials, found " . count($serials);
                }

                // FULL SERIAL VALIDATION
                if (!empty($serials)) {
                    $existingSerials = Warranty::whereIn('serial_number', $serials)
                        ->pluck('serial_number')->toArray();
                    $missingSerials = array_diff($serials, $existingSerials);
                    foreach ($missingSerials as $s) {
                        $errors[] = "Serial {$s} not found in system.";
                    }

                    $wrongBranch = Warranty::whereIn('serial_number', $serials)
                        ->where('branch_id', '!=', $fromBranchId)
                        ->pluck('serial_number')->toArray();
                    foreach ($wrongBranch as $s) {
                        $errors[] = "Serial {$s} does not belong to source branch.";
                    }

                    $alreadyInTo = Warranty::whereIn('serial_number', $serials)
                        ->where('branch_id', $toBranchId)
                        ->pluck('serial_number')->toArray();
                    foreach ($alreadyInTo as $s) {
                        $errors[] = "Serial {$s} already exists in destination branch.";
                    }
                }

                if (!empty($errors)) {
                    $csvName = $this->generateErrorCsv($errors);
                    session(['error_csv' => $csvName, 'error_count' => count($errors)]);
                    throw new \Exception("Serial validation failed. Download error report.");
                }

                $csvPath = $csvFile->store('stock_transfers', 'public');
            }

            $transferResponse = $this->inventoryMutationService->transferBetweenBranches(
                productId: (int)$productId,
                qty: $qty,
                fromBranchId: (int)$fromBranchId,
                toBranchId: (int)$toBranchId,
                variant: $variationType,
                referenceId: (int)$transfer->id,
                context: 'Stock Transfer',
                stockReason: StockReason::BRANCH_TRANSFER
            );
            if (!($transferResponse['status'] ?? false)) {
                $var = $variationType ? " ({$variationType})" : '';
                throw new \Exception($transferResponse['message'] ?? "Insufficient stock for {$product->name}{$var}");
            }

            // Create StockTransferProduct
            StockTransferProduct::create([
                'stock_transfers_id' => $transfer->id,
                'product_id'         => $productId,
                'category_id'        => $item['category_id'],
                'variation_type'     => $variationType,
                'variation_key'      => $variationKey ?? '',
                'attributes'         => $attributes ?? '',
                'quantity'           => $qty,
                'serial_csv_path'    => $csvPath,
            ]);

            // Update serial ownership and serial transfer history
            if ($isTraceable && !empty($serials)) {
                Warranty::whereIn('serial_number', $serials)
                    ->update(['branch_id' => $toBranchId]);

                $history = array_map(fn($s) => [
                    'stock_transfer_id' => $transfer->id,
                    'serial_number'     => $s,
                    'from_branch_id'    => $fromBranchId,
                    'to_branch_id'      => $toBranchId,
                    'transfer_type'     => 'branch_to_branch',
                    'transferred_at'    => now(),
                ], $serials);

                SerialTransferHistory::insert($history);
            }
        }

        DB::commit();
        Toastr::success('Stock transferred successfully!');
        return redirect()->route('admin.stock-transfer.list');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Transfer Failed: ' . $e->getMessage());

        if (str_contains($e->getMessage(), 'Serial') || str_contains($e->getMessage(), 'CSV')) {
            Toastr::error("Transfer failed. Check error report.");
            return redirect()->back()->withInput();
        }

        Toastr::error($e->getMessage());
        return redirect()->back()->withInput();
    }
}

    private function storeCsvFile($file, string $folder): string
    {
        $path = $file->store($folder, 'public');               // storage/app/public/...
        return 'storage/' . $path;                             // web-accessible URL
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
        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            $serial = trim($data[0] ?? '');
            if (empty($serial)) {
                $errors[] = "Row {$row}: Empty serial";
                continue;
            }
            if (strlen($serial) > 50) {
                $errors[] = "Row {$row}: Serial too long";
                continue;
            }
            if (in_array($serial, $serials)) {
                $errors[] = "Row {$row}: Duplicate serial";
                continue;
            }
            $serials[] = $serial;
        }
        fclose($handle);
        return array_unique($serials);
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

        return $filename; // session set na karo yahan
    }


    private function updateBranchStock($request, $product)
    {
        $transferResponse = $this->inventoryMutationService->transferBetweenBranches(
            productId: (int)$product['product_id'],
            qty: (int)$product['quantity'],
            fromBranchId: (int)$request['from_branch_id'],
            toBranchId: (int)$request['to_branch_id'],
            variant: ($product['variation_type'] ?? null),
            referenceId: 0,
            context: 'Stock Transfer Helper',
            stockReason: StockReason::BRANCH_TRANSFER
        );
        if (!($transferResponse['status'] ?? false)) {
            throw new \Exception($transferResponse['message'] ?? ('Stock transfer failed for product ' . $product['product_id']));
        }
    }

    // StockTransferController.php
    public function downloadCsv($stockTransferProductId)
    {
        $item = StockTransferProduct::findOrFail($stockTransferProductId);

        if (!$item->serial_csv_path || !Storage::disk('public')->exists($item->serial_csv_path)) {
            abort(404);
        }

        session()->forget(['error_csv', 'error_count']);

        return Storage::disk('public')->download($item->serial_csv_path, 'transfer_report.csv');
    }
}
