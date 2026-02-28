<?php

namespace App\Http\Controllers\Customer;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Library\Payer;
use App\Library\Payment as PaymentInfo;
use App\Library\Receiver;
use App\Models\ShippingAddress;
use App\Models\ShippingType;
use App\Models\BusinessSetting;
use App\Models\Cart;
use App\Models\ServiceInvoice;
use App\Models\CartShipping;
use App\Models\Currency;
use App\Traits\Payment;
use App\Utils\CartManager;
use App\Utils\Convert;
use App\Utils\OrderManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
use function App\Utils\payment_gateways;
use App\Services\ServiceInvoicePaymentService;

class PaymentController extends Controller
{
    use Payment;

    public function payment(Request $request): JsonResponse|Redirector|RedirectResponse
    {
        $user = Helpers::getCustomerInformation($request);
        $orderAdditionalData = [];
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required',
            'payment_platform' => 'required',
        ]);

        $validator->sometimes('customer_id', 'required', function ($input) {
            return in_array($input->payment_request_from, ['app']);
        });
        $validator->sometimes('is_guest', 'required', function ($input) {
            return in_array($input->payment_request_from, ['app']);
        });

        if ($validator->fails()) { //api
            $errors = Helpers::validationErrorProcessor($validator);
            if (in_array($request['payment_request_from'], ['app'])) {
                return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
            } else {
                foreach ($errors as $value) {
                    Toastr::error(translate($value['message']));
                }
                return back();
            }
        }

        $cartGroupIds = CartManager::get_cart_group_ids(request: $request, type: 'checked');
        $carts = Cart::whereHas('product', function ($query) {
            return $query->active();
        })->whereIn('cart_group_id', $cartGroupIds)->where(['is_checked' => 1])->get();

        if (count($cartGroupIds) === 0 || $carts->isEmpty()) {
            if (in_array($request['payment_request_from'], ['app'])) {
                return response()->json(['errors' => ['code' => 'empty-cart', 'message' => 'No checked cart items found']], 403);
            }
            Toastr::error(translate('please_select_at_least_one_item_before_checkout'));
            return redirect()->route('shop-cart');
        }

        $deliveryType = data_get($request, 'delivery_type');
        $branchId = CartManager::resolvePickupBranchIdForStockCheck($request);
        $productStockCheck = $deliveryType === 'pickup'
            ? CartManager::product_stock_check_by_branch($carts, $branchId)
            : CartManager::product_stock_check($carts);

        if (!$productStockCheck && in_array($request['payment_request_from'], ['app'])) {
            return response()->json(['errors' => ['code' => 'product-stock', 'message' => 'The following items in your cart are currently out of stock']], 403);
        } elseif (!$productStockCheck) {
            Toastr::error(translate('the_following_items_in_your_cart_are_currently_out_of_stock'));
            return redirect()->route('shop-cart');
        }

        $verifyStatus = OrderManager::verifyCartListMinimumOrderAmount($request);
        if ($verifyStatus['status'] == 0 && in_array($request['payment_request_from'], ['app'])) {
            return response()->json(['errors' => ['code' => 'Check the minimum order amount requirement']], 403);
        } elseif ($verifyStatus['status'] == 0) {
            Toastr::info('Check the minimum order amount requirement');
            return redirect()->route('shop-cart');
        }

        if (in_array($request['payment_request_from'], ['app'])) {
            $deliveryType = $request['delivery_type'] ?? 'delivery';
            $shippingMethod = getWebConfig(name: 'shipping_method');
            $physicalProductExist = false;
            foreach ($carts as $cart) {
                if ($cart->product_type == 'physical') {
                    $physicalProductExist = true;
                }

                if ($shippingMethod == 'inhouse_shipping') {
                    $adminShipping = ShippingType::where('seller_id', 0)->first();
                    $getShippingType = isset($adminShipping) == true ? $adminShipping->shipping_type : 'order_wise';
                } else {
                    if ($cart->seller_is == 'admin') {
                        $adminShipping = ShippingType::where('seller_id', 0)->first();
                        $getShippingType = isset($adminShipping) == true ? $adminShipping->shipping_type : 'order_wise';
                    } else {
                        $seller_shipping = ShippingType::where('seller_id', $cart->seller_id)->first();
                        $getShippingType = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
                    }
                }

                if ($getShippingType == 'order_wise' && $deliveryType !== 'pickup') {
                    $cartShipping = CartShipping::where('cart_group_id', $cart->cart_group_id)->first();
                    if (!isset($cartShipping) && $physicalProductExist) {
                        return response()->json(['errors' => ['code' => 'shipping-method', 'message' => 'Data not found']], 403);
                    }
                }
            }

            if (($user == 'offline' && $request['is_check_create_account'])) {
                $getAPIProcess = self::getRegisterNewCustomerAPIProcess($request);
                if ($getAPIProcess['status'] == 0) {
                    return response()->json(['message' => translate('Already_registered ')], 403);
                }
                $orderAdditionalData += [
                    'new_customer_info' => $getAPIProcess['data'],
                ];
            }
        }

        $redirectLink = $this->getCustomerPaymentRequest($request, $orderAdditionalData);

        if (in_array($request['payment_request_from'], ['app'])) {
            return response()->json([
                'redirect_link' => $redirectLink,
                'new_user' => isset($orderAdditionalData['new_customer_info']) && $orderAdditionalData['new_customer_info'] != null ? 1 : 0,
            ], 200);
        } else {
            return redirect($redirectLink);
        }
    }

    function getRegisterNewCustomerAPIProcess($request)
    {
        $newCustomerRegister = [];
        $shippingAddress = ShippingAddress::where(['customer_id' => $request['guest_id'], 'is_guest' => 1, 'id' => $request->input('address_id')])->first();
        if ($request->has('address_id') && $request['address_id'] && $shippingAddress) {
            if (User::where(['email' => $shippingAddress['email']])->orWhere(['phone' => $shippingAddress['phone']])->first()) {
                return ['status' => 0];
            } else {
                $newCustomerRegister = [
                    'status' => 1,
                    'data' => self::getRegisterNewCustomer(
                        request: $request,
                        address: $shippingAddress,
                        shippingId: $request['address_id'],
                        billingId: $request->has('billing_address_id') && $request['billing_address_id'] ? $request['billing_address_id'] : null
                    )
                ];
            }
        }

        $billingAddress = ShippingAddress::where(['customer_id' => $request['guest_id'], 'is_guest' => 1, 'id' => $request->input('billing_address_id')])->first();
        if ($request['address_id'] == null && $request->has('billing_address_id') && $request['billing_address_id'] && $billingAddress) {
            if (User::where(['email' => $billingAddress['email']])->orWhere(['phone' => $billingAddress['phone']])->first()) {
                return ['status' => 0];
            } else {
                $newCustomerRegister = [
                    'status' => 1,
                    'data' => self::getRegisterNewCustomer(
                        request: $request,
                        address: $billingAddress,
                        shippingId: null,
                        billingId: $request['billing_address_id'],
                    )
                ];
            }
        }

        return $newCustomerRegister;
    }


    function getRegisterNewCustomer($request, $address, $shippingId = null, $billingId = null): array
    {
        return [
            'name' => $address['contact_person_name'],
            'f_name' => $address['contact_person_name'],
            'l_name' => '',
            'email' => $address['email'],
            'phone' => $address['phone'],
            'is_active' => 1,
            'password' => $request['password'],
            'referral_code' => Helpers::generate_referer_code(),
            'shipping_id' => $shippingId,
            'billing_id' => $billingId,
        ];
    }

    public function success(Request $request): JsonResponse
    {
        return response()->json(['message' => 'Payment succeeded'], 200);
    }

    public function fail(): JsonResponse
    {
        return response()->json(['message' => 'Payment failed'], 403);
    }

    public function web_payment_success(Request $request)
    {
        $paymentRequest = $this->getPaymentRequestFromToken($request->query('token'));
        $serviceInvoiceId = $this->resolveServiceInvoiceId($paymentRequest);
        $isServiceInvoicePayment = $serviceInvoiceId !== null || session('payment_type') === 'service_invoice' || ($paymentRequest && $paymentRequest->attribute === 'service_invoice');

        if ($request->flag == 'success') {
            if ($isServiceInvoicePayment) {
                if (!$this->isValidServicePaymentSuccess($paymentRequest, $serviceInvoiceId)) {
                    session()->forget(['payment_type', 'invoice_id']);
                    Toastr::error(translate('Payment verification failed'));
                    return redirect(url('/'));
                }

                $invoice = $serviceInvoiceId ? ServiceInvoice::with('ticket')->find($serviceInvoiceId) : null;
                if (!$invoice) {
                    session()->forget(['payment_type', 'invoice_id']);
                    Toastr::error(translate('Invoice not found or already paid'));
                    return redirect(url('/'));
                }

                if ($invoice->payment_status !== 'paid') {
                    $invoice->update(['payment_status' => 'paid']);
                }

                $this->afterServiceInvoicePaymentSuccess($invoice);
                session()->forget(['payment_type', 'invoice_id']);

                Toastr::success(translate('Payment_success'));
                return view(VIEW_FILE_NAMES['service_payment_success']);
            }

            if (session()->has('payment_mode') && session('payment_mode') == 'app') {
                return response()->json(['message' => 'Payment succeeded'], 200);
            }

            Toastr::success(translate('Payment_success'));
            $isNewCustomerInSession = session('newCustomerRegister');
            session()->forget('newCustomerRegister');
            return view(VIEW_FILE_NAMES['order_complete'], compact('isNewCustomerInSession'));
        }

        if ($isServiceInvoicePayment) {
            session()->forget(['payment_type', 'invoice_id']);
        }

        if (session()->has('payment_mode') && session('payment_mode') == 'app') {
            return response()->json(['message' => 'Payment failed'], 403);
        }

        Toastr::error(translate('Payment_failed') . '!');
        return redirect(url('/'));
    }
    public function getCustomerPaymentRequest(Request $request, $orderAdditionalData = []): mixed
    {
        $additionalData = [
            'business_name' => getWebConfig(name: 'company_name'),
            'business_logo' => getStorageImages(path: getWebConfig('company_web_logo'), type:'shop'),
            'payment_mode' => $request->has('payment_platform') ? $request['payment_platform'] : 'web',
        ];

        $user = Helpers::getCustomerInformation($request);

        $getGuestId = $request['is_guest'] ? $request['guest_id'] : (session('guest_id') ?? 0);
        $isGuestUser = ($user == 'offline') ? 1 : 0;
        $getCustomerID = null;
        $isGuestUserInOrder = $isGuestUser;
        if ($user == 'offline' && session('newCustomerRegister')) {
            $additionalData['new_customer_info'] = session('newCustomerRegister') ?? null;
            $additionalData['customer_id'] = $getGuestId;
            $additionalData['address_id'] = session('newCustomerRegister')['address_id'] ?? null;
            $additionalData['billing_address_id'] = session('newCustomerRegister')['billing_address_id'] ?? null;
            $getCustomerID = $getGuestId;
            $isGuestUserInOrder = 0;
        } elseif ($user == 'offline' && !session('newCustomerRegister') && isset($orderAdditionalData['new_customer_info'])) {
            $additionalData['new_customer_info'] = $orderAdditionalData['new_customer_info'];
            $getCustomerID = $getGuestId;
            $isGuestUserInOrder = 0;
        } elseif ($user != 'offline') {
            $getCustomerID = 0;
            $isGuestUserInOrder = 0;
        }

        $additionalData['is_guest'] = $isGuestUser;
        if (in_array($request['payment_request_from'], ['app'])) {
            $additionalData['customer_id'] = $request['customer_id'];
            $additionalData['is_guest'] = $request['is_guest'];
            $additionalData['order_note'] = $request['order_note'];
            $additionalData['address_id'] = $request['address_id'];
            $additionalData['billing_address_id'] = $request['billing_address_id'];
            $additionalData['coupon_code'] = $request['coupon_code'];
            $additionalData['coupon_discount'] = $request['coupon_discount'];
            $additionalData['coupon_type'] = $request['coupon_type'] ?? null;
            $additionalData['delivery_type'] = $request['delivery_type'] ?? 'delivery';
            $additionalData['pickup_branch_id'] = $request['pickup_branch_id'] ?? null;
            $additionalData['nearest_branch'] = $request['nearest_branch'] ?? null;
            $additionalData['area_wise_shipping_resolved'] = $request['area_wise_shipping_resolved'] ?? null;
            $additionalData['payment_request_from'] = $request['payment_request_from'];
        } else {
            $additionalData['customer_id'] = $user != 'offline' ? $user->id : $getCustomerID;
            $additionalData['order_note'] = session('order_note') ?? null;
            $additionalData['address_id'] = session('address_id') ?? 0;
            $additionalData['billing_address_id'] = session('billing_address_id') ?? 0;

            $additionalData['coupon_code'] = session('coupon_code') ?? null;
            $additionalData['coupon_discount'] = session('coupon_discount') ?? 0;
            $additionalData['payment_request_from'] = $request['payment_mode'] ?? 'web';
        }
        $additionalData['new_customer_id'] = $getCustomerID;
        $additionalData['is_guest_in_order'] = $isGuestUserInOrder;

        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $currency_code = 'USD';
        } else {
            $default = getWebConfig(name: 'system_default_currency');
            $currency_code = Currency::find($default)->code;
        }

        if (in_array($request['payment_request_from'], ['app'])) {
            $paymentAmount = OrderManager::getApiPayableAmount($request);
        } else {
            $paymentAmount = CartManager::getCartPriceSummary(type: 'checked')['totalAmount'];
        }

        $customer = Helpers::getCustomerInformation($request);

        if ($customer == 'offline') {
            $address = ShippingAddress::where(['customer_id' => $request['customer_id'], 'is_guest' => 1])->latest()->first();
            if ($address) {
                $payer = new Payer(
                    $address->contact_person_name,
                    $address->email,
                    $address->phone,
                    ''
                );
            } else {
                $payer = new Payer(
                    'Contact person name',
                    '',
                    '',
                    ''
                );
            }
        } else {
            $payer = new Payer(
                $customer['f_name'] . ' ' . $customer['l_name'],
                $customer['email'],
                $customer['phone'],
                ''
            );
            if (empty($customer['phone'])) {
                Toastr::error(translate('please_update_your_phone_number'));
                return route('checkout-payment');
            }
        }

        $paymentInfo = new PaymentInfo(
            success_hook: 'digital_payment_success',
            failure_hook: 'digital_payment_fail',
            currency_code: $currency_code,
            payment_method: $request['payment_method'],
            payment_platform: $request['payment_platform'],
            payer_id: $customer == 'offline' ? $request['customer_id'] : $customer['id'],
            receiver_id: '100',
            additional_data: $additionalData,
            payment_amount: $paymentAmount,
            external_redirect_link: $request['payment_platform'] == 'web' ? $request['external_redirect_link'] : null,
            attribute: 'order',
            attribute_id: idate("U")
        );

        $receiverInfo = new Receiver('receiver_name', 'example.png');
        return $this->generate_link($payer, $paymentInfo, $receiverInfo);
    }

    public function customer_add_to_fund_request(Request $request): JsonResponse|Redirector|RedirectResponse
    {
        if (getWebConfig(name: 'add_funds_to_wallet') != 1) {
            if (in_array($request['payment_request_from'], ['app'])) {
                return response()->json(['message' => 'Add funds to wallet is deactivated'], 403);
            }
            Toastr::error(translate('add_funds_to_wallet_is_deactivated'));
            return back();
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'payment_method' => 'required',
            'payment_platform' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = Helpers::validationErrorProcessor($validator);
            if (in_array($request->payment_request_from, ['app'])) {
                return response()->json(['errors' => $errors]);
            } else {
                foreach ($errors as $value) {
                    Toastr::error(translate($value['message']));
                }
                return back();
            }
        }

        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $default_currency = Currency::find(getWebConfig(name: 'system_default_currency'));
            $currency_code = $default_currency['code'];
            $currentCurrency = $request->current_currency_code ?? session('currency_code');
        } else {
            $default = BusinessSetting::where(['type' => 'system_default_currency'])->first()->value;
            $currency_code = Currency::find($default)->code;
            $currentCurrency = $currency_code;
        }


        $minimumAddFundAmount = getWebConfig(name: 'minimum_add_fund_amount') ?? 0;
        $maximumAddFundAmount = getWebConfig(name: 'maximum_add_fund_amount') ?? 0;

        if (!(Convert::usdPaymentModule($request->amount, $currentCurrency) >= Convert::usdPaymentModule($minimumAddFundAmount, 'USD')) || !(Convert::usdPaymentModule($request->amount, $currentCurrency) <= Convert::usdPaymentModule($maximumAddFundAmount, 'USD'))) {
            $errors = [
                'minimum_amount' => $minimumAddFundAmount ?? 0,
                'maximum_amount' => $maximumAddFundAmount ?? 1000,
            ];
            if (in_array($request->payment_request_from, ['app'])) {
                return response()->json($errors, 202);
            } else {
                Toastr::error(translate('the_amount_needs_to_be_between') . ' ' . webCurrencyConverter($minimumAddFundAmount) . ' - ' . webCurrencyConverter($maximumAddFundAmount));
                return back();
            }
        }

        $additional_data = [
            'business_name' => BusinessSetting::where(['type' => 'company_name'])->first()->value,
            'business_logo' => getWebConfig('company_web_logo')['path'],
            'payment_mode' => $request->has('payment_platform') ? $request->payment_platform : 'web',
        ];

        $customer = Helpers::getCustomerInformation($request);

        if (in_array($request->payment_request_from, ['app'])) {
            $additional_data['customer_id'] = $customer->id;
            $additional_data['payment_request_from'] = $request->payment_request_from;
        }

        $payer = new Payer(
            $customer->f_name . ' ' . $customer->l_name,
            $customer['email'],
            $customer->phone,
            ''
        );

        $payment_info = new PaymentInfo(
            success_hook: 'add_fund_to_wallet_success',
            failure_hook: 'add_fund_to_wallet_fail',
            currency_code: getWebConfig(name: 'currency_model') == 'multi_currency' ? 'USD' : $currency_code,
            payment_method: $request->payment_method,
            payment_platform: $request->payment_platform,
            payer_id: $customer->id,
            receiver_id: '100',
            additional_data: $additional_data,
            payment_amount: Convert::usdPaymentModule($request->amount, $currentCurrency),
            external_redirect_link: $request->payment_platform == 'web' ? $request->external_redirect_link : null,
            attribute: 'add_funds_to_wallet',
            attribute_id: idate("U")
        );

        $receiver_info = new Receiver('receiver_name', 'example.png');

        $redirect_link = Payment::generate_link($payer, $payment_info, $receiver_info);

        if (in_array($request['payment_request_from'], ['app'])) {
            return response()->json(['redirect_link' => $redirect_link], 200);
        } else {
            return redirect($redirect_link);
        }
    }


    public function servicePayment($id)
    {
        if (!auth('customer')->check()) {
            Toastr::error(translate('please_login_first'));
            return redirect()->route('customer.auth.login');
        }

        $invoice = ServiceInvoice::with('ticket')->find($id);
        if (!$invoice || $invoice->payment_status !== 'pending' || !$invoice->ticket) {
            Toastr::error(translate('Invoice not found or already paid'));
            return redirect('/');
        }

        if ((int)$invoice->ticket->customer_id !== (int)auth('customer')->id()) {
            Toastr::error(translate('you_have_no_access_to_this_invoice'));
            return redirect('/');
        }

        $payment_gateways_list = payment_gateways();
        $digital_payment = getWebConfig(name: 'digital_payment');

        return view(VIEW_FILE_NAMES['service_payment'], compact('invoice', 'payment_gateways_list', 'digital_payment'));
    }

    public function service_payment_request(Request $request)
    {
        if (!auth('customer')->check()) {
            Toastr::error(translate('please_login_first'));
            return redirect()->route('customer.auth.login');
        }

        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|exists:service_invoices,id',
            'payment_method' => 'required',
            'payment_platform' => 'required|in:web,app',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                Toastr::error($error);
            }
            return back();
        }

        $invoice = ServiceInvoice::with('ticket')->find($request->invoice_id);
        if (!$invoice || $invoice->payment_status !== 'pending' || !$invoice->ticket) {
            Toastr::error(translate('Invoice not found or already paid'));
            return back();
        }

        if ((int)$invoice->ticket->customer_id !== (int)auth('customer')->id()) {
            Toastr::error(translate('you_have_no_access_to_this_invoice'));
            return back();
        }

        $customer = auth('customer')->user();
        session([
            'payment_type' => 'service_invoice',
            'invoice_id' => $invoice->id,
        ]);

        $additional_data = [
            'business_name' => getWebConfig('company_name'),
            'business_logo' => getStorageImages(path: getWebConfig('company_web_logo'), type: 'shop'),
            'payment_mode' => $request->payment_platform,
            'invoice_id' => $invoice->id,
            'service_invoice_id' => $invoice->id,
            'ticket_id' => $invoice->ticket_id,
            'customer_id' => $customer->id,
            'is_guest' => 0,
            'is_guest_in_order' => 0,
            'new_customer_id' => null,
            'address_id' => null,
            'billing_address_id' => null,
            'order_note' => 'Service Invoice Payment - Ticket #' . $invoice->ticket_id,
            'payment_request_from' => 'service',
        ];

        $payer = new Payer(
            $customer->f_name . ' ' . $customer->l_name,
            $customer->email,
            $customer->phone,
            ''
        );

        $currency_model = getWebConfig(name: 'currency_model');
        if ($currency_model == 'multi_currency') {
            $currency_code = 'USD';
        } else {
            $default = getWebConfig(name: 'system_default_currency');
            $currency_code = Currency::find($default)->code;
        }

        $payment_info = new PaymentInfo(
            success_hook: 'service_invoice_payment_success',
            failure_hook: 'service_invoice_payment_fail',
            currency_code: $currency_code,
            payment_method: $request->payment_method,
            payment_platform: $request->payment_platform,
            payer_id: $customer->id,
            receiver_id: '100',
            additional_data: $additional_data,
            payment_amount: $invoice->total,
            external_redirect_link: route('web-payment-success'),
            attribute: 'service_invoice',
            attribute_id: $invoice->id
        );

        $receiver_info = new Receiver('receiver_name', 'example.png');
        $redirect_link = $this->generate_link($payer, $payment_info, $receiver_info);

        if ($request->payment_platform === 'app') {
            return response()->json(['redirect_link' => $redirect_link], 200);
        }

        return redirect($redirect_link);
    }

    private function getPaymentRequestFromToken(?string $token): ?PaymentRequest
    {
        if (!$token) {
            return null;
        }

        $token = str_replace(' ', '+', $token);
        $decoded = base64_decode($token, true);
        if (!$decoded) {
            return null;
        }

        $payload = [];
        foreach (explode('&&', $decoded) as $pair) {
            $parts = explode('=', $pair, 2);
            if (count($parts) === 2) {
                $payload[$parts[0]] = $parts[1];
            }
        }

        $paymentMethod = $payload['payment_method'] ?? null;
        $transactionReference = $payload['transaction_reference'] ?? null;
        if (!$paymentMethod || !$transactionReference) {
            return null;
        }

        return PaymentRequest::query()
            ->where('payment_method', $paymentMethod)
            ->where('transaction_id', $transactionReference)
            ->latest('updated_at')
            ->first();
    }

    private function resolveServiceInvoiceId(?PaymentRequest $paymentRequest): ?int
    {
        if ($paymentRequest) {
            $fromPaymentRequest = $this->resolveServiceInvoiceIdFromPaymentRequest($paymentRequest);
            if ($fromPaymentRequest) {
                return $fromPaymentRequest;
            }
        }

        $sessionInvoiceId = session('invoice_id');
        return $sessionInvoiceId ? (int)$sessionInvoiceId : null;
    }

    private function resolveServiceInvoiceIdFromPaymentRequest(PaymentRequest $paymentRequest): ?int
    {
        if ($paymentRequest->attribute === 'service_invoice' && $paymentRequest->attribute_id) {
            return (int)$paymentRequest->attribute_id;
        }

        if ($paymentRequest->additional_data) {
            $additionalData = json_decode($paymentRequest->additional_data, true);
            if (is_array($additionalData)) {
                $invoiceId = $additionalData['service_invoice_id'] ?? $additionalData['invoice_id'] ?? null;
                if ($invoiceId) {
                    return (int)$invoiceId;
                }
            }
        }

        return null;
    }

    private function isValidServicePaymentSuccess(?PaymentRequest $paymentRequest, ?int $resolvedInvoiceId): bool
    {
        if (!$paymentRequest || (int)$paymentRequest->is_paid !== 1) {
            return false;
        }

        if ($paymentRequest->attribute !== 'service_invoice') {
            return false;
        }

        $expectedInvoiceId = $this->resolveServiceInvoiceIdFromPaymentRequest($paymentRequest);
        if (!$expectedInvoiceId || !$resolvedInvoiceId) {
            return false;
        }

        return $expectedInvoiceId === (int)$resolvedInvoiceId;
    }

    private function afterServiceInvoicePaymentSuccess(ServiceInvoice $invoice): void
    {
        app(ServiceInvoicePaymentService::class)->handlePaidInvoice($invoice);
    }
}
