<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Deal;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Exception;

class LeadConvertService
{
    public function convert(Lead $lead, array $data): Deal
    {
        $admin = auth('admin')->user();
        $logContext = [
            'admin_id' => $admin?->id,
            'admin_name' => $admin?->name,
            'lead_id' => $lead->id,
            'lead_subject' => $lead->subject ?? 'No Subject',
            'input_data' => $data,
        ];

        Log::info('Lead conversion started', $logContext);

        try {
            return DB::transaction(function () use ($lead, $data, $logContext, $admin) {

                Log::info('Checking for existing open deal', [
                    'party_type' => $data['party_type'],
                    'party_id' => $data['party_id'],
                ] + $logContext);

                $existingDeal = Deal::where('related_party_type', $data['party_type'])
                    ->where('related_party_id', $data['party_id'])
                    ->where('status', 'open')
                    ->first();

                if ($existingDeal) {
                    Log::warning('Blocked: Deal already exists and is open', [
                        'existing_deal_id' => $existingDeal->id,
                    ] + $logContext);

                    throw new Exception("A deal for this party already exists (Deal #{$existingDeal->id}) and is still open.");
                }

                $stage = $data['party_type'] === 'company' ? 'join_request' : 'register';

                Log::info('Creating new deal', [
                    'stage' => $stage,
                    'owner_id' => $data['owner_id'],
                    'employee_id' => $data['employee_id'] ?? $admin?->id,
                ] + $logContext);

                $deal = Deal::create([
                    'lead_id'            => $lead->id,
                    'related_party_type' => $data['party_type'],
                    'related_party_id'   => $data['party_id'],
                    'stage'              => $stage,
                    'owner_id'           => $data['owner_id'],
                    'value'              => $data['value'] ?? 0,
                    'source_id'          => $lead->source_id,
                    'po_id'              => $lead->po_id,
                    'status'             => 'open',
                    'quotation_id'       => null,
                    'quotation_status'   => 'draft',
                    'employee_id'        => $data['employee_id'] ?? $admin?->id,
                ]);

                Log::info('Deal created successfully', [
                    'deal_id' => $deal->id,
                ] + $logContext);

                // Update Lead
                if ($data['party_type'] === 'company') {
                    $lead->company_id = $data['party_id'];
                    $lead->contact_id = null;
                } elseif ($data['party_type'] === 'contact') {
                    $lead->contact_id = $data['party_id'];
                    $lead->company_id = null;
                }

                $lead->status = 'converted';
                $lead->converted_at = now();
                $lead->save();

                Log::info('Lead updated to converted', [
                    'lead_id' => $lead->id,
                    'contact_id' => $lead->contact_id,
                    'company_id' => $lead->company_id,
                ] + $logContext);

                // Create Follow-up Activity
                Activity::create([
                    'deal_id'     => $deal->id,
                    'type'        => 'follow_up',
                    'subject'     => 'Initial follow-up after conversion',
                    'due_date'    => now()->addDay(),
                    'assigned_to' => $deal->owner_id,
                    'status'      => 'pending',
                ]);

                Log::info('Follow-up activity created', ['deal_id' => $deal->id] + $logContext);

                // Activity Log
                activity()
                    ->performedOn($deal)
                    ->causedBy($admin)
                    ->withProperties([
                        'lead_id' => $lead->id,
                        'from' => 'lead',
                        'to' => 'deal',
                    ])
                    ->log("Lead #{$lead->id} converted to Deal #{$deal->id} by {$admin?->name}");

                Log::info('Lead successfully converted to Deal', [
                    'deal_id' => $deal->id,
                    'lead_id' => $lead->id,
                ] + $logContext);

                Toastr::success('Lead converted to deal successfully!');

                return $deal;
            });

        } catch (Exception $e) {
            Log::error('Lead conversion failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ] + $logContext);

            Toastr::error($e->getMessage() ?: 'Failed to convert lead.');

            throw $e;
        }
    }
}