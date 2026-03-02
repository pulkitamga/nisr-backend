<?php

namespace App\Services;

use App\Models\ServiceInvoice;
use App\Models\ServiceJobActivity;
use App\Models\SupportTicket;

class ServiceInvoicePaymentService
{
    public function handlePaidInvoice(ServiceInvoice|int $invoice): void
    {
        $invoiceModel = $invoice instanceof ServiceInvoice
            ? $invoice
            : ServiceInvoice::query()->with('ticket')->find($invoice);

        if (!$invoiceModel) {
            return;
        }

        if (!$invoiceModel->relationLoaded('ticket')) {
            $invoiceModel->load('ticket');
        }

        $ticket = $invoiceModel->ticket ?: SupportTicket::find($invoiceModel->ticket_id);
        if (!$ticket) {
            return;
        }

        $shouldNotify = true;
        if ($invoiceModel->job_id) {
            $description = "Payment received for invoice #{$invoiceModel->id}";
            $alreadyLogged = ServiceJobActivity::query()
                ->where('job_id', $invoiceModel->job_id)
                ->where('activity_type', 'payment_received')
                ->where('description', $description)
                ->exists();

            if (!$alreadyLogged) {
                ServiceJobActivity::create([
                    'job_id' => $invoiceModel->job_id,
                    'activity_type' => 'payment_received',
                    'description' => $description,
                    'created_by' => $ticket->owner_id ?? $ticket->employee_id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $shouldNotify = false;
            }
        }

        if (!$shouldNotify) {
            return;
        }

        $recipients = [];
        if ($ticket->customer_id) {
            $recipients[] = ['type' => 'customer', 'id' => $ticket->customer_id];
        }
        if ($ticket->employee_id) {
            $recipients[] = ['type' => 'employee', 'id' => $ticket->employee_id];
        }
        if ($ticket->department_id) {
            $recipients[] = ['type' => 'department', 'id' => $ticket->department_id];
        }

        app(ServiceWorkflowNotificationService::class)->notify(
            ticket: $ticket->id,
            eventKey: 'payment_received',
            title: 'Payment Received',
            message: "Payment received for service ticket #{$ticket->id}.",
            link: route('admin.support-ticket.service.singleTicket', $ticket->id),
            recipients: $recipients,
            templateData: ['amount' => number_format((float)$invoiceModel->total, 2)]
        );
    }
}

