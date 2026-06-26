<?php
namespace App\Jobs;

use App\Models\Conversation;
use App\Models\MessageRead;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UpdateReadStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public function backoff(): array { return [10, 30, 60]; }

    public function __construct(public Conversation $conversation, public User $user) {}

    public function handle(): void
    {
        $participant = $this->conversation->participants()->where('user_id', $this->user->id)->first();
        if ($participant) {
            $participant->update(['last_read_at' => now()]);
        }
    }

    public function failed(Throwable $e): void
    {
        \Log::error('UpdateReadStatusJob failed', ['error' => $e->getMessage()]);
    }
}
