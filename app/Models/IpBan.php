<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class IpBan extends Model {
    protected $fillable = ['ip_address','banned_by','reason','expires_at'];
    protected $casts = ['expires_at'=>'datetime'];
    public function banner() { return $this->belongsTo(User::class,'banned_by'); }
    public function isActive(): bool { return !$this->expires_at || $this->expires_at->isFuture(); }
}
