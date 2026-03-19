<?php

namespace App\Services\Crm;

use App\Models\InboxMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Contact Resolver Service
 *
 * Extracts the logic for resolving contact_id from various auth guards
 * and email/phone lookups. This makes the code more testable and
 * easier to maintain.
 */
class ContactResolver
{
    /**
     * Resolve the contact_id for an inbox message.
     *
     * Priority order:
     * 1. Already set contact_id (returns early)
     * 2. Customer auth guard
     * 3. Seller auth guard
     * 4. Email lookup
     * 5. Phone lookup
     *
     * @param InboxMessage $message The message to resolve contact for
     * @return int|null The resolved contact ID or null
     */
    public function resolveContact(InboxMessage $message): ?int
    {
        // If contact_id is already set, no need to resolve
        if (!empty($message->contact_id)) {
            return $message->contact_id;
        }

        // Try customer auth guard first
        $contactId = $this->resolveFromAuthGuard('customer');
        if ($contactId) {
            return $contactId;
        }

        // Try seller auth guard second
        $contactId = $this->resolveFromAuthGuard('seller');
        if ($contactId) {
            return $contactId;
        }

        // Try email lookup
        $contactId = $this->resolveFromEmail($message->sender_email);
        if ($contactId) {
            return $contactId;
        }

        // Try phone lookup
        return $this->resolveFromPhone($message->sender_phone);
    }

    /**
     * Resolve contact ID from a specific auth guard.
     */
    protected function resolveFromAuthGuard(string $guard): ?int
    {
        if (Auth::guard($guard)->check()) {
            return Auth::guard($guard)->id();
        }

        return null;
    }

    /**
     * Resolve contact ID from email address.
     */
    protected function resolveFromEmail(?string $email): ?int
    {
        if (empty($email)) {
            return null;
        }

        $user = User::where('email', $email)->first();

        return $user?->id;
    }

    /**
     * Resolve contact ID from phone number.
     */
    protected function resolveFromPhone(?string $phone): ?int
    {
        if (empty($phone)) {
            return null;
        }

        $user = User::where('phone', $phone)->first();

        return $user?->id;
    }

    /**
     * Set contact_id on a message using resolution logic.
     * This is a convenience method for use in model events.
     */
    public function setContactOnMessage(InboxMessage $message): void
    {
        $contactId = $this->resolveContact($message);

        if ($contactId !== null) {
            $message->contact_id = $contactId;
        }
    }
}
