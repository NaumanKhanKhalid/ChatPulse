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

    /**
     * Notifications are only created for things the conversation list cannot
     * already show: @mentions. Plain new messages are intentionally skipped —
     * the unread badge on the conversation already covers those.
     */
    public function notifyNewMessage(Message $message): void
    {
        $body = $message->body ?? '';
        if ($body === '' || !str_contains($body, '@')) return;

        $conversation = $message->conversation;
        $sender = $message->user;

        $participants = $conversation->participants()
            ->with('user')
            ->where('user_id', '!=', $sender->id)
            ->get();

        foreach ($participants as $participant) {
            $user = $participant->user;
            if (!$user || $participant->is_muted) continue;

            $handle = $user->username ?: strtolower(str_replace(' ', '_', $user->name));
            $mentioned = stripos($body, '@' . $handle) !== false
                || stripos($body, '@' . strtok($user->name, ' ')) !== false;
            if (!$mentioned) continue;

            $this->create($user, 'mention',
                $sender->name . ' mentioned you',
                substr($body, 0, 100),
                ['conversation_id' => $conversation->id, 'message_id' => $message->id]
            );
        }
    }

    public function markAllRead(User $user): void
    {
        $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);
    }
}
