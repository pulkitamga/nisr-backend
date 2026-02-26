<?php

namespace App\Exports;

use App\Models\SupportTicket;
use App\Models\Service;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SupportTicketExport implements FromCollection, WithHeadings, WithTitle
{
    protected $request;
    protected $type;

    public function __construct($request, $type)
    {
        $this->request = $request;
        $this->type = $type; 
    }

    public function collection()
    {
        $tickets = SupportTicket::with(['customer', 'status_details', 'department', 'employee', 'latestServiceJob'])
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
                        ->orWhereHas('status_details', function ($q) use ($searchValue) {
                            $q->where('name', 'like', "%{$searchValue}%");
                        })
                        ->orWhereHas('relatedInboxMessages', function ($inboxQuery) use ($searchValue) {
                            $inboxQuery->where('sender_name', 'like', "%{$searchValue}%")
                                ->orWhere('sender_email', 'like', "%{$searchValue}%")
                                ->orWhere('sender_phone', 'like', "%{$searchValue}%");
                        });
                });
            })
            ->when($this->request->get('priority') && $this->request->get('priority') != 'all', function ($query) {
                $query->where('priority', $this->request->get('priority'));
            })
            ->when(
                $status = $this->request->get('status'), // request me status
                function ($query) use ($status) {
                    if ($status !== 'all') {
                        $query->where('status', $status);
                    }
                }
            )
            ->where('type', $this->type)
            ->orderBy('id', 'desc')
            ->get();

        $data = new Collection();

        foreach ($tickets as $ticket) {
            $service = $ticket->latestServiceJob
                ? Service::find($ticket->latestServiceJob->service_sku)
                : null;

            $data->push([
                'SL' => $ticket->id,
                'Subject' => $ticket->subject ?? 'No Subject',
                'Customer' => $ticket->customer ? ($ticket->customer->f_name . ' ' . $ticket->customer->l_name) : 'Customer Not Found',
                'Customer Email' => $ticket->customer->email ?? 'N/A',
                'Ticket Type' => $ticket->sub_type ? str_replace('_', ' ', $ticket->sub_type) : 'No Sub-Type',
                'Priority' => ucfirst($ticket->priority),
                'Status' => $ticket->status_details->name ?? $ticket->status,
                'Department' => $ticket->department->name ?? 'N/A',
                'Assigned Employee' => $ticket->employee->name ?? 'N/A',
                'Created At' => $ticket->created_at->format('d M, Y H:i'),
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = [
            'SL',
            'Subject',
            'Customer',
            'Customer Email',
            'Ticket Type',
            'Priority',
            'Status',
            'Department',
            'Assigned Employee',
            'Created At',
        ];

        if ($this->type === 'service') {
            array_splice($headings, 7, 0, 'Service');
        }
        return $headings;
    }

    public function title(): string
    {
        return ucfirst($this->type ?? 'All') . ' Tickets';
    }
}
