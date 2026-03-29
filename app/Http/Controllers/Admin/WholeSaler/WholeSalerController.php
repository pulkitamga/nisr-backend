<?php

namespace App\Http\Controllers\Admin\WholeSaler;

use Carbon\Carbon;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Warranty;
use App\Enums\StockReason;
use App\Enums\WebConfigKey;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use App\Models\QuotationMeta;
use App\Models\WholesaleTier;
use App\Traits\PaginatorTrait;
use Illuminate\Validation\Rule;
use App\Models\WholesaleContact;
use App\Models\WholeSaleProducts;
use Illuminate\Http\JsonResponse;
use App\Exports\WholesalersExport;
use App\Models\WholesaleOrderItem;
use App\Models\WholesalePriceTier;
use App\Models\WholesaleQuotation;
use App\Models\WholeSalerBusiness;
use Illuminate\Support\Facades\DB;
use App\Services\WholeSalerService;
use App\Services\InventoryMutationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use App\Exports\WholesalerReqExport;
use App\Services\LeadConvertService;
use App\Services\WholeSaleProductsService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\SerialTransferHistory;
use App\Models\WholesaleConfirmOrder;
use App\Models\WholesaleOrderDelivery;
use App\Models\WholesalePurchaseOrder;
use App\Models\WholesaleOrderPayment;
use illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use App\Http\Controllers\BaseController;
use App\Models\ManageBranchProductStock;
use App\Enums\ViewPaths\Admin\WholeSaler;
use App\Exports\WholesalerConfiremExport;
use App\Exports\WholesalerPurchaceExport;
use App\Models\WholesaleConfirmOrderItem;
use Illuminate\Support\Facades\Validator;
use App\Exports\WholesalerQuotationExport;
use App\Models\WholesaleProductPriceRange;
use App\Exports\ProductListWithPriceRangeExport;
use App\Domain\Stock\Support\VariantMatcher;
use App\Http\Requests\Admin\WholeSalerAddRequrest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\WholeSalerRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Enums\ExportFileNames\Admin\Wholesaler as WholesalerExport;
use App\Contracts\Repositories\AdminNotificationRepositoryInterface;



class WholeSalerController extends BaseController
{
    use PaginatorTrait;
    use CommonTrait;

    public function __construct(
        private readonly WholeSalerRepositoryInterface  $wholeSalerRepo,
        private readonly CategoryRepositoryInterface    $categoryRepo,
        private readonly ProductRepositoryInterface     $productRepo,
        private readonly TranslationRepositoryInterface     $translationRepo,
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly AdminNotificationRepositoryInterface   $notificationRepo,
        private readonly InventoryMutationService           $inventoryMutationService,
        private readonly VariantMatcher                     $variantMatcher,
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
        $wholesaler_business = $this->wholeSalerRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['wholesaler'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );

        return view(WholeSaler::LIST[VIEW], compact('wholesaler_business'));
    }
    public function dashboard(): View
    {
        return view(WholeSaler::DASHBOARD[VIEW]);
    }

    public function wholesalerRequest(Request $request): View
    {
        $current_date = date('Y-m-d');
        $wholesaler_business = $this->wholeSalerRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['wholesaler'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );
        $tiers = WholesaleTier::where('is_active', 1)->orderBy('rank')->orderBy('id')->get();

        return view(WholeSaler::LIST_REQUEST[VIEW], compact('wholesaler_business', 'tiers'));
    }

    public function exportReqList(Request $request): BinaryFileResponse
    {
        $wholesaler_business = $this->wholeSalerRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['wholesaler'],
            dataLimit: 'all'
        );

        $filtered_wholesalers = $wholesaler_business->filter(function ($item) {
            return $item->wholesaler && $item->wholesaler->wholesaler_status == 0;
        });

        $filter = 'all';


        $data = [
            'wholesaler' => $filtered_wholesalers,
            'filter' => $filter
        ];

        return Excel::download(new WholesalerReqExport($data), WholesalerExport::WHOLESALE_REQ_XLSX);
    }
    public function exportWholesalerList(Request $request): BinaryFileResponse
    {
        $wholesaler_business = $this->wholeSalerRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['wholesaler'],
            dataLimit: 'all'
        );

        $filtered_wholesalers = $wholesaler_business->filter(function ($item) {
            return $item->wholesaler && $item->wholesaler->wholesaler_status == 1;
        });
        $filter = 'all';

        $data = [
            'wholesaler' => $filtered_wholesalers,
            'filter' => $filter
        ];

        return Excel::download(new WholesalersExport($data), WholesalerExport::WHOLESALER_XLSX);
    }
    public function exporPurchaseList(Request $request): BinaryFileResponse
    {
        $purchaseReq = WholesalePurchaseOrder::with(['wholeseller.wholesalerBusiness', 'wholeseller', 'items.product'])->get();

        $filter = 'all';

        $data = [
            'purchase' => $purchaseReq,
            'filter' => $filter
        ];

        return Excel::download(new WholesalerPurchaceExport($data), WholesalerExport::PURCHASE_ORDER_LIST_XLSX);
    }
    public function exportQuotationList(Request $request): BinaryFileResponse
    {
        $wholesaleQuotations = WholesaleQuotation::with([
            'wholeseller.wholesalerBusiness',
            'items.product'
        ])->get();
        $filter = 'all';

        $data = [
            'quotation' => $wholesaleQuotations,
            'filter' => $filter
        ];

        return Excel::download(new WholesalerQuotationExport($data), WholesalerExport::WHOLESALE_QUOTATION_LIST_XLSX);
    }
    public function exporConfirmList(Request $request): BinaryFileResponse
    {
        $wholesaler_confierm = WholesaleConfirmOrder::with(['wholeseller.wholesalerBusiness', 'wholeseller'])->get();

        $filter = 'all';

        $data = [
            'confirem' => $wholesaler_confierm,
            'filter' => $filter
        ];

        return Excel::download(new WholesalerConfiremExport($data), WholesalerExport::WHOLESALE_CONFIRM_XLSX);
    }


    public function getAddView(Request $request): View
    {
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = $this->categoryRepo->getFirstWhere(params: ['id' => $request['sub_category_id']]);
        return view(WholeSaler::ADD[VIEW], compact('categories', 'subCategory'));
    }
    public function add(WholeSaleProductAddRequrest $request, WholeSaleProductsService $service): JsonResponse
    {
        $dataArray = $service->getAddData(request: $request);
        $savedRequest = $this->wholeSalerRepo->add(data: $dataArray);

        $pricerange = $service->addProductRangePrices($request->min_qty, $savedRequest->id);

        if ($request->has('min_qty') && is_array($request->min_qty)) {
            $priceRanges = [];
            foreach ($request->min_qty as $index => $minQty) {
                $priceRanges[] = [
                    'wholesale_id'     => $savedRequest->id,
                    'min_qty'          => $minQty,
                    'max_qty'          => $request->max_qty[$index] ?? 0,
                    'price_per_piece'  => $request->price_per_piece[$index] ?? 0,
                    'status'           => 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            WholesaleProductPriceRange::insert($priceRanges);
        }
        return response()->json(['message' => translate('Product_added_successfully')]);
    }

    public function getProductView(string|int $id): View
    {
        $ProductData = $this->wholeSalerRepo->getFirstWhere(params: ['id' => $id], relations: ['price_list', 'product', 'category', 'subcategory']);
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = [];
        return view(WholeSaler::PRODUCT_VIEW[VIEW], compact('ProductData', 'categories', 'subCategory'));
    }
    public function viewWholesalerDetails($id)
    {

        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $business = WholeSalerBusiness::with(['wholesaler', 'contacts'])->findOrFail($id); // ← contacts added

        $wholesaler = $business->wholesaler;

        $orders = WholesalePurchaseOrder::with(['items.product'])
            ->where('wholeseller_id', $wholesaler->id)
            ->latest()
            ->paginate($dataLimit);

        return view(WholeSaler::WHOLESALER_VIEW[VIEW], compact('wholesaler', 'business', 'orders'));
    }

    public function viewWholesalerEdit($id)
    {
        $business = WholeSalerBusiness::with(['wholesaler'])->findOrFail($id);
        $wholesaler = $business->wholesaler;

        $tiers = WholesaleTier::where('is_active', 1)->orderBy('rank')->orderBy('id')->get(); // sirf active tiers

        return view(WholeSaler::WHOLESALER_EDIT[VIEW], compact('wholesaler', 'business', 'tiers'));
    }
    public function wholesalerUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'tier' => [
                'required',
                'string',
                Rule::exists('wholesale_tiers', 'name')->where(function ($query) {
                    $query->where('is_active', 1)->whereNull('deleted_at');
                }),
            ],
            'wholesaler_discount' => 'required|numeric|min:0|max:100',
            'wholesaler_status' => 'required|in:0,1',
        ]);

        $wholesaler = User::findOrFail($id);
        $wholesaler->tier = $validated['tier'];
        $wholesaler->wholesaler_discount = $validated['wholesaler_discount'];
        $wholesaler->wholesaler_status = (int) $validated['wholesaler_status'];

        $wholesaler->save();

        Toastr::success(translate('Wholesaler updated successfully'));

        return back();
    }
    public function getUpdateView(string|int $id): View
    {
        $ProductData = $this->wholeSalerRepo->getFirstWhere(params: ['id' => $id], relations: ['price_list', 'product', 'category', 'subcategory']);
        $get_sub_category = "";
        if ($ProductData->sub_category_id) {
            $get_sub_category = $this->categoryRepo->getFirstWhere(params: ['id' => $ProductData->sub_category_id]);
        }
        $get_product = "";
        if ($ProductData->product_id) {
            $get_product = $this->productRepo->getFirstWhere(params: ['id' => $ProductData->product_id]);
        }
        $categories = $this->categoryRepo->getListWhere(filters: ['position' => 0], dataLimit: 'all');
        $subCategory = [];
        return view(WholeSaler::UPDATE_VIEW[VIEW], compact('ProductData', 'categories', 'subCategory', 'get_sub_category', 'get_product'));
    }


    public function update(WholeSaleProductAddRequrest $request, WholeSaleProductsService $service, string|int $id): JsonResponse
    {
        $price_range = WholesaleProductPriceRange::where('wholesale_id', $request->primary_id);

        if ($price_range->exists()) {
            $price_range->delete();
        }
        if ($request->has('min_qty') && is_array($request->min_qty)) {
            $priceRanges = [];
            foreach ($request->min_qty as $index => $minQty) {
                $priceRanges[] = [
                    'wholesale_id'     => $request->primary_id,
                    'min_qty'          => $minQty,
                    'max_qty'          => $request->max_qty[$index] ?? 0,
                    'price_per_piece'  => $request->price_per_piece[$index] ?? 0,
                    'status'           => 0,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            WholesaleProductPriceRange::insert($priceRanges);
        }
        return response()->json(['message' => translate('Product_price_details_updated_successfully')]);
    }

    public function exportProductWithPrices(Request $request): BinaryFileResponse
    {
        $wholesale_products_with_prices = $this->wholeSalerRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['price_list', 'product', 'category', 'subcategory'],
            dataLimit: 'all'
        );
        $data = [];
        $total_price_range_rows = 0;
        foreach ($wholesale_products_with_prices as $price) {
            $price_range = WholesaleProductPriceRange::where('wholesale_id', $price->id)->get();
            $total_price_range_rows += $price_range->count();

            $data[] = [
                'primary_id'        => $price->id,
                'product_name'      => $price->product?->name,
                'category_name'     => $price->category?->name,
                'sub_category_name' => $price->subcategory?->name,
                'attribute_id'      => $price->attribute_id,
                'price_ranges'      => $price_range->toArray(),
            ];
        }
        $data[] = [
            'total_rows' => $total_price_range_rows,
        ];
        return Excel::download(new ProductListWithPriceRangeExport($data), 'product-list.xlsx');
    }

    public function getWholesalerBusinessRequests(Request $request): view
    {
        $current_date = date('Y-m-d');
        $wholesale_products = $this->wholeSalerRepo->getListWhere(
            orderBy: ['id' => 'desc'],
            searchValue: $request['searchValue'],
            relations: ['price_list', 'product', 'category', 'subcategory'],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT)
        );

        return view(WholeSaler::LIST[VIEW], compact('wholesale_products'));
    }


    public function approveRejectWholesaleBusiness(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:users,id',
            'action' => 'required|in:approve,reject',
        ]);

        $wholesaler = User::findOrFail($validated['id']);
        if ($validated['action'] === 'approve') {
            $approveValidated = $request->validate([
                'tier' => 'required|string',
                'wholesaler_discount' => 'required|numeric|min:0',
            ]);

            $tier = WholesaleTier::where('name', $approveValidated['tier'])->where('is_active', 1)->first();

            if (!$tier) {
                return redirect()->back()->with('error', 'Selected tier is not valid or inactive.');
            }

            $wholesaler->update([
                'user_type' => 1,
                'wholesaler_status' => 1,
                'tier' => $tier->name,
                'wholesaler_discount' => $approveValidated['wholesaler_discount'],
            ]);

            $title   = "Business Account Approved";
            $message = "Your Business Account is approved";
            $link    = route('home');

            $recipients = [
                ['type' => 'customer', 'id' => $validated['id']],
            ];

            $this->notificationRepo->notifyRecipients(
                $validated['id'],
                User::class,
                $title,
                $message,
                $link,
                $recipients
            );

            return redirect()->back()->with('success', 'Business approved successfully');
        } elseif ($validated['action'] === 'reject') {
            $wholesaler->update([
                'user_type' => 0,
                'wholesaler_status' => 0,
                'tier' => null,
                'wholesaler_discount' => 0,
                'moq_override_enabled' => 0,
            ]);
            $title   = "Business Account is rejected";
            $message = "Your Business Account is rejected by admin";
            $link    = route('home');

            $recipients = [
                ['type' => 'customer', 'id' => $validated['id']],
            ];

            $this->notificationRepo->notifyRecipients(
                $validated['id'],
                User::class,
                $title,
                $message,
                $link,
                $recipients
            );
            return redirect()->back()->with('success', 'Business rejected successfully');
        }

        return redirect()->back()->with('error', 'Invalid action');
    }

    public function toggleMOQOverride(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'status' => 'required|boolean',
        ]);

        $user = User::find($request->id);
        $user->moq_override_enabled = $request->status;
        $user->save();


        $title   = "MOQ override";
        $message = "Your minimum order quantity restrition is now off you can purchase order start from 1 quantity ";
        $link    = route('home');

        $recipients = [
            ['type' => 'customer', 'id' => $request->id],
        ];

        $this->notificationRepo->notifyRecipients(
            $request->id,
            User::class,
            $title,
            $message,
            $link,
            $recipients
        );

        return response()->json(['message' => 'MOQ override status updated successfully.']);
    }

    public function orderRequest(Request $request)
    {

        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $query = WholesalePurchaseOrder::with(['wholeseller.wholesalerBusiness', 'wholeseller', 'items.product']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('tier')) {
            $query->whereHas('wholeseller', function ($q) use ($request) {
                $q->where('tier', $request->tier);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate($dataLimit);

        return view(WholeSaler::ORDER_REQUEST[VIEW], compact('orders'));
    }

    public function checkNumber(Request $request)
    {
        $exists = WholesalePurchaseOrder::where('purchase_order_no', $request->number)->exists();
        return response()->json(['exists' => $exists]);
    }

    public function assignNumber(Request $request)
    {
        $request->validate([
            'purchase_order_no' => 'required|string|max:255',
            'order_id' => 'required|exists:wholesale_purchase_orders,id'
        ]);

        DB::transaction(function () use ($request) {
            $order = WholesalePurchaseOrder::query()
                ->lockForUpdate()
                ->findOrFail($request->order_id);

            $duplicateExists = WholesalePurchaseOrder::query()
                ->where('purchase_order_no', $request->purchase_order_no)
                ->where('id', '!=', $order->id)
                ->lockForUpdate()
                ->exists();

            if ($duplicateExists) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'purchase_order_no' => translate('purchase_order_number_is_already_assigned'),
                ]);
            }

            $order->purchase_order_no = $request->purchase_order_no;
            $order->status = 'processed';
            $order->save();
        });

        Toastr::success(translate('Purchase Order number assigned successfully.'));

        return redirect()->back();
    }


    public function confirmedOrders(Request $request)
    {

        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $query = WholesaleConfirmOrder::with(['wholeseller.wholesalerBusiness', 'wholeseller']);
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $request->delivery_status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->price_sort === 'low_high') {
            $query->orderBy('final_price', 'asc');
        } elseif ($request->price_sort === 'high_low') {
            $query->orderBy('final_price', 'desc');
        } else {
            $query->latest();
        }

        $orders = $query->paginate($dataLimit);

        return view(WholeSaler::CONFIRMED_ORDERS[VIEW], compact('orders'));
    }



    public function wholesaleQuotation(Request $request)
    {

        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $query = WholesaleQuotation::with(['wholeseller.wholesalerBusiness', 'items.product']);
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('tier')) {
            $query->where('wholeseller_tier', $request->tier); // or use relation if stored in related model
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('searchValue')) {
            $query->whereHas('items.product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->searchValue . '%');
            });
        }
        if ($request->price_sort == 'low_high') {
            $query->orderBy('final_price', 'asc');
        } elseif ($request->price_sort == 'high_low') {
            $query->orderBy('final_price', 'desc');
        } else {
            $query->latest();
        }
        $orders = $query->paginate($dataLimit);
        return view(WholeSaler::WHOLESALE_QUOTATIONS[VIEW], compact('orders'));
    }




     public function viewOrder($id)
    {
        $order = WholesalePurchaseOrder::with(['items.product', 'wholeseller'])->findOrFail($id);

        // Default empty collection
        $priceRanges = collect();

        // Safe check
        $firstItem = $order->items->first();

        if ($firstItem && $firstItem->product) {
            $priceRanges = WholesaleProductPriceRange::where(
                'wholesale_id',
                $firstItem->product->wholesale_id
            )->get();
        }

        $wholesaleProducts = WholeSaleProducts::with(['product'])->get();

        return view(WholeSaler::REQUEST_VIEW[VIEW], compact('order', 'priceRanges', 'wholesaleProducts'));
    }


    public function viewPurchaseOrder($id)
    {
        $order = WholesalePurchaseOrder::with('items.product', 'wholeseller')->findOrFail($id);

        $order_no = $order->purchase_order_no;

        $quotation = WholesaleQuotation::where('purchase_order_no', $order_no)->firstOrFail();

        $priceRanges = collect();
        $firstItem = $order->items->first();
        if ($firstItem?->product?->wholesale_id) {
            $priceRanges = WholesaleProductPriceRange::where('wholesale_id', $firstItem->product->wholesale_id)->get();
        }

        $wholesaleProducts = WholeSaleProducts::with(['product'])->get();

        return view(WholeSaler::PURCHASE_ORDER_VIEW[VIEW], compact('order', 'priceRanges', 'quotation',  'wholesaleProducts'));
    }





    public function createQuotation()
    {
        $wholesalers = User::where('user_type', 1)
            ->where('is_active', 1)
            ->where('wholesaler_status', 1)
            ->with('wholesalerBusiness')
            ->get();

        $wholesaleProducts = WholeSaleProducts::with(['product'])->get();

        return view(WholeSaler::CREATE_QUOTATION[VIEW], compact('wholesalers', 'wholesaleProducts'));
    }



    public function storeQuotation(Request $request)
    {
        Log::info("request create quotation", ['request' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'quotation_no' => 'required|unique:wholesale_quotations,quotation_no',
            'wholesaler_id' => 'required|integer|exists:users,id',
            'wholesale_tier' => 'required|string|max:100',
            'wholesaler_discount' => 'nullable|string',
            'wholesaler_discount_amount' => 'nullable|numeric|min:0',
            'final_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $enIndex = getDefaultLanguageIndex($request);

        $quotation = DB::transaction(function () use ($request, $enIndex) {
            $quotation = WholesaleQuotation::create([
                'quotation_no' => $request->quotation_no,
                'wholeseller_id' => $request->wholesaler_id,
                'wholeseller_tier' => $request->wholesale_tier,
                'final_price' => $request->final_price,
                'wholesaler_discount_amount' => $request->wholesaler_discount_amount ?? 0,
                'terms_and_conditions' => $request->terms_and_conditions[$enIndex] ?? null,
                'note' => $enIndex !== false ? ($request->note[$enIndex] ?? null) : null,
                'status' => 'sent',
            ]);

            $dealId = $request->input('deal_id') ?? $request->query('deal_id');
            if ($dealId) {
                Deal::where('id', $dealId)->update([
                    'quotation_id' => $quotation->id,
                    'quotation_status' => 'sent',
                ]);
            }

            if ($request->has('products') && is_array($request->products)) {
                foreach ($request->products as $item) {
                    if (!is_array($item) || empty($item['approved_quantity'])) {
                        continue;
                    }

                    $variationType = $item['variation_type'] ?? null;
                    if (empty($variationType) || $variationType === 'null') {
                        $variationType = null;
                    }

                    $tax = $item['tax'] ?? '0';
                    $tax = is_string($tax) ? str_replace('%', '', $tax) : $tax;

                    $quotation->items()->create([
                        'wholesale_quotation_id' => $quotation->id,
                        'product_id'             => $item['product_id'] ?? null,
                        'product_variation_type' => $variationType,
                        'product_quantity'       => (int) $item['approved_quantity'],
                        'base_price'             => (float) ($item['price'] ?? 0),
                        'final_price'            => (float) ($item['final_price'] ?? 0),
                        'tax'                    => $tax,
                        'price_range_id'         => null,
                    ]);
                }
            }

            foreach ($request->input('charges', []) as $charge) {
                if (!empty($charge['name']) || !empty($charge['value'])) {
                    $quotation->metas()->create([
                        'type' => 'charge',
                        'key' => $charge['name'] ?? 'Untitled Charge',
                        'value' => $charge['value'] ?? 0,
                    ]);
                }
            }

            foreach ($request->input('discounts', []) as $discount) {
                if (!empty($discount['name']) || !empty($discount['value'])) {
                    $quotation->metas()->create([
                        'type' => 'discount',
                        'key' => $discount['name'] ?? 'Untitled Discount',
                        'value' => $discount['value'] ?? 0,
                    ]);
                }
            }

            $this->translationRepo->add(
                request: $request,
                model: WholesaleQuotation::class,
                id: $quotation->id
            );

            return $quotation;
        });


        $title   = "Quotation Send";
        $message = "Admin send you an quotation review it in my quotations";
        $link    = route('home');

        $recipients = [
            ['type' => 'customer', 'id' => $request->wholesaler_id],
        ];

        $this->notificationRepo->notifyRecipients(
            $request->wholesaler_id,
            User::class,
            $title,
            $message,
            $link,
            $recipients
        );

        Toastr::success('Quotation created successfully!');
        return redirect()->route('admin.wholesale.business.wholesale.order');
    }
    public function orderDestroy($id)
    {

        $order = WholesalePurchaseOrder::findOrFail($id);
        $order->delete();

        Toastr::success(translate('Purchase Request deleted successfully'));
        return back();
    }
    public function confiremOrderDestroy($id)
    {
        $order = WholesaleConfirmOrder::findOrFail($id);
        $order->delete();
        Toastr::success(translate('Confirem order deleted successfully'));
        return back();
    }
    public function quotationDestroy($id)
    {

        $quotation = WholesaleQuotation::findOrFail($id);

        $isUsed = WholesaleConfirmOrder::where('quotation_no', $quotation->quotation_no)->exists();

        if ($isUsed) {
            Toastr::error(translate('This quotation is linked with a confirmed order and cannot be deleted.'));
            return back();
        }
        $quotation->delete();

        Toastr::success(translate('Quotation deleted successfully'));
        return back();
    }

    public function checkOrderNo(Request $request)
    {
        $orderNo = $request->order_no;
        $exists = WholesaleQuotation::where('quotation_no', $orderNo)->exists();

        return response()->json([
            'input' => $orderNo,
            'exists' => $exists,
            'count' => WholesaleQuotation::where('quotation_no', $orderNo)->count(),
        ]);
    }


    public function invoiceView($id)
    {
        $order = WholesaleQuotation::with(['wholeseller', 'items.product', 'metas', 'translations'])->findOrFail($id);
        return view(WholeSaler::INVOICE_VIEW[VIEW], compact('order'));
    }
    public function showCompleteInvoice($id)
    {
        $order = WholesaleConfirmOrder::with(['wholeseller', 'items.product', 'metas'])->findOrFail($id);
        $quotation = null;
        if ($order->quotation_no) {
            $quotation = WholesaleQuotation::with('metas')
                ->where('quotation_no', $order->quotation_no)
                ->first();
        }

        $businessSetting = $this->businessSettingRepo->getFirstWhere(
            params: ['type' => 'quotation_settings'],
            relations: ['translations']
        );

        $quotationImages = json_decode($businessSetting->value, true);

        $products = WholesaleConfirmOrderItem::with(['product'])
            ->where('confirmed_order_id', $id)
            ->get();

        return view(WholeSaler::CONFIREM_ORDER_INVOICE[VIEW], compact('order', 'products', 'quotationImages', 'quotation'));
    }



    public function wholesalerContact(Request $request)
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', Rule::exists('wholesaler_businesses', 'id')],
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'mobile_number_1' => 'nullable|string|max:20',
            'mobile_number_2' => 'nullable|string|max:20',
            'preferred_contact_method' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'tags' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'last_contacted_at' => 'nullable|date',
        ]);

        WholesaleContact::create($validated);

        Toastr::success(translate('Contact added successfully.'));
        return redirect()->back();
    }

    public function wholesalerContactUpdate(Request $request, $id)
    {
        $contact = WholesaleContact::findOrFail($id);
        $contact->update($request->all());
        Toastr::success(translate('Contact updated successfully.'));

        return back();
    }

    public function wholesalerContactDelete($id)
    {
        $contact = WholesaleContact::findOrFail($id);
        $contact->delete();
        Toastr::success(translate('Contact deleted successfully.'));

        return back();
    }

    public function tierIndex()
    {
        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $tiers = WholesaleTier::with('translations')
            ->orderBy('rank')
            ->orderBy('id')
            ->paginate($dataLimit);
        $language = getWebConfig(name: 'pnc_language') ?? null;
        $defaultLanguage = $language[0] ?? 'en'; // fallback to en
        return view(WholeSaler::TIER_VIEW[VIEW], compact('tiers', 'language', 'defaultLanguage'));
    }

    public function tierStore(Request $request)
    {
        $defaultLangIndex = array_search(getSaveLanguage(), $request->input('lang', []), true);
        if ($defaultLangIndex === false) {
            $defaultLangIndex = 0;
        }

        $request->validate([
            'name' => 'required|array',
            'name.*' => 'nullable|string|max:255',
            "name.$defaultLangIndex" => 'required|string|max:255',
            'lang' => 'required|array',
            'lang.*' => 'required|string',
            'rank' => 'required|integer|min:1',
        ]);

        $tier = WholesaleTier::create([
            'name' => $request->name[$defaultLangIndex],
            'is_active' => $request->has('is_active'),
            'rank' => (int)$request->rank,
        ]);
        $this->translationRepo->add(
            request: $request,
            model: WholesaleTier::class,
            id: $tier->id
        );
        Toastr::success(translate('Tier added successfully'));
        return back();
    }


    public function tierUpdate(Request $request, $id)
    {
        $defaultLangIndex = array_search(getSaveLanguage(), $request->input('lang', []), true);
        if ($defaultLangIndex === false) {
            $defaultLangIndex = 0;
        }

        $request->validate([
            'name' => 'required|array',
            'name.*' => 'nullable|string|max:255',
            "name.$defaultLangIndex" => 'required|string|max:255',
            'lang' => 'required|array',
            'lang.*' => 'required|string',
            'rank' => 'required|integer|min:1',
        ]);

        $tier = WholesaleTier::findOrFail($id);
        $tier->update([
            'name' => $request->name[$defaultLangIndex],
            'rank' => (int)$request->rank,
        ]);

        $this->translationRepo->update(
            request: $request,
            model: WholesaleTier::class,
            id: $tier->id
        );
        Toastr::success(translate('Tier updated successfully'));

        return back();
    }

    public function tierDestroy($id)
    {
        $tier = WholesaleTier::findOrFail($id);
        $tier->delete();
        $this->translationRepo->delete(
            model: WholesaleTier::class,
            id: $tier->id
        );
        Toastr::success(translate('Tier deleted (soft) successfully'));

        return back();
    }

    public function tierUpdateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:wholesale_tiers,id',
            'status' => 'required|boolean',
        ]);

        $tier = WholesaleTier::findOrFail($request->id);
        $tier->is_active = $request->status;
        $tier->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function invoiceEdit($id)
    {
        $order = WholesaleQuotation::with('items.product', 'wholeseller', 'metas', 'translations')->findOrFail($id);

        $priceRanges = collect([]);
        if ($order->items->isNotEmpty()) {
            $priceRanges = WholesaleProductPriceRange::where('wholesale_id', $order->items->first()->product->wholesale_id)->get();
        }

        $wholesaleProducts = WholeSaleProducts::with('product')->get();

        $existingCharges = [];
        $existingDiscounts = [];

        foreach ($order->metas as $meta) {
            if ($meta->type === 'charge') {
                $existingCharges[] = [
                    'label' => $meta->key,
                    'amount' => $meta->value,
                ];
            } elseif ($meta->type === 'discount') {
                $existingDiscounts[] = [
                    'label' => $meta->key,
                    'amount' => $meta->value,
                ];
            }
        }


        return view(WholeSaler::INVOICE_EDIT[VIEW], compact('order', 'priceRanges', 'wholesaleProducts',  'existingCharges',  'existingDiscounts'));
    }

    public function quotationCreate(Request $request, $id)
    {
        Log::info("request", ['request' => $request->all()]);

        $order = WholesalePurchaseOrder::findOrFail($id);
        $order->status = 'quotationsend';
        $order->save();

        $purchaseOrder = WholesaleQuotation::create([
            'order_id' => $order->order_id,
            'purchase_order_no' => $request->input('order_no') ?? null,
            'quotation_no' => $request->input('quotation_no'),
            'wholeseller_id' => $order->wholeseller_id,
            'wholeseller_tier' => $order->wholeseller_tier,
            'status' => 'sent',
            'final_price' => $request->input('final_price'),
            'wholesaler_discount_amount' => $request->input('wholesaler_discount_amount'),
            'terms_and_conditions' => $request->input('terms_and_conditions')[getDefaultLanguageIndex($request)] ?? null,
            'note' => $request->input('note')[getDefaultLanguageIndex($request)] ?? null,
        ]);

        $lead = Lead::where('po_id', $order->id)->first();
        if ($lead) {
            try {
                $deal = Deal::where('lead_id', $lead->id)->first();
                if (!$deal) {
                    $wholesalerBusiness = $order->wholeseller->wholesalerBusiness ?? null;

                    if (!$wholesalerBusiness) {
                        Toastr::error('Wholesaler business not found for this wholesaler');
                        return redirect()->back();
                    }

                    $data = [
                        'party_type'  => 'company',
                        'party_id'    => $wholesalerBusiness->id,
                        'owner_id'    => auth('admin')->id(),
                        'value'       => $request->input('final_price') ?? 0,
                        'employee_id' => auth('admin')->id(),
                    ];

                    $deal = app(LeadConvertService::class)->convert($lead, $data);
                }
                $deal->update([
                    'quotation_id'     => $purchaseOrder->id,
                    'quotation_status' => $purchaseOrder->status,
                ]);
            } catch (\Exception $e) {
                Toastr::error($e->getMessage());
                return redirect()->back();
            }
        }

        if ($request->has('products')) {
            foreach ($request->products as $productId => $productData) {
                $variationType = null;
                $itemData = [];

                if (is_array($productData) && array_key_exists(0, $productData)) {
                    // Non-variation: merge all sub-arrays
                    foreach ($productData as $subItem) {
                        if (is_array($subItem)) {
                            $itemData = array_merge($itemData, $subItem);
                        }
                    }
                } else {
                    // Variation: $productData is assoc array with variationKey => item
                    foreach ($productData as $key => $item) {
                        $variationType = (string)$key; // Assume key is variation type
                        $itemData = $item;
                        break; // Assuming one variation per product in this structure
                    }
                }

                if (empty($itemData) || !isset($itemData['approved_quantity'])) {
                    continue;
                }

                $quantity = (int)($itemData['approved_quantity'] ?? 0);
                if ($quantity <= 0) continue;

                $basePrice = $itemData['price'] ?? 0;
                $finalPrice = $itemData['final_price'] ?? 0;
                $tax = $itemData['tax'] ?? 0;

                $purchaseOrder->items()->create([
                    'wholesale_quotation_id' => $purchaseOrder->id,
                    'product_id'             => $productId,
                    'product_variation_type' => $variationType,
                    'product_quantity'       => $quantity,
                    'base_price'             => $basePrice,
                    'final_price'            => $finalPrice,
                    'tax'                    => $tax,
                    'price_range_id'         => null,
                ]);
            }
        }

        // 5. Insert charges & discounts
        $quotationMeta = [];

        foreach ($request->input('charges', []) as $charge) {
            $quotationMeta[] = [
                'type' => 'charge',
                'key' => $charge['name'],
                'value' => $charge['value'],
            ];
        }

        foreach ($request->input('discounts', []) as $discount) {
            $quotationMeta[] = [
                'type' => 'discount',
                'key' => $discount['name'],
                'value' => $discount['value'],
            ];
        }

        $purchaseOrder->metas()->delete();

        foreach ($quotationMeta as $meta) {
            $purchaseOrder->metas()->create($meta);
        }

        // 6. Add translations
        $this->translationRepo->add(
            request: $request,
            model: WholesaleQuotation::class,
            id: $purchaseOrder->id
        );

        $title   = "Wholesale Purchase Order Approved";
        $message = "Your purchase order is approved admin send you a quotation review it in my quotations";
        $link    = route('home');

        $recipients = [
            ['type' => 'customer', 'id' => $order->wholeseller_id],
        ];

        $this->notificationRepo->notifyRecipients(
            $order->wholeseller_id,
            User::class,
            $title,
            $message,
            $link,
            $recipients
        );

        Toastr::success(translate('Order approved successfully'));

        return redirect()->route('admin.wholesale.business.order.request');
    }

    public function quotationUpdate(Request $request, $id)
    {
        Log::info("update request", ['request' => $request->all()]);

        $quotation = WholesaleQuotation::findOrFail($id);

        $quotation->update([
            'final_price' => $request->final_price,
            'wholesaler_discount_amount' => $request->wholesaler_discount_amount ?? 0,
            'terms_and_conditions' => $request->terms_and_conditions[getDefaultLanguageIndex($request)] ?? null,
            'note' => $request->note[getDefaultLanguageIndex($request)] ?? null,
        ]);
        $quotation->items()->delete();
        if ($request->has('products') && is_array($request->products)) {
            foreach ($request->products as $productId => $productData) {
                $variationType = null;
                $itemData = [];

                // Case 1: Variation product → object with variation key
                if (is_array($productData) && !array_key_exists(0, $productData)) {
                    // Like: "1200" => [data]
                    foreach ($productData as $key => $data) {
                        $variationType = $key; // "1200", "50-100-120" etc.
                        $itemData = $data;
                        break;
                    }
                }
                // Case 2: Non-variation product → numeric indexed array of arrays
                else if (is_array($productData) && isset($productData[0])) {
                    foreach ($productData as $sub) {
                        if (is_array($sub)) {
                            $itemData = array_merge($itemData, $sub);
                        }
                    }
                    $variationType = null; // explicitly null
                }
                if (empty($itemData) || !isset($itemData['approved_quantity'])) {
                    continue;
                }

                $quantity   = (int)($itemData['approved_quantity'] ?? 0);
                $basePrice  = (float)($itemData['price'] ?? 0);
                $finalPrice = (float)($itemData['final_price'] ?? 0);
                $tax        = $itemData['tax'] ?? '0';
                $tax        = is_string($tax) ? str_replace('%', '', $tax) : $tax;

                if ($quantity <= 0) continue;

                $quotation->items()->create([
                    'wholesale_quotation_id' => $quotation->id,
                    'product_id'             => $productId,
                    'product_variation_type' => $variationType, // sahi jayega: ya to string ya null
                    'product_quantity'       => $quantity,
                    'base_price'             => $basePrice,
                    'final_price'            => $finalPrice,
                    'tax'                    => $tax,
                    'price_range_id'         => null,
                ]);
            }
        }

        // Charges & Discounts
        $quotation->metas()->delete();

        foreach ($request->input('charges', []) as $charge) {
            if (!empty($charge['name']) || !empty($charge['value'])) {
                $quotation->metas()->create([
                    'type'  => 'charge',
                    'key'   => $charge['name'] ?? 'Charge',
                    'value' => $charge['value'] ?? 0,
                ]);
            }
        }

        foreach ($request->input('discounts', []) as $discount) {
            if (!empty($discount['name']) || !empty($discount['value'])) {
                $quotation->metas()->create([
                    'type'  => 'discount',
                    'key'   => $discount['name'] ?? 'Discount',
                    'value' => $discount['value'] ?? 0,
                ]);
            }
        }

        // Update translations
        $this->translationRepo->update(
            request: $request,
            model: WholesaleQuotation::class,
            id: $quotation->id
        );

        $title   = "Quotation Update";
        $message = "Your quotation is updated by admin review it in my quotations";
        $link    = route('home');

        $recipients = [
            ['type' => 'customer', 'id' => $quotation->wholeseller_id],
        ];

        $this->notificationRepo->notifyRecipients(
            $quotation->wholeseller_id,
            User::class,
            $title,
            $message,
            $link,
            $recipients
        );


        Toastr::success('Quotation updated successfully!');
        return redirect()->back();
    }



    public function showPaymentPage($id)
    {
        $order = WholesaleConfirmOrder::with(['wholeseller.wholesalerBusiness', 'payments'])->findOrFail($id);

        $remaining = $order->payments()->latest()->value('remaining_amount') ?? $order->final_price;

        return view(WholeSaler::PAYMENT_VIEW[VIEW],  compact('order', 'remaining'));
    }
    public function showDeliveryPage($id)
    {
        $branches = Branch::all();
        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $order = WholesaleConfirmOrder::with(['wholeseller.wholesalerBusiness', 'deliveries'])->findOrFail($id);

        // Requested Products
        $deliveries = WholesaleConfirmOrderItem::with(['product'])
            ->where('confirmed_order_id', $id)
            ->get();

        // Delivery Logs
        $deliveryLogs = WholesaleOrderDelivery::with('product', 'branch')
            ->where('confirmed_order_id', $id)
            ->latest()
            ->paginate($dataLimit);

        return view(WholeSaler::DELIVERY_VIEW[VIEW], compact('order', 'deliveries', 'branches', 'deliveryLogs'));
    }

    public function paymentStore(Request $request)
    {

        $request->validate([
            'order_id' => 'required|exists:wholesale_confirm_orders,id',
            'reference' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
            'note' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $incomingAmount = (float)$request->amount;
        try {
            DB::transaction(function () use ($request, $incomingAmount) {
                $order = WholesaleConfirmOrder::query()
                    ->where('id', $request->order_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $paidAmount = (float)WholesaleOrderPayment::query()
                    ->where('wholesale_confirm_order_id', $order->id)
                    ->lockForUpdate()
                    ->sum('amount');
                $currentRemaining = max(0, (float)$order->final_price - $paidAmount);

                if ($incomingAmount > $currentRemaining + 0.00001) {
                    throw new \RuntimeException(translate('Payment amount exceeds remaining balance.'));
                }

                $newRemaining = max(0, $currentRemaining - $incomingAmount);

                $order->payments()->create([
                    'order_id' =>  $order->order_id,
                    'wholesale_confirm_order_id' =>  $order->id,
                    'reference' => $request->reference,
                    'amount' => $incomingAmount,
                    'payment_through' => $request->method,
                    'notes' => $request->note,
                    'remaining_amount' => $newRemaining,
                    'date' => $request->date,
                ]);

                $order->payment_status = $newRemaining <= 0 ? 'paid' : 'partials';
                $order->save();
            });
        } catch (\RuntimeException $exception) {
            Toastr::error($exception->getMessage());
            return back()->withInput();
        } catch (\Throwable $exception) {
            Log::error('Failed to store wholesale payment', [
                'order_id' => $request->order_id,
                'error' => $exception->getMessage(),
            ]);
            Toastr::error(translate('Unable to save payment.'));
            return back()->withInput();
        }

        Toastr::success(translate('Payment_Added_Successfully'));

        return back();
    }

    public function getByType(Request $request)
    {
        $type = $request->type;
        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        // Current wholesaler/business id
        $wholesalerId = $request->business_id ?? 0; // ya $business->id agar controller me available hai

        switch ($type) {
            case 'purchase':
                $orders = WholesalePurchaseOrder::where('wholeseller_id', $wholesalerId)
                    ->latest()
                    ->paginate($dataLimit);
                $view = view('admin-views.wholesaler-business.partials.purchase', compact('orders'))->render();
                break;

            case 'quotation':
                $quotations = WholesaleQuotation::where('wholeseller_id', $wholesalerId)
                    ->latest()
                    ->paginate($dataLimit);
                $view = view('admin-views.wholesaler-business.partials.quotation', compact('quotations'))->render();
                break;

            case 'confirmed':
                $confirmed = WholesaleConfirmOrder::where('wholesaler_id', $wholesalerId)
                    ->latest()
                    ->paginate($dataLimit);
                $view = view('admin-views.wholesaler-business.partials.confirmed', compact('confirmed'))->render();
                break;

            default:
                $view = '<div class="text-danger p-4">Invalid type.</div>';
                break;
        }

        return response()->json([
            'html' => $view
        ]);
    }



    public function checkConfirmNumber(Request $request)
    {
        $request->validate([
            'type' => 'required|in:invoice_no,confirm_order_no',
            'number' => 'required|string',
        ]);

        $exists = WholesaleConfirmOrder::where($request->type, $request->number)->exists();

        return response()->json(['exists' => $exists]);
    }


    public function assignInvoiceNumber(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:wholesale_confirm_orders,id',
            'invoice_no' => 'required|unique:wholesale_confirm_orders,invoice_no',
        ]);

        $order = WholesaleConfirmOrder::findOrFail($request->order_id);
        $order->invoice_no = $request->invoice_no;
        $order->save();

        Toastr::success(translate('Invoice number assigned successfully'));

        return redirect()->back();
    }

    public function assignConfirmNumber(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:wholesale_confirm_orders,id',
            'confirm_order_no' => 'required|unique:wholesale_confirm_orders,confirm_order_no',
        ]);

        $order = WholesaleConfirmOrder::findOrFail($request->order_id);
        $order->confirm_order_no = $request->confirm_order_no;
        $order->save();

        Toastr::success(translate('Confirm order number assigned successfully.'));

        return redirect()->back();
    }



    public function branchList()
    {
        $branches = Branch::select('id', 'branch_name')->where('status', 'active')->get();
        return response()->json($branches);
    }
     

// Then query like this:
// public function branchProductStock(Request $request)
// {
//     $branchId = $request->input('branch_id');
//     $wholesaleProductId = $request->input('product_id');
//     $variationType = $request->input('variation_type');

//     // Get the wholesale product first
//     $wholesaleProduct = \App\Models\WholeSaleProducts::find($wholesaleProductId);
    
//     if (!$wholesaleProduct) {
//         return response()->json(['stock' => 0]);
//     }
    
//     // Find stock using the actual product_id
//     $stockQuery = ManageBranchProductStock::where('branch_id', $branchId)
//         ->where('product_id', $wholesaleProduct->product_id);

//     if ($variationType) {
//         $stockQuery->where('variation_type', $variationType);
//     }

//     $stock = $stockQuery->first();

//     return response()->json(['stock' => $stock ? $stock->current_stock : 0]);
// }
    public function branchProductStock(Request $request)
    {
       
        Log::info("request", ['request' => $request->all()]);
        $branchId = $request->input('branch_id');
        $productId = $request->input('product_id');
        $variationType = $request->input('variation_type'); 

        $stockQuery = ManageBranchProductStock::where('branch_id', $branchId)
            ->where('product_id', $productId);

        $stock = $variationType
            ? $stockQuery->get()->first(function ($row) use ($variationType) {
                return $this->variantMatcher->matches($variationType, $row->variation_type)
                    || $this->variantMatcher->matches($variationType, $row->variation_key);
            })
            : $stockQuery->where(function ($query) {
                $query->whereNull('variation_type')->orWhere('variation_type', '');
            })->first();

        return response()->json(['stock' => $stock ? $stock->current_stock : 0]);
    }

    public function deliveryStore(Request $request)
    {
        Log::info("📥 DELIVERY REQUEST", ['request' => $request->all()]);

        $request->validate([
            'product_id'         => 'required|exists:products,id',
            'confirmed_order_id' => 'required|exists:wholesale_confirm_orders,id',
            'branch_id'          => 'required|exists:branches,id',
            'quantity_sent'      => 'required|integer|min:1',
            'delivery_status'    => 'sometimes|in:partials,delivered',
            'note'               => 'nullable|string',
            'variation_type'     => 'nullable|string',
            'serial_csv'         => 'nullable|mimes:csv,txt',
        ]);

        $product = Product::findOrFail($request->product_id);
        $isTraceable = (int)$product->is_traceable === 1;
        $variationRequested = $request->filled('variation_type') ? trim($request->variation_type) : null;
        $qtyToSend = (int) $request->quantity_sent;
        $serialCsvPath = null;
        $serials = [];

        if ($isTraceable) {
            if (!$request->hasFile('serial_csv')) {
                Toastr::error(translate('Serial CSV is required for traceable products.'));
                return redirect()->back()->withInput();
            }

            $errors = [];
            $serials = $this->parseCsvSerials($request->file('serial_csv'), $errors);
            if (count($serials) !== $qtyToSend) {
                $errors[] = "CSV must contain exactly {$qtyToSend} serials.";
            }

            if (!empty($errors)) {
                $csvName = $this->generateErrorCsv($errors);
                session()->forget('error_csv');
                session()->flash('error_csv', $csvName);
                session()->flash('error_count', count($errors));
                Toastr::error('Serial validation failed. Download error report.');
                return redirect()->back()->withInput();
            }

            $serialCsvPath = $request->file('serial_csv')->store('wholesale_delivery_csv', 'public');
        } elseif ($request->hasFile('serial_csv')) {
            $serialCsvPath = $request->file('serial_csv')->store('wholesale_delivery_csv', 'public');
        }

        DB::beginTransaction();
        try {
            $confirmItemQuery = WholesaleConfirmOrderItem::where('confirmed_order_id', $request->confirmed_order_id)
                ->where('product_id', $request->product_id)
                ->lockForUpdate();

            $confirmItem = $variationRequested
                ? $confirmItemQuery->get()->first(function ($item) use ($variationRequested) {
                    return $this->variantMatcher->matches($variationRequested, $item->product_variation_type);
                })
                : $confirmItemQuery->first();

            if (!$confirmItem) {
                throw new \Exception('Requested order item/variation not found.');
            }

            $confirmOrder = $confirmItem->confirmOrder;

            if ($confirmOrder->delivery_status === 'delivered') {
                throw new \RuntimeException('This order is already fully delivered.');
            }

            if ($qtyToSend > (int)$confirmItem->remaining) {
                throw new \RuntimeException('Quantity exceeds remaining quantity for this order item.');
            }

            if ($isTraceable && !empty($serials)) {
                $warranties = Warranty::query()
                    ->whereIn('serial_number', $serials)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('serial_number');

                $serialErrors = [];
                foreach ($serials as $serial) {
                    $warranty = $warranties->get($serial);
                    if (!$warranty) {
                        $serialErrors[] = "Serial {$serial} not found in system.";
                        continue;
                    }
                    if (!empty($warranty->distributor_id)) {
                        $serialErrors[] = "Serial {$serial} is already delivered to a wholesaler.";
                        continue;
                    }
                    if (!empty($warranty->branch_id) && (int)$warranty->branch_id !== (int)$request->branch_id) {
                        $serialErrors[] = "Serial {$serial} does not belong to selected branch.";
                    }
                }

                if (!empty($serialErrors)) {
                    $csvName = $this->generateErrorCsv($serialErrors);
                    session()->forget('error_csv');
                    session()->flash('error_csv', $csvName);
                    session()->flash('error_count', count($serialErrors));
                    throw new \RuntimeException('Serial validation failed. Download error report.');
                }
            }

            $stockMutation = $this->inventoryMutationService->decreaseForPosLine(
                productId: (int)$request->product_id,
                qty: $qtyToSend,
                variant: $variationRequested,
                branchId: (int)$request->branch_id,
                sellerId: null,
                referenceId: (int)$request->confirmed_order_id,
                context: 'Wholesale Delivery',
                stockReason: StockReason::WHOLESALE_DELIVERY
            );

            if (!($stockMutation['status'] ?? false)) {
                throw new \RuntimeException($stockMutation['message'] ?? 'Not enough stock in selected branch.');
            }

            $resolvedBranchId = isset($stockMutation['branchId']) && (int)$stockMutation['branchId'] > 0
                ? (int)$stockMutation['branchId']
                : (int)$request->branch_id;

            $delivery = WholesaleOrderDelivery::create([
                'order_id' => $confirmOrder->order_id,
                'confirmed_order_id' => $confirmOrder->id,
                'product_id' => $request->product_id,
                'branch_id' => $resolvedBranchId,
                'quantity_sent' => $qtyToSend,
                'product_variation_type' => $variationRequested,
                'serial_csv_path' => $serialCsvPath,
                'note' => $request->note,
                'delivery_date' => now(),
            ]);

            if ($isTraceable && !empty($serials)) {
                Warranty::query()
                    ->whereIn('serial_number', $serials)
                    ->update([
                        'distributor_id' => $confirmOrder->wholesaler_id,
                        'branch_id' => null,
                    ]);

                $historyRows = array_map(function ($serial) use ($delivery, $request, $confirmOrder) {
                    return [
                        'wholesale_delivery_id' => $delivery->id,
                        'serial_number' => $serial,
                        'from_branch_id' => $request->branch_id,
                        'to_branch_id' => null,
                        'distributor_id' => $confirmOrder->wholesaler_id,
                        'transfer_type' => 'branch_to_wholesale',
                        'transferred_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $serials);
                SerialTransferHistory::insert($historyRows);
            }

            $confirmItem->update([
                'quantity_sent' => $confirmItem->quantity_sent + $qtyToSend,
                'remaining' => max(0, $confirmItem->remaining - $qtyToSend),
            ]);

            $hasRemaining = WholesaleConfirmOrderItem::query()
                ->where('confirmed_order_id', $confirmOrder->id)
                ->where('remaining', '>', 0)
                ->exists();
            $newStatus = $hasRemaining ? 'partials' : 'delivered';
            $confirmOrder->update(['delivery_status' => $newStatus]);

            if (!empty($confirmOrder->purchase_order_no)) {
                WholesalePurchaseOrder::query()
                    ->where('purchase_order_no', $confirmOrder->purchase_order_no)
                    ->update(['status' => $newStatus]);
            }

            DB::commit();

            Toastr::success('Delivery recorded successfully.');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DeliveryStore error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Toastr::error('Error: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    } 

    // public function deliveryStore(Request $request)
    // {
    //     Log::info("request", ['request' => $request->all()]);

    //     $request->validate([
    //         'product_id'         => 'required|exists:products,id',
    //         'confirmed_order_id' => 'required|exists:wholesale_confirm_orders,id',
    //         'branch_id'          => 'required|exists:branches,id',
    //         'quantity_sent'      => 'required|integer|min:1',
    //         'delivery_status'    => 'sometimes|in:partials,delivered',
    //         'note'               => 'nullable|string',
    //         // 'variation_type' is optional
    //     ]);

    //     $product = Product::findOrFail($request->product_id);
    //     $variationRequested = $request->filled('variation_type') ? trim($request->variation_type) : null;
    //     $qtyToSend = (int) $request->quantity_sent;

    //     DB::beginTransaction();
    //     try {
    //         // Find the confirm item — if variation was provided, find the matching confirm item for that variation
    //         $confirmItem = WholesaleConfirmOrderItem::where('confirmed_order_id', $request->confirmed_order_id)
    //             ->where('product_id', $request->product_id)
    //             ->when($variationRequested, function ($q) use ($variationRequested) {
    //                 return $q->where('product_variation_type', $variationRequested);
    //             })
    //             ->firstOrFail();

    //         Log::info('Confirm item found', ['id' => $confirmItem->id, 'product_id' => $confirmItem->product_id, 'variation' => $confirmItem->product_variation_type]);

    //         $confirmOrder = $confirmItem->confirmOrder;

    //         if ($confirmOrder->delivery_status === 'delivered') {
    //             Toastr::error('This order is already fully delivered.');
    //             return redirect()->back();
    //         }

    //         // Branch stock: build query and require matching variation only if variation provided
    //         $stockQuery = ManageBranchProductStock::where('branch_id', $request->branch_id)
    //             ->where('product_id', $request->product_id);

    //         if ($variationRequested) {
    //             $stockQuery->where('variation_type', $variationRequested);
    //         }

    //         $stock = $stockQuery->first();

    //         if (!$stock) {
    //             // If user requested variation but branch has no variation row, show error.
    //             if ($variationRequested) {
    //                 Toastr::error('No stock record found in selected branch for this product variation.');
    //                 return redirect()->back();
    //             } else {
    //                 Toastr::error('No stock record found in selected branch for this product.');
    //                 return redirect()->back();
    //             }
    //         }

    //         Log::info('Branch stock found', ['branch_stock_id' => $stock->id, 'current_stock' => $stock->current_stock, 'variation' => $stock->variation_type ?? null]);

    //         if ($stock->current_stock < $qtyToSend) {
    //             Toastr::error('Not enough stock in selected branch for this selection.');
    //             return redirect()->back();
    //         }

    //         $csvPath = null;
    //         $serials = [];

    //         // Traceability / CSV logic (unchanged)
    //         if ($product->is_traceable == 1) {
    //             $request->validate([
    //                 'serial_csv' => 'required|mimes:csv,txt'
    //             ]);

    //             $csvFile = $request->file('serial_csv');
    //             $errors = [];
    //             $serials = $this->parseCsvSerials($csvFile, $errors);

    //             if (count($serials) !== $qtyToSend) {
    //                 $errors[] = "CSV must contain exactly {$qtyToSend} serials.";
    //             }

    //             if (!empty($errors)) {
    //                 $csvName = $this->generateErrorCsv($errors);
    //                 session()->forget('error_csv');
    //                 session()->flash('error_csv', $csvName);
    //                 session()->flash('error_count', count($errors));
    //                 Toastr::error('Serial validation failed. Download error report.');
    //                 return redirect()->back()->withInput();
    //             }

    //             $warranties = Warranty::whereIn('serial_number', $serials)->get()->keyBy('serial_number');

    //             $rowErrors = [];
    //             foreach ($serials as $s) {
    //                 if (!isset($warranties[$s])) {
    //                     $rowErrors[] = "Serial {$s} not found in system.";
    //                     continue;
    //                 }

    //                 $w = $warranties[$s];

    //                 if ($w->distributor_id) {
    //                     $rowErrors[] = "Serial {$s} already delivered to wholesaler.";
    //                 } elseif ($w->branch_id && $w->branch_id != $request->branch_id) {
    //                     $rowErrors[] = "Serial {$s} does not belong to selected branch.";
    //                 }
    //             }

    //             if (!empty($rowErrors)) {
    //                 $csvName = $this->generateErrorCsv($rowErrors);
    //                 session()->forget('error_csv');
    //                 session()->flash('error_csv', $csvName);
    //                 session()->flash('error_count', count($rowErrors));
    //                 Toastr::error('Serial validation failed. Download error report.');
    //                 return redirect()->back()->withInput();
    //             }

    //             $csvPath = $csvFile->store('stock_transfers', 'public');
    //         }

    //         // 1) Deduct branch stock (variation-specific row if variation given)
    //         $stock->decrement('current_stock', $qtyToSend);
    //         Log::info('Branch stock decremented', ['branch_stock_id' => $stock->id, 'deducted' => $qtyToSend]);

    //         if ($variationRequested) {
    //             $rawVariations = $product->variation;
    //             Log::info('Product raw variation', ['product_id' => $product->id, 'raw' => $rawVariations]);
    //             $variations = [];

    //             if (is_string($rawVariations)) {
    //                 $decoded = json_decode($rawVariations, true);
    //                 if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
    //                     $variations = $decoded;
    //                 } else {
    //                     // invalid json
    //                     Log::error('Product variation JSON invalid', ['product_id' => $product->id, 'raw' => $rawVariations]);
    //                     throw new \Exception('Invalid variation format stored on product.');
    //                 }
    //             } elseif (is_array($rawVariations)) {
    //                 $variations = $rawVariations;
    //             } else {
    //                 Log::warning('Product variation empty or invalid type', ['product_id' => $product->id, 'type' => gettype($rawVariations)]);
    //             }

    //             $found = false;
    //             foreach ($variations as $idx => $var) {
    //                 // ensure array structure
    //                 if (!is_array($var) || !isset($var['type'])) continue;

    //                 $varType = trim((string)$var['type']);
    //                 if ($varType === $variationRequested) {
    //                     $found = true;
    //                     $currentVarQty = isset($var['qty']) ? (int)$var['qty'] : 0;
    //                     Log::info('Matched variation inside product', ['product_id' => $product->id, 'variation' => $varType, 'current_qty' => $currentVarQty]);

    //                     if ($currentVarQty < $qtyToSend) {
    //                         // rollback earlier branch decrement as we cannot proceed
    //                         DB::rollBack();
    //                         Toastr::error('Variation quantity is not enough in product for requested delivery.');
    //                         return redirect()->back();
    //                     }

    //                     // decrement variation qty
    //                     $variations[$idx]['qty'] = $currentVarQty - $qtyToSend;
    //                     break;
    //                 }
    //             }

    //             if (!$found) {
    //                 // rollback branch decrement too
    //                 DB::rollBack();
    //                 Toastr::error('Requested variation not found in product record.');
    //                 return redirect()->back();
    //             }

    //             // Save updated variations back to product
    //             $product->variation = json_encode($variations);
    //             // decrement overall product current_stock as well (consistency)
    //             $product->current_stock = max(0, (int)$product->current_stock - $qtyToSend);
    //             $product->save();

    //             Log::info('Product variation & overall stock updated', ['product_id' => $product->id, 'deducted' => $qtyToSend]);
    //         } else {
    //             // No variation requested: just decrement product current_stock
    //             if ((int)$product->current_stock < $qtyToSend) {
    //                 // rollback branch decrement
    //                 DB::rollBack();
    //                 Toastr::error('Not enough overall product stock to fulfill this delivery.');
    //                 return redirect()->back();
    //             }

    //             $product->decrement('current_stock', $qtyToSend);
    //             Log::info('Product current_stock decremented (no variation)', ['product_id' => $product->id, 'deducted' => $qtyToSend]);
    //         }
    //         $delivery = WholesaleOrderDelivery::create([
    //             'order_id' => $confirmOrder->order_id,
    //             'confirmed_order_id' => $confirmOrder->id,
    //             'product_id' => $request->product_id,
    //             'branch_id' => $request->branch_id,
    //             'quantity_sent' => $qtyToSend,
    //             'product_variation_type' => $variationRequested ?? null,
    //             'note' => $request->note,
    //             'serial_csv_path' => $csvPath,
    //             'delivery_date' => now(),
    //         ]);

    //         Log::info('Delivery created', ['delivery_id' => $delivery->id]);
    //         $totalSent = $confirmItem->quantity_sent + $qtyToSend;
    //         $remaining = max(0, $confirmItem->remaining - $qtyToSend);

    //         $confirmItem->update([
    //             'quantity_sent' => $totalSent,
    //             'remaining' => $remaining,
    //         ]);

    //         Log::info('Confirm item updated', ['confirm_item_id' => $confirmItem->id, 'quantity_sent' => $totalSent, 'remaining' => $remaining]);

    //         // 5) Update statuses
    //         $newStatus = $request->delivery_status === 'delivered' ? 'delivered' : 'partials';
    //         $confirmOrder->update(['delivery_status' => $newStatus]);

    //         $purchaseOrder = WholesalePurchaseOrder::where('purchase_order_no', $confirmOrder->purchase_order_no)->first();
    //         if ($purchaseOrder) {
    //             $purchaseOrder->update(['status' => $newStatus]);
    //         }

    //         // 6) Warranty transfers for serials (unchanged)
    //         if ($product->is_traceable == 1 && !empty($serials)) {
    //             $distributorId = $confirmOrder->wholesaler_id;

    //             Warranty::whereIn('serial_number', $serials)
    //                 ->update([
    //                     'distributor_id' => $distributorId,
    //                     'branch_id' => null,
    //                 ]);

    //             $history = array_map(fn($s) => [
    //                 'wholesale_delivery_id' => $delivery->id,
    //                 'serial_number' => $s,
    //                 'from_branch_id' => $request->branch_id,
    //                 'distributor_id' => $distributorId,
    //                 'transfer_type' => 'branch_to_wholesale',
    //                 'transferred_at' => now(),
    //             ], $serials);

    //             SerialTransferHistory::insert($history);
    //         }

    //         DB::commit();

    //           $title   = "Delivery added";
    //             $message ="Your purchase order delivery added to the system";
    //             $link    = route('home');

    //             $recipients = [
    //                 ['type' => 'customer', 'id' => $confirmOrder->wholesaler_id],
    //             ];

    //             $this->notificationRepo->notifyRecipients(
    //                 $confirmOrder->wholesaler_id,
    //                 User::class,
    //                 $title,
    //                 $message,
    //                 $link,
    //                 $recipients
    //             );
    //         Toastr::success('Delivery recorded successfully.');
    //         return redirect()->back();
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         session()->flash('error', 'Error: ' . $e->getMessage());
    //         Log::error('DeliveryStore error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    //         Toastr::error('Error: ' . $e->getMessage());
    //         return redirect()->back()->withInput();
    //     }
    // }

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

        return $filename;
    }

    public function showTrackingPage($id)
    {
        $dataLimit = getWebConfig('pagination_limit') ?? 10;

        $order = WholesaleConfirmOrder::with([
            'wholeseller.wholesalerBusiness',
            'payments',
            'deliveries'
        ])->findOrFail($id);

        $remaining = $order->payments()->latest()->value('remaining_amount') ?? $order->final_price;

        $branches = Branch::all();

        $deliveries = WholesaleConfirmOrderItem::with(['product'])
            ->where('confirmed_order_id', $id)
            ->get();

        $deliveryLogs = WholesaleOrderDelivery::with('product', 'branch')
            ->where('confirmed_order_id', $id)
            ->latest()
            ->paginate($dataLimit);

        return view(WholeSaler::CONFIRM_TRACKING_PAGE[VIEW], compact(
            'order',
            'remaining',
            'branches',
            'deliveries',
            'deliveryLogs'
        ));
    }


    public function getOrderStatusHistory($order): View
    {
        $histories = Activity::where('order_id', $order)
            ->select('event', 'properties', 'created_at')
            ->orderBy('created_at', 'asc')
            ->get();
        return view(WholeSaler::ORDER_HISTORY[VIEW], compact('histories'));
    }

    public function downloadCsv($deliveryId)
    {
        $item = WholesaleOrderDelivery::findOrFail($deliveryId);

        if (!$item->serial_csv_path || !Storage::disk('public')->exists($item->serial_csv_path)) {
            abort(404);
        }
        session()->forget(['error_csv', 'error_count']);
        return Storage::disk('public')->download($item->serial_csv_path, 'transfer_report.csv');
    }
}
