<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthSnapshot extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'cpu_pct','mem_pct','disk_pct','pending_jobs','failed_jobs',
        'online_users','messages_last_hour','db_ok','reverb_ok','created_at',
    ];
    protected $casts = ['db_ok' => 'boolean', 'reverb_ok' => 'boolean', 'created_at' => 'datetime'];
}
