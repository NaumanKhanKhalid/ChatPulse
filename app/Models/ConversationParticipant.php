<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ConversationParticipant extends Model {
    protected $fillable = ['conversation_id','user_id','role','joined_at','last_read_at','is_muted'];
    protected $casts = ['is_muted'=>'boolean','joined_at'=>'datetime','last_read_at'=>'datetime'];
    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function isAdmin(): bool { return $this->role === 'admin'; }
}
