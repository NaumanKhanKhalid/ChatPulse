<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLog extends Model
{
    protected $fillable = [
        'admin_id', 'action', 'target_type', 'target_id',
        'target_label', 'details', 'ip_address',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /** Record an admin action in the audit trail. Never throws. */
    public static function record(string $action, ?string $targetType = null, $targetId = null, ?string $targetLabel = null, ?string $details = null): void
    {
        try {
            static::create([
                'admin_id'     => auth()->id(),
                'action'       => $action,
                'target_type'  => $targetType,
                'target_id'    => $targetId !== null ? (string) $targetId : null,
                'target_label' => $targetLabel,
                'details'      => $details,
                'ip_address'   => request()->ip(),
            ]);
        } catch (\Throwable) {
            // Audit logging must never break the actual action
        }
    }
}
