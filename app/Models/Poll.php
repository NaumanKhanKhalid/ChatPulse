<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Poll extends Model {
    protected $fillable = ['message_id','question','is_multiple_choice','is_anonymous','ends_at'];
    protected $casts = ['is_multiple_choice'=>'boolean','is_anonymous'=>'boolean','ends_at'=>'datetime'];
    public function message() { return $this->belongsTo(Message::class); }
    public function options() { return $this->hasMany(PollOption::class)->orderBy('order'); }
    public function votes() { return $this->hasMany(PollVote::class); }
    public function getTotalVotesAttribute(): int { return $this->votes()->distinct('user_id')->count(); }
    public function isClosed(): bool { return $this->ends_at && $this->ends_at->isPast(); }
    public function userHasVoted(int $userId): bool { return $this->votes()->where('user_id',$userId)->exists(); }
}
