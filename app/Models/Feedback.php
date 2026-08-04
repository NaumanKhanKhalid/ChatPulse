<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'user_id', 'type', 'message', 'contact_email', 'page',
        'browser', 'screen', 'status', 'admin_note', 'handled_by',
    ];

    public const TYPES = [
        'bug'        => 'Something is broken',
        'suggestion' => 'I have an idea',
        'question'   => 'I need help',
        'other'      => 'Something else',
    ];

    public const STATUSES = ['open' => 'Open', 'reviewing' => 'Reviewing', 'resolved' => 'Resolved'];

    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function handler(): BelongsTo { return $this->belongsTo(User::class, 'handled_by'); }

    public function typeLabel(): string { return self::TYPES[$this->type] ?? ucfirst($this->type); }
}
