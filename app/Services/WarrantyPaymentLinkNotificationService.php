<?php

namespace App\Services;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Models\Admin;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimPayment;
use App\Utils\SMSModule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WarrantyPaymentLinkNotificationService
{
    public function __construct(
        private readonly AdminNotificationRepositoryInterface $notificationRepo
    ) {}

    public function dispatchCustomerOnlineLink(WarrantyClaimPayment $payment, bool $isReminder = false): array
    {
        $payment->loadMissing(['claim.warranty.user']);
        $claim = $payment->claim;
        $customer = $claim?->warranty?->user;

        if (
            !$claim
            || !$customer
            || $payment->payment_channel !== 'online_link'
            || empty($payment->payment_link)
        ) {
            return [
                'sms' => 'skipped',
                'email' => 'skipped',
            ];
        }

        $claimNumber = $claim->claim_number ?: ('#' . $claim->id);
        $amount = number_format((float)$payment->amount, 2, '.', '');
        $expiresAt = $payment->payment_link_expires_at?->format('Y-m-d H:i');
        $actionLabel = $isReminder ? 'Payment reminder' : 'Payment link generated';

        $smsBody = "{$actionLabel} for warranty claim {$claimNumber}. Amount: {$amount}. Link: {$payment->payment_link}";
        if ($expiresAt) {
            $smsBody .= " | Expires: {$expiresAt}";
        }

        $emailSubject = $isReminder
            ? "Warranty claim {$claimNumber} payment reminder"
            : "Warranty claim {$claimNumber} payment link";
        $emailBody = "Warranty claim {$claimNumber} requires payment.\n\n"
            . "Amount: {$amount}\n"
            . "Payment link: {$payment->payment_link}\n";

        if ($expiresAt) {
            $emailBody .= "Expires at: {$expiresAt}\n";
        }

        $emailBody .= "\nIf you already paid, please ignore this message.";

        $dispatch = [
            'sms' => 'skipped',
            'email' => 'skipped',
        ];

        $phone = $this->normalizePhone($customer->phone ?? null);
        if ($phone) {
            try {
                $smsResponse = SMSModule::sendTextMessage($phone, $smsBody);
                $dispatch['sms'] = $this->mapSmsResponse($smsResponse);
            } catch (\Throwable $exception) {
                Log::warning('Warranty payment link SMS dispatch failed', [
                    'payment_id' => $payment->id,
                    'claim_id' => $claim->id,
                    'phone' => $phone,
                    'error' => $exception->getMessage(),
                ]);
                $dispatch['sms'] = 'failed';
            }
        }

        $email = $this->normalizeEmail($customer->email ?? null);
        if ($email) {
            if (!$this->isMailEnabled()) {
                $dispatch['email'] = 'disabled';
            } else {
                try {
                    Mail::raw($emailBody, function ($message) use ($email, $emailSubject): void {
                        $message->to($email)->subject($emailSubject);
                    });
                    $dispatch['email'] = 'success';
                } catch (\Throwable $exception) {
                    Log::warning('Warranty payment link email dispatch failed', [
                        'payment_id' => $payment->id,
                        'claim_id' => $claim->id,
                        'email' => $email,
                        'error' => $exception->getMessage(),
                    ]);
                    $dispatch['email'] = 'failed';
                }
            }
        }

        return $dispatch;
    }

    public function notifyActiveAdminsAboutExpiredPendingLinks(
        int $expiredCount,
        array $claimNumbers = [],
        ?int $notifiableClaimId = null
    ): array {
        if ($expiredCount <= 0) {
            return ['database' => 0, 'email' => 0];
        }

        $admins = collect();
        try {
            $admins = Admin::query()
                ->permission('crm_section.warranty_claim_payment')
                ->active()
                ->get(['id', 'email']);
        } catch (\Throwable $exception) {
            Log::warning('Warranty stale payment link permission lookup failed, falling back to all active admins', [
                'error' => $exception->getMessage(),
            ]);
        }

        if ($admins->isEmpty()) {
            $admins = Admin::query()->active()->get(['id', 'email']);
        }

        if ($admins->isEmpty()) {
            return ['database' => 0, 'email' => 0];
        }

        $claimNumbers = array_values(array_unique(array_filter($claimNumbers)));
        $sample = implode(', ', array_slice($claimNumbers, 0, 10));
        $message = "Auto-expired {$expiredCount} stale pending online warranty payment link(s).";
        if ($sample !== '') {
            $message .= " Claims: {$sample}.";
        }

        $link = route('admin.warranty.claim.waiting-payment');
        $title = 'Warranty payment links auto-expired';

        $recipients = $admins
            ->map(fn($admin) => ['type' => 'employee', 'id' => $admin->id])
            ->values()
            ->all();

        $createdCount = 0;
        try {
            $created = $this->notificationRepo->notifyRecipients(
                $notifiableClaimId ?? 0,
                WarrantyClaim::class,
                $title,
                $message,
                $link,
                $recipients
            );
            $createdCount = $created->count();
        } catch (\Throwable $exception) {
            Log::warning('Warranty stale payment link admin DB notification failed', [
                'expired_count' => $expiredCount,
                'error' => $exception->getMessage(),
            ]);
        }

        $sentEmailCount = 0;
        if ($this->isMailEnabled()) {
            foreach ($admins as $admin) {
                $email = $this->normalizeEmail($admin->email ?? null);
                if (!$email) {
                    continue;
                }

                try {
                    Mail::raw("{$message}\n\nOpen: {$link}", function ($mail) use ($email, $title): void {
                        $mail->to($email)->subject($title);
                    });
                    $sentEmailCount++;
                } catch (\Throwable $exception) {
                    Log::warning('Warranty stale payment link admin email failed', [
                        'email' => $email,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return [
            'database' => $createdCount,
            'email' => $sentEmailCount,
        ];
    }

    private function isMailEnabled(): bool
    {
        $mailConfig = getWebConfig(name: 'mail_config');
        if (is_array($mailConfig) && (int)($mailConfig['status'] ?? 0) === 1) {
            return true;
        }

        $sendgridConfig = getWebConfig(name: 'mail_config_sendgrid');
        return is_array($sendgridConfig) && (int)($sendgridConfig['status'] ?? 0) === 1;
    }

    private function mapSmsResponse(string $response): string
    {
        return match ($response) {
            'success' => 'success',
            'not_found' => 'disabled',
            default => 'failed',
        };
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $normalized = trim($phone);
        return strlen($normalized) >= 6 ? $normalized : null;
    }

    private function normalizeEmail(?string $email): ?string
    {
        if (!$email) {
            return null;
        }

        $normalized = trim($email);
        return filter_var($normalized, FILTER_VALIDATE_EMAIL) ? $normalized : null;
    }
}
