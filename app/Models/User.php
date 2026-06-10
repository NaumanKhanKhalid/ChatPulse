<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name','email','password','username','avatar','bio','status_message',
        'status_type','status_emoji','status_clears_at','role','is_online',
        'last_seen_at','is_guest','is_banned','banned_at','banned_reason',
        'dark_mode','email_notifications','email_digest',
    ];

    protected $hidden = ['password','remember_token'];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_online' => 'boolean',
            'is_guest' => 'boolean',
            'is_banned' => 'boolean',
            'dark_mode' => 'boolean',
            'email_notifications' => 'boolean',
            'last_seen_at' => 'datetime',
            'banned_at' => 'datetime',
            'status_clears_at' => 'datetime',
        ];
    }

    // relationships
    public function conversations() { return $this->belongsToMany(Conversation::class, 'conversation_participants')->withPivot('role','joined_at','last_read_at','is_muted')->withTimestamps(); }
    public function messages() { return $this->hasMany(Message::class); }
    public function reactions() { return $this->hasMany(MessageReaction::class); }
    public function bookmarks() { return $this->hasMany(Bookmark::class); }
    public function notifications() { return $this->hasMany(Notification::class); }
    public function calls() { return $this->belongsToMany(Call::class, 'call_participants')->withPivot('joined_at','left_at'); }

    // helpers
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isGuest(): bool { return $this->is_guest; }
    public function getAvatarUrlAttribute(): string {
        if ($this->avatar) return asset('storage/' . $this->avatar);
        $initials = urlencode(substr($this->name, 0, 1));
        return "https://ui-avatars.com/api/?name={$initials}&background=10b981&color=fff&size=128";
    }
    public function getStatusColorAttribute(): string {
        return match($this->status_type) {
            'busy' => 'bg-busy',
            'away' => 'bg-away',
            default => $this->is_online ? 'bg-online' : 'bg-offline',
        };
    }
    public function unreadNotificationsCount(): int {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function avatarGradient(): array
    {
        // Derive a stable, well-distributed hue from the username via md5
        $seed = $this->username ?? $this->name;
        $hex  = md5($seed);
        $hue  = hexdec(substr($hex, 0, 4)) % 360; // 0–359, evenly spread

        return [
            self::hslToHex($hue, 80, 72),  // light pastel (gradient start)
            self::hslToHex($hue, 65, 38),  // rich saturated (gradient end)
        ];
    }

    private static function hslToHex(float $h, float $s, float $l): string
    {
        $s /= 100;
        $l /= 100;
        $a  = $s * min($l, 1 - $l);
        $f  = function (float $n) use ($h, $l, $a): float {
            $k = fmod($n + $h / 30, 12);
            return $l - $a * max(-1.0, min($k - 3, min(9 - $k, 1.0)));
        };
        return sprintf('#%02x%02x%02x',
            (int) round($f(0) * 255),
            (int) round($f(8) * 255),
            (int) round($f(4) * 255),
        );
    }
}
