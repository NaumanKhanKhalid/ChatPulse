<?php
namespace App\Jobs;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class BroadcastMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public function backoff(): array { return [10, 30, 60]; }

    public function __construct(public Message $message) {}

    public function handle(): void
    {
        $this->message->refresh();
        broadcast(new MessageSent($this->message));
    }

    public function failed(Throwable $e): void
    {
        \Log::error('BroadcastMessageJob failed', ['message_id' => $this->message->id, 'error' => $e->getMessage()]);
    }
}
