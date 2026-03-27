<?php

namespace App\Services;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Models\Blacklist;
use App\Models\OrderDetail;
use App\Models\Warranty;
use App\Models\WarrantyTimelineEvent;
use App\Support\WarrantyOrderSupport;

class WarrantyOrderActivationService
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    ) {}

    public function activate(
        OrderDetail $detail,
        iterable $serialNumbers,
        int $customerId,
        string $ip,
        array $options = [],
    ): array {
        $defaultDuration = (int) ($this->businessSettingRepo->getFirstWhere(['type' => 'warranty_months'])['value'] ?? 12);
        $purchaseDate = WarrantyOrderSupport::resolvePurchaseDate($detail->order, $detail);
        $start = $purchaseDate->copy();
        $end = $purchaseDate->copy()->addMonths($defaultDuration);
        $activatedCount = (int) ($options['activated_count'] ?? 0);
        $timelineDescription = (string) ($options['timeline_description'] ?? 'Activated via order details');
        $policyVersion = $options['policy_version'] ?? null;
        $timelineUserId = $options['user_id'] ?? null;

        $activatedSerials = [];
        $failedSerials = [];
        $activatedWarrantyIds = [];

        foreach ($serialNumbers as $serialNumber) {
            $normalizedSerial = trim((string) $serialNumber);

            if ($normalizedSerial === '') {
                continue;
            }

            $warranty = Warranty::query()
                ->eligibleForOrderActivation($normalizedSerial, (int) $detail->product_id)
                ->first();

            if (!$warranty) {
                $failedSerials[] = $normalizedSerial;
                continue;
            }

            if (Warranty::query()
                ->where('serial_number', $normalizedSerial)
                ->where('status', 'active')
                ->where('end_date', '>', now())
                ->exists()
            ) {
                $failedSerials[] = $normalizedSerial;
                continue;
            }

            if (Blacklist::query()->where('serial_number', $normalizedSerial)->exists()) {
                $failedSerials[] = $normalizedSerial;
                continue;
            }

            $warranty->update([
                'product_id' => $warranty->product_id ?? $detail->product_id,
                'status' => 'active',
                'activation_date' => $purchaseDate,
                'start_date' => $start,
                'end_date' => $end,
                'purchase_date' => $purchaseDate,
                'invoice_number' => $detail->order_id,
                'final_user_id' => $customerId,
                'activation_method' => 'order_activation',
                'consent_checked' => true,
                'consent_timestamp' => now(),
                'consent_ip' => $ip,
                'policy_version' => $policyVersion,
            ]);

            WarrantyTimelineEvent::create([
                'warranty_id' => $warranty->id,
                'event_type' => 'activated',
                'description' => $timelineDescription,
                'timestamp' => now(),
                'user_id' => $timelineUserId,
            ]);

            $activatedSerials[] = $normalizedSerial;
            $activatedWarrantyIds[] = $warranty->id;
        }

        if (!empty($activatedSerials)) {
            $detail->warranty_status = ($activatedCount + count($activatedSerials)) >= (int) $detail->qty ? 1 : 0;
            $detail->save();
        }

        return [
            'activated_serials' => $activatedSerials,
            'failed_serials' => $failedSerials,
            'activated_warranty_ids' => $activatedWarrantyIds,
        ];
    }
}
