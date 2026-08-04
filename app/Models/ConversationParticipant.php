<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ConversationParticipant extends Model {
    protected $fillable = ['conversation_id','user_id','role','joined_at','last_read_at','is_muted','is_pinned','is_favourite','is_archived','cleared_at'];
    protected $casts = ['is_muted'=>'boolean','is_pinned'=>'boolean','is_favourite'=>'boolean','is_archived'=>'boolean','joined_at'=>'datetime','last_read_at'=>'datetime','cleared_at'=>'datetime'];
    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function isAdmin(): bool { return $this->role === 'admin'; }
}
