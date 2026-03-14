<?php
namespace App\Jobs;

use App\Models\Blacklist;
use App\Models\WarrantyClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TriageClaimJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public WarrantyClaim $claim) {}

    public function handle()
    {
        $this->claim->loadMissing('warranty');
        $redFlags = $this->hasRedFlags($this->claim);
        $hasActiveWarranty = $this->claim->warranty && $this->claim->warranty->isActive();

        try {
            if ($hasActiveWarranty && !$redFlags) {
                $this->claim->update(['status' => 'approved']);
                \App\Services\RMAService::issueRMA($this->claim);
                return;
            }

            $this->claim->update(['status' => 'triage_pending']);

            $agentEmailSetting = getWebConfig(name: 'warranty_agent_email');
            $agentEmail = is_array($agentEmailSetting)
                ? ($agentEmailSetting['value'] ?? null)
                : $agentEmailSetting;
            if (!is_string($agentEmail) || empty(trim($agentEmail))) {
                $agentEmail = 'agent@yourapp.com';
            }

            event(new \App\Events\ClaimForReviewEvent($this->claim, $agentEmail));
        } catch (\Throwable $exception) {
            Log::error('Warranty claim triage side effect failed', [
                'claim_id' => $this->claim->id,
                'claim_number' => $this->claim->claim_number,
                'status' => $this->claim->status,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function hasRedFlags(WarrantyClaim $claim): bool
    {
        if (!$claim->warranty) {
            return true;
        }

        if (Blacklist::where('serial_number', $claim->serial_number)->exists()) {
            return true;
        }

        $description = trim(strip_tags((string)$claim->description));
        if ($description === '' || strlen($description) < 10) {
            return true;
        }

        $hasAnotherOpenClaim = WarrantyClaim::where('warranty_id', $claim->warranty_id)
            ->where('id', '!=', $claim->id)
            ->whereNotIn('status', ['resolved', 'closed', 'rejected'])
            ->exists();

        return $hasAnotherOpenClaim;
    }
}
