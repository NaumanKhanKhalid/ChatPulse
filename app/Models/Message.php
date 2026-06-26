<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model {
    use HasFactory, SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = ['conversation_id','user_id','body','type','parent_id','forwarded_from_id','is_edited','edited_at','is_scheduled','scheduled_at','sent_at','deleted_at'];
    protected $casts = ['is_edited'=>'boolean','is_scheduled'=>'boolean','edited_at'=>'datetime','scheduled_at'=>'datetime','sent_at'=>'datetime','deleted_at'=>'datetime'];

    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function parent() { return $this->belongsTo(Message::class, 'parent_id'); }
    public function replies() { return $this->hasMany(Message::class, 'parent_id'); }
    public function forwardedFrom() { return $this->belongsTo(Message::class, 'forwarded_from_id'); }
    public function attachments() { return $this->hasMany(MessageAttachment::class); }
    public function reactions() { return $this->hasMany(MessageReaction::class); }
    public function reads() { return $this->hasMany(MessageRead::class); }
    public function poll() { return $this->hasOne(Poll::class); }

    public function isDeleted(): bool { return $this->deleted_at !== null; }

    public function getGroupedReactions(): array {
        return $this->reactions->groupBy('emoji')->map(fn($group) => [
            'emoji' => $group->first()->emoji,
            'count' => $group->count(),
            'users' => $group->pluck('user.name'),
        ])->values()->toArray();
    }
}
