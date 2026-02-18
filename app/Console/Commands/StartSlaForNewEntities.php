<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SlaService;
use App\Models\InboxMessage;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\SupportTicket;
use App\Models\WarrantyClaim; // Agar hai
use Illuminate\Support\Facades\Log;

class StartSlaForNewEntities extends Command
{
    protected $signature = 'sla:start-for-new';
    protected $description = 'Start SLA for newly created entities (Inbox, Lead, Deal, Ticket, etc.)';

    public function handle(SlaService $slaService)
    {
        $this->info('Starting SLA for new entities...');

        $count = 0;

        $count += $this->processEntities(
            InboxMessage::where('status', 'new')
                        ->whereNull('response_due')
                        ->get(),
            $slaService,
            'inbox_message'
        );

        $count += $this->processEntities(
            Lead::where('status', 'new')
                ->whereNull('response_due')
                ->get(),
            $slaService,
            'lead'
        );

        $count += $this->processEntities(
            Deal::where('status', 'new')
                ->whereNull('response_due')
                ->get(),
            $slaService,
            'deal'
        );

        $newTicketStatuses = [1, 20, 27, 36, 43, 56]; 
        $count += $this->processEntities(
            SupportTicket::whereIn('status', $newTicketStatuses)
                         ->whereNull('response_due')
                         ->get(),
            $slaService,
            'ticket'
        );

        $count += $this->processEntities(
            WarrantyClaim::where('status', 'new')->whereNull('response_due')->get(),
           $slaService,
            'warranty_claim'
        );

        $this->info("SLA started for {$count} new entities.");
    }

    private function processEntities($entities, $slaService, $type)
    {
        $count = 0;
        foreach ($entities as $entity) {
            try {
                if (!$entity->priority) {
                    $entity->priority = 'medium';
                    $entity->saveQuietly();
                }

                $slaService->startSlaTimers($entity);
                $count++;
                $this->line("SLA started for {$type} #{$entity->id}");
            } catch (\Exception $e) {
                Log::error("SLA Auto-Start Failed for {$type} #{$entity->id}: " . $e->getMessage());
            }
        }
        return $count;
    }
}