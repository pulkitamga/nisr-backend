<?php

namespace App\Http\Controllers\restapi\v1;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimAttachment;
use App\Models\WarrantyTimelineEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class WarrantyClaimController extends Controller
{
     public function store(Request $request)
    {
        $request->validate([
            'warranty_public_id' => 'required|exists:warranties,warranty_public_id',
            'subject' => 'required|string|max:255',
            'details' => 'required|string',
            'issue' => 'required|string',
            'product_images' => 'required|array|min:1',
            'product_images.*' => 'file|mimes:jpg,jpeg,png|max:2048',
        ]);

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

            $claim = WarrantyClaim::create([
                'warranty_id' => $warranty->id,
                'serial_number' => $warranty->serial_number,
                'claim_number' => 'CLM-' . strtoupper(Str::random(8)),
                'status' => 'new',
                'description' => $description,
                'submitted_at' => Carbon::now(),
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
