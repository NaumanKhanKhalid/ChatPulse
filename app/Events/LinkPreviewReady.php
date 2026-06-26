<?php
namespace App\Events;

use App\Models\LinkPreview;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class LinkPreviewReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $conversationId,
        public int $messageId,
        public LinkPreview $preview
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->conversationId)];
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'preview' => [
                'url' => $this->preview->url,
                'title' => $this->preview->title,
                'description' => $this->preview->description,
                'image' => $this->preview->image,
                'site_name' => $this->preview->site_name,
            ]
        ];
    }
}
