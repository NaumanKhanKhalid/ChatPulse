<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Call extends Model {
    protected $fillable = ['conversation_id','initiated_by','type','status','duration_seconds','started_at','ended_at'];
    protected $casts = ['started_at'=>'datetime','ended_at'=>'datetime'];
    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function initiator() { return $this->belongsTo(User::class,'initiated_by'); }
    public function participants() { return $this->belongsToMany(User::class,'call_participants')->withPivot('joined_at','left_at'); }
    public function getFormattedDurationAttribute(): string {
        $s = $this->duration_seconds ?? 0;
        return sprintf('%d:%02d', intdiv($s,60), $s%60);
    }
}
