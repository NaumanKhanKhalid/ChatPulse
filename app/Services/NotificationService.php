<?php
namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Message;

class NotificationService
{
    public function create(User $user, string $type, string $title, string $body, array $data = []): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }

    public function notifyNewMessage(Message $message): void
    {
        $conversation = $message->conversation;
        $sender = $message->user;

        $participants = $conversation->participants()
            ->with('user')
            ->where('user_id', '!=', $sender->id)
            ->get();

        foreach ($participants as $participant) {
            $user = $participant->user;
            if (!$user || $participant->is_muted) continue;

            $this->create($user, 'new_message',
                $sender->name,
                $message->body ? substr($message->body, 0, 100) : 'Sent an attachment',
                ['conversation_id' => $conversation->id, 'message_id' => $message->id]
            );
        }
    }

    public function markAllRead(User $user): void
    {
        $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);
    }
}
