<?php
namespace App\Jobs;

use App\Events\ExportReady;
use App\Models\Conversation;
use App\Services\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExportChatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public function backoff(): array { return [10, 30, 60]; }

    public function __construct(
        public Conversation $conversation,
        public int $userId,
        public string $format,
        public ?string $from = null,
        public ?string $to = null
    ) {}

    public function handle(ExportService $service): void
    {
        if ($this->format === 'pdf') {
            $path = $service->exportToPdf($this->conversation, $this->from, $this->to);
        } else {
            $path = $service->exportToCsv($this->conversation, $this->from, $this->to);
        }

        $downloadUrl = route('exports.download', ['path' => base64_encode($path)]);
        broadcast(new ExportReady($this->userId, $downloadUrl, $this->format));
    }

    public function failed(Throwable $e): void
    {
        \Log::error('ExportChatJob failed', ['conversation_id' => $this->conversation->id, 'error' => $e->getMessage()]);
    }
}
