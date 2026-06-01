<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PollOption extends Model {
    protected $fillable = ['poll_id','text','order'];
    public function poll() { return $this->belongsTo(Poll::class); }
    public function votes() { return $this->hasMany(PollVote::class); }
    public function getVotesCountAttribute(): int { return $this->votes()->count(); }
    public function getVoters(): \Illuminate\Support\Collection { return $this->votes()->with('user')->get()->pluck('user'); }
}
