<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A single line for the admin dashboard's live activity feed.
 * Kept deliberately light — it is display-only, never a source of truth.
 */
class AdminActivity implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $kind,       // message | login | signup | group | report | ban | call
        public string $actor,      // who did it
        public string $text,       // what happened
        public ?string $target = null, // where (conversation / group name)
        public ?array $grad = null,    // avatar gradient for the actor
        public ?string $initials = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.activity')];
    }

    public function broadcastWith(): array
    {
        return [
            'kind'     => $this->kind,
            'actor'    => $this->actor,
            'text'     => $this->text,
            'target'   => $this->target,
            'grad'     => $this->grad ?? ['#94a3b8', '#475569'],
            'initials' => $this->initials ?? '?',
            'at'       => now()->format('g:i:s A'),
        ];
    }

    /** Convenience: build from a user model. */
    public static function fire(string $kind, ?\App\Models\User $user, string $text, ?string $target = null): void
    {
        try {
            $name = $user?->name ?? 'Someone';
            $ini  = collect(explode(' ', $name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
            broadcast(new self($kind, $name, $text, $target, $user?->avatarGradient(), $ini));
        } catch (\Throwable) {}
    }
}
