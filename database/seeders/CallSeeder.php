<?php
namespace Database\Seeders;

use App\Models\Call;
use App\Models\CallParticipant;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Seeder;

class CallSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::all()->keyBy('username');

        // Matching the prototype call log exactly
        $entries = [
            // [conv_type, conv_name_or_other, initiator, type, status, when_mins_ago, duration_s]
            ['dm',    'ahmed_raza',      'ahmed_raza',     'video', 'answered',  100, 724],   // incoming video from Ahmed, 12:04
            ['dm',    'usman_tariq',     'admin',          'audio', 'answered',  165, 228],   // outgoing audio to Usman, 3:48
            ['dm',    'ali_hassan',      'ali_hassan',     'audio', 'missed',    250, 0],     // incoming missed from Ali
            ['group', 'Design Critique', 'admin',          'video', 'answered',  1120, 2712], // outgoing group video, 45:12
            ['dm',    'sara_karim',      'sara_karim',     'audio', 'answered',  1430, 42],   // incoming audio from Sara, 0:42
            ['dm',    'ahmed_raza',      'admin',          'video', 'answered',  3600, 1845], // outgoing video to Ahmed yesterday
            ['dm',    'fatima_ali',      'fatima_ali',     'audio', 'missed',    4320, 0],    // missed from Fatima
        ];

        foreach ($entries as [$type, $target, $initiatorUsername, $callType, $status, $minsAgo, $dur]) {
            $initiator = $u[$initiatorUsername];

            if ($type === 'dm') {
                $otherUser = $u[$target];
                $conv = Conversation::where('type', 'direct')
                    ->whereHas('participants', fn($q) => $q->whereIn('user_id', [$initiator->id, $otherUser->id]))
                    ->whereHas('participants', fn($q) => $q->whereIn('user_id', [$initiator->id, $otherUser->id]))
                    ->first();
                if (!$conv) continue;
                $otherParticipant = $otherUser;
            } else {
                $conv = Conversation::where('name', $target)->first();
                if (!$conv) continue;
                $otherParticipant = null;
            }

            $startedAt = now()->subMinutes($minsAgo);

            $call = Call::create([
                'conversation_id'  => $conv->id,
                'initiated_by'     => $initiator->id,
                'type'             => $callType,
                'status'           => $status,
                'duration_seconds' => $dur,
                'started_at'       => $startedAt,
                'ended_at'         => $startedAt->copy()->addSeconds(max($dur, 15)),
            ]);

            CallParticipant::create([
                'call_id'   => $call->id,
                'user_id'   => $initiator->id,
                'joined_at' => $startedAt,
                'left_at'   => $status === 'answered' ? $startedAt->copy()->addSeconds($dur) : null,
            ]);

            if ($status === 'answered' && $otherParticipant) {
                CallParticipant::create([
                    'call_id'   => $call->id,
                    'user_id'   => $otherParticipant->id,
                    'joined_at' => $startedAt->copy()->addSeconds(4),
                    'left_at'   => $startedAt->copy()->addSeconds($dur),
                ]);
            } elseif ($status === 'answered' && $type === 'group') {
                // Add a couple of group participants
                $groupMembers = $conv->participants()
                    ->where('user_id', '!=', $initiator->id)
                    ->take(3)
                    ->pluck('user_id');
                foreach ($groupMembers as $uid) {
                    CallParticipant::create([
                        'call_id'   => $call->id,
                        'user_id'   => $uid,
                        'joined_at' => $startedAt->copy()->addSeconds(rand(3, 10)),
                        'left_at'   => $startedAt->copy()->addSeconds($dur),
                    ]);
                }
            }
        }
    }
}
