<?php

namespace App\Http\Controllers\restapi\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warranty\ClaimStoreRequest;
use App\Jobs\TriageClaimJob;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimAttachment;
use App\Models\WarrantyTimelineEvent;
use Illuminate\Support\Carbon;

class WarrantyClaimController extends Controller
{
     public function store(ClaimStoreRequest $request)
    {
        try {

            $warranty = Warranty::where('warranty_public_id', $request->warranty_public_id)
                ->firstOrFail();

            if (!$warranty->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Warranty is not active or expired.'
                ], 400);
            }

            if ($warranty->claims()->open()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'There is already an open claim for this warranty.'
                ], 400);
            }

            $description = "Subject: {$request->subject}\nDetails: {$request->details}\nIssue: {$request->issue}";
            $submittedAt = Carbon::now();
            $firstResponseHours = (int) (getWebConfig(name: 'warranty_sla_first_response')['value'] ?? 24);
            $resolutionDays = (int) (getWebConfig(name: 'warranty_sla_decision')['value'] ?? 3);

            $claim = WarrantyClaim::create([
                'warranty_id' => $warranty->id,
                'serial_number' => $warranty->serial_number,
                'branch_id' => $warranty->branch_id,
                'claim_number' => WarrantyClaim::generateClaimNumber('CLM-'),
                'status' => 'new',
                'description' => $description,
                'submitted_at' => $submittedAt,
                'response_due' => $submittedAt->copy()->addHours($firstResponseHours),
                'resolution_due' => $submittedAt->copy()->addDays($resolutionDays),
            ]);

            // Upload images
            foreach ($request->file('product_images') as $file) {
                $path = $file->store('warranty/attachments', 'public');

                WarrantyClaimAttachment::create([
                    'warranty_claim_id' => $claim->id,
                    'file_path' => $path,
                    'type' => 'image',
                ]);
            }

            // Timeline
            WarrantyTimelineEvent::create([
                'warranty_id' => $warranty->id,
                'warranty_claim_id' => $claim->id,
                'event_type' => 'claim_submitted',
                'description' => 'Claim submitted by customer',
                'timestamp' => now(),
            ]);

            TriageClaimJob::dispatch($claim);

            return response()->json([
                'success' => true,
                'message' => 'Claim submitted successfully',
                'data' => [
                    'claim_number' => $claim->claim_number,
                    'status' => $claim->status,
                ]
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
