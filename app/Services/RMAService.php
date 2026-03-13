<?php
namespace App\Services;

use App\Events\EmailVerificationEvent;
use App\Models\WarrantyClaim;
use App\Notifications\RMAIssued;  
use Illuminate\Support\Str;
use App\Models\Branch;

class RMAService
{
    public static function issueRMA(WarrantyClaim $claim)
    {
        $warranty = $claim->warranty;
        $fallbackBranchId = $warranty?->branch_id ?: Branch::query()->value('id');
        $resolvedBranchId = $claim->branch_id ?: $fallbackBranchId;

        $claim->update([
            'status' => 'rma_issued',
            'rma_number' => 'RMA-' . Str::upper(Str::random(8)),
            'rma_deadline' => now()->addDays(14),
            'branch_id' => $resolvedBranchId,
        ]);

        $instructions = self::generateInstructions($claim);
        $user = $warranty?->user;

        if ($user) {
            $user->notify(new RMAIssued($claim, $instructions));
            return;
        }

        $email = $warranty?->activated_by_email;
        if (is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mailConfig = getWebConfig(name: 'mail_config');
            $mailEnabled = is_array($mailConfig) && (($mailConfig['status'] ?? 0) == 1);

            if ($mailEnabled) {
                $data = [
                    'userName' => $warranty?->activated_by_name ?? 'Customer',
                    'subject' => translate('RMA Issued'),
                    'title' => translate('Your RMA') . ' ' . $claim->rma_number,
                    'rmaNumber' => $claim->rma_number,
                    'instructions' => $instructions,
                    'userType' => 'customer',
                    'templateName' => 'rma-issued',
                ];
                event(new EmailVerificationEvent($email, $data));
            }
        }
    }

    private static function generateInstructions(WarrantyClaim $claim): string
    {
        $branch = $claim->branch ?? Branch::first();
        $branchName = $branch?->branch_name ?? $branch?->name ?? 'our service center';
        return "Return to {$branchName}. Pack securely. Deadline: {$claim->rma_deadline}.";
    }
}
