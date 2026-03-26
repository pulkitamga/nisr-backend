<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Contact;
use App\Models\GuestUser;
use App\Models\HelpTopic;
use App\Models\InboxActivities;
use App\Models\InboxMessage;
use App\Services\TicketConvert;
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
                'branches' => $branches->map(fn(Branch $branch): array => [
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
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // 5MB max
        ], [
            'name.required' => 'Name is Empty!',
            'mobile_number.required' => 'Mobile Number is Empty!',
            'subject.required' => ' Subject is Empty!',
            'message.required' => 'Message is Empty!',
            'email.required' => 'Email is Empty!',
            'attachment.file' => 'The attachment must be a file.',
            'attachment.mimes' => 'The attachment must be a file of type: jpg, jpeg, png, pdf, doc, docx.',
            'attachment.max' => 'The attachment size must not exceed 5MB.',
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

        // Handle file upload - store as array format for attachment column
        $attachmentArray = [];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Store file in storage/app/public/support-ticket
            $filePath = $file->storeAs('support-ticket', $fileName, 'public');

            // Format attachment as array as expected by the model
            $attachmentArray = [
                [
                    'file_name' => $fileName,
                    'storage' => 'public',
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $filePath,
                ]
            ];
        }

        [$contact, $inboxMessage, $ticket] = DB::transaction(function () use ($request, $customer, $name, $phone, $category, $messageType, $attachmentArray) {
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
                    'has_attachment' => !empty($attachmentArray),
                    'attachment_count' => count($attachmentArray),
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
                    'has_attachment' => !empty($attachmentArray),
                ],
            ]);

            $ticket = TicketConvert::fromInboxMessage(
                message: $inboxMessage,
                subType: $category,
                reason: null,
                departmentId: null,
                priority: 'medium'
            );

            // Update the ticket with attachment if it exists
            if (!empty($attachmentArray) && $ticket) {
                $ticket->update([
                    'attachment' => $attachmentArray, // This will be cast to JSON automatically
                ]);
            }

            $inboxMessage->update([
                'related_ticket_id' => $ticket->id,
                'convert_type' => 'ticket',
                'convert_sub_type' => $category,
                'status' => 'converted',
            ]);

            return [$contact, $inboxMessage, $ticket];
        });

        // Get the attachment URLs using the model's accessor
        $attachmentUrls = $ticket->attachment_full_url ?? [];

        return response()->json([
            'message' => 'your_message_send_successfully',
            'case' => [
                'id' => (string)$inboxMessage->id,
                'reference' => 'CASE-' . $inboxMessage->id,
                'category' => $category,
                'subject' => $request['subject'],
                'status' => 'converted',
                'priority' => 'medium',
                'created_at' => optional($inboxMessage->created_at)?->toIso8601String(),
                'updated_at' => optional($inboxMessage->updated_at)?->toIso8601String(),
                'is_converted' => true,
                'ticket_id' => (string)$ticket->id,
                'last_update' => optional($inboxMessage->updated_at)?->toDateTimeString(),
                'next_step' => 'Your case has been converted to a support ticket.',
                'attachments' => $attachmentUrls, // Array of attachment URLs
                'has_attachments' => !empty($attachmentUrls),
            ],
        ], 200);
    }
}
