<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SlaService;
use App\Models\InboxMessage;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Log;

class CheckSlaBreaches extends Command
{
    protected $signature = 'sla:check-breaches';
    protected $description = 'Check all active entities for SLA breaches every 10 minutes';

    private  $ignoredStatuses = [
            'resolved',
            'closed',
            'rejected',
            'hired',
            'converted',
            'ignore',
            'spam',
            'won',
            'lost',
            'new',
            1,
            10,
            19,
            25,
            26,
            30,
            35,
            33,
            34,
            40,
            41,
            42,
            43,
            48,
            49,
            54,
            55,
            56,
            60,
            61,
            62,
            20,
            27,
            36
        ];

    public function handle(SlaService $slaService)
    {
        $this->info('SLA Breach Check Started...');

        $this->checkEntities(
            $slaService,
            InboxMessage::where('status', 'new')
                ->whereNotNull('response_due')
                ->get(),
            'inbox_message'
        );

        $this->checkEntities(
            $slaService,
            Lead::where('status', 'new')
                ->whereNotNull('response_due')
                ->get(),
            'lead'
        );

        $this->checkEntities(
            $slaService,
            Deal::where('status', 'new')
                ->whereNotNull('response_due')
                ->get(),
            'deal'
        );

        $this->checkEntities(
            $slaService,
            SupportTicket::where('status', 'new')
                ->whereNotNull('response_due')
                ->get(),
            'ticket'
        );

        $this->checkEntities(
            $slaService,
            InboxMessage::whereNotIn('status', $this->ignoredStatuses)
                ->whereNotNull('resolution_due')
                ->get(),
            'inbox_message_resolution'
        );

        $this->checkEntities(
            $slaService,
            Lead::whereNotIn('status', $this->ignoredStatuses)
                ->whereNotNull('resolution_due')
                ->get(),
            'lead_resolution'
        );

        $this->checkEntities(
            $slaService,
            Deal::whereNotIn('status', ['won', 'lost', 'closed', 'new'])
                ->whereNotNull('resolution_due')
                ->get(),
            'deal_resolution'
        );

        $this->checkEntities(
            $slaService,
            SupportTicket::whereNotIn('status', $this->ignoredStatuses)
                ->whereNotNull('resolution_due')
                ->get(),
            'ticket_resolution'
        );

        $this->info('SLA Breach Check Completed!');
    }

    private function checkEntities($slaService, $entities, $type = '')
    {
        if ($entities->isEmpty()) {
            return;
        }

        foreach ($entities as $entity) {
            try {
                $breached = $slaService->checkForBreaches($entity);
                if ($breached) {
                    $this->warn("Breach detected for {$entity->getTable()} #{$entity->id} ({$type})");
                }
            } catch (\Exception $e) {
                Log::error("SLA Check Failed for {$entity->getTable()} #{$entity->id}: " . $e->getMessage());
            }
        }
    }
}
