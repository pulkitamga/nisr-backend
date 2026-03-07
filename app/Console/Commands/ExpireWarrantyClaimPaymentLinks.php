<?php

namespace App\Console\Commands;

use App\Models\WarrantyClaimPayment;
use App\Services\WarrantyPaymentLinkNotificationService;
use Illuminate\Console\Command;

class ExpireWarrantyClaimPaymentLinks extends Command
{
    protected $signature = 'warranty-claims:expire-pending-links';
    protected $description = 'Auto-expire stale pending online warranty claim payment links and notify admins';

    public function __construct(
        private readonly WarrantyPaymentLinkNotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = now();
        $expiredCount = 0;
        $sampleClaimNumbers = [];
        $firstClaimId = null;

        WarrantyClaimPayment::query()
            ->with(['claim'])
            ->where('payment_channel', 'online_link')
            ->where('payment_status', 'pending')
            ->whereNotNull('payment_link_expires_at')
            ->where('payment_link_expires_at', '<=', $now)
            ->orderBy('id')
            ->chunkById(100, function ($payments) use (&$expiredCount, &$sampleClaimNumbers, &$firstClaimId, $now): void {
                foreach ($payments as $payment) {
                    $metadata = is_array($payment->metadata) ? $payment->metadata : [];
                    $metadata['expired_via_scheduler'] = true;
                    $metadata['expired_at'] = $now->toDateTimeString();

                    $updated = WarrantyClaimPayment::query()
                        ->where('id', $payment->id)
                        ->where('payment_status', 'pending')
                        ->update([
                            'payment_status' => 'expired',
                            'metadata' => json_encode($metadata),
                            'updated_at' => now(),
                        ]);

                    if ($updated < 1) {
                        continue;
                    }

                    $expiredCount++;

                    if (!$firstClaimId && $payment->claim?->id) {
                        $firstClaimId = (int)$payment->claim->id;
                    }

                    if ($payment->claim?->claim_number && count($sampleClaimNumbers) < 10) {
                        $sampleClaimNumbers[] = $payment->claim->claim_number;
                    }

                    if ($payment->claim) {
                        $payment->claim->timelineEvents()->create([
                            'event_type' => 'payment_link_expired',
                            'description' => "Online payment link auto-expired by scheduler | Payment ID: {$payment->id}",
                            'user_id' => null,
                        ]);
                    }
                }
            });

        if ($expiredCount > 0) {
            $summary = $this->notificationService->notifyActiveAdminsAboutExpiredPendingLinks(
                expiredCount: $expiredCount,
                claimNumbers: $sampleClaimNumbers,
                notifiableClaimId: $firstClaimId
            );

            $this->info("Expired {$expiredCount} stale link(s). Admin notifications: {$summary['database']}, admin emails: {$summary['email']}.");
            return self::SUCCESS;
        }

        $this->info('No stale pending warranty payment links found.');
        return self::SUCCESS;
    }
}
