<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InboxMessage;
use App\Models\User;
use App\Models\InboxSuggestion;

class GenerateInboxSuggestions extends Command
{
    protected $signature = 'inbox:generate-suggestions';
    protected $description = 'Generate user suggestions for inbox messages with null contact_id';

    public function handle()
    {
        $messages = InboxMessage::whereNull('contact_id')->get();

        foreach ($messages as $msg) {
            $user = null;
            if (!empty($msg->sender_email)) {
                $user = User::where('email', $msg->sender_email)->first();
            }
            if (!$user && !empty($msg->sender_phone)) {
                $user = User::where('phone', $msg->sender_phone)->first();
            }

            if ($user) {
                // Create/Update suggestion
                InboxSuggestion::updateOrCreate(
                    ['inbox_message_id' => $msg->id],
                    ['user_id' => $user->id, 'status' => 'pending']
                );
            }
        }

        $this->info('Inbox suggestions generated successfully.');
    }
}
