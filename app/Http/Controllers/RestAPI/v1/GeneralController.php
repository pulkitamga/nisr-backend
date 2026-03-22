<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Contact;
use App\Models\GuestUser;
use App\Models\HelpTopic;
use App\Models\InboxActivities;
use App\Models\InboxMessage;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'mobile_number' => 'required_without:phone',
            'phone' => 'required_without:mobile_number',
            'subject' => 'required',
            'message' => 'required',
            'email' => 'required',
            'name' => 'required_without:full_name',
            'full_name' => 'required_without:name',
            'category' => 'nullable|string|in:support,complaint,career,service,retail,wholesale',
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

        $customer = auth('api')->user();
        $name = trim((string)($request->input('full_name') ?: $request->input('name')));
        $phone = trim((string)($request->input('phone') ?: $request->input('mobile_number')));
        $category = (string)$request->input('category', 'support');
        $messageType = match ($category) {
            'service' => 'service',
            'career' => 'career',
            'retail', 'wholesale' => 'contact',
            default => 'support',
        };

        [$contact, $inboxMessage] = DB::transaction(function () use ($request, $customer, $name, $phone, $category, $messageType) {
            $contact = Contact::create([
                'name' => $name,
                'email' => $request['email'],
                'mobile_number' => $phone,
                'subject' => $request['subject'],
                'message' => $request['message'],
            ]);

            $inboxMessage = InboxMessage::create([
                'subject' => $request['subject'],
                'body' => $request['message'],
                'contact_id' => $customer?->id,
                'sender_name' => $name,
                'sender_email' => $request['email'],
                'sender_phone' => $phone,
                'pipeline' => 'form',
                'message_type' => $messageType,
                'status' => 'new',
                'priority' => 'medium',
                'details' => [
                    'category' => $category,
                    'subject' => $request['subject'],
                    'message' => $request['message'],
                    'contact_id' => $contact->id,
                ],
            ]);

            InboxActivities::create([
                'message_id' => $inboxMessage->id,
                'activity_type' => 'submission',
                'title' => 'Inquiry submitted',
                'subject' => 'Submitted from mobile contact form',
                'note_date' => now(),
                'employee_id' => null,
                'details' => [
                    'channel' => 'mobile',
                    'pipeline' => 'form',
                    'message_type' => $messageType,
                    'category' => $category,
                ],
            ]);

            return [$contact, $inboxMessage];
        });

        return response()->json([
            'message' => 'your_message_send_successfully',
            'case' => [
                'id' => (string)$inboxMessage->id,
                'reference' => 'CASE-' . $inboxMessage->id,
                'category' => $category,
                'subject' => $request['subject'],
                'status' => 'new',
                'priority' => 'medium',
                'created_at' => optional($inboxMessage->created_at)?->toIso8601String(),
                'updated_at' => optional($inboxMessage->updated_at)?->toIso8601String(),
                'is_converted' => false,
                'ticket_id' => null,
                'last_update' => optional($inboxMessage->updated_at)?->toDateTimeString(),
                'next_step' => 'Your case is waiting for CRM triage.',
            ],
        ], 200);
    }
}
