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
        $u = User::all()->keyBy('username');

        // ── Public groups (matching prototype) ──────────────────────────
        $this->group('Northwind Studio',
            'Product & design team workspace. Ship fast, iterate often.',
            false, $u['sara_karim'],
            [$u['admin'], $u['ahmed_raza'], $u['usman_tariq'], $u['ali_hassan'], $u['fatima_ali'], $u['zara_sheikh']]);

        $this->group('Design Critique',
            'Weekly crit — share work, get feedback. No sugarcoating.',
            false, $u['sara_karim'],
            [$u['ahmed_raza'], $u['ali_hassan'], $u['zara_sheikh'], $u['hina_malik']]);

        $this->group('Frontend Guild',
            'Alpine, Tailwind, Blade & all things UI. Pixel perfection club.',
            false, $u['hina_malik'],
            [$u['ahmed_raza'], $u['sara_karim'], $u['zara_sheikh'], $u['ali_hassan'], $u['usman_tariq']]);

        $this->group('Laravel Devs',
            'Backend patterns, queues, Reverb & more. The PHP way.',
            false, $u['usman_tariq'],
            [$u['admin'], $u['ahmed_raza'], $u['omar_farooq'], $u['fatima_ali']]);

        $this->group('Weekend Crew',
            'Off-topic — trails, coffee, good times. No work talk allowed.',
            false, $u['ali_hassan'],
            [$u['sara_karim'], $u['ahmed_raza'], $u['zara_sheikh'], $u['hina_malik'], $u['omar_farooq']]);

        // ── Private groups ───────────────────────────────────────────────
        $this->group('Product Leads',
            'Roadmap, priorities, launches. Keep it confidential.',
            true, $u['admin'],
            [$u['sara_karim'], $u['ahmed_raza'], $u['usman_tariq']]);

        // ── Direct messages (everyone with admin so login works well) ───
        $dms = [
            [$u['admin'], $u['sara_karim']],
            [$u['admin'], $u['ahmed_raza']],
            [$u['admin'], $u['usman_tariq']],
            [$u['admin'], $u['ali_hassan']],
            [$u['sara_karim'], $u['ahmed_raza']],
            [$u['sara_karim'], $u['zara_sheikh']],
            [$u['ahmed_raza'], $u['usman_tariq']],
            [$u['hina_malik'], $u['omar_farooq']],
        ];

        foreach ($dms as [$a, $b]) {
            $conv = Conversation::create([
                'type' => 'direct',
                'is_private' => true,
                'created_by' => $a->id,
                'last_activity_at' => now()->subMinutes(rand(2, 1200)),
            ]);
            foreach ([$a, $b] as $member) {
                ConversationParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id'         => $member->id,
                    'role'            => 'member',
                    'joined_at'       => now(),
                ]);
            }
        }
    }

    private function group(string $name, string $desc, bool $private, User $creator, array $members): Conversation
    {
        $conv = Conversation::create([
            'type'             => 'group',
            'name'             => $name,
            'description'      => $desc,
            'is_private'       => $private,
            'created_by'       => $creator->id,
            'last_activity_at' => now()->subMinutes(rand(2, 480)),
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conv->id,
            'user_id'         => $creator->id,
            'role'            => 'admin',
            'joined_at'       => now(),
        ]);

        foreach ($members as $member) {
            ConversationParticipant::create([
                'conversation_id' => $conv->id,
                'user_id'         => $member->id,
                'role'            => 'member',
                'joined_at'       => now(),
            ]);
        }

        return $conv;
    }
}
