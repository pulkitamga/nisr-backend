<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Http\Controllers\Controller;
use App\Models\StockTransfers;
use App\Models\StockTransferProduct;
use App\Models\StockReceived;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\StockRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
use App\Repositories\DigitalProductPublishingHouseRepository;
use App\Services\ProductService;
use App\Services\StockRequestService;
use App\Traits\FileManagerTrait;
use App\Traits\ProductTrait;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\Admin\StockRequestAddProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Models\StockRequestProduct;
use Symfony\Component\HttpFoundation\StreamedResponse;



class StockMovementController extends Controller
{
    private const ACTIONABLE_TRANSFER_STATUSES = ['pending', 'transferred', 'Transferred'];


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
        private readonly StockRequestRepositoryInterface         	$stockRequestRepo,
        private readonly StockRequestService         				$stockRequestService,

    )

    {
    }

    public function approveIndex()
    {
        $authUser = Auth::guard('admin')->user();
        if (!$authUser) {
            abort(403);
        }

        if (!$authUser->isSuperAdmin() && !$authUser->branch_id) {
            Toastr::error(translate('branch_manager_must_be_assigned_to_branch'));

            return redirect()->route('admin.branch.index');
        }

        $transfers = StockTransfers::with([
            'products' => function ($query) {
                $query->whereIn('status', self::ACTIONABLE_TRANSFER_STATUSES);
            },
            'products.product.translations',
            'products.category.translations',
            'toBranch.translations',
            'fromBranch.translations',
        ])
            ->when(
                !$authUser->isSuperAdmin(),
                fn($query) => $query->where('to_branch_id', (int)$authUser->branch_id)
            )
            ->whereHas('products', function ($query) {
                $query->whereIn('status', self::ACTIONABLE_TRANSFER_STATUSES);
            })
            ->latest()
            ->get();

        return view('admin-views.branch-management.stock-movement.stock-approve', compact('transfers'));
    }

    public function approveProduct($id)
    {
        return $this->processTransferDecision($id, 'approved');
    }

    public function rejectProduct($id)
    {
        return $this->processTransferDecision($id, 'rejected');
    }




    public function request(Request $request)
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
        $fromBranches = $this->branchRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $toBranches = $this->branchRepo->getListWhere(filters: ['status' => 1, 'id' => 1], dataLimit: 'all');
        $brands = $this->brandRepo->getListWhere(filters: ['status' => 1], dataLimit: 'all');
        $attributes = $this->attributeRepo->getListWhere(dataLimit: 'all');
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]);
        $totalRestockProducts = $this->restockProductRepo->getListWhere(filters: $filters, dataLimit: 'all')->count();
        $products = $this->productRepo->getListWhere(filters: ['scope' => 'active'], dataLimit: 'all');
        return view('admin-views.branch-management.stock-movement.stock-request', compact(
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

    private function processTransferDecision(int|string $id, string $decision): RedirectResponse
    {
        $authUser = Auth::guard('admin')->user();
        if (!$authUser) {
            abort(403);
        }

        if (!$authUser->isSuperAdmin() && !$authUser->branch_id) {
            return back()->with('error', translate('branch_manager_must_be_assigned_to_branch'));
        }

        return DB::transaction(function () use ($id, $decision, $authUser) {
            $product = StockTransferProduct::with(['stockTransfer.toBranch'])
                ->lockForUpdate()
                ->findOrFail($id);

            $stockTransfer = $product->stockTransfer;
            $destinationBranch = $stockTransfer?->toBranch;

            if (!$stockTransfer || !$destinationBranch) {
                return back()->with('error', translate('stock_transfer_not_found_or_destination_branch_missing'));
            }

            if (!$this->canManageTransfer($authUser, (int)$destinationBranch->id)) {
                return back()->with('error', translate('you_are_not_authorized_to_manage_this_stock_transfer'));
            }

            if (!in_array($this->normalizeTransferStatus($product->status), ['pending', 'transferred'], true)) {
                return back()->with('error', translate('stock_transfer_has_already_been_processed'));
            }

            $quantity = (int)$product->quantity;
            if ($quantity <= 0) {
                return back()->with('error', translate('stock_transfer_quantity_must_be_greater_than_zero'));
            }

            $product->status = $decision;
            $product->approved_at = now();
            $product->save();

            StockReceived::create([
                'branch_id' => (int)$destinationBranch->id,
                'product_id' => (int)$product->product_id,
                'quantity_received' => $quantity,
                'received_date' => now()->toDateString(),
                'status' => $decision,
                'approved_by' => (string)$authUser->name,
            ]);

            $successMessage = $decision === 'approved'
                ? translate('stock_approved_and_received_successfully')
                : translate('stock_rejected_and_recorded_successfully');

            return back()->with('success', $successMessage);
        });
    }

    private function canManageTransfer($authUser, int $destinationBranchId): bool
    {
        if ($authUser?->isSuperAdmin()) {
            return true;
        }

        return (int)($authUser?->branch_id ?? 0) === $destinationBranchId;
    }

    private function normalizeTransferStatus(?string $status): string
    {
        return strtolower(trim((string)$status));
    }

    public function saveStockRequest(StockRequestAddProduct $request, stockRequestService $service): JsonResponse|RedirectResponse
    {
      

        $dataArray = $service->getAddData(request: $request);
        // Save stock request
        $savedRequest = $this->stockRequestRepo->add(data: $dataArray);    

	    // Extract and process products
	    $products = $service->getAddRequestProducts($request->products, $savedRequest->id);
	    // Save products
	    foreach ($products as $product) {
	        $this->stockRequestRepo->stockRequestProduct($product);
	    }

        Toastr::success(translate('product_added_successfully'));
        return redirect()->route('admin.branch.stock.request');
    }
    
    
       

     public function received(Request|null $request, string $type = null): View
    {
        return $this->getStockTransferListView($request);
    }

    public function getStockTransferListView(Request $request): View|RedirectResponse
    {
        $aStockTransfers = $this->receivedTransfersQuery($request)
            ->paginate($this->resolveListPerPage($request))
            ->appends($request->query());

        return view('admin-views.branch-management.stock-movement.stock-received', compact('aStockTransfers'));
    }

    public function exportReceivedList(Request $request): StreamedResponse
    {
        $stockTransfers = $this->receivedTransfersQuery($request)->get();

        return response()->streamDownload(function () use ($stockTransfers) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                translate('To Branch'),
                translate('Transfer Date'),
                translate('Products'),
                translate('Category'),
                translate('Attribute'),
                translate('Qty'),
                translate('Status'),
            ]);

            foreach ($stockTransfers as $stockTransfer) {
                foreach ($stockTransfer->products as $product) {
                    fputcsv($handle, [
                        $stockTransfer->toBranch?->getTranslatedField('branch_name') ?? translate('not_available'),
                        $stockTransfer->transfer_date ? date('M d, Y', strtotime($stockTransfer->transfer_date)) : translate('not_available'),
                        $product->product?->getTranslatedField('name') ?? translate('not_available'),
                        $product->category?->getTranslatedField('name') ?? translate('not_available'),
                        $product->attribute ?: translate('not_available'),
                        (string) $product->quantity,
                        translate($product->status),
                    ]);
                }
            }

            fclose($handle);
        }, 'received-stock-transfer-list.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
        $products = $this->productRepo->getListWhere(filters: ['scope' => 'active'], dataLimit: 'all');
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
            'attributes'
        ));
    }

    private function receivedTransfersQuery(Request $request): Builder
    {
        $searchValue = $this->sanitizeListSearch($request->input('searchValue'));

        return StockTransfers::query()
            ->with([
                'products' => fn ($query) => $this->applyReceivedTransferProductFilters($query, $request)
                    ->with(['product.translations', 'category.translations', 'attribute']),
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

    private function applyReceivedTransferProductFilters($query, Request $request)
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

   

}
