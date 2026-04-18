<?php

namespace App\Exports;

use App\Models\SupportTicket;
use App\Models\SupportTicketStatusMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

use function App\Utils\support_ticket_status_label;

class CareerTicketExport implements FromCollection, WithHeadings, WithTitle
{
    private ?Collection $tickets = null;

    public function __construct(private readonly Request $request)
    {
    }

    public function collection(): Collection
    {
        return collect($this->rows());
    }

    public function rows(): array
    {
        return $this->tickets()->values()->map(function (SupportTicket $ticket) {
            return [
                (string) $ticket->id,
                (string) ($ticket->subject ?? translate('No Subject')),
                (string) ($ticket->relatedInboxMessage?->sender_name
                    ?? trim(implode(' ', array_filter([
                        (string) ($ticket->customer?->f_name ?? ''),
                        (string) ($ticket->customer?->l_name ?? ''),
                    ])))
                    ?: translate('Unknown')),
                (string) ($ticket->relatedInboxMessage?->sender_email ?? $ticket->customer?->email ?? translate('N/A')),
                support_ticket_status_label($ticket->status_details?->name ?? $ticket->status),
                (string) ($ticket->employee?->name ?? translate('Unassigned')),
                ucfirst((string) translate((string) ($ticket->priority ?? ''))),
                $ticket->created_at?->format('Y-m-d H:i') ?? '-',
                $ticket->careerTalentPool?->consent ? translate('Yes') : translate('No'),
                $ticket->careerTalentPool?->recontact_date ?? translate('N/A'),
            ];
        })->all();
    }

    public function headings(): array
    {
        return [
            translate('ticket_id'),
            translate('Subject'),
            translate('candidate_name'),
            translate('candidate_email'),
            translate('Status'),
            translate('recruiter'),
            translate('Priority'),
            translate('Created_At'),
            translate('talent_pool_consent'),
            translate('recontact_date'),
        ];
    }

    public function title(): string
    {
        return translate('career_tickets');
    }

    public function titleLabel(): string
    {
        return $this->title();
    }

    public function filterSummary(): string
    {
        return implode(' | ', [
            translate('Search') . ': ' . (trim((string) $this->request->get('searchValue', '')) ?: translate('All')),
            translate('Priority') . ': ' . (($this->request->filled('priority') && $this->request->get('priority') !== 'all')
                ? ucfirst((string) translate((string) $this->request->get('priority')))
                : translate('All')),
            translate('Status') . ': ' . $this->statusFilterLabel(),
            translate('talent_pool') . ': ' . $this->talentPoolFilterLabel(),
        ]);
    }

    private function tickets(): Collection
    {
        if ($this->tickets !== null) {
            return $this->tickets;
        }

        $query = SupportTicket::query()
            ->with([
                'status_details.translations',
                'employee',
                'customer',
                'relatedInboxMessage',
                'careerTalentPool',
            ])
            ->where('type', 'career');

        if ($this->request->filled('searchValue')) {
            $searchValue = trim((string) $this->request->searchValue);
            $query->where(function ($q) use ($searchValue) {
                $q->where('subject', 'like', '%' . $searchValue . '%')
                    ->orWhereHas('status_details', function ($statusQuery) use ($searchValue) {
                        $statusQuery->where('name', 'like', '%' . $searchValue . '%');
                    })
                    ->orWhereHas('relatedInboxMessage', function ($inboxQuery) use ($searchValue) {
                        $inboxQuery->where('sender_name', 'like', '%' . $searchValue . '%')
                            ->orWhere('sender_email', 'like', '%' . $searchValue . '%');
                    });
            });
        }

        if ($this->request->filled('priority') && $this->request->priority !== 'all') {
            $query->where('priority', $this->request->priority);
        }

        if ($this->request->filled('status') && $this->request->status !== 'all') {
            $query->where('status', $this->request->status);
        }

        if ($this->request->filled('talent_pool') && in_array($this->request->talent_pool, ['yes', 'no'], true)) {
            $query->whereHas('careerTalentPool', function ($careerTalentPoolQuery) {
                $careerTalentPoolQuery->where('consent', $this->request->talent_pool === 'yes' ? 1 : 0);
            });
        }

        return $this->tickets = $query->orderByDesc('id')->get();
    }

    private function statusFilterLabel(): string
    {
        $status = (string) $this->request->get('status', 'all');

        if ($status === '' || $status === 'all') {
            return translate('All');
        }

        $statusModel = SupportTicketStatusMaster::with('translations')->find($status);

        return $statusModel?->getTranslatedField('name')
            ?? $statusModel?->name
            ?? $status;
    }

    private function talentPoolFilterLabel(): string
    {
        return match ((string) $this->request->get('talent_pool', 'all')) {
            'yes' => translate('Yes'),
            'no' => translate('No'),
            default => translate('All'),
        };
    }
}
