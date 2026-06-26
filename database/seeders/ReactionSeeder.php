<?php
namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReactionSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::all()->keyBy('username');

        $this->reactToNth('Northwind Studio', 5, [
            [$u['ahmed_raza'],  '🎉'],
            [$u['usman_tariq'], '🎉'],
            [$u['zara_sheikh'], '🔥'],
            [$u['hina_malik'],  '👍'],
        ]);

        $this->reactToNth('Northwind Studio', 11, [
            [$u['sara_karim'],  '👍'],
            [$u['zara_sheikh'], '👍'],
        ]);

        $this->reactToNth('Design Critique', 9, [
            [$u['ahmed_raza'],  '🚀'],
            [$u['ali_hassan'],  '🚀'],
            [$u['zara_sheikh'], '💚'],
        ]);

        $this->reactToNth('Frontend Guild', 9, [
            [$u['hina_malik'],  '🏅'],
            [$u['ahmed_raza'],  '🏅'],
        ]);

        $this->reactToNth('Weekend Crew', 11, [
            [$u['sara_karim'],  '😄'],
            [$u['ahmed_raza'],  '😄'],
            [$u['hina_malik'],  '❤️'],
        ]);
    }

    private function reactToNth(string $convName, int $n, array $reactions): void
    {
        $conv = Conversation::where('name', $convName)->first();
        if (!$conv) return;

        $msg = $conv->messages()->orderBy('created_at')->skip($n - 1)->first();
        if (!$msg) $msg = $conv->messages()->latest()->first();
        if (!$msg) return;

        foreach ($reactions as [$user, $emoji]) {
            MessageReaction::firstOrCreate([
                'message_id' => $msg->id,
                'user_id'    => $user->id,
                'emoji'      => $emoji,
            ]);
        }
    }
}
