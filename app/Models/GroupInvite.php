<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GroupInvite extends Model {
    protected $fillable = ['conversation_id','invited_by','token','expires_at','used_at'];
    protected $casts = ['expires_at'=>'datetime','used_at'=>'datetime'];
    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function inviter() { return $this->belongsTo(User::class,'invited_by'); }
    public function isExpired(): bool { return $this->expires_at->isPast(); }
    public function isUsed(): bool { return $this->used_at !== null; }
    public function isValid(): bool { return !$this->isExpired() && !$this->isUsed(); }
}
