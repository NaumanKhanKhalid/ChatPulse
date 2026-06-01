<?php
namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRead;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    private array $sampleMessages = [
        'Hey everyone! Ready for the standup?',
        'Just pushed the new feature to staging. Please review when you can.',
        'The design mockups are ready in Figma. Check them out!',
        'Can someone review my PR? It\'s been waiting for a day.',
        'Great work on the release everyone! 🎉',
        'Meeting in 15 minutes, don\'t forget!',
        'I\'ve fixed the bug we discussed yesterday.',
        'The client loved the new UI. Well done team!',
        'Anyone facing issues with the dev environment?',
        'Deployment successful! All systems green ✅',
        'Need help with the API integration.',
        'Let\'s schedule a code review session this week.',
        'The test coverage is now at 85%!',
        'Reminder: Performance review forms due Friday.',
        'New sprint planning doc has been shared.',
        'Server response time improved by 40% after optimization.',
        'Anyone available for a quick call?',
        'The database migration ran successfully in production.',
        'Updated the documentation with the latest changes.',
        'The mobile app is now live on the App Store! 🚀',
        'We need to refactor the authentication module.',
        'Redis cache is working perfectly now.',
        'Team lunch tomorrow at 1pm, everyone invited!',
        'Just merged 3 pull requests. Code review done.',
        'The new onboarding flow has been deployed.',
        'Security audit passed with no critical issues.',
        'Need feedback on the color scheme for the dashboard.',
        'All unit tests passing. Ready to merge.',
        'The payment gateway integration is complete.',
        'Weekly report has been sent to stakeholders.',
    ];

    public function run(): void
    {
        $conversations = Conversation::with('participants.user')->get();

        foreach ($conversations as $conversation) {
            $participants = $conversation->participants->where('user_id', '!=', null);
            if ($participants->isEmpty()) continue;

            $messageCount = rand(15, 40);
            $createdAt = now()->subDays(7);

            for ($i = 0; $i < $messageCount; $i++) {
                $participant = $participants->random();
                $createdAt = $createdAt->addMinutes(rand(5, 120));

                $message = Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $participant->user_id,
                    'body' => $this->sampleMessages[array_rand($this->sampleMessages)],
                    'type' => 'text',
                    'sent_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Mark as read by sender
                MessageRead::create([
                    'message_id' => $message->id,
                    'user_id' => $participant->user_id,
                    'read_at' => $createdAt,
                ]);

                // Update conversation last_message
                $conversation->update([
                    'last_message_id' => $message->id,
                    'last_activity_at' => $createdAt,
                ]);
            }
        }
    }
}
