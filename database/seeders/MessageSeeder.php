<?php
namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::all()->keyBy('username');

        // ── Per-conversation realistic message threads ────────────────────
        $this->thread(
            'Northwind Studio',
            [
                [$u['sara_karim'],   'Morning team! Design review is today at 4pm — client joining. Decks due by noon 🙌', -3],
                [$u['ahmed_raza'],   'On it — dropping them in the shared deck within the hour.', -3],
                [$u['usman_tariq'],  'Same. Backend summary slides are done already.', -3],
                [$u['ali_hassan'],   'Research deck is uploading now. Should be ready in 10 min.', -3],
                [$u['sara_karim'],   'Polls just shipped 🎉 vote on the launch date?', -2],
                [$u['ahmed_raza'],   'Voted! Hoping for Thursday honestly.', -2],
                [$u['zara_sheikh'],  'Same — Thursday works for everyone right?', -2],
                [$u['fatima_ali'],   'Thursday is good for me 👍', -2],
                [$u['usman_tariq'],  'Reverb scaling tip: keep broadcast jobs on a dedicated queue so chat latency stays low under load.', -1],
                [$u['ahmed_raza'],   'Good call. I\'ll add a `broadcasting` queue to the Forge config today.', -1],
                [$u['admin'],        'Nice. Also bump the worker count on staging before the demo.', -1],
                [$u['sara_karim'],   'Brand palette is final — warmer neutrals, emerald accent locked at #10b981.', -1],
                [$u['zara_sheikh'],  'Love the emerald. Feels premium without being loud.', 0],
                [$u['hina_malik'],   'Tailwind config updated to match. All tokens in place ✅', 0],
                [$u['ahmed_raza'],   'Anyone want to pair on the message search feature this afternoon?', 0],
                [$u['usman_tariq'],  'I\'m free after 3. Let\'s do it.', 0],
            ]
        );

        $this->thread(
            'Design Critique',
            [
                [$u['sara_karim'],  'Sharing the new onboarding flow for feedback. Be brutal 😅', -4],
                [$u['zara_sheikh'], 'Step 3 feels too cluttered. Can we split it into two screens?', -4],
                [$u['ali_hassan'],  'Agreed. Users are dropping off there in the prototype tests.', -4],
                [$u['hina_malik'],  'The progress bar also needs more contrast in dark mode.', -3],
                [$u['sara_karim'],  'Good catches. Revising now.', -3],
                [$u['ahmed_raza'],  'The animations on the welcome screen are chef\'s kiss 🤌', -2],
                [$u['zara_sheikh'], 'Updated mocks just posted in the Figma link. Check the new step 3.', -1],
                [$u['ali_hassan'],  'Much better! Clear hierarchy now.', -1],
                [$u['hina_malik'],  'Dark mode contrast fixed too. Looking sharp.', 0],
                [$u['sara_karim'],  'Shipping this to staging today. Great crit session everyone 💚', 0],
            ]
        );

        $this->thread(
            'Frontend Guild',
            [
                [$u['hina_malik'],   'Has anyone tried the new Tailwind v4 container queries? Game changer.', -5],
                [$u['ahmed_raza'],   'Yes! Replaced like 40 breakpoint hacks with 3 container rules.', -5],
                [$u['sara_karim'],   'The @starting-style CSS rule is also wild. Entrance animations without JS.', -4],
                [$u['zara_sheikh'],  'Adding that to the component library this week.', -4],
                [$u['usman_tariq'],  'Alpine 3.14 dropped too. The new `$el.closest()` magic is handy.', -3],
                [$u['ali_hassan'],   'Our chat composer uses that now actually. So clean.', -3],
                [$u['hina_malik'],   'Anyone benchmarking Vite 6? Build times on this project feel snappy.', -2],
                [$u['ahmed_raza'],   '786ms cold build. Pretty happy with that.', -2],
                [$u['sara_karim'],   'Ship it. Frontend Guild stamp of approval 🏅', -1],
                [$u['zara_sheikh'],  'Next topic: CSS anchor positioning — anyone tried it in Chrome 125+?', 0],
            ]
        );

        $this->thread(
            'Laravel Devs',
            [
                [$u['usman_tariq'],  'Reverb is handling 800 concurrent connections on the $6 DigitalOcean droplet. Wild.', -6],
                [$u['ahmed_raza'],   'WebSockets at that scale on shared hardware is impressive honestly.', -6],
                [$u['admin'],        'We should document the horizontal scaling strategy before the team grows.', -5],
                [$u['omar_farooq'],  'Done — I\'ll add it to the Notion runbook today.', -5],
                [$u['usman_tariq'],  'Queue tip: give broadcasting its own worker. Instant delivery.', -4],
                [$u['ahmed_raza'],   'Already on it after Northwind Studio channel convo 😂', -4],
                [$u['fatima_ali'],   'Ran a full regression suite. 0 failures after the queue refactor.', -3],
                [$u['admin'],        'Excellent. Deploying to prod tonight.', -3],
                [$u['omar_farooq'],  'Deployment successful. All systems green ✅', -2],
                [$u['usman_tariq'],  'Response time down 40% vs last week. Queue isolation was the bottleneck.', -1],
                [$u['ahmed_raza'],   'Documenting this as a performance win in the sprint retro.', 0],
            ]
        );

        $this->thread(
            'Weekend Crew',
            [
                [$u['ali_hassan'],   'Trail run this Saturday? Margalla Hills, early start — 6:30am.', -7],
                [$u['sara_karim'],   'I\'m in! Need to get off the laptop for a day 😅', -7],
                [$u['ahmed_raza'],   'Same. Count me in.', -6],
                [$u['zara_sheikh'],  'I\'ll come but 6:30 is early... fine fine I\'ll set an alarm.', -6],
                [$u['omar_farooq'],  'Trail 3 or trail 5? Trail 5 has better views.', -5],
                [$u['ali_hassan'],   'Trail 5 it is. Meeting at the main gate parking.', -5],
                [$u['hina_malik'],   'Bringing homemade granola bars for everyone 🎒', -3],
                [$u['sara_karim'],   'You\'re the best Hina!!', -3],
                [$u['ahmed_raza'],   'Post-run coffee at Mocca nearby?', -2],
                [$u['ali_hassan'],   'Obviously. It\'s tradition at this point.', -1],
                [$u['zara_sheikh'],  'Saturday gang 🌄 Can\'t wait.', 0],
            ]
        );

        $this->thread(
            'Product Leads',
            [
                [$u['admin'],        'Q2 roadmap doc is shared. Please review before Thursday call.', -3],
                [$u['sara_karim'],   'Read it. Love the focus on real-time features. Polls + calls in one sprint is ambitious.', -3],
                [$u['ahmed_raza'],   'Doable if we scope it tight. I\'ve broken it into sub-tasks already.', -2],
                [$u['usman_tariq'],  'Infrastructure is ready. Just need the feature flags wired up.', -2],
                [$u['admin'],        'Ship date target: end of month. Let\'s make it happen.', -1],
                [$u['sara_karim'],   'Design is unblocked. Handing off to frontend today.', 0],
            ]
        );

        // ── DM threads ───────────────────────────────────────────────────
        $this->dmThread('admin', 'sara_karim', [
            ['sara_karim', 'Hey! Quick question — can I get write access to the staging config?', -2],
            ['admin',      'Sure, I\'ll add you now. Ping me if it doesn\'t work.', -2],
            ['sara_karim', 'Got it, thank you! 🙏', -2],
            ['admin',      'Any time. How\'s the onboarding redesign coming?', -1],
            ['sara_karim', 'Really well actually. Sharing in Design Critique today.', 0],
        ]);

        $this->dmThread('admin', 'ahmed_raza', [
            ['ahmed_raza', 'The API rate limit is hitting during load tests. Can we raise it for staging?', -3],
            ['admin',      'Done — bumped to 200 req/min on staging. Prod stays at 60.', -3],
            ['ahmed_raza', 'Perfect, thanks. Tests are passing now.', -2],
            ['admin',      'Nice. Let me know results before we push to prod.', -1],
            ['ahmed_raza', 'All green ✅ Will send the report tonight.', 0],
        ]);

        $this->dmThread('admin', 'usman_tariq', [
            ['usman_tariq', 'Redis is at 87% memory on the dev server. Should I clear it?', -1],
            ['admin',       'Yeah go ahead. Run `redis-cli FLUSHDB` on dev only — not prod!', -1],
            ['usman_tariq', 'Done. Down to 12% now.', 0],
        ]);

        $this->dmThread('admin', 'ali_hassan', [
            ['ali_hassan', 'User interview recordings are in the shared Drive. 6 sessions total.', -4],
            ['admin',      'Great. I\'ll review them this week and share highlights.', -4],
            ['ali_hassan', 'Key theme: people love the speed but want better file search.', -2],
            ['admin',      'Adding that to the Q3 backlog. Good catch.', -1],
        ]);

        $this->dmThread('sara_karim', 'ahmed_raza', [
            ['sara_karim', 'Ahmed can you check the animation on the message composer? Feels laggy on Firefox.', -2],
            ['ahmed_raza', 'Looking at it now. It\'s the backdrop-filter — Firefox handles it differently.', -2],
            ['ahmed_raza', 'Fixed! Used a solid background fallback for Firefox. Looks clean.', -1],
            ['sara_karim', 'Perfect! You\'re a lifesaver 🙌', 0],
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function thread(string $convName, array $lines): void
    {
        $conv = Conversation::where('name', $convName)->first();
        if (!$conv) return;

        $base = now()->subHours(8);
        $gap  = 0;

        foreach ($lines as $i => [$user, $body, $dayOffset]) {
            $gap += rand(4, 35);
            $ts = now()->addDays($dayOffset)->setTime(
                rand(8, 19),
                rand(0, 59)
            );
            // spread messages within day
            $ts = $ts->addMinutes($gap % 480);

            $msg = Message::create([
                'conversation_id' => $conv->id,
                'user_id'         => $user->id,
                'body'            => $body,
                'type'            => 'text',
                'sent_at'         => $ts,
                'created_at'      => $ts,
                'updated_at'      => $ts,
            ]);

            MessageRead::create([
                'message_id' => $msg->id,
                'user_id'    => $user->id,
                'read_at'    => $ts,
            ]);

            $conv->update([
                'last_message_id'   => $msg->id,
                'last_activity_at'  => $ts,
            ]);
        }
    }

    private function dmThread(string $usernameA, string $usernameB, array $lines): void
    {
        $u    = User::all()->keyBy('username');
        $a    = $u[$usernameA];
        $b    = $u[$usernameB];

        $conv = Conversation::where('type', 'direct')
            ->whereHas('participants', fn($q) => $q->where('user_id', $a->id))
            ->whereHas('participants', fn($q) => $q->where('user_id', $b->id))
            ->first();

        if (!$conv) return;

        $gap = 0;
        foreach ($lines as [$username, $body, $dayOffset]) {
            $gap += rand(3, 25);
            $sender = $u[$username];
            $ts = now()->addDays($dayOffset)->setTime(rand(9, 20), rand(0, 59))->addMinutes($gap % 300);

            $msg = Message::create([
                'conversation_id' => $conv->id,
                'user_id'         => $sender->id,
                'body'            => $body,
                'type'            => 'text',
                'sent_at'         => $ts,
                'created_at'      => $ts,
                'updated_at'      => $ts,
            ]);

            MessageRead::create([
                'message_id' => $msg->id,
                'user_id'    => $sender->id,
                'read_at'    => $ts,
            ]);

            $conv->update([
                'last_message_id'  => $msg->id,
                'last_activity_at' => $ts,
            ]);
        }
    }
}
