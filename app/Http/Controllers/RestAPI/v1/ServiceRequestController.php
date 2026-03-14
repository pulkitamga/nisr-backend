<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequestFormRequest;
use App\Models\CmsService;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceInvoice;
use App\Models\SupportTicket;
use App\Models\SupportTicketConv;
use App\Models\VehicleMake;
use App\Models\VehicleYear;
use App\Services\ServiceRequestSubmissionService;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ServiceRequestController extends Controller
{
    public function __construct(
        private readonly ServiceRequestSubmissionService $serviceRequestSubmissionService,
    ) {}

    public function referenceData(): JsonResponse
    {
        $makes = VehicleMake::query()
            ->with(['models' => function ($query) {
                $query->select('id', 'make_id', 'name')->orderBy('name');
            }])
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $years = VehicleYear::query()
            ->select('id', 'year')
            ->orderBy('year', 'desc')
            ->get();

        return response()->json([
            'service_options' => [
                ['key' => 'in_shop', 'label' => 'In Shop'],
                ['key' => 'mobile', 'label' => 'Mobile Service'],
            ],
            'vehicle_types' => [
                'Sedan',
                'SUV',
                'Hatchback',
                'Pickup',
                'Van',
                'Truck',
                'Motorcycle',
                'Other',
            ],
            'makes' => $makes,
            'years' => $years,
        ], 200);
    }

    public function catalog(): JsonResponse
    {
        $isEnabled = (bool) getWebConfig(name: 'services');

        if (!$isEnabled) {
            return response()->json([
                'enabled' => false,
                'showcase_cards' => [],
                'services' => [],
            ], 200);
        }

        $showcaseCards = CmsService::query()
            ->with('translations')
            ->whereIn('type', ['request_card_1', 'request_card_2', 'request_card_3'])
            ->where('is_active', 1)
            ->orderByRaw("
                case type
                    when 'request_card_1' then 1
                    when 'request_card_2' then 2
                    when 'request_card_3' then 3
                    else 99
                end
            ")
            ->get()
            ->map(fn (CmsService $card) => $this->formatShowcaseCard($card))
            ->values();

        $services = Product::query()
            ->with(['translations', 'service'])
            ->where('product_type', 'services')
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => $this->formatCatalogProduct($product))
            ->filter()
            ->values();

        return response()->json([
            'enabled' => true,
            'showcase_cards' => $showcaseCards,
            'services' => $services,
        ], 200);
    }

    public function create(ServiceRequestFormRequest $request): JsonResponse
    {
        try {
            $ticket = $this->serviceRequestSubmissionService->submit(
                validated: $request->validated(),
                customer: $request->user()
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'errors' => [
                    [
                        'code' => 'service_request_create_failed',
                        'message' => 'Unable to create service request.',
                    ],
                ],
            ], 422);
        }

        return response()->json([
            'message' => 'Service request created successfully.',
            'ticket_id' => $ticket->id,
            'ticket' => $this->formatSummary($ticket),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $tickets = $this->customerTicketQuery($request->user()->id)
            ->with([
                'status_details',
                'relatedInboxMessage',
                'service',
                'latestServiceJob.technician',
                'invoices' => function ($query) {
                    $query->latest('id');
                },
            ])
            ->latest('id')
            ->get();

        return response()->json($tickets->map(fn (SupportTicket $ticket) => $this->formatSummary($ticket))->values(), 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = $this->customerTicketQuery($request->user()->id)
            ->with([
                'status_details',
                'relatedInboxMessage',
                'service',
                'conversations',
                'latestServiceJob.technician',
                'latestServiceJob.activities.createdBy',
                'invoices' => function ($query) {
                    $query->latest('id');
                },
            ])
            ->where('id', $id)
            ->first();

        if (!$ticket) {
            return response()->json(['message' => 'Service request not found.'], 404);
        }

        return response()->json($this->formatDetail($ticket), 200);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $ticket = $this->customerTicketQuery($request->user()->id)
            ->with(['status_details'])
            ->where('id', $id)
            ->first();

        if (!$ticket) {
            return response()->json(['message' => 'Service request not found.'], 404);
        }

        if ($this->statusKey($ticket) === 'closed') {
            return response()->json(['message' => 'Closed service requests cannot be updated.'], 422);
        }

        $reply = SupportTicketConv::query()->create([
            'support_ticket_id' => $ticket->id,
            'admin_id' => 0,
            'customer_message' => trim((string) $request->input('message')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ticket->update(['updated_at' => now()]);

        return response()->json([
            'message' => 'Reply sent successfully.',
            'reply' => $this->formatMessage($reply),
        ], 200);
    }

    private function customerTicketQuery(int $customerId)
    {
        return SupportTicket::query()
            ->where('customer_id', $customerId)
            ->where('type', 'service')
            ->where('request_type', 'service_request');
    }

    private function formatSummary(SupportTicket $ticket): array
    {
        $details = (array) ($ticket->relatedInboxMessage?->details ?? []);
        $latestInvoice = $ticket->invoices->first();

        return [
            'id' => (int) $ticket->id,
            'ticket_number' => (string) $ticket->id,
            'subject' => $ticket->subject,
            'status_id' => (int) $ticket->status,
            'status_key' => $this->statusKey($ticket),
            'status_label' => $ticket->status_details?->name ?? 'Unknown',
            'service_option' => $details['service_option'] ?? null,
            'service_option_label' => $this->serviceOptionLabel($details['service_option'] ?? null),
            'service' => $this->formatService($ticket),
            'vehicle' => $this->formatVehicle($details),
            'location' => $this->formatLocation($details),
            'created_at' => $ticket->created_at?->toIso8601String(),
            'updated_at' => $ticket->updated_at?->toIso8601String(),
            'latest_invoice' => $this->formatInvoice($latestInvoice),
            'latest_job' => $this->formatLatestJob($ticket),
            'can_reply' => $this->statusKey($ticket) !== 'closed',
        ];
    }

    private function formatDetail(SupportTicket $ticket): array
    {
        $details = (array) ($ticket->relatedInboxMessage?->details ?? []);
        $messages = [[
            'id' => 0,
            'admin_id' => null,
            'customer_message' => $ticket->description,
            'admin_message' => null,
            'created_at' => $ticket->created_at?->toIso8601String(),
            'updated_at' => $ticket->updated_at?->toIso8601String(),
            'attachment' => $ticket->attachment ?? [],
            'attachment_full_url' => $ticket->attachment_full_url ?? [],
        ]];

        foreach ($ticket->conversations as $conversation) {
            $messages[] = $this->formatMessage($conversation);
        }

        $summary = $this->formatSummary($ticket);
        $summary['messages'] = $messages;
        $summary['activities'] = $ticket->latestServiceJob?->activities
            ? $ticket->latestServiceJob->activities
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($activity) {
                    return [
                        'id' => (int) $activity->id,
                        'activity_type' => $activity->activity_type,
                        'description' => $activity->description,
                        'created_by' => $activity->createdBy?->name,
                        'created_at' => $activity->created_at?->toIso8601String(),
                    ];
                })
                ->toArray()
            : [];
        $summary['invoices'] = $ticket->invoices->map(fn (ServiceInvoice $invoice) => $this->formatInvoice($invoice))->values()->toArray();
        $summary['service_request'] = [
            'service_request_id' => $details['service_request_id'] ?? null,
            'service_id' => $details['service_id'] ?? null,
            'service_option' => $details['service_option'] ?? null,
            'service_option_label' => $this->serviceOptionLabel($details['service_option'] ?? null),
            'vehicle' => $this->formatVehicle($details),
            'location' => $this->formatLocation($details),
        ];

        return $summary;
    }

    private function formatService(SupportTicket $ticket): ?array
    {
        $service = $ticket->service;

        if (!$service) {
            $serviceId = data_get($ticket->relatedInboxMessage?->details, 'service_id');
            if ($serviceId) {
                $service = Service::query()->find($serviceId);
            }
        }

        if (!$service) {
            return null;
        }

        return [
            'id' => (int) $service->id,
            'service_id' => $service->service_id,
            'title' => $service->title,
            'base_price_inshop' => $service->base_price_inshop,
            'base_price_mobile' => $service->base_price_mobile,
            'included_km_mobile' => $service->included_km_mobile,
            'travel_fee_per_km' => $service->travel_fee_per_km,
            'parts_included' => $service->parts_included,
            'call_center_flag' => (bool) $service->call_center_flag,
        ];
    }

    private function formatVehicle(array $details): array
    {
        return [
            'type' => $details['vehicle_type'] ?? null,
            'make' => $details['vehicle_make'] ?? null,
            'model' => $details['vehicle_model'] ?? null,
            'year' => $details['vehicle_year'] ?? null,
            'mileage' => $details['vehicle_mileage'] ?? null,
            'vin' => $details['vin'] ?? null,
        ];
    }

    private function formatLocation(array $details): ?array
    {
        if (($details['service_option'] ?? null) !== 'mobile') {
            return null;
        }

        return [
            'country' => $details['country'] ?? null,
            'state' => $details['state'] ?? null,
            'city' => $details['city'] ?? null,
            'area' => $details['area'] ?? null,
            'address' => $details['address'] ?? null,
            'latitude' => $details['latitude'] ?? null,
            'longitude' => $details['longitude'] ?? null,
        ];
    }

    private function formatInvoice(?ServiceInvoice $invoice): ?array
    {
        if (!$invoice) {
            return null;
        }

        return [
            'id' => (int) $invoice->id,
            'subtotal' => $invoice->subtotal,
            'tax' => $invoice->tax,
            'total' => $invoice->total,
            'payment_status' => $invoice->payment_status,
            'payment_link' => $invoice->payment_link,
            'payment_link_expires_at' => $invoice->payment_link_expires_at?->toIso8601String(),
            'generated_at' => $invoice->generated_at?->toIso8601String(),
            'paid_at' => $invoice->paid_at?->toIso8601String(),
        ];
    }

    private function formatCatalogProduct(Product $product): ?array
    {
        if (!$product->service) {
            return null;
        }

        return [
            'product_id' => (int) $product->id,
            'slug' => $product->slug,
            'name' => getTranslatedValue($product, 'name', $product->name ?? ''),
            'description' => Str::of(strip_tags((string) $product->details))
                ->squish()
                ->limit(160)
                ->value(),
            'thumbnail_full_url' => $product->thumbnail_full_url,
            'service' => [
                'id' => (int) $product->service->id,
                'service_id' => $product->service->service_id,
                'title' => $product->service->title ?: $product->name,
                'base_price_inshop' => $product->service->base_price_inshop,
                'base_price_mobile' => $product->service->base_price_mobile,
                'parts_cost' => $product->service->parts_cost,
                'included_km_mobile' => $product->service->included_km_mobile,
                'travel_fee_per_km' => $product->service->travel_fee_per_km,
                'labor_hours' => $product->service->labor_hours,
                'parts_included' => $product->service->parts_included,
                'call_center_flag' => (bool) $product->service->call_center_flag,
            ],
        ];
    }

    private function formatShowcaseCard(CmsService $card): array
    {
        return [
            'id' => (int) $card->id,
            'type' => $card->type,
            'heading' => getTranslatedValue($card, 'heading', $card->heading ?? ''),
            'description' => getTranslatedValue($card, 'description', $card->description ?? ''),
            'image_url' => $card->image ? url(Storage::url($card->image)) : null,
            'button_link' => $card->button_link,
        ];
    }

    private function formatLatestJob(SupportTicket $ticket): ?array
    {
        $job = $ticket->latestServiceJob;
        if (!$job) {
            return null;
        }

        return [
            'id' => (int) $job->id,
            'status' => $job->status,
            'service_mode' => $job->service_mode,
            'scheduled_at' => $job->scheduled_at?->toIso8601String(),
            'started_at' => $job->started_at?->toIso8601String(),
            'completed_at' => $job->completed_at?->toIso8601String(),
            'description' => $job->description,
            'remarks' => $job->remarks,
            'technician' => $job->technician ? [
                'id' => (int) $job->technician->id,
                'name' => $job->technician->name,
                'phone' => $job->technician->phone,
            ] : null,
        ];
    }

    private function formatMessage(SupportTicketConv $reply): array
    {
        return [
            'id' => (int) $reply->id,
            'admin_id' => $reply->admin_id,
            'customer_message' => $reply->customer_message,
            'admin_message' => $reply->admin_message,
            'created_at' => $reply->created_at?->toIso8601String(),
            'updated_at' => $reply->updated_at?->toIso8601String(),
            'attachment' => $reply->attachment ?? [],
            'attachment_full_url' => $reply->attachment_full_url ?? [],
        ];
    }

    private function statusKey(SupportTicket $ticket): string
    {
        return Str::of((string) ($ticket->status_details?->name ?? 'unknown'))
            ->trim()
            ->lower()
            ->replace(' ', '_')
            ->value();
    }

    private function serviceOptionLabel(?string $option): ?string
    {
        return match ($option) {
            'in_shop' => 'In Shop',
            'mobile' => 'Mobile Service',
            default => null,
        };
    }
}
