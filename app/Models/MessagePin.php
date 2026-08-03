<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MessagePin extends Model {
    // Table has only pinned_at — no created_at/updated_at columns
    public $timestamps = false;
    protected $fillable = ['conversation_id','message_id','pinned_by','pinned_at'];
    protected $casts = ['pinned_at'=>'datetime'];
    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function message() { return $this->belongsTo(Message::class); }
    public function pinner() { return $this->belongsTo(User::class,'pinned_by'); }
}
