<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WholeSalerBusiness;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\Web\WholesaleBusinessAddRequest;
use App\Traits\FileManagerTrait;
use App\Models\Product;
use App\Models\WholeSaleProducts;
use App\Models\PriceRange;
use App\Models\WholesalePurchaseOrder;
use App\Models\WholesaleConfirmOrder;
use App\Models\WholesaleOrderDelivery;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\WholesaleQuotation;
use App\Models\WholesalePurchaseOrderItem;
use App\Models\QuotationMeta;
use Illuminate\Support\Facades\Auth;
use App\Models\WholesaleProductPriceRange;
use App\Models\WholesaleConfirmOrderItem;
use App\Domain\Stock\Support\VariantMatcher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Events\WholesalerEmailEvent;
use App\Models\Lead;
use App\Models\Deal;
use Illuminate\Validation\Rule;


class WholesaleController extends Controller
{
    //
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }
    public function index(): View
    {
        $SpecificBusiness = WholeSalerBusiness::where('wholesaler_id', auth('customer')->user()->id)->get();
        return view(VIEW_FILE_NAMES['add_business_details'], compact('SpecificBusiness'));
    }
    public function SaveBusinessInfo(WholesaleBusinessAddRequest $request): RedirectResponse
    {
        $user = auth('customer')->user();

        // Check if user already has business information
        $existingBusiness = WholeSalerBusiness::where('wholesaler_id', $user->id)->first();
        if ($existingBusiness) {
            Toastr::warning(translate('You have already submitted your business information.'));
            return redirect()->route('business-profile');
        }

        WholeSalerBusiness::create([
            'wholesaler_id'   => $user->id,
            'company_name'   => $request->company_name,
            'trade_name'   => $request->trade_name,
            'registration_number' => $request->registration_number,
            'tax_id'   => $request->tax_id,
            'register_copy'  => $request->register_copy ? $this->fileUpload(dir: 'register_copies/', format: $request->register_copy->getClientOriginalExtension(), file: $request->register_copy) : null,
            'tax_card_copy'  => $request->tax_card_copy ? $this->fileUpload(dir: 'tax_cards/', format: $request->tax_card_copy->getClientOriginalExtension(), file: $request->tax_card_copy) : null,
            'vat_number'     => $request->vat_number,
            'vat_register_copy' => $request->vat_register_copy ? $this->fileUpload(dir: 'vat_copies/', format: $request->vat_register_copy->getClientOriginalExtension(), file: $request->vat_register_copy) : null
        ]);

        $data = [
            'userName' => $user->f_name,
            'templateName' => 'registration',
            'subject' => translate('Business Registered'),
            'title' => translate('Welcome to our wholesale program!'),
            'message' => translate('Thank you for registering your business.'),
        ];

        // event(new WholesalerEmailEvent($user->email, $data));
        Toastr::success(translate('Your business information submitted successfully. It will be reviewed by admin.'));
        return redirect()->route('business-profile');
    }


    public function showProduct($slug)
    {
        $baseProduct = Product::where('slug', $slug)->firstOrFail();

        // Fetch all wholesale variations
        $wholesaleProducts = WholeSaleProducts::where('product_id', $baseProduct->id)
            ->with(['product', 'price_list_for_user'])
            ->get();

        if ($wholesaleProducts->isEmpty()) {
            return redirect()->back()->with('error', translate('No wholesale variations found for this product.'));
        }

        // MOQ override logic
        $moqOverride = false;
        if (auth()->guard('customer')->check()) {
            $user = auth()->guard('customer')->user();
            if ($user->user_type == 1 && $user->moq_override_enabled) {
                $moqOverride = true;
            }
        }

        // Extract filtered ranges for each variation
        $variationsWithRanges = [];
        foreach ($wholesaleProducts as $wp) {
            $filteredRange = $wp->price_list_for_user->first();

            if ($filteredRange) {
                $variationsWithRanges[] = [
                    'wholesaleProduct' => $wp,
                    'filteredRange' => $filteredRange,
                    'variation_key' => $wp->resolved_variation_key,
                    'variation_type' => $wp->resolved_variation_type,
                    'variation_display' => $wp->resolved_variation_display,
                ];
            }
        }

        return view(VIEW_FILE_NAMES['wholesale_products_details'], compact(
            'baseProduct',
            'variationsWithRanges',
            'moqOverride'
        ));
    }

    // public function createPurchaseOrder(Request $request)
    // {
    //     Log::info('Starting createPurchaseOrder', ['user_id' => auth('customer')->id() ?? null]);

    //     try {
    //         $request->validate([
    //             'products' => 'required|array|min:1',
    //             'products.*.id' => 'required|exists:products,id',
    //             'products.*.quantity' => 'required|integer|min:1',
    //         ]);
    //         Log::info('Validation passed', ['products' => $request->products]);
    //     } catch (\Exception $e) {
    //         Log::error('Validation failed', ['error' => $e->getMessage()]);
    //         return back()->with('error', $e->getMessage());
    //     }

    //     $wholeseller = auth('customer')->user();
    //     $tier = $wholeseller->tier;

    //     try {
    //         $order = DB::transaction(function () use ($wholeseller, $tier) {
    //             $lastOrderId = DB::table('wholesale_purchase_orders')
    //                 ->lockForUpdate()
    //                 ->max('order_id');

    //             $nextOrderId = $lastOrderId ? $lastOrderId + 1 : 10000;

    //             Log::info('Creating new wholesale order', ['next_order_id' => $nextOrderId]);

    //             return WholesalePurchaseOrder::create([
    //                 'wholeseller_id'   => $wholeseller->id,
    //                 'wholeseller_tier' => $tier,
    //                 'status'           => 'pending',
    //                 'order_id'         => $nextOrderId,
    //             ]);
    //         });

    //         Log::info('Order created successfully', ['order_id' => $order->id]);
    //     } catch (\Exception $e) {
    //         Log::error('Order creation failed', ['error' => $e->getMessage()]);
    //         return back()->with('error', 'Order creation failed. Try again.');
    //     }

    //     foreach ($request->products as $productData) {
    //         try {
    //             Log::info('Processing product', ['product_id' => $productData['id'], 'quantity' => $productData['quantity']]);

    //             $wholesaleProduct = WholeSaleProducts::where('product_id', $productData['id'])
    //                 ->when(!empty($productData['variation_type']), function ($q) use ($productData) {
    //                     $q->where('variation_type', $productData['variation_type']);
    //                 })
    //                 ->first();

    //             if (!$wholesaleProduct) {
    //                 Log::warning('Wholesale product not found', ['product_id' => $productData['id']]);
    //                 return back()->with('error', 'Wholesale product not found.');
    //             }

    //             $priceRange = WholesaleProductPriceRange::where('wholesale_id', $wholesaleProduct->id)
    //                 ->where('tier', $tier)
    //                 ->where('min_qty', '<=', $productData['quantity'])
    //                 ->orderByDesc('min_qty')
    //                 ->first();

    //             if (!$priceRange && $wholeseller->moq_override_enabled) {
    //                 Log::info('MOQ override active, fetching first available price range', ['product_id' => $productData['id']]);
    //                 $priceRange = WholesaleProductPriceRange::where('wholesale_id', $wholesaleProduct->id)
    //                     ->where('tier', $tier)
    //                     ->orderBy('min_qty', 'asc')
    //                     ->first();
    //             }

    //             if (!$priceRange) {
    //                 Log::warning('No valid price range', ['product_id' => $productData['id'], 'quantity' => $productData['quantity']]);
    //                 return back()->with('error', 'No valid price found for the selected quantity and tier.');
    //             }

    //             $pricePerUnit = $priceRange->price_per_piece;

    //             $item = WholesalePurchaseOrderItem::create([
    //                 'wholesale_order_id' => $order->id,
    //                 'product_id'         => $productData['id'],
    //                 'product_variation_type' => $productData['variation_type'] ?? null,
    //                 'product_quantity'   => $productData['quantity'],
    //                 'tax'                => $productData['tax'],
    //                 'base_price'         => $pricePerUnit,
    //                 'final_price'        => $pricePerUnit * $productData['quantity'],
    //                 'price_range_id'     => $priceRange->id,
    //             ]);

    //             Log::info('Order item created', ['item_id' => $item->id]);
    //         } catch (\Exception $e) {
    //             Log::error('Item save failed', ['error' => $e->getMessage(), 'product' => $productData]);
    //             return back()->with('error', 'Item save failed: ' . $e->getMessage());
    //         }
    //     }

    //     try {
    //         $userWithBusiness = \App\Models\User::with('wholesalerBusiness')->find($wholeseller->id);

    //         if ($userWithBusiness && $userWithBusiness->wholesalerBusiness) {
    //             $companyId = $userWithBusiness->wholesalerBusiness->id;

    //             Lead::create([
    //                 'party_type'  => 'wholesale',
    //                 'company_id'  => $companyId,
    //                 'source_id'   => $order->id,
    //                 'po_id'       => $order->id,
    //                 'status'      => 'new',
    //                 'priority'    => 'high',
    //                 'employee_id' => 1,
    //             ]);

    //             Log::info('Lead created for wholesale order', ['po_id' => $order->id, 'company_id' => $companyId]);
    //         } else {
    //             Log::warning('User does not have a wholesaler business', ['user_id' => $wholeseller->id]);
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Lead creation failed', ['error' => $e->getMessage()]);
    //     }

    //     $this->remove_all_cart();
    //     Log::info('Cart cleared and purchase success view returned', ['order_id' => $order->id]);

    //     return view(VIEW_FILE_NAMES['purches_success']);
    // }

    public function createPurchaseOrder(Request $request)
    {
        Log::info('Starting createPurchaseOrder', ['user_id' => auth('customer')->id() ?? null]);

        $wholeseller = auth('customer')->user();
        $activeWholesaleGroupId = $this->getOrCreateCartGroupId($wholeseller);
        $cartItems = $this->getWholesaleCartQuery($wholeseller->id, $activeWholesaleGroupId)->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        $tier = $wholeseller->tier;

        try {
            $order = DB::transaction(function () use ($wholeseller, $tier, $cartItems) {
                $lastOrderId = DB::table('wholesale_purchase_orders')
                    ->lockForUpdate()
                    ->max('order_id');

                $nextOrderId = $lastOrderId ? $lastOrderId + 1 : 10000;

                Log::info('Creating new wholesale order', ['next_order_id' => $nextOrderId]);

                $order = WholesalePurchaseOrder::create([
                    'wholeseller_id'   => $wholeseller->id,
                    'wholeseller_tier' => $tier,
                    'status'           => 'pending',
                    'order_id'         => $nextOrderId,
                ]);
                $createdItemsCount = 0;
                foreach ($cartItems as $cartItem) {
                    $variationData = $this->extractVariationDataFromCart($cartItem);
                    $wholesaleProduct = $this->findWholesaleProductByVariation(
                        productId: $cartItem->product_id,
                        variationInput: $variationData['variation_key'] ?? null,
                        variationType: $variationData['variation_type'] ?? null
                    );

                    if (!$wholesaleProduct) {
                        throw new \RuntimeException("Wholesale product not found for product #{$cartItem->product_id}.");
                    }

                    $priceRange = WholesaleProductPriceRange::query()
                        ->where('wholesale_id', $wholesaleProduct->id)
                        ->where('tier', $tier)
                        ->where('min_qty', '<=', $cartItem->quantity)
                        ->where(function ($query) use ($cartItem) {
                            $query->whereNull('max_qty')
                                ->orWhere('max_qty', 0)
                                ->orWhere('max_qty', '>=', $cartItem->quantity);
                        })
                        ->orderByDesc('min_qty')
                        ->first();

                    if (!$priceRange && $wholeseller->moq_override_enabled) {
                        $priceRange = WholesaleProductPriceRange::query()
                            ->where('wholesale_id', $wholesaleProduct->id)
                            ->where('tier', $tier)
                            ->orderBy('min_qty')
                            ->first();
                    }

                    if (!$priceRange) {
                        throw new \RuntimeException("No valid price range found for product #{$cartItem->product_id}.");
                    }

                    $pricePerUnit = (float)$priceRange->price_per_piece;
                    WholesalePurchaseOrderItem::create([
                        'wholesale_order_id' => $order->id,
                        'product_id'         => $cartItem->product_id,
                        'product_variation_type' => $variationData['variation_type'] ?? $variationData['variant'],
                        'product_quantity'   => $cartItem->quantity,
                        'tax'                => $cartItem->tax,
                        'base_price'         => $pricePerUnit,
                        'final_price'        => $pricePerUnit * $cartItem->quantity,
                        'price_range_id'     => $priceRange->id,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                    $createdItemsCount++;
                }

                if ($createdItemsCount < 1) {
                    throw new \RuntimeException('No valid wholesale items were found to place this order.');
                }

                try {
                    $userWithBusiness = \App\Models\User::with('wholesalerBusiness')->find($wholeseller->id);
                    if ($userWithBusiness && $userWithBusiness->wholesalerBusiness) {
                        Lead::create([
                            'party_type'  => 'wholesale',
                            'company_id'  => $userWithBusiness->wholesalerBusiness->id,
                            'source_id'   => $order->id,
                            'po_id'       => $order->id,
                            'status'      => 'new',
                            'priority'    => 'high',
                            'employee_id' => 1,
                        ]);
                    }
                } catch (\Throwable $leadException) {
                    Log::warning('Lead creation skipped for wholesale order', [
                        'order_id' => $order->id,
                        'error' => $leadException->getMessage(),
                    ]);
                }

                return $order;
            }, 3);

            Log::info('Order created successfully', ['order_id' => $order->id]);
        } catch (\Throwable $e) {
            Log::error('Order creation failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }

        $this->clearWholesaleCart($wholeseller->id, $activeWholesaleGroupId);
        Log::info('Wholesale cart cleared and purchase success view returned', ['order_id' => $order->id]);

        return view(VIEW_FILE_NAMES['purches_success']);
    }

    // Helper method to extract variation data from cart item
    private function extractVariationDataFromCart($cartItem)
    {
        $variationKey = '';
        $variant = trim((string)($cartItem->variant ?? ''));
        $variationType = $variant !== '' ? $variant : null;

        // Try to get original variation key from choices column
        if ($cartItem->choices) {
            $choices = json_decode($cartItem->choices, true);
            if (isset($choices['original_variation_key'])) {
                $variationKey = $choices['original_variation_key'];
            }
        }

        // If not found in choices, reconstruct from variations column
        if (empty($variationKey) && $cartItem->variations) {
            $variations = json_decode($cartItem->variations, true);
            $parts = [];
            foreach (($variations ?? []) as $key => $value) {
                $normalizedKey = $this->normalizeVariationSegmentKey($key);
                $normalizedValue = trim((string)$value);
                if ($normalizedValue === '') {
                    continue;
                }

                $parts[] = "{$normalizedKey}:{$normalizedValue}";
            }

            if (!empty($parts)) {
                $variationKey = implode(' | ', $parts);
            }
        }

        if ($variationType === null && !empty($variationKey)) {
            $variationType = WholeSaleProducts::extractTypeFromVariationKey($variationKey);
        }

        $variationKey = WholeSaleProducts::normalizeVariationKey($variationType, $variationKey);

        return [
            'variation_key' => $variationKey,
            'variation_type' => $variationType,
            'variant' => $variationType ?? $variant,
        ];
    }

    public function remove_all_cart()
    {
        $customerId = Auth::guard('customer')->id();
        if ($customerId) {
            $activeWholesaleGroupId = session('wholesale_cart_group_id');
            if (!is_string($activeWholesaleGroupId) || !preg_match('/^(WH-|wholesale_)/i', $activeWholesaleGroupId)) {
                $activeWholesaleGroupId = (string)(Cart::query()
                    ->where('customer_id', $customerId)
                    ->where('is_guest', 0)
                    ->where(function ($query) {
                        $query->where('cart_group_id', 'like', 'WH-%')
                            ->orWhere('cart_group_id', 'like', 'wholesale_%');
                    })
                    ->orderByDesc('id')
                    ->value('cart_group_id') ?? '');
            }
            $this->clearWholesaleCart($customerId, $activeWholesaleGroupId !== '' ? $activeWholesaleGroupId : null);
        }

        return redirect()->back();
    }

    public function showInvoice($order_id)
    {
        $customerId = Auth::guard('customer')->id();
        $order = WholesaleQuotation::with(['metas', 'wholeseller', 'items.product'])
            ->where('id', $order_id)
            ->where('wholeseller_id', $customerId)
            ->firstOrFail();

        if ($order->status === 'pending') {
            return back()->with('warning', 'Your order is under review. You will be able to view the quotation once it is approved.');
        }

        return view(VIEW_FILE_NAMES['order_invoice_details'], compact('order'));
    }

    public function orderQuotation($id)
    {
        $customerId = Auth::guard('customer')->id();
        $order = WholesaleQuotation::with(['wholeseller', 'items.product', 'metas'])
            ->where('id', $id)
            ->where('wholeseller_id', $customerId)
            ->firstOrFail();

        if ($order->status === 'pending') {
            Toastr::success(translate('Your order is under review. You will be able to view the quotation once it is approved.'));

            return back();
        }

        return view(VIEW_FILE_NAMES['order_invoice_details'], compact('order'));
    }

    public function viewOrderPage($order_id)
    {
        $customerId = Auth::guard('customer')->id();
        $order = WholesalePurchaseOrder::with(['items.product', 'wholeseller', 'confirmOrder'])
            ->where('id', $order_id)
            ->where('wholeseller_id', $customerId)
            ->firstOrFail();

        return view(VIEW_FILE_NAMES['wholesale_order_view'], compact('order'));
    }

    public function viewWholesaleOrders()
    {
        $userId = auth('customer')->id();
        $orders = WholesalePurchaseOrder::with('items.product')
            ->where('wholeseller_id', $userId)
            ->latest()
            ->paginate(10);

        return view(VIEW_FILE_NAMES['wholesale_orders'], compact('orders'));
    }

    private function generateCartGroupId($user)
    {
        $userId = ($user == null || !empty($user->guest_id)) ? 'guest' : $user->id;

        $sessionGroupId = session('wholesale_cart_group_id');
        if (is_string($sessionGroupId) && preg_match('/^(WH-|wholesale_)/i', $sessionGroupId)) {
            return $sessionGroupId;
        }

        $groupId = 'WH-' . $userId . '-' . Str::random(6) . '-' . time();
        session()->put('wholesale_cart_group_id', $groupId);

        return $groupId;
    }


    // public function addMultipleToWholesaleCart(Request $request)
    // {
    //     $validated = $request->validate([
    //         'variations' => 'required|array|min:1',
    //         'variations.*.product_id' => 'required|exists:products,id',
    //         'variations.*.quantity' => 'required|integer|min:1',
    //         'variations.*.price' => 'required|numeric',
    //         'variations.*.variant' => 'nullable|string',
    //         'variations.*.selected' => 'nullable|in:1',
    //         'variations.*.discount' => 'nullable|numeric',
    //         'variations.*.tax' => 'nullable|numeric',
    //     ]);

    //     $user = auth()->guard('customer')->user();
    //     $cartGroupId = $this->generateCartGroupId($user);

    //     $addedCount = 0;

    //     foreach ($validated['variations'] as $variationData) {

    //         if (!isset($variationData['selected']) || $variationData['selected'] != 1) {
    //             continue;
    //         }

    //         $product = Product::findOrFail($variationData['product_id']);

    //         $cart = new Cart();
    //         $cart->customer_id = $user->id;
    //         $cart->product_id = $product->id;
    //         $cart->quantity = $variationData['quantity'];
    //         $cart->price = $variationData['price'];
    //         $cart->discount = $variationData['discount'] ?? 0;
    //         $cart->tax = $variationData['tax'] ?? 0;
    //         $cart->variant = $variationData['variant'] ?? null;
    //         $cart->name = $product->name;
    //         $cart->tax_model = $product->tax_model;
    //         $cart->slug = $product->slug;
    //         $cart->thumbnail = $product->thumbnail;
    //         $cart->seller_id = ($product->added_by === 'admin') ? 1 : $product->user_id;
    //         $cart->is_guest = 0;
    //         $cart->shipping_cost = 0;
    //         $cart->cart_group_id = $cartGroupId;
    //         $cart->is_checked = 1;
    //         $cart->save();

    //         $addedCount++;
    //     }

    //     if ($addedCount === 0) {
    //         Toastr::warning(translate('Please select at least one variation'));
    //         return redirect()->back();
    //     }

    //     Toastr::success(
    //         translate('Product added to cart!', ['count' => $addedCount])
    //     );

    //     return redirect()->back();
    // }

    public function myOrders()
    {
        $customerId = Auth::guard('customer')->id();

        $orders = WholesalePurchaseOrder::with(['items.product'])
            ->where('wholeseller_id', $customerId)
            ->latest()
            ->paginate(10);

        return view(VIEW_FILE_NAMES['wholesale_orders'], compact('orders'));
    }

    public function allQuotation()
    {
        $customerId = Auth::guard('customer')->id();
        $orders = WholesaleQuotation::with('items')
            ->where('wholeseller_id', $customerId)
            ->latest()
            ->paginate(10);

        return view(VIEW_FILE_NAMES['wholesale_quotations'], compact('orders'));
    }

    public function showOrderOne($id)
    {
        $customerId = Auth::guard('customer')->id();
        $order = WholesalePurchaseOrder::with([
            'items.product',
            'wholeseller',
        ])
            ->where('id', $id)
            ->where('wholeseller_id', $customerId)
            ->firstOrFail();

        return view(VIEW_FILE_NAMES['wholesale_order_view'], compact('order'));
    }

    public function approveQuotation(Request $request, $id)
    {
        $customerId = Auth::guard('customer')->id();
        $request->validate([
            'external_po_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('wholesale_confirm_orders', 'external_po_number')
            ],
            'quotation_file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $id, $customerId) {
                $order = WholesaleQuotation::with('items.product')
                    ->where('id', $id)
                    ->where('wholeseller_id', $customerId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($order->status === 'accepted') {
                    throw new \RuntimeException(translate('This quotation is already approved.'));
                }
                if ($order->status === 'rejected') {
                    throw new \RuntimeException(translate('This quotation is already rejected.'));
                }
                if ($order->status !== 'sent') {
                    throw new \RuntimeException(translate('Only sent quotations can be approved.'));
                }

                $existingConfirmOrder = WholesaleConfirmOrder::query()
                    ->where('wholesaler_id', $customerId)
                    ->where(function ($query) use ($order) {
                        $query->where('quotation_no', $order->quotation_no);
                        if (!empty($order->purchase_order_no)) {
                            $query->orWhere('purchase_order_no', $order->purchase_order_no);
                        }
                    })
                    ->lockForUpdate()
                    ->first();

                if ($existingConfirmOrder) {
                    throw new \RuntimeException(translate('This quotation has already been confirmed.'));
                }

                $filePath = null;
                if ($request->hasFile('quotation_file')) {
                    $file = $request->file('quotation_file');
                    $storedPath = $file->store('wholesale_attachment', 'public');
                    $filePath = basename($storedPath);
                }

                $confirmOrder = WholesaleConfirmOrder::create([
                    'order_id'         => $order->order_id,
                    'purchase_order_no' => $order->purchase_order_no ?? null,
                    'quotation_no'     => $order->quotation_no,
                    'wholesaler_id'    => $order->wholeseller_id,
                    'status'           => 'confirmed',
                    'delivery_status'  => 'pending',
                    'payment_status'   => 'unpaid',
                    'confirmed_at'     => Carbon::now(),
                    'final_price'      => $order->final_price,
                    'attachments'      => $filePath,
                    'external_po_number' => $request->input('external_po_number') ?? null,
                ]);

                foreach ($order->items as $item) {
                    WholesaleConfirmOrderItem::create([
                        'confirmed_order_id' => $confirmOrder->id,
                        'product_id'         => $item->product_id,
                        'product_variation_type' => $item->product_variation_type,
                        'product_quantity'   => $item->product_quantity,
                        'base_price'         => $item->base_price,
                        'tax'                => $item->tax,
                        'final_price'        => $item->final_price,
                        'quantity_sent'      => 0,
                        'remaining'          => $item->product_quantity,
                    ]);
                }

                $order->status = 'accepted';
                $order->save();
                $deal = Deal::where('quotation_id', $order->id)->first();
                if ($deal) {
                    $deal->update([
                        'quotation_status' => 'accepted',
                        'status'           => 'won',
                    ]);
                }
            });
        } catch (\RuntimeException $exception) {
            Toastr::warning($exception->getMessage());
            return redirect()->back();
        } catch (\Throwable $exception) {
            Log::error('Failed to approve wholesale quotation', [
                'quotation_id' => $id,
                'error' => $exception->getMessage(),
            ]);
            Toastr::error(translate('Unable to approve quotation at the moment.'));
            return redirect()->back();
        }

        Toastr::success(translate('Quotation Approved & Delivery Initialized'));

        return redirect()->back();
    }
    public function rejectQuotation($id)
    {
        $customerId = Auth::guard('customer')->id();
        $order = WholesaleQuotation::with('items.product')
            ->where('id', $id)
            ->where('wholeseller_id', $customerId)
            ->firstOrFail();

        if ($order->status === 'accepted') {
            Toastr::error(translate('Approved quotation cannot be rejected.'));
            return redirect()->back();
        }
        if ($order->status === 'rejected') {
            Toastr::warning(translate('This quotation is already rejected.'));
            return redirect()->back();
        }
        if ($order->status !== 'sent') {
            Toastr::warning(translate('Only sent quotations can be rejected.'));
            return redirect()->back();
        }

        $order->status = 'rejected';
        $order->save();

        $deal = Deal::where('quotation_id', $order->id)->first();
        if ($deal) {
            $deal->update([
                'quotation_status' => 'rejected',
                'status'           => 'lost',
            ]);
        }
        Toastr::success(translate('Quotation Rejected '));

        return redirect()->back();
    }

    public function addtowholesalecart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'price_range_id' => 'required|integer|exists:wholesale_price_ranges,id',
            'quantity' => 'required|integer|min:1',
            'variant' => 'required|string',
        ]);

        $user = auth()->guard('customer')->user();
        if (!$user || empty($user->tier)) {
            Toastr::error(translate('Your wholesale tier is not configured.'));
            return redirect()->back();
        }

        $product = Product::findOrFail($validated['product_id']);
        $tier = (string)$user->tier;
        $requestedRange = WholesaleProductPriceRange::query()
            ->where('id', $validated['price_range_id'])
            ->first();

        if (!$requestedRange || (string)$requestedRange->tier !== $tier) {
            Toastr::error(translate('Selected wholesale price range is invalid.'));
            return redirect()->back();
        }

        $wholesaleProduct = WholeSaleProducts::query()
            ->where('id', (int)$requestedRange->wholesale_id)
            ->where('product_id', $product->id)
            ->first();

        if (!$wholesaleProduct) {
            Toastr::error(translate('Selected wholesale price range is invalid.'));
            return redirect()->back();
        }

        $quantity = (int)$validated['quantity'];
        $requestedRange = $this->resolveWholesalePriceRangeForQuantity(
            wholesaleId: (int)$wholesaleProduct->id,
            tier: $tier,
            quantity: $quantity,
            moqOverrideEnabled: (bool)$user->moq_override_enabled,
            preferredRange: $requestedRange
        );

        if (!$requestedRange) {
            Toastr::error(translate('Selected quantity does not match the requested price range.'));
            return redirect()->back();
        }

        $serverTax = is_numeric($product->tax) ? (float)$product->tax : 0;

        $cartGroupId = $this->getOrCreateCartGroupId($user);

        $variationKey = trim((string)($wholesaleProduct->resolved_variation_key ?? $validated['variant']));
        $variationsArray = $this->parseVariationKeyPairs($variationKey);
        $variantForColumn = trim((string)($wholesaleProduct->resolved_variation_type ?? ''));
        if ($variantForColumn === '') {
            $variantForColumn = trim((string)$this->extractVariantFromKey($variationKey));
        }
        if ($variantForColumn === '') {
            $variantForColumn = 'Standard';
        }

        $choicesArray = $this->buildChoicePayloadFromVariationPairs(
            $variationsArray,
            json_decode($product->choice_options ?? '[]', true) ?: []
        );
        if ($variationKey !== '') {
            $choicesArray['original_variation_key'] = $variationKey;
        }

        $color = $this->extractColorValueFromPairs($variationsArray);
        $existingCartLine = $this->findExistingWholesaleCartLine(
            customerId: (int)$user->id,
            cartGroupId: $cartGroupId,
            productId: (int)$product->id,
            variationKey: $variationKey,
            variationType: $variantForColumn
        );

        $finalQuantity = $quantity;
        if ($existingCartLine) {
            $finalQuantity += (int)$existingCartLine->quantity;
            $requestedRange = $this->resolveWholesalePriceRangeForQuantity(
                wholesaleId: (int)$wholesaleProduct->id,
                tier: $tier,
                quantity: $finalQuantity,
                moqOverrideEnabled: (bool)$user->moq_override_enabled,
                preferredRange: null
            );

            if (!$requestedRange) {
                Toastr::error(translate('No valid wholesale price range was found.'));
                return redirect()->back();
            }
        }

        $pricePerUnit = (float)$requestedRange->price_per_piece;

        $sellerId = ($product->added_by == 'admin') ? 1 : $product->user_id;
        $sellerIs = ($product->added_by == 'admin') ? 'admin' : 'seller';
        $shopInfo = ($product->added_by == 'admin') ? 'Dynamic Logic' : null;

        $payload = [
            'customer_id' => $user->id,
            'cart_group_id' => $cartGroupId,
            'product_id' => $product->id,
            'product_type' => 'physical',
            'digital_product_type' => null,
            'color' => $color,
            'choices' => !empty($choicesArray) ? json_encode($choicesArray, JSON_UNESCAPED_SLASHES) : null,
            'variations' => !empty($variationsArray) ? json_encode($variationsArray, JSON_UNESCAPED_SLASHES) : null,
            'variant' => $variantForColumn,
            'quantity' => $finalQuantity,
            'price' => $pricePerUnit,
            'tax' => $serverTax,
            'discount' => 0,
            'installtion_charges' => 0,
            'exchange_qty' => 0,
            'exchange_charges' => 0,
            'tax_model' => $product->tax_model ?? 'exclude',
            'is_checked' => 1,
            'name' => $product->name,
            'thumbnail' => $product->thumbnail,
            'seller_id' => $sellerId,
            'seller_is' => $sellerIs,
            'shop_info' => $shopInfo,
            'shipping_cost' => 0,
            'shipping_type' => 'area_wise',
            'is_guest' => 0,
        ];

        if ($existingCartLine) {
            $existingCartLine->fill($payload);
            $existingCartLine->customer_type = 0;
            $existingCartLine->wholesale_discount = 0.000000;
            $existingCartLine->wholesale_spacial_discount = 0.000000;
            $existingCartLine->save();

            Log::info('Wholesale cart item updated', [
                'cart_id' => $existingCartLine->id,
                'quantity' => $existingCartLine->quantity,
                'variant' => $existingCartLine->variant,
                'variation_key' => $variationKey,
            ]);

            Toastr::success(translate('Cart updated successfully!'));
            return redirect()->back();
        }

        $payload['slug'] = $product->slug . '-' . Str::random(8);

        $cart = new Cart($payload);
        $cart->customer_type = 0;
        $cart->wholesale_discount = 0.000000;
        $cart->wholesale_spacial_discount = 0.000000;
        $cart->save();

        Log::info('Wholesale cart item saved', [
            'cart_id' => $cart->id,
            'quantity' => $cart->quantity,
            'variant' => $cart->variant,
            'variation_key' => $variationKey,
        ]);

        Toastr::success(translate('Added to cart!'));
        return redirect()->back();
    }

    private function resolveWholesalePriceRangeForQuantity(
        int $wholesaleId,
        string $tier,
        int $quantity,
        bool $moqOverrideEnabled,
        ?WholesaleProductPriceRange $preferredRange = null
    ): ?WholesaleProductPriceRange {
        if ($preferredRange && (string)$preferredRange->tier === $tier) {
            $maxQty = is_null($preferredRange->max_qty) ? null : (int)$preferredRange->max_qty;
            $quantityInsidePreferredRange = $quantity >= (int)$preferredRange->min_qty
                && (is_null($maxQty) || $maxQty === 0 || $quantity <= $maxQty);

            if ($quantityInsidePreferredRange) {
                return $preferredRange;
            }
        }

        $matchedRange = WholesaleProductPriceRange::query()
            ->where('wholesale_id', $wholesaleId)
            ->where('tier', $tier)
            ->where('min_qty', '<=', $quantity)
            ->where(function ($query) use ($quantity) {
                $query->whereNull('max_qty')
                    ->orWhere('max_qty', 0)
                    ->orWhere('max_qty', '>=', $quantity);
            })
            ->orderByDesc('min_qty')
            ->first();

        if ($matchedRange) {
            return $matchedRange;
        }

        if ($moqOverrideEnabled) {
            return WholesaleProductPriceRange::query()
                ->where('wholesale_id', $wholesaleId)
                ->where('tier', $tier)
                ->orderBy('min_qty')
                ->first();
        }

        return null;
    }

    private function findExistingWholesaleCartLine(
        int $customerId,
        string $cartGroupId,
        int $productId,
        string $variationKey,
        string $variationType
    ): ?Cart {
        $targetVariationKey = trim((string)$variationKey);
        $targetVariationType = trim((string)$variationType);
        $variantMatcher = app(VariantMatcher::class);

        return Cart::query()
            ->where('customer_id', $customerId)
            ->where('is_guest', 0)
            ->where('cart_group_id', $cartGroupId)
            ->where('product_id', $productId)
            ->orderByDesc('id')
            ->get()
            ->first(function (Cart $cartItem) use ($targetVariationKey, $targetVariationType, $variantMatcher) {
                $choices = json_decode($cartItem->choices ?? '[]', true) ?: [];
                $choiceVariationKey = trim((string)($choices['original_variation_key'] ?? ''));

                if ($targetVariationKey !== '' && $choiceVariationKey !== '') {
                    return $variantMatcher->matches($choiceVariationKey, $targetVariationKey);
                }

                $cartVariationData = $this->extractVariationDataFromCart($cartItem);
                $cartVariationKey = trim((string)($cartVariationData['variation_key'] ?? ''));
                $cartVariationType = trim((string)($cartVariationData['variation_type'] ?? $cartItem->variant ?? ''));

                if ($targetVariationKey !== '') {
                    if ($cartVariationKey !== '') {
                        return $variantMatcher->matches($cartVariationKey, $targetVariationKey);
                    }

                    return $targetVariationType !== '' && $variantMatcher->matches($cartVariationType, $targetVariationType);
                }

                if ($targetVariationType !== '') {
                    return $variantMatcher->matches($cartVariationType, $targetVariationType)
                        || ($cartVariationKey !== '' && $variantMatcher->matches($cartVariationKey, $targetVariationType));
                }

                return $cartVariationKey === '' && $cartVariationType === '';
            });
    }

    // Helper function to extract variant from key
    private function extractVariantFromKey($variationKey)
    {
        $normalizedKey = trim((string)$variationKey);
        if ($normalizedKey === '') {
            return '';
        }

        $resolved = trim((string)(WholeSaleProducts::extractTypeFromVariationKey($normalizedKey) ?? ''));
        if ($resolved !== '') {
            return $resolved;
        }

        if (!str_contains($normalizedKey, '|') && !str_contains($normalizedKey, ':')) {
            return $normalizedKey;
        }

        $pairs = $this->parseVariationKeyPairs($normalizedKey);
        if (!empty($pairs)) {
            return (string)end($pairs);
        }

        return $normalizedKey;
    }

    private function parseVariationKeyPairs(string $variationKey): array
    {
        $normalizedInput = trim($variationKey);
        if ($normalizedInput === '') {
            return [];
        }

        $pairs = [];
        $segments = preg_split('/\|/', $normalizedInput) ?: [];
        foreach ($segments as $segment) {
            $segment = trim((string)$segment);
            if ($segment === '') {
                continue;
            }

            if (str_contains($segment, ':')) {
                [$rawKey, $rawValue] = array_map('trim', explode(':', $segment, 2));
                $key = $this->normalizeVariationSegmentKey($rawKey);
                $value = trim((string)$rawValue);
                if ($key === '' || $value === '') {
                    continue;
                }

                $pairs[$key] = $value;
                continue;
            }

            $pairs['variant'] = $segment;
        }

        return $pairs;
    }

    private function buildChoicePayloadFromVariationPairs(array $variationPairs, array $choiceOptions): array
    {
        $choices = [];
        foreach ($choiceOptions as $choice) {
            $choiceName = trim((string)($choice['name'] ?? ''));
            if ($choiceName === '') {
                continue;
            }

            $choiceTitle = $this->normalizeVariationSegmentKey($choice['title'] ?? '');
            if ($choiceTitle === '') {
                continue;
            }

            if (array_key_exists($choiceTitle, $variationPairs)) {
                $choices[$choiceName] = $variationPairs[$choiceTitle];
            }
        }

        return $choices;
    }

    private function extractColorValueFromPairs(array $variationPairs): ?string
    {
        foreach ($variationPairs as $key => $value) {
            if ($this->normalizeVariationSegmentKey($key) === 'color') {
                $normalizedValue = trim((string)$value);
                return $normalizedValue !== '' ? $normalizedValue : null;
            }
        }

        return null;
    }

    private function normalizeVariationSegmentKey(mixed $key): string
    {
        $normalized = strtolower(trim((string)$key));
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return str_replace(' ', '_', $normalized);
    }

    private function findWholesaleProductByVariation(int $productId, ?string $variationInput, ?string $variationType = null): ?WholeSaleProducts
    {
        $normalizedInput = trim((string)($variationInput ?? ''));
        if (in_array(strtolower($normalizedInput), ['default', '__default__', 'no variation'], true)) {
            $normalizedInput = '';
        }
        $normalizedType = trim((string)($variationType ?? ''));

        if ($normalizedType === '' && $normalizedInput !== '') {
            $normalizedType = trim((string)(WholeSaleProducts::extractTypeFromVariationKey($normalizedInput) ?? ''));
        }

        $normalizedKey = WholeSaleProducts::normalizeVariationKey(
            $normalizedType !== '' ? $normalizedType : null,
            $normalizedInput !== '' ? $normalizedInput : null
        );

        if ($normalizedInput === '' && $normalizedType === '' && empty($normalizedKey)) {
            return WholeSaleProducts::where('product_id', $productId)
                ->where(function ($query) {
                    $query->whereNull('variation_type')->orWhere('variation_type', '');
                })
                ->first();
        }

        $variantMatcher = app(VariantMatcher::class);

        $exactMatch = WholeSaleProducts::where('product_id', $productId)
            ->get()
            ->first(function (WholeSaleProducts $candidate) use ($normalizedInput, $normalizedKey, $normalizedType, $variantMatcher) {
                $candidateType = trim((string)($candidate->resolved_variation_type ?? ''));
                $candidateKey = trim((string)($candidate->resolved_variation_key ?? ''));

                if ($normalizedInput !== '' && (
                    $variantMatcher->matches($normalizedInput, $candidateKey)
                    || $variantMatcher->matches($normalizedInput, $candidateType)
                )) {
                    return true;
                }

                if (!empty($normalizedKey) && (
                    $variantMatcher->matches($normalizedKey, $candidateKey)
                    || $variantMatcher->matches($normalizedKey, $candidateType)
                )) {
                    return true;
                }

                if ($normalizedType !== '' && (
                    $variantMatcher->matches($normalizedType, $candidateType)
                    || $variantMatcher->matches($normalizedType, $candidateKey)
                )) {
                    return true;
                }

                return false;
            });

        if ($exactMatch) {
            return $exactMatch;
        }

        $normalizedInputKey = WholeSaleProducts::normalizeVariationKey(null, $normalizedInput);

        return WholeSaleProducts::where('product_id', $productId)
            ->get()
            ->first(function (WholeSaleProducts $candidate) use ($normalizedKey, $normalizedInputKey, $normalizedType, $variantMatcher) {
                $candidateType = trim((string)($candidate->resolved_variation_type ?? ''));
                $candidateKey = trim((string)($candidate->resolved_variation_key ?? ''));

                if ($normalizedKey && (
                    $variantMatcher->matches($candidateKey, $normalizedKey)
                    || $variantMatcher->matches($candidateType, $normalizedKey)
                )) {
                    return true;
                }

                if ($normalizedInputKey && (
                    $variantMatcher->matches($candidateKey, $normalizedInputKey)
                    || $variantMatcher->matches($candidateType, $normalizedInputKey)
                )) {
                    return true;
                }

                if ($normalizedType !== '' && (
                    $variantMatcher->matches($candidateType, $normalizedType)
                    || $variantMatcher->matches($candidateKey, $normalizedType)
                )) {
                    return true;
                }

                return false;
            });
    }

    private function getOrCreateCartGroupId($user)
    {
        $userId = $user->id;
        $sessionGroupId = session('wholesale_cart_group_id');
        if (is_string($sessionGroupId) && preg_match('/^(WH-|wholesale_)/i', $sessionGroupId)) {
            return $sessionGroupId;
        }

        $existingWholesaleGroup = (string)(Cart::query()
            ->where('customer_id', $userId)
            ->where('is_guest', 0)
            ->where(function ($query) {
                $query->where('cart_group_id', 'like', 'WH-%')
                    ->orWhere('cart_group_id', 'like', 'wholesale_%');
            })
            ->orderByDesc('id')
            ->value('cart_group_id') ?? '');

        if ($existingWholesaleGroup !== '') {
            session()->put('wholesale_cart_group_id', $existingWholesaleGroup);
            return $existingWholesaleGroup;
        }

        $groupId = 'WH-' . $userId . '-' . Str::random(6) . '-' . time();
        session()->put('wholesale_cart_group_id', $groupId);
        return $groupId;
    }

    private function getWholesaleCartQuery(int $customerId, ?string $groupId = null)
    {
        $resolvedGroupId = $groupId;
        if (!is_string($resolvedGroupId) || !preg_match('/^(WH-|wholesale_)/i', $resolvedGroupId)) {
            $sessionGroupId = session('wholesale_cart_group_id');
            if (is_string($sessionGroupId) && preg_match('/^(WH-|wholesale_)/i', $sessionGroupId)) {
                $resolvedGroupId = $sessionGroupId;
            }
        }

        if (!is_string($resolvedGroupId) || !preg_match('/^(WH-|wholesale_)/i', $resolvedGroupId)) {
            return Cart::query()->whereRaw('1 = 0');
        }

        $query = Cart::query()
            ->where('customer_id', $customerId)
            ->where('is_guest', 0)
            ->where(function ($builder) {
                $builder->where('cart_group_id', 'like', 'WH-%')
                    ->orWhere('cart_group_id', 'like', 'wholesale_%');
            });
        $query->where('cart_group_id', $resolvedGroupId);

        return $query;
    }

    private function clearWholesaleCart(int $customerId, ?string $groupId = null): void
    {
        $this->getWholesaleCartQuery($customerId, $groupId)->delete();
        if (is_string($groupId) && preg_match('/^(WH-|wholesale_)/i', $groupId)) {
            if (session('wholesale_cart_group_id') === $groupId) {
                session()->forget('wholesale_cart_group_id');
            }
            return;
        }

        session()->forget('wholesale_cart_group_id');
    }
}
