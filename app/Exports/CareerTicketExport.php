<?php

namespace App\Exports;

use App\Models\SupportTicket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CareerTicketExport implements FromCollection, WithHeadings, WithMapping, ShouldQueue
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = SupportTicket::query()
            ->with(['status_details', 'employee', 'relatedInboxMessage', 'careerTalentPool'])
            ->where('type', 'career');

        // Apply filters
        if ($this->request->filled('searchValue')) {
            $query->where('subject', 'like', '%' . $this->request->searchValue . '%');
        }

        if ($this->request->filled('priority') && $this->request->priority !== 'all') {
            $query->where('priority', $this->request->priority);
        }

        if ($this->request->filled('status') && $this->request->status !== 'all') {
            $query->where('status', $this->request->status);
        }

        // Talent pool filter
        if ($this->request->filled('talent_pool') && in_array($this->request->talent_pool, ['yes', 'no'], true)) {
            $query->whereHas('careerTalentPool', function ($q) {
                if ($this->request->talent_pool === 'yes') {
                    $q->where('consent', 1);
                } elseif ($this->request->talent_pool === 'no') {
                    $q->where('consent', 0);
                }
            });
        }

        // Return pure Eloquent collection
        return $query->get();
    }

    public function headings(): array
    {
        return [
            translate('ticket_id'),
            translate('subject'),
            translate('candidate_name'),
            translate('candidate_email'),
            translate('status'),
            translate('recruiter'),
            translate('priority'),
            translate('created_at'),
            translate('talent_pool_consent'),
            translate('recontact_date'),
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->id,
            $ticket->subject,
            $ticket->relatedInboxMessage?->sender_name ?? 'N/A',
            $ticket->relatedInboxMessage?->sender_email ?? 'N/A',
            $ticket->status_details?->name ?? 'N/A',
            $ticket->employee?->name ?? 'Unassigned',
            ucfirst($ticket->priority),
            $ticket->created_at->format('d M, Y H:i'),
            optional($ticket->careerTalentPool)->consent ? 'Yes' : 'No',
            optional($ticket->careerTalentPool)->recontact_date ?? 'N/A',
        ];
    }
}
