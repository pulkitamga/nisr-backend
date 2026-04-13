<?php

namespace App\Exports;

use App\Models\SupportTicket;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

use function App\Utils\support_ticket_status_label;

class SupportTicketExport implements FromCollection, WithHeadings, WithTitle
{
    private ?Collection $tickets = null;

    public function __construct(
        protected $request,
        protected $type
    ) {
    }

    public function collection(): Collection
    {
        return collect($this->rows());
    }

    public function rows(): array
    {
        return $this->tickets()->map(function (SupportTicket $ticket) {
            $row = [
                (string) $ticket->id,
                (string) ($ticket->subject ?? translate('No Subject')),
                $ticket->customer
                    ? trim(implode(' ', array_filter([
                        (string) ($ticket->customer->f_name ?? ''),
                        (string) ($ticket->customer->l_name ?? ''),
                    ])))
                    : translate('Customer Not Found'),
                (string) ($ticket->customer->email ?? 'N/A'),
                $ticket->sub_type ? str_replace('_', ' ', $ticket->sub_type) : translate('No Sub-Type'),
                ucfirst((string) $ticket->priority),
                support_ticket_status_label($ticket->status_details->name ?? $ticket->status),
            ];

            if ($this->type === 'service') {
                $row[] = (string) ($ticket->latestServiceJob?->service?->name ?? translate('not_available'));
            }

            $row[] = $ticket->department?->getTranslatedField('name') ?? $ticket->department?->name ?? 'N/A';
            $row[] = (string) ($ticket->employee->name ?? 'N/A');
            $row[] = $ticket->created_at?->format('Y-m-d H:i') ?? '-';

            return $row;
        })->all();
    }

    public function headings(): array
    {
        $headings = [
            translate('SL'),
            translate('Subject'),
            translate('customer'),
            translate('customer_Email'),
            translate('Ticket_Type'),
            translate('Priority'),
            translate('status'),
        ];

        if ($this->type === 'service') {
            $headings[] = translate('service');
        }

        $headings[] = translate('Department');
        $headings[] = translate('assigned_employee');
        $headings[] = translate('Created At');

        return $headings;
    }

    public function title(): string
    {
        return translate($this->titleKey());
    }

    public function titleLabel(): string
    {
        return $this->title();
    }

    public function filterSummary(): string
    {
        return implode(' | ', array_filter([
            translate('search') . ': ' . (trim((string) $this->request->get('searchValue', '')) ?: translate('all')),
            translate('Priority') . ': ' . (($this->request->get('priority') && $this->request->get('priority') !== 'all')
                ? ucfirst((string) $this->request->get('priority'))
                : translate('all')),
            translate('status') . ': ' . (($this->request->get('status') && $this->request->get('status') !== 'all')
                ? (string) $this->request->get('status')
                : translate('all')),
        ]));
    }

    private function tickets(): Collection
    {
        if ($this->tickets !== null) {
            return $this->tickets;
        }

        return $this->tickets = SupportTicket::with([
            'customer',
            'status_details',
            'department.translations',
            'employee',
            'latestServiceJob.service',
        ])
            ->when($this->request->get('searchValue'), function ($query) {
                $searchValue = $this->request->get('searchValue');
                $query->where(function ($q) use ($searchValue) {
                    $q->where('subject', 'like', "%{$searchValue}%")
                        ->orWhere('priority', 'like', "%{$searchValue}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($searchValue) {
                            $customerQuery->where('f_name', 'like', "%{$searchValue}%")
                                ->orWhere('l_name', 'like', "%{$searchValue}%")
                                ->orWhereRaw(
                                    "CONCAT(COALESCE(f_name, ''), ' ', COALESCE(l_name, '')) LIKE ?",
                                    ["%{$searchValue}%"]
                                )
                                ->orWhere('email', 'like', "%{$searchValue}%")
                                ->orWhere('phone', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('status_details', function ($statusQuery) use ($searchValue) {
                            $statusQuery->where('name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('relatedInboxMessages', function ($inboxQuery) use ($searchValue) {
                            $inboxQuery->where('sender_name', 'like', "%{$searchValue}%")
                                ->orWhere('sender_email', 'like', "%{$searchValue}%")
                                ->orWhere('sender_phone', 'like', "%{$searchValue}%");
                        });
                });
            })
            ->when($this->request->get('priority') && $this->request->get('priority') !== 'all', function ($query) {
                $query->where('priority', $this->request->get('priority'));
            })
            ->when($status = $this->request->get('status'), function ($query) use ($status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            })
            ->where('type', $this->type)
            ->orderBy('id', 'desc')
            ->get();
    }

    private function titleKey(): string
    {
        return match ($this->type) {
            'complaint' => 'complaint_ticket',
            'support' => 'support_ticket',
            'service' => 'service_ticket',
            'retail' => 'retail_ticket',
            'wholesale' => 'wholesale_ticket',
            'career' => 'career_ticket',
            default => 'support_ticket',
        };
    }
}
