<?php
namespace Database\Seeders;

use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReactionSeeder extends Seeder
{
    public function run(): void
    {
        $messages = Message::where('type', 'text')->inRandomOrder()->limit(60)->get();
        $users = User::all();
        $emojis = ['👍', '❤️', '😂', '🔥', '✅', '👏'];

        foreach ($messages as $message) {
            // 30% chance of getting reactions
            if (rand(1, 10) > 3) continue;

            $reactors = $users->random(rand(1, 3));
            foreach ($reactors as $user) {
                $emoji = $emojis[array_rand($emojis)];
                MessageReaction::firstOrCreate([
                    'message_id' => $message->id,
                    'user_id' => $user->id,
                    'emoji' => $emoji,
                ]);
            }
        }
    }
}
