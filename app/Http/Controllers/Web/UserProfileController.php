<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\RobotsMetaContentRepositoryInterface;
use App\Enums\WebConfigKey;
use App\Events\RefundEvent;
use App\Http\Requests\Web\CustomerProfileUpdateRequest;
use App\Models\Contact;
use App\Models\InboxActivities;
use App\Models\InboxMessage;
use App\Models\SupportTicketConv;
use App\Traits\PdfGenerator;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\DeliveryMan;
use App\Models\DeliveryZipCode;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Departments;
use App\Models\ProductCompare;
use App\Models\RefundRequest;
use App\Models\OrderStatusHistory;
use App\Models\Review;
use App\Models\Seller;
use App\Models\ShippingAddress;
use App\Models\SupportTicket;
use App\Models\Warranty;
use App\Models\Wishlist;
use App\Models\SupportTicketDepartmentEmployee;
use App\Models\SupportTicketNotification;
use App\Traits\CacheManagerTrait;
use App\Traits\CommonTrait;
use App\Models\User;
use App\Models\WholeSalerBusiness;
use App\Utils\ImageManager;
use App\Utils\OrderManager;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SlaService;
use App\Support\ServiceTicketWorkflow;

class UserProfileController extends Controller
{
    use CommonTrait, PdfGenerator, CacheManagerTrait;

    public function __construct(
        private Order                                         $order,
        private Seller                                        $seller,
        private Product                                       $product,
        private Review                                        $review,
        private DeliveryMan                                   $deliver_man,
        private ProductCompare                                $compare,
        private SlaService                                         $slaService,
        private Wishlist                                      $wishlist,
        private readonly BusinessSettingRepositoryInterface   $businessSettingRepo,
        private readonly RobotsMetaContentRepositoryInterface $robotsMetaContentRepo,
        private readonly RestockProductRepositoryInterface    $restockProductRepo,
        private readonly RestockProductCustomerRepositoryInterface    $restockProductCustomerRepo,
    ) {}

    public function user_profile(Request $request)
    {
        $wishlists = $this->wishlist->whereHas('wishlistProduct', function ($q) {
            return $q;
        })->where('customer_id', auth('customer')->id())->count();
        $total_order = $this->order->where('customer_id', auth('customer')->id())->count();
        $total_loyalty_point = auth('customer')->user()->loyalty_point;
        $total_wallet_balance = auth('customer')->user()->wallet_balance;
        $addresses = ShippingAddress::where('customer_id', auth('customer')->id())->latest()->get();
        $customer_detail = User::where('id', auth('customer')->id())->first();

        return view(VIEW_FILE_NAMES['user_profile'], compact('customer_detail', 'addresses', 'wishlists', 'total_order', 'total_loyalty_point', 'total_wallet_balance'));
    }

    public function user_account(Request $request)
    {
        $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');
        $customerDetail = User::where('id', auth('customer')->id())->first();
        return view(VIEW_FILE_NAMES['user_account'], compact('customerDetail'));
    }

    public function getUserProfileUpdate(CustomerProfileUpdateRequest $request): RedirectResponse
    {
        $imageName = $request->file('image') ? ImageManager::update('profile/', auth('customer')->user()->image, 'webp', $request->file('image')) : auth('customer')->user()->image;
        $user = auth('customer')->user();
        $isPhoneChanged = $request['phone'] != $user['phone'];
        $isEmailChanged = $request['email'] != $user['email'];

        User::find($user['id'])->update([
            'f_name' => $request['f_name'],
            'l_name' => $request['l_name'],
            'phone' => $request['phone'],
            'email' => $request['email'],
            'is_phone_verified' => $isPhoneChanged ? 0 : $user['is_phone_verified'],
            'is_email_verified' => $isEmailChanged ? 0 : $user['is_email_verified'],
            'email_verified_at' => $isEmailChanged ? null : $user['email_verified_at'],
            'image' => $imageName,
            'password' => strlen($request['password']) > 5 ? bcrypt($request['password']) : auth('customer')->user()->password,
        ]);

        Toastr::info(translate('updated_successfully'));
        return redirect()->back();
    }

    public function account_address_add()
    {
        $deliveryRestriction = $this->cacheDeliveryRestrictionSetup();
        $country_restrict_status = $deliveryRestriction['delivery_country_restriction'];
        $zip_restrict_status = $deliveryRestriction['delivery_zip_code_area_restriction'];
        $default_location = getWebConfig(name: 'default_location');

        $countries = $this->getAddressCountryOptions($deliveryRestriction);

        $zip_codes = $zip_restrict_status ? DeliveryZipCode::all() : 0;

        return view(VIEW_FILE_NAMES['account_address_add'], compact('countries', 'zip_restrict_status', 'zip_codes', 'default_location', 'deliveryRestriction'));
    }

    // Add these methods to your controller (assuming it's the same controller handling user_account, e.g., CustomerController)

    // First, the business_profile method
    public function business_profile(Request $request)
    {
        $id = auth('customer')->id();
        $user = User::findOrFail($id);
        $business = $user->wholesalerBusiness;

        if (!$business) {
            // If no business info exists, redirect to add business page
            return redirect()->route('add.business.details');
        }

        $isPending = ($user->wholesaler_status == 0);
        return view(VIEW_FILE_NAMES['business_profile'], compact('business', 'isPending'));
    }

    // Then, the business_update method with validation for uniqueness
    public function business_update(Request $request)
    {
        $id = auth('customer')->id();
        $user = User::findOrFail($id);
        // Check if wholesaler is approved
        if ($user->wholesaler_status == 0) {
            Toastr::error(translate('Your account is pending approval. You cannot update business information until approved by admin.'));
            return redirect()->back();
        }
        $business = $user->wholesalerBusiness;
        if (!$business) {
            Toastr::error(translate('Business information not found.'));
            return redirect()->back();
        }
        // Validation rules - ensuring uniqueness for business-related fields
        $request->validate([
            'company_name' => 'required|string|max:255',
            'trade_name' => 'required|string|max:255|unique:wholesaler_businesses,trade_name,' . $business->id,
            'registration_number' => 'required|string|max:255|unique:wholesaler_businesses,registration_number,' . $business->id,
            'tax_id' => 'required|string|max:255|unique:wholesaler_businesses,tax_id,' . $business->id,
            'vat_number' => 'nullable|string|max:255|unique:wholesaler_businesses,vat_number,' . $business->id,
            'register_copy' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tax_card_copy' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'vat_register_copy' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        // Update business information
        $business->company_name = $request->company_name;
        $business->trade_name = $request->trade_name;
        $business->registration_number = $request->registration_number;
        $business->tax_id = $request->tax_id;
        $business->vat_number = $request->vat_number ?? null;
        // Handle file uploads using ImageManager
        if ($request->hasFile('register_copy')) {
            $business->register_copy = ImageManager::update('register_copies/', $business->register_copy, 'webp', $request->file('register_copy'));
        }
        if ($request->hasFile('tax_card_copy')) {
            $business->tax_card_copy = ImageManager::update('tax_cards/', $business->tax_card_copy, 'webp', $request->file('tax_card_copy'));
        }
        if ($request->hasFile('vat_register_copy')) {
            $business->vat_register_copy = ImageManager::update('vat_copies/', $business->vat_register_copy, 'webp', $request->file('vat_register_copy'));
        }
        $business->save();
        Toastr::info(translate('Business information updated successfully'));
        return redirect()->back();
    }

    public function account_delete($id)
    {
        if (auth('customer')->id() == $id) {
            $user = User::find($id);

            $ongoing = ['out_for_delivery', 'processing', 'confirmed', 'pending'];
            $order = Order::where('customer_id', $user->id)->whereIn('order_status', $ongoing)->count();
            if ($order > 0) {
                Toastr::warning(translate('you_can_not_delete_account_due_ongoing_order'));
                return redirect()->back();
            }
            auth()->guard('customer')->logout();

            ImageManager::delete('/profile/' . $user['image']);
            session()->forget('wish_list');

            $user->delete();
            Toastr::info(translate('Your_account_deleted_successfully!!'));
            return redirect()->route('home');
        }

        Toastr::warning(translate('access_denied') . '!!');
        return back();
    }

    public function account_address(): View|RedirectResponse
    {
        $deliveryRestriction = $this->cacheDeliveryRestrictionSetup();
        $country_restrict_status = $deliveryRestriction['delivery_country_restriction'];
        $zip_restrict_status = $deliveryRestriction['delivery_zip_code_area_restriction'];

        $countries = $this->getAddressCountryOptions($deliveryRestriction);
        $zip_codes = $zip_restrict_status ? DeliveryZipCode::all() : 0;

        $countriesName = [];
        $countriesCode = [];
        foreach ($countries as $country) {
            $countriesName[] = $country['name'];
            $countriesCode[] = $country['code'];
        }

        if (auth('customer')->check()) {
            $shippingAddresses = ShippingAddress::where('customer_id', auth('customer')->id())->latest()->get();
            return view('web-views.users-profile.account-address', compact('shippingAddresses', 'country_restrict_status', 'zip_restrict_status', 'countries', 'zip_codes', 'countriesName', 'countriesCode', 'deliveryRestriction'));
        } else {
            return redirect()->route('home');
        }
    }

    public function address_store(Request $request): RedirectResponse
    {
        $deliveryRestriction = $this->cacheDeliveryRestrictionSetup();
        $countryRestrictionEnabled = (int)$deliveryRestriction['delivery_country_restriction'] === 1;
        $stateRestrictionEnabled = (int)$deliveryRestriction['delivery_state_restriction'] === 1;
        $cityRestrictionEnabled = (int)$deliveryRestriction['delivery_city_restriction'] === 1;
        $areaRestrictionEnabled = (int)$deliveryRestriction['delivery_area_restriction'] === 1;

        $validationRules = [
            'name' => 'required',
            'phone' => 'required|max:20',
            'address' => 'required',
        ];

        if ($countryRestrictionEnabled) {
            $validationRules['country'] = 'required';
        }
        if ($stateRestrictionEnabled) {
            $validationRules['state'] = 'required';
        }
        if ($cityRestrictionEnabled) {
            $validationRules['city'] = 'required';
        }
        if ($areaRestrictionEnabled) {
            $validationRules['area'] = 'required';
        }

        $request->validate($validationRules);

        $numericPhoneValue = preg_replace('/[^0-9]/', '', $request['phone']);
        $numericLength = strlen($numericPhoneValue);

        if ($numericLength < 4 || $numericLength > 20) {
            $request->validate([
                'phone' => 'min:5|max:20',
            ]);
        }

        $country = $this->resolveCustomerAddressCountry($request, $deliveryRestriction);
        $state = $stateRestrictionEnabled ? $request['state'] : null;
        $city = $cityRestrictionEnabled ? $request['city'] : null;
        $area = $areaRestrictionEnabled ? $request['area'] : null;
        $zip = ((int)$deliveryRestriction['delivery_zip_code_area_restriction'] === 1) ? ($request['zip'] ?? null) : null;

        $country_exist = ($countryRestrictionEnabled || $deliveryRestriction['single_country_mode']) ? self::delivery_country_exist_check($country) : true;

        if (!$country_exist) {
            Toastr::error(translate('Delivery_unavailable_in_this_country!'));
            return back();
        }

        $address = [
            'customer_id' => auth('customer')->check() ? auth('customer')->id() : null,
            'contact_person_name' => $request['name'],
            'address_type' => $request['addressAs'],
            'address' => $request['address'],
            'city' => $city,
            'area' => $area,
            'state' => $state,
            'zip' => $zip,
            'country' => $country,
            'phone' => $request['phone'],
            'is_billing' => $request['is_billing'],
            'latitude' => $request['latitude'],
            'longitude' => $request['longitude'],
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('shipping_addresses')->insert($address);

        Toastr::success(translate('address_added_successfully!'));

        if (theme_root_path() == 'default') {
            return back();
        } else {
            return redirect()->route('user-profile');
        }
    }

    public function address_edit(Request $request, $id)
    {
        $shippingAddress = ShippingAddress::where('customer_id', auth('customer')->id())->find($id);
        $deliveryRestriction = $this->cacheDeliveryRestrictionSetup();
        $country_restrict_status = $deliveryRestriction['delivery_country_restriction'];
        $zip_restrict_status = $deliveryRestriction['delivery_zip_code_area_restriction'];

        $delivery_countries = $this->getAddressCountryOptions($deliveryRestriction);
        $delivery_zipcodes = $zip_restrict_status ? DeliveryZipCode::all() : 0;

        $countriesName = [];
        $countriesCode = [];
        foreach ($delivery_countries as $country) {
            $countriesName[] = $country['name'];
            $countriesCode[] = $country['code'];
        }

        if (isset($shippingAddress)) {
            return view(VIEW_FILE_NAMES['account_address_edit'], compact('shippingAddress', 'country_restrict_status', 'zip_restrict_status', 'delivery_countries', 'delivery_zipcodes', 'countriesName', 'countriesCode', 'deliveryRestriction'));
        } else {
            Toastr::warning(translate('access_denied'));
            return back();
        }
    }

    public function address_update(Request $request)
    {
        $deliveryRestriction = $this->cacheDeliveryRestrictionSetup();
        $countryRestrictionEnabled = (int)$deliveryRestriction['delivery_country_restriction'] === 1;
        $stateRestrictionEnabled = (int)$deliveryRestriction['delivery_state_restriction'] === 1;
        $cityRestrictionEnabled = (int)$deliveryRestriction['delivery_city_restriction'] === 1;
        $areaRestrictionEnabled = (int)$deliveryRestriction['delivery_area_restriction'] === 1;

        $validationRules = [
            'name' => 'required',
            'phone' => 'required|max:20',
            'address' => 'required',
        ];

        if ($countryRestrictionEnabled) {
            $validationRules['country'] = 'required';
        }
        if ($stateRestrictionEnabled) {
            $validationRules['state'] = 'required';
        }
        if ($cityRestrictionEnabled) {
            $validationRules['city'] = 'required';
        }
        if ($areaRestrictionEnabled) {
            $validationRules['area'] = 'required';
        }

        $request->validate($validationRules);

        $numericPhoneValue = preg_replace('/[^0-9]/', '', $request['phone']);
        $numericLength = strlen($numericPhoneValue);
        if ($numericLength < 4 || $numericLength > 20) {
            $request->validate([
                'phone' => 'min:5|max:20',
            ], [
                'phone.min' => translate('The_phone_number_must_be_at_least_4_characters'),
                'phone.max' => translate('The_phone_number_may_not_be_greater_than_20_characters'),
            ]);
        }

        $country = $this->resolveCustomerAddressCountry($request, $deliveryRestriction);
        $state = $stateRestrictionEnabled ? $request['state'] : null;
        $city = $cityRestrictionEnabled ? $request['city'] : null;
        $area = $areaRestrictionEnabled ? $request['area'] : null;
        $zip = ((int)$deliveryRestriction['delivery_zip_code_area_restriction'] === 1) ? ($request['zip'] ?? null) : null;

        $country_exist = ($countryRestrictionEnabled || $deliveryRestriction['single_country_mode']) ? self::delivery_country_exist_check($country) : true;

        if (!$country_exist) {
            Toastr::error(translate('Delivery_unavailable_in_this_country!'));
            return back();
        }

        $updateAddress = [
            'contact_person_name' => $request->name,
            'address_type' => $request->addressAs,
            'address' => $request->address,
            'zip' => $zip,
            'country' => $country,
            'state' => $state,
            'city' => $city,
            'area' => $area,
            'phone' => $request->phone,
            'is_billing' => $request->is_billing,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (auth('customer')->check()) {
            ShippingAddress::where('id', $request->id)->update($updateAddress);
            Toastr::success(translate('address_updated_successfully!'));
        } else {
            Toastr::error(translate('Insufficient_permission!'));
        }
        return theme_root_path() == 'default' ? redirect()->route('account-address') : redirect()->route('user-profile');
    }

    private function getAddressCountryOptions(array $deliveryRestriction): array
    {
        if (!empty($deliveryRestriction['single_country_mode'])) {
            return $this->get_delivery_country_array();
        }

        return !empty($deliveryRestriction['delivery_country_restriction'])
            ? $this->get_delivery_country_array()
            : COUNTRIES;
    }

    private function resolveCustomerAddressCountry(Request $request, array $deliveryRestriction): ?string
    {
        if (!empty($deliveryRestriction['single_country_mode']) && !empty($deliveryRestriction['default_country_code'])) {
            foreach ($this->get_delivery_country_array() as $country) {
                if (($country['code'] ?? null) === $deliveryRestriction['default_country_code']) {
                    return $country['name'] ?? $country['code'];
                }
            }

            return $deliveryRestriction['default_country_code'];
        }

        return $request->country;
    }

    public function address_delete(Request $request)
    {
        if (auth('customer')->check()) {
            ShippingAddress::destroy($request->id);
            Toastr::success(translate('address_Delete_Successfully'));
            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }

    public function account_payment()
    {
        if (auth('customer')->check()) {
            return view('web-views.users-profile.account-payment');
        } else {
            return redirect()->route('home');
        }
    }

    public function account_order(Request $request)
    {
        $order_by = $request->order_by ?? 'desc';
        if (theme_root_path() == 'theme_fashion') {
            $show_order = $request->show_order ?? 'ongoing';

            $array = ['pending', 'confirmed', 'out_for_delivery', 'processing'];
            $orders = $this->order->withSum('orderDetails', 'qty')
                ->where(['customer_id' => auth('customer')->id(), 'is_guest' => '0'])
                ->when($show_order == 'ongoing', function ($query) use ($array) {
                    $query->whereIn('order_status', $array);
                })
                ->when($show_order == 'previous', function ($query) use ($array) {
                    $query->whereNotIn('order_status', $array);
                })
                ->when($request['search'], function ($query) use ($request) {
                    $query->where('id', 'like', "%{$request['search']}%");
                })
                ->orderBy('id', $order_by)->paginate(10)->appends(['show_order' => $show_order, 'search' => $request->search]);
        } else {
            $orders = $this->order->withSum('orderDetails', 'qty')->where(['customer_id' => auth('customer')->id(), 'is_guest' => '0'])
                ->orderBy('id', $order_by)
                ->paginate(10);
        }

        return view(VIEW_FILE_NAMES['account_orders'], compact('orders', 'order_by'));
    }

    public function account_order_details(Request $request): View|RedirectResponse
    {
        $order = $this->order->with(['deliveryManReview', 'customer', 'offlinePayments', 'details.productAllStatus', 'orderStatusHistory'])
            ->where(['id' => $request['id'], 'customer_id' => auth('customer')->id(), 'is_guest' => '0'])
            ->first();

        if ($order) {
            $deliveredAtForRefundWindow = $order->orderStatusHistory
                ?->where('status', 'delivered')
                ?->sortByDesc('id')
                ?->first()
                ?->created_at;

            $order?->details?->map(function ($detail) use ($order, $deliveredAtForRefundWindow) {
                $order['total_qty'] += $detail->qty;

                $reviews = Review::where(['product_id' => $detail['product_id'], 'customer_id' => auth('customer')->id()])->whereNull('delivery_man_id')->get();
                $reviewData = null;
                foreach ($reviews as $review) {
                    if ($review->order_id == $detail->order_id) {
                        $reviewData = $review;
                    }
                }

                if (isset($reviews[0]) && is_null($reviewData)) {
                    $reviewData = ($reviews[0]['order_id'] == null ? $reviews[0] : null);
                }
                $detail['reviewData'] = $reviewData;
                $detail['refund_window_start_at'] = $deliveredAtForRefundWindow ?? $detail->created_at;
                return $order;
            });
            return view(VIEW_FILE_NAMES['account_order_details'], [
                'order' => $order,
                'refund_day_limit' => getWebConfig(name: 'refund_day_limit'),
                'current_date' => Carbon::now(),
            ]);
        }

        Toastr::warning(translate('invalid_order'));
        return redirect()->route('account-oder');
    }

    public function account_order_details_warranty_support(Request $request): View|RedirectResponse
    {
        $order = $this->order->with(['deliveryManReview', 'customer', 'offlinePayments', 'details.productAllStatus', 'details.product'])
            ->where(['id' => $request['id'], 'customer_id' => auth('customer')->id(), 'is_guest' => '0'])
            ->first();

        if (!$order) {
            Toastr::warning(translate('invalid_order'));
            return redirect()->route('account-oder');
        }

        $productIds = $order->details->pluck('product_id')->filter()->unique()->values()->toArray();
        $warrantiesByProduct = [];
        if (!empty($productIds)) {
            $warrantiesByProduct = Warranty::where('final_user_id', auth('customer')->id())
                ->where('invoice_number', $order->id)
                ->whereIn('product_id', $productIds)
                ->where('activation_method', 'order_activation')
                ->whereNotNull('activation_date')
                ->with(['claims' => fn($query) => $query->latest('submitted_at')])
                ->orderBy('activation_date')
                ->get()
                ->groupBy('product_id');
        }

        $consumedWarrantyCountByProduct = [];
        $orderDetailWarrantyMap = [];
        foreach ($order->details as $detail) {
            $productId = (int)$detail->product_id;
            $detailQty = max(0, (int)$detail->qty);
            $productWarranties = collect($warrantiesByProduct[$productId] ?? [])->values();
            $offset = $consumedWarrantyCountByProduct[$productId] ?? 0;

            $detailWarranties = $productWarranties->slice($offset, $detailQty)->values();
            $consumedWarrantyCountByProduct[$productId] = $offset + $detailWarranties->count();

            $activatedCount = $detailWarranties->count();
            $remainingCount = max(0, $detailQty - $activatedCount);

            $orderDetailWarrantyMap[$detail->id] = [
                'items' => $detailWarranties,
                'first' => $detailWarranties->first(),
                'activated_count' => $activatedCount,
                'remaining_count' => $remainingCount,
            ];
        }

        return view(VIEW_FILE_NAMES['order_details_warranty_support'] ?? 'web-views.users-profile.account-details.warranty-support', [
            'order' => $order,
            'orderDetailWarrantyMap' => $orderDetailWarrantyMap,
            'refund_day_limit' => getWebConfig(name: 'refund_day_limit'),
            'current_date' => Carbon::now(),
        ]);
    }

    public function account_order_details_seller_info(Request $request)
    {
        $order = $this->order->with(['seller.shop'])->find($request->id);
        if (!$order) {
            Toastr::warning(translate('invalid_order'));
            return redirect()->route('account-oder');
        }

        $productIds = $this->product->active()->where(['added_by' => $order->seller_is])->where('user_id', $order->seller_id)->pluck('id')->toArray();
        $rating = $this->review->active()->whereIn('product_id', $productIds);
        $rating_count = $rating->count();
        $avg_rating = $rating->avg('rating');
        $product_count = count($productIds);

        $vendorRattingStatusPositive = 0;
        foreach ($rating->pluck('rating') as $singleRating) {
            ($singleRating >= 4 ? ($vendorRattingStatusPositive++) : '');
        }

        $rating_percentage = $rating_count != 0 ? ($vendorRattingStatusPositive * 100) / $rating_count : 0;

        return view(VIEW_FILE_NAMES['seller_info'], compact('avg_rating', 'product_count', 'rating_count', 'order', 'rating_percentage'));
    }

    public function account_order_details_delivery_man_info(Request $request)
    {

        $order = $this->order->with(['verificationImages', 'details.product', 'deliveryMan.rating', 'deliveryManReview', 'deliveryMan' => function ($query) {
            return $query->withCount('review');
        }])->find($request->id);

        if (!$order) {
            Toastr::warning(translate('invalid_order'));
            return redirect()->route('account-oder');
        }

        if (theme_root_path() == 'theme_fashion' || theme_root_path() == 'default') {
            foreach ($order->details as $details) {
                if ($details->product) {
                    if ($details->product->product_type == 'physical') {
                        $order['product_type_check'] = $details->product->product_type;
                        break;
                    } else {
                        $order['product_type_check'] = $details->product->product_type;
                    }
                }
            }
        }

        $delivered_count = $this->order->where(['order_status' => 'delivered', 'delivery_man_id' => $order->delivery_man_id, 'delivery_type' => 'self_delivery'])->count();

        return view(VIEW_FILE_NAMES['delivery_man_info'], compact('delivered_count', 'order'));
    }

    public function getAccountOrderDetailsReviewsView(Request $request): View|RedirectResponse
    {
        $order = $this->order->with(['deliveryManReview', 'customer', 'offlinePayments', 'details'])
            ->where(['id' => $request['id'], 'customer_id' => auth('customer')->id(), 'is_guest' => '0'])
            ->first();
        if ($order) {
            $order?->details?->map(function ($detail) use ($order) {
                $order['total_qty'] += $detail->qty;
                $reviews = Review::with('reply')
                    ->where(['product_id' => $detail['product_id'], 'customer_id' => auth('customer')->id()])
                    ->whereNull('delivery_man_id')->get();
                $reviewData = null;
                foreach ($reviews as $review) {
                    if ($review->order_id == $detail->order_id) {
                        $reviewData = $review;
                    }
                }
                if (isset($reviews[0]) && !$reviewData) {
                    $reviewData = ($reviews[0]['order_id'] != null ? $reviews[0] : null);
                }
                $detail['reviewData'] = $reviewData;
                return $order;
            });

            return view(VIEW_FILE_NAMES['order_details_review'], compact('order'));
        }
        Toastr::warning(translate('invalid_order'));
        return redirect()->route('account-oder');
    }


    public function account_wishlist()
    {
        if (auth('customer')->check()) {
            $wishlists = Wishlist::where('customer_id', auth('customer')->id())->get();
            return view('web-views.products.wishlist', compact('wishlists'));
        } else {
            return redirect()->route('home');
        }
    }

    public function account_tickets()
    {
        if (auth('customer')->check()) {
            $aDepartments = Departments::where('status', 1)->get();
            $supportTickets = SupportTicket::with('status_details')
                ->where('customer_id', auth('customer')->id())
                ->latest()
                ->paginate(10);
            return view(VIEW_FILE_NAMES['account_tickets'], compact('supportTickets', 'aDepartments'));
        } else {
            return redirect()->route('home');
        }
    }

    public function submitSupportTicket(Request $request): RedirectResponse
    {
        $request->validate([
            'ticket_subject' => 'required',
            'ticket_type' => 'required',
            'ticket_description' => 'required_without_all:image.*',
            'image.*' => 'required_without_all:ticket_description|image|mimes:jpeg,png,jpg,gif|max:6000',
        ], [
            'ticket_subject.required' => translate('The_ticket_subject_is_required'),
            'ticket_type.required' => translate('The_ticket_type_is_required'),
            'ticket_description.required_without_all' => translate('Either_a_ticket_description_or_an_image_is_required'),
            'image.*.required_without_all' => translate('Either_a_ticket_description_or_an_image_is_required'),
            'image.*.image' => translate('The_file_must_be_an_image'),
            'image.*.mimes' => translate('The_file_must_be_of_type:_jpeg,_png,_jpg,_gif'),
            'image.*.max' => translate('The_image_must_not_exceed_6_MB'),
        ]);

        $images = [];
        if ($request->file('image')) {
            foreach ($request['image'] as $key => $value) {
                $image_name = ImageManager::upload('support-ticket/', 'webp', $value);
                $images[] = [
                    'file_name' => $image_name,
                    'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                ];
            }
        }

        $statusMap = [
            'support' => 1,
            'complaint' => 36,
            'service' => ServiceTicketWorkflow::STATUS_NEW,
            'retail' => 43,
            'wholesale' => 56,
        ];

        $ticketStatus = $statusMap[$request->ticket_type] ?? 1;

        DB::transaction(function () use ($request, $images, $ticketStatus): void {
            $customer = auth('customer')->user();

            $ticket = SupportTicket::create([
                'subject' => $request['ticket_subject'],
                'type' => $request['ticket_type'],
                'customer_id' => $customer?->id,
                'description' => $request['ticket_description'],
                'attachment' => $images,
                'status' => $ticketStatus,
                'department_id' => $this->resolveDepartmentIdForTicketType($request->ticket_type),
            ]);

            $this->createConvertedInboxCaseForTicket(
                ticket: $ticket,
                customer: $customer,
                category: (string) $request->ticket_type,
                messageBody: (string) ($request['ticket_description'] ?? ''),
                sourceContext: []
            );
        });

        return back();
    }

    public function storeOrderItemSupportTicket(Request $request): RedirectResponse
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'order_detail_id' => 'required|integer|exists:order_details,id',
            'ticket_type' => 'required|in:support,complaint,service,retail,wholesale',
            'ticket_description' => 'nullable|string|max:2000',
        ]);

        $order = $this->order->with(['details.productAllStatus'])
            ->where([
                'id' => $request->order_id,
                'customer_id' => auth('customer')->id(),
                'is_guest' => '0',
            ])
            ->first();

        if (!$order) {
            Toastr::error(translate('invalid_order'));
            return redirect()->route('account-oder');
        }

        $orderDetail = $order->details->firstWhere('id', (int)$request->order_detail_id);
        if (!$orderDetail) {
            Toastr::error(translate('invalid_order'));
            return back();
        }

        $productDetails = json_decode($orderDetail->product_details, true);
        $productName = $productDetails['name'] ?? $orderDetail?->productAllStatus?->name ?? translate('product');

        $descriptionLines = [
            'Order ID: #' . $order->id,
            'Order Detail ID: ' . $orderDetail->id,
            'Product: ' . $productName,
            'Quantity: ' . $orderDetail->qty,
        ];

        if (!empty($request->ticket_description)) {
            $descriptionLines[] = 'Customer Message: ' . trim((string)$request->ticket_description);
        }

        $ticket = DB::transaction(function () use ($request, $order, $orderDetail, $productName, $descriptionLines) {
            $customer = auth('customer')->user();

            $ticket = SupportTicket::create([
                'subject' => 'Order #' . $order->id . ' - ' . $productName,
                'description' => implode(PHP_EOL, $descriptionLines),
                'customer_id' => $customer?->id,
                'department_id' => $this->resolveDepartmentIdForTicketType($request->ticket_type),
                'priority' => 'low',
                'type' => $request->ticket_type,
                'sub_type' => 'order_item_support',
                'status' => $this->getSupportTicketStatusByType($request->ticket_type),
                'source_id' => $orderDetail->id,
            ]);

            $this->createConvertedInboxCaseForTicket(
                ticket: $ticket,
                customer: $customer,
                category: (string) $request->ticket_type,
                messageBody: implode(PHP_EOL, $descriptionLines),
                sourceContext: [
                    'order_id' => $order->id,
                    'order_detail_id' => $orderDetail->id,
                    'product_name' => $productName,
                    'source' => 'order_item_support',
                ]
            );

            return $ticket;
        });

        Toastr::success(translate('support_ticket_created_successfully') . ' #' . $ticket->id);
        return redirect()->route('account-order-details-warranty-support', ['id' => $order->id]);
    }

    private function resolveDepartmentIdForTicketType(string $ticketType): int
    {
        $typeToDepartmentName = [
            'support' => 'Support Department',
            'complaint' => 'Complaint Department',
            'service' => 'Service Department',
            'retail' => 'Retail Department',
            'wholesale' => 'Wholesale Department',
        ];

        $departmentName = $typeToDepartmentName[$ticketType] ?? 'Support Department';

        $department = Departments::where('name', $departmentName)->first();

        return $department?->id ?? Departments::first()?->id ?? 1;
    }

    private function createConvertedInboxCaseForTicket(
        SupportTicket $ticket,
        ?Authenticatable $customer,
        string $category,
        string $messageBody,
        array $sourceContext = []
    ): InboxMessage {
        $normalizedCategory = strtolower(trim($category)) ?: 'support';
        $messageType = match ($normalizedCategory) {
            'service' => 'service',
            'career' => 'career',
            'retail', 'wholesale' => 'contact',
            default => 'support',
        };

        $customerName = trim(implode(' ', array_filter([
            $customer?->f_name,
            $customer?->l_name,
        ])));

        $contact = Contact::create([
            'name' => $customerName !== '' ? $customerName : ($customer?->name ?? translate('customer')),
            'email' => $customer?->email ?? '',
            'mobile_number' => $customer?->phone ?? '',
            'subject' => $ticket->subject,
            'message' => $messageBody,
        ]);

        $inboxMessage = InboxMessage::create([
            'subject' => $ticket->subject,
            'body' => $messageBody,
            'contact_id' => $customer?->id,
            'sender_name' => $contact->name,
            'sender_email' => $contact->email,
            'sender_phone' => $contact->mobile_number,
            'pipeline' => 'form',
            'message_type' => $messageType,
            'status' => 'converted',
            'priority' => $ticket->priority ?: 'medium',
            'department_id' => $ticket->department_id,
            'source_id' => $contact->id,
            'related_ticket_id' => $ticket->id,
            'convert_type' => 'ticket',
            'convert_sub_type' => $normalizedCategory,
            'details' => array_merge([
                'category' => $normalizedCategory,
                'subject' => $ticket->subject,
                'message' => $messageBody,
                'contact_id' => $contact->id,
                'has_attachment' => !empty($ticket->attachment),
                'attachment_count' => is_array($ticket->attachment) ? count($ticket->attachment) : 0,
            ], $sourceContext),
        ]);

        InboxActivities::create([
            'message_id' => $inboxMessage->id,
            'activity_type' => 'submission',
            'title' => translate('crm_timeline_inquiry_submitted'),
            'subject' => translate('crm_submitted_from_mobile_contact_form'),
            'note_date' => now(),
            'employee_id' => null,
            'details' => [
                'channel' => 'web',
                'pipeline' => 'form',
                'message_type' => $messageType,
                'category' => $normalizedCategory,
                'has_attachment' => !empty($ticket->attachment),
                'ticket_id' => $ticket->id,
            ],
        ]);

        return $inboxMessage;
    }

    public function single_ticket(Request $request)
    {
        if (!auth('customer')->check()) {
            return redirect()->route('customer.auth.login');
        }

        $ticket = SupportTicket::with(['conversations' => function ($query) {
            $query->when(theme_root_path() == 'default', function ($sub_query) {
                $sub_query->orderBy('id', 'desc');
            });
        }])
            ->where('id', $request->id)
            ->where('customer_id', auth('customer')->id())
            ->first();

        if (!$ticket) {
            Toastr::error(translate('ticket_not_found'));
            return redirect()->route('account-tickets');
        }

        return view(VIEW_FILE_NAMES['ticket_view'], compact('ticket'));
    }

    public function comment_submit(Request $request, $id)
    {
        if (!auth('customer')->check()) {
            return redirect()->route('customer.auth.login');
        }

        $ticket = SupportTicket::where('id', $id)
            ->where('customer_id', auth('customer')->id())
            ->first();

        if (!$ticket) {
            Toastr::error(translate('ticket_not_found'));
            return redirect()->route('account-tickets');
        }

        if ($request->file('image') == null && empty($request['comment'])) {
            Toastr::error(translate('type_something') . '!');
            return back();
        }

        DB::table('support_tickets')->where(['id' => $ticket->id])->update([
            // 'status' => 'open',
            'updated_at' => now(),
        ]);

        $image = [];
        if ($request->file('image')) {
            $validator = $request->validate([
                'image.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:6000'
            ]);
            foreach ($request->image as $key => $value) {
                $image_name = ImageManager::upload('support-ticket/', 'webp', $value);
                $image[] = [
                    'file_name' => $image_name,
                    'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                ];
            }
        }
        $data = [
            'customer_message' => $request->comment,
            'attachment' => $image,
            'support_ticket_id' => $ticket->id,
            'position' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        SupportTicketConv::create($data);
        Toastr::success(translate('message_send_successfully') . '!');
        return back();
    }

    public function support_ticket_close($id)
    {
        if (!auth('customer')->check()) {
            return redirect()->route('customer.auth.login');
        }

        // Ticket fetch
        $aSupportTicket = DB::table('support_tickets')
            ->join('departments', 'support_tickets.department_id', '=', 'departments.id')
            ->where('support_tickets.id', $id)
            ->where('support_tickets.customer_id', auth('customer')->id())
            ->select('support_tickets.*', 'departments.name as department_name', 'departments.head_id')
            ->first();

        if (!$aSupportTicket) {
            Toastr::error(translate('ticket_not_found'));
            return redirect()->route('account-tickets');
        }

        if (strtolower((string)$aSupportTicket->type) === 'service') {
            Toastr::error('Service tickets can only be closed by support after QA and payment confirmation.');
            return redirect()->route('account-tickets');
        }

        // Last status ke master_id ka closed id fetch karna
        $lastStatus = DB::table('support_ticket_status_master')
            ->where('id', $aSupportTicket->status) // current ticket status
            ->first();

        $closedStatus = DB::table('support_ticket_status_master')
            ->where('master_id', $lastStatus->master_id)
            ->where('name', 'closed')
            ->first();

        $statusToUpdate = $closedStatus ? $closedStatus->id : $aSupportTicket->status;

        DB::table('support_tickets')->where(['id' => $id])->update([
            'status' => $statusToUpdate,
            'updated_at' => now(),
        ]);

        // Ticket department employee entry
        SupportTicketDepartmentEmployee::create([
            'ticket_id' => $id,
            'department_id' => $aSupportTicket->department_id,
            'employee_id' => $aSupportTicket->employee_id,
            'status_id' => $statusToUpdate,
            'status_type_id' => 0,
            'created_by' => auth('customer')->check() ? auth('customer')->id() : 0
        ]);

        $aNotificationData = [
            [
                'ticket_id' => $id,
                'notification_for' => \App\Enums\TicketDispatchTarget::DepartmentHead->value,
                'user_id' => $aSupportTicket->head_id,
                'customer_id' => 0,
                'title' => 'Task Closed by Customer',
                'message' => 'The customer has closed the ticket. No further action is needed unless re-opened.',
                'status' => 0,
                'is_active' => 0
            ],
            [
                'ticket_id' => $id,
                'notification_for' => \App\Enums\TicketDispatchTarget::Employee->value,
                'user_id' => $aSupportTicket->employee_id,
                'customer_id' => 0,
                'title' => 'Task Closed by Customer',
                'message' => 'The customer has closed the ticket. No further action is needed unless re-opened.',
                'status' => 0,
                'is_active' => 0
            ]
        ];

        // Conversation
        $aReplyJourney = [
            'support_ticket_id' => $id,
            'admin_message' => 'The customer has marked this support ticket as closed. All related processes have been completed. If any further assistance is required, the customer may choose to reopen the ticket or submit a new request.',
            'admin_id' => auth('customer')->check() ? auth('customer')->id() : 0,
            'created_at' => now(),
            'updated_at' => now()
        ];

        SupportTicketConv::create($aReplyJourney);
        SupportTicketNotification::insert($aNotificationData);

        Toastr::success(translate('ticket_closed') . '!');
        return redirect('/account-tickets');
    }



    public function support_ticket_delete(Request $request)
    {
        if (auth('customer')->check()) {
            $support = SupportTicket::where('id', $request->id)
                ->where('customer_id', auth('customer')->id())
                ->first();

            if (!$support) {
                Toastr::error(translate('ticket_not_found'));
                return redirect()->route('account-tickets');
            }

            if ($support->attachment && !is_array($support->attachment) && count(json_decode($support->attachment)) > 0) {
                foreach (json_decode($support->attachment, true) as $image) {
                    ImageManager::delete('/support-ticket/' . $image);
                }
            } else if ($support->attachment && is_array($support->attachment) && count($support->attachment) > 0) {
                foreach ($support->attachment as $image) {
                    ImageManager::delete('/support-ticket/' . $image['file_name']);
                }
            }

            foreach ($support->conversations as $conversation) {
                if ($conversation->attachment && !is_array($support->attachment) && count(json_decode($conversation->attachment)) > 0) {
                    foreach (json_decode($conversation->attachment, true) as $image) {
                        ImageManager::delete('/support-ticket/' . $image);
                    }
                } else if ($conversation->attachment && is_array($conversation->attachment) && count($conversation->attachment) > 0) {
                    foreach ($conversation->attachment as $image) {
                        ImageManager::delete('/support-ticket/' . $image['file_name']);
                    }
                }
            }
            $support->conversations()->delete();

            $support->delete();
            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }

    public function track_order(): View
    {
        $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'track-order']);
        if (!$robotsMetaContentData) {
            $robotsMetaContentData = $this->robotsMetaContentRepo->getFirstWhere(params: ['page_name' => 'default']);
        }
        return view(VIEW_FILE_NAMES['tracking-page'], [
            'robotsMetaContentData' => $robotsMetaContentData
        ]);
    }

    public function track_order_wise_result(Request $request)
    {
        if (auth('customer')->check()) {
            $orderDetails = Order::with('orderDetails')->where('id', $request['order_id'])->whereHas('details', function ($query) {
                $query->where('customer_id', (auth('customer')->id()));
            })->first();

            if (!$orderDetails) {
                Toastr::warning(translate('invalid_order'));
                return redirect()->route('account-oder');
            }

            $isOrderOnlyDigital = self::getCheckIsOrderOnlyDigital($orderDetails);
            return view(VIEW_FILE_NAMES['track_order_wise_result'], compact('orderDetails', 'isOrderOnlyDigital'));
        }
        return back();
    }

    public function getCheckIsOrderOnlyDigital($order): bool
    {
        $isOrderOnlyDigital = true;
        if ($order->orderDetails) {
            foreach ($order->orderDetails as $detail) {
                $product = json_decode($detail->product_details, true);
                if (isset($product['product_type']) && $product['product_type'] == 'physical') {
                    $isOrderOnlyDigital = false;
                }
            }
        }
        return $isOrderOnlyDigital;
    }

    public function track_order_result(Request $request)
    {
        $isOrderOnlyDigital = false;
        $user = auth('customer')->user();
        $user_phone = $request['phone_number'] ?? '';

        if (!isset($user)) {
            $userInfo = User::where('phone', $request['phone_number'])->orWhere('phone', 'like', "%{$request['phone_number']}%")->first();
            $order = Order::where('id', $request['order_id'])->first();

            if ($order && $order->is_guest) {
                $orderDetails = Order::with('shippingAddress')
                    ->where('id', $request['order_id'])
                    ->first();

                $orderDetails = ($orderDetails && $orderDetails->shippingAddress && $orderDetails->shippingAddress->phone == $request['phone_number']) ? $orderDetails : null;

                if (!$orderDetails) {
                    $orderDetails = Order::where('id', $request['order_id'])
                        ->whereHas('billingAddress', function ($query) use ($request) {
                            $query->where('phone', $request['phone_number']);
                        })->first();
                }
            } elseif ($userInfo) {
                $orderDetails = Order::where('id', $request['order_id'])->whereHas('details', function ($query) use ($userInfo) {
                    $query->where('customer_id', $userInfo->id);
                })->first();
            } else {
                Toastr::error(translate('invalid_Order_Id_or_phone_Number'));
                return redirect()->route('track-order.index', ['order_id' => $request['order_id'], 'phone_number' => $request['phone_number']]);
            }
        } else {
            $order = Order::where('id', $request['order_id'])->first();
            if ($order && $order->is_guest) {
                $orderDetails = Order::where('id', $request['order_id'])->whereHas('shippingAddress', function ($query) use ($request) {
                    $query->where('phone', $request['phone_number']);
                })->first();
            } elseif ($user->phone == $request['phone_number']) {
                $orderDetails = Order::where('id', $request['order_id'])->whereHas('details', function ($query) {
                    $query->where('customer_id', auth('customer')->id());
                })->first();
            }

            if ($request['from_order_details'] == 1) {
                $orderDetails = Order::where('id', $request['order_id'])->whereHas('details', function ($query) {
                    $query->where('customer_id', auth('customer')->id());
                })->first();
            }
        }

        $order_verification_status = getWebConfig(name: 'order_verification');

        if (isset($orderDetails)) {
            if ($orderDetails['order_type'] == 'POS') {
                Toastr::error(translate('this_order_is_created_by_') . ($orderDetails['seller_is'] == 'seller' ? 'vendor' : 'admin') . translate('_from POS') . ',' . translate('please_contact_with_') . ($orderDetails['seller_is'] == 'seller' ? 'vendor' : 'admin') . translate('_to_know_more_details') . '.');
                return redirect()->back();
            }
            $isOrderOnlyDigital = self::getCheckIsOrderOnlyDigital($orderDetails);
            return view(VIEW_FILE_NAMES['track_order'], compact('orderDetails', 'user_phone', 'order_verification_status', 'isOrderOnlyDigital'));
        }

        Toastr::error(translate('invalid_Order_Id_or_phone_Number'));
        return redirect()->route('track-order.index', ['order_id' => $request['order_id'], 'phone_number' => $request['phone_number']]);
    }

    public function track_last_order()
    {
        $orderDetails = OrderManager::track_order(Order::where('customer_id', auth('customer')->id())->latest()->first()->id);

        if ($orderDetails != null) {
            return view('web-views.order.tracking', compact('orderDetails'));
        } else {
            return redirect()->route('track-order.index')->with('Error', translate('invalid_Order_Id_or_phone_Number'));
        }
    }

    public function order_cancel($id)
    {
        $order = Order::where(['id' => $id])->first();
        if ($order['payment_method'] == 'cash_on_delivery' && $order['order_status'] == 'pending') {
            $stockUpdated = OrderManager::stock_update_on_order_status_change($order, 'canceled');
            if (!$stockUpdated) {
                Toastr::error(translate('Stock_not_available_in_selected_branch'));
                return back();
            }
            Order::where(['id' => $id])->update(['order_status' => 'canceled']);

            SupportTicket::create([
                'subject' => "Order #{$order->id} Canceled",
                'description' => "Customer canceled the order.",
                'customer_id' => $order->customer_id,
                'department_id' => null,
                'employee_id' => null,
                'priority' => 'low',
                'type' => 'retail',
                'sub_type' => 'order canceled',
                'status' => 'New',
                'source_id' => $order->id,
            ]);


            Toastr::success(translate('successfully_canceled'));
        } elseif ($order['payment_method'] == 'offline_payment') {
            Toastr::error(translate('The_order_status_cannot_be_updated_as_it_is_an_offline_payment'));
        } else {
            Toastr::error(translate('status_not_changable_now'));
        }
        return back();
    }


    public function refund_request(Request $request, $id): View|RedirectResponse
    {
        $user = auth('customer')->user();
        $orderDetails = $this->getCustomerOrderDetail(orderDetailsId: $id, customerId: $user->id);
        if (!$orderDetails) {
            Toastr::error(translate('order_not_found'));
            return back();
        }

        if ($orderDetails->delivery_status !== 'delivered') {
            Toastr::warning(translate('you_can_refund_request_after_the_product_is_delivered'));
            return back();
        }

        if ($this->isRefundWindowExpired(orderDetails: $orderDetails)) {
            Toastr::warning(translate('refund_request_time_limit'));
            return back();
        }

        if ($this->hasRefundRequest(orderDetailsId: (int)$orderDetails->id, currentFlag: (int)$orderDetails->refund_request)) {
            Toastr::warning(translate('already_applied_for_refund_request!!'));
            return back();
        }

        return view('web-views.users-profile.refund-request', [
            'order_details' => $orderDetails,
        ]);
    }

    public function store_refund(Request $request): RedirectResponse
    {
        $request->validate([
            'order_details_id' => 'required',
            'refund_reason' => 'required',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = auth('customer')->user();
        $orderDetails = $this->getCustomerOrderDetail(orderDetailsId: $request->order_details_id, customerId: $user->id);
        if (!$orderDetails) {
            Toastr::error(translate('order_not_found'));
            return back();
        }

        if ($orderDetails->delivery_status !== 'delivered') {
            Toastr::warning(translate('you_can_refund_request_after_the_product_is_delivered'));
            return back();
        }

        if ($this->isRefundWindowExpired(orderDetails: $orderDetails)) {
            Toastr::warning(translate('refund_request_time_limit'));
            return back();
        }

        if ($this->hasRefundRequest(orderDetailsId: (int)$orderDetails->id, currentFlag: (int)$orderDetails->refund_request)) {
            Toastr::warning(translate('already_applied_for_refund_request!!'));
            return back();
        }

        // RefundRequest save code
        $refundRequest = new RefundRequest();
        $refundRequest->order_details_id = $orderDetails->id;
        $refundRequest->customer_id = $user->id;
        $refundRequest->status = 'pending';
        $refundRequest->amount = $this->calculateRefundAmount(orderDetails: $orderDetails);
        $refundRequest->product_id = $orderDetails->product_id;
        $refundRequest->order_id = $orderDetails->order_id;
        $refundRequest->refund_reason = $request->refund_reason;

        if ($request->file('images')) {
            $images = [];
            foreach ($request->file('images') as $img) {
                $images[] = [
                    'image_name' => ImageManager::upload('refund/', 'webp', $img),
                    'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                ];
            }
            $refundRequest->images = $images;
        }
        $refundRequest->save();

        $orderDetails->refund_request = 1;
        $orderDetails->save();

        $ticket = SupportTicket::create([
            'subject' => "Refund Request for Order #{$orderDetails->order_id}",
            'description' => "Customer requested refund. Reason: {$request->refund_reason}",
            'customer_id' => $user->id,
            'department_id' => null,
            'employee_id' => null,
            'priority' => 'low',
            'type' => 'retail',
            'sub_type' => 'refund',
            'status' => 'New',
            'source_id' => $refundRequest->id,
        ]);

        $order = Order::find($orderDetails->order_id);
        event(new RefundEvent(status: 'refund_request', order: $order, refund: $refundRequest, orderDetails: $orderDetails));

        Toastr::success(translate('refund_requested_successful!!'));
        return redirect()->route('account-order-details', ['id' => $orderDetails->order_id]);
    }


    public function generate_invoice($id)
    {
        $order = Order::with('seller')->with('shipping')->where('id', $id)->first();
        $data["email"] = $order->customer["email"];
        $data["order"] = $order;
        $invoiceSettings = json_decode($this->businessSettingRepo->getFirstWhere(params: ['type' => 'invoice_settings'])?->value, true);
        $mpdf_view = \View::make(VIEW_FILE_NAMES['order_invoice'], compact('order', 'invoiceSettings'));
        $this->generatePdf(view: $mpdf_view, filePrefix: 'order_invoice_', filePostfix: $order['id'], pdfType: 'invoice', requestFrom: 'web');
    }

    public function refund_details($id)
    {
        $customerId = auth('customer')->id();
        $order_details = $this->getCustomerOrderDetail(orderDetailsId: $id, customerId: (int)$customerId);
        if (!$order_details) {
            Toastr::error(translate('order_not_found'));
            return redirect()->back();
        }

        $refund = RefundRequest::with(['product', 'order'])->where('customer_id', auth('customer')->id())
            ->where('order_details_id', $order_details->id)->first();
        $product = $this->product->find($order_details->product_id);
        $order = $this->order->find($order_details->order_id);

        if (request()->ajax()) {
            if ($product) {
                return response()->json([
                    'status' => 1,
                    'view' => view(VIEW_FILE_NAMES['refund_details'], compact('order_details', 'refund', 'product', 'order'))->render(),
                ]);
            }
            return response()->json(['status' => 0, 'message' => translate('product_not_found')]);
        }

        if ($product) {
            return view(VIEW_FILE_NAMES['refund_details'], compact('order_details', 'refund', 'product', 'order'));
        }

        Toastr::error(translate('product_not_found'));
        return redirect()->back();
    }

    private function getCustomerOrderDetail(int|string $orderDetailsId, int $customerId): ?OrderDetail
    {
        return OrderDetail::where('id', $orderDetailsId)
            ->whereHas('order', function ($query) use ($customerId) {
                $query->where('customer_id', $customerId)->where('is_guest', 0);
            })->first();
    }

    private function hasRefundRequest(int $orderDetailsId, int $currentFlag): bool
    {
        if ($currentFlag !== 0) {
            return true;
        }

        return RefundRequest::where('order_details_id', $orderDetailsId)->exists();
    }

    private function isRefundWindowExpired(OrderDetail $orderDetails): bool
    {
        $refundDayLimit = (int)(getWebConfig(name: 'refund_day_limit') ?? 0);
        if ($refundDayLimit <= 0) {
            return false;
        }

        $refundWindowStartAt = $this->getRefundWindowStartAt(orderDetails: $orderDetails);
        return $refundWindowStartAt->diffInDays(now()) > $refundDayLimit;
    }

    private function calculateRefundAmount(OrderDetail $orderDetails): float
    {
        $order = Order::with('details')->find($orderDetails->order_id);
        if (!$order || !$order->details) {
            return 0;
        }

        $totalProductPrice = 0;
        foreach ($order->details as $detail) {
            $totalProductPrice += ($detail->qty * $detail->price) + $detail->tax - $detail->discount;
        }

        if ($totalProductPrice <= 0) {
            return 0;
        }

        $subtotal = ($orderDetails->price * $orderDetails->qty) - $orderDetails->discount + $orderDetails->tax;
        $couponDiscount = (($order->discount_amount ?? 0) * $subtotal) / $totalProductPrice;
        return max(0, (float)($subtotal - $couponDiscount));
    }

    private function getRefundWindowStartAt(OrderDetail $orderDetails): Carbon
    {
        $deliveredAt = OrderStatusHistory::query()
            ->where('order_id', $orderDetails->order_id)
            ->where('status', 'delivered')
            ->latest('id')
            ->value('created_at');

        if ($deliveredAt) {
            return Carbon::parse($deliveredAt);
        }

        return Carbon::parse($orderDetails->updated_at ?? $orderDetails->created_at ?? now());
    }

    public function submit_review(Request $request, $id): View|RedirectResponse
    {
        $order_details = OrderDetail::where(['id' => $id])->whereHas('order', function ($q) {
            $q->where(['customer_id' => auth('customer')->id(), 'payment_status' => 'paid']);
        })->first();

        if (!$order_details) {
            Toastr::error(translate('invalid_order!'));
            return redirect('/');
        }

        return view('web-views.users-profile.submit-review', compact('order_details'));
    }

    public function refer_earn(Request $request): View|RedirectResponse
    {
        $refEarningStatus = getWebConfig(name: 'ref_earning_status') ?? 0;
        if (!$refEarningStatus) {
            Toastr::error(translate('you_have_no_permission'));
            return redirect('/');
        }
        $customer_detail = User::where('id', auth('customer')->id())->first();
        return view(VIEW_FILE_NAMES['refer_earn'], compact('customer_detail'));
    }

    public function user_coupons(Request $request): View
    {
        $coupons = Coupon::active()->with('seller')
            ->whereIn('customer_id', [auth('customer')->id(), '0'])
            ->whereDate('start_date', '<=', date('Y-m-d'))
            ->whereDate('expire_date', '>=', date('Y-m-d'))
            ->paginate(8);

        return view(VIEW_FILE_NAMES['user_coupons'], compact('coupons'));
    }

    public function restockRequestsView(Request $request): View
    {
        $restockProducts = $this->restockProductRepo->getListWhere(
            orderBy: ['updated_at' => 'desc'],
            searchValue: $request['searchValue'],
            filters: ['customer_id' => auth('customer')->id()],
            relations: ['product.clearanceSale' => function ($query) {
                return $query->active();
            }],
            dataLimit: getWebConfig(name: WebConfigKey::PAGINATION_LIMIT),
        );
        $productIdsArray = $restockProducts->pluck('product_id')->toArray();
        return view(VIEW_FILE_NAMES['user_restock_requests'], compact('restockProducts', 'productIdsArray'));
    }

    public function deleteRestockRequest(Request $request): RedirectResponse
    {
        $customerId = auth('customer')->id();
        if ($request['id']) {
            $this->restockProductCustomerRepo->delete(params: ['restock_product_id' => $request['id'], 'customer_id' => $customerId]);
        } else {
            $this->restockProductCustomerRepo->delete(params: ['customer_id' => $customerId]);
        }

        $restockProducts = $this->restockProductRepo->getListWhere(relations: ['restockProductCustomers'], dataLimit: 'all');
        $restockProducts->map(function ($restockProduct) {
            if ($restockProduct->restockProductCustomers->count() === 0) {
                $this->restockProductRepo->delete(params: ['id' => $restockProduct['id']]);
            }
        });

        Toastr::success(translate('product_restock_request_removed_successfully'));
        return redirect()->route('user-restock-requests');
    }

    private function getSupportTicketStatusByType(string $ticketType): int
    {
        $statusMap = [
            'support' => 1,
            'complaint' => 36,
            'service' => ServiceTicketWorkflow::STATUS_NEW,
            'retail' => 43,
            'wholesale' => 56,
        ];

        return $statusMap[$ticketType] ?? 1;
    }
}
