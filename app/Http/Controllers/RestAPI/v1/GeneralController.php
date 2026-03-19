<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Contact;
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
        $branches = Branch::query()
            ->where('id', '!=', 1)
            ->where('status', 'active')
            ->where('branch_name', '!=', 'System')
            ->where(function ($query): void {
                $query->whereNotNull('phone')
                    ->orWhereNotNull('email')
                    ->orWhereNotNull('branch_address');
            })
            ->orderBy('id')
            ->get();

        $phone = $this->preferredValue(getWebConfig('company_phone'));
        $email = $this->preferredValue(getWebConfig('company_email'));
        $address = $this->preferredValue(getWebConfig('shop_address'));

        if (!$phone && !$email && !$address && $branches->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
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
                'branches' => $branches->map(fn (Branch $branch): array => [
                    'id' => (int)$branch->id,
                    'branch_name' => $this->preferredValue($branch->branch_name) ?? '',
                    'address' => $this->preferredValue($branch->branch_address),
                    'phone' => $this->preferredValue($branch->phone),
                    'email' => $this->preferredValue($branch->email),
                    'latitude' => $branch->branch_latitude !== null ? (float)$branch->branch_latitude : null,
                    'longitude' => $branch->branch_longitude !== null ? (float)$branch->branch_longitude : null,
                ])->values()->all(),
            ],
        ], 200);
    }

    private function preferredValue(mixed ...$values): mixed
    {
        foreach ($values as $value) {
            $normalizedValue = is_string($value) ? trim($value) : $value;
            if (!empty($normalizedValue)) {
                return $normalizedValue;
            }
        }

        return null;
    }

    public function faq(): JsonResponse
    {
        return response()->json(
            HelpTopic::withoutGlobalScope('translate')
                ->with('translations')
                ->orderBy('ranking')
                ->get(),
            200
        );
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
