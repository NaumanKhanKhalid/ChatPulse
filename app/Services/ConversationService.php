<?php
namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Collection;

class ConversationService
{
    public function getOrCreateDirect(User $userA, User $userB): Conversation
    {
        // Find existing DM between these two users
        $existing = Conversation::where('type', 'direct')
            ->whereHas('participants', fn($q) => $q->where('user_id', $userA->id))
            ->whereHas('participants', fn($q) => $q->where('user_id', $userB->id))
            ->first();

        if ($existing) return $existing;

        $conversation = Conversation::create([
            'type' => 'direct',
            'created_by' => $userA->id,
            'last_activity_at' => now(),
            'is_private' => true,
        ]);

        ConversationParticipant::create(['conversation_id'=>$conversation->id,'user_id'=>$userA->id,'role'=>'member','joined_at'=>now()]);
        ConversationParticipant::create(['conversation_id'=>$conversation->id,'user_id'=>$userB->id,'role'=>'member','joined_at'=>now()]);

        return $conversation;
    }

    public function createGroup(User $creator, array $data): Conversation
    {
        $conversation = Conversation::create([
            'type' => 'group',
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_private' => $data['is_private'] ?? true,
            'created_by' => $creator->id,
            'last_activity_at' => now(),
        ]);

        ConversationParticipant::create(['conversation_id'=>$conversation->id,'user_id'=>$creator->id,'role'=>'admin','joined_at'=>now()]);

        if (!empty($data['member_ids'])) {
            foreach ($data['member_ids'] as $memberId) {
                ConversationParticipant::firstOrCreate(
                    ['conversation_id'=>$conversation->id,'user_id'=>$memberId],
                    ['role'=>'member','joined_at'=>now()]
                );
            }
        }

        return $conversation;
    }

    public function getUserConversations(User $user): Collection
    {
        return Conversation::whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->with(['lastMessage.user', 'users' => fn($q) => $q->select('users.id','users.name','users.avatar','users.is_online','users.status_type')])
            ->orderByDesc('last_activity_at')
            ->get();
    }

    public function addMember(Conversation $conversation, User $user, string $role = 'member'): ConversationParticipant
    {
        return ConversationParticipant::firstOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['role' => $role, 'joined_at' => now()]
        );
    }

    public function removeMember(Conversation $conversation, User $user): void
    {
        $conversation->participants()->where('user_id', $user->id)->delete();
    }

    public function isParticipant(Conversation $conversation, User $user): bool
    {
        return $conversation->participants()->where('user_id', $user->id)->exists();
    }

    public function isAdmin(Conversation $conversation, User $user): bool
    {
        return $conversation->participants()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();
    }
}
