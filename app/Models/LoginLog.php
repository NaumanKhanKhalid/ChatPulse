<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id','email','event','ip_address','user_agent','new_device','created_at'];
    protected $casts = ['new_device' => 'boolean', 'created_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /** Record an auth event. Never throws — logging must not break login. */
    public static function record(string $event, ?User $user = null, ?string $email = null): void
    {
        try {
            $ip = request()->ip();
            $ua = substr((string) request()->userAgent(), 0, 500);

            $newDevice = false;
            if ($user && $event === 'success') {
                $newDevice = !static::where('user_id', $user->id)
                    ->where('event', 'success')
                    ->where(fn($q) => $q->where('ip_address', $ip)->orWhere('user_agent', $ua))
                    ->exists();
            }

            static::create([
                'user_id'    => $user?->id,
                'email'      => $email ?? $user?->email,
                'event'      => $event,
                'ip_address' => $ip,
                'user_agent' => $ua,
                'new_device' => $newDevice,
                'created_at' => now(),
            ]);
        } catch (\Throwable) {}
    }

    /** Short device label from the user agent. */
    public function deviceLabel(): string
    {
        $ua = $this->user_agent ?? '';
        $browser = str_contains($ua, 'Edg') ? 'Edge'
            : (str_contains($ua, 'Chrome') ? 'Chrome'
            : (str_contains($ua, 'Firefox') ? 'Firefox'
            : (str_contains($ua, 'Safari') ? 'Safari' : 'Unknown browser')));
        $os = str_contains($ua, 'Windows') ? 'Windows'
            : (str_contains($ua, 'Mac OS') ? 'macOS'
            : (str_contains($ua, 'Android') ? 'Android'
            : (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') ? 'iOS'
            : (str_contains($ua, 'Linux') ? 'Linux' : ''))));
        return trim($browser . ($os ? " · $os" : ''));
    }
}
