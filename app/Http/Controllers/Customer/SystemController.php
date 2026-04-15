<?php

namespace App\Http\Controllers\Customer;

use App\Models\Area;
use App\Models\City;
use App\Models\DeliveryArea;
use App\Models\DeliveryCity;
use App\Models\DeliveryCountryCode;
use App\Models\DeliveryState;
use App\Models\State;
use App\Models\User;
use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Models\ShippingAddress;
use App\Models\ShippingMethod;
use App\Models\CartShipping;
use App\Traits\CommonTrait;
use App\Utils\CartManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Library\Constant;

class SystemController extends Controller
{
    use CommonTrait;

    public function setPaymentMethod($name): JsonResponse
    {
        if (auth('customer')->check() || session()->has('mobile_app_payment_customer_id')) {
            session()->put('payment_method', $name);
            return response()->json(['status' => 1]);
        }
        return response()->json(['status' => 0]);
    }

    public function setShippingMethod(Request $request): JsonResponse
    {
        if ($request['cart_group_id'] == 'all_cart_group') {
            foreach (CartManager::get_cart_group_ids() as $groupId) {
                $request['cart_group_id'] = $groupId;
                self::insertIntoCartShipping($request);
            }
        } else {
            self::insertIntoCartShipping($request);
        }
        return response()->json(['status' => 1]);
    }

    public static function insertIntoCartShipping($request): void
    {
        $shipping = CartShipping::where(['cart_group_id' => $request['cart_group_id']])->first();
           Log::info('SAVE ADDRESS shipping payload', $shipping ? $shipping->toArray() : []);

        if (isset($shipping) == false) {
            $shipping = new CartShipping();
        }
        $shipping['cart_group_id'] = $request['cart_group_id'];
        $shipping['shipping_method_id'] = $request['id'];
        $shipping['shipping_cost'] = ShippingMethod::find($request['id'])->cost;
        $shipping->save();
    }

    /*
     * default theme
     * @return json
     */
    public function getChooseShippingAddress(Request $request): JsonResponse
    {
        $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');

        $physical_product = $request['physical_product'];
        $shipping = [];
        $billing = [];

        parse_str($request['shipping'], $shipping);
        parse_str($request['billing'], $billing);
        $is_guest = !auth('customer')->check();

        if (isset($shipping['save_address']) && $shipping['save_address'] == 'on') {

            if ($shipping['contact_person_name'] == null || $shipping['address'] == null || $shipping['city'] == null || $shipping['country'] == null || ($is_guest && $shipping['email'] == null)) {
                return response()->json([
                    'errors' => translate('Fill_all_required_fields_of_shipping_address')
                ], 403);
            } elseif ($country_restrict_status && !self::delivery_country_exist_check($shipping['country'])) {
                return response()->json([
                    'errors' => translate('Delivery_unavailable_in_this_country.')
                ], 403);
            }


            $address_id = DB::table('shipping_addresses')->insertGetId([
                'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id') : 0)),
                'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                'contact_person_name' => $shipping['contact_person_name'],
                'address_type' => $shipping['address_type'],
                'address' => $shipping['address'],
                'city' => $shipping['city'],
                'zip' => $shipping['zip'] ?? null,
                'country' => $shipping['country'],
                'phone' => $shipping['phone'],
                'email' => auth('customer')->check() ? null : $shipping['email'],
                'latitude' => $shipping['latitude'],
                'longitude' => $shipping['longitude'],
                'is_billing' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else if (isset($shipping['shipping_method_id']) && $shipping['shipping_method_id'] == 0) {

            if ($shipping['contact_person_name'] == null || $shipping['address'] == null || $shipping['city'] == null || $shipping['country'] == null || ($is_guest && $shipping['email'] == null)) {
                return response()->json([
                    'errors' => translate('Fill_all_required_fields_of_shipping/billing_address_part_2')
                ], 403);
            } elseif ($country_restrict_status && !self::delivery_country_exist_check($shipping['country'])) {
                return response()->json([
                    'errors' => translate('Delivery_unavailable_in_this_country')
                ], 403);
            }

            $address_id = DB::table('shipping_addresses')->insertGetId([
                'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id') : 0)),
                'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                'contact_person_name' => $shipping['contact_person_name'],
                'address_type' => $shipping['address_type'],
                'address' => $shipping['address'],
                'city' => $shipping['city'],
                'zip' => $shipping['zip'] ?? null,
                'country' => $shipping['country'],
                'phone' => $shipping['phone'],
                'email' => auth('customer')->check() ? null : $shipping['email'],
                'latitude' => $shipping['latitude'],
                'longitude' => $shipping['longitude'],
                'is_billing' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            if (isset($shipping['shipping_method_id'])) {
                $address = ShippingAddress::find($shipping['shipping_method_id']);
                if (!$address->country) {
                    return response()->json([
                        'errors' => translate('Please_update_country_for_this_shipping_address')
                    ], 403);
                } elseif ($country_restrict_status && !self::delivery_country_exist_check($address->country)) {
                    return response()->json([
                        'errors' => translate('Delivery_unavailable_in_this_country')
                    ], 403);
                }
                $address_id = $shipping['shipping_method_id'];
            } else {
                $address_id =  0;
            }
        }

        if ($request->billing_addresss_same_shipping == 'false') {
            if (isset($billing['save_address_billing']) && $billing['save_address_billing'] == 'on') {

                if ($billing['billing_contact_person_name'] == null || $billing['billing_address'] == null || $billing['billing_city'] == null || $billing['billing_country'] == null || ($is_guest && $billing['billing_contact_email'] == null)) {
                    return response()->json([
                        'errors' => translate('Fill_all_required_fields_of_billing_address_part_3')
                    ], 403);
                } elseif ($country_restrict_status && !self::delivery_country_exist_check($billing['billing_country'])) {
                    return response()->json([
                        'errors' => translate('Delivery_unavailable_in_this_country')
                    ], 403);
                }

                $billing_address_id = DB::table('shipping_addresses')->insertGetId([
                    'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id') : 0)),
                    'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                    'contact_person_name' => $billing['billing_contact_person_name'],
                    'address_type' => $billing['billing_address_type'],
                    'address' => $billing['billing_address'],
                    'city' => $billing['billing_city'],
                    'zip' => $billing['billing_zip'],
                    'country' => $billing['billing_country'],
                    'phone' => $billing['billing_phone'],
                    'email' => auth('customer')->check() ? null : $billing['billing_contact_email'],
                    'latitude' => $billing['billing_latitude'],
                    'longitude' => $billing['billing_longitude'],
                    'is_billing' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($billing['billing_method_id'] == 0) {

                if ($billing['billing_contact_person_name'] == null || $billing['billing_address'] == null || $billing['billing_city'] == null ||  $billing['billing_country'] == null || ($is_guest && $billing['billing_contact_email'] == null)) {
                    return response()->json([
                        'errors' => translate('Fill_all_required_fields_of_billing_address')
                    ], 403);
                } elseif ($country_restrict_status && !self::delivery_country_exist_check($billing['billing_country'])) {
                    return response()->json([
                        'errors' => translate('Delivery_unavailable_in_this_country')
                    ], 403);
                }

                $billing_address_id = DB::table('shipping_addresses')->insertGetId([
                    'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id') : 0)),
                    'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                    'contact_person_name' => $billing['billing_contact_person_name'],
                    'address_type' => $billing['billing_address_type'],
                    'address' => $billing['billing_address'],
                    'city' => $billing['billing_city'],
                    'zip' => $billing['billing_zip'],
                    'country' => $billing['billing_country'],
                    'phone' => $billing['billing_phone'],
                    'email' => auth('customer')->check() ? null : $billing['billing_contact_email'],
                    'latitude' => $billing['billing_latitude'],
                    'longitude' => $billing['billing_longitude'],
                    'is_billing' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $address = ShippingAddress::find($billing['billing_method_id']);
                if ($physical_product == 'yes') {
                    if (!$address->country) {
                        return response()->json([
                            'errors' => translate('Update_country_for_this_billing_address')
                        ], 403);
                    } elseif ($country_restrict_status && !self::delivery_country_exist_check($address->country)) {
                        return response()->json([
                            'errors' => translate('Delivery_unavailable_in_this_country')
                        ], 403);
                    }
                }
                $billing_address_id = $billing['billing_method_id'];
            }
        } else {
            $billing_address_id = $address_id;
        }

        session()->put('address_id', $address_id);
        session()->put('billing_address_id', $billing_address_id);

        return response()->json([], 200);
    }

    /*
     * Except Default Theme
     * @return json
     */
    // public function getChooseShippingAddressOther(Request $request): JsonResponse
    // {
    //     $shipping = [];
    //     $billing = [];
    //     parse_str($request['shipping'], $shipping);
    //     parse_str($request['billing'], $billing);

    //     session()->put('nearest_branch', $shipping['nearest_branch']);
    //     session()->put('delivery_type', $shipping['delivery_type']);
    //     session()->put('pickup_branch_id', $shipping['pickup_branch_id']);
    //     if (isset($shipping['phone'])) {
    //         $shippingPhoneValue = preg_replace('/[^0-9]/', '', $shipping['phone']);
    //         $shippingPhoneLength = strlen($shippingPhoneValue);
    //         if ($shippingPhoneLength < 4) {
    //             return response()->json([
    //                 'errors' => translate('The_phone_number_must_be_at_least_4_characters')
    //             ], 403);
    //         }
    //         if ($shippingPhoneLength > 20) {
    //             return response()->json([
    //                 'errors' => translate('The_phone_number_may_not_be_greater_than_20_characters')
    //             ], 403);
    //         }
    //     }

    //     if ($request['billing_addresss_same_shipping'] == 'false' && isset($billing['billing_phone'])) {
    //         $billingPhoneValue = preg_replace('/[^0-9]/', '', $billing['billing_phone']);
    //         $billingPhoneLength = strlen($billingPhoneValue);
    //         if ($billingPhoneLength < 4) {
    //             return response()->json([
    //                 'errors' => translate('The_phone_number_must_be_at_least_4_characters')
    //             ], 403);
    //         }

    //         if ($billingPhoneLength > 20) {
    //             return response()->json([
    //                 'errors' => translate('The_phone_number_may_not_be_greater_than_20_characters')
    //             ], 403);
    //         }
    //     }

    //     $physicalProduct = $request['physical_product'];
    //     $countryRestrictStatus = getWebConfig(name: 'delivery_country_restriction');
    //     $billingInputByCustomer = getWebConfig(name: 'billing_input_by_customer');
    //     $isGuestCustomer = !auth('customer')->check();

    //     // Shipping start
    //     $addressId = $shipping['shipping_method_id'] ?? 0;

    //     if ($shipping['delivery_type'] != 'pickup' && isset($shipping['shipping_method_id'])) {
    //         if ($shipping['contact_person_name'] == null || !isset($shipping['address_type']) || $shipping['address'] == null || $shipping['city'] == null || $shipping['state'] == null || $shipping['area'] == null || !isset($shipping['country']) || $shipping['country'] == null || $shipping['phone'] == null || ($isGuestCustomer && $shipping['email'] == null)) {
    //             return response()->json([
    //                 'errors' => translate('Fill_all_required_fields_of_shipping_address')
    //             ], 403);
    //         } elseif ($countryRestrictStatus && !self::delivery_country_exist_check($shipping['country'])) {
    //             return response()->json([
    //                 'errors' => translate('Delivery_unavailable_in_this_country.')
    //             ], 403);
    //         }
    //     }

    //     if (isset($shipping['save_address']) && $shipping['save_address'] == 'on') {
    //         $addressId = ShippingAddress::insertGetId([
    //             'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id') : 0)),
    //             'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
    //             'contact_person_name' => $shipping['contact_person_name'],
    //             'address_type' => $shipping['address_type'],
    //             'address' => $shipping['address'],
    //             'city' => $shipping['city'],
    //             'state' => $shipping['state'],
    //             'area' => $shipping['area'],
    //             'zip' => $shipping['zip'] ?? null,
    //             'country' => $shipping['country'],
    //             'phone' => $shipping['phone'],
    //             'latitude' => $shipping['latitude'],
    //             'longitude' => $shipping['longitude'],
    //             'email' => auth('customer')->check() ? null : $shipping['email'],
    //             'is_billing' => 0,
    //         ]);
    //     } elseif (isset($shipping['update_address']) && $shipping['update_address'] == 'on') {
    //         $getShipping = ShippingAddress::find($addressId);
    //         $getShipping->contact_person_name = $shipping['contact_person_name'];
    //         $getShipping->address_type = $shipping['address_type'];
    //         $getShipping->address = $shipping['address'];
    //         $getShipping->city = $shipping['city'];
    //         $getShipping->state = $shipping['state'];
    //         $getShipping->area = $shipping['area'];
    //         $getShipping->zip = $shipping['zip'] ?? null;
    //         $getShipping->country = $shipping['country'];
    //         $getShipping->phone = $shipping['phone'];
    //         $getShipping->latitude = $shipping['latitude'];
    //         $getShipping->longitude = $shipping['longitude'];
    //         $getShipping->save();
    //     } elseif (isset($shipping['shipping_method_id']) && !isset($shipping['update_address']) && !isset($shipping['save_address'])) {
    //         $addressId = ShippingAddress::insertGetId([
    //             'customer_id' => auth('customer')->check() ? 0 : ((session()->has('guest_id') ? session('guest_id') : 0)),
    //             'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
    //             'contact_person_name' => $shipping['contact_person_name'],
    //             'address_type' => $shipping['address_type'],
    //             'address' => $shipping['address'],
    //             'city' => $shipping['city'],
    //             'state' => $shipping['state'],
    //             'area' => $shipping['area'],
    //             'zip' => $shipping['zip'] ?? null,
    //             'country' => $shipping['country'],
    //             'phone' => $shipping['phone'],
    //             'email' => auth('customer')->check() ? null : $shipping['email'],
    //             'latitude' => $shipping['latitude'] ?? '',
    //             'longitude' => $shipping['longitude'] ?? '',
    //             'is_billing' => 0,
    //         ]);
    //     }
    //     // Shipping End

    //     // Billing Start
    //     $billingAddressId = $addressId ?? 0;
    //     if ($request['billing_addresss_same_shipping'] == 'false' && isset($billing['billing_method_id']) && $billingInputByCustomer) {
    //         $billingAddressId = $billing['billing_method_id'];


    //         if ($billing['billing_contact_person_name'] == null || !isset($billing['billing_address_type']) || !isset($billing['billing_address']) || $billing['billing_address'] == null || $billing['billing_city'] == null || $billing['billing_state'] == null || $billing['billing_area'] == null || !isset($billing['billing_country']) || $billing['billing_country'] == null || $billing['billing_phone'] == null || ($isGuestCustomer && $billing['billing_contact_email'] == null)) {
    //             return response()->json([
    //                 'errors' => translate('Fill_all_required_fields_of_billing_address')
    //             ], 403);
    //         } elseif ($countryRestrictStatus && !self::delivery_country_exist_check($billing['billing_country'])) {
    //             return response()->json([
    //                 'errors' => translate('Delivery_unavailable_in_this_country')
    //             ], 403);
    //         }

    //         if (isset($billing['save_address_billing']) && $billing['save_address_billing'] == 'on') {
    //             $billingAddressId = ShippingAddress::insertGetId([
    //                 'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id') : 0)),
    //                 'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
    //                 'contact_person_name' => $billing['billing_contact_person_name'],
    //                 'address_type' => $billing['billing_address_type'],
    //                 'address' => $billing['billing_address'],
    //                 'city' => $billing['billing_city'],
    //                 'state' => $billing['billing_state'],
    //                 'area' => $billing['billing_area'],
    //                 'zip' => $billing['billing_zip'] ?? null,
    //                 'country' => $billing['billing_country'],
    //                 'phone' => $billing['billing_phone'],
    //                 'email' => auth('customer')->check() ? null : $billing['billing_contact_email'],
    //                 'latitude' => $billing['billing_latitude'] ?? '',
    //                 'longitude' => $billing['billing_longitude'] ?? '',
    //                 'is_billing' => 1,
    //             ]);
    //         } elseif (isset($billing['update_billing_address']) && $billing['update_billing_address'] == 'on') {
    //             $getBilling = ShippingAddress::find($billingAddressId);
    //             $getBilling->contact_person_name = $billing['billing_contact_person_name'];
    //             $getBilling->address_type = $billing['billing_address_type'];
    //             $getBilling->address = $billing['billing_address'];
    //             $getBilling->city = $billing['billing_city'];
    //             $getBilling->area = $billing['billing_area'];
    //             $getBilling->state = $billing['billing_state'];
    //             $getBilling->zip = $billing['billing_zip'] ?? null;
    //             $getBilling->country = $billing['billing_country'];
    //             $getBilling->phone = $billing['billing_phone'];
    //             $getBilling->latitude = $billing['billing_latitude'];
    //             $getBilling->longitude = $billing['billing_longitude'];
    //             $getBilling->save();
    //         } elseif (!isset($billing['update_billing_address']) && !isset($billing['save_address_billing'])) {
    //             $billingAddressId = ShippingAddress::insertGetId([
    //                 'customer_id' => auth('customer')->check() ? 0 : ((session()->has('guest_id') ? session('guest_id') : 0)),
    //                 'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
    //                 'contact_person_name' => $billing['billing_contact_person_name'],
    //                 'address_type' => $billing['billing_address_type'],
    //                 'address' => $billing['billing_address'],
    //                 'city' => $billing['billing_city'],
    //                 'state' => $billing['billing_state'],
    //                 'area' => $billing['billing_area'],
    //                 'zip' => $billing['billing_zip'] ?? null,
    //                 'country' => $billing['billing_country'],
    //                 'phone' => $billing['billing_phone'],
    //                 'email' => auth('customer')->check() ? null : $billing['billing_contact_email'],
    //                 'latitude' => $billing['billing_latitude'] ?? '',
    //                 'longitude' => $billing['billing_longitude'] ?? '',
    //                 'is_billing' => 1,
    //             ]);
    //         }
    //     } elseif (
    //         ($request['billing_addresss_same_shipping'] == 'true' || $shipping['delivery_type'] == 'pickup')
    //         && !isset($billing['billing_method_id'])
    //         && $physicalProduct != 'yes'
    //     ) {
    //         return response()->json([
    //             'errors' => translate('Fill_all_required_fields_of_billing_address')
    //         ], 403);
    //     } elseif ($request['billing_addresss_same_shipping'] == 'true' && !isset($billing['billing_method_id']) && $physicalProduct != 'yes') {
    //         return response()->json([
    //             'errors' => translate('Fill_all_required_fields_of_billing_address')
    //         ], 403);
    //     }

    //     session()->put('address_id', $addressId);
    //     session()->put('billing_address_id', $billingAddressId);

    //     if ($request['is_check_create_account'] && $isGuestCustomer) {
    //         if (empty($request['customer_password']) || empty($request['customer_confirm_password'])) {
    //             return response()->json([
    //                 'errors' => translate('The_password_or_confirm_password_can_not_be_empty')
    //             ], 403);
    //         }
    //         if ($request['customer_password'] != $request['customer_confirm_password']) {
    //             return response()->json([
    //                 'errors' => translate('The_password_and_confirm_password_must_match')
    //             ], 403);
    //         }
    //         if (strlen($request['customer_password']) < 7 || strlen($request['customer_confirm_password']) < 7) {
    //             return response()->json([
    //                 'errors' => translate('The_password_must_be_at_least_8_characters')
    //             ], 403);
    //         }
    //         if ($request['shipping']) {
    //             $newCustomerAddress = [
    //                 'name' => $shipping['contact_person_name'],
    //                 'email' => $shipping['email'],
    //                 'phone' => $shipping['phone'],
    //                 'password' => $request['customer_password'],
    //             ];
    //         } else {
    //             $newCustomerAddress = [
    //                 'name' => $billing['billing_contact_person_name'],
    //                 'email' => $billing['billing_contact_email'],
    //                 'phone' => $billing['billing_phone'],
    //                 'password' => $request['customer_password'],
    //             ];
    //         }

    //         if (User::where(['email' => $newCustomerAddress['email']])->orWhere(['phone' => $newCustomerAddress['phone']])->first()) {
    //             return response()->json(['errors' => translate('Already_registered')], 403);
    //         } else {
    //             $newCustomerRegister = self::getRegisterNewCustomer(request: $request, address: $newCustomerAddress);
    //             session()->put('newCustomerRegister', $newCustomerRegister);
    //         }
    //     } else {
    //         session()->forget('newCustomerRegister');
    //         session()->forget('newRegisterCustomerInfo');
    //     }

    //     return response()->json([], 200);
    // }

    public function getChooseShippingAddressOther(Request $request): JsonResponse
    {
        if (!$request->has('shipping') || !$request->has('billing')) {
            return $this->checkoutValidationError(
                message: translate('Please_fill_all_required_shipping_and_billing_information'),
                status: 403
            );
        }

        $shipping = [];
        $billing = [];

        try {
            parse_str($request['shipping'], $shipping);
            parse_str($request['billing'], $billing);
        } catch (\Exception $e) {
            return $this->checkoutValidationError(
                message: translate('Invalid_shipping_or_billing_data_format'),
                status: 403
            );
        }

        $defaultShippingValues = [
            'delivery_type' => null,
            'nearest_branch' => null,
            'pickup_branch_id' => null,
            'contact_person_name' => null,
            'address_type' => null,
            'address' => null,
            'state_id' => null,
            'city' => null,
            'city_id' => null,
            'state' => null,
            'area' => null,
            'area_id' => null,
            'country' => null,
            'phone' => null,
            'email' => null,
            'latitude' => null,
            'longitude' => null,
            'zip' => null,
            'save_address' => null,
            'update_address' => null,
            'shipping_method_id' => null
        ];

        $defaultBillingValues = [
            'billing_method_id' => null,
            'billing_contact_person_name' => null,
            'billing_address_type' => null,
            'billing_address' => null,
            'billing_city' => null,
            'billing_city_id' => null,
            'billing_state' => null,
            'billing_state_id' => null,
            'billing_area' => null,
            'billing_area_id' => null,
            'billing_country' => null,
            'billing_phone' => null,
            'billing_contact_email' => null,
            'billing_latitude' => null,
            'billing_longitude' => null,
            'billing_zip' => null,
            'save_address_billing' => null,
            'update_billing_address' => null
        ];

        // Merge default values with actual values
        $shipping = array_merge($defaultShippingValues, $shipping);
        $billing = array_merge($defaultBillingValues, $billing);
        $shipping = $this->normalizeShippingCheckoutData($shipping);
        $billing = $this->normalizeBillingCheckoutData($billing);

        $isFormEmpty = true;
        foreach ($shipping as $key => $value) {
            if (!empty($value) && !in_array($key, ['delivery_type', 'nearest_branch', 'pickup_branch_id', 'latitude', 'longitude', 'zip'])) {
                $isFormEmpty = false;
                break;
            }
        }

        if ($isFormEmpty) {
            return $this->checkoutValidationError(
                message: translate('Please_fill_all_required_fields_in_shipping_address'),
                status: 403
            );
        }

        session()->put('nearest_branch', $shipping['nearest_branch']);
        session()->put('delivery_type', $shipping['delivery_type']);
        session()->put('pickup_branch_id', $shipping['pickup_branch_id']);

        // Step 6: Phone validation (existing code)
        if (isset($shipping['phone']) && !empty($shipping['phone'])) {
            $shippingPhoneValue = preg_replace('/[^0-9]/', '', $shipping['phone']);
            $shippingPhoneLength = strlen($shippingPhoneValue);
            if ($shippingPhoneLength < 4) {
                return $this->checkoutValidationError(
                    message: translate('The_phone_number_must_be_at_least_4_characters'),
                    fieldErrors: ['phone' => translate('The_phone_number_must_be_at_least_4_characters')],
                    focusField: 'phone',
                    status: 403
                );
            }
            if ($shippingPhoneLength > 20) {
                return $this->checkoutValidationError(
                    message: translate('The_phone_number_may_not_be_greater_than_20_characters'),
                    fieldErrors: ['phone' => translate('The_phone_number_may_not_be_greater_than_20_characters')],
                    focusField: 'phone',
                    status: 403
                );
            }
        }

        if ($request['billing_addresss_same_shipping'] == 'false' && isset($billing['billing_phone']) && !empty($billing['billing_phone'])) {
            $billingPhoneValue = preg_replace('/[^0-9]/', '', $billing['billing_phone']);
            $billingPhoneLength = strlen($billingPhoneValue);
            if ($billingPhoneLength < 4) {
                return $this->checkoutValidationError(
                    message: translate('The_phone_number_must_be_at_least_4_characters'),
                    fieldErrors: ['billing_phone' => translate('The_phone_number_must_be_at_least_4_characters')],
                    focusField: 'billing_phone',
                    status: 403
                );
            }

            if ($billingPhoneLength > 20) {
                return $this->checkoutValidationError(
                    message: translate('The_phone_number_may_not_be_greater_than_20_characters'),
                    fieldErrors: ['billing_phone' => translate('The_phone_number_may_not_be_greater_than_20_characters')],
                    focusField: 'billing_phone',
                    status: 403
                );
            }
        }

        $physicalProduct = $request['physical_product'];
        $countryRestrictStatus = getWebConfig(name: 'delivery_country_restriction');
        $zipRestrictStatus = getWebConfig(name: 'delivery_zip_code_area_restriction');
        $billingInputByCustomer = getWebConfig(name: 'billing_input_by_customer');
        $isGuestCustomer = !auth('customer')->check();

        // Shipping start
        $addressId = !empty($shipping['shipping_method_id']) ? $shipping['shipping_method_id'] : 0;

        // Step 7: Validate required fields when delivery_type is not pickup
        if ($shipping['delivery_type'] != 'pickup') {
            $requiredFields = ['contact_person_name', 'address_type', 'address', 'phone'];

            $countryRestrictionEnabled = (int)getWebConfig(name: 'delivery_country_restriction') === 1;
            $stateRestrictionEnabled = (int)getWebConfig(name: 'delivery_state_restriction') === 1;
            $cityRestrictionEnabled = (int)getWebConfig(name: 'delivery_city_restriction') === 1;
            $areaRestrictionEnabled = (int)getWebConfig(name: 'delivery_area_restriction') === 1;

            if ($countryRestrictionEnabled) {
                $requiredFields[] = 'country';
            }
            if ($stateRestrictionEnabled) {
                $requiredFields[] = 'state';
            }
            if ($cityRestrictionEnabled) {
                $requiredFields[] = 'city';
            }
            if ($areaRestrictionEnabled) {
                $requiredFields[] = 'area';
            }

            if ($isGuestCustomer) {
                $requiredFields[] = 'email';
            }

            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (empty($shipping[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                return $this->checkoutValidationError(
                    message: $this->buildCheckoutMissingFieldsMessage($missingFields, 'Please_fill_the_following_fields'),
                    fieldErrors: $this->buildCheckoutFieldErrors($missingFields),
                    focusField: $missingFields[0],
                    status: 403
                );
            }

            if ($countryRestrictionEnabled && !self::delivery_country_exist_check($shipping['country'])) {
                return $this->checkoutValidationError(
                    message: translate('Delivery_unavailable_in_this_country.'),
                    fieldErrors: ['country' => translate('Delivery_unavailable_in_this_country.')],
                    focusField: 'country',
                    status: 403
                );
            }
        }

        if ($shipping['delivery_type'] != 'pickup') {
            $shippingRestrictionError = $this->validateShippingDeliveryLocation($shipping);
            if ($shippingRestrictionError) {
                return $this->checkoutValidationError(
                    message: $shippingRestrictionError['message'],
                    fieldErrors: [$shippingRestrictionError['field'] => $shippingRestrictionError['message']],
                    focusField: $shippingRestrictionError['field'],
                    status: 403
                );
            }

            if ((int)$zipRestrictStatus === 1) {
                if (empty($shipping['zip'])) {
                    return $this->checkoutValidationError(
                        message: $this->buildCheckoutMissingFieldsMessage(['zip'], 'Please_fill_the_following_fields'),
                        fieldErrors: $this->buildCheckoutFieldErrors(['zip']),
                        focusField: 'zip',
                        status: 403
                    );
                }

                if (!self::delivery_zipcode_exist_check($shipping['zip'])) {
                    return $this->checkoutValidationError(
                        message: translate('Delivery_unavailable_for_this_zip_code_area'),
                        fieldErrors: ['zip' => translate('Delivery_unavailable_for_this_zip_code_area')],
                        focusField: 'zip',
                        status: 403
                    );
                }
            }
        }

        if (isset($shipping['save_address']) && $shipping['save_address'] == 'on') {
            $addressId = ShippingAddress::insertGetId([
                'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id') : 0)),
                'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                'contact_person_name' => $shipping['contact_person_name'],
                'address_type' => $shipping['address_type'],
                'address' => $shipping['address'],
                'city' => $shipping['city'],
                'state' => $shipping['state'],
                'area' => $shipping['area'],
                'zip' => $shipping['zip'] ?? null,
                'country' => $shipping['country'],
                'phone' => $shipping['phone'],
                'latitude' => $shipping['latitude'],
                'longitude' => $shipping['longitude'],
                'email' => auth('customer')->check() ? null : $shipping['email'],
                'is_billing' => 0,
            ]);
        } elseif (isset($shipping['update_address']) && $shipping['update_address'] == 'on') {
            $getShipping = ShippingAddress::find($addressId);
            if ($getShipping) {
                $getShipping->contact_person_name = $shipping['contact_person_name'];
                $getShipping->address_type = $shipping['address_type'];
                $getShipping->address = $shipping['address'];
                $getShipping->city = $shipping['city'];
                $getShipping->state = $shipping['state'];
                $getShipping->area = $shipping['area'];
                $getShipping->zip = $shipping['zip'] ?? null;
                $getShipping->country = $shipping['country'];
                $getShipping->phone = $shipping['phone'];
                $getShipping->latitude = $shipping['latitude'];
                $getShipping->longitude = $shipping['longitude'];
                $getShipping->save();
            }
        } elseif (isset($shipping['shipping_method_id']) && !isset($shipping['update_address']) && !isset($shipping['save_address'])) {
            $addressId = ShippingAddress::insertGetId([
                'customer_id' => auth('customer')->check() ? 0 : ((session()->has('guest_id') ? session('guest_id') : 0)),
                'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                'contact_person_name' => $shipping['contact_person_name'],
                'address_type' => $shipping['address_type'],
                'address' => $shipping['address'],
                'city' => $shipping['city'],
                'state' => $shipping['state'],
                'area' => $shipping['area'],
                'zip' => $shipping['zip'] ?? null,
                'country' => $shipping['country'],
                'phone' => $shipping['phone'],
                'email' => auth('customer')->check() ? null : $shipping['email'],
                'latitude' => $shipping['latitude'] ?? '',
                'longitude' => $shipping['longitude'] ?? '',
                'is_billing' => 0,
            ]);
        }
        // Shipping End

        // Billing Start
        $billingAddressId = $addressId ?? 0;
        if ($request['billing_addresss_same_shipping'] == 'false' && isset($billing['billing_method_id']) && $billingInputByCustomer) {
            $billingAddressId = $billing['billing_method_id'];

            // Validate required billing fields
            $requiredBillingFields = ['billing_contact_person_name', 'billing_address_type', 'billing_address', 'billing_phone'];
            $restrictionFlags = $this->getCheckoutAddressRestrictionFlags();
            if (!$this->resolveSingleBillingCountryCode()) {
                $requiredBillingFields[] = 'billing_country';
            }
            if ($restrictionFlags['state']) {
                $requiredBillingFields[] = 'billing_state';
            }
            if ($restrictionFlags['city']) {
                $requiredBillingFields[] = 'billing_city';
            }
            if ($isGuestCustomer) {
                $requiredBillingFields[] = 'billing_contact_email';
            }

            $missingBillingFields = [];
            foreach ($requiredBillingFields as $field) {
                if (empty($billing[$field])) {
                    $missingBillingFields[] = $field;
                }
            }

            if (!empty($missingBillingFields)) {
                return $this->checkoutValidationError(
                    message: $this->buildCheckoutMissingFieldsMessage($missingBillingFields, 'Please_fill_the_following_billing_fields'),
                    fieldErrors: $this->buildCheckoutFieldErrors($missingBillingFields),
                    focusField: $missingBillingFields[0],
                    status: 403
                );
            }
            // elseif ($countryRestrictStatus && !self::delivery_country_exist_check($billing['billing_country'])) {
            //     return response()->json([
            //         'errors' => translate('Delivery_unavailable_in_this_country')
            //     ], 403);
            // }

            if (isset($billing['save_address_billing']) && $billing['save_address_billing'] == 'on') {
                $billingAddressId = ShippingAddress::insertGetId([
                    'customer_id' => auth('customer')->id() ?? ((session()->has('guest_id') ? session('guest_id') : 0)),
                    'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                    'contact_person_name' => $billing['billing_contact_person_name'],
                    'address_type' => $billing['billing_address_type'],
                    'address' => $billing['billing_address'],
                    'city' => $billing['billing_city'],
                    'state' => $billing['billing_state'],
                    'area' => $billing['billing_area'] ?? null,
                    'zip' => $billing['billing_zip'] ?? null,
                    'country' => $billing['billing_country'],
                    'phone' => $billing['billing_phone'],
                    'email' => auth('customer')->check() ? null : $billing['billing_contact_email'],
                    'latitude' => $billing['billing_latitude'] ?? '',
                    'longitude' => $billing['billing_longitude'] ?? '',
                    'is_billing' => 1,
                ]);
            } elseif (isset($billing['update_billing_address']) && $billing['update_billing_address'] == 'on') {
                $getBilling = ShippingAddress::find($billingAddressId);
                if ($getBilling) {
                    $getBilling->contact_person_name = $billing['billing_contact_person_name'];
                    $getBilling->address_type = $billing['billing_address_type'];
                    $getBilling->address = $billing['billing_address'];
                    $getBilling->city = $billing['billing_city'];
                    $getBilling->area = $billing['billing_area'] ?? null;
                    $getBilling->state = $billing['billing_state'];
                    $getBilling->zip = $billing['billing_zip'] ?? null;
                    $getBilling->country = $billing['billing_country'];
                    $getBilling->phone = $billing['billing_phone'];
                    $getBilling->latitude = $billing['billing_latitude'];
                    $getBilling->longitude = $billing['billing_longitude'];
                    $getBilling->save();
                }
            } elseif (!isset($billing['update_billing_address']) && !isset($billing['save_address_billing'])) {
                $billingAddressId = ShippingAddress::insertGetId([
                    'customer_id' => auth('customer')->check() ? 0 : ((session()->has('guest_id') ? session('guest_id') : 0)),
                    'is_guest' => auth('customer')->check() ? 0 : (session()->has('guest_id') ? 1 : 0),
                    'contact_person_name' => $billing['billing_contact_person_name'],
                    'address_type' => $billing['billing_address_type'],
                    'address' => $billing['billing_address'],
                    'city' => $billing['billing_city'],
                    'state' => $billing['billing_state'],
                    'area' => $billing['billing_area'] ?? null,
                    'zip' => $billing['billing_zip'] ?? null,
                    'country' => $billing['billing_country'],
                    'phone' => $billing['billing_phone'],
                    'email' => auth('customer')->check() ? null : $billing['billing_contact_email'],
                    'latitude' => $billing['billing_latitude'] ?? '',
                    'longitude' => $billing['billing_longitude'] ?? '',
                    'is_billing' => 1,
                ]);
            }
        } elseif (
            ($request['billing_addresss_same_shipping'] == 'true' || $shipping['delivery_type'] == 'pickup')
            && !isset($billing['billing_method_id'])
            && $physicalProduct != 'yes'
        ) {
            return $this->checkoutValidationError(
                message: translate('Fill_all_required_fields_of_billing_address'),
                status: 403
            );
        } elseif ($request['billing_addresss_same_shipping'] == 'true' && !isset($billing['billing_method_id']) && $physicalProduct != 'yes') {
            return $this->checkoutValidationError(
                message: translate('Fill_all_required_fields_of_billing_address'),
                status: 403
            );
        }

        if (empty($addressId) && empty($billingAddressId)) {
            return $this->checkoutValidationError(
                message: translate('Please_provide_at_least_one_shipping_or_billing_address'),
                status: 403
            );
        }

        session()->put('address_id', $addressId);
        session()->put('billing_address_id', $billingAddressId);

        if ($request['is_check_create_account'] && $isGuestCustomer) {
            if (empty($request['customer_password']) || empty($request['customer_confirm_password'])) {
                $emptyPasswordFields = [];
                if (empty($request['customer_password'])) {
                    $emptyPasswordFields[] = 'customer_password';
                }
                if (empty($request['customer_confirm_password'])) {
                    $emptyPasswordFields[] = 'customer_confirm_password';
                }

                return $this->checkoutValidationError(
                    message: translate('The_password_or_confirm_password_can_not_be_empty'),
                    fieldErrors: $this->buildCheckoutFieldErrors($emptyPasswordFields),
                    focusField: $emptyPasswordFields[0] ?? null,
                    status: 403
                );
            }
            if ($request['customer_password'] != $request['customer_confirm_password']) {
                return $this->checkoutValidationError(
                    message: translate('The_password_and_confirm_password_must_match'),
                    fieldErrors: [
                        'customer_password' => translate('The_password_and_confirm_password_must_match'),
                        'customer_confirm_password' => translate('The_password_and_confirm_password_must_match'),
                    ],
                    focusField: 'customer_password',
                    status: 403
                );
            }
            if (strlen($request['customer_password']) < 7 || strlen($request['customer_confirm_password']) < 7) {
                return $this->checkoutValidationError(
                    message: translate('The_password_must_be_at_least_8_characters'),
                    fieldErrors: [
                        'customer_password' => translate('The_password_must_be_at_least_8_characters'),
                        'customer_confirm_password' => translate('The_password_must_be_at_least_8_characters'),
                    ],
                    focusField: 'customer_password',
                    status: 403
                );
            }
            if ($request['shipping']) {
                $newCustomerAddress = [
                    'name' => $shipping['contact_person_name'],
                    'email' => $shipping['email'],
                    'phone' => $shipping['phone'],
                    'password' => $request['customer_password'],
                ];
            } else {
                $newCustomerAddress = [
                    'name' => $billing['billing_contact_person_name'],
                    'email' => $billing['billing_contact_email'],
                    'phone' => $billing['billing_phone'],
                    'password' => $request['customer_password'],
                ];
            }

            if (User::where(['email' => $newCustomerAddress['email']])->orWhere(['phone' => $newCustomerAddress['phone']])->first()) {
                $duplicateField = $request['shipping'] ? 'email' : 'billing_contact_email';
                return $this->checkoutValidationError(
                    message: translate('Already_registered'),
                    fieldErrors: [$duplicateField => translate('Already_registered')],
                    focusField: $duplicateField,
                    status: 403
                );
            } else {
                $newCustomerRegister = self::getRegisterNewCustomer(request: $request, address: $newCustomerAddress);
                session()->put('newCustomerRegister', $newCustomerRegister);
            }
        } else {
            session()->forget('newCustomerRegister');
            session()->forget('newRegisterCustomerInfo');
        }

        return response()->json([], 200);
    }

    function getRegisterNewCustomer($request, $address): array
    {
        return [
            'name' => $address['name'],
            'f_name' => $address['name'],
            'l_name' => '',
            'email' => $address['email'],
            'phone' => $address['phone'],
            'is_active' => 1,
            'password' => $address['password'],
            'referral_code' => Helpers::generate_referer_code(),
            'shipping_id' => session('address_id'),
            'billing_id' => session('billing_address_id'),
        ];
    }

    private function checkoutValidationError(string $message, array $fieldErrors = [], ?string $focusField = null, int $status = 422): JsonResponse
    {
        if ($focusField === null && !empty($fieldErrors)) {
            $focusField = array_key_first($fieldErrors);
        }

        return response()->json([
            'message' => $message,
            'field_errors' => $fieldErrors,
            'focus_field' => $focusField,
        ], $status);
    }

    private function buildCheckoutFieldErrors(array $fields, ?string $message = null): array
    {
        $fieldErrors = [];
        $resolvedMessage = $message ?? translate('Please_fill_out_this_field');

        foreach ($fields as $field) {
            $fieldErrors[$field] = $resolvedMessage;
        }

        return $fieldErrors;
    }

    private function buildCheckoutMissingFieldsMessage(array $fields, string $translationKey): string
    {
        $labels = array_map(fn($field) => $this->translateCheckoutFieldLabel($field), $fields);

        return translate($translationKey) . ': ' . implode(', ', $labels);
    }

    private function translateCheckoutFieldLabel(string $field): string
    {
        $fieldLabels = [
            'contact_person_name' => 'contact_person_name',
            'phone' => 'phone',
            'email' => 'email',
            'delivery_type' => 'Delivery_Type',
            'pickup_branch_id' => 'Pickup_branch',
            'address_type' => 'address_type',
            'country' => 'country',
            'state' => 'state',
            'state_id' => 'state',
            'city' => 'city',
            'city_id' => 'city',
            'area' => 'area',
            'zip' => 'zip_code',
            'address' => 'address',
            'billing_contact_person_name' => 'contact_person_name',
            'billing_phone' => 'phone',
            'billing_contact_email' => 'email',
            'billing_address_type' => 'address_type',
            'billing_country' => 'country',
            'billing_state' => 'state',
            'billing_state_id' => 'state',
            'billing_city' => 'city',
            'billing_city_id' => 'city',
            'billing_area' => 'area',
            'billing_zip' => 'zip_code',
            'billing_address' => 'address',
            'customer_password' => 'new_Password',
            'customer_confirm_password' => 'confirm_Password',
        ];

        return translate($fieldLabels[$field] ?? str_replace('_', ' ', $field));
    }

    private function getCheckoutAddressRestrictionFlags(): array
    {
        return [
            'country' => (int)getWebConfig(name: 'delivery_country_restriction') === 1,
            'state' => (int)getWebConfig(name: 'delivery_state_restriction') === 1,
            'city' => (int)getWebConfig(name: 'delivery_city_restriction') === 1,
            'area' => (int)getWebConfig(name: 'delivery_area_restriction') === 1,
        ];
    }

    private function normalizeShippingCheckoutData(array $shipping): array
    {
        return $this->normalizeCheckoutAddressData(
            address: $shipping,
            countryKey: 'country',
            stateKey: 'state',
            cityKey: 'city',
            areaKey: 'area',
            stateIdKey: 'state_id',
            cityIdKey: 'city_id',
            areaIdKey: 'area_id',
            singleCountryCode: $this->resolveSingleShippingCountryCode(),
        );
    }

    private function normalizeBillingCheckoutData(array $billing): array
    {
        return $this->normalizeCheckoutAddressData(
            address: $billing,
            countryKey: 'billing_country',
            stateKey: 'billing_state',
            cityKey: 'billing_city',
            areaKey: 'billing_area',
            stateIdKey: 'billing_state_id',
            cityIdKey: 'billing_city_id',
            areaIdKey: 'billing_area_id',
            singleCountryCode: $this->resolveSingleBillingCountryCode(),
        );
    }

    private function normalizeCheckoutAddressData(
        array $address,
        string $countryKey,
        string $stateKey,
        string $cityKey,
        string $areaKey,
        string $stateIdKey,
        string $cityIdKey,
        string $areaIdKey,
        ?string $singleCountryCode = null,
    ): array
    {
        $restrictionFlags = $this->getCheckoutAddressRestrictionFlags();

        if (empty($address[$countryKey]) && $singleCountryCode) {
            $address[$countryKey] = $singleCountryCode;
        }

        if (!$restrictionFlags['state']) {
            $address[$stateKey] = null;
            $address[$stateIdKey] = null;
        }

        if (!$restrictionFlags['city']) {
            $address[$cityKey] = null;
            $address[$cityIdKey] = null;
        }

        if (!$restrictionFlags['area']) {
            $address[$areaKey] = null;
            $address[$areaIdKey] = null;
        }

        return $address;
    }

    private function resolveSingleShippingCountryCode(): ?string
    {
        $allowedCountryCodes = null;
        if ((int)getWebConfig(name: 'delivery_country_restriction') === 1) {
            $allowedCountryCodes = DeliveryCountryCode::query()
                ->pluck('country_code')
                ->map(fn($countryCode) => $this->normalizeCountryCodeFromInput($countryCode))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return $this->resolveSingleCountryCodeFromStates($allowedCountryCodes);
    }

    private function resolveSingleBillingCountryCode(): ?string
    {
        return $this->resolveSingleCountryCodeFromStates();
    }

    private function resolveSingleCountryCodeFromStates(?array $allowedCountryCodes = null): ?string
    {
        $countryCodes = State::query()
            ->pluck('country')
            ->map(fn($country) => $this->normalizeCountryCodeFromInput($country))
            ->filter()
            ->unique()
            ->values();

        if (is_array($allowedCountryCodes)) {
            $normalizedAllowedCountryCodes = collect($allowedCountryCodes)
                ->map(fn($countryCode) => strtoupper((string)$countryCode))
                ->filter()
                ->unique();

            $countryCodes = $countryCodes
                ->intersect($normalizedAllowedCountryCodes)
                ->values();
        }

        return $countryCodes->count() === 1
            ? (string)$countryCodes->first()
            : null;
    }

    private function validateShippingDeliveryLocation(array $shipping): ?array
    {
        $countryRestrictionEnabled = (int)getWebConfig(name: 'delivery_country_restriction') === 1;
        $stateRestrictionEnabled = (int)getWebConfig(name: 'delivery_state_restriction') === 1;
        $cityRestrictionEnabled = (int)getWebConfig(name: 'delivery_city_restriction') === 1;
        $areaRestrictionEnabled = (int)getWebConfig(name: 'delivery_area_restriction') === 1;

        // Country: always resolve if provided
        $countryCode = $this->normalizeCountryCodeFromInput($shipping['country'] ?? null);
        if ($countryRestrictionEnabled && !$countryCode) {
            return [
                'field' => 'country',
                'message' => translate('Please_select_a_valid_country'),
            ];
        }

        // State: only resolve and validate if state restriction is enabled
        $stateId = null;
        if ($stateRestrictionEnabled && $countryCode) {
            $stateId = $this->resolveStateId(
                stateIdOrName: $shipping['state_id'] ?? ($shipping['state'] ?? null),
                countryCode: $countryCode
            );
            if (!$stateId) {
                return [
                    'field' => 'state',
                    'message' => translate('Please_select_a_valid_state'),
                ];
            }

            $allowedStateIds = DeliveryState::query()->pluck('state_id')->toArray();
            if (!in_array($stateId, $allowedStateIds, true)) {
                return [
                    'field' => 'state',
                    'message' => translate('Delivery_unavailable_in_this_state'),
                ];
            }
        }

        // City: only resolve and validate if city restriction is enabled
        $cityId = null;
        if ($cityRestrictionEnabled) {
            $cityId = $this->resolveCityId(
                cityIdOrName: $shipping['city_id'] ?? ($shipping['city'] ?? null),
                stateId: $stateId
            );
            if (!$cityId) {
                return [
                    'field' => 'city',
                    'message' => translate('Please_select_a_valid_city'),
                ];
            }

            $allowedCityIds = DeliveryCity::query()->pluck('city_id')->toArray();
            if (!in_array($cityId, $allowedCityIds, true)) {
                return [
                    'field' => 'city',
                    'message' => translate('Delivery_unavailable_in_this_city'),
                ];
            }
        }

        // Area: only resolve and validate if area restriction is enabled
        if ($areaRestrictionEnabled) {
            $areaId = $this->resolveAreaId(
                areaIdOrName: $shipping['area'] ?? null,
                cityId: $cityId
            );
            if (!$areaId) {
                return [
                    'field' => 'area',
                    'message' => translate('Please_select_a_valid_area'),
                ];
            }

            $allowedAreaIds = DeliveryArea::query()->pluck('area_id')->toArray();
            if (!in_array($areaId, $allowedAreaIds, true)) {
                return [
                    'field' => 'area',
                    'message' => translate('Delivery_unavailable_in_this_area'),
                ];
            }
        }

        return null;
    }

    private function normalizeCountryCodeFromInput(?string $countryInput): ?string
    {
        $countryInput = strtoupper(trim((string)$countryInput));
        if ($countryInput === '') {
            return null;
        }

        if (strlen($countryInput) === 2) {
            return $countryInput;
        }

        foreach (COUNTRIES as $country) {
            if (strtoupper((string)($country['name'] ?? '')) === $countryInput) {
                return strtoupper((string)($country['code'] ?? ''));
            }
        }

        return null;
    }

    private function resolveStateId(mixed $stateIdOrName, string $countryCode): ?int
    {
        if (is_numeric($stateIdOrName)) {
            $state = State::query()
                ->where('id', (int)$stateIdOrName)
                ->where('country', $countryCode)
                ->first();

            return $state ? (int)$state->id : null;
        }

        $stateName = trim((string)$stateIdOrName);
        if ($stateName === '') {
            return null;
        }

        $state = State::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($stateName)])
            ->where('country', $countryCode)
            ->first();

        return $state ? (int)$state->id : null;
    }

    private function resolveCityId(mixed $cityIdOrName, int $stateId): ?int
    {
        if (is_numeric($cityIdOrName)) {
            $city = City::query()
                ->where('id', (int)$cityIdOrName)
                ->where('state_id', $stateId)
                ->first();

            return $city ? (int)$city->id : null;
        }

        $cityName = trim((string)$cityIdOrName);
        if ($cityName === '') {
            return null;
        }

        $city = City::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($cityName)])
            ->where('state_id', $stateId)
            ->first();

        return $city ? (int)$city->id : null;
    }

    private function resolveAreaId(mixed $areaIdOrName, int $cityId): ?int
    {
        if (is_numeric($areaIdOrName)) {
            $area = Area::query()
                ->where('id', (int)$areaIdOrName)
                ->where('city_id', $cityId)
                ->first();

            return $area ? (int)$area->id : null;
        }

        $areaName = trim((string)$areaIdOrName);
        if ($areaName === '') {
            return null;
        }

        $area = Area::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($areaName)])
            ->where('city_id', $cityId)
            ->first();

        return $area ? (int)$area->id : null;
    }
}
