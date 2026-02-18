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
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Models\StockRequestProduct;



class StockMovementController extends Controller
{


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
        $branchId = auth('admin')->user()->branch_id;

        $transfers = StockTransfers::with(['products.product', 'products.category', 'toBranch'])
            ->where('to_branch_id', $branchId)
            ->whereHas('products', function ($query) {
                $query->where('status', 'pending'); 
            })
            ->latest()
            ->get();

        return view('admin-views.branch-management.stock-movement.stock-approve', compact('transfers'));
    }

    public function approveProduct($id)
    {
        // Eager load the related stockTransfer and its toBranch
        $product = StockTransferProduct::with('stockTransfer.toBranch')->findOrFail($id);

        // Ensure stockTransfer exists before accessing to_branch_id
        if (!$product->stockTransfer) {
            return back()->with('error', 'Stock transfer not found.');
        }

        // Check if the branch manager owns this transfer
        if ($product->stockTransfer->toBranch->id != auth('admin')->user()->branch_id) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Update the stock transfer product status to approved
        $product->status = 'approved';
        $product->save();
            
        // Insert the data into StockReceived table
        StockReceived::create([
            'branch_id' => $product->stockTransfer->toBranch->id,
            'product_id' => $product->product_id, // Assuming product_id is part of StockTransferProduct model
            'quantity_received' => $product->quantity, // Assuming quantity is part of StockTransferProduct model
            'received_date' => now(), // Current date as received date
            'status' => 'approved', // Status as approved
            'approved_by' => auth('admin')->user()->name, // Assuming the admin's name is in the auth session
        ]);

        return back()->with('success', 'Stock approved and received successfully.');
    }

    public function rejectProduct($id)
    {
        // Eager load the related stockTransfer and its toBranch
        $product = StockTransferProduct::with('stockTransfer.toBranch')->findOrFail($id);

        // Ensure stockTransfer exists before accessing to_branch_id
        if (!$product->stockTransfer) {
            return back()->with('error', 'Stock transfer not found.');
        }

        // Check if the branch manager owns this transfer
        if ($product->stockTransfer->toBranch->id != auth('admin')->user()->branch_id) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Update the stock transfer product status to rejected
        $product->status = 'rejected';
        $product->save();

        // Insert the data into StockReceived table
        StockReceived::create([
            'branch_id' => $product->stockTransfer->toBranch->id,
            'product_id' => $product->product_id, // Assuming product_id is part of StockTransferProduct model
            'quantity_received' => $product->quantity, // Assuming quantity is part of StockTransferProduct model
            'received_date' => now(), // Current date as received date
            'status' => 'rejected', // Status as rejected
            'approved_by' => auth('admin')->user()->name, // Assuming the admin's name is in the auth session
        ]);

        return back()->with('success', 'Stock rejected and recorded successfully.');
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
        $aStockTransfers = StockTransfers::with([
            'products.product',
            'products.category',       // Load product and its category
            'products.attribute',
            'toBranch'
        ])
            ->when($request->restock_date, function ($query) use ($request) {
                return $query->whereDate('transfer_date', $request->restock_date); // Filter by transfer date
            })
            ->when($request->category_id, function ($query) use ($request) {
                return $query->whereHas('products.product', function ($query) use ($request) {
                    $query->where('category_id', $request->category_id); // Filter by category
                });
            })
            ->when($request->sub_category_id, function ($query) use ($request) {
                return $query->whereHas('products.product', function ($query) use ($request) {
                    $query->where('sub_category_id', $request->sub_category_id); // Filter by sub-category
                });
            })
            ->when($request->brand_id, function ($query) use ($request) {
                return $query->whereHas('products.product', function ($query) use ($request) {
                    $query->where('brand_id', $request->brand_id); // Filter by brand
                });
            })
            ->when($request->searchValue, function ($query) use ($request) {
                return $query->whereHas('products.product', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->searchValue . '%'); // Search by product name
                });
            })
            ->paginate(10); // Adjust pagination as needed
        // dd($aStockRequests);
        // Pass the data to the view
        return view('admin-views.branch-management.stock-movement.stock-received', compact('aStockTransfers'));
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

   

}
