<?php

namespace App\Http\Controllers\Admin\Branch;

use App\Models\Admin;
use App\Models\State;
use App\Domain\Stock\Support\VariantMatcher;
use App\Enums\WebConfigKey;
use App\Support\AdminPermissionRegistry;
use App\Traits\CommonTrait;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\ShopService;
use App\Traits\PaginatorTrait;
use App\Services\BranchService;
use App\Exports\BranchListExport;
use App\Exports\VendorListExport;
use Illuminate\Http\JsonResponse;
use App\Models\ShippingMethodArea;
use App\Traits\EmailTemplateTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use App\Enums\ViewPaths\Admin\Branch;
use App\Models\Branch as BranchModel;
use App\Traits\PushNotificationTrait;
use Illuminate\Http\RedirectResponse;
use App\Exports\VendorOrderListExport;
use App\Exports\VendorWithdrawRequest;
use App\Events\VendorRegistrationEvent;
use App\Models\ProductStockTransaction;
use App\Http\Controllers\BaseController;
use App\Models\ManageBranchProductStock;
use App\Events\WithdrawStatusUpdateEvent;
use App\Exports\BranchStockHistoryExport;
use App\Http\Requests\Admin\BranchAddRequest;
use App\Http\Requests\Admin\ManagerAddRequest;
use App\Http\Requests\Admin\BranchUpdateRequest;
use App\Http\Requests\Admin\ManagerUpdateRequest;
use App\Contracts\Repositories\ShopRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Enums\ExportFileNames\Admin\Branch as BranchExport;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\DeliveryAreaRepositoryInterface;
use App\Contracts\Repositories\VendorWalletRepositoryInterface;
use App\Contracts\Repositories\DeliveryZipCodeRepositoryInterface;
use App\Contracts\Repositories\ShippingAddressRepositoryInterface;
use App\Contracts\Repositories\WithdrawRequestRepositoryInterface;
use App\Contracts\Repositories\OrderTransactionRepositoryInterface;
use App\Contracts\Repositories\ShippingMethodAreaRepositoryInterface;
use App\Contracts\Repositories\StockClearanceSetupRepositoryInterface;
use App\Contracts\Repositories\StockClearanceProductRepositoryInterface;


class BranchController extends BaseController
{
    use PaginatorTrait;
    use CommonTrait;
    use PushNotificationTrait;
    use EmailTemplateTrait;

    public function __construct(
        private readonly VendorRepositoryInterface              $vendorRepo,
        private readonly BranchRepositoryInterface              $branchRepo,
        private readonly BranchService                          $branchService,
        private readonly DeliveryAreaRepositoryInterface        $deliveryAreaRepo,
        private readonly ShippingMethodAreaRepositoryInterface  $shippingMethodAreaRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,

    ) {}

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getListView($request);
    }

    public function getListView(Request $request): View
    {
        $current_date = date('Y-m-d');
        $branches = $this->branchRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['manager', 'translations'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );
        foreach ($branches as $branch) {
            $branch->shipping_method_areas = $branch->getShippingMethodsAreas();
            $branch->delivery_restrictions = $branch->getDeliveryRestriction();
        }

        return view(Branch::LIST[VIEW], compact('branches', 'current_date'));
    }


    public function getAddView(Request $request): View
    {
        $aShippingMethodArea = $this->shippingMethodAreaRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['status' => 1],
            dataLimit: 'all'
        );
        $aDeliveryRestriction = $this->deliveryAreaRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['status' => 1],
            dataLimit: 'all'
        );
        $aUniqueCities = ShippingMethodArea::with('city')
            ->get()
            ->unique('city_id')
            ->map(function ($item) {
                return [
                    'city_id' => $item->city->id,
                    'city_name' => $item->city->name,
                ];
            });

        $admins = $this->getActiveBranchManagers();

        $states = State::all();

        return view(Branch::ADD[VIEW], compact('aShippingMethodArea', 'aDeliveryRestriction', 'aUniqueCities', 'admins', 'states'));
    }

    public function assignManager(Request $request, $seller_id): View
    {
        $seller     = $this->branchRepo->getFirstWhere(params: ['id' => $seller_id]);
        $managers   = $this->branchRepo->getManager(params: ['branch_id' => $seller_id]);
        $AdminData   = $this->branchRepo->getAdminData(params: ['branch_id' => $seller_id]);
        return view(Branch::ASSIGN_MANAGER[VIEW], compact('seller', 'managers', 'AdminData'));
    }

    public function addManager(ManagerAddRequest $request, $seller_id): JsonResponse
    {
        $this->branchRepo->addManager(data: $this->branchService->getAddManager($request));
        $admin = $this->branchRepo->addToAdmin(data: $this->branchService->getAddDataToLogin($request));
        if ($admin instanceof Admin) {
            $admin->syncRoles([AdminPermissionRegistry::branchManagerRole()]);
        }
        return response()->json(['message' => translate('Manager_added_successfully')]);
    }
    public function updateManager(ManagerUpdateRequest $request, $seller_id): JsonResponse
    {
        $this->branchRepo->updateManager(id: $request['branch_id'], data: $this->branchService->getUpdateManager($request));
        /* $this->branchRepo->addToAdmin(data: $this->branchService->getAddDataToLogin($request));*/
        return response()->json(['message' => translate('Manager_updated_successfully')]);
    }

    public function add(BranchAddRequest $request, $seller_id = 0): JsonResponse
    {

        $branch = $this->branchRepo->add(data: $this->branchService->getAddData($request));
        $id = $branch->id;
        $this->translationRepo->add(
            request: $request,
            model: BranchModel::class,
            id: $id
        );
        return response()->json(['message' => translate('Branch_added_successfully')]);
    }

    public function updateStatus(Request $request): RedirectResponse
    {
        $vendor = $this->branchRepo->getFirstWhere(params: ['id' => $request['id']]);
        $this->branchRepo->update(id: $request['id'], data: ['status' => $request['status']]);
        if ($request['status'] == "active") {
            Toastr::success(translate('branch_has_been_approved_successfully'));
        } else if ($request['status'] == "inactive") {
            Toastr::info(translate('branch_has_been_rejected_successfully'));
        } else if ($request['status'] == "suspended") {
            $this->branchRepo->update(id: $request['id'], data: ['auth_token' => Str::random(80)]);
            Toastr::info(translate('branch_has_been_suspended_successfully'));
        }

        return back();
    }

    // public function exportList(Request $request): BinaryFileResponse
    // {

    //     // --- NEW: Single Product History Export Logic ---
    //     if ($request->has('product_id')) {
    //         $productId = $request->product_id;
    //         $branchId = $request->branch_id;
    //         $variationType = $request->variation_type; // Pass this from JS
    //         $variantMatcher = app(VariantMatcher::class);

    //         // Replicate the logic from fGetBranchesStockList
    //         $history = \App\Models\StockRequestProduct::where('product_id', $productId)
    //             ->whereIn('status', ['transferred', 'pending', 'approved'])
    //             ->where(function ($q) use ($branchId) {
    //                 $q->where('received_from_branch', $branchId)
    //                     ->orWhereHas('stockRequest', function ($sr) use ($branchId) {
    //                         $sr->where('from_branch_id', $branchId);
    //                     });
    //             })
    //             ->with('stockRequest')
    //             ->latest()
    //             ->get()
    //             ->filter(function ($row) use ($variationType, $variantMatcher) {
    //                 if ($variationType === 'No Variation' || empty($variationType) || $variationType === 'null') {
    //                     return $variantMatcher->isDefault($row->variation_type);
    //                 }

    //                 return $variantMatcher->matches($variationType, $row->variation_type);
    //             })
    //             ->values();

    //         return Excel::download(new \App\Exports\BranchStockHistoryExport(['history' => $history]), 'stock-history.xlsx');
    //     }

    //     // changes end for single product 


    //     $vendors = $this->branchRepo->getListWhere(
    //         orderBy: ['id' => 'desc'],
    //         searchValue: $request['searchValue'],
    //         relations: [],
    //         dataLimit: 'all'
    //     );

    //     $active = $vendors->where('status', 'active')->count();
    //     $inactive = $vendors->where('status', '!=', 'active')->count();
    //     $data = [
    //         'vendors' => $vendors,
    //         'search' => $request['searchValue'],
    //         'active' => $active,
    //         'inactive' => $inactive,
    //     ];
    //     return Excel::download(new BranchListExport($data), BranchExport::EXPORT_XLSX);
    // }
    public function exportList(Request $request): BinaryFileResponse
    {
        // --- CASE 1: Single Product Stock History Export ---
        if ($request->has('product_id') && $request->has('branch_id')) {

            // Prepare parameters for the unified history helper
            $stock = new \stdClass();
            $stock->branch_id = $request->branch_id;
            $stock->product_id = $request->product_id;
            $stock->variation_type = $request->variation_type;
            $stock->variation_key = $request->variation_key;

            // Fetch the unified history (same logic as the web view)
            $history = $this->getUnifiedStockHistory($stock);

            return Excel::download(
                new \App\Exports\BranchStockHistoryExport(['history' => $history]),
                'stock-history-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        // --- CASE 2: General Branch List Export ---
        $vendors = $this->branchRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: [],
            dataLimit: 'all'
        );

        $data = [
            'vendors' => $vendors,
            'search' => $request['searchValue'],
            'active' => $vendors->where('status', 'active')->count(),
            'inactive' => $vendors->where('status', '!=', 'active')->count(),
        ];

        return Excel::download(new \App\Exports\BranchListExport($data), 'Branch-List.xlsx');
    }

    public function getView(Request $request, $id, $tab = null): View|RedirectResponse
    {
        $seller = $this->branchRepo->getFirstWhere(
            params: ['id' => $id],
            relations: []
        );

        if (!$seller) {
            return redirect()->route('admin.branch.branch-list');
        }


        if (!isset($seller)) {
            Toastr::error(translate('vendor_not_found_It_may_be_deleted'));
            return back();
        }

        if ($tab == 'order') {
            return $this->getOrderListTabView(request: $request, seller: $seller);
        } else if ($tab == 'product') {
            return $this->getProductListTabView(request: $request, seller: $seller);
        } else if ($tab == 'setting') {
            return $this->getSettingListTabView(request: $request, seller: $seller, id: $id);
        } else if ($tab == 'transaction') {
            return $this->getTransactionListTabView(request: $request, seller: $seller);
        } else if ($tab == 'review') {
            return $this->getReviewListTabView(request: $request, seller: $seller);
        } else if ($tab == 'clearance_sale') {
            return $this->getClearanceSaleTabView(request: $request, seller: $seller);
        }

        return view(Branch::VIEW[VIEW], [
            'seller' => $seller,
            'current_date' => date('Y-m-d'),
        ]);
    }

    //update branch
    public function getUpdateView($id): View
    {
        $aShippingMethodArea = $this->shippingMethodAreaRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['status' => 1],
            dataLimit: 'all'
        );
        $aDeliveryRestriction = $this->deliveryAreaRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            filters: ['status' => 1],
            dataLimit: 'all'
        );
        $aBranchDetails = $this->branchRepo->getFirstWhere(
            params: ['id' => $id],
            relations: ['translations']
        );

        $shipping_methods_area = isset($aBranchDetails['shipping_methods_area'])
            ? explode(',', $aBranchDetails['shipping_methods_area'])
            : [];
        $delivery_restriction = isset($aBranchDetails['delivery_restriction'])
            ? explode(',', $aBranchDetails['delivery_restriction'])
            : [];
        $aUniqueCities = ShippingMethodArea::with('city')
            ->get()
            ->unique('city_id')
            ->map(function ($item) {
                return [
                    'city_id' => $item->city->id,
                    'city_name' => $item->city->name,
                ];
            });
        $states = State::all();

        $admins = $this->getActiveBranchManagers();

        return view(Branch::UPDATE[VIEW], compact('aBranchDetails', 'aShippingMethodArea', 'aDeliveryRestriction', 'shipping_methods_area', 'delivery_restriction', 'aUniqueCities', 'admins', 'states'));
    }

    public function update(BranchUpdateRequest $request, BranchService $branchService): JsonResponse
    {
        $aBranchDetails = $this->branchRepo->getFirstWhere(params: ['id' => $request['id']]);
        if (!$aBranchDetails) {
            return response()->json(['message' => translate('Branch not found')]);
        }
        $this->branchRepo->update(id: $request['id'], data: $this->branchService->getAddData($request));
        $id = $request['id'];
        $this->translationRepo->update(
            request: $request,
            model: BranchModel::class,
            id: $id
        );

        return response()->json(['message' => translate('Branch_updated_successfully')]);
    }

    public function fGetCitiesArea(Request $request): JsonResponse
    {
        $success = 1;
        $iCityId = $request->input('iCityId');
        $aCitiesArea = ShippingMethodArea::where('city_id', $iCityId)
            ->select('id', 'area as name')
            ->get();

        return response()->json([
            'success' => $success,
            'data' => $aCitiesArea
        ], 200);
    }

    //  public function fGetBranchesStockList(Request $request): view
    // {
    //     $searchValue = $request->input('searchValue', '');
    //     $branchFilter = $request->input('branch_id', '');
    //     $productFilter = $request->input('product_id', '');
    //     $attributeFilter = $request->input('attribute', '');

    //     // First, debug: Check if ManageBranchProductStock has data
    //     \Log::info('ManageBranchProductStock count: ' . \App\Models\ManageBranchProductStock::count());

    //     $branches = ManageBranchProductStock::with(['branch', 'product'])
    //         ->select(
    //             'branch_id',
    //             'product_id',
    //             DB::raw("COALESCE(NULLIF(variation_key, ''), 'No Variation') as variation_key"),
    //             DB::raw("COALESCE(NULLIF(variation_type, ''), 'No Variation') as variation_type"),
    //             DB::raw('SUM(current_stock) as total_stock')
    //         )
    //         ->whereHas('product', fn($q) => $q->where('product_type', 'physical'))
    //         ->when($branchFilter, fn($q) => $q->where('branch_id', $branchFilter))
    //         ->when($productFilter, fn($q) => $q->where('product_id', $productFilter))
    //         ->when(
    //             $attributeFilter,
    //             fn($q) =>
    //             $q->where(function ($qq) use ($attributeFilter) {
    //                 $qq->where('variation_key', 'LIKE', "%$attributeFilter%")
    //                     ->orWhere('variation_type', 'LIKE', "%$attributeFilter%");
    //             })
    //         )
    //         ->groupBy(
    //             'branch_id',
    //             'product_id',
    //             DB::raw("COALESCE(NULLIF(variation_key, ''), 'No Variation')"),
    //             DB::raw("COALESCE(NULLIF(variation_type, ''), 'No Variation')")
    //         )
    //         ->paginate(10);

    //     // Debug: Log what we're getting
    //     \Log::info('Branches stock query result count: ' . $branches->count());
    //     \Log::info('First stock item:', $branches->first() ? $branches->first()->toArray() : []);

    //     $branches->getCollection()->transform(function ($stock) {
    //         \Log::info('Processing stock item:', [
    //             'branch_id' => $stock->branch_id,
    //             'product_id' => $stock->product_id,
    //             'variation_type' => $stock->variation_type,
    //             'variation_key' => $stock->variation_key
    //         ]);

    //         // Fix variation matching logic
    //         $transferLogs = \App\Models\StockRequestProduct::where('product_id', $stock->product_id)
    //             ->where(function ($q) use ($stock) {
    //                 // Handle variation matching more flexibly
    //                 if ($stock->variation_type === 'No Variation' || empty($stock->variation_type)) {
    //                     // Match NULL or empty variation_type
    //                     $q->where(function ($qq) {
    //                         $qq->whereNull('variation_type')
    //                             ->orWhere('variation_type', '')
    //                             ->orWhere('variation_type', 'No Variation');
    //                     });
    //                 } else {
    //                     // Match by variation_type (case-insensitive partial match)
    //                     $q->where(function ($qq) use ($stock) {
    //                         $qq->where('variation_type', 'like', '%' . $stock->variation_type . '%')
    //                             ->orWhere('variation_type', $stock->variation_type);
    //                     });
    //                 }
    //             })
    //             // Include all statuses to see all transfers
    //             ->whereIn('status', ['transferred', 'pending', 'approved'])
    //             ->where(function ($q) use ($stock) {
    //                 $q->where('received_from_branch', $stock->branch_id)
    //                     ->orWhereHas('stockRequest', function ($sr) use ($stock) {
    //                         $sr->where('from_branch_id', $stock->branch_id);
    //                     });
    //             })
    //             ->with('stockRequest')
    //             ->latest()
    //             ->get();

    //         \Log::info('Found transfer logs:', [
    //             'count' => $transferLogs->count(),
    //             'product_id' => $stock->product_id,
    //             'logs' => $transferLogs->pluck('id')->toArray()
    //         ]);

    //         $stock->transfer_logs = $transferLogs;
    //         return $stock;
    //     });

    //     $branchList = BranchModel::pluck('branch_name', 'id');
    //     $productList = \App\Models\Product::where('product_type', 'physical')
    //         ->pluck('name', 'id');

    //     return view(Branch::BRANCH_STOCK_LIST[VIEW], compact('branches', 'branchList', 'productList'));
    // }


    public function fGetBranchesStockList(Request $request): view
    {
        $searchValue = $request->input('searchValue', '');
        $branchFilter = $request->input('branch_id', '');
        $productFilter = $request->input('product_id', '');
        $attributeFilter = $request->input('attribute', '');
        $branches = ManageBranchProductStock::with(['branch', 'product'])
            ->select(
                'branch_id',
                'product_id',
                DB::raw("COALESCE(NULLIF(variation_key, ''), 'No Variation') as variation_key"),
                DB::raw("COALESCE(NULLIF(variation_type, ''), 'No Variation') as variation_type"),
                DB::raw('SUM(current_stock) as total_stock')
            )
            ->whereHas('product', fn($q) => $q->where('product_type', 'physical'))
            ->when($branchFilter, fn($q) => $q->where('branch_id', $branchFilter))
            ->when($productFilter, fn($q) => $q->where('product_id', $productFilter))
            ->when(
                $attributeFilter,
                fn($q) =>
                $q->where(function ($qq) use ($attributeFilter) {
                    $qq->where('variation_key', 'LIKE', "%$attributeFilter%")
                        ->orWhere('variation_type', 'LIKE', "%$attributeFilter%");
                })
            )
            ->groupBy(
                'branch_id',
                'product_id',
                DB::raw("COALESCE(NULLIF(variation_key, ''), 'No Variation')"),
                DB::raw("COALESCE(NULLIF(variation_type, ''), 'No Variation')")
            )
            ->paginate(10);



        // Transform each stock row to attach unified transfer logs
        $branches->getCollection()->transform(function ($stock) {


            // Attach combined stock request + transfer logs
            $stock->transfer_logs = $this->getUnifiedStockHistory($stock);

            return $stock;
        });

        // Collect branch and product lists **outside** the transform closure
        $branchList = BranchModel::pluck('branch_name', 'id');
        $productList = \App\Models\Product::where('product_type', 'physical')->pluck('name', 'id');

        return view(
            Branch::BRANCH_STOCK_LIST[VIEW],
            compact('branches', 'branchList', 'productList')
        );
    }

    public function deleteBranch($id)
    {
        $branch = $this->branchRepo->getFirstWhere(params: ['id' => $id]);

        if ($branch) {
            $this->branchRepo->delete(['id' => $id]);
            $this->translationRepo->delete(
                model: BranchModel::class,
                id: $id
            );
            Toastr::success(translate('Branch_deleted_successfully'));
        } else {
            Toastr::error(translate('Branch_not_found'));
        }

        return back();
    }

    private function getUnifiedStockHistory($stock)
    {
        /* -----------------------------
     * 1️⃣ PRODUCT STOCK TRANSACTION HISTORY
     * ----------------------------- */
        $transactionLogs = ProductStockTransaction::with(['fromBranch', 'toBranch'])
            ->whereIn('product_stock_id', function ($q) use ($stock) {
                $variantMatcher = app(VariantMatcher::class);
                $matchingStockIds = \App\Models\ProductStock::query()
                    ->where('product_id', $stock->product_id)
                    ->get()
                    ->filter(function ($productStock) use ($stock, $variantMatcher) {
                        if ($variantMatcher->isDefault($stock->variation_key) || $variantMatcher->isDefault($stock->variation_type)) {
                            return $variantMatcher->isDefault($productStock->variant);
                        }

                        return $variantMatcher->matches($productStock->variant, $stock->variation_key)
                            || $variantMatcher->matches($productStock->variant, $stock->variation_type);
                    })
                    ->pluck('id')
                    ->all();

                $q->select('id')
                    ->from('product_stocks')
                    ->whereIn('id', $matchingStockIds);
            })
            /* Filter to show only logs for the specific branch being viewed */
            ->where(function ($q) use ($stock) {
                $q->where('to_branch_id', $stock->branch_id)
                    ->orWhere('from_branch_id', $stock->branch_id)
                    ->orWhere(function ($qq) {
                        $qq->whereNull('to_branch_id')->whereNull('from_branch_id');
                    });
            })
            ->get()
            ->map(function ($item) {
                $reasonClean = str_replace('_', ' ', $item->reason ?? 'MANUAL');
                $reference = $reasonClean;

                // Default names from relations (if they exist)
                $fromName = $item->fromBranch?->branch_name;
                $toName   = $item->toBranch?->branch_name;

                if ($item->reason === 'BRANCH_TRANSFER') {
                    if ($item->type === 'IN') {
                        /* * Logic for RECEIVED (IN): from_branch_id is NULL.
             * Extract "1" from "Received from branch 1"
             */
                        if (!$fromName && !empty($item->remarks)) {
                            preg_match('/\d+/', $item->remarks, $matches);
                            $extractedId = $matches[0] ?? null;
                            if ($extractedId) {
                                $fromName = \App\Models\Branch::withTrashed()->find($extractedId)?->branch_name
                                    ?? "Branch #" . $extractedId;
                            }
                        }
                        $reference .= " (From: " . ($fromName ?? 'Unknown') . ")";
                    } else {
                        /* * Logic for TRANSFERRED (OUT): to_branch_id is NULL.
             * Extract "11" from "Transferred to branch 11"
             */
                        if (!$toName && !empty($item->remarks)) {
                            preg_match('/\d+/', $item->remarks, $matches);
                            $extractedId = $matches[0] ?? null;
                            if ($extractedId) {
                                $toName = \App\Models\Branch::withTrashed()->find($extractedId)?->branch_name
                                    ?? "Branch #" . $extractedId;
                            }
                        }
                        $reference .= " (To: " . ($toName ?? 'Unknown') . ")";
                    }
                }

                return [
                    'source'         => 'transaction',
                    'type'           => $item->type,
                    'reference'      => $reference,
                    'quantity'       => $item->quantity,
                    'status'         => 'completed',
                    'created_at'     => $item->created_at,
                    'remarks'        => $item->remarks,
                    'from_branch'    => $fromName ?? 'N/A',
                    'to_branch'      => $toName ?? 'N/A',
                ];
            });
        return collect()
            ->merge($transactionLogs)
            ->sortByDesc('created_at')
            ->values();
    }



    // public function fGetBranchesStockHistory(Request $request, $branch_id, $product_id)
    // {
    //     // 1. Fetch Basic Info
    //     $branch = BranchModel::findOrFail($branch_id);
    //     $product = \App\Models\Product::findOrFail($product_id);

    //     // 2. Prepare the $stock object so your existing helper function can read it
    //     $stock = new \stdClass();
    //     $stock->branch_id = $branch_id;
    //     $stock->product_id = $product_id;
    //     $stock->variation_type = $request->query('variation_type');
    //     $stock->variation_key = $request->query('variation_key');

    //     // 3. REUSE YOUR EXISTING LOGIC
    //     // This is where the history "comes from" in your current modal
    //     $logs = $this->getUnifiedStockHistory($stock);

    //     // 4. Return the new dedicated page
    //     return view('admin-views.branch.stock-history', compact('branch', 'product', 'logs', 'stock'));
    // }

    public function fGetBranchesStockHistory(Request $request, $branch_id, $product_id)
    {
        // 1. Fetch Basic Info
        $branch = BranchModel::findOrFail($branch_id);
        $product = \App\Models\Product::findOrFail($product_id);

        // 2. Prepare the $stock object (Handle 'No Variation' as null/empty)
        $stock = new \stdClass();
        $stock->branch_id = $branch_id;
        $stock->product_id = $product_id;
        $stock->variation_type = ($request->variation_type === 'No Variation') ? null : $request->variation_type;
        $stock->variation_key = ($request->variation_key === 'No Variation') ? null : $request->variation_key;

        // 3. Get History (Reusing your existing getUnifiedStockHistory logic)
        $logs = $this->getUnifiedStockHistory($stock);

        // 4. Calculate Current Stock with Null/Empty Check
        $query = \App\Models\ManageBranchProductStock::where([
            'branch_id' => $branch_id,
            'product_id' => $product_id,
        ]);

        // Handle Variation Type NULL or Empty
        if (empty($stock->variation_type)) {
            $query->where(function ($q) {
                $q->whereNull('variation_type')->orWhere('variation_type', '');
            });
        } else {
            $query->where('variation_type', $stock->variation_type);
        }

        // Handle Variation Key NULL or Empty
        if (empty($stock->variation_key)) {
            $query->where(function ($q) {
                $q->whereNull('variation_key')->orWhere('variation_key', '');
            });
        } else {
            $query->where('variation_key', $stock->variation_key);
        }

        $current_stock = $query->sum('current_stock');

        return view('admin-views.branch.stock-history', compact('branch', 'product', 'logs', 'stock', 'current_stock'));
    }
}
