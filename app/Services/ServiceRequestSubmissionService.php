<?php

namespace App\Services;

use App\Enums\SupportTicketRequestType;
use App\Contracts\Repositories\ServiceRequestRepositoryInterface;
use App\Models\InboxMessage;
use App\Models\ServiceRequest;
use App\Models\SupportTicket as SupportTicketModel;
use App\Models\SupportTicketStatusMaster;
use App\Models\User;
use App\Support\ServiceTicketWorkflow;
use Illuminate\Support\Facades\DB;

class ServiceRequestSubmissionService
{
    public function __construct(
        private readonly ServiceRequestRepositoryInterface $serviceRequestRepo,
        private readonly ServiceWorkflowNotificationService $workflowNotifier,
    ) {}

    public function submit(array $validated, User $customer, ?string $notificationLink = null): SupportTicketModel
    {
        $ticket = DB::transaction(function () use ($validated, $customer): SupportTicketModel {
            $payload = $validated;
            $payload['customer_id'] = $customer->id;

            $serviceRequest = $this->serviceRequestRepo->create($payload);
            $serviceRequest->loadMissing(['service', 'customer']);

            $subject = 'New Service Request For - ' . ($serviceRequest->service?->title ?? 'Service');
            $details = $this->buildInboxDetails($serviceRequest);

            $inboxMessage = InboxMessage::create([
                'subject' => $subject,
                'body' => 'A new service request has been submitted.',
                'contact_id' => $serviceRequest->customer_id,
                'sender_name' => trim(($serviceRequest->customer->f_name ?? '') . ' ' . ($serviceRequest->customer->l_name ?? ''))
                    ?: ($serviceRequest->customer->name ?? 'N/A'),
                'sender_email' => $serviceRequest->customer->email ?? null,
                'sender_phone' => $serviceRequest->customer->phone ?? null,
                'pipeline' => 'form',
                'message_type' => 'service',
                'status' => 'new',
                'details' => $details,
            ]);

            $ticket = SupportTicketModel::create([
                'service_id' => $serviceRequest->service_id,
                'source_id' => $inboxMessage->id,
                'customer_id' => $serviceRequest->customer_id,
                'subject' => $subject,
                'type' => 'service',
                'sub_type' => 'service',
                'request_type' => SupportTicketRequestType::Service->value,
                'priority' => 'medium',
                'description' => $this->buildTicketDescription($serviceRequest),
                'status' => $this->resolveDefaultStatusId(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $inboxMessage->update([
                'related_ticket_id' => $ticket->id,
                'convert_type' => 'ticket',
                'convert_sub_type' => 'service',
                'status' => 'converted',
            ]);

            return $ticket->loadMissing(['service', 'status_details', 'relatedInboxMessage']);
        });

        $this->workflowNotifier->notify(
            ticket: $ticket,
            eventKey: 'ticket_created',
            title: 'Service Ticket Created',
            message: "Your service ticket #{$ticket->id} has been created.",
            link: $notificationLink,
            recipients: [['type' => 'customer', 'id' => $ticket->customer_id]]
        );

        return $ticket;
    }

    private function resolveDefaultStatusId(): int
    {
        return (int) (
            SupportTicketStatusMaster::query()
                ->where('master_id', ServiceTicketWorkflow::STATUS_MASTER_ID)
                ->whereRaw('LOWER(name) = ?', ['new'])
                ->where('status', 'active')
                ->orderBy('position', 'asc')
                ->value('id')
            ?? ServiceTicketWorkflow::STATUS_NEW
        );
    }

    private function buildInboxDetails(ServiceRequest $serviceRequest): array
    {
        return [
            'service_request_id' => $serviceRequest->id,
            'service_id' => $serviceRequest->service_id,
            'service_option' => $serviceRequest->service_option,
            'country' => $serviceRequest->country,
            'state' => $serviceRequest->state,
            'city' => $serviceRequest->city,
            'area' => $serviceRequest->area,
            'address' => $serviceRequest->address,
            'latitude' => $serviceRequest->latitude,
            'longitude' => $serviceRequest->longitude,
            'vehicle_type' => $serviceRequest->vehicle_type,
            'vehicle_make' => $serviceRequest->vehicle_make,
            'vehicle_model' => $serviceRequest->vehicle_model,
            'vehicle_year' => $serviceRequest->vehicle_year,
            'vehicle_mileage' => $serviceRequest->vehicle_mileage,
            'vin' => $serviceRequest->vin,
        ];
    }

    private function buildTicketDescription(ServiceRequest $serviceRequest): string
    {
        return implode(PHP_EOL, array_filter([
            "Service option: {$serviceRequest->service_option}",
            "Vehicle: {$serviceRequest->vehicle_type} / {$serviceRequest->vehicle_make} / {$serviceRequest->vehicle_model} / {$serviceRequest->vehicle_year}",
            "Mileage: {$serviceRequest->vehicle_mileage}",
            $serviceRequest->vin ? "VIN: {$serviceRequest->vin}" : null,
            $serviceRequest->service_option === 'mobile'
                ? "Address: {$serviceRequest->address}, {$serviceRequest->area}, {$serviceRequest->city}, {$serviceRequest->state}, {$serviceRequest->country}"
                : null,
            $serviceRequest->service_option === 'mobile'
                ? "Coordinates: {$serviceRequest->latitude}, {$serviceRequest->longitude}"
                : null,
        ])) ?: 'Service request submitted.';
    }
}
