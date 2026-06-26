<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CallParticipant extends Model {
    protected $fillable = ['call_id','user_id','joined_at','left_at'];
    protected $casts = ['joined_at'=>'datetime','left_at'=>'datetime'];
    public function call() { return $this->belongsTo(Call::class); }
    public function user() { return $this->belongsTo(User::class); }
}
