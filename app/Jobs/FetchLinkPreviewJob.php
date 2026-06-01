<?php
namespace App\Jobs;

use App\Events\LinkPreviewReady;
use App\Models\Message;
use App\Services\LinkPreviewService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class FetchLinkPreviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public function backoff(): array { return [10, 30]; }

    public function __construct(public string $url, public int $messageId) {}

    public function handle(LinkPreviewService $service): void
    {
        $message = Message::find($this->messageId);
        if (!$message) return;

        // Only fetch http/https
        if (!preg_match('/^https?:\/\//i', $this->url)) return;

        $preview = $service->fetch($this->url);
        if ($preview) {
            broadcast(new LinkPreviewReady($message->conversation_id, $this->messageId, $preview));
        }
    }

    public function failed(Throwable $e): void
    {
        \Log::warning('FetchLinkPreviewJob failed', ['url' => $this->url, 'error' => $e->getMessage()]);
    }
}
