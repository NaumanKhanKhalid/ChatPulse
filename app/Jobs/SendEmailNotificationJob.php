<?php
namespace App\Jobs;

use App\Mail\NotificationMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public function backoff(): array { return [10, 30, 60]; }

    public function __construct(
        public User $user,
        public string $subject,
        public string $body,
        public array $data = []
    ) {}

    public function handle(): void
    {
        if (!$this->user->email || !$this->user->email_notifications) return;
        Mail::to($this->user->email)->send(new NotificationMail($this->subject, $this->body, $this->data));
    }

    public function failed(Throwable $e): void
    {
        \Log::error('SendEmailNotificationJob failed', ['user_id' => $this->user->id, 'error' => $e->getMessage()]);
    }
}
