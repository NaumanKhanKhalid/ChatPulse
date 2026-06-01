<?php
namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all()->keyBy('username');

        // Public groups
        $this->createGroup('General', 'General team discussion', false, $users['admin'], [
            $users['sara'], $users['ahmed'], $users['fatima'], $users['usman'],
            $users['ali'], $users['maria'], $users['zara'], $users['omar'],
        ]);

        $this->createGroup('Engineering', 'Backend and frontend engineering team', false, $users['ahmed'], [
            $users['admin'], $users['usman'], $users['ali'], $users['bilal'],
        ]);

        $this->createGroup('Design', 'UI/UX design discussions', false, $users['maria'], [
            $users['sara'], $users['zara'], $users['fatima'],
        ]);

        // Private groups
        $this->createGroup('Leadership', 'Management channel', true, $users['admin'], [
            $users['sara'], $users['ahmed'],
        ]);

        $this->createGroup('QA Team', 'Quality assurance', true, $users['hina'], [
            $users['omar'], $users['bilal'],
        ]);

        // DMs
        $dmPairs = [
            [$users['admin'], $users['sara']],
            [$users['admin'], $users['ahmed']],
            [$users['sara'], $users['fatima']],
            [$users['ahmed'], $users['usman']],
            [$users['ali'], $users['maria']],
        ];

        foreach ($dmPairs as [$a, $b]) {
            $conv = Conversation::create([
                'type' => 'direct',
                'is_private' => true,
                'created_by' => $a->id,
                'last_activity_at' => now()->subMinutes(rand(1, 1440)),
            ]);
            ConversationParticipant::create(['conversation_id'=>$conv->id,'user_id'=>$a->id,'role'=>'member','joined_at'=>now()]);
            ConversationParticipant::create(['conversation_id'=>$conv->id,'user_id'=>$b->id,'role'=>'member','joined_at'=>now()]);
        }
    }

    private function createGroup(string $name, string $description, bool $private, User $creator, array $members): Conversation
    {
        $conv = Conversation::create([
            'type' => 'group',
            'name' => $name,
            'description' => $description,
            'is_private' => $private,
            'created_by' => $creator->id,
            'last_activity_at' => now()->subMinutes(rand(1, 720)),
        ]);

        ConversationParticipant::create(['conversation_id'=>$conv->id,'user_id'=>$creator->id,'role'=>'admin','joined_at'=>now()]);

        foreach ($members as $member) {
            ConversationParticipant::create([
                'conversation_id' => $conv->id,
                'user_id' => $member->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);
        }

        return $conv;
    }
}
