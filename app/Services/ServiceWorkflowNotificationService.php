<?php

namespace App\Services;

use App\Contracts\Repositories\AdminNotificationRepositoryInterface;
use App\Models\Admin;
use App\Models\Departments;
use App\Models\SupportTicket;
use App\Models\User;
use App\Utils\SMSModule;
use Illuminate\Support\Facades\Log;

class ServiceWorkflowNotificationService
{
    private const DEFAULT_SMS_TEMPLATES = [
        'ticket_created' => 'Ticket #{ticket_id} created for your service request.',
        'estimate_created' => 'Ticket #{ticket_id}: estimate created. Total {amount}.',
        'ticket_assigned' => 'Ticket #{ticket_id} assigned for {service_title}.',
        'ticket_scheduled' => 'Ticket #{ticket_id} scheduled for {scheduled_at}.',
        'job_started' => 'Ticket #{ticket_id} service has started.',
        'change_order_created' => 'Ticket #{ticket_id}: change order added. {description}',
        'job_completed' => 'Ticket #{ticket_id} completed. Please check invoice.',
        'invoice_generated' => 'Ticket #{ticket_id}: invoice {amount}. Pay: {payment_link}',
        'payment_received' => 'Ticket #{ticket_id}: payment received.',
        'qa_failed' => 'Ticket #{ticket_id} reopened after QA review.',
        'ticket_closed' => 'Ticket #{ticket_id} has been closed.',
        'ticket_cancelled' => 'Ticket #{ticket_id} cancelled. Reason: {reason}',
    ];

    public function __construct(
        private readonly AdminNotificationRepositoryInterface $notificationRepo
    ) {}

    public function notify(
        SupportTicket|int $ticket,
        string $eventKey,
        string $title,
        string $message,
        ?string $link,
        array $recipients,
        array $templateData = []
    ): void {
        $ticketModel = $ticket instanceof SupportTicket ? $ticket : SupportTicket::query()->find($ticket);
        if (!$ticketModel) {
            return;
        }

        if (!empty($recipients)) {
            $this->notificationRepo->notifyRecipients(
                $ticketModel->id,
                SupportTicket::class,
                $title,
                $message,
                $link,
                $recipients
            );
        }

        $template = $this->resolveSmsTemplate($eventKey);
        if (!$template) {
            return;
        }

        $payload = array_merge([
            'ticket_id' => $ticketModel->id,
            'customer_id' => $ticketModel->customer_id,
            'employee_id' => $ticketModel->employee_id,
            'department_id' => $ticketModel->department_id,
        ], $templateData);

        $smsBody = $this->renderTemplate($template, $payload);
        $phones = $this->resolveRecipientPhones($recipients);

        foreach ($phones as $phone) {
            try {
                SMSModule::sendTextMessage($phone, $smsBody);
            } catch (\Throwable $exception) {
                Log::warning('Service workflow SMS dispatch failed', [
                    'event' => $eventKey,
                    'ticket_id' => $ticketModel->id,
                    'phone' => $phone,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function resolveSmsTemplate(string $eventKey): ?string
    {
        $customTemplates = $this->normalizeTemplates(getWebConfig('service_sms_templates'));
        $template = $customTemplates[$eventKey] ?? self::DEFAULT_SMS_TEMPLATES[$eventKey] ?? null;

        if (!is_string($template) || trim($template) === '') {
            return null;
        }

        return $template;
    }

    private function normalizeTemplates(mixed $templates): array
    {
        if (is_string($templates)) {
            $decoded = json_decode($templates, true);
            $templates = is_array($decoded) ? $decoded : [];
        }

        if (is_object($templates)) {
            $templates = (array)$templates;
        }

        if (!is_array($templates)) {
            return [];
        }

        if (isset($templates['value'])) {
            $value = $templates['value'];
            if (is_string($value)) {
                $decodedValue = json_decode($value, true);
                if (is_array($decodedValue)) {
                    $templates = $decodedValue;
                }
            } elseif (is_array($value)) {
                $templates = $value;
            }
        }

        $normalized = [];
        foreach ($templates as $key => $value) {
            if (is_string($key) && is_string($value) && trim($value) !== '') {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    private function renderTemplate(string $template, array $data): string
    {
        $replacements = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $replacements['{' . $key . '}'] = (string)$value;
            }
        }

        return strtr($template, $replacements);
    }

    private function resolveRecipientPhones(array $recipients): array
    {
        $phones = [];

        foreach ($recipients as $recipient) {
            $type = $recipient['type'] ?? null;
            $id = $recipient['id'] ?? null;
            if (!$type || !$id) {
                continue;
            }

            $phone = null;
            switch ($type) {
                case 'customer':
                    $phone = User::query()->where('id', $id)->value('phone');
                    break;
                case 'employee':
                case 'user':
                    $phone = Admin::query()->where('id', $id)->value('phone');
                    break;
                case 'department':
                    $headId = Departments::query()->where('id', $id)->value('head_id');
                    if ($headId) {
                        $phone = Admin::query()->where('id', $headId)->value('phone');
                    }
                    break;
            }

            $normalized = $this->normalizePhone($phone);
            if ($normalized) {
                $phones[] = $normalized;
            }
        }

        return array_values(array_unique($phones));
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $normalized = trim($phone);
        return strlen($normalized) >= 6 ? $normalized : null;
    }
}
