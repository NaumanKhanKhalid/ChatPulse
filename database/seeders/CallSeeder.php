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
        $dms = Conversation::where('type', 'direct')->with('participants')->get()->take(5);
        $statuses = ['answered', 'answered', 'answered', 'missed', 'declined'];
        $types = ['audio', 'video'];

        foreach ($dms as $dm) {
            $participants = $dm->participants;
            if ($participants->count() < 2) continue;

            $initiator = $participants->first();
            $other = $participants->last();
            $status = $statuses[array_rand($statuses)];
            $type = $types[array_rand($types)];
            $startedAt = now()->subHours(rand(1, 72));
            $duration = $status === 'answered' ? rand(60, 1800) : 0;

            $call = Call::create([
                'conversation_id' => $dm->id,
                'initiated_by' => $initiator->user_id,
                'type' => $type,
                'status' => $status,
                'duration_seconds' => $duration,
                'started_at' => $startedAt,
                'ended_at' => $status === 'answered' ? $startedAt->copy()->addSeconds($duration) : $startedAt->copy()->addSeconds(15),
            ]);

            CallParticipant::create(['call_id'=>$call->id,'user_id'=>$initiator->user_id,'joined_at'=>$startedAt]);
            if ($status === 'answered') {
                CallParticipant::create(['call_id'=>$call->id,'user_id'=>$other->user_id,'joined_at'=>$startedAt->copy()->addSeconds(5),'left_at'=>$startedAt->copy()->addSeconds($duration)]);
            }
        }
    }
}
