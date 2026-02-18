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
use Illuminate\Support\Str;
use App\Utils\Helpers;
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
            return redirect()->back()->with('error', 'No wholesale variations found for this product.');
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
                    'variation_key' => $wp->variation_key,
                    'variation_type' => $wp->variation_type,
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

        // Instead of getting products from request, get from cart
        $cartItems = Cart::where('customer_id', $wholeseller->id)
            ->where('is_guest', 0)
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        $tier = $wholeseller->tier;

        try {
            $order = DB::transaction(function () use ($wholeseller, $tier) {
                $lastOrderId = DB::table('wholesale_purchase_orders')
                    ->lockForUpdate()
                    ->max('order_id');

                $nextOrderId = $lastOrderId ? $lastOrderId + 1 : 10000;

                Log::info('Creating new wholesale order', ['next_order_id' => $nextOrderId]);

                return WholesalePurchaseOrder::create([
                    'wholeseller_id'   => $wholeseller->id,
                    'wholeseller_tier' => $tier,
                    'status'           => 'pending',
                    'order_id'         => $nextOrderId,
                ]);
            });

            Log::info('Order created successfully', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Order creation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Order creation failed. Try again.');
        }

        // Process each cart item
        foreach ($cartItems as $cartItem) {
            try {
                Log::info('Processing cart item', [
                    'cart_item_id' => $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity
                ]);

                // Get variation data from cart item
                $variationData = $this->extractVariationDataFromCart($cartItem);

                // Get wholesale product using variation key from cart
                $wholesaleProduct = WholeSaleProducts::where('product_id', $cartItem->product_id)
                    ->where('variation_key', $variationData['variation_key'])
                    ->first();

                if (!$wholesaleProduct) {
                    Log::warning('Wholesale product not found', [
                        'product_id' => $cartItem->product_id,
                        'variation_key' => $variationData['variation_key']
                    ]);
                    continue; // Skip this item instead of returning error
                }

                $priceRange = WholesaleProductPriceRange::where('wholesale_id', $wholesaleProduct->id)
                    ->where('tier', $tier)
                    ->where('min_qty', '<=', $cartItem->quantity)
                    ->orderByDesc('min_qty')
                    ->first();

                if (!$priceRange && $wholeseller->moq_override_enabled) {
                    Log::info('MOQ override active, fetching first available price range', [
                        'product_id' => $cartItem->product_id
                    ]);
                    $priceRange = WholesaleProductPriceRange::where('wholesale_id', $wholesaleProduct->id)
                        ->where('tier', $tier)
                        ->orderBy('min_qty', 'asc')
                        ->first();
                }

                if (!$priceRange) {
                    Log::warning('No valid price range', [
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity
                    ]);
                    continue; // Skip this item
                }

                $pricePerUnit = $priceRange->price_per_piece;

                // Create order item with variation data
                $item = WholesalePurchaseOrderItem::create([
                    'wholesale_order_id' => $order->id,
                    'product_id'         => $cartItem->product_id,
                    'product_variation_type' => $variationData['variant'], // This will be "Left" or "Right"
                    'product_quantity'   => $cartItem->quantity,
                    'tax'                => $cartItem->tax,
                    'base_price'         => $pricePerUnit,
                    'final_price'        => $pricePerUnit * $cartItem->quantity,
                    'price_range_id'     => $priceRange->id,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                Log::info('Order item created', [
                    'item_id' => $item->id,
                    'product_variation_type' => $variationData['variant']
                ]);
            } catch (\Exception $e) {
                Log::error('Item save failed', [
                    'error' => $e->getMessage(),
                    'cart_item' => $cartItem->id
                ]);
                continue; // Skip this item and continue with others
            }
        }

        // Create lead if user has business
        try {
            $userWithBusiness = \App\Models\User::with('wholesalerBusiness')->find($wholeseller->id);

            if ($userWithBusiness && $userWithBusiness->wholesalerBusiness) {
                $companyId = $userWithBusiness->wholesalerBusiness->id;

                Lead::create([
                    'party_type'  => 'wholesale',
                    'company_id'  => $companyId,
                    'source_id'   => $order->id,
                    'po_id'       => $order->id,
                    'status'      => 'new',
                    'priority'    => 'high',
                    'employee_id' => 1,
                ]);

                Log::info('Lead created for wholesale order', [
                    'po_id' => $order->id,
                    'company_id' => $companyId
                ]);
            } else {
                Log::warning('User does not have a wholesaler business', [
                    'user_id' => $wholeseller->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Lead creation failed', ['error' => $e->getMessage()]);
        }

        // Clear cart
        $this->remove_all_cart();
        Log::info('Cart cleared and purchase success view returned', ['order_id' => $order->id]);

        return view(VIEW_FILE_NAMES['purches_success']);
    }

    // Helper method to extract variation data from cart item
    private function extractVariationDataFromCart($cartItem)
    {
        $variationKey = '';
        $variant = $cartItem->variant; // This should be "Left" or "Right"

        // Try to get original variation key from choices column
        if ($cartItem->choices) {
            $choices = json_decode($cartItem->choices, true);
            if (isset($choices['original_variation_key'])) {
                $variationKey = $choices['original_variation_key'];
            }
        }

        // If not found in choices, try to reconstruct from variations column
        if (empty($variationKey) && $cartItem->variations) {
            $variations = json_decode($cartItem->variations, true);
            $parts = [];

            if (isset($variations['color'])) {
                $parts[] = 'unknown:' . $variations['color'];
            }

            if (isset($variations['L/R'])) {
                $parts[] = 'l/r:' . strtolower($variations['L/R']); // Convert to lowercase for key
            }

            if (!empty($parts)) {
                $variationKey = implode(' | ', $parts);
            }
        }

        return [
            'variation_key' => $variationKey,
            'variant' => $variant, // "Left" or "Right"
        ];
    }

    public function remove_all_cart()
    {
        $user = Helpers::getCustomerInformation();

        Cart::where([
            'customer_id' => ($user == 'offline' ? session('guest_id') : auth('customer')->id()),
            'is_guest' => ($user == 'offline' ? 1 : '0'),
        ])->delete();
        return redirect()->back();
    }

    public function showInvoice($order_id)
    {
        $order = WholesaleQuotation::with('metas', 'wholeseller', 'product')->findOrFail($order_id);
        if ($order->status === 'pending') {
            return back()->with('warning', 'Your order is under review. You will be able to view the quotation once it is approved.');
        }
        return view(VIEW_FILE_NAMES['order_invoice_details'], compact('order'));
    }

    public function orderQuotation($id)
    {
        // Fetching the order with related items (products and quantities) and metas
        $order = WholesaleQuotation::with(['wholeseller', 'items.product', 'metas'])->findOrFail($id);
        if ($order->status === 'pending') {
            Toastr::success(translate('Your order is under review. You will be able to view the quotation once it is approved.'));

            return back();
        }

        return view(VIEW_FILE_NAMES['order_invoice_details'], compact('order'));
    }

    public function viewOrderPage($order_id)
    {
        $order = WholesalePurchaseOrder::with('product', 'wholesaler')->findOrFail($order_id);

        return view('wholesale.order-view', compact('order'));
    }

    public function viewWholesaleOrders()
    {
        $userId = auth('customer')->id();
        $orders = WholesalePurchaseOrder::with('product')->where('wholeseller_id', $userId)->latest()->get();

        return view(VIEW_FILE_NAMES['product_order_details'], compact('orders'));
    }

    private function generateCartGroupId($user)
    {
        $userId = ($user == null || !empty($user->guest_id)) ? 'guest' : $user->id;

        if (session()->has('cart_group_id')) {
            return session('cart_group_id');
        }

        $groupId = 'wholesale_' . $userId . '-' . Str::random(5) . '-' . time();
        session()->put('cart_group_id', $groupId);

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
        $order = WholesalePurchaseOrder::with([
            'items.product',
            'wholeseller',
        ])->findOrFail($id);

        return view(VIEW_FILE_NAMES['wholesale_order_view'], compact('order'));
    }

    public function approveQuotation(Request $request, $id)
    {

        Log::info('the approve request is ', ['the request is ' => $request->all()]);
        $order = WholesaleQuotation::with('items.product')->findOrFail($id);
        $request->validate([
            'external_po_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('wholesale_confirm_orders', 'external_po_number')
            ],
            'quotation_file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $filePath = null;
        if ($request->hasFile('quotation_file')) {
            $file = $request->file('quotation_file');
            $storedPath = $file->store('wholesale_attachment', 'public');
            $filePath = basename($storedPath);
        }
        $confirmOrder = WholesaleConfirmOrder::create([
            'purchase_order_no' => $order->purchase_order_no ?? null,
            'quotation_no'    => $order->quotation_no,
            'wholesaler_id'   => $order->wholeseller_id,
            'status'          => 'confirmed',
            'delivery_status' => 'pending',
            'payment_status'  => 'unpaid',
            'confirmed_at'    => Carbon::now(),
            'final_price'     => $order->final_price,
            'attachments'    => $filePath,
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
        Toastr::success(translate('Quotation Approved & Delivery Initialized'));

        return redirect()->back();
    }
    public function rejectQuotation($id)
    {
        $order = WholesaleQuotation::with('items.product')->findOrFail($id);

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
            'name' => 'required|string|max:255',
            'thumbnail' => 'nullable|string',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'tax_model' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric',
            'price_range_id' => 'required',
            'quantity' => 'required|integer|min:1',
            'variant' => 'required|string',
        ]);

        $user = auth()->guard('customer')->user();
        $product = Product::findOrFail($validated['product_id']);

        // Get wholesale product for variations
        $wholesaleProduct = WholeSaleProducts::where('product_id', $product->id)
            ->where('variation_key', $validated['variant'])
            ->first();

        if (!$wholesaleProduct) {
            Toastr::error(translate('Wholesale product not found.'));
            return redirect()->back();
        }

        // Get cart group ID
        $cartGroupId = $this->getOrCreateCartGroupId($user);

        // Step 1: Extract data for variations column: {"color":"Yellow","L\/R":"left"}
        $variationsArray = [];
        $variantForColumn = ''; // For variant column: "Left"
        $choicesArray = []; // For choices column: {"choice_7":"left"}

        // Parse the variation key: e.g., "unknown:Yellow | l/r:left"
        $variationKey = $validated['variant'];
        $pairs = explode('|', $variationKey);

        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if (strpos($pair, ':') !== false) {
                list($key, $value) = explode(':', $pair, 2);
                $key = trim($key);
                $value = trim($value);

                // FIX: Map keys to correct format
                if (strtolower($key) === 'unknown') {
                    $key = 'color'; // Change "unknown" to "color"
                    $color = $value;
                } elseif (strtolower($key) === 'l/r' || strtolower($key) === 'l / r') {
                    $key = 'L/R'; // Standardize to "L/R" with uppercase L and R

                    // Step 3: For variant column - Capitalize first letter
                    $variantForColumn = ucfirst($value); // "left" becomes "Left"

                    // Step 2: For choices column
                    $choicesArray = ['choice_7' => $value]; // Keep value as is for choices
                }

                // Add to variations array with corrected keys
                $variationsArray[$key] = $value;
            }
        }

        // If we couldn't determine the variant, use a default
        if (empty($variantForColumn)) {
            // Extract from variations array or use a default
            if (isset($variationsArray['L/R'])) {
                $variantForColumn = ucfirst($variationsArray['L/R']);
            } elseif (isset($variationsArray['l/r'])) {
                $variantForColumn = ucfirst($variationsArray['l/r']);
            } else {
                $variantForColumn = 'Standard';
            }
        }

        // Get color for color column
        $color = $variationsArray['color'] ?? $variationsArray['Color'] ?? $variationsArray['COLOR'] ?? null;

        // Get seller info
        $sellerId = ($product->added_by == 'admin') ? 1 : $product->user_id;
        $sellerIs = ($product->added_by == 'admin') ? 'admin' : 'seller';
        $shopInfo = ($product->added_by == 'admin') ? 'Dynamic Logic' : null;

        // Generate unique slug for cart item
        $slug = $product->slug . '-' . Str::random(8);

        // Get the original variation key (for reference if needed)
        $originalVariationKey = $validated['variant'];

        // Create NEW cart item - ALWAYS create new entry
        $cart = new Cart();
        $cart->customer_id = $user->id;
        $cart->customer_type = 0; // Default for wholesale
        $cart->cart_group_id = $cartGroupId;
        $cart->product_id = $product->id;
        $cart->product_type = 'physical';
        $cart->digital_product_type = null;
        $cart->color = $color;

        // IMPORTANT: choices column should have EXACTLY: {"choice_7":"left"}
        $cart->choices = !empty($choicesArray) ? json_encode($choicesArray, JSON_UNESCAPED_SLASHES) : null;

        // IMPORTANT: variations column should have EXACTLY: {"color":"Yellow","L\/R":"left"}
        // Note: JSON_UNESCAPED_SLASHES to avoid escaping forward slashes
        $cart->variations = !empty($variationsArray) ? json_encode($variationsArray, JSON_UNESCAPED_SLASHES) : null;

        // IMPORTANT: variant column should have EXACTLY: "Left" (capital L)
        $cart->variant = $variantForColumn;

        $cart->quantity = $validated['quantity'];
        $cart->price = $validated['price'];
        $cart->tax = $validated['tax'] ?? 0;
        $cart->discount = $validated['discount'] ?? 0;
        $cart->wholesale_discount = 0.000000;
        $cart->wholesale_spacial_discount = 0.000000;
        $cart->installtion_charges = 0;
        $cart->exchange_qty = 0;
        $cart->exchange_charges = 0;
        $cart->tax_model = $validated['tax_model'] ?? 'exclude';
        $cart->is_checked = 1;
        $cart->slug = $slug;
        $cart->name = $product->name;
        $cart->thumbnail = $product->thumbnail;
        $cart->seller_id = $sellerId;
        $cart->seller_is = $sellerIs;
        $cart->created_at = now();
        $cart->updated_at = now();
        $cart->shop_info = $shopInfo;
        $cart->shipping_cost = $validated['shipping_cost'] ?? 0;
        $cart->shipping_type = 'area_wise';
        $cart->is_guest = 0;

        $cart->save();

        // Debug: Log the saved data
        Log::info('Cart item saved', [
            'cart_id' => $cart->id,
            'variations' => $cart->variations,
            'choices' => $cart->choices,
            'variant' => $cart->variant,
        ]);

        Toastr::success(translate('Added to cart!'));
        return redirect()->back();
    }

    // Helper function to extract variant from key
    private function extractVariantFromKey($variationKey)
    {
        // If it's already a clean variant, return as is with first letter capitalized
        if (strpos($variationKey, '|') === false && strpos($variationKey, ':') === false) {
            return ucfirst(trim($variationKey));
        }

        // Try to extract L/R value from full variation key
        $pairs = explode('|', $variationKey);
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if (strpos($pair, ':') !== false) {
                list($key, $value) = explode(':', $pair, 2);
                $key = trim($key);
                $value = trim($value);

                if (strtolower($key) === 'l/r' || strtolower($key) === 'l / r') {
                    return ucfirst($value);
                }
            }
        }

        // If no L/R found, get last value and capitalize first letter
        $lastPart = end($pairs);
        $lastPart = trim($lastPart);

        if (strpos($lastPart, ':') !== false) {
            $subParts = explode(':', $lastPart);
            return ucfirst(trim(end($subParts)));
        }

        return ucfirst($lastPart);
    }

    private function getOrCreateCartGroupId($user)
    {
        $userId = $user->id;

        // Check if user has any cart items
        $existingCart = Cart::where('customer_id', $userId)
            ->where('is_guest', 0)
            ->first();

        if ($existingCart && $existingCart->cart_group_id) {
            return $existingCart->cart_group_id;
        }

        // Create unique group ID with timestamp
        return 'WH-' . $userId . '-' . Str::random(6) . '-' . time();
    }
}
