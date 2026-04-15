<?php

namespace App\Services;

use App\Enums\SupportTicketRequestType;
use App\Contracts\Repositories\ServiceRequestRepositoryInterface;
use App\Models\Area;
use App\Models\City;
use App\Models\InboxMessage;
use App\Models\ServiceRequest;
use App\Models\State;
use App\Models\SupportTicket as SupportTicketModel;
use App\Models\SupportTicketStatusMaster;
use App\Support\ServiceTicketWorkflow;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceRequestSubmissionService
{
    public function __construct(
        private readonly ServiceRequestRepositoryInterface $serviceRequestRepo,
        private readonly ServiceWorkflowNotificationService $workflowNotifier,
    ) {}

    public function submit(array $validated, Authenticatable $customer, ?string $notificationLink = null): SupportTicketModel
    {
        $ticket = DB::transaction(function () use ($validated, $customer): SupportTicketModel {
            $payload = $this->normalizeLocationPayload($validated);
            $payload['customer_id'] = $customer->id;

            $serviceRequest = $this->serviceRequestRepo->create($payload);
            $serviceRequest->loadMissing(['service', 'customer']);

            $subject = translate('New Service Request For - :service', [
                'service' => $serviceRequest->service?->title ?? translate('Service'),
            ]);
            $details = $this->buildInboxDetails($serviceRequest, $validated);

            $inboxMessage = InboxMessage::create([
                'subject' => $subject,
                'body' => translate('A new service request has been submitted.'),
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

        try {
            $this->workflowNotifier->notify(
                ticket: $ticket,
                eventKey: 'ticket_created',
                title: translate('Service Ticket Created'),
                message: translate('Your service ticket #:ticket has been created.', ['ticket' => $ticket->id]),
                link: $notificationLink,
                recipients: [['type' => 'customer', 'id' => $ticket->customer_id]]
            );
        } catch (\Throwable $exception) {
            Log::warning('Service request notification dispatch failed', [
                'ticket_id' => $ticket->id,
                'customer_id' => $ticket->customer_id,
                'error' => $exception->getMessage(),
            ]);
        }

        return $ticket;
    }

    private function normalizeLocationPayload(array $payload): array
    {
        if (($payload['service_option'] ?? null) !== 'mobile') {
            return $payload;
        }

        $payload['country'] = trim((string)($payload['country'] ?? ''));
        $payload['state'] = trim((string)($payload['state'] ?? ''));
        $payload['city'] = trim((string)($payload['city'] ?? ''));
        $payload['area'] = trim((string)($payload['area'] ?? ''));

        $countryCode = $this->normalizeCountryCodeFromInput($payload['country'] ?? null);
        if (!$countryCode) {
            return $payload;
        }

        $state = $this->firstOrCreateState($payload['state'] ?? null, $countryCode);
        if ($state) {
            $payload['state'] = (string)$state->name;
        }

        $city = $state ? $this->firstOrCreateCity($payload['city'] ?? null, (int)$state->id) : null;
        if ($city) {
            $payload['city'] = (string)$city->name;
        }

        $area = $city ? $this->firstOrCreateArea($payload['area'] ?? null, (int)$city->id) : null;
        if ($area) {
            $payload['area'] = (string)$area->name;
        }

        return $payload;
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

    private function firstOrCreateState(?string $stateName, string $countryCode): ?State
    {
        $stateName = trim((string)$stateName);
        if ($stateName === '') {
            return null;
        }

        return $this->firstOrCreateByNormalizedName(
            modelClass: State::class,
            name: $stateName,
            where: ['country' => $countryCode],
            create: ['country' => $countryCode]
        );
    }

    private function firstOrCreateCity(?string $cityName, int $stateId): ?City
    {
        $cityName = trim((string)$cityName);
        if ($cityName === '') {
            return null;
        }

        return $this->firstOrCreateByNormalizedName(
            modelClass: City::class,
            name: $cityName,
            where: ['state_id' => $stateId],
            create: ['state_id' => $stateId]
        );
    }

    private function firstOrCreateArea(?string $areaName, int $cityId): ?Area
    {
        $areaName = trim((string)$areaName);
        if ($areaName === '') {
            return null;
        }

        return $this->firstOrCreateByNormalizedName(
            modelClass: Area::class,
            name: $areaName,
            where: ['city_id' => $cityId],
            create: ['city_id' => $cityId]
        );
    }

    /**
     * @template TModel of Model
     *
     * @param class-string<TModel> $modelClass
     * @return TModel
     */
    private function firstOrCreateByNormalizedName(string $modelClass, string $name, array $where, array $create): Model
    {
        $query = $modelClass::query();

        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        /** @var TModel|null $existing */
        $existing = $query
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])
            ->first();

        if ($existing) {
            return $existing;
        }

        /** @var TModel $created */
        $created = $modelClass::query()->create(array_merge($create, [
            'name' => $name,
        ]));

        return $created;
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

    private function buildInboxDetails(ServiceRequest $serviceRequest, array $validated): array
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
            'vehicle_make_id' => $validated['vehicle_make_id'] ?? null,
            'vehicle_make' => $serviceRequest->vehicle_make,
            'vehicle_model_id' => $validated['vehicle_model_id'] ?? null,
            'vehicle_model' => $serviceRequest->vehicle_model,
            'vehicle_year_id' => $validated['vehicle_year_id'] ?? null,
            'vehicle_year' => $serviceRequest->vehicle_year,
            'vehicle_mileage' => $serviceRequest->vehicle_mileage,
            'vin' => $serviceRequest->vin,
            'problem_description' => $serviceRequest->problem_description,
            'notes' => $serviceRequest->notes,
        ];
    }

    private function buildTicketDescription(ServiceRequest $serviceRequest): string
    {
        $vehicleDetails = implode(' / ', array_filter([
            $serviceRequest->vehicle_type,
            $serviceRequest->vehicle_make,
            $serviceRequest->vehicle_model,
            $serviceRequest->vehicle_year,
        ]));

        $addressDetails = implode(', ', array_filter([
            $serviceRequest->address,
            $serviceRequest->area,
            $serviceRequest->city,
            $serviceRequest->state,
            $serviceRequest->country,
        ]));

        $coordinates = implode(', ', array_filter([
            $serviceRequest->latitude,
            $serviceRequest->longitude,
        ], static fn ($value) => $value !== null && $value !== ''));

        $serviceOptionLabel = $serviceRequest->service_option === 'mobile'
            ? translate('Mobile')
            : translate('In-shop');

        return implode(PHP_EOL, array_filter([
            translate('Service option') . ': ' . $serviceOptionLabel,
            $vehicleDetails ? translate('Vehicle') . ': ' . $vehicleDetails : null,
            $serviceRequest->vehicle_mileage ? translate('Mileage') . ': ' . $serviceRequest->vehicle_mileage : null,
            $serviceRequest->vin ? translate('VIN') . ': ' . $serviceRequest->vin : null,
            $serviceRequest->problem_description
                ? translate('Problem description') . ': ' . $serviceRequest->problem_description
                : null,
            $serviceRequest->notes ? translate('Notes') . ': ' . $serviceRequest->notes : null,
            $serviceRequest->service_option === 'mobile' && $addressDetails
                ? translate('Address') . ': ' . $addressDetails
                : null,
            $serviceRequest->service_option === 'mobile' && $coordinates
                ? translate('Coordinates') . ': ' . $coordinates
                : null,
        ])) ?: translate('Service request submitted.');
    }
}
