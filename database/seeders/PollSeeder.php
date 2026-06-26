<?php
namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Database\Seeder;

class PollSeeder extends Seeder
{
    public function run(): void
    {
        $groups = Conversation::where('type', 'group')->with('participants')->get()->take(3);

        $pollData = [
            [
                'question' => 'What time works best for the weekly standup?',
                'options' => ['9:00 AM', '10:00 AM', '11:00 AM', '2:00 PM'],
            ],
            [
                'question' => 'Which tech stack should we use for the new service?',
                'options' => ['Laravel + Vue', 'Node.js + React', 'Django + Angular', 'Rails + Svelte'],
            ],
            [
                'question' => 'How should we prioritize next sprint?',
                'options' => ['Bug fixes first', 'New features first', 'Performance optimization', 'Documentation'],
            ],
        ];

        foreach ($groups as $i => $group) {
            if (!isset($pollData[$i])) break;
            $data = $pollData[$i];
            $creator = $group->participants->first();
            if (!$creator) continue;

            $message = Message::create([
                'conversation_id' => $group->id,
                'user_id' => $creator->user_id,
                'body' => null,
                'type' => 'poll',
                'sent_at' => now()->subHours(rand(1, 48)),
            ]);

            $poll = Poll::create([
                'message_id' => $message->id,
                'question' => $data['question'],
                'is_multiple_choice' => false,
                'is_anonymous' => false,
            ]);

            foreach ($data['options'] as $j => $text) {
                PollOption::create(['poll_id' => $poll->id, 'text' => $text, 'order' => $j]);
            }

            $group->update(['last_message_id' => $message->id, 'last_activity_at' => $message->sent_at]);
        }
    }
}
