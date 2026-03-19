<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactPageModel;
use App\Models\GuestUser;
use App\Models\HelpTopic;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralController extends Controller
{
    public function contacts(): JsonResponse
    {
        $contact = ContactPageModel::query()
            ->where('is_active', 1)
            ->latest('id')
            ->first();

        if (!$contact) {
            $contact = ContactPageModel::query()->latest('id')->first();
        }

        if (!$contact) {
            return response()->json([
                'success' => true,
                'data' => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'phone' => $contact->phone,
                'email' => $contact->email,
                'address' => $contact->location,
                'latitude' => null,
                'longitude' => null,
                'title' => null,
                'description' => null,
                'image' => null,
                'title_ar' => null,
                'title_en' => null,
                'description_ar' => null,
                'description_en' => null,
                'address_ar' => null,
                'address_en' => null,
            ],
        ], 200);
    }

    public function faq(): JsonResponse
    {
        return response()->json(HelpTopic::orderBy('ranking')->get(), 200);
    }

    public function get_guest_id(Request $request): JsonResponse
    {
        $guestId = GuestUser::create([
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
        return response()->json(['guest_id' => $guestId?->id], 200);
    }

    public function contact_store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required',
            'subject' => 'required',
            'message' => 'required',
            'email' => 'required',
            'name' => 'required',
        ], [
            'name.required' => 'Name is Empty!',
            'mobile_number.required' => 'Mobile Number is Empty!',
            'subject.required' => ' Subject is Empty!',
            'message.required' => 'Message is Empty!',
            'email.required' => 'Email is Empty!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        Contact::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'mobile_number' => $request['mobile_number'],
            'subject' => $request['subject'],
            'message' => $request['message']
        ]);

        return response()->json(['message' => 'your_message_send_successfully'], 200);
    }
}
