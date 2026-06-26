<?php
namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Console\Command;

class ChatStatsCommand extends Command
{
    protected $signature = 'chat:stats';
    protected $description = 'Display ChatPulse application statistics';

    public function handle(): void
    {
        $this->table(['Metric', 'Value'], [
            ['Total Users', User::count()],
            ['Online Users', User::where('is_online', true)->count()],
            ['Guest Users', User::where('is_guest', true)->count()],
            ['Total Conversations', Conversation::count()],
            ['Direct Messages', Conversation::where('type', 'direct')->count()],
            ['Group Chats', Conversation::where('type', 'group')->count()],
            ['Total Messages', Message::count()],
            ['Messages Today', Message::whereDate('created_at', today())->count()],
        ]);
    }
}
