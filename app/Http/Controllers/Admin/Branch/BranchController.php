<?php

namespace App\Http\Controllers\Admin\Branch;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\State;
use App\Exports\FormattedTableExport;
use App\Domain\Stock\Support\VariantMatcher;
use App\Enums\WebConfigKey;
use App\Support\AdminPermissionRegistry;
use App\Traits\CommonTrait;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\ShopService;
use App\Traits\PaginatorTrait;
use App\Services\BranchService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
use App\Support\LocalizedExport;
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
        $perPage = $this->resolveListPerPage($request);
        $searchValue = $this->sanitizeSearchTerm($request['searchValue']);
        $branches = $this->branchRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $searchValue,
            relations: ['manager', 'translations', 'shippingAreas', 'deliveryRestrictions'],
            dataLimit: $perPage
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
        $this->branchService->syncAreaRelations($branch, $request);
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

    public function exportList(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|BinaryFileResponse|RedirectResponse
    {

        // --- NEW: Single Product History Export Logic ---
        if ($request->has('product_id')) {
            $authUser = auth('admin')->user();
            $productId = $request->product_id;
            $branchId = (int)$request->branch_id;
            $variationType = $request->variation_type; // Pass this from JS
            $variantMatcher = app(VariantMatcher::class);

            if ($branchId <= 0) {
                Toastr::error(translate('you_are_not_authorized_to_export_this_branch_data'));

                return redirect()->route('admin.branch.branch-stock-list');
            }

            if (!$this->canAccessBranchData($authUser, $branchId)) {
                Toastr::error(translate('you_are_not_authorized_to_export_this_branch_data'));

                return redirect()->route('admin.branch.branch-stock-list');
            }

            // Replicate the logic from fGetBranchesStockList
            $history = \App\Models\StockRequestProduct::where('product_id', $productId)
                ->whereIn('status', ['transferred', 'pending', 'approved'])
                ->where(function ($q) use ($branchId) {
                    $q->where('received_from_branch', $branchId)
                        ->orWhereHas('stockRequest', function ($sr) use ($branchId) {
                            $sr->where('from_branch_id', $branchId);
                        });
                })
                ->with('stockRequest')
                ->latest()
                ->get()
                ->filter(function ($row) use ($variationType, $variantMatcher) {
                    if ($variationType === 'No Variation' || empty($variationType) || $variationType === 'null') {
                        return $variantMatcher->isDefault($row->variation_type);
                    }

                    return $variantMatcher->matches($variationType, $row->variation_type);
                })
                ->values();

            return Excel::download(new \App\Exports\BranchStockHistoryExport(['history' => $history]), 'stock-history.xlsx');
        }

        if ($request->input('export_scope') === 'branch_stock') {
            return $this->exportBranchStockList($request);
        }

        // changes end for single product 


        $vendors = $this->branchRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $this->sanitizeSearchTerm($request['searchValue']),
            relations: [],
            dataLimit: 'all'
        );

        $active = $vendors->where('status', 'active')->count();
        $inactive = $vendors->where('status', '!=', 'active')->count();
        $data = [
            'vendors' => $vendors,
            'search' => $request['searchValue'],
            'active' => $active,
            'inactive' => $inactive,
        ];
        return Excel::download(new BranchListExport($data), BranchExport::EXPORT_XLSX);
    }

    public function getView(Request $request, $id, $tab = null): View|RedirectResponse
    {
        $seller = $this->branchRepo->getFirstWhere(
            params: ['id' => $id],
            relations: ['translations']
        );

        if (!$seller) {
            return redirect()->route('admin.branch.branch-list');
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
            relations: ['manager', 'translations', 'shippingAreas', 'deliveryRestrictions']
        );

        $shipping_methods_area = $aBranchDetails?->shippingAreas?->pluck('id')->map(fn ($id) => (string)$id)->all() ?? [];
        $delivery_restriction = $aBranchDetails?->deliveryRestrictions?->pluck('id')->map(fn ($id) => (string)$id)->all() ?? [];
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

        $admins = $this->getActiveBranchManagers($aBranchDetails);

        return view(Branch::UPDATE[VIEW], compact('aBranchDetails', 'aShippingMethodArea', 'aDeliveryRestriction', 'shipping_methods_area', 'delivery_restriction', 'aUniqueCities', 'admins', 'states'));
    }

    public function update(BranchUpdateRequest $request, BranchService $branchService): JsonResponse
    {
        $aBranchDetails = $this->branchRepo->getFirstWhere(params: ['id' => $request['id']]);
        if (!$aBranchDetails) {
            return response()->json(['message' => translate('Branch not found')]);
        }
        $this->branchRepo->update(id: $request['id'], data: $this->branchService->getAddData($request));
        $this->branchService->syncAreaRelations($aBranchDetails, $request);
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
            ->get()
            ->map(fn($item) => ['id' => $item->id, 'name' => $item->area]);

        return response()->json([
            'success' => $success,
            'data' => $aCitiesArea
        ], 200);
    }

    public function fGetBranchesStockList(Request $request): view
    {
        $branches = $this->branchStockListQuery($request)
            ->paginate($this->resolveListPerPage($request))
            ->appends($request->query());

        $branchList = BranchModel::query()
            ->with('translations')
            ->orderBy('branch_name')
            ->get()
            ->mapWithKeys(fn(BranchModel $branch) => [$branch->id => $branch->branch_name]);

        $productList = \App\Models\Product::query()
            ->with('translations')
            ->where('product_type', 'physical')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn(\App\Models\Product $product) => [$product->id => $product->name]);

        return view(
            Branch::BRANCH_STOCK_LIST[VIEW],
            compact('branches', 'branchList', 'productList')
        );
    }

    public function getStockHistory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'product_id' => 'required|integer|exists:products,id',
            'variation_type' => 'nullable|string|max:255',
            'variation_key' => 'nullable|string|max:255',
        ]);

        $authUser = auth('admin')->user();
        $branchId = (int) $validated['branch_id'];
        $productId = (int) $validated['product_id'];
        $variationType = $validated['variation_type'] ?? 'No Variation';
        $variationKey = $validated['variation_key'] ?? 'No Variation';

        if (!$this->canAccessBranchData($authUser, $branchId)) {
            return response()->json([
                'message' => translate('you_are_not_authorized_to_export_this_branch_data'),
            ], 403);
        }

        $stock = $this->findBranchStockSummary($branchId, $productId, $variationType, $variationKey);

        if (!$stock) {
            return response()->json([
                'message' => translate('No transfer history found'),
            ], 404);
        }

        return response()->json([
            'branch_name' => $stock->branch?->getTranslatedField('branch_name') ?? translate('not_available'),
            'product_name' => $stock->product?->getTranslatedField('name') ?? translate('not_available'),
            'variation_label' => $this->formatVariationLabel($stock->variation_type, $stock->variation_key),
            'current_stock' => (int) $stock->total_stock,
            'history' => $this->getUnifiedStockHistory($stock)
                ->map(fn(array $item) => $this->formatStockHistoryItem($item))
                ->values(),
            'export_url' => route('admin.branch.export', [
                'product_id' => $stock->product_id,
                'branch_id' => $stock->branch_id,
                'variation_type' => $stock->variation_type,
            ]),
        ]);
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

    private function attachUnifiedTransferLogs(Collection $stocks): void
    {
        if ($stocks->isEmpty()) {
            return;
        }

        $variantMatcher = app(VariantMatcher::class);
        $productIds = $stocks->pluck('product_id')->filter()->unique()->values();
        $branchIds = $stocks->pluck('branch_id')->filter()->unique()->values();
        $productStocksByProduct = \App\Models\ProductStock::query()
            ->whereIn('product_id', $productIds)
            ->get()
            ->groupBy('product_id');

        $transactionIds = $productStocksByProduct
            ->flatten(1)
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        if ($transactionIds->isEmpty()) {
            $stocks->transform(function ($stock) {
                $stock->transfer_logs = collect();

                return $stock;
            });

            return;
        }

        $transactionsByStockId = ProductStockTransaction::with(['fromBranch', 'toBranch'])
            ->whereIn('product_stock_id', $transactionIds)
            ->where(function ($query) use ($branchIds) {
                $query->whereIn('to_branch_id', $branchIds)
                    ->orWhereIn('from_branch_id', $branchIds)
                    ->orWhere(function ($innerQuery) {
                        $innerQuery->whereNull('to_branch_id')->whereNull('from_branch_id');
                    });
            })
            ->get()
            ->groupBy('product_stock_id');

        $stocks->transform(function ($stock) use ($productStocksByProduct, $transactionsByStockId, $variantMatcher) {
            $productStocks = $productStocksByProduct->get($stock->product_id, collect());

            $matchingStockIds = $productStocks
                ->filter(function ($productStock) use ($stock, $variantMatcher) {
                    if ($variantMatcher->isDefault($stock->variation_key) || $variantMatcher->isDefault($stock->variation_type)) {
                        return $variantMatcher->isDefault($productStock->variant);
                    }

                    return $variantMatcher->matches($productStock->variant, $stock->variation_key)
                        || $variantMatcher->matches($productStock->variant, $stock->variation_type);
                })
                ->pluck('id');

            $stock->transfer_logs = $matchingStockIds
                ->flatMap(fn($stockId) => $transactionsByStockId->get($stockId, collect()))
                ->filter(function ($item) use ($stock) {
                    return (int)$item->to_branch_id === (int)$stock->branch_id
                        || (int)$item->from_branch_id === (int)$stock->branch_id
                        || (is_null($item->to_branch_id) && is_null($item->from_branch_id));
                })
                ->unique('id')
                ->sortByDesc('created_at')
                ->values()
                ->map(fn($item) => $this->mapUnifiedStockTransaction($item))
                ->values();

            return $stock;
        });
    }

    private function getUnifiedStockHistory($stock)
    {
        $transactionLogs = ProductStockTransaction::with(['fromBranch.translations', 'toBranch.translations'])
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
            ->where(function ($q) use ($stock) {
                $q->where('to_branch_id', $stock->branch_id)
                    ->orWhere('from_branch_id', $stock->branch_id)
                    ->orWhere(function ($qq) {
                        $qq->whereNull('to_branch_id')->whereNull('from_branch_id');
                    });
            })
            ->get()
            ->map(fn($item) => $this->mapUnifiedStockTransaction($item));

        return collect()
            ->merge($transactionLogs)
            ->sortByDesc('created_at')
            ->values();
    }

    private function mapUnifiedStockTransaction(ProductStockTransaction $item): array
    {
        $reasonClean = str_replace('_', ' ', $item->reason ?? 'MANUAL');
        $reference = $reasonClean;

        $fromName = $item->fromBranch?->getTranslatedField('branch_name') ?? $item->fromBranch?->branch_name;
        $toName = $item->toBranch?->getTranslatedField('branch_name') ?? $item->toBranch?->branch_name;

        if ($item->reason === 'BRANCH_TRANSFER') {
            if ($item->type === 'IN') {
                if (!$fromName && !empty($item->remarks)) {
                    preg_match('/\d+/', $item->remarks, $matches);
                    $extractedId = $matches[0] ?? null;
                    if ($extractedId) {
                        $resolvedBranch = \App\Models\Branch::withTrashed()->find($extractedId);
                        $fromName = $resolvedBranch?->getTranslatedField('branch_name')
                            ?? $resolvedBranch?->branch_name
                            ?? 'Branch #' . $extractedId;
                    }
                }
                $reference .= ' (' . translate('From') . ': ' . ($fromName ?? translate('not_available')) . ')';
            } else {
                if (!$toName && !empty($item->remarks)) {
                    preg_match('/\d+/', $item->remarks, $matches);
                    $extractedId = $matches[0] ?? null;
                    if ($extractedId) {
                        $resolvedBranch = \App\Models\Branch::withTrashed()->find($extractedId);
                        $toName = $resolvedBranch?->getTranslatedField('branch_name')
                            ?? $resolvedBranch?->branch_name
                            ?? 'Branch #' . $extractedId;
                    }
                }
                $reference .= ' (' . translate('To') . ': ' . ($toName ?? translate('not_available')) . ')';
            }
        }

        return [
            'source' => 'transaction',
            'type' => $item->type,
            'reference' => $reference,
            'quantity' => $item->quantity,
            'status' => 'completed',
            'created_at' => $item->created_at,
            'remarks' => $item->remarks,
            'from_branch' => $fromName ?? translate('not_available'),
            'to_branch' => $toName ?? translate('not_available'),
        ];
    }

    private function findBranchStockSummary(int $branchId, int $productId, string $variationType, string $variationKey): ?ManageBranchProductStock
    {
        $normalizedVariationType = $variationType !== '' ? $variationType : 'No Variation';
        $normalizedVariationKey = $variationKey !== '' ? $variationKey : 'No Variation';

        return ManageBranchProductStock::query()
            ->with(['branch.translations', 'product.translations'])
            ->select(
                'branch_id',
                'product_id',
                DB::raw("COALESCE(NULLIF(variation_key, ''), 'No Variation') as variation_key"),
                DB::raw("COALESCE(NULLIF(variation_type, ''), 'No Variation') as variation_type"),
                DB::raw('SUM(current_stock) as total_stock')
            )
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->whereRaw("COALESCE(NULLIF(variation_type, ''), 'No Variation') = ?", [$normalizedVariationType])
            ->whereRaw("COALESCE(NULLIF(variation_key, ''), 'No Variation') = ?", [$normalizedVariationKey])
            ->groupBy(
                'branch_id',
                'product_id',
                DB::raw("COALESCE(NULLIF(variation_key, ''), 'No Variation')"),
                DB::raw("COALESCE(NULLIF(variation_type, ''), 'No Variation')")
            )
            ->first();
    }

    private function formatVariationLabel(?string $variationType, ?string $variationKey): string
    {
        if (blank($variationType) || $variationType === 'No Variation') {
            return translate('Default');
        }

        $label = $variationType;
        if (!blank($variationKey) && $variationKey !== 'No Variation') {
            $label .= ' (' . Str::replace('|', ' • ', Str::replace(':', ' : ', $variationKey)) . ')';
        }

        return $label;
    }

    private function formatStockHistoryItem(array $item): array
    {
        $isStockIn = strtoupper((string) ($item['type'] ?? '')) === 'IN';

        return [
            'date' => Carbon::parse($item['created_at'])->translatedFormat('d M Y, h:i A'),
            'type_label' => $isStockIn ? translate('Stock In') : translate('Stock Out'),
            'type_class' => $isStockIn ? 'text-success' : 'text-danger',
            'quantity_label' => ($isStockIn ? '+' : '-') . ' ' . (int) ($item['quantity'] ?? 0),
            'reference' => $item['reference'] ?? translate('not_available'),
            'description' => $this->resolveStockHistoryDescription($item),
            'status_label' => translate($item['status'] ?? 'completed'),
            'status_class' => 'badge badge-success',
        ];
    }

    private function resolveStockHistoryDescription(array $item): string
    {
        if (str_starts_with((string) ($item['reference'] ?? ''), 'BRANCH TRANSFER')) {
            return strtoupper((string) ($item['type'] ?? '')) === 'IN'
                ? translate('Received from') . ' ' . ($item['from_branch'] ?? translate('not_available'))
                : translate('Sent to') . ' ' . ($item['to_branch'] ?? translate('not_available'));
        }

        return $item['remarks'] ?: ($item['reference'] ?? translate('not_available'));
    }

    private function canAccessBranchData(?Admin $authUser, int $branchId): bool
    {
        if (!$authUser) {
            return false;
        }

        if ($authUser->isSuperAdmin()) {
            return true;
        }

        return (int)($authUser->branch_id ?? 0) === $branchId;
    }

    private function escapeLikeValue(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function resolveListPerPage(Request $request): int
    {
        if ($request->filled('choose_first') && (int) $request->choose_first > 0) {
            return (int) $request->choose_first;
        }

        return (int) (getWebConfig(name: WebConfigKey::PAGINATION_LIMIT) ?? 10);
    }

    private function sanitizeSearchTerm(?string $value): string
    {
        return mb_substr(trim((string) $value), 0, 100);
    }

    private function branchStockListQuery(Request $request): Builder
    {
        $searchValue = $this->sanitizeSearchTerm($request->input('searchValue'));
        $branchFilter = $request->input('branch_id', '');
        $productFilter = $request->input('product_id', '');
        $attributeFilter = $this->sanitizeSearchTerm($request->input('attribute'));
        $escapedAttributeFilter = $this->escapeLikeValue($attributeFilter);
        $escapedSearchValue = $this->escapeLikeValue($searchValue);

        return ManageBranchProductStock::query()
            ->with(['branch.translations', 'product.translations'])
            ->select(
                'branch_id',
                'product_id',
                DB::raw("COALESCE(NULLIF(variation_key, ''), 'No Variation') as variation_key"),
                DB::raw("COALESCE(NULLIF(variation_type, ''), 'No Variation') as variation_type"),
                DB::raw('SUM(current_stock) as total_stock')
            )
            ->whereHas('product', fn($query) => $query->where('product_type', 'physical'))
            ->when($branchFilter, fn(Builder $query) => $query->where('branch_id', $branchFilter))
            ->when($productFilter, fn(Builder $query) => $query->where('product_id', $productFilter))
            ->when(
                $attributeFilter !== '',
                fn(Builder $query) => $query->where(function (Builder $innerQuery) use ($escapedAttributeFilter) {
                    $innerQuery
                        ->where('variation_key', 'LIKE', '%' . $escapedAttributeFilter . '%')
                        ->orWhere('variation_type', 'LIKE', '%' . $escapedAttributeFilter . '%');
                })
            )
            ->when(
                $searchValue !== '',
                fn(Builder $query) => $query->where(function (Builder $innerQuery) use ($escapedSearchValue) {
                    $innerQuery
                        ->whereHas('branch', fn(Builder $branchQuery) => $branchQuery->where('branch_name', 'LIKE', '%' . $escapedSearchValue . '%'))
                        ->orWhereHas('product', fn(Builder $productQuery) => $productQuery->where('name', 'LIKE', '%' . $escapedSearchValue . '%'));
                })
            )
            ->groupBy(
                'branch_id',
                'product_id',
                DB::raw("COALESCE(NULLIF(variation_key, ''), 'No Variation')"),
                DB::raw("COALESCE(NULLIF(variation_type, ''), 'No Variation')")
            );
    }

    private function exportBranchStockList(Request $request): BinaryFileResponse
    {
        $stocks = $this->branchStockListQuery($request)->get();
        $rows = $stocks->map(function ($stock) {
            $variationLabel = translate('Default');
            if (!empty($stock->variation_type) && $stock->variation_type !== 'No Variation') {
                $variationLabel = $stock->variation_type;
                if (!empty($stock->variation_key) && $stock->variation_key !== 'No Variation') {
                    $variationLabel .= ' (' . Str::replace('|', ' • ', Str::replace(':', ' : ', $stock->variation_key)) . ')';
                }
            }

            return [
                $stock->branch?->getTranslatedField('branch_name') ?? translate('not_available'),
                $stock->product?->getTranslatedField('name') ?? translate('not_available'),
                $variationLabel,
                (int) $stock->total_stock,
            ];
        })->values()->all();

        return Excel::download(
            new FormattedTableExport(
                rows: $rows,
                headings: [
                    translate('branch_name'),
                    translate('product_name'),
                    translate('variation'),
                    translate('Current_stock'),
                ],
                title: translate('branch_stock_list'),
                locale: LocalizedExport::locale(),
                isRtl: LocalizedExport::isRtl(),
                metaPairs: [
                    ['label' => translate('exported_at'), 'value' => LocalizedExport::exportedAtLabel()],
                    ['label' => translate('count'), 'value' => (string) count($rows)],
                ],
                filterSummary: implode(' | ', array_filter([
                    translate('search') . ': ' . (trim((string) $request->input('searchValue', '')) ?: translate('all')),
                    translate('branch') . ': ' . ($request->input('branch_id') ?: translate('all')),
                    translate('product') . ': ' . ($request->input('product_id') ?: translate('all')),
                ])),
                columnWidths: ['A' => 24, 'B' => 30, 'C' => 26, 'D' => 14],
                centerColumns: ['D'],
                sumColumns: ['D']
            ),
            LocalizedExport::fileName(translate('branch_stock_list'))
        );
    }

    public function getActiveBranchManagers(?BranchModel $branch = null): Collection
    {
        $branchManagerRoles = array_values(array_unique(array_filter([
            AdminPermissionRegistry::branchManagerRole(),
            'Branch Manager',
            'Operations Manager',
        ])));

        $branchManagers = Admin::query()
            ->active()
            ->withRole($branchManagerRoles)
            ->orderBy('name')
            ->get();

        if ($branchManagers->isNotEmpty()) {
            return $this->appendCurrentBranchManager($branchManagers, $branch);
        }

        $leadEmployeeIds = Lead::query()
            ->whereNotNull('employee_id')
            ->where('employee_id', '>', 0)
            ->pluck('employee_id');
        $leadOwnerIds = Lead::query()
            ->whereNotNull('owner_id')
            ->where('owner_id', '>', 0)
            ->pluck('owner_id');
        $dealEmployeeIds = Deal::query()
            ->whereNotNull('employee_id')
            ->where('employee_id', '>', 0)
            ->pluck('employee_id');
        $dealOwnerIds = Deal::query()
            ->whereNotNull('owner_id')
            ->where('owner_id', '>', 0)
            ->pluck('owner_id');

        $assignedIds = $leadEmployeeIds
            ->merge($leadOwnerIds)
            ->merge($dealEmployeeIds)
            ->merge($dealOwnerIds)
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($assignedIds)) {
            return $this->appendCurrentBranchManager(collect(), $branch);
        }

        return $this->appendCurrentBranchManager(Admin::query()
            ->active()
            ->whereIn('id', $assignedIds)
            ->orderBy('name')
            ->get(), $branch);
    }

    private function appendCurrentBranchManager(Collection $managers, ?BranchModel $branch = null): Collection
    {
        $currentManagerId = (int)data_get($branch, 'manager_id', 0);

        if ($currentManagerId <= 0 || $managers->contains(fn ($manager) => (int)$manager->id === $currentManagerId)) {
            return $managers->values();
        }

        $currentManager = Admin::query()->find($currentManagerId);

        if (!$currentManager) {
            return $managers->values();
        }

        return $managers
            ->prepend($currentManager)
            ->unique('id')
            ->values();
    }
}
