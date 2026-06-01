<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Bookmark extends Model {
    public $timestamps = false;
    protected $fillable = ['user_id','message_id','created_at'];
    protected $casts = ['created_at'=>'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function message() { return $this->belongsTo(Message::class); }
}
