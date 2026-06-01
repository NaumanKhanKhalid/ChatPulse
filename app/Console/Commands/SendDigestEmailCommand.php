<?php
namespace App\Console\Commands;

use App\Jobs\SendEmailNotificationJob;
use App\Models\User;
use Illuminate\Console\Command;

class SendDigestEmailCommand extends Command
{
    protected $signature = 'mail:send-digest';
    protected $description = 'Send daily/weekly digest emails to users';

    public function handle(): void
    {
        $today = today()->dayOfWeek;

        User::where('email_notifications', true)
            ->whereNotNull('email')
            ->where('is_guest', false)
            ->whereIn('email_digest', ['daily', 'weekly'])
            ->each(function (User $user) use ($today) {
                if ($user->email_digest === 'weekly' && $today !== 1) return;

                $unreadCount = $user->conversations()
                    ->get()
                    ->sum(fn($conv) => $conv->getUnreadCountFor($user));

                if ($unreadCount === 0) return;

                $convCount = $user->conversations()
                    ->get()
                    ->filter(fn($conv) => $conv->getUnreadCountFor($user) > 0)
                    ->count();

                SendEmailNotificationJob::dispatch(
                    $user,
                    'Your ChatPulse Digest',
                    "You have {$unreadCount} unread message(s) in {$convCount} conversation(s).",
                    ['unread_count' => $unreadCount, 'conversation_count' => $convCount]
                );
            });
    }
}
