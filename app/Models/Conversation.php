<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory;
    protected $fillable = ['type','name','description','avatar','is_private','created_by','last_message_id','last_activity_at'];
    protected $casts = ['is_private'=>'boolean','last_activity_at'=>'datetime'];

    public function participants() { return $this->hasMany(ConversationParticipant::class); }
    public function users() { return $this->belongsToMany(User::class, 'conversation_participants')->withPivot('role','joined_at','last_read_at','is_muted'); }
    public function messages() { return $this->hasMany(Message::class)->whereNull('deleted_at'); }
    public function allMessages() { return $this->hasMany(Message::class); }
    public function lastMessage() { return $this->belongsTo(Message::class, 'last_message_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function pins() { return $this->hasMany(MessagePin::class); }
    public function invites() { return $this->hasMany(GroupInvite::class); }

    public function isDirect(): bool { return $this->type === 'direct'; }
    public function isGroup(): bool { return $this->type === 'group'; }

    public function getOtherUser(User $user): ?User {
        return $this->users()->where('users.id', '!=', $user->id)->first();
    }

    public function getDisplayName(User $user): string {
        if ($this->isDirect()) {
            $other = $this->getOtherUser($user);
            return $other?->name ?? 'Unknown';
        }
        return $this->name ?? 'Group Chat';
    }

    public function getAvatarUrl(User $user): string {
        if ($this->isDirect()) {
            $other = $this->getOtherUser($user);
            return $other?->avatar_url ?? '';
        }
        if ($this->avatar) return asset('storage/' . $this->avatar);
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name ?? 'G') . "&background=10b981&color=fff&size=128";
    }

    public function getUnreadCountFor(User $user): int {
        $participant = $this->participants()->where('user_id', $user->id)->first();
        if (!$participant) return 0;
        $query = $this->messages()->where('user_id', '!=', $user->id);
        if ($participant->last_read_at) {
            $query->where('created_at', '>', $participant->last_read_at);
        }
        return $query->count();
    }
}
