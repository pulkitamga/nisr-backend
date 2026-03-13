<?php
namespace App\Jobs;

use App\Models\Blacklist;
use App\Models\WarrantyClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TriageClaimJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public WarrantyClaim $claim) {}

    public function handle()
    {
        $this->claim->loadMissing('warranty');
        $redFlags = $this->hasRedFlags($this->claim);
        $hasActiveWarranty = $this->claim->warranty && $this->claim->warranty->isActive();

        if ($hasActiveWarranty && !$redFlags) {
            $this->claim->update(['status' => 'approved']);
            \App\Services\RMAService::issueRMA($this->claim);
        } else {
            $this->claim->update(['status' => 'triage_pending']);

            $agentEmailSetting = getWebConfig(name: 'warranty_agent_email');
            $agentEmail = is_array($agentEmailSetting)
                ? ($agentEmailSetting['value'] ?? null)
                : $agentEmailSetting;
            if (!is_string($agentEmail) || empty(trim($agentEmail))) {
                $agentEmail = 'agent@yourapp.com';
            }

            // Send event or mail directly
            event(new \App\Events\ClaimForReviewEvent($this->claim, $agentEmail));
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
