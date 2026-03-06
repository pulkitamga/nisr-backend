<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Models\Admin;
use App\Models\ServiceInvoice;
use App\Models\ServiceJobActivity;
use App\Models\SupportTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ExpireServiceInvoicePaymentLinks extends Command
{
    protected $signature = 'service-invoices:expire-pending-links';
    protected $description = 'Auto-expire stale pending service invoice payment links and notify admins';

    public function __construct(
        private readonly AdminNotificationRepositoryInterface $notificationRepo
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = now();
        $expiredCount = 0;
        $sampleTicketIds = [];
        $firstTicketId = null;

        ServiceInvoice::query()
            ->with('ticket')
            ->where('payment_status', 'pending')
            ->where(function ($query) use ($now): void {
                $query->where(function ($subQuery) use ($now): void {
                    $subQuery->whereNotNull('payment_link_expires_at')
                        ->where('payment_link_expires_at', '<=', $now);
                })->orWhere(function ($subQuery) use ($now): void {
                    $subQuery->whereNull('payment_link_expires_at')
                        ->whereNotNull('generated_at')
                        ->where('generated_at', '<=', $now->copy()->subHours(24));
                });
            })
            ->orderBy('id')
            ->chunkById(100, function ($invoices) use (&$expiredCount, &$sampleTicketIds, &$firstTicketId): void {
                foreach ($invoices as $invoice) {
                    $updated = ServiceInvoice::query()
                        ->where('id', $invoice->id)
                        ->where('payment_status', 'pending')
                        ->update([
                            'payment_status' => 'expired',
                            'updated_at' => now(),
                        ]);

                    if ($updated < 1) {
                        continue;
                    }

                    $expiredCount++;

                    if (!$firstTicketId && $invoice->ticket_id) {
                        $firstTicketId = (int)$invoice->ticket_id;
                    }

                    if ($invoice->ticket_id && count($sampleTicketIds) < 10) {
                        $sampleTicketIds[] = (string)$invoice->ticket_id;
                    }

                    if ($invoice->job_id) {
                        $description = "Invoice payment link auto-expired by scheduler #{$invoice->id}";
                        $alreadyLogged = ServiceJobActivity::query()
                            ->where('job_id', $invoice->job_id)
                            ->where('activity_type', 'invoice_link_expired')
                            ->where('description', $description)
                            ->exists();

                        if (!$alreadyLogged) {
                            ServiceJobActivity::query()->create([
                                'job_id' => $invoice->job_id,
                                'activity_type' => 'invoice_link_expired',
                                'description' => $description,
                                'created_by' => $invoice->ticket?->owner_id ?? $invoice->ticket?->employee_id ?? 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            });

        if ($expiredCount <= 0) {
            $this->info('No stale pending service invoice payment links found.');
            return self::SUCCESS;
        }

        $summary = $this->notifyAdmins(
            expiredCount: $expiredCount,
            ticketIds: $sampleTicketIds,
            firstTicketId: $firstTicketId
        );

        $this->info("Expired {$expiredCount} stale service invoice link(s). Admin notifications: {$summary['database']}, admin emails: {$summary['email']}.");
        return self::SUCCESS;
    }

    private function notifyAdmins(int $expiredCount, array $ticketIds = [], ?int $firstTicketId = null): array
    {
        $admins = collect();
        try {
            $admins = Admin::query()
                ->permission('crm_section.service_ticket_details')
                ->active()
                ->get(['id', 'email']);
        } catch (\Throwable $exception) {
            Log::warning('Service invoice link expiry permission lookup failed, fallback to all active admins', [
                'error' => $exception->getMessage(),
            ]);
        }

        if ($admins->isEmpty()) {
            $admins = Admin::query()->active()->get(['id', 'email']);
        }

        if ($admins->isEmpty()) {
            return ['database' => 0, 'email' => 0];
        }

        $sample = implode(', ', array_slice(array_unique(array_filter($ticketIds)), 0, 10));
        $message = "Auto-expired {$expiredCount} stale pending service invoice payment link(s).";
        if ($sample !== '') {
            $message .= " Tickets: {$sample}.";
        }

        $title = 'Service invoice links auto-expired';
        $link = route('admin.support-ticket.view', ['status' => 'service']);
        $recipients = $admins->map(fn($admin) => ['type' => 'employee', 'id' => $admin->id])->values()->all();

        $databaseCount = 0;
        try {
            $created = $this->notificationRepo->notifyRecipients(
                $firstTicketId ?? 0,
                SupportTicket::class,
                $title,
                $message,
                $link,
                $recipients
            );
            $databaseCount = $created->count();
        } catch (\Throwable $exception) {
            Log::warning('Service invoice link expiry admin DB notification failed', [
                'error' => $exception->getMessage(),
            ]);
        }

        $emailCount = 0;
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
                    $emailCount++;
                } catch (\Throwable $exception) {
                    Log::warning('Service invoice link expiry admin email failed', [
                        'email' => $email,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return [
            'database' => $databaseCount,
            'email' => $emailCount,
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

    private function normalizeEmail(?string $email): ?string
    {
        if (!$email) {
            return null;
        }

        $normalized = trim($email);
        return filter_var($normalized, FILTER_VALIDATE_EMAIL) ? $normalized : null;
    }
}
