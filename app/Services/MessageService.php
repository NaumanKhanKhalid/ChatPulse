<?php
namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use App\Models\MessageRead;
use App\Events\MessageSent;
use App\Events\MessageUpdated;
use App\Events\MessageDeleted;
use App\Jobs\FetchLinkPreviewJob;
use App\Jobs\NotifyParticipantsJob;
use App\Jobs\BroadcastMessageJob;
use Illuminate\Support\Str;

class MessageService
{
    public function send(Conversation $conversation, User $user, array $data): Message
    {
        $body = isset($data['body']) ? strip_tags($data['body']) : null;

        $message = $conversation->messages()->create([
            'user_id' => $user->id,
            'body' => $body,
            'type' => $data['type'] ?? 'text',
            'parent_id' => $data['parent_id'] ?? null,
            'is_scheduled' => isset($data['scheduled_at']),
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'sent_at' => isset($data['scheduled_at']) ? null : now(),
        ]);

        if (!$message->is_scheduled) {
            $conversation->update([
                'last_message_id' => $message->id,
                'last_activity_at' => now(),
            ]);

            // Mark as read for sender
            MessageRead::firstOrCreate(
                ['message_id' => $message->id, 'user_id' => $user->id],
                ['read_at' => now()]
            );

            BroadcastMessageJob::dispatch($message);
            NotifyParticipantsJob::dispatch($message);

            // Check for URLs and fetch link previews
            if ($body && preg_match_all('/https?:\/\/[^\s]+/', $body, $matches)) {
                foreach (array_unique($matches[0]) as $url) {
                    FetchLinkPreviewJob::dispatch($url, $message->id);
                }
            }
        }

        return $message;
    }

    public function edit(Message $message, string $body): Message
    {
        $message->update([
            'body' => strip_tags($body),
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        broadcast(new MessageUpdated($message->load(['user', 'attachments', 'reactions.user'])))->toOthers();

        return $message;
    }

    public function delete(Message $message): void
    {
        $conversationId = $message->conversation_id;
        $messageId = $message->id;

        $message->delete();

        broadcast(new MessageDeleted($conversationId, $messageId));
    }

    public function markConversationAsRead(Conversation $conversation, User $user): void
    {
        $participant = $conversation->participants()->where('user_id', $user->id)->first();
        if ($participant) {
            $participant->update(['last_read_at' => now()]);
        }

        // Bulk insert message reads
        $unreadMessageIds = $conversation->messages()
            ->where('user_id', '!=', $user->id)
            ->whereDoesntHave('reads', fn($q) => $q->where('user_id', $user->id))
            ->pluck('id');

        foreach ($unreadMessageIds as $messageId) {
            MessageRead::firstOrCreate(
                ['message_id' => $messageId, 'user_id' => $user->id],
                ['read_at' => now()]
            );
        }
    }

    public function forward(Message $original, User $user, array $conversationIds): array
    {
        $forwarded = [];
        foreach ($conversationIds as $convId) {
            $conversation = Conversation::find($convId);
            if (!$conversation) continue;
            if (!$conversation->users()->where('users.id', $user->id)->exists()) continue;

            $message = $conversation->messages()->create([
                'user_id' => $user->id,
                'body' => $original->body,
                'type' => 'forwarded',
                'forwarded_from_id' => $original->id,
                'sent_at' => now(),
            ]);

            $conversation->update([
                'last_message_id' => $message->id,
                'last_activity_at' => now(),
            ]);

            BroadcastMessageJob::dispatch($message);
            $forwarded[] = $message;
        }
        return $forwarded;
    }
}
