<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'reporter_id', 'reported_user_id', 'message_id', 'conversation_id',
        'reason', 'details', 'excerpt', 'status', 'resolved_by', 'resolution', 'resolved_at',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    public const REASONS = [
        'spam'       => 'Spam / unsolicited links',
        'harassment' => 'Harassment or abuse',
        'off_topic'  => 'Off-topic flooding',
        'other'      => 'Other',
    ];

    public function reporter(): BelongsTo     { return $this->belongsTo(User::class, 'reporter_id'); }
    public function reportedUser(): BelongsTo { return $this->belongsTo(User::class, 'reported_user_id'); }
    public function resolver(): BelongsTo     { return $this->belongsTo(User::class, 'resolved_by'); }
    public function message(): BelongsTo      { return $this->belongsTo(Message::class); }
    public function conversation(): BelongsTo { return $this->belongsTo(Conversation::class); }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? ucfirst(str_replace('_', ' ', $this->reason));
    }
}
