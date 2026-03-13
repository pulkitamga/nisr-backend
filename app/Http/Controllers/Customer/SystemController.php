<?php

namespace App\Http\Controllers\Customer;

use App\Models\Area;
use App\Models\City;
use App\Models\DeliveryArea;
use App\Models\DeliveryCity;
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
            return response()->json([
                'errors' => translate('Please_fill_all_required_shipping_and_billing_information')
            ], 403);
        }

        $shipping = [];
        $billing = [];

        try {
            parse_str($request['shipping'], $shipping);
            parse_str($request['billing'], $billing);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => translate('Invalid_shipping_or_billing_data_format')
            ], 403);
        }

        $defaultShippingValues = [
            'delivery_type' => null,
            'nearest_branch' => null,
            'pickup_branch_id' => null,
            'contact_person_name' => null,
            'address_type' => null,
            'address' => null,
            'city' => null,
            'state' => null,
            'area' => null,
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
            'billing_state' => null,
            'billing_area' => null,
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

        $isFormEmpty = true;
        foreach ($shipping as $key => $value) {
            if (!empty($value) && !in_array($key, ['delivery_type', 'nearest_branch', 'pickup_branch_id', 'latitude', 'longitude', 'zip'])) {
                $isFormEmpty = false;
                break;
            }
        }

        if ($isFormEmpty) {
            return response()->json([
                'errors' => translate('Please_fill_all_required_fields_in_shipping_address')
            ], 403);
        }

        session()->put('nearest_branch', $shipping['nearest_branch']);
        session()->put('delivery_type', $shipping['delivery_type']);
        session()->put('pickup_branch_id', $shipping['pickup_branch_id']);

        // Step 6: Phone validation (existing code)
        if (isset($shipping['phone']) && !empty($shipping['phone'])) {
            $shippingPhoneValue = preg_replace('/[^0-9]/', '', $shipping['phone']);
            $shippingPhoneLength = strlen($shippingPhoneValue);
            if ($shippingPhoneLength < 4) {
                return response()->json([
                    'errors' => translate('The_phone_number_must_be_at_least_4_characters')
                ], 403);
            }
            if ($shippingPhoneLength > 20) {
                return response()->json([
                    'errors' => translate('The_phone_number_may_not_be_greater_than_20_characters')
                ], 403);
            }
        }

        if ($request['billing_addresss_same_shipping'] == 'false' && isset($billing['billing_phone']) && !empty($billing['billing_phone'])) {
            $billingPhoneValue = preg_replace('/[^0-9]/', '', $billing['billing_phone']);
            $billingPhoneLength = strlen($billingPhoneValue);
            if ($billingPhoneLength < 4) {
                return response()->json([
                    'errors' => translate('The_phone_number_must_be_at_least_4_characters')
                ], 403);
            }

            if ($billingPhoneLength > 20) {
                return response()->json([
                    'errors' => translate('The_phone_number_may_not_be_greater_than_20_characters')
                ], 403);
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
        if ($shipping['delivery_type'] != 'pickup' && isset($shipping['shipping_method_id'])) {
            $requiredFields = ['contact_person_name', 'address_type', 'address', 'city', 'state', 'area', 'country', 'phone'];
            if ($isGuestCustomer) {
                $requiredFields[] = 'email';
            }

            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (empty($shipping[$field])) {
                    $missingFields[] = str_replace('_', ' ', $field);
                }
            }

            if (!empty($missingFields)) {
                return response()->json([
                    'errors' => translate('Please_fill_the_following_fields') . ': ' . implode(', ', $missingFields)
                ], 403);
            }

            if ($countryRestrictStatus && !self::delivery_country_exist_check($shipping['country'])) {
                return response()->json([
                    'errors' => translate('Delivery_unavailable_in_this_country.')
                ], 403);
            }
        } else if ($shipping['delivery_type'] != 'pickup') {
            $requiredFields = ['contact_person_name', 'address_type', 'address', 'city', 'state', 'country', 'phone'];
            if ($isGuestCustomer) {
                $requiredFields[] = 'email';
            }

            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (empty($shipping[$field])) {
                    $missingFields[] = str_replace('_', ' ', $field);
                }
            }

            if (!empty($missingFields)) {
                return response()->json([
                    'errors' => translate('Please_fill_the_following_fields') . ': ' . implode(', ', $missingFields)
                ], 403);
            }
        }

        if ($shipping['delivery_type'] != 'pickup') {
            $shippingRestrictionError = $this->validateShippingDeliveryLocation($shipping);
            if ($shippingRestrictionError) {
                return response()->json([
                    'errors' => $shippingRestrictionError
                ], 403);
            }

            if ((int)$zipRestrictStatus === 1) {
                if (empty($shipping['zip'])) {
                    return response()->json([
                        'errors' => translate('Please_fill_the_following_fields') . ': ' . translate('zip_code')
                    ], 403);
                }

                if (!self::delivery_zipcode_exist_check($shipping['zip'])) {
                    return response()->json([
                        'errors' => translate('Delivery_unavailable_for_this_zip_code_area')
                    ], 403);
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
            $requiredBillingFields = ['billing_contact_person_name', 'billing_address_type', 'billing_address', 'billing_city', 'billing_state', 'billing_country', 'billing_phone'];
            if ($isGuestCustomer) {
                $requiredBillingFields[] = 'billing_contact_email';
            }

            $missingBillingFields = [];
            foreach ($requiredBillingFields as $field) {
                if (empty($billing[$field])) {
                    $missingBillingFields[] = str_replace('billing_', '', str_replace('_', ' ', $field));
                }
            }

            if (!empty($missingBillingFields)) {
                return response()->json([
                    'errors' => translate('Please_fill_the_following_billing_fields') . ': ' . implode(', ', $missingBillingFields)
                ], 403);
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
            return response()->json([
                'errors' => translate('Fill_all_required_fields_of_billing_address')
            ], 403);
        } elseif ($request['billing_addresss_same_shipping'] == 'true' && !isset($billing['billing_method_id']) && $physicalProduct != 'yes') {
            return response()->json([
                'errors' => translate('Fill_all_required_fields_of_billing_address')
            ], 403);
        }

        if (empty($addressId) && empty($billingAddressId)) {
            return response()->json([
                'errors' => translate('Please_provide_at_least_one_shipping_or_billing_address')
            ], 403);
        }

        session()->put('address_id', $addressId);
        session()->put('billing_address_id', $billingAddressId);

        if ($request['is_check_create_account'] && $isGuestCustomer) {
            if (empty($request['customer_password']) || empty($request['customer_confirm_password'])) {
                return response()->json([
                    'errors' => translate('The_password_or_confirm_password_can_not_be_empty')
                ], 403);
            }
            if ($request['customer_password'] != $request['customer_confirm_password']) {
                return response()->json([
                    'errors' => translate('The_password_and_confirm_password_must_match')
                ], 403);
            }
            if (strlen($request['customer_password']) < 7 || strlen($request['customer_confirm_password']) < 7) {
                return response()->json([
                    'errors' => translate('The_password_must_be_at_least_8_characters')
                ], 403);
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
                return response()->json(['errors' => translate('Already_registered')], 403);
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

    private function validateShippingDeliveryLocation(array $shipping): ?string
    {
        $countryCode = $this->normalizeCountryCodeFromInput($shipping['country'] ?? null);
        if (!$countryCode) {
            return translate('Please_select_a_valid_country');
        }

        $stateId = $this->resolveStateId(
            stateIdOrName: $shipping['state_id'] ?? ($shipping['state'] ?? null),
            countryCode: $countryCode
        );
        if (!$stateId) {
            return translate('Please_select_a_valid_state');
        }

        $cityId = $this->resolveCityId(
            cityIdOrName: $shipping['city_id'] ?? ($shipping['city'] ?? null),
            stateId: $stateId
        );
        if (!$cityId) {
            return translate('Please_select_a_valid_city');
        }

        $areaId = $this->resolveAreaId(
            areaIdOrName: $shipping['area'] ?? null,
            cityId: $cityId
        );

        $stateRestrictionEnabled = (int)getWebConfig(name: 'delivery_state_restriction') === 1;
        if ($stateRestrictionEnabled) {
            $allowedStateIds = DeliveryState::query()->pluck('state_id')->toArray();
            if (!in_array($stateId, $allowedStateIds, true)) {
                return translate('Delivery_unavailable_in_this_state');
            }
        }

        $cityRestrictionEnabled = (int)getWebConfig(name: 'delivery_city_restriction') === 1;
        if ($cityRestrictionEnabled) {
            $allowedCityIds = DeliveryCity::query()->pluck('city_id')->toArray();
            if (!in_array($cityId, $allowedCityIds, true)) {
                return translate('Delivery_unavailable_in_this_city');
            }
        }

        $areaRestrictionEnabled = (int)getWebConfig(name: 'delivery_area_restriction') === 1;
        if ($areaRestrictionEnabled) {
            if (!$areaId) {
                return translate('Please_select_a_valid_area');
            }

            $allowedAreaIds = DeliveryArea::query()->pluck('area_id')->toArray();
            if (!in_array($areaId, $allowedAreaIds, true)) {
                return translate('Delivery_unavailable_in_this_area');
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
