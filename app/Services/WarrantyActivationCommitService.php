<?php

namespace App\Services;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Models\ActivationReview;
use App\Models\Warranty;
use App\Models\WarrantyTimelineEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarrantyActivationCommitService
{
    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
    ) {}

    public function commit(Warranty $warranty, Request|array $payload, array $options = []): Warranty
    {
        return DB::transaction(function () use ($warranty, $payload, $options) {
            $lockedWarranty = Warranty::query()
                ->with('product')
                ->lockForUpdate()
                ->findOrFail($warranty->id);

            $currentStatus = (string) $lockedWarranty->status;
            if (in_array($currentStatus, ['active', 'pending_review'], true)) {
                return $lockedWarranty;
            }

            if (!in_array($currentStatus, ['preactivated', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'serial_number' => $this->stringOption(
                        $options,
                        'ineligible_message',
                        'Invalid serial number or status not eligible for activation.'
                    ),
                ]);
            }

            $conflictingActiveWarrantyExists = Warranty::query()
                ->where('serial_number', $lockedWarranty->serial_number)
                ->whereKeyNot($lockedWarranty->id)
                ->active()
                ->lockForUpdate()
                ->exists();

            if ($conflictingActiveWarrantyExists) {
                throw ValidationException::withMessages([
                    'serial_number' => $this->stringOption(
                        $options,
                        'active_conflict_message',
                        'An active warranty already exists for this serial.'
                    ),
                ]);
            }

            $defaultDuration = $this->businessSettingRepo->getFirstWhere(['type' => 'warranty_months'])['value'] ?? '12';
            $duration = $lockedWarranty->product?->warranty_duration ?? $defaultDuration;
            $purchaseDate = Carbon::parse((string) $this->payloadValue($payload, 'purchase_date'));
            $start = $purchaseDate->copy();
            $end = $purchaseDate->copy()->addMonths($duration);
            $autoApprove = $this->businessSettingRepo->getFirstWhere(['type' => 'warranty_auto_approve_off_platform'])['value'] ?? '0';
            $flagged = (bool) ($options['flagged'] ?? false);
            $status = ($flagged && $autoApprove != '1') ? 'pending_review' : 'active';
            $activationIp = $this->stringOption(
                $options,
                'activation_ip',
                (string) ($this->payloadValue($payload, 'activation_ip') ?: request()->ip())
            );

            $lockedWarranty->update([
                'status' => $status,
                'activation_date' => $purchaseDate,
                'start_date' => $start,
                'end_date' => $end,
                'purchase_date' => $purchaseDate,
                'retailer_branch_id' => $this->payloadValue($payload, 'retailer_branch_id'),
                'retailer_name' => $this->payloadValue($payload, 'retailer_name'),
                'invoice_number' => $this->payloadValue($payload, 'invoice_number'),
                'activated_ip' => $activationIp,
                'activation_method' => $this->stringOption($options, 'activation_method'),
                'policy_version' => $this->payloadValue($payload, 'policy_version', $options['policy_version'] ?? null),
                'consent_checked' => true,
                'consent_timestamp' => now(),
                'consent_ip' => $activationIp,
                'activated_by_name' => $this->payloadValue($payload, 'name'),
                'activated_by_phone' => $this->payloadValue($payload, 'phone'),
                'activated_by_email' => $this->payloadValue($payload, 'email'),
            ]);

            $receiptPath = $this->payloadValue($payload, 'receipt_path', $options['receipt_path'] ?? null);
            if ($receiptPath) {
                $lockedWarranty->update(['receipt_path' => $receiptPath]);
            }

            WarrantyTimelineEvent::create([
                'warranty_id' => $lockedWarranty->id,
                'event_type' => 'activated',
                'description' => $this->stringOption($options, 'timeline_description', 'Activated'),
                'timestamp' => now(),
                'user_id' => $options['user_id'] ?? null,
            ]);

            if ($flagged && $autoApprove != '1') {
                $reasons = $this->normalizeFlaggedReasons($options['flagged_reason'] ?? []);
                $submittedAt = now();

                ActivationReview::query()->updateOrCreate(
                    [
                        'warranty_id' => $lockedWarranty->id,
                        'status' => 'pending',
                    ],
                    [
                        'review_notes' => $this->stringOption(
                            $options,
                            'review_notes',
                            'Auto-created from warranty activation; awaiting admin review.'
                        ),
                        'flagged_reason' => !empty($reasons) ? implode(', ', $reasons) : 'No reason specified',
                        'submitted_at' => $submittedAt,
                        'first_response_due' => $submittedAt->copy()->addHours(24),
                        'decision_due' => $submittedAt->copy()->addDays(3),
                    ]
                );
            }

            return $lockedWarranty->fresh();
        });
    }

    private function payloadValue(Request|array $payload, string $key, mixed $default = null): mixed
    {
        if ($payload instanceof Request) {
            return $payload->input($key, $default);
        }

        return $payload[$key] ?? $default;
    }

    private function normalizeFlaggedReasons(array|string|null $flaggedReason): array
    {
        if (is_array($flaggedReason)) {
            return array_values(array_filter($flaggedReason));
        }

        if (is_string($flaggedReason) && $flaggedReason !== '') {
            return array_values(array_filter(explode(', ', $flaggedReason)));
        }

        return [];
    }

    private function stringOption(array $options, string $key, ?string $default = null): ?string
    {
        $value = $options[$key] ?? $default;

        return $value === null ? null : (string) $value;
    }
}
