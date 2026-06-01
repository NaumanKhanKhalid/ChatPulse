<?php
namespace App\Services;

use App\Models\Conversation;
use App\Models\GroupInvite;
use App\Models\User;
use App\Models\Message;
use App\Jobs\BroadcastMessageJob;
use Illuminate\Support\Str;

class GroupService
{
    public function generateInviteLink(Conversation $conversation, User $user): GroupInvite
    {
        return GroupInvite::create([
            'conversation_id' => $conversation->id,
            'invited_by' => $user->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function joinViaInvite(string $token, User $user): Conversation
    {
        $invite = GroupInvite::where('token', $token)->firstOrFail();

        if (!$invite->isValid()) {
            abort(410, 'This invite link has expired or already been used.');
        }

        $maxMembers = (int) config('app.group_max_members', 200);
        if ($invite->conversation->participants()->count() >= $maxMembers) {
            abort(422, 'This group has reached its member limit.');
        }

        $alreadyMember = $invite->conversation->participants()->where('user_id', $user->id)->exists();
        if (!$alreadyMember) {
            $invite->conversation->participants()->create([
                'user_id' => $user->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            // System message
            $this->sendSystemMessage($invite->conversation, "{$user->name} joined via invite link");
        }

        $invite->update(['used_at' => now()]);
        return $invite->conversation;
    }

    public function joinPublicGroup(Conversation $conversation, User $user): void
    {
        if ($conversation->is_private) abort(403, 'This group is private.');

        $maxMembers = (int) config('app.group_max_members', 200);
        if ($conversation->participants()->count() >= $maxMembers) {
            abort(422, 'This group has reached its member limit.');
        }

        $alreadyMember = $conversation->participants()->where('user_id', $user->id)->exists();
        if (!$alreadyMember) {
            $conversation->participants()->create([
                'user_id' => $user->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);
            $this->sendSystemMessage($conversation, "{$user->name} joined the group");
        }
    }

    public function sendSystemMessage(Conversation $conversation, string $text): Message
    {
        $message = $conversation->messages()->create([
            'user_id' => $conversation->created_by,
            'body' => $text,
            'type' => 'system',
            'sent_at' => now(),
        ]);
        $conversation->update(['last_message_id'=>$message->id,'last_activity_at'=>now()]);
        BroadcastMessageJob::dispatch($message);
        return $message;
    }

    public function updateGroup(Conversation $conversation, array $data): Conversation
    {
        $oldName = $conversation->name;
        $conversation->update(array_filter([
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'is_private' => $data['is_private'] ?? null,
        ], fn($v) => $v !== null));

        if (isset($data['name']) && $data['name'] !== $oldName) {
            $user = auth()->user();
            $this->sendSystemMessage($conversation, "{$user->name} renamed the group to \"{$data['name']}\"");
        }

        return $conversation->fresh();
    }
}
