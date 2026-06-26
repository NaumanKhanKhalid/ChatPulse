<?php
namespace App\Console\Commands;

use App\Jobs\BroadcastMessageJob;
use App\Jobs\NotifyParticipantsJob;
use App\Models\Message;
use Illuminate\Console\Command;

class SendScheduledMessagesCommand extends Command
{
    protected $signature = 'messages:send-scheduled';
    protected $description = 'Send all scheduled messages that are due';

    public function handle(): void
    {
        $due = Message::where('is_scheduled', true)
            ->whereNull('sent_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($due as $message) {
            $message->update(['sent_at' => now()]);

            $message->conversation->update([
                'last_message_id' => $message->id,
                'last_activity_at' => now(),
            ]);

            BroadcastMessageJob::dispatch($message);
            NotifyParticipantsJob::dispatch($message);
        }

        if ($due->count() > 0) {
            $this->info("Sent {$due->count()} scheduled message(s).");
        }
    }
}
